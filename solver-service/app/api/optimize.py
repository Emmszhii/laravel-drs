from fastapi import APIRouter
from app.models.schemas import AllocationRequest, AllocationResponse, RoutingRequest, RoutingResponse
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


@router.post("/routing", response_model=RoutingResponse)
def optimize_routing(payload: RoutingRequest) -> RoutingResponse:
    """
    Called by RunOptimizationJob after /allocation completes, with that
    stage's depot->zone quantities passed through as `payload.allocations`.
    Returns a CVRPTW route per vehicle — which zones it visits, in what
    order, respecting capacity and the shared operating time window.
    """
    return routing_solver.solve(payload)
