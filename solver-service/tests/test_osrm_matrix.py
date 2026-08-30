from unittest.mock import patch, MagicMock
import httpx
from app.services.osrm_matrix import in_laguna_bbox, fetch_distance_matrix, LAGUNA_BBOX


def test_in_laguna_bbox_accepts_a_point_inside():
    assert in_laguna_bbox(14.2, 121.3) is True


def test_in_laguna_bbox_rejects_a_point_outside():
    # Albay coordinates (used by the seeder/solver tests) are well outside Laguna.
    assert in_laguna_bbox(13.141, 123.745) is False


def test_fetch_distance_matrix_returns_empty_ok_for_no_locations():
    result = fetch_distance_matrix([])
    assert result == {"ok": True, "distances": [], "durations": []}


def test_fetch_distance_matrix_fails_gracefully_when_osrm_unreachable():
    # No OSRM server is running in the unit-test environment — this must
    # never raise, only report ok=False so callers fall back to haversine.
    inside_bbox_point = ((LAGUNA_BBOX["min_lat"] + LAGUNA_BBOX["max_lat"]) / 2, (LAGUNA_BBOX["min_lng"] + LAGUNA_BBOX["max_lng"]) / 2)
    result = fetch_distance_matrix([inside_bbox_point, inside_bbox_point])
    assert result["ok"] is False
    assert "error" in result


def _mock_response(status_code=200, json_data=None):
    response = MagicMock()
    response.status_code = status_code
    response.json.return_value = json_data or {}
    return response


def test_fetch_distance_matrix_returns_ok_on_a_valid_osrm_response():
    points = [(14.1, 121.05), (14.2, 121.1)]
    osrm_json = {
        "code": "Ok",
        "distances": [[0, 5000], [5000, 0]],  # meters
        "durations": [[0, 600], [600, 0]],  # seconds
    }

    with patch("httpx.get", return_value=_mock_response(200, osrm_json)) as mock_get:
        result = fetch_distance_matrix(points)

    assert mock_get.called
    assert result == {
        "ok": True,
        "distances": [[0, 5.0], [5.0, 0]],  # converted to km
        "durations": [[0, 600], [600, 0]],
    }


def test_fetch_distance_matrix_fails_on_non_200_status():
    points = [(14.1, 121.05), (14.2, 121.1)]

    with patch("httpx.get", return_value=_mock_response(500, {})):
        result = fetch_distance_matrix(points)

    assert result["ok"] is False
    assert "500" in result["error"]


def test_fetch_distance_matrix_fails_when_osrm_code_is_not_ok():
    points = [(14.1, 121.05), (14.2, 121.1)]
    osrm_json = {"code": "InvalidQuery", "message": "Invalid coordinate"}

    with patch("httpx.get", return_value=_mock_response(200, osrm_json)):
        result = fetch_distance_matrix(points)

    assert result["ok"] is False
    assert result["error"] == "Invalid coordinate"


def test_fetch_distance_matrix_fails_when_distances_or_durations_missing():
    points = [(14.1, 121.05), (14.2, 121.1)]
    osrm_json = {"code": "Ok", "distances": None, "durations": None}

    with patch("httpx.get", return_value=_mock_response(200, osrm_json)):
        result = fetch_distance_matrix(points)

    assert result["ok"] is False
    assert "missing" in result["error"]
