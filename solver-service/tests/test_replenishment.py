"""Stage 1 replenishment tests — Chapter III, Unit Testing Procedure."""

import random

from fastapi.testclient import TestClient

from app.models.replenishment import ReplenishmentItem, ReplenishmentRequest
from app.solvers.replenishment import (
    BUDGET_RAN_OUT, FULLY_COVERED, SET_MANUALLY, STOCK_COVERS, budget_curve, covering_level, item_target, solve,
)
from main import app


def worked_example(budget=200_000):
    # Table 5: four items, flat 1.5 targets, equal weights.
    return ReplenishmentRequest(budget=budget, items=[
        ReplenishmentItem(id="food", q=150, unit_cost=500, target=480),
        ReplenishmentItem(id="hygiene", q=60, unit_cost=350, target=300),
        ReplenishmentItem(id="water", q=250, unit_cost=150, target=600),
        ReplenishmentItem(id="medical", q=200, unit_cost=900, target=128),
    ])


def by_id(res):
    return {o.id: o for o in res.orders}


def test_worked_example_least_total_shortage():
    res = solve(worked_example())
    o = by_id(res)
    assert (o["food"].x, o["hygiene"].x, o["water"].x, o["medical"].x) == (127, 240, 350, 0)
    assert res.spent == 200_000
    assert o["food"].shortfall_pct == 42.3
    assert o["water"].reason == FULLY_COVERED and o["hygiene"].reason == FULLY_COVERED
    assert o["medical"].reason == STOCK_COVERS
    assert o["food"].reason == BUDGET_RAN_OUT
    assert res.binding == {"budget": True, "space": False}
    assert res.space_modeled is False


def test_next_10k_buys_20_food_packs():
    base, more = by_id(solve(worked_example())), by_id(solve(worked_example(210_000)))
    assert more["food"].x - base["food"].x == 20
    assert round(more["food"].shortfall_pct) == 38


def test_covering_level_follows_ved_coverage():
    h = [100, 120, 90, 300, 110, 95, 105, 130]
    assert covering_level(h, k=8) == 300  # vital: highest
    assert covering_level(h, k=7) == 130  # essential: 2nd-highest
    assert covering_level(h, k=6) == 120  # desirable: 3rd-highest
    assert covering_level([1] * 4 + h, k=8) == 300  # only the last M=8 count


def test_multiplier_is_covering_level_over_average():
    target, basis, mult = item_target(ReplenishmentItem(id="a", q=0, history=[100, 120, 90, 300, 110, 95, 105, 130], coverage_k=7), 8)
    assert (target, basis) == (130, "multiplier")
    assert mult == round(130 / 131.25, 4)


def test_cold_start_rules():
    few, _, _ = item_target(ReplenishmentItem(id="a", q=0, history=[10, 20, 31]), 8)
    assert few == 31  # ceil(1.5 * 20.33)
    assert item_target(ReplenishmentItem(id="b", q=0, history=[10, 20, 31]), 8)[1] == "limited_history"
    assert item_target(ReplenishmentItem(id="c", q=0, history=[]), 8)[0] is None
    assert item_target(ReplenishmentItem(id="d", q=0, history=[0, 0, 0, 0]), 8)[0] is None

    res = solve(ReplenishmentRequest(budget=1000, items=[
        ReplenishmentItem(id="new", q=0, unit_cost=10, history=[]),
        ReplenishmentItem(id="nocost", q=0, history=[5, 5, 5, 5]),
    ]))
    o = by_id(res)
    assert o["new"].reason == SET_MANUALLY and o["new"].basis == "no_history"
    assert o["nocost"].reason == SET_MANUALLY and o["nocost"].basis == "no_unit_cost"


def test_zero_budget_orders_nothing():
    o = by_id(solve(worked_example(budget=0)))
    assert all(line.x == 0 for line in o.values())
    assert o["food"].reason == BUDGET_RAN_OUT and o["medical"].reason == STOCK_COVERS


def test_reserved_stock_is_not_counted_as_available():
    o = by_id(solve(ReplenishmentRequest(budget=10_000, items=[
        ReplenishmentItem(id="a", q=100, reserved=40, unit_cost=10, target=100)])))
    assert o["a"].x == 40


def test_space_constraint_limits_orders_when_modeled():
    res = solve(ReplenishmentRequest(budget=1_000_000, free_space=110, items=[
        ReplenishmentItem(id="a", q=50, unit_cost=10, space_per_unit=1, target=100),
        ReplenishmentItem(id="b", q=50, unit_cost=10, space_per_unit=1, target=100),
    ]))
    assert res.space_modeled and res.binding == {"budget": False, "space": True}
    assert sum(o.x for o in res.orders) == 10
    assert {o.reason for o in res.orders if o.shortfall} == {"limited by depot space"}


def test_budget_never_exceeded_and_money_left_only_when_targets_met():
    rng = random.Random(7)
    for _ in range(40):
        items = [ReplenishmentItem(id=str(i), q=rng.randint(0, 50), unit_cost=rng.choice([15, 40, 120, 500]),
                                   weight=rng.choice([1, 2, 3]), target=rng.randint(0, 120)) for i in range(6)]
        budget = rng.randint(0, 20_000)
        res = solve(ReplenishmentRequest(budget=budget, items=items))
        assert res.spent <= budget
        cost = {it.id: it.unit_cost for it in items}
        short = [o for o in res.orders if o.shortfall > 0]
        if short:  # leftover must be too small to buy one more unit of anything still short
            assert budget - res.spent < min(cost[o.id] for o in short)


def test_budget_curve_has_11_levels_and_never_rises():
    curve = budget_curve(worked_example())
    pts = curve["points"]
    assert [p["pct"] for p in pts] == list(range(50, 151, 10))
    assert pts[5]["budget"] == 200_000
    shortages = [p["expected_shortage"] for p in pts]
    assert all(b <= a + 1e-9 for a, b in zip(shortages, shortages[1:]))
    assert shortages[-1] == round(3 / 480, 6)  # P300,000 is P1,500 (3 food packs) short of the P301,500 full cost
    assert curve["next_step"]["changes"] == [
        {"id": "food", "delta_x": 20, "shortfall_pct_before": 42.3, "shortfall_pct_after": 38.1}]


def test_budget_curve_never_rises_on_random_instances():
    rng = random.Random(11)
    for _ in range(10):
        items = [ReplenishmentItem(id=str(i), q=rng.randint(0, 50), unit_cost=rng.choice([15, 40, 120, 500]),
                                   weight=rng.choice([1, 2, 3]), target=rng.randint(0, 150)) for i in range(8)]
        pts = budget_curve(ReplenishmentRequest(budget=rng.randint(1_000, 30_000), items=items))["points"]
        vals = [p["expected_shortage"] for p in pts]
        assert all(b <= a + 1e-9 for a, b in zip(vals, vals[1:]))


def test_endpoint_round_trip():
    body = worked_example().model_dump()
    resp = TestClient(app).post("/api/v1/replenishment/solve", json=body)
    assert resp.status_code == 200
    assert [o["x"] for o in resp.json()["orders"]] == [127, 240, 350, 0]
