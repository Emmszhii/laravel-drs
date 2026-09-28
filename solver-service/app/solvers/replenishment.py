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
and every item gets one reason read from the solution, never written by hand.
"""

import math
import time

import pulp

from app.models.replenishment import OrderLine, ReplenishmentItem, ReplenishmentRequest, ReplenishmentResponse

FULLY_COVERED = "fully covered"
BUDGET_RAN_OUT = "partly covered: the budget ran out"
SPACE_LIMITED = "limited by depot space"
STOCK_COVERS = "not ordered: current stock already covers it"
SET_MANUALLY = "no computed order: set manually"

MIN_QUARTERS = 4  # below this, the cold-start flat 1.5 rule applies
FLAT_FACTOR = 1.5


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

    plan = []  # (item, target, basis, multiplier, q)
    lines: dict[str, OrderLine] = {}
    for item in req.items:
        target, basis, multiplier = item_target(item, req.m)
        if target is not None and item.unit_cost is None:
            basis = "no_unit_cost"
        if target is None or item.unit_cost is None:
            lines[item.id] = OrderLine(id=item.id, x=0, target=target, shortfall=0, shortfall_pct=0,
                                       reason=SET_MANUALLY, basis=basis, multiplier=multiplier)
            continue
        plan.append((item, target, basis, multiplier, max(0, item.q - item.reserved)))

    space_modeled = req.free_space is not None and bool(plan) and all(
        it.space_per_unit is not None for it, *_ in plan)

    prob = pulp.LpProblem("stage1_per_item_multiplier", pulp.LpMinimize)
    x, u = {}, {}
    for it, target, _, _, q in plan:
        if target <= q:
            continue  # stock already covers it; nothing to decide
        x[it.id] = pulp.LpVariable(f"x_{len(x)}", 0, target - q, cat="Integer")
        u[it.id] = pulp.LpVariable(f"u_{len(u)}", 0)
        prob += u[it.id] >= target - (q + x[it.id])
    prob += pulp.lpSum(it.weight * u[it.id] / target for it, target, *_ in plan if it.id in u)
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
    under = [(it, q) for it, target, _, _, q in plan if q + order.get(it.id, 0) < target]
    budget_binding = bool(under) and req.budget - spent < min(it.unit_cost for it, _ in under)
    space_binding = False
    if space_modeled and under:
        used = sum(it.space_per_unit * (q + order.get(it.id, 0)) for it, _, _, _, q in plan)
        space_binding = req.free_space - used < min(it.space_per_unit for it, _ in under)

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


NEXT_STEP = 10_000


def _ms(started: float) -> int:
    return int((time.perf_counter() - started) * 1000)
