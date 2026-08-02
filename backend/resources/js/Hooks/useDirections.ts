import { useCallback, useEffect, useMemo, useState } from "react";
import axios from "axios";
import { DirectionsResult } from "@/Types/models";

type LatLng = { lat: number; lng: number };

/**
 * Fetches a route from GET /api/directions and owns the "which turn-by-turn
 * step are we on" cursor — shared by DirectionsPanel and useVoiceGuidance so
 * both stay in sync off one source of truth.
 */
export default function useDirections(from: LatLng | null, to: LatLng | null) {
  const [route, setRoute] = useState<DirectionsResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [currentStepIndex, setCurrentStepIndex] = useState(0);

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
        setCurrentStepIndex(0);
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

  const steps = useMemo(() => route?.steps ?? [], [route]);

  const next = useCallback(() => {
    setCurrentStepIndex((i) => Math.min(i + 1, Math.max(steps.length - 1, 0)));
  }, [steps.length]);

  const prev = useCallback(() => {
    setCurrentStepIndex((i) => Math.max(i - 1, 0));
  }, []);

  const reset = useCallback(() => setCurrentStepIndex(0), []);

  return { route, steps, loading, error, currentStepIndex, next, prev, reset };
}
