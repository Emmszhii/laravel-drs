import { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import useDirections from "@/Hooks/useDirections";
import useWatchPosition from "@/Hooks/useWatchPosition";

// Leaflet's default Icon hardcodes root-relative image paths, which break
// once Vite bundles/hashes this file — point it at the bundler-resolved URLs
// instead.
delete (L.Icon.Default.prototype as unknown as { _getIconUrl?: unknown })
  ._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

type Incident = {
  id: string;
  name: string;
  lat: number;
  lng: number;
  signal: number;
};
type Depot = { id: string; name: string; lat: number; lng: number };

type DispatchMarker = {
  id: string;
  vehicle_label?: string | null;
  status: string;
  current_lat: number | null;
  current_lng: number | null;
};

/** [lng, lat] pairs — matches the GeoJSON order returned by GET /api/directions. */
type RouteGeometry = [number, number][];

type Props = {
  incidents: Incident[];
  depots: Depot[];
  dispatches?: DispatchMarker[];
  onSelectIncident?: (id: string) => void;
  route?: RouteGeometry | null;
  selectedIncidentId?: string;
  onMapClick?: (lat: number, lng: number) => void;
};

const PH_CENTER: [number, number] = [12.8797, 121.774];

// Mapbox's congestion annotation values, per segment — see
// PremiumDirectionsProvider::normalizeRoute(). "unknown" also covers a
// segment value this map doesn't recognize.
const CONGESTION_COLORS: Record<string, string> = {
  low: "#3FA98C",
  moderate: "#E8C468",
  heavy: "#E8843F",
  severe: "#E14B3F",
  unknown: "#f2a63c",
};

/**
 * Real Leaflet map (not an illustration) — defaults to a Philippines-wide
 * view, same as the prototype. Depot markers have a "Navigate here" popup
 * button that uses the browser's geolocation as the route origin.
 */
export default function IncidentMap({
  incidents,
  depots,
  dispatches,
  onSelectIncident,
  route,
  selectedIncidentId,
  onMapClick,
}: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMap = useRef<L.Map | null>(null);
  const routeLayers = useRef<L.Layer[]>([]);
  const depotMarkers = useRef<Record<string, L.CircleMarker>>({});
  const dispatchMarkers = useRef<Record<string, L.CircleMarker>>({});
  const yourLocationMarker = useRef<L.CircleMarker | null>(null);
  const onMapClickRef = useRef(onMapClick);
  onMapClickRef.current = onMapClick;

  const [navTarget, setNavTarget] = useState<Depot | null>(null);
  const [userLocation, setUserLocation] = useState<{
    lat: number;
    lng: number;
  } | null>(null);

  const { route: directions, error: directionsError } = useDirections(
    userLocation,
    navTarget ? { lat: navTarget.lat, lng: navTarget.lng } : null,
  );

  const { position: livePosition, start: startWatchingPosition, stop: stopWatchingPosition } =
    useWatchPosition();

  useEffect(() => {
    if (navTarget) {
      startWatchingPosition();
    } else {
      stopWatchingPosition();
    }
  }, [navTarget, startWatchingPosition, stopWatchingPosition]);

  useEffect(() => {
    if (!mapRef.current || leafletMap.current) return;

    leafletMap.current = L.map(mapRef.current).setView(PH_CENTER, 6);
    L.tileLayer(
      "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png",
      {
        maxZoom: 18,
      },
    ).addTo(leafletMap.current);

    leafletMap.current.on("click", (e: L.LeafletMouseEvent) => {
      onMapClickRef.current?.(e.latlng.lat, e.latlng.lng);
    });

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          if (!leafletMap.current) return;
          yourLocationMarker.current = L.circleMarker(
            [pos.coords.latitude, pos.coords.longitude],
            { radius: 7, color: "#4A9EFF", fillColor: "#4A9EFF", fillOpacity: 0.9 },
          )
            .addTo(leafletMap.current)
            .bindPopup("Your location");
        },
        () => {
          // Denied/unavailable — no marker, no error surfaced (best-effort only).
        },
        { enableHighAccuracy: false, timeout: 8000 },
      );
    }

    return () => {
      leafletMap.current?.remove();
      leafletMap.current = null;
    };
  }, []);

  useEffect(() => {
    if (!leafletMap.current || !selectedIncidentId) return;
    const inc = incidents.find((i) => i.id === selectedIncidentId);
    if (!inc) return;
    leafletMap.current.flyTo([inc.lat, inc.lng], 10);
  }, [selectedIncidentId, incidents]);

  function requestNavigation(depot: Depot, marker: L.CircleMarker) {
    if (!navigator.geolocation) {
      marker.setPopupContent(
        `<strong>${depot.name}</strong><br>Geolocation isn't available in this browser.`,
      );
      return;
    }

    marker.setPopupContent(`<strong>${depot.name}</strong><br>Locating you…`);

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        setUserLocation({
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
        });
        setNavTarget(depot);
      },
      () => {
        marker.setPopupContent(
          `<strong>${depot.name}</strong><br>Location permission denied — can't calculate a route.`,
        );
      },
      { enableHighAccuracy: false, timeout: 8000 },
    );
  }

  useEffect(() => {
    if (!leafletMap.current) return;

    incidents.forEach((inc) => {
      const marker = L.circleMarker([inc.lat, inc.lng], {
        radius: 6,
        color: inc.signal >= 4 ? "#E14B3F" : "#E8C468",
      }).addTo(leafletMap.current!);
      marker.bindPopup(inc.name);
      marker.on("click", () => onSelectIncident?.(inc.id));
    });

    depotMarkers.current = {};
    depots.forEach((d) => {
      const marker = L.circleMarker([d.lat, d.lng], {
        radius: 5,
        color: "#3FA98C",
      }).addTo(leafletMap.current!);
      marker.bindPopup(
        `<strong>${d.name}</strong><br>` +
          `<button class="navigate-btn" data-depot-id="${d.id}" ` +
          `style="margin-top:6px;background:#F2A63C;color:#1a1200;border:none;padding:4px 8px;font-size:11px;cursor:pointer;">` +
          `Navigate here</button>`,
      );
      marker.on("popupopen", (e) => {
        const el = e.popup.getElement();
        const btn = el?.querySelector<HTMLButtonElement>(".navigate-btn");
        if (btn) {
          // Without this, the click bubbles to the map and Leaflet's
          // closePopupOnClick (default true) closes the popup instantly.
          L.DomEvent.disableClickPropagation(btn);
          btn.addEventListener("click", () => requestNavigation(d, marker));
        }
      });
      depotMarkers.current[d.id] = marker;
    });
  }, [incidents, depots, onSelectIncident]);

  // True dynamic repositioning via setLatLng — unlike the incident/depot pins
  // above, dispatch markers move on live position updates, so they must not
  // be torn down and rebuilt every time (that would drop the popup/selection
  // state and flicker on every GPS update).
  useEffect(() => {
    if (!leafletMap.current) return;

    const seen = new Set<string>();

    (dispatches ?? []).forEach((d) => {
      if (d.current_lat == null || d.current_lng == null) return;
      seen.add(d.id);

      const existing = dispatchMarkers.current[d.id];
      if (existing) {
        existing.setLatLng([d.current_lat, d.current_lng]);
        existing.setPopupContent(
          `<strong>${d.vehicle_label ?? "Dispatch"}</strong><br>${d.status}`,
        );
        return;
      }

      const marker = L.circleMarker([d.current_lat, d.current_lng], {
        radius: 6,
        color: "#4A9EFF",
        fillColor: "#4A9EFF",
        fillOpacity: 0.9,
      }).addTo(leafletMap.current!);
      marker.bindPopup(
        `<strong>${d.vehicle_label ?? "Dispatch"}</strong><br>${d.status}`,
      );
      dispatchMarkers.current[d.id] = marker;
    });

    Object.keys(dispatchMarkers.current).forEach((id) => {
      if (!seen.has(id)) {
        dispatchMarkers.current[id].remove();
        delete dispatchMarkers.current[id];
      }
    });
  }, [dispatches]);

  useEffect(() => {
    if (!navTarget || !directions) return;
    const marker = depotMarkers.current[navTarget.id];
    if (!marker) return;

    if (
      directions.ok &&
      directions.distance_m != null &&
      directions.duration_s != null
    ) {
      const km = (directions.distance_m / 1000).toFixed(1);
      const mins = Math.round(directions.duration_s / 60);
      marker.setPopupContent(
        `<strong>${navTarget.name}</strong><br>${km} km · ~${mins} min away`,
      );
    } else if (!directions.ok) {
      marker.setPopupContent(
        `<strong>${navTarget.name}</strong><br>${directionsError ?? "No route found."}`,
      );
    }
  }, [directions, navTarget, directionsError]);

  useEffect(() => {
    if (!leafletMap.current) return;

    routeLayers.current.forEach((layer) => layer.remove());
    routeLayers.current = [];

    const geometry =
      navTarget && directions?.ok ? (directions.geometry ?? null) : route;
    if (!geometry || geometry.length === 0) return;

    const latlngs: [number, number][] = geometry.map(([lng, lat]) => [
      lat,
      lng,
    ]);

    // Only present from a traffic-aware provider (see PremiumDirectionsProvider)
    // — one entry per segment between consecutive coordinates. OSRM (no live
    // traffic) never sets this, so the polyline falls back to a flat color.
    const congestion = navTarget && directions?.ok ? directions.congestion : null;
    const layers: L.Layer[] = [];

    if (congestion && congestion.length === latlngs.length - 1) {
      for (let i = 0; i < latlngs.length - 1; i++) {
        const segment = L.polyline([latlngs[i], latlngs[i + 1]], {
          color: CONGESTION_COLORS[congestion[i]] ?? CONGESTION_COLORS.unknown,
          weight: 4,
        }).addTo(leafletMap.current);
        layers.push(segment);
      }
    } else {
      layers.push(
        L.polyline(latlngs, { color: "#f2a63c", weight: 4 }).addTo(leafletMap.current),
      );
    }

    const start = L.circleMarker(latlngs[0], {
      radius: 7,
      color: "#3FA98C",
    }).addTo(leafletMap.current);
    const end = L.circleMarker(latlngs[latlngs.length - 1], {
      radius: 7,
      color: "#E14B3F",
    }).addTo(leafletMap.current);

    routeLayers.current = [...layers, start, end];
    const bounds = L.latLngBounds(latlngs);
    leafletMap.current.fitBounds(bounds, { padding: [40, 40] });
  }, [route, directions, navTarget]);

  // Live "you are here" marker while a navigation is active — separate from
  // the one-shot getCurrentPosition used only to seed the route's origin.
  useEffect(() => {
    if (!leafletMap.current || !livePosition) return;

    if (yourLocationMarker.current) {
      yourLocationMarker.current.setLatLng([livePosition.lat, livePosition.lng]);
    } else {
      yourLocationMarker.current = L.circleMarker(
        [livePosition.lat, livePosition.lng],
        { radius: 7, color: "#4A9EFF", fillColor: "#4A9EFF", fillOpacity: 0.9 },
      )
        .addTo(leafletMap.current)
        .bindPopup("Your location");
    }
  }, [livePosition]);

  return <div ref={mapRef} className="h-full w-full" />;
}
