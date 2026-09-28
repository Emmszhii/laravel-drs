from fastapi import APIRouter

from app.models.replenishment import ReplenishmentRequest, ReplenishmentResponse
from app.solvers import replenishment

router = APIRouter()


@router.post("/solve", response_model=ReplenishmentResponse)
def solve(payload: ReplenishmentRequest) -> ReplenishmentResponse:
    """
    Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)
    runs the solver on the quarter's budget. Recommends only — the Admin
    approves or rejects the run in the backend. No database access here.
    """
    return replenishment.solve(payload)
