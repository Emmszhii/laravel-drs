import { useEffect, useState } from "react";
import Button from "../Common/Button";
import useDirections from "../../Hooks/useDirections";
import useVoiceGuidance from "../../Hooks/useVoiceGuidance";
import useWatchPosition from "../../Hooks/useWatchPosition";

type Point = { id: string; name: string; lat: number; lng: number };

type Props = {
  depots: Point[];
  destinations: Point[]; // zones/incidents
  onRouteChange?: (geometry: [number, number][] | null) => void;
};

function formatDistance(m: number) {
  return m >= 1000 ? `${(m / 1000).toFixed(1)} km` : `${Math.round(m)} m`;
}

function formatDuration(s: number) {
  const mins = Math.round(s / 60);
  return mins >= 60 ? `${Math.floor(mins / 60)} h ${mins % 60} min` : `${mins} min`;
}

const FALLBACK_MESSAGES: Record<string, string> = {
  cap_exceeded: "Basic navigation — premium routing limit reached this month",
  provider_error: "Basic navigation — premium provider unavailable",
};

/**
 * Origin/destination pickers + turn-by-turn step list. Draws its route via
 * `onRouteChange` into IncidentMap (kept dumb/imperative, see that file).
 */
export default function DirectionsPanel({ depots, destinations, onRouteChange }: Props) {
  const [fromId, setFromId] = useState("");
  const [toId, setToId] = useState("");

  const from = depots.find((d) => d.id === fromId) ?? null;
  const to = destinations.find((d) => d.id === toId) ?? null;

  const { route, steps, loading, error, currentStepIndex, next, prev } = useDirections(from, to);
  const currentStep = steps[currentStepIndex] ?? null;
  const { position, error: positionError, watching, start: startWatching, stop: stopWatching } =
    useWatchPosition();
  const { muted, toggleMute, supported } = useVoiceGuidance(currentStep?.instruction ?? null, {
    steps,
    currentStepIndex,
    position,
    onAdvance: next,
  });

  useEffect(() => {
    onRouteChange?.(route?.ok ? route.geometry ?? null : null);
  }, [route, onRouteChange]);

  // Start/stop live tracking alongside an active navigation — this is the
  // GPS auto-advance feed for useVoiceGuidance above.
  useEffect(() => {
    if (route?.ok) {
      startWatching();
    } else {
      stopWatching();
    }
  }, [route?.ok, startWatching, stopWatching]);

  return (
    <div className="flex h-full flex-col p-4 text-sm">
      <h2 className="mb-3 text-xs font-semibold uppercase tracking-wide text-[var(--text-secondary)]">
        Navigate
      </h2>

      <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
        From (depot)
      </label>
      <select
        value={fromId}
        onChange={(e) => setFromId(e.target.value)}
        className="mb-3 w-full border border-white/20 bg-transparent p-2 text-[var(--text-primary)]"
      >
        <option value="">Select a depot…</option>
        {depots.map((d) => (
          <option key={d.id} value={d.id} className="bg-[var(--bg-panel)]">
            {d.name}
          </option>
        ))}
      </select>

      <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
        To (incident / zone)
      </label>
      <select
        value={toId}
        onChange={(e) => setToId(e.target.value)}
        className="mb-3 w-full border border-white/20 bg-transparent p-2 text-[var(--text-primary)]"
      >
        <option value="">Select a destination…</option>
        {destinations.map((d) => (
          <option key={d.id} value={d.id} className="bg-[var(--bg-panel)]">
            {d.name}
          </option>
        ))}
      </select>

      {loading && <p className="text-[var(--text-secondary)]">Calculating route…</p>}
      {error && <p className="text-[var(--critical)]">{error}</p>}

      {route?.ok && (
        <>
          <div className="mb-3 flex items-center justify-between border-y border-white/10 py-2">
            <div>
              <div className="font-semibold">{formatDistance(route.distance_m ?? 0)}</div>
              <div className="text-xs text-[var(--text-secondary)]">
                {formatDuration(route.duration_in_traffic_s ?? route.duration_s ?? 0)}
                {route.duration_in_traffic_s != null && " (with traffic)"}
              </div>
            </div>
            {supported && (
              <button
                onClick={toggleMute}
                className="border border-white/20 px-2 py-1 text-xs uppercase text-[var(--text-secondary)]"
              >
                {muted ? "Unmute" : "Mute"}
              </button>
            )}
          </div>

          {route.fallback_reason && (
            <p className="mb-3 border border-[var(--pending)]/40 bg-[var(--pending)]/10 p-2 text-xs text-[var(--pending)]">
              {FALLBACK_MESSAGES[route.fallback_reason]}
            </p>
          )}

          {watching && (
            <p className="mb-3 text-xs text-[var(--text-secondary)]">
              {position ? "Live location on — steps auto-advance as you drive." : "Locating you…"}
            </p>
          )}
          {positionError && (
            <p className="mb-3 text-xs text-[var(--critical)]">{positionError}</p>
          )}

          <ol className="flex-1 space-y-1 overflow-y-auto">
            {steps.map((step, i) => (
              <li
                key={i}
                className={`border-l-2 p-2 ${
                  i === currentStepIndex
                    ? "border-[var(--amber)] bg-[var(--bg-panel-raised)]"
                    : "border-transparent"
                }`}
              >
                <div>{step.instruction}</div>
                <div className="text-xs text-[var(--text-secondary)]">
                  {formatDistance(step.distance_m)}
                </div>
              </li>
            ))}
          </ol>

          <div className="mt-3 flex gap-2">
            <Button variant="ghost" onClick={prev} disabled={currentStepIndex === 0}>
              Prev
            </Button>
            <Button variant="ghost" onClick={next} disabled={currentStepIndex >= steps.length - 1}>
              Next
            </Button>
          </div>
        </>
      )}
    </div>
  );
}
