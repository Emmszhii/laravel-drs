import { useEffect, useRef } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

type Incident = { id: string; name: string; lat: number; lng: number; signal: number };
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
 * view, same as the prototype. Wire up geolocation + theme-aware tile
 * switching (CARTO dark/light) as documented in SUMMARY.md.
 */
export default function IncidentMap({ incidents, depots, onSelectIncident, route }: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMap = useRef<L.Map | null>(null);
  const routeLayers = useRef<L.Layer[]>([]);

  useEffect(() => {
    if (!mapRef.current || leafletMap.current) return;

    leafletMap.current = L.map(mapRef.current).setView(PH_CENTER, 6);
    L.tileLayer("https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png", {
      maxZoom: 18,
    }).addTo(leafletMap.current);

    return () => {
      leafletMap.current?.remove();
      leafletMap.current = null;
    };
  }, []);

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

    depots.forEach((d) => {
      L.circleMarker([d.lat, d.lng], { radius: 5, color: "#3FA98C" })
        .addTo(leafletMap.current!)
        .bindPopup(d.name);
    });
  }, [incidents, depots, onSelectIncident]);

  useEffect(() => {
    if (!leafletMap.current) return;

    routeLayers.current.forEach((layer) => layer.remove());
    routeLayers.current = [];

    if (!route || route.length === 0) return;

    const latlngs: [number, number][] = route.map(([lng, lat]) => [lat, lng]);

    const polyline = L.polyline(latlngs, { color: "#f2a63c", weight: 4 }).addTo(leafletMap.current);
    const start = L.circleMarker(latlngs[0], { radius: 7, color: "#3FA98C" }).addTo(leafletMap.current);
    const end = L.circleMarker(latlngs[latlngs.length - 1], { radius: 7, color: "#E14B3F" }).addTo(
      leafletMap.current
    );

    routeLayers.current = [polyline, start, end];
    leafletMap.current.fitBounds(polyline.getBounds(), { padding: [40, 40] });
  }, [route]);

  return <div ref={mapRef} className="h-full w-full" />;
}
