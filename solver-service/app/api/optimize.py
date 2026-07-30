from fastapi import APIRouter
from app.models.schemas import AllocationRequest, AllocationResponse
from app.solvers.pulp_solver import PulpAllocationSolver
from app.solvers.ortools_solver import ORToolsRoutingSolver

router = APIRouter()

allocation_solver = PulpAllocationSolver()
routing_solver = ORToolsRoutingSolver()


@router.post("/allocation", response_model=AllocationResponse)
def optimize_allocation(payload: AllocationRequest) -> AllocationResponse:
    """
    Called by App\\Jobs\\RunOptimizationJob (Laravel) whenever an incident
    is logged or escalates. Returns a suggested allocation for the
    coordinator to review and approve — never auto-applied.
    """
    return allocation_solver.solve(payload)


@router.post("/routing")
def optimize_routing(payload: AllocationRequest):
    """Depot-to-zone routing suggestions (straight-line v1; OSRM later)."""
    return routing_solver.solve(payload)
