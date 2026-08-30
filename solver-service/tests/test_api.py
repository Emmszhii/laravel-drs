"""
End-to-end HTTP-level tests via FastAPI's TestClient — exercises request
validation, routing, and the module-level singleton solver instances in
app/api/optimize.py, none of which test_solvers.py's direct-solver-class
tests touch.
"""
from fastapi.testclient import TestClient
from main import app

client = TestClient(app)


def test_health_endpoint_returns_ok():
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "ok"}


def test_allocation_endpoint_returns_a_valid_response():
    payload = {
        "disaster_id": "test-disaster-1",
        "zones": [
            {"id": "zone-a", "lat": 13.141, "lng": 123.745, "demand": 100},
        ],
        "depots": [
            {"id": "depot-a", "lat": 13.1449, "lng": 123.7515, "capacity": 150},
        ],
    }

    response = client.post("/api/v1/optimize/allocation", json=payload)

    assert response.status_code == 200
    body = response.json()
    assert body["algorithm"] == "pulp_linear_programming"
    assert body["allocations"] == [{"zone_id": "zone-a", "depot_id": "depot-a", "quantity": 100}]


def test_allocation_endpoint_rejects_a_malformed_payload():
    response = client.post("/api/v1/optimize/allocation", json={"disaster_id": "d-1"})
    assert response.status_code == 422


def test_routing_endpoint_returns_a_valid_response():
    payload = {
        "disaster_id": "test-disaster-1",
        "zones": [
            {"id": "zone-a", "lat": 13.141, "lng": 123.745, "demand": 100},
        ],
        "depots": [
            {"id": "depot-a", "lat": 13.1449, "lng": 123.7515, "capacity": 150},
        ],
        "vehicles": [
            {"id": "vehicle-a", "capacity": 150, "depot_id": "depot-a"},
        ],
        "allocations": [
            {"zone_id": "zone-a", "depot_id": "depot-a", "quantity": 100},
        ],
    }

    response = client.post("/api/v1/optimize/routing", json=payload)

    assert response.status_code == 200
    body = response.json()
    assert body["algorithm"] == "ortools_cvrptw"
    assert len(body["routes"]) == 1
    assert body["routes"][0]["vehicle_id"] == "vehicle-a"


def test_routing_endpoint_rejects_a_malformed_payload():
    response = client.post("/api/v1/optimize/routing", json={"disaster_id": "d-1"})
    assert response.status_code == 422


def test_routing_endpoint_returns_empty_routes_with_no_vehicles():
    payload = {
        "disaster_id": "test-disaster-1",
        "zones": [],
        "depots": [],
        "vehicles": [],
        "allocations": [],
    }

    response = client.post("/api/v1/optimize/routing", json=payload)

    assert response.status_code == 200
    assert response.json()["routes"] == []
