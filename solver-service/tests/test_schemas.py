"""
Pydantic validation coverage for app/models/schemas.py — required fields,
defaults, and type coercion. These models are otherwise only ever
instantiated with valid data in test_solvers.py/test_api.py, so the
validation behavior itself was previously unexercised.
"""
import pytest
from pydantic import ValidationError
from app.models.schemas import (
    ZoneDemand,
    DepotCapacity,
    AllocationRequest,
    AllocationLine,
    AllocationResponse,
    VehicleSpec,
    RoutingStop,
    VehicleRoute,
    RoutingRequest,
    RoutingResponse,
)


def test_zone_demand_requires_all_fields():
    with pytest.raises(ValidationError):
        ZoneDemand(id="zone-1", lat=14.1)  # missing lng, demand


def test_zone_demand_coerces_numeric_strings():
    zone = ZoneDemand(id="zone-1", lat="14.1", lng="121.05", demand="50")
    assert zone.lat == 14.1
    assert zone.lng == 121.05
    assert zone.demand == 50


def test_depot_capacity_defaults_capacity_to_zero():
    depot = DepotCapacity(id="depot-1", lat=14.1, lng=121.05)
    assert depot.capacity == 0


def test_allocation_request_requires_zones_and_depots_lists():
    with pytest.raises(ValidationError):
        AllocationRequest(disaster_id="d-1")


def test_allocation_request_accepts_empty_zones_and_depots():
    payload = AllocationRequest(disaster_id="d-1", zones=[], depots=[])
    assert payload.zones == []
    assert payload.depots == []


def test_allocation_line_requires_quantity():
    with pytest.raises(ValidationError):
        AllocationLine(zone_id="z-1", depot_id="d-1")


def test_allocation_response_requires_all_fields():
    with pytest.raises(ValidationError):
        AllocationResponse(algorithm="pulp_linear_programming")


def test_allocation_response_accepts_valid_payload():
    response = AllocationResponse(
        algorithm="pulp_linear_programming",
        objective_value=0.0,
        allocations=[AllocationLine(zone_id="z-1", depot_id="d-1", quantity=10)],
    )
    assert response.allocations[0].quantity == 10


def test_vehicle_spec_requires_capacity_and_depot_id():
    with pytest.raises(ValidationError):
        VehicleSpec(id="v-1")


def test_routing_stop_requires_all_fields():
    with pytest.raises(ValidationError):
        RoutingStop(location_id="loc-1", arrival_time=0)


def test_vehicle_route_requires_stops_list():
    with pytest.raises(ValidationError):
        VehicleRoute(vehicle_id="v-1")


def test_vehicle_route_accepts_empty_stops():
    route = VehicleRoute(vehicle_id="v-1", stops=[])
    assert route.stops == []


def test_routing_request_defaults_time_window_minutes_to_480():
    payload = RoutingRequest(
        disaster_id="d-1",
        zones=[],
        depots=[],
        vehicles=[],
        allocations=[],
    )
    assert payload.time_window_minutes == 480


def test_routing_request_accepts_explicit_time_window_minutes():
    payload = RoutingRequest(
        disaster_id="d-1",
        zones=[],
        depots=[],
        vehicles=[],
        allocations=[],
        time_window_minutes=240,
    )
    assert payload.time_window_minutes == 240


def test_routing_response_requires_all_fields():
    with pytest.raises(ValidationError):
        RoutingResponse(algorithm="ortools_cvrptw")


def test_routing_response_accepts_valid_payload():
    response = RoutingResponse(algorithm="ortools_cvrptw", objective_value=1.5, routes=[])
    assert response.routes == []
