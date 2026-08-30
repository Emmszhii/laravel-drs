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


class VehicleSpec(BaseModel):
    id: str
    capacity: int
    depot_id: str  # home depot — route starts and ends here


class RoutingStop(BaseModel):
    location_id: str  # a zone id, or the vehicle's home depot id for the return leg
    arrival_time: int  # minutes elapsed since the vehicle departed its depot
    quantity: int  # amount delivered at this stop (0 for depot nodes)


class VehicleRoute(BaseModel):
    vehicle_id: str
    stops: list[RoutingStop]


class RoutingRequest(BaseModel):
    disaster_id: str
    zones: list[ZoneDemand]
    depots: list[DepotCapacity]
    vehicles: list[VehicleSpec]
    # Depot->zone delivery quantities decided by the allocation stage
    # (PulpAllocationSolver) — routing distributes these across vehicles
    # instead of re-deciding *how much* goes where.
    allocations: list[AllocationLine]
    # Per-vehicle operating window in minutes from route start (e.g. an
    # 8-hour shift = 480). Applied uniformly — there's no per-zone delivery
    # deadline field anywhere in the schema yet to differentiate further.
    time_window_minutes: int = 480


class RoutingResponse(BaseModel):
    algorithm: str
    objective_value: float
    routes: list[VehicleRoute]
