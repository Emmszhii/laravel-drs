from pydantic import BaseModel


class ZoneDemand(BaseModel):
    id: str
    lat: float
    lng: float
    demand: int  # population_affected, or unit-resource demand


class DepotCapacity(BaseModel):
    id: str
    lat: float
    lng: float
    capacity: int = 0


class AllocationRequest(BaseModel):
    disaster_id: str
    zones: list[ZoneDemand]
    depots: list[DepotCapacity]


class AllocationLine(BaseModel):
    zone_id: str
    depot_id: str
    quantity: int


class AllocationResponse(BaseModel):
    algorithm: str
    objective_value: float
    allocations: list[AllocationLine]
