import { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import useDirections from "@/Hooks/useDirections";

type Incident = {
  id: string;
  name: string;
  lat: number;
  lng: number;
  signal: number;
};
type Depot = { id: string; name: string; lat: number; lng: number };

/** [lng, lat] pairs — matches the GeoJSON order returned by GET /api/directions. */
type RouteGeometry = [number, number][];

type Props = {
  incidents: Incident[];
  depots: Depot[];
  onSelectIncident?: (id: string) => void;
  route?: RouteGeometry | null;
};

const PH_CENTER: [number, number] = [12.8797, 121.774];

/**
 * Real Leaflet map (not an illustration) — defaults to a Philippines-wide
 * view, same as the prototype. Depot markers have a "Navigate here" popup
 * button that uses the browser's geolocation as the route origin.
 */
export default function IncidentMap({
  incidents,
  depots,
  onSelectIncident,
  route,
}: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMap = useRef<L.Map | null>(null);
  const routeLayers = useRef<L.Layer[]>([]);
  const depotMarkers = useRef<Record<string, L.CircleMarker>>({});

  const [navTarget, setNavTarget] = useState<Depot | null>(null);
  const [userLocation, setUserLocation] = useState<{
    lat: number;
    lng: number;
  } | null>(null);

  const { route: directions, error: directionsError } = useDirections(
    userLocation,
    navTarget ? { lat: navTarget.lat, lng: navTarget.lng } : null,
  );

  useEffect(() => {
    if (!mapRef.current || leafletMap.current) return;

    leafletMap.current = L.map(mapRef.current).setView(PH_CENTER, 6);
    L.tileLayer(
      "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png",
      {
        maxZoom: 18,
      },
    ).addTo(leafletMap.current);

    return () => {
      leafletMap.current?.remove();
      leafletMap.current = null;
    };
  }, []);

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

    const polyline = L.polyline(latlngs, { color: "#f2a63c", weight: 4 }).addTo(
      leafletMap.current,
    );
    const start = L.circleMarker(latlngs[0], {
      radius: 7,
      color: "#3FA98C",
    }).addTo(leafletMap.current);
    const end = L.circleMarker(latlngs[latlngs.length - 1], {
      radius: 7,
      color: "#E14B3F",
    }).addTo(leafletMap.current);

    routeLayers.current = [polyline, start, end];
    leafletMap.current.fitBounds(polyline.getBounds(), { padding: [40, 40] });
  }, [route, directions, navTarget]);

  return <div ref={mapRef} className="h-full w-full" />;
}
