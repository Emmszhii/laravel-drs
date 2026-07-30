import math
from ortools.constraint_solver import pywrapcp, routing_enums_pb2
from app.models.schemas import AllocationRequest, AllocationResponse, AllocationLine
from app.solvers.base import BaseSolver


class ORToolsRoutingSolver(BaseSolver):
    """
    Depot -> zone routing using OR-Tools' routing library.
    v1 uses haversine straight-line distance as the cost matrix;
    swap `_distance_matrix` for real road-network distances (OSRM) later —
    see the "open items" section of SUMMARY.md.
    """

    def solve(self, payload: AllocationRequest) -> AllocationResponse:
        locations = [(d.lat, d.lng) for d in payload.depots] + [(z.lat, z.lng) for z in payload.zones]
        num_depots = len(payload.depots)

        if not locations:
            return AllocationResponse(algorithm="ortools_routing", objective_value=0, allocations=[])

        manager = pywrapcp.RoutingIndexManager(len(locations), max(num_depots, 1), 0)
        routing = pywrapcp.RoutingModel(manager)

        distance_matrix = self._distance_matrix(locations)

        def distance_callback(from_index, to_index):
            from_node = manager.IndexToNode(from_index)
            to_node = manager.IndexToNode(to_index)
            return int(distance_matrix[from_node][to_node] * 1000)

        transit_callback_index = routing.RegisterTransitCallback(distance_callback)
        routing.SetArcCostEvaluatorOfAllVehicles(transit_callback_index)

        search_parameters = pywrapcp.DefaultRoutingSearchParameters()
        search_parameters.first_solution_strategy = (
            routing_enums_pb2.FirstSolutionStrategy.PATH_CHEAPEST_ARC
        )

        solution = routing.SolveWithParameters(search_parameters)

        allocations: list[AllocationLine] = []
        objective_value = solution.ObjectiveValue() / 1000 if solution else 0.0

        # NOTE: translating the raw OR-Tools route back into
        # (zone, depot, quantity) lines depends on your final data
        # shape — this is a starting skeleton, not a finished mapping.

        return AllocationResponse(
            algorithm="ortools_routing",
            objective_value=objective_value,
            allocations=allocations,
        )

    @staticmethod
    def _distance_matrix(locations: list[tuple[float, float]]) -> list[list[float]]:
        def haversine(a, b):
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

        return [[haversine(a, b) for b in locations] for a in locations]
