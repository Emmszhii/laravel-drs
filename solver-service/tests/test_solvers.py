"""
Performance and correctness bench tests for the allocation/routing solvers.

Run with: pytest tests/ -v

These are intentionally minimal — expand with real fixture data (mirroring
the Albay/Marikina/Tacloban scenarios from the seeder) before relying on
this for thesis evaluation numbers.
"""
import time
from app.models.schemas import AllocationRequest, ZoneDemand, DepotCapacity
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


def test_ortools_routing_solver_returns_a_response():
    solver = ORToolsRoutingSolver()
    result = solver.solve(sample_payload())

    assert result.algorithm == "ortools_routing"
    assert result.objective_value >= 0
