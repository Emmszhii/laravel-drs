"""Paper revision + review fixes: space after stock, short-history covering level, validation, no-solution handling."""

import math

import pulp
import pytest
from fastapi.testclient import TestClient
from pydantic import ValidationError

from app.models.replenishment import BacktestRequest, ReplenishmentItem, ReplenishmentRequest
from app.solvers import replenishment as rep
from app.solvers.replenishment import (
    BUDGET_RAN_OUT, FULLY_COVERED, NO_FEASIBLE, SET_MANUALLY, SPACE_LIMITED, STOCK_COVERS, STOPPED_EARLY,
    backtest, budget_curve, covering_level, solve,
)
from main import app


def by_id(res):
    return {o.id: o for o in res.orders}


# 1. V is the space still free after the stock on hand: only orders count, so x = 0 is always feasible.
def test_space_counts_orders_only_and_is_always_feasible():
    res = solve(ReplenishmentRequest(budget=10_000, free_space=0, items=[
        ReplenishmentItem(id="a", q=500, unit_cost=10, space_per_unit=1, target=600)]))
    assert res.status == "optimal" and res.objective is not None
    assert by_id(res)["a"].x == 0 and by_id(res)["a"].reason == SPACE_LIMITED

    res = solve(ReplenishmentRequest(budget=10_000, free_space=30, items=[
        ReplenishmentItem(id="a", q=500, unit_cost=10, space_per_unit=2, target=600)]))
    assert by_id(res)["a"].x == 15


# 2. Fewer than M quarters: k scales to the same share of the quarters available, rounded up.
def test_covering_level_scales_k_with_short_history():
    h = [10, 20, 30, 40, 50]
    assert covering_level(h, k=8) == 50  # ceil(8*5/8) = 5 of 5 -> highest
    assert covering_level(h, k=7) == 50  # ceil(35/8) = 5 -> highest
    assert covering_level(h, k=6) == 40  # ceil(30/8) = 4 of 5 -> 2nd-highest
    assert covering_level([10, 20, 30, 40], k=6) == 30  # ceil(24/8) = 3 of 4 -> 2nd-highest


# 3. Input validation: bad data is a 422, never a CBC crash.
def test_negative_history_and_duplicate_ids_are_422():
    client = TestClient(app)
    ok = {"budget": 100, "items": [{"id": "a", "q": 0, "unit_cost": 10, "history": [1, 2, 3, 4]}]}
    assert client.post("/api/v1/replenishment/solve", json=ok).status_code == 200

    neg = {**ok, "items": [{**ok["items"][0], "history": [1, -2, 3, 4]}]}
    assert client.post("/api/v1/replenishment/solve", json=neg).status_code == 422

    dup = {**ok, "items": ok["items"] * 2}
    assert client.post("/api/v1/replenishment/solve", json=dup).status_code == 422
    assert client.post("/api/v1/replenishment/budget-curve", json=dup).status_code == 422
    assert client.post("/api/v1/replenishment/backtest", json=dup).status_code == 422


@pytest.mark.parametrize("bad", [
    {"budget": math.nan}, {"budget": math.inf}, {"budget": -1}, {"free_space": math.inf},
    {"time_limit_s": 600}, {"m": 4},  # coverage_k 8 > m 4
])
def test_request_level_validation(bad):
    with pytest.raises(ValidationError):
        ReplenishmentRequest(**{"budget": 100, "items": [ReplenishmentItem(id="a", q=0)], **bad})


@pytest.mark.parametrize("bad", [
    {"unit_cost": math.nan}, {"space_per_unit": -1}, {"space_per_unit": math.inf}, {"weight": math.inf},
    {"history": [1] * 41},
])
def test_item_level_validation(bad):
    with pytest.raises(ValidationError):
        ReplenishmentItem(**{"id": "a", "q": 0, **bad})


# 4. A solve with no order never reports objective 0.
def no_solution(self, *args, **kwargs):
    self.status, self.sol_status = pulp.LpStatusNotSolved, pulp.LpSolutionNoSolutionFound


def test_no_solution_reports_none_and_every_planned_item(monkeypatch):
    monkeypatch.setattr(pulp.LpProblem, "solve", no_solution)
    res = solve(ReplenishmentRequest(budget=1_000, items=[
        ReplenishmentItem(id="a", q=0, unit_cost=10, target=50),
        ReplenishmentItem(id="b", q=100, unit_cost=10, target=50),
        ReplenishmentItem(id="c", q=0, target=5, history=[5, 5, 5, 5])]))
    assert res.status == "not_solved" and res.objective is None and res.spent == 0
    o = by_id(res)
    assert [o["a"].x, o["b"].x] == [0, 0]
    assert o["a"].reason == o["b"].reason == NO_FEASIBLE
    assert o["a"].shortfall == 50
    assert o["c"].reason == SET_MANUALLY
    assert TestClient(app).post("/api/v1/replenishment/solve",
                                json={"budget": 1_000, "items": [{"id": "a", "q": 0, "unit_cost": 10, "target": 50}]}
                                ).json()["objective"] is None


def test_budget_curve_skips_unsolved_levels(monkeypatch):
    real = rep.solve
    req = ReplenishmentRequest(budget=10_000, items=[ReplenishmentItem(id="a", q=0, unit_cost=100, target=200)])

    def flaky(r):
        if r.budget == 7_000:  # the 70% level fails
            return real(r).model_copy(update={"status": "not_solved", "objective": None})
        return real(r)

    monkeypatch.setattr(rep, "solve", flaky)
    pts = budget_curve(req)["points"]
    bad = pts[2]
    assert bad["pct"] == 70 and not bad["solved"] and bad["expected_shortage"] is None and bad["objective"] is None
    good = [p for p in pts if p["solved"]]
    assert len(good) == 10
    assert pts[3]["expected_shortage"] == pts[3]["objective"] < pts[1]["objective"]  # no fake 0 carried forward


# 5. Reasons are read from which constraint actually blocks one more unit of that item.
def test_reason_is_per_item_binding_constraint():
    res = solve(ReplenishmentRequest(budget=2_500, free_space=5, items=[
        ReplenishmentItem(id="a", q=0, unit_cost=10, space_per_unit=1, target=100),
        ReplenishmentItem(id="b", q=0, unit_cost=1_000, space_per_unit=0, target=100)]))
    o = by_id(res)
    assert (o["a"].x, o["b"].x) == (5, 2)
    assert o["a"].reason == SPACE_LIMITED and o["b"].reason == BUDGET_RAN_OUT
    assert res.binding == {"budget": True, "space": True}


def test_short_item_with_budget_and_space_left_says_solver_stopped_early(monkeypatch):
    def zero_incumbent(self, *args, **kwargs):
        for v in self.variables():
            v.varValue = 0
        self.status, self.sol_status = pulp.LpStatusNotSolved, pulp.LpSolutionIntegerFeasible

    monkeypatch.setattr(pulp.LpProblem, "solve", zero_incumbent)
    res = solve(ReplenishmentRequest(budget=1_000_000, items=[ReplenishmentItem(id="a", q=0, unit_cost=10, target=50)]))
    assert res.status == "feasible" and res.binding == {"budget": False, "space": False}
    assert by_id(res)["a"].reason == STOPPED_EARLY


# 6. Stage 2 uses each item's own quarters; fewer than 4 uses the flat 1.5 target.
def test_stage2_does_not_dilute_a_new_items_shortage():
    res = solve(ReplenishmentRequest(rule="scenario", budget=0, items=[
        ReplenishmentItem(id="old", q=100, unit_cost=10, history=[100] * 8),
        ReplenishmentItem(id="new", q=0, unit_cost=10, history=[100] * 5)]))
    assert res.objective == 1.0  # the new item is short its full average in all 5 of its quarters


def test_stage2_cold_start_uses_the_flat_target():
    o = by_id(solve(ReplenishmentRequest(rule="scenario", budget=1_000_000, items=[
        ReplenishmentItem(id="a", q=0, unit_cost=10, history=[30, 30])])))["a"]
    assert (o.x, o.target, o.basis, o.reason) == (45, 45, "limited_history", FULLY_COVERED)


def test_backtest_does_not_pad_a_new_items_history_with_zeros():
    req = BacktestRequest(budget=1_000_000, items=[
        ReplenishmentItem(id="food", q=0, unit_cost=10, history=[100] * 12),
        ReplenishmentItem(id="new", q=0, unit_cost=10, history=[50] * 6)])  # starts at quarter 6
    row = backtest(req)["quarters"][3]  # t = 7: 'new' has one real past quarter
    assert row["t"] == 7
    assert row["rules"]["flat_1_5"]["items_ran_out"] == 0  # flat target 75, not 1.5 x (50 / 7)


# 7. No unit cost: covered stock is "stock covers it"; only uncovered items are set manually.
def test_no_cost_item_already_covered():
    o = by_id(solve(ReplenishmentRequest(budget=100, items=[
        ReplenishmentItem(id="covered", q=10, history=[5, 5, 5, 5]),
        ReplenishmentItem(id="short", q=0, history=[5, 5, 5, 5])])))
    assert o["covered"].reason == STOCK_COVERS and o["covered"].basis == "no_unit_cost"
    assert o["short"].reason == SET_MANUALLY


# 8. Backtest feasibility also checks space when it is modeled.
def test_backtest_feasibility_checks_space():
    req = BacktestRequest(budget=1_000_000, free_space=10, items=[
        ReplenishmentItem(id="a", q=0, unit_cost=10, space_per_unit=1, history=[100] * 6)])
    rules = backtest(req)["quarters"][0]["rules"]
    assert rules["flat_1_5"]["feasible"] is False  # the flat rule ignores space
    assert rules["per_item_multiplier"]["feasible"] and rules["scenario"]["feasible"]
