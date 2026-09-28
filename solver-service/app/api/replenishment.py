from fastapi import APIRouter

from app.models.replenishment import BacktestRequest, ReplenishmentRequest, ReplenishmentResponse
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


@router.post("/budget-curve")
def budget_curve(payload: ReplenishmentRequest) -> dict:
    """Same payload as /solve; returns 11 {budget, expected_shortage} points and the next-P10,000 diff."""
    return replenishment.budget_curve(payload)


@router.post("/backtest")
def backtest(payload: BacktestRequest) -> dict:
    """Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and the office's own orders)."""
    return replenishment.backtest(payload)
