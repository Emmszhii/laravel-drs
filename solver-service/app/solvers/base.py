from abc import ABC, abstractmethod
from app.models.schemas import AllocationRequest, AllocationResponse, RoutingRequest, RoutingResponse


class BaseSolver(ABC):
    """
    Allocation solvers (decide *how much* each depot ships to each zone)
    implement this interface so `api/optimize.py` can swap implementations
    without touching route code.
    """

    @abstractmethod
    def solve(self, payload: AllocationRequest) -> AllocationResponse:
        raise NotImplementedError


class BaseRoutingSolver(ABC):
    """
    Routing solvers (decide vehicle stop order under capacity/time-window
    constraints, given allocation-stage quantities) implement this interface.
    Deliberately separate from `BaseSolver` — routing's request/response
    shape (vehicles, stops, time windows) is not the allocation shape.
    """

    @abstractmethod
    def solve(self, payload: RoutingRequest) -> RoutingResponse:
        raise NotImplementedError
