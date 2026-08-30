import { useCallback, useEffect, useRef, useState } from "react";

export type LatLng = { lat: number; lng: number };

/**
 * Generic wrapper around navigator.geolocation.watchPosition — the shared
 * "continuous live position" primitive. useLiveGeolocation builds the
 * driver-position-reporting POST loop on top of this; IncidentMap/
 * DirectionsPanel use it directly for a live "you are here" marker and
 * GPS-driven turn-by-turn auto-advance (see useVoiceGuidance).
 *
 * Known limitation: navigator.geolocation requires a secure context
 * (HTTPS/localhost) — this hook degrades to an explicit `error` on plain
 * HTTP, same as the one-shot getCurrentPosition calls elsewhere in the app.
 */
export default function useWatchPosition() {
  const [position, setPosition] = useState<LatLng | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [watching, setWatching] = useState(false);
  const watchIdRef = useRef<number | null>(null);

  const stop = useCallback(() => {
    if (watchIdRef.current != null) {
      navigator.geolocation.clearWatch(watchIdRef.current);
      watchIdRef.current = null;
    }
    setWatching(false);
  }, []);

  const start = useCallback(() => {
    if (!navigator.geolocation) {
      setError("Geolocation isn't available in this browser.");
      return;
    }

    setError(null);
    setWatching(true);

    watchIdRef.current = navigator.geolocation.watchPosition(
      (pos) => {
        setPosition({ lat: pos.coords.latitude, lng: pos.coords.longitude });
      },
      () => {
        setError("Location permission denied.");
        stop();
      },
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 },
    );
  }, [stop]);

  useEffect(() => stop, [stop]);

  return { position, error, watching, start, stop };
}
