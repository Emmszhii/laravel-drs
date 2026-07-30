import pulp
from app.models.schemas import AllocationRequest, AllocationResponse, AllocationLine
from app.solvers.base import BaseSolver


class PulpAllocationSolver(BaseSolver):
    """
    Minimizes unmet demand across zones, subject to depot capacity.
    This is the v1 allocation algorithm referenced in PROJECT.md / SUMMARY.md —
    document the exact objective function and constraints here for the
    methodology chapter as they're refined.
    """

    def solve(self, payload: AllocationRequest) -> AllocationResponse:
        problem = pulp.LpProblem("resource_allocation", pulp.LpMinimize)

        zones = payload.zones
        depots = payload.depots

        # Decision variables: quantity allocated from depot d to zone z.
        x = {
            (z.id, d.id): pulp.LpVariable(f"x_{z.id}_{d.id}", lowBound=0, cat="Integer")
            for z in zones
            for d in depots
        }

        # Unmet demand per zone (to minimize).
        unmet = {z.id: pulp.LpVariable(f"unmet_{z.id}", lowBound=0) for z in zones}

        problem += pulp.lpSum(unmet.values())  # objective: minimize total unmet demand

        for z in zones:
            problem += (
                pulp.lpSum(x[(z.id, d.id)] for d in depots) + unmet[z.id] == z.demand
            )

        for d in depots:
            if d.capacity > 0:
                problem += pulp.lpSum(x[(z.id, d.id)] for z in zones) <= d.capacity

        problem.solve(pulp.PULP_CBC_CMD(msg=False))

        allocations = [
            AllocationLine(zone_id=z.id, depot_id=d.id, quantity=int(x[(z.id, d.id)].value() or 0))
            for z in zones
            for d in depots
            if (x[(z.id, d.id)].value() or 0) > 0
        ]

        return AllocationResponse(
            algorithm="pulp_linear_programming",
            objective_value=pulp.value(problem.objective) or 0.0,
            allocations=allocations,
        )
