from typing import Annotated, Literal

from pydantic import BaseModel, Field, model_validator

MAX_ITEMS = 2000
MAX_QUARTERS = 40

# Every float the model multiplies by must be a real, non-negative number: NaN/inf would reach CBC.
NonNegFloat = Annotated[float, Field(ge=0, allow_inf_nan=False)]
NonNegInt = Annotated[int, Field(ge=0)]


class ReplenishmentItem(BaseModel):
    id: str
    q: int = Field(ge=0, description="Stock on hand at the start of the quarter")
    reserved: int = Field(0, ge=0, description="Held for approved requests; subtracted from q")
    unit_cost: float | None = Field(None, gt=0, allow_inf_nan=False)
    space_per_unit: NonNegFloat | None = None
    weight: float = Field(1, gt=0, allow_inf_nan=False, description="VED priority weight w_i")
    coverage_k: int = Field(8, ge=1, description="Cover use in k of the last M quarters")
    target: int | None = Field(None, ge=0, description="Fixed target; skips the history rule when given")
    history: list[NonNegInt] = Field(default_factory=list, max_length=MAX_QUARTERS, description="Quarterly use, oldest first")


def _check_items(items: list[ReplenishmentItem], m: int) -> None:
    ids = [it.id for it in items]
    if len(ids) != len(set(ids)):
        raise ValueError("item ids must be unique")
    if bad := [it.id for it in items if it.coverage_k > m]:
        raise ValueError(f"coverage_k must be at most m={m} (items: {', '.join(bad[:5])})")


class ReplenishmentRequest(BaseModel):
    rule: Literal["per_item_multiplier", "scenario"] = "per_item_multiplier"
    budget: NonNegFloat
    free_space: NonNegFloat | None = Field(None, description="Depot space still free after the stock on hand")
    m: int = Field(8, ge=1, le=MAX_QUARTERS, description="M, the number of past quarters the covering level looks at")
    time_limit_s: float = Field(10, gt=0, le=60)
    items: list[ReplenishmentItem] = Field(max_length=MAX_ITEMS)

    @model_validator(mode="after")
    def _items_valid(self):
        _check_items(self.items, self.m)
        return self


class BacktestRequest(BaseModel):
    """Items carry their full encoded history; the backtest cuts it per quarter."""
    items: list[ReplenishmentItem] = Field(min_length=1, max_length=MAX_ITEMS)
    budget: NonNegFloat = Field(description="Budget for any quarter without its own entry in `budgets`")
    budgets: list[NonNegFloat | None] = Field(default_factory=list, max_length=MAX_QUARTERS, description="Per-quarter budget, same index as history")
    start: int = Field(4, ge=1, le=MAX_QUARTERS, description="First backtested quarter index (4 = the 5th encoded quarter)")
    initial_stock: dict[str, NonNegInt] = Field(default_factory=dict)
    actual_orders: dict[str, dict[str, NonNegInt]] = Field(default_factory=dict, description="The office's own orders, by quarter index")
    typhoon_quarters: list[int] = Field(default_factory=list)
    stockout_quarters: list[int] = Field(default_factory=list, description="Quarters where recorded use understates need")
    free_space: NonNegFloat | None = None
    m: int = Field(8, ge=1, le=MAX_QUARTERS)
    time_limit_s: float = Field(10, gt=0, le=60)

    @model_validator(mode="after")
    def _items_valid(self):
        _check_items(self.items, self.m)
        return self


class OrderLine(BaseModel):
    id: str
    x: int
    target: int | None
    shortfall: int
    shortfall_pct: float
    reason: str
    basis: Literal["multiplier", "limited_history", "given", "no_history", "no_unit_cost"]
    multiplier: float | None


class ReplenishmentResponse(BaseModel):
    status: Literal["optimal", "feasible", "infeasible", "not_solved"]
    rule: str
    orders: list[OrderLine]
    objective: float | None = Field(description="None when no order was found (infeasible / not_solved)")
    spent: float
    binding: dict[str, bool]
    space_modeled: bool
    solve_ms: int
