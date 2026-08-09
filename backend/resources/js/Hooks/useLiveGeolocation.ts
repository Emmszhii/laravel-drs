import { useCallback, useEffect, useRef, useState } from "react";
import { dispatchesService } from "@/api-service";

const REPORT_THROTTLE_MS = 8000;

/**
 * Wraps navigator.geolocation.watchPosition (genuinely continuous, unlike
 * IncidentMap.tsx's one-shot getCurrentPosition used for point-to-point
 * navigation) and throttles POSTs of the driver's position to the assigned
 * dispatch. Call `start()` from a driver-initiated "Share my location"
 * toggle — this never runs unprompted.
 */
export default function useLiveGeolocation(dispatchId: string | null) {
  const [sharing, setSharing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const watchIdRef = useRef<number | null>(null);
  const lastSentAtRef = useRef(0);

  const stop = useCallback(() => {
    if (watchIdRef.current != null) {
      navigator.geolocation.clearWatch(watchIdRef.current);
      watchIdRef.current = null;
    }
    setSharing(false);
  }, []);

  const start = useCallback(() => {
    if (!dispatchId) return;
    if (!navigator.geolocation) {
      setError("Geolocation isn't available in this browser.");
      return;
    }

    setError(null);
    setSharing(true);

    watchIdRef.current = navigator.geolocation.watchPosition(
      (pos) => {
        const now = Date.now();
        if (now - lastSentAtRef.current < REPORT_THROTTLE_MS) return;
        lastSentAtRef.current = now;

        dispatchesService
          .updatePosition(dispatchId, pos.coords.latitude, pos.coords.longitude)
          .catch(() => setError("Failed to report position — will retry."));
      },
      () => {
        setError("Location permission denied.");
        stop();
      },
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 },
    );
  }, [dispatchId, stop]);

  useEffect(() => stop, [stop]);

  return { sharing, error, start, stop };
}
