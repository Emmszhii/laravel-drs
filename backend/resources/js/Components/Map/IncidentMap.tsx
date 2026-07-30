import { useEffect, useRef } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

type Incident = { id: string; name: string; lat: number; lng: number; signal: number };
type Depot = { id: string; name: string; lat: number; lng: number };

type Props = {
  incidents: Incident[];
  depots: Depot[];
  onSelectIncident?: (id: string) => void;
};

const PH_CENTER: [number, number] = [12.8797, 121.774];

/**
 * Real Leaflet map (not an illustration) — defaults to a Philippines-wide
 * view, same as the prototype. Wire up geolocation + theme-aware tile
 * switching (CARTO dark/light) as documented in SUMMARY.md.
 */
export default function IncidentMap({ incidents, depots, onSelectIncident }: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMap = useRef<L.Map | null>(null);

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

  return <div ref={mapRef} className="h-full w-full" />;
}
