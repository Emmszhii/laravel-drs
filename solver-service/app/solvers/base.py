from abc import ABC, abstractmethod
from app.models.schemas import AllocationRequest, AllocationResponse


class BaseSolver(ABC):
    """
    All solvers (allocation, routing) implement this interface so
    `api/optimize.py` can swap implementations without touching route code.
    """

    @abstractmethod
    def solve(self, payload: AllocationRequest) -> AllocationResponse:
        raise NotImplementedError
