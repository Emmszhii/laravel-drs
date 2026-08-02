import { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import Button from "../Common/Button";
import { reverseGeocode } from "@/Utils/geocoding";

const PH_CENTER: [number, number] = [12.8797, 121.774];

type Props = {
  lat: number | null;
  lng: number | null;
  address: string;
  onChange: (lat: number, lng: number) => void;
  onResolveAddress?: (address: string) => void;
  onResolvePsgc?: (municipality?: string, province?: string) => void;
};

/** Click-to-place-pin map + Nominatim "Locate address" geocode button. */
export default function LocationPickMap({ lat, lng, address, onChange, onResolveAddress, onResolvePsgc }: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const leafletMap = useRef<L.Map | null>(null);
  const marker = useRef<L.Marker | null>(null);
  const onChangeRef = useRef(onChange);
  onChangeRef.current = onChange;
  const onResolveAddressRef = useRef(onResolveAddress);
  onResolveAddressRef.current = onResolveAddress;
  const onResolvePsgcRef = useRef(onResolvePsgc);
  onResolvePsgcRef.current = onResolvePsgc;
  const [geocoding, setGeocoding] = useState(false);
  const [resolvingAddress, setResolvingAddress] = useState(false);
  const [hint, setHint] = useState("No location selected — click the map to place the pin.");

  useEffect(() => {
    if (!mapRef.current || leafletMap.current) return;

    leafletMap.current = L.map(mapRef.current).setView(lat && lng ? [lat, lng] : PH_CENTER, lat && lng ? 14 : 6);
    L.tileLayer("https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png", { maxZoom: 18 }).addTo(
      leafletMap.current
    );

    leafletMap.current.on("click", (e: L.LeafletMouseEvent) => {
      const { lat: clickedLat, lng: clickedLng } = e.latlng;
      onChangeRef.current(clickedLat, clickedLng);

      if (onResolveAddressRef.current || onResolvePsgcRef.current) {
        setResolvingAddress(true);
        reverseGeocode(clickedLat, clickedLng)
          .then((resolved) => {
            if (resolved.display) onResolveAddressRef.current?.(resolved.display);
            onResolvePsgcRef.current?.(resolved.municipality, resolved.province);
          })
          .finally(() => setResolvingAddress(false));
      }
    });

    return () => {
      leafletMap.current?.remove();
      leafletMap.current = null;
    };
  }, []);

  useEffect(() => {
    if (!leafletMap.current) return;

    if (lat == null || lng == null) {
      marker.current?.remove();
      marker.current = null;
      setHint("No location selected — click the map to place the pin.");
      return;
    }

    if (marker.current) {
      marker.current.setLatLng([lat, lng]);
    } else {
      marker.current = L.marker([lat, lng]).addTo(leafletMap.current);
    }
    leafletMap.current.setView([lat, lng], 14);
    setHint(`Selected: ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
  }, [lat, lng]);

  const locateAddress = async () => {
    if (!address.trim()) return;
    setGeocoding(true);
    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&countrycodes=ph&limit=1&q=${encodeURIComponent(
        address
      )}`;
      const res = await fetch(url);
      const results = await res.json();
      if (results?.[0]) {
        onChange(parseFloat(results[0].lat), parseFloat(results[0].lon));
      } else {
        setHint("Address not found — click the map to place the pin manually.");
      }
    } catch {
      setHint("Geocoding failed — click the map to place the pin manually.");
    } finally {
      setGeocoding(false);
    }
  };

  return (
    <div>
      <Button type="button" variant="ghost" className="mb-2 w-full" onClick={locateAddress} disabled={geocoding || !address.trim()}>
        {geocoding ? "Locating…" : "Locate address on map"}
      </Button>
      <div className="overflow-hidden border border-white/20">
        <div ref={mapRef} className="h-[180px] w-full" />
      </div>
      <p className="mt-1 text-xs text-[var(--text-secondary)]">
        {hint}
        {resolvingAddress && " · Resolving address…"}
      </p>
    </div>
  );
}
