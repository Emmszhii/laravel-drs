"""
Budget-limited quarterly replenishment (Chapter III, Model Building).

Stage 1 — per-item multiplier: each item's target is its covering level L_i,
the use that would have covered k of its last M quarters. The MILP picks
whole-number orders x_i that minimize the weighted shortfall below target,
    min  sum_i w_i * u_i / T_i
    s.t. u_i >= T_i - (q_i + x_i)
         sum_i c_i * x_i <= B
         sum_i v_i * (q_i + x_i) <= V        (only when space is modeled)
         0 <= x_i <= max(0, T_i - q_i), x_i integer, u_i >= 0

Stage 2 — scenario model (sample average approximation): every past quarter
s = 1..S is an equally likely version of the next one, with use D_is,
    min  (1/S) * sum_s sum_i w_i * u_is / m_i
    s.t. u_is >= D_is - (q_i + x_i)
         the same budget and space constraints, x_i >= 0 integer.
Typhoon quarters stay in as their own scenarios instead of being averaged away.

Either way, every item gets one reason read from the solution, never
written by hand, measured against its covering-level target.
"""

import math
import time

import pulp

from app.models.replenishment import (
    BacktestRequest, OrderLine, ReplenishmentItem, ReplenishmentRequest, ReplenishmentResponse,
)

FULLY_COVERED = "fully covered"
BUDGET_RAN_OUT = "partly covered: the budget ran out"
SPACE_LIMITED = "limited by depot space"
STOCK_COVERS = "not ordered: current stock already covers it"
SET_MANUALLY = "no computed order: set manually"

MIN_QUARTERS = 4  # below this, the cold-start flat 1.5 rule applies
FLAT_FACTOR = 1.5
NEXT_STEP = 10_000


def covering_level(history: list[int], k: int, m: int = 8) -> int:
    """Use that would have covered k of the last m quarters: the (m-k+1)-th highest."""
    recent = sorted(history[-m:], reverse=True)
    return recent[min(max(m - k, 0), len(recent) - 1)]


def item_target(item: ReplenishmentItem, m: int) -> tuple[int | None, str, float | None]:
    """(target, basis, multiplier). Target None means the item is listed for Logistics to set."""
    if item.target is not None:
        return item.target, "given", None
    if not item.history or sum(item.history) == 0:
        return None, "no_history", None
    avg = sum(item.history) / len(item.history)
    if len(item.history) < MIN_QUARTERS:
        return math.ceil(FLAT_FACTOR * avg), "limited_history", FLAT_FACTOR
    level = covering_level(item.history, item.coverage_k, m)
    return level, "multiplier", round(level / avg, 4)


def solve(req: ReplenishmentRequest) -> ReplenishmentResponse:
    started = time.perf_counter()
    scenario = req.rule == "scenario"

    plan = []  # (item, target, basis, multiplier, q)
    lines: dict[str, OrderLine] = {}
    for item in req.items:
        target, basis, multiplier = item_target(item, req.m)
        if scenario and target is not None and not any(item.history):
            target, basis, multiplier = None, "no_history", None  # no past quarters to plan against
        if target is not None and item.unit_cost is None:
            basis = "no_unit_cost"
        if target is None or item.unit_cost is None:
            lines[item.id] = OrderLine(id=item.id, x=0, target=target, shortfall=0, shortfall_pct=0,
                                       reason=SET_MANUALLY, basis=basis, multiplier=multiplier)
            continue
        plan.append((item, target, basis, multiplier, max(0, item.q - item.reserved)))

    space_modeled = req.free_space is not None and bool(plan) and all(
        it.space_per_unit is not None for it, *_ in plan)

    # Scenario matrix: every item's history left-padded to the same S quarters.
    S = max((len(it.history) for it, *_ in plan), default=0) if scenario else 0
    D = {it.id: [0] * (S - len(it.history)) + it.history for it, *_ in plan} if scenario else {}
    avg_use = {it.id: sum(it.history) / len(it.history) for it, *_ in plan} if scenario else {}
    # Upper bound per item: ordering past it can't reduce shortage any further.
    cap = {it.id: (max(D[it.id]) if scenario else target) for it, target, *_ in plan}

    prob = pulp.LpProblem("stage2_scenario" if scenario else "stage1_per_item_multiplier", pulp.LpMinimize)
    x, terms = {}, []
    for it, target, _, _, q in plan:
        if cap[it.id] <= q:
            continue  # stock already covers every case; nothing to decide
        x[it.id] = pulp.LpVariable(f"x_{len(x)}", 0, cap[it.id] - q, cat="Integer")
        if scenario:
            for s, use in enumerate(D[it.id]):
                if use > q:
                    u = pulp.LpVariable(f"u_{len(x)}_{s}", 0)
                    prob += u >= use - (q + x[it.id])
                    terms.append(it.weight * u / (S * avg_use[it.id]))
        else:
            u = pulp.LpVariable(f"u_{len(x)}", 0)
            prob += u >= target - (q + x[it.id])
            terms.append(it.weight * u / target)
    prob += pulp.lpSum(terms)
    prob += pulp.lpSum(it.unit_cost * x[it.id] for it, *_ in plan if it.id in x) <= req.budget
    if space_modeled:
        prob += pulp.lpSum(it.space_per_unit * (q + x.get(it.id, 0)) for it, _, _, _, q in plan) <= req.free_space

    status = "optimal"
    if x:
        prob.solve(pulp.PULP_CBC_CMD(msg=False, timeLimit=req.time_limit_s))
        if prob.status == pulp.LpStatusInfeasible or any(v.varValue is None for v in x.values()):
            status = "infeasible"
        elif prob.status != pulp.LpStatusOptimal:
            status = "feasible"  # stopped at the time limit with an incumbent
    elif space_modeled and sum(it.space_per_unit * q for it, _, _, _, q in plan) > req.free_space:
        status = "infeasible"

    if status == "infeasible":
        return ReplenishmentResponse(status=status, rule=req.rule, orders=list(lines.values()), objective=0,
                                     spent=0, binding={"budget": False, "space": False},
                                     space_modeled=space_modeled, solve_ms=_ms(started))

    order = {i: int(round(v.varValue)) for i, v in x.items()}
    spent = sum(it.unit_cost * order.get(it.id, 0) for it, *_ in plan)
    improvable = [it for it, _, _, _, q in plan if q + order.get(it.id, 0) < cap[it.id]]
    budget_binding = bool(improvable) and req.budget - spent < min(it.unit_cost for it in improvable)
    space_binding = False
    if space_modeled and improvable:
        used = sum(it.space_per_unit * (q + order.get(it.id, 0)) for it, _, _, _, q in plan)
        space_binding = req.free_space - used < min(it.space_per_unit for it in improvable)

    objective = 0.0
    for it, target, basis, multiplier, q in plan:
        xi = order.get(it.id, 0)
        short = max(0, target - q - xi)
        if short == 0:
            reason = FULLY_COVERED if xi > 0 else STOCK_COVERS
        elif space_binding and not budget_binding:
            reason = SPACE_LIMITED
        else:
            reason = BUDGET_RAN_OUT
        if scenario:
            objective += sum(it.weight * max(0, use - q - xi) / avg_use[it.id] for use in D[it.id]) / S
        else:
            objective += it.weight * short / target if target else 0
        lines[it.id] = OrderLine(id=it.id, x=xi, target=target, shortfall=short,
                                 shortfall_pct=round(100 * short / target, 1) if target else 0,
                                 reason=reason, basis=basis, multiplier=multiplier)

    return ReplenishmentResponse(
        status=status, rule=req.rule, orders=[lines[it.id] for it in req.items],
        objective=round(objective, 6), spent=round(spent, 2),
        binding={"budget": budget_binding, "space": space_binding},
        space_modeled=space_modeled, solve_ms=_ms(started),
    )


def budget_curve(req: ReplenishmentRequest) -> dict:
    """
    Re-solves at 11 budget levels, 50%-150% of the entered budget in 10% steps
    (the expected shortage can never rise as the budget rises, since a larger
    budget can always buy the smaller budget's orders), and diffs the solution
    at B against B + P10,000 for the "next P10,000 goes to" view.
    """
    points = []
    for pct in range(50, 151, 10):
        budget = req.budget * pct / 100
        res = solve(req.model_copy(update={"budget": budget}))
        points.append({"pct": pct, "budget": round(budget, 2), "expected_shortage": res.objective, "status": res.status})

    base = solve(req)
    more = solve(req.model_copy(update={"budget": req.budget + NEXT_STEP}))
    next_step = [{"id": a.id, "delta_x": b.x - a.x, "shortfall_pct_before": a.shortfall_pct, "shortfall_pct_after": b.shortfall_pct}
                 for a, b in zip(base.orders, more.orders) if b.x != a.x]

    return {"points": points, "next_step": {"amount": NEXT_STEP, "changes": next_step}}


def flat_rule_orders(items: list[ReplenishmentItem], q: dict[str, int], budget: float) -> tuple[dict[str, int], int]:
    """The office's flat 1.5 rule, cut by the same percentage for every item to fit the budget."""
    started = time.perf_counter()
    want = {}
    for it in items:
        if it.unit_cost is None or not it.history:
            continue
        target = math.ceil(FLAT_FACTOR * sum(it.history) / len(it.history))
        want[it.id] = max(0, target - q[it.id])
    cost = {it.id: it.unit_cost for it in items if it.id in want}
    total = sum(cost[i] * n for i, n in want.items())
    factor = min(1.0, budget / total) if total else 1.0

    return {i: math.floor(n * factor) for i, n in want.items()}, _ms(started)


def backtest(req: BacktestRequest) -> dict:
    """
    Rolling-origin backtest: for each quarter from the 5th encoded one onward,
    every rule builds its order using only earlier quarters, and the order is
    scored against that quarter's actual use. Each rule carries its own stock
    forward (q_{t+1} = max(0, q_t + x_t - D_t)), so a rule lives with its own
    past decisions.
    """
    T = max(len(it.history) for it in req.items)
    hist = {it.id: [0] * (T - len(it.history)) + it.history for it in req.items}
    rules = ["flat_1_5", "per_item_multiplier", "scenario"] + (["office_actual"] if req.actual_orders else [])
    stock = {r: {it.id: req.initial_stock.get(it.id, 0) for it in req.items} for r in rules}
    quarters = []

    for t in range(req.start, T):
        budget = req.budgets[t] if t < len(req.budgets) and req.budgets[t] is not None else req.budget
        past = [it.model_copy(update={"history": hist[it.id][:t], "q": 0, "reserved": 0, "target": None}) for it in req.items]
        row = {"t": t, "budget": budget, "typhoon": t in req.typhoon_quarters, "stockout": t in req.stockout_quarters, "rules": {}}

        for rule in rules:
            q = stock[rule]
            if rule == "flat_1_5":
                orders, ms, feasible = *flat_rule_orders(past, q, budget), True
            elif rule == "office_actual":
                orders, ms, feasible = req.actual_orders.get(str(t), {}), 0, True
            else:
                res = solve(ReplenishmentRequest(rule=rule, budget=budget, free_space=req.free_space, m=req.m,
                                                 time_limit_s=req.time_limit_s,
                                                 items=[p.model_copy(update={"q": q[p.id]}) for p in past]))
                orders, ms, feasible = {o.id: o.x for o in res.orders}, res.solve_ms, res.status != "infeasible"

            spent = sum((it.unit_cost or 0) * orders.get(it.id, 0) for it in req.items)
            row["rules"][rule] = _score(req.items, hist, t, q, orders, budget, spent, ms, feasible)
            stock[rule] = {it.id: max(0, q[it.id] + orders.get(it.id, 0) - hist[it.id][t]) for it in req.items}

        quarters.append(row)

    ws = lambda r, row: row["rules"][r]["weighted_shortage"]  # noqa: E731
    wins = {r: sum(1 for row in quarters if ws(r, row) < ws("flat_1_5", row)) for r in ("per_item_multiplier", "scenario")}
    n = len(quarters)

    return {
        "quarters": quarters,
        "summary": {
            "backtested_quarters": n,
            "wins_vs_flat": wins,
            "target_met": {r: n > 0 and wins[r] > n / 2 for r in wins},
        },
    }


def _score(items, hist, t, q, orders, budget, spent, ms, feasible) -> dict:
    fill_num = fill_den = ws = ran_out = leftover = 0.0
    for it in items:
        d, have = hist[it.id][t], q[it.id] + orders.get(it.id, 0)
        past = hist[it.id][:t]
        # ponytail: an item with no past use divides by 1, not its (zero) average.
        avg = (sum(past) / len(past)) if past and sum(past) else 1
        fill_num += min(d, have)
        fill_den += d
        ws += it.weight * max(0, d - have) / avg
        ran_out += d > have
        leftover += max(0, have - d) * (it.unit_cost or 0)

    return {
        "fill_rate": round(fill_num / fill_den, 4) if fill_den else 1.0,
        "weighted_shortage": round(ws, 4),
        "items_ran_out": int(ran_out),
        "budget_used": round(spent / budget, 4) if budget else 0.0,
        "stock_left_value": round(leftover, 2),
        "feasible": feasible and spent <= budget + 1e-6,
        "solve_ms": ms,
    }


def _ms(started: float) -> int:
    return int((time.perf_counter() - started) * 1000)
