import { useCallback, useEffect, useRef, useState } from "react";
import { DirectionsStep } from "@/Types/models";

const AUTO_ADVANCE_THRESHOLD_M = 40;

function haversineMeters(a: { lat: number; lng: number }, b: { lat: number; lng: number }) {
  const R = 6371000;
  const dLat = ((b.lat - a.lat) * Math.PI) / 180;
  const dLng = ((b.lng - a.lng) * Math.PI) / 180;
  const s =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((a.lat * Math.PI) / 180) * Math.cos((b.lat * Math.PI) / 180) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
}

type AutoAdvanceOptions = {
  steps?: DirectionsStep[];
  currentStepIndex?: number;
  position?: { lat: number; lng: number } | null;
  onAdvance?: () => void;
  thresholdMeters?: number;
};

/**
 * Speaks turn-by-turn instructions aloud via the browser's built-in
 * Web Speech API — free, no signup, no external service. Optionally
 * auto-advances the current step once a live position (from
 * useWatchPosition) comes within `thresholdMeters` of the *next*
 * maneuver's location — manual Prev/Next in DirectionsPanel still works
 * as an override regardless.
 *
 * Known limitation: navigator.geolocation requires a secure context
 * (HTTPS/localhost), which the current deployment doesn't have — this
 * piece is accepted as untestable live locally and verified by code
 * review only; everything else in this hook (speaking on step change,
 * mute toggle) works and is tested independently of GPS.
 */
export default function useVoiceGuidance(
  currentInstruction: string | null,
  autoAdvance?: AutoAdvanceOptions
) {
  const [muted, setMuted] = useState(false);
  const supported = typeof window !== "undefined" && "speechSynthesis" in window;
  const lastAdvancedForStepRef = useRef<number | null>(null);

  const speak = useCallback(
    (text: string) => {
      if (!supported || muted || !text) return;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(new SpeechSynthesisUtterance(text));
    },
    [supported, muted]
  );

  useEffect(() => {
    if (currentInstruction) speak(currentInstruction);
  }, [currentInstruction, speak]);

  const toggleMute = useCallback(() => {
    if (!muted) window.speechSynthesis?.cancel();
    setMuted((m) => !m);
  }, [muted]);

  const { steps, currentStepIndex, position, onAdvance, thresholdMeters } = autoAdvance ?? {};

  useEffect(() => {
    if (!steps || currentStepIndex == null || !position || !onAdvance) return;

    const nextStep = steps[currentStepIndex + 1];
    if (!nextStep) return;

    const [nextLng, nextLat] = nextStep.location;
    const distance = haversineMeters(position, { lat: nextLat, lng: nextLng });

    if (
      distance <= (thresholdMeters ?? AUTO_ADVANCE_THRESHOLD_M) &&
      lastAdvancedForStepRef.current !== currentStepIndex
    ) {
      lastAdvancedForStepRef.current = currentStepIndex;
      onAdvance();
    }
  }, [steps, currentStepIndex, position, onAdvance, thresholdMeters]);

  return { muted, toggleMute, supported, speak };
}
