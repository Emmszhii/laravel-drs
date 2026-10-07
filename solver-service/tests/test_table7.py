"""
Table 7 (Chapter III, Worked Example): in-sample expected shortage of the three rules on the
illustrative eight-quarter history, so the paper's numbers can be regenerated. Hygiene and medical kits
are steady, food packs and water swing, and the sixth quarter is a typhoon quarter. Medical kits are
Vital (weight 3, covers 8 of 8 quarters); the others are Essential (weight 2, 7 of 8), as in Table 4.
"""

from app.models.replenishment import ReplenishmentItem, ReplenishmentRequest
from app.solvers.replenishment import flat_rule_orders, item_target, solve

HISTORY = {
    "food": [230, 250, 220, 240, 260, 640, 300, 420],       # avg 320
    "hygiene": [190, 210, 195, 205, 200, 230, 185, 185],    # avg 200
    "water": [300, 340, 360, 330, 520, 860, 280, 210],      # avg 400
    "medical": [80, 85, 90, 82, 88, 95, 84, 76],            # avg 85
}
STOCK = {"food": 150, "hygiene": 60, "water": 250, "medical": 200}  # Table 5
COST = {"food": 500, "hygiene": 350, "water": 150, "medical": 900}
VED = {"food": (2, 7), "hygiene": (2, 7), "water": (2, 7), "medical": (3, 8)}  # (weight, k)

# budget: (flat 1.5, per-item multiplier, scenario model), as printed in Table 7
TABLE_7 = {
    150_000: (0.88, 0.68, 0.68),
    200_000: (0.53, 0.50, 0.46),
    250_000: (0.37, 0.41, 0.28),
    300_000: (0.29, 0.41, 0.15),
    350_000: (0.29, 0.41, 0.07),
}


def items():
    return [ReplenishmentItem(id=i, q=STOCK[i], unit_cost=COST[i], weight=VED[i][0], coverage_k=VED[i][1],
                              history=HISTORY[i]) for i in HISTORY]


def expected_shortage(orders):
    """Weighted shortage in quarters of average use, summed over items, averaged over the eight quarters."""
    total = 0.0
    for it in items():
        avg = sum(it.history) / len(it.history)
        short = sum(max(0, d - it.q - orders.get(it.id, 0)) for d in it.history) / len(it.history)
        total += it.weight * short / avg
    return total


def test_averages_match_table_5():
    assert [sum(h) / len(h) for h in HISTORY.values()] == [320, 200, 400, 85]


def test_per_item_multipliers_in_the_table_note():
    assert [round(item_target(it, 8)[2], 2) for it in items()] == [1.31, 1.05, 1.30, 1.12]


def test_table_7_values_and_the_sentences_under_it():
    for budget, expected in TABLE_7.items():
        flat = expected_shortage(flat_rule_orders(items(), STOCK, budget)[0])
        mult = expected_shortage({o.id: o.x for o in solve(ReplenishmentRequest(budget=budget, items=items())).orders})
        scen = solve(ReplenishmentRequest(rule="scenario", budget=budget, items=items()))
        assert (round(flat, 2), round(mult, 2), round(scen.objective, 2)) == expected
        assert scen.objective <= min(flat, mult) + 1e-3  # the scenario model is least (or equal-least) everywhere
        assert (mult < flat) == (budget <= 200_000)  # the multiplier wins only when money is tight
