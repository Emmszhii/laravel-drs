from typing import Literal

from pydantic import BaseModel, Field


class ReplenishmentItem(BaseModel):
    id: str
    q: int = Field(ge=0, description="Stock on hand at the start of the quarter")
    reserved: int = Field(0, ge=0, description="Held for approved requests; subtracted from q")
    unit_cost: float | None = Field(None, gt=0)
    space_per_unit: float | None = Field(None, ge=0)
    weight: float = Field(1, gt=0, description="VED priority weight w_i")
    coverage_k: int = Field(8, ge=1, description="Cover use in k of the last M quarters")
    target: int | None = Field(None, ge=0, description="Fixed target; skips the history rule when given")
    history: list[int] = Field(default_factory=list, description="Quarterly use, oldest first")


class ReplenishmentRequest(BaseModel):
    rule: Literal["per_item_multiplier"] = "per_item_multiplier"
    budget: float = Field(ge=0)
    free_space: float | None = Field(None, ge=0)
    m: int = Field(8, ge=1, description="M, the number of past quarters the covering level looks at")
    time_limit_s: float = Field(10, gt=0)
    items: list[ReplenishmentItem]


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
    status: Literal["optimal", "feasible", "infeasible"]
    rule: str
    orders: list[OrderLine]
    objective: float
    spent: float
    binding: dict[str, bool]
    space_modeled: bool
    solve_ms: int
