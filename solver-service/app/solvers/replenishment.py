"""
Budget-limited quarterly replenishment (Chapter III, Model Building).

Stage 1 — per-item multiplier: each item's target is its covering level L_i,
the use that would have covered k of its last M quarters. The MILP picks
whole-number orders x_i that minimize the weighted shortfall below target,
    min  sum_i w_i * u_i / T_i
    s.t. u_i >= T_i - (q_i + x_i)
         sum_i c_i * x_i <= B
         sum_i v_i * x_i <= V        (V = depot space still free after the stock on hand;
                                      only when space is modeled)
         0 <= x_i <= max(0, T_i - q_i), x_i integer, u_i >= 0
Since x = 0 satisfies both constraints, the model is always feasible.

Stage 2 — scenario model (sample average approximation): every past quarter
s of item i is an equally likely version of the next one, with use D_is,
    min  sum_i (1/S_i) * sum_s w_i * u_is / m_i
    s.t. u_is >= D_is - (q_i + x_i)
         the same budget and space constraints, x_i >= 0 integer.
Typhoon quarters stay in as their own scenarios instead of being averaged away.
The objective is separable per item, so each item uses the S_i quarters it has
(a new item's missing quarters are not zeros). An item with fewer than 4
quarters uses its cold-start flat 1.5 target as its single scenario.

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
STOPPED_EARLY = "partly covered: the solver stopped early"
NO_FEASIBLE = "no feasible order"

MIN_QUARTERS = 4  # below this, the cold-start flat 1.5 rule applies
# CBC stops once it has proven the order is within 0.01% of the best possible. Proving the last
# fraction costs ~20x the time on an office-sized Stage 2 (10 s vs 0.56 s for 200 items x 12
# scenarios) and changes the expected shortage by < 0.004%. "optimal" means optimal within this gap.
MIP_GAP = 1e-4
FLAT_FACTOR = 1.5
NEXT_STEP = 10_000
EPS = 1e-6


def covering_level(history: list[int], k: int, m: int = 8) -> int:
    """
    Use that would have covered k of the last m quarters: the (m-k+1)-th highest. With only
    n < m quarters, k is scaled to the same share of the n, rounded up: the (n-k'+1)-th highest.
    """
    recent = sorted(history[-m:], reverse=True)
    n = len(recent)
    k_eff = min(n, -(-k * n // m))  # ceil(k * n / m); equals k when n == m
    return recent[n - k_eff]


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


def _line(it, xi, target, short, reason, basis, multiplier) -> OrderLine:
    return OrderLine(id=it.id, x=xi, target=target, shortfall=short,
                     shortfall_pct=round(100 * short / target, 1) if target else 0,
                     reason=reason, basis=basis, multiplier=multiplier)


def solve(req: ReplenishmentRequest) -> ReplenishmentResponse:
    started = time.perf_counter()
    scenario = req.rule == "scenario"

    plan = []  # (item, target, basis, multiplier, q)
    lines: dict[str, OrderLine] = {}
    for item in req.items:
        target, basis, multiplier = item_target(item, req.m)
        if scenario and target is not None and not any(item.history):
            target, basis, multiplier = None, "no_history", None  # no past quarters to plan against
        q = max(0, item.q - item.reserved)
        if target is not None and item.unit_cost is None:
            basis = "no_unit_cost"
        if target is None or item.unit_cost is None:
            covered = target is not None and q >= target
            lines[item.id] = _line(item, 0, target, 0, STOCK_COVERS if covered else SET_MANUALLY, basis, multiplier)
            continue
        plan.append((item, target, basis, multiplier, q))

    space_modeled = req.free_space is not None and bool(plan) and all(
        it.space_per_unit is not None for it, *_ in plan)

    # Scenarios per item: its own quarters, or its cold-start target when it has fewer than 4.
    D = {it.id: (it.history if len(it.history) >= MIN_QUARTERS else [target])
         for it, target, *_ in plan} if scenario else {}
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
                    terms.append(it.weight * u / (len(D[it.id]) * avg_use[it.id]))
        else:
            u = pulp.LpVariable(f"u_{len(x)}", 0)
            prob += u >= target - (q + x[it.id])
            terms.append(it.weight * u / target)
    prob += pulp.lpSum(terms)
    prob += pulp.lpSum(it.unit_cost * x[it.id] for it, *_ in plan if it.id in x) <= req.budget
    if space_modeled:
        prob += pulp.lpSum(it.space_per_unit * x[it.id] for it, *_ in plan if it.id in x) <= req.free_space

    status = "optimal"
    if x:
        prob.solve(pulp.PULP_CBC_CMD(msg=False, timeLimit=req.time_limit_s, gapRel=MIP_GAP))
        if prob.status == pulp.LpStatusInfeasible or prob.sol_status == pulp.LpSolutionInfeasible:
            status = "infeasible"
        elif (prob.sol_status not in (pulp.LpSolutionOptimal, pulp.LpSolutionIntegerFeasible)
              or any(v.varValue is None for v in x.values())):
            status = "not_solved"  # e.g. the time limit hit before CBC found any order
        elif prob.status != pulp.LpStatusOptimal or prob.sol_status == pulp.LpSolutionIntegerFeasible:
            status = "feasible"  # stopped at the time limit with an incumbent, not a proven optimum

    if status in ("infeasible", "not_solved"):
        for it, target, basis, multiplier, q in plan:
            lines[it.id] = _line(it, 0, target, max(0, target - q), NO_FEASIBLE, basis, multiplier)
        return ReplenishmentResponse(status=status, rule=req.rule, orders=[lines[it.id] for it in req.items],
                                     objective=None, spent=0, binding={"budget": False, "space": False},
                                     space_modeled=space_modeled, solve_ms=_ms(started))

    order = {i: int(round(v.varValue)) for i, v in x.items()}
    spent = sum(it.unit_cost * order.get(it.id, 0) for it, *_ in plan)
    left_budget = req.budget - spent
    left_space = (req.free_space - sum(it.space_per_unit * order.get(it.id, 0) for it, *_ in plan)
                  if space_modeled else math.inf)

    def no_money(it):  # one more unit would not fit the budget left
        return left_budget < it.unit_cost - EPS

    def no_room(it):  # one more unit would not fit the depot space left
        return space_modeled and left_space < it.space_per_unit - EPS

    improvable = [it for it, _, _, _, q in plan if q + order.get(it.id, 0) < cap[it.id]]
    budget_binding = any(no_money(it) for it in improvable)
    space_binding = any(no_room(it) for it in improvable)

    objective = 0.0
    for it, target, basis, multiplier, q in plan:
        xi = order.get(it.id, 0)
        short = max(0, target - q - xi)
        if short == 0:
            reason = FULLY_COVERED if xi > 0 else STOCK_COVERS
        elif no_money(it):
            reason = BUDGET_RAN_OUT
        elif no_room(it):
            reason = SPACE_LIMITED
        else:
            reason = STOPPED_EARLY  # neither constraint blocks one more unit: the solve ended at its limit/gap
        if scenario:
            objective += sum(it.weight * max(0, use - q - xi) / avg_use[it.id] for use in D[it.id]) / len(D[it.id])
        else:
            objective += it.weight * short / target if target else 0
        lines[it.id] = _line(it, xi, target, short, reason, basis, multiplier)

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

    Each point carries its own solve's raw `objective` and the plotted
    `expected_shortage`, the best known shortage at or below that budget. A level
    with no order (`solved` false) has both as None and is skipped in the carry-forward.
    """
    points = []
    best = None
    for pct in range(50, 151, 10):
        budget = req.budget * pct / 100
        res = solve(req.model_copy(update={"budget": budget}))
        solved = res.objective is not None
        # Within the MIP gap, a larger budget's solve can land a hair above a smaller one's. The
        # smaller budget's order is still affordable at the larger budget, so the best known
        # shortage at this level is never worse than the level before it.
        if solved:
            best = res.objective if best is None else min(best, res.objective)
        points.append({"pct": pct, "budget": round(budget, 2), "expected_shortage": best if solved else None,
                       "objective": res.objective, "status": res.status, "solved": solved})

    base = solve(req)
    more = solve(req.model_copy(update={"budget": req.budget + NEXT_STEP}))
    solved = base.objective is not None and more.objective is not None
    changes = [{"id": a.id, "delta_x": b.x - a.x, "shortfall_pct_before": a.shortfall_pct, "shortfall_pct_after": b.shortfall_pct}
               for a, b in zip(base.orders, more.orders) if b.x != a.x] if solved else []

    return {"points": points, "next_step": {"amount": NEXT_STEP, "changes": changes, "solved": solved}}


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
    past decisions. Histories end at the same latest quarter; an item with a
    shorter one did not exist before it starts, so its missing quarters are
    neither history nor use.
    """
    T = max(len(it.history) for it in req.items)
    off = {it.id: T - len(it.history) for it in req.items}
    use = {it.id: [0] * off[it.id] + it.history for it in req.items}  # actual use by calendar quarter
    space_modeled = req.free_space is not None and all(it.space_per_unit is not None for it in req.items)
    rules = ["flat_1_5", "per_item_multiplier", "scenario"] + (["office_actual"] if req.actual_orders else [])
    stock = {r: {it.id: req.initial_stock.get(it.id, 0) for it in req.items} for r in rules}
    quarters = []

    for t in range(req.start, T):
        budget = req.budgets[t] if t < len(req.budgets) and req.budgets[t] is not None else req.budget
        past = [it.model_copy(update={"history": use[it.id][off[it.id]:t], "q": 0, "reserved": 0, "target": None})
                for it in req.items]
        # ponytail: an item with no past use divides by 1, not its (zero) average.
        avg = {p.id: sum(p.history) / len(p.history) if sum(p.history) else 1 for p in past}
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
                orders, ms, feasible = {o.id: o.x for o in res.orders}, res.solve_ms, res.objective is not None

            spent = sum((it.unit_cost or 0) * orders.get(it.id, 0) for it in req.items)
            space = sum(it.space_per_unit * orders.get(it.id, 0) for it in req.items) if space_modeled else 0
            feasible = feasible and spent <= budget + EPS and (not space_modeled or space <= req.free_space + EPS)
            row["rules"][rule] = _score(req.items, use, avg, t, q, orders, budget, spent, ms, feasible)
            stock[rule] = {it.id: max(0, q[it.id] + orders.get(it.id, 0) - use[it.id][t]) for it in req.items}

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


def _score(items, use, avg, t, q, orders, budget, spent, ms, feasible) -> dict:
    fill_num = fill_den = ws = ran_out = leftover = 0.0
    for it in items:
        d, have = use[it.id][t], q[it.id] + orders.get(it.id, 0)
        fill_num += min(d, have)
        fill_den += d
        ws += it.weight * max(0, d - have) / avg[it.id]
        ran_out += d > have
        leftover += max(0, have - d) * (it.unit_cost or 0)

    return {
        "fill_rate": round(fill_num / fill_den, 4) if fill_den else 1.0,
        "weighted_shortage": round(ws, 4),
        "items_ran_out": int(ran_out),
        "budget_used": round(spent / budget, 4) if budget else 0.0,
        "stock_left_value": round(leftover, 2),
        "feasible": feasible,
        "solve_ms": ms,
    }


def _ms(started: float) -> int:
    return int((time.perf_counter() - started) * 1000)
