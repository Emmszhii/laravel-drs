"""
Performance and correctness bench tests for the allocation/routing solvers.

Run with: pytest tests/ -v

These are intentionally minimal — expand with real fixture data (mirroring
the Albay/Marikina/Tacloban scenarios from the seeder) before relying on
this for thesis evaluation numbers.
"""
import time
import pytest
from app.models.schemas import (
    AllocationRequest,
    ZoneDemand,
    DepotCapacity,
    VehicleSpec,
    AllocationLine,
    RoutingRequest,
)
from app.solvers.pulp_solver import PulpAllocationSolver
from app.solvers.ortools_solver import ORToolsRoutingSolver


def sample_payload() -> AllocationRequest:
    return AllocationRequest(
        disaster_id="test-disaster-1",
        zones=[
            ZoneDemand(id="zone-bogtong", lat=13.141, lng=123.745, demand=120),
            ZoneDemand(id="zone-rawis", lat=13.150, lng=123.752, demand=80),
        ],
        depots=[
            DepotCapacity(id="depot-a", lat=13.1449, lng=123.7515, capacity=150),
            DepotCapacity(id="depot-b", lat=13.1510, lng=123.6980, capacity=100),
        ],
    )


def test_pulp_solver_fully_satisfies_demand_within_capacity():
    solver = PulpAllocationSolver()
    result = solver.solve(sample_payload())

    total_allocated = sum(line.quantity for line in result.allocations)
    assert total_allocated == 200  # 120 + 80, capacity covers it exactly
    assert result.objective_value == 0  # no unmet demand expected


def test_pulp_solver_reports_unmet_demand_when_capacity_insufficient():
    payload = sample_payload()
    payload.depots[0].capacity = 50
    payload.depots[1].capacity = 50

    solver = PulpAllocationSolver()
    result = solver.solve(payload)

    assert result.objective_value > 0  # some demand should be unmet


def test_pulp_solver_runs_within_reasonable_time():
    solver = PulpAllocationSolver()
    start = time.perf_counter()
    solver.solve(sample_payload())
    elapsed = time.perf_counter() - start

    assert elapsed < 5.0  # generous ceiling for a 2-zone/2-depot problem


def sample_routing_payload() -> RoutingRequest:
    allocation = sample_payload()
    pulp_result = PulpAllocationSolver().solve(allocation)

    return RoutingRequest(
        disaster_id=allocation.disaster_id,
        zones=allocation.zones,
        depots=allocation.depots,
        vehicles=[
            VehicleSpec(id="vehicle-a", capacity=150, depot_id="depot-a"),
            VehicleSpec(id="vehicle-b", capacity=100, depot_id="depot-b"),
        ],
        allocations=pulp_result.allocations,
        time_window_minutes=480,
    )


def test_ortools_routing_solver_returns_nonempty_routes_with_valid_allocations():
    solver = ORToolsRoutingSolver()
    result = solver.solve(sample_routing_payload())

    assert result.algorithm == "ortools_cvrptw"
    assert result.objective_value >= 0
    assert len(result.routes) == 2

    # Every vehicle that received a positive allocation from its home depot
    # must produce a real multi-stop route (depot -> zone(s) -> depot), not
    # a discarded/empty one.
    nonempty_routes = [r for r in result.routes if r.stops]
    assert len(nonempty_routes) > 0

    for route in nonempty_routes:
        assert route.stops[0].location_id in {"depot-a", "depot-b"}
        assert route.stops[-1].location_id in {"depot-a", "depot-b"}
        # Arrival times must be non-decreasing along the route.
        arrivals = [s.arrival_time for s in route.stops]
        assert arrivals == sorted(arrivals)


def test_ortools_routing_solver_respects_vehicle_capacity():
    payload = sample_routing_payload()
    # Force a vehicle capacity far below the zone demand allocated to it —
    # the solved route's total delivered quantity must never exceed capacity.
    payload.vehicles = [VehicleSpec(id="vehicle-a", capacity=10, depot_id="depot-a")]
    payload.allocations = [AllocationLine(zone_id="zone-bogtong", depot_id="depot-a", quantity=120)]

    solver = ORToolsRoutingSolver()
    result = solver.solve(payload)

    route = result.routes[0]
    delivered = sum(s.quantity for s in route.stops)
    assert delivered <= 10


def test_ortools_routing_solver_returns_empty_routes_when_no_vehicles():
    payload = sample_routing_payload()
    payload.vehicles = []

    result = ORToolsRoutingSolver().solve(payload)

    assert result.routes == []
    assert result.objective_value == 0


def test_pulp_solver_handles_zero_zones_and_depots():
    payload = AllocationRequest(disaster_id="d-empty", zones=[], depots=[])

    result = PulpAllocationSolver().solve(payload)

    assert result.allocations == []
    assert result.objective_value == 0


def test_pulp_solver_skips_capacity_constraint_when_capacity_is_zero():
    # depot.capacity == 0 is the solver's "no constraint" sentinel (see the
    # `if d.capacity > 0` guard in pulp_solver.py) — demand should still be
    # fully allocatable to a zero-capacity depot when it's the only option.
    payload = AllocationRequest(
        disaster_id="d-1",
        zones=[ZoneDemand(id="zone-a", lat=14.1, lng=121.05, demand=100)],
        depots=[DepotCapacity(id="depot-a", lat=14.1, lng=121.05, capacity=0)],
    )

    result = PulpAllocationSolver().solve(payload)

    total_allocated = sum(line.quantity for line in result.allocations)
    assert total_allocated == 100
    assert result.objective_value == 0


def test_pulp_solver_splits_demand_across_multiple_depots_when_needed():
    payload = AllocationRequest(
        disaster_id="d-1",
        zones=[ZoneDemand(id="zone-a", lat=14.1, lng=121.05, demand=150)],
        depots=[
            DepotCapacity(id="depot-a", lat=14.1, lng=121.05, capacity=80),
            DepotCapacity(id="depot-b", lat=14.2, lng=121.1, capacity=80),
        ],
    )

    result = PulpAllocationSolver().solve(payload)

    total_allocated = sum(line.quantity for line in result.allocations)
    assert total_allocated == 150
    assert result.objective_value == 0
    depots_used = {line.depot_id for line in result.allocations}
    assert len(depots_used) == 2


def test_ortools_routing_solver_returns_empty_routes_when_home_depot_is_missing():
    payload = sample_routing_payload()
    # Vehicle homed at a depot_id absent from payload.depots.
    payload.vehicles = [VehicleSpec(id="vehicle-ghost", capacity=100, depot_id="depot-unknown")]
    payload.allocations = [AllocationLine(zone_id="zone-bogtong", depot_id="depot-unknown", quantity=50)]

    result = ORToolsRoutingSolver().solve(payload)

    assert len(result.routes) == 1
    assert result.routes[0].vehicle_id == "vehicle-ghost"
    assert result.routes[0].stops == []


def test_ortools_routing_solver_returns_empty_routes_when_zone_demand_is_empty():
    payload = sample_routing_payload()
    payload.allocations = []  # no demand allocated to any depot

    result = ORToolsRoutingSolver().solve(payload)

    assert all(route.stops == [] for route in result.routes)


def test_ortools_routing_solver_returns_empty_routes_when_infeasible():
    # A shift window too short to reach the zone and back makes the model
    # infeasible — the solver must emit empty routes, not raise.
    payload = sample_routing_payload()
    payload.time_window_minutes = 0

    result = ORToolsRoutingSolver().solve(payload)

    assert all(route.stops == [] for route in result.routes)
    assert result.objective_value == 0


def test_ortools_routing_solver_splits_multiple_vehicles_at_the_same_depot():
    # Standard CVRP doesn't split a single zone's demand across vehicles —
    # each zone node is visited by exactly one vehicle. Use two zones so a
    # 2-vehicle fleet at one depot has a real reason to split the work.
    payload = sample_routing_payload()
    payload.vehicles = [
        VehicleSpec(id="vehicle-a1", capacity=130, depot_id="depot-a"),
        VehicleSpec(id="vehicle-a2", capacity=90, depot_id="depot-a"),
    ]
    payload.allocations = [
        AllocationLine(zone_id="zone-bogtong", depot_id="depot-a", quantity=120),
        AllocationLine(zone_id="zone-rawis", depot_id="depot-a", quantity=80),
    ]

    result = ORToolsRoutingSolver().solve(payload)

    assert len(result.routes) == 2
    total_delivered = sum(s.quantity for route in result.routes for s in route.stops)
    assert total_delivered == 200
    visited_zones = {s.location_id for route in result.routes for s in route.stops if s.quantity > 0}
    assert visited_zones == {"zone-bogtong", "zone-rawis"}


def test_haversine_matrix_produces_symmetric_zero_diagonal_matrix():
    locations = [(14.1, 121.05), (14.2, 121.10), (14.15, 121.08)]

    matrix = ORToolsRoutingSolver._haversine_matrix(locations)

    for i in range(len(locations)):
        assert matrix["distances"][i][i] == 0
        assert matrix["durations"][i][i] == 0

    for i in range(len(locations)):
        for j in range(len(locations)):
            assert matrix["distances"][i][j] == pytest.approx(matrix["distances"][j][i])

    # durations are distances converted at AVERAGE_SPEED_KMH — sanity-check
    # the conversion rather than the exact haversine value.
    assert matrix["durations"][0][1] == pytest.approx(matrix["distances"][0][1] / 40 * 3600)
