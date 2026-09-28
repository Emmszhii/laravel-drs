import { useEffect, useState } from "react";
import axios from "axios";
import { DirectionsResult } from "@/Types/models";

type LatLng = { lat: number; lng: number };

/**
 * Fetches a route from GET /api/directions — used by IncidentMap to draw
 * the depot-to-destination line and show distance/duration on the live map
 * (see the interview's "live map with route visualization" ask). No
 * turn-by-turn step tracking here — that was a separate, unused UI layer
 * (DirectionsPanel/useVoiceGuidance) removed since it wasn't requested.
 */
export default function useDirections(from: LatLng | null, to: LatLng | null) {
  const [route, setRoute] = useState<DirectionsResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!from || !to) {
      setRoute(null);
      setError(null);
      return;
    }

    let cancelled = false;
    setLoading(true);
    setError(null);

    axios
      .get<DirectionsResult>("/api/directions", {
        params: {
          from_lat: from.lat,
          from_lng: from.lng,
          to_lat: to.lat,
          to_lng: to.lng,
        },
      })
      .then(({ data }) => {
        if (cancelled) return;
        setRoute(data);
        if (!data.ok) setError(data.error ?? "No route found");
      })
      .catch(() => {
        if (!cancelled) setError("Directions request failed");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [from?.lat, from?.lng, to?.lat, to?.lng]);

  return { route, loading, error };
}
