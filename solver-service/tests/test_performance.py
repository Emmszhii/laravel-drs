"""
Performance pass marks (Chapter III, Performance Testing Procedure):
a Stage 2 solve in under 2 s for 200 items and 12 scenarios, and a full
11-point budget curve in under 20 s. Times are also printed so they can be
reported as measured values (`pytest -s tests/test_performance.py`).
"""

import random
import time

from app.models.replenishment import ReplenishmentItem, ReplenishmentRequest
from app.solvers.replenishment import budget_curve, solve


def office_sized_request(n_items=200, n_scenarios=12, seed=42) -> ReplenishmentRequest:
    rng = random.Random(seed)
    items = []
    for i in range(n_items):
        base = rng.randint(5, 400)
        history = [max(0, int(rng.gauss(base, base * 0.2))) for _ in range(n_scenarios)]
        history[rng.randrange(n_scenarios)] = base * rng.randint(2, 4)  # one typhoon quarter per item
        items.append(ReplenishmentItem(id=f"item-{i}", q=rng.randint(0, base), unit_cost=rng.choice([25, 80, 150, 350, 500, 900]),
                                       weight=rng.choice([1, 2, 3]), coverage_k=rng.choice([6, 7, 8]), history=history))
    full_cost = sum(it.unit_cost * max(history_max - it.q, 0) for it in items for history_max in [max(it.history)])
    return ReplenishmentRequest(rule="scenario", budget=full_cost * 0.6, items=items)


def timed(fn, *args):
    started = time.perf_counter()
    out = fn(*args)
    return out, time.perf_counter() - started


def test_stage2_solves_200_items_12_scenarios_under_2_seconds():
    res, secs = timed(solve, office_sized_request())
    print(f"\nStage 2, 200 items x 12 scenarios: {secs * 1000:.0f} ms ({res.status})")
    assert res.status == "optimal"
    assert secs < 2


def test_budget_curve_of_11_resolves_under_20_seconds():
    curve, secs = timed(budget_curve, office_sized_request())
    print(f"\n11-point budget curve (+ next P10,000), 200 items x 12 scenarios: {secs:.1f} s")
    assert len(curve["points"]) == 11
    assert secs < 20


def test_stage1_scales_to_200_items():
    req = office_sized_request().model_copy(update={"rule": "per_item_multiplier"})
    res, secs = timed(solve, req)
    print(f"\nStage 1, 200 items: {secs * 1000:.0f} ms")
    assert res.status == "optimal" and secs < 2
