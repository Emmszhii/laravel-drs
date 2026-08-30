"""
OSRM /table (many-to-many matrix) client — supplies real road-network
distances to ORToolsRoutingSolver in place of straight-line haversine.

Never raises: every function returns a plain dict with an `ok` flag, mirroring
OsrmDirectionsProvider's PHP-side `['ok' => bool, ...]` convention, so callers
fall back to haversine on any failure instead of the optimization run erroring.
"""
import httpx
from app.core.config import settings

# Must match docker/osrm/prepare/entrypoint.sh's LAGUNA_BBOX — the OSM extract
# OSRM is preprocessed from only covers this box, so a /table call for
# coordinates outside it returns unusable (None) distances.
LAGUNA_BBOX = {"min_lat": 13.95, "max_lat": 14.55, "min_lng": 120.95, "max_lng": 121.70}


def in_laguna_bbox(lat: float, lng: float) -> bool:
    return (
        LAGUNA_BBOX["min_lat"] <= lat <= LAGUNA_BBOX["max_lat"]
        and LAGUNA_BBOX["min_lng"] <= lng <= LAGUNA_BBOX["max_lng"]
    )


def fetch_distance_matrix(locations: list[tuple[float, float]]) -> dict:
    """
    locations: list of (lat, lng) pairs. Returns
    {"ok": True, "distances": [[km, ...], ...], "durations": [[seconds, ...], ...]}
    (both NxN, same order as `locations`) or {"ok": False, "error": str}.
    `durations` come straight from OSRM (real road-network travel time) —
    used for CVRPTW time windows instead of an assumed average speed.
    """
    if not locations:
        return {"ok": True, "distances": [], "durations": []}

    coords = ";".join(f"{lng},{lat}" for lat, lng in locations)
    url = f"{settings.osrm_url}/table/v1/driving/{coords}"

    try:
        response = httpx.get(url, params={"annotations": "distance,duration"}, timeout=10.0)
    except httpx.HTTPError as e:
        return {"ok": False, "error": str(e)}

    if response.status_code != 200:
        return {"ok": False, "error": f"OSRM /table returned HTTP {response.status_code}"}

    data = response.json()
    if data.get("code") != "Ok":
        return {"ok": False, "error": data.get("message", data.get("code", "unknown OSRM error"))}

    distances = data.get("distances")
    durations = data.get("durations")
    if distances is None or durations is None:
        return {"ok": False, "error": "OSRM /table response missing distances/durations"}

    # OSRM returns meters/seconds; convert distance to km to match the
    # existing haversine unit. Durations are already seconds.
    return {
        "ok": True,
        "distances": [[d / 1000 for d in row] for row in distances],
        "durations": durations,
    }
