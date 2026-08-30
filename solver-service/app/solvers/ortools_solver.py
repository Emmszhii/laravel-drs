import math
from collections import defaultdict
from ortools.constraint_solver import pywrapcp, routing_enums_pb2
from app.models.schemas import RoutingRequest, RoutingResponse, VehicleRoute, RoutingStop
from app.solvers.base import BaseRoutingSolver
from app.services.osrm_matrix import fetch_distance_matrix, in_laguna_bbox

# Fallback assumption when OSRM duration data isn't available (haversine path).
AVERAGE_SPEED_KMH = 40


class ORToolsRoutingSolver(BaseRoutingSolver):
    """
    CVRPTW: for each depot, jointly routes that depot's vehicles over the
    zones it was allocated to ship to (from `RoutingRequest.allocations`,
    decided upstream by PulpAllocationSolver), subject to each vehicle's
    capacity and a shared operating time window. Produces an actual
    multi-stop route per vehicle instead of discarding the solved model.

    Distances/durations come from OSRM's /table matrix service when every
    location involved falls inside the Laguna bbox OSRM's data covers;
    falls back to haversine distance + an assumed average speed otherwise.
    """

    def solve(self, payload: RoutingRequest) -> RoutingResponse:
        if not payload.vehicles:
            return RoutingResponse(algorithm="ortools_cvrptw", objective_value=0, routes=[])

        zones_by_id = {z.id: z for z in payload.zones}
        depots_by_id = {d.id: d for d in payload.depots}

        # Aggregate allocation-stage quantities by (depot, zone) — a stop's
        # demand only counts against vehicles homed at the depot that was
        # actually allocated to ship it.
        demand_by_depot_zone: dict[tuple[str, str], int] = defaultdict(int)
        for line in payload.allocations:
            demand_by_depot_zone[(line.depot_id, line.zone_id)] += line.quantity

        vehicles_by_depot: dict[str, list] = defaultdict(list)
        for vehicle in payload.vehicles:
            vehicles_by_depot[vehicle.depot_id].append(vehicle)

        routes: list[VehicleRoute] = []
        total_objective = 0.0

        for depot_id, fleet in vehicles_by_depot.items():
            home = depots_by_id.get(depot_id)
            zone_demand = {
                zone_id: qty
                for (d_id, zone_id), qty in demand_by_depot_zone.items()
                if d_id == depot_id and qty > 0
            }

            if home is None or not zone_demand:
                routes += [VehicleRoute(vehicle_id=v.id, stops=[]) for v in fleet]
                continue

            solved = self._solve_depot(home, fleet, zone_demand, zones_by_id, payload.time_window_minutes)
            routes += solved["routes"]
            total_objective += solved["objective_value"]

        return RoutingResponse(
            algorithm="ortools_cvrptw",
            objective_value=total_objective,
            routes=routes,
        )

    @classmethod
    def _solve_depot(cls, home, fleet: list, zone_demand: dict, zones_by_id: dict, time_window_minutes: int) -> dict:
        stop_ids = list(zone_demand.keys())
        locations = [(home.lat, home.lng)] + [(zones_by_id[z].lat, zones_by_id[z].lng) for z in stop_ids]
        demands = [0] + [zone_demand[z] for z in stop_ids]
        num_vehicles = len(fleet)

        matrix = cls._resolve_matrix(locations)

        manager = pywrapcp.RoutingIndexManager(len(locations), num_vehicles, 0)
        routing = pywrapcp.RoutingModel(manager)

        def distance_callback(from_index, to_index):
            from_node = manager.IndexToNode(from_index)
            to_node = manager.IndexToNode(to_index)
            return int(matrix["distances"][from_node][to_node] * 1000)

        transit_callback_index = routing.RegisterTransitCallback(distance_callback)
        routing.SetArcCostEvaluatorOfAllVehicles(transit_callback_index)

        def demand_callback(from_index):
            node = manager.IndexToNode(from_index)
            return demands[node]

        demand_callback_index = routing.RegisterUnaryTransitCallback(demand_callback)
        routing.AddDimensionWithVehicleCapacity(
            demand_callback_index,
            0,  # no slack
            [v.capacity for v in fleet],
            True,  # cumul starts at zero
            "Capacity",
        )

        def time_callback(from_index, to_index):
            from_node = manager.IndexToNode(from_index)
            to_node = manager.IndexToNode(to_index)
            return int(matrix["durations"][from_node][to_node] / 60)  # seconds -> minutes

        time_callback_index = routing.RegisterTransitCallback(time_callback)
        routing.AddDimension(
            time_callback_index,
            time_window_minutes,  # allow waiting, capped at the shift length
            time_window_minutes,
            False,
            "Time",
        )
        time_dimension = routing.GetDimensionOrDie("Time")
        for node in range(len(locations)):
            index = manager.NodeToIndex(node)
            time_dimension.CumulVar(index).SetRange(0, time_window_minutes)

        search_parameters = pywrapcp.DefaultRoutingSearchParameters()
        search_parameters.first_solution_strategy = (
            routing_enums_pb2.FirstSolutionStrategy.PATH_CHEAPEST_ARC
        )

        solution = routing.SolveWithParameters(search_parameters)

        if solution is None:
            # Infeasible under this depot's combined fleet capacity/time
            # window — emit empty routes rather than raising, matching the
            # never-throw convention used elsewhere in this service.
            return {"routes": [VehicleRoute(vehicle_id=v.id, stops=[]) for v in fleet], "objective_value": 0.0}

        routes = []
        for vehicle_index, vehicle in enumerate(fleet):
            stops: list[RoutingStop] = []
            index = routing.Start(vehicle_index)
            while not routing.IsEnd(index):
                node = manager.IndexToNode(index)
                arrival = solution.Value(time_dimension.CumulVar(index))
                location_id = home.id if node == 0 else stop_ids[node - 1]
                stops.append(RoutingStop(location_id=location_id, arrival_time=arrival, quantity=demands[node]))
                index = solution.Value(routing.NextVar(index))
            node = manager.IndexToNode(index)
            arrival = solution.Value(time_dimension.CumulVar(index))
            stops.append(RoutingStop(location_id=home.id, arrival_time=arrival, quantity=0))
            routes.append(VehicleRoute(vehicle_id=vehicle.id, stops=stops))

        return {"routes": routes, "objective_value": solution.ObjectiveValue() / 1000}

    @staticmethod
    def _resolve_matrix(locations: list[tuple[float, float]]) -> dict:
        if locations and all(in_laguna_bbox(lat, lng) for lat, lng in locations):
            result = fetch_distance_matrix(locations)
            if result["ok"]:
                return {"distances": result["distances"], "durations": result["durations"]}

        return ORToolsRoutingSolver._haversine_matrix(locations)

    @staticmethod
    def _haversine_matrix(locations: list[tuple[float, float]]) -> dict:
        def haversine_km(a, b):
            lat1, lng1 = a
            lat2, lng2 = b
            r = 6371
            d_lat = math.radians(lat2 - lat1)
            d_lng = math.radians(lng2 - lng1)
            h = (
                math.sin(d_lat / 2) ** 2
                + math.cos(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.sin(d_lng / 2) ** 2
            )
            return r * 2 * math.atan2(math.sqrt(h), math.sqrt(1 - h))

        distances = [[haversine_km(a, b) for b in locations] for a in locations]
        durations = [[d / AVERAGE_SPEED_KMH * 3600 for d in row] for row in distances]  # seconds
        return {"distances": distances, "durations": durations}
