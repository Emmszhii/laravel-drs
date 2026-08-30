import { useEffect, useRef } from "react";
import { dispatchesService } from "@/api-service";
import useWatchPosition from "./useWatchPosition";

const REPORT_THROTTLE_MS = 8000;

/**
 * Built on the shared useWatchPosition primitive — throttles POSTs of the
 * driver's live position to the assigned dispatch. Call `start()` from a
 * driver-initiated "Share my location" toggle — this never runs unprompted.
 */
export default function useLiveGeolocation(dispatchId: string | null) {
  const { position, error, watching, start, stop } = useWatchPosition();
  const lastSentAtRef = useRef(0);

  useEffect(() => {
    if (!dispatchId || !position || !watching) return;

    const now = Date.now();
    if (now - lastSentAtRef.current < REPORT_THROTTLE_MS) return;
    lastSentAtRef.current = now;

    dispatchesService
      .updatePosition(dispatchId, position.lat, position.lng)
      .catch(() => {
        // Best-effort — the next watchPosition tick will retry.
      });
  }, [dispatchId, position, watching]);

  return { sharing: watching, error, start, stop };
}
