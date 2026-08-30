"""
Abstract-contract tests for BaseSolver/BaseRoutingSolver — neither should be
directly instantiable, and a subclass that doesn't implement solve() should
also fail to instantiate.
"""
import pytest
from app.solvers.base import BaseSolver, BaseRoutingSolver


def test_base_solver_cannot_be_instantiated_directly():
    with pytest.raises(TypeError):
        BaseSolver()


def test_base_routing_solver_cannot_be_instantiated_directly():
    with pytest.raises(TypeError):
        BaseRoutingSolver()


def test_incomplete_solver_subclass_cannot_be_instantiated():
    class IncompleteSolver(BaseSolver):
        pass

    with pytest.raises(TypeError):
        IncompleteSolver()


def test_incomplete_routing_solver_subclass_cannot_be_instantiated():
    class IncompleteRoutingSolver(BaseRoutingSolver):
        pass

    with pytest.raises(TypeError):
        IncompleteRoutingSolver()


def test_solver_subclass_implementing_solve_can_be_instantiated():
    class ConcreteSolver(BaseSolver):
        def solve(self, payload):
            return None

    ConcreteSolver()  # should not raise
