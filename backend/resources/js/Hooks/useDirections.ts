import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import axios from "axios";
import { DirectionsResult } from "@/Types/models";

type LatLng = { lat: number; lng: number };

// While a navigation is active, traffic-aware providers can pick a different
// "best" route as congestion changes — re-fetch periodically to catch that,
// rather than only ever fetching once on mount.
const POLL_INTERVAL_MS = 90_000;

function sameGeometry(a?: [number, number][], b?: [number, number][]) {
  if (a === b) return true;
  if (!a || !b || a.length !== b.length) return false;
  return a.every(([lng, lat], i) => lng === b[i][0] && lat === b[i][1]);
}

/**
 * Fetches a route from GET /api/directions and owns the "which turn-by-turn
 * step are we on" cursor — shared by DirectionsPanel and useVoiceGuidance so
 * both stay in sync off one source of truth. Polls periodically while a
 * navigation is active so a traffic-aware provider's "best route" pick can
 * change mid-trip without the caller having to re-trigger a fetch manually.
 */
export default function useDirections(from: LatLng | null, to: LatLng | null) {
  const [route, setRoute] = useState<DirectionsResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [currentStepIndex, setCurrentStepIndex] = useState(0);
  const routeRef = useRef<DirectionsResult | null>(null);

  useEffect(() => {
    if (!from || !to) {
      setRoute(null);
      routeRef.current = null;
      setError(null);
      return;
    }

    let cancelled = false;
    let isFirstFetch = true;

    const fetchRoute = () => {
      if (isFirstFetch) setLoading(true);
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

          // Only redraw/reset the step cursor if the chosen route geometry
          // actually changed — a poll that reconfirms the same route
          // shouldn't reset the driver's place in the turn-by-turn list.
          if (!sameGeometry(routeRef.current?.geometry, data.geometry)) {
            routeRef.current = data;
            setRoute(data);
            setCurrentStepIndex(0);
          } else {
            routeRef.current = data;
            setRoute(data);
          }

          if (!data.ok) setError(data.error ?? "No route found");
        })
        .catch(() => {
          if (!cancelled) setError("Directions request failed");
        })
        .finally(() => {
          if (!cancelled) setLoading(false);
          isFirstFetch = false;
        });
    };

    fetchRoute();
    const intervalId = window.setInterval(fetchRoute, POLL_INTERVAL_MS);

    return () => {
      cancelled = true;
      window.clearInterval(intervalId);
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
