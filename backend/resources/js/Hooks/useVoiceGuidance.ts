import { useCallback, useEffect, useState } from "react";

/**
 * Speaks turn-by-turn instructions aloud via the browser's built-in
 * Web Speech API — free, no signup, no external service. Live GPS-driven
 * auto-advance is intentionally out of scope for now: navigator.geolocation
 * requires a secure context (HTTPS/localhost), which the current deployment
 * doesn't have; this hook only speaks when `currentStepIndex` changes,
 * driven by the manual Prev/Next controls in DirectionsPanel.
 */
export default function useVoiceGuidance(currentInstruction: string | null) {
  const [muted, setMuted] = useState(false);
  const supported = typeof window !== "undefined" && "speechSynthesis" in window;

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

  return { muted, toggleMute, supported, speak };
}
