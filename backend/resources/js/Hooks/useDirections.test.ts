import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook, waitFor } from "@testing-library/react";
import axios from "axios";
import useDirections from "./useDirections";
import { DirectionsResult } from "@/Types/models";

vi.mock("axios");
const mockedGet = vi.mocked(axios.get);

const FROM = { lat: 14.1, lng: 121.05 };
const TO = { lat: 14.2, lng: 121.1 };

function okResult(overrides: Partial<DirectionsResult> = {}): DirectionsResult {
  return {
    ok: true,
    distance_m: 5000,
    duration_s: 600,
    geometry: [
      [121.05, 14.1],
      [121.1, 14.2],
    ],
    steps: [
      {
        instruction: "Head out",
        distance_m: 5000,
        duration_s: 600,
        maneuver_type: "depart",
        maneuver_modifier: null,
        location: [121.05, 14.1],
      },
    ],
    ...overrides,
  };
}

describe("useDirections", () => {
  beforeEach(() => {
    mockedGet.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("does not fetch when from/to are missing", () => {
    renderHook(() => useDirections(null, null));
    expect(mockedGet).not.toHaveBeenCalled();
  });

  it("fetches a route once both from and to are set", async () => {
    mockedGet.mockResolvedValue({ data: okResult() });

    const { result } = renderHook(() => useDirections(FROM, TO));

    await waitFor(() => expect(result.current.loading).toBe(false));

    expect(mockedGet).toHaveBeenCalledWith(
      "/api/directions",
      expect.objectContaining({
        params: { from_lat: FROM.lat, from_lng: FROM.lng, to_lat: TO.lat, to_lng: TO.lng },
      }),
    );
    expect(result.current.route?.ok).toBe(true);
    expect(result.current.steps).toHaveLength(1);
  });

  it("sets an error when the response is not ok", async () => {
    mockedGet.mockResolvedValue({ data: { ok: false, error: "no_route_found" } });

    const { result } = renderHook(() => useDirections(FROM, TO));

    await waitFor(() => expect(result.current.error).toBe("no_route_found"));
  });

  it("sets a generic error when the request itself fails", async () => {
    mockedGet.mockRejectedValue(new Error("network down"));

    const { result } = renderHook(() => useDirections(FROM, TO));

    await waitFor(() => expect(result.current.error).toBe("Directions request failed"));
  });

  it("clears the route when from/to become null", async () => {
    mockedGet.mockResolvedValue({ data: okResult() });

    const { result, rerender } = renderHook(({ from, to }) => useDirections(from, to), {
      initialProps: { from: FROM as typeof FROM | null, to: TO as typeof TO | null },
    });

    await waitFor(() => expect(result.current.route).not.toBeNull());

    rerender({ from: null, to: null });

    expect(result.current.route).toBeNull();
  });

  it("next()/prev() move the step cursor within bounds", async () => {
    mockedGet.mockResolvedValue({
      data: okResult({
        steps: [
          { instruction: "A", distance_m: 1, duration_s: 1, maneuver_type: "depart", maneuver_modifier: null, location: [0, 0] },
          { instruction: "B", distance_m: 1, duration_s: 1, maneuver_type: "turn", maneuver_modifier: "left", location: [0, 0] },
        ],
      }),
    });

    const { result } = renderHook(() => useDirections(FROM, TO));
    await waitFor(() => expect(result.current.steps).toHaveLength(2));

    expect(result.current.currentStepIndex).toBe(0);

    act(() => result.current.next());
    expect(result.current.currentStepIndex).toBe(1);

    act(() => result.current.next()); // already at last step
    expect(result.current.currentStepIndex).toBe(1);

    act(() => result.current.prev());
    expect(result.current.currentStepIndex).toBe(0);

    act(() => result.current.prev()); // already at first step
    expect(result.current.currentStepIndex).toBe(0);
  });

  it("polls again after the interval and keeps the step cursor when the route is unchanged", async () => {
    const response = okResult();
    mockedGet.mockResolvedValue({ data: response });
    vi.useFakeTimers();

    const { result } = renderHook(() => useDirections(FROM, TO));

    await act(async () => {
      await vi.advanceTimersByTimeAsync(0);
    });
    expect(result.current.route).not.toBeNull();

    act(() => result.current.next());
    expect(result.current.currentStepIndex).toBe(0); // only one step in fixture, clamps to 0

    mockedGet.mockClear();
    await act(async () => {
      await vi.advanceTimersByTimeAsync(90_000);
    });

    expect(mockedGet).toHaveBeenCalledTimes(1);
  });

  it("resets the step cursor when a poll returns a different route geometry", async () => {
    const firstSteps = [
      { instruction: "A", distance_m: 1, duration_s: 1, maneuver_type: "depart" as const, maneuver_modifier: null, location: [0, 0] as [number, number] },
      { instruction: "B", distance_m: 1, duration_s: 1, maneuver_type: "turn" as const, maneuver_modifier: "left" as const, location: [0, 0] as [number, number] },
    ];
    mockedGet.mockResolvedValue({
      data: okResult({ geometry: [[0, 0], [1, 1]], steps: firstSteps }),
    });
    vi.useFakeTimers();

    const { result } = renderHook(() => useDirections(FROM, TO));

    await act(async () => {
      await vi.advanceTimersByTimeAsync(0);
    });
    expect(result.current.steps).toHaveLength(2);

    act(() => result.current.next());
    expect(result.current.currentStepIndex).toBe(1);

    mockedGet.mockResolvedValue({
      data: okResult({ geometry: [[9, 9], [8, 8]], steps: firstSteps }),
    });

    await act(async () => {
      await vi.advanceTimersByTimeAsync(90_000);
    });

    expect(result.current.currentStepIndex).toBe(0);
  });
});
