import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook } from "@testing-library/react";
import useVoiceGuidance from "./useVoiceGuidance";
import { DirectionsStep } from "@/Types/models";

function step(lng: number, lat: number, instruction = "Turn left"): DirectionsStep {
  return {
    instruction,
    distance_m: 100,
    duration_s: 10,
    maneuver_type: "turn",
    maneuver_modifier: "left",
    location: [lng, lat],
  };
}

describe("useVoiceGuidance", () => {
  let speak: ReturnType<typeof vi.fn>;
  let cancel: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    speak = vi.fn();
    cancel = vi.fn();
    vi.stubGlobal("speechSynthesis", { speak, cancel });
    vi.stubGlobal(
      "SpeechSynthesisUtterance",
      vi.fn().mockImplementation(function (this: { text: string }, text: string) {
        this.text = text;
      }),
    );
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it("speaks the instruction when it changes", () => {
    const { rerender } = renderHook(({ instruction }) => useVoiceGuidance(instruction), {
      initialProps: { instruction: "Head north" as string | null },
    });

    expect(speak).toHaveBeenCalledTimes(1);

    rerender({ instruction: "Turn right" });
    expect(speak).toHaveBeenCalledTimes(2);
  });

  it("does not speak when the instruction is null", () => {
    renderHook(() => useVoiceGuidance(null));
    expect(speak).not.toHaveBeenCalled();
  });

  it("does not speak while muted", () => {
    const { result, rerender } = renderHook(({ instruction }) => useVoiceGuidance(instruction), {
      initialProps: { instruction: "Head north" as string | null },
    });
    speak.mockClear();

    act(() => result.current.toggleMute());
    rerender({ instruction: "Turn right" });

    expect(speak).not.toHaveBeenCalled();
  });

  it("toggleMute cancels any in-progress speech when muting", () => {
    const { result } = renderHook(() => useVoiceGuidance("Head north"));

    act(() => result.current.toggleMute());

    expect(cancel).toHaveBeenCalled();
  });

  it("reports supported=true when speechSynthesis exists on window", () => {
    const { result } = renderHook(() => useVoiceGuidance(null));
    expect(result.current.supported).toBe(true);
  });

  it("auto-advances once position comes within threshold of the next maneuver", () => {
    const onAdvance = vi.fn();
    const steps = [step(121.05, 14.1, "Depart"), step(121.06, 14.11, "Turn left")];

    const { rerender } = renderHook(
      ({ position }) =>
        useVoiceGuidance(null, {
          steps,
          currentStepIndex: 0,
          position,
          onAdvance,
        }),
      { initialProps: { position: { lat: 14.1, lng: 121.05 } } },
    );

    expect(onAdvance).not.toHaveBeenCalled();

    // Move to within ~a few meters of the next maneuver's location.
    rerender({ position: { lat: 14.10999, lng: 121.05999 } });

    expect(onAdvance).toHaveBeenCalledTimes(1);
  });

  it("does not re-fire onAdvance repeatedly for the same step", () => {
    const onAdvance = vi.fn();
    const steps = [step(121.05, 14.1, "Depart"), step(121.06, 14.11, "Turn left")];

    const { rerender } = renderHook(
      ({ position }) =>
        useVoiceGuidance(null, {
          steps,
          currentStepIndex: 0,
          position,
          onAdvance,
        }),
      { initialProps: { position: { lat: 14.10999, lng: 121.05999 } } },
    );

    expect(onAdvance).toHaveBeenCalledTimes(1);

    // Position updates again while still close — should not fire again.
    rerender({ position: { lat: 14.10998, lng: 121.05998 } });

    expect(onAdvance).toHaveBeenCalledTimes(1);
  });

  it("does not auto-advance when there is no next step", () => {
    const onAdvance = vi.fn();
    const steps = [step(121.05, 14.1, "Arrive")];

    renderHook(() =>
      useVoiceGuidance(null, {
        steps,
        currentStepIndex: 0,
        position: { lat: 14.1, lng: 121.05 },
        onAdvance,
      }),
    );

    expect(onAdvance).not.toHaveBeenCalled();
  });

  it("does not auto-advance while far from the next maneuver", () => {
    const onAdvance = vi.fn();
    const steps = [step(121.05, 14.1, "Depart"), step(121.5, 14.5, "Turn left")];

    renderHook(() =>
      useVoiceGuidance(null, {
        steps,
        currentStepIndex: 0,
        position: { lat: 14.1, lng: 121.05 },
        onAdvance,
      }),
    );

    expect(onAdvance).not.toHaveBeenCalled();
  });
});
