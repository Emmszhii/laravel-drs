import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook } from "@testing-library/react";
import useLiveGeolocation from "./useLiveGeolocation";

const updatePosition = vi.fn();

vi.mock("@/api-service", () => ({
  dispatchesService: {
    updatePosition: (...args: unknown[]) => updatePosition(...args),
  },
}));

describe("useLiveGeolocation", () => {
  let watchPosition: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.useFakeTimers();
    updatePosition.mockReset();
    updatePosition.mockResolvedValue({});
    watchPosition = vi.fn();
    vi.stubGlobal("navigator", {
      geolocation: { watchPosition, clearWatch: vi.fn() },
    });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
    vi.useRealTimers();
  });

  it("does nothing until start() is called", () => {
    renderHook(() => useLiveGeolocation("dispatch-1"));
    expect(watchPosition).not.toHaveBeenCalled();
    expect(updatePosition).not.toHaveBeenCalled();
  });

  it("posts the position for the given dispatch once sharing starts", () => {
    watchPosition.mockImplementation((success: PositionCallback) => {
      success({ coords: { latitude: 14.1, longitude: 121.05 } } as GeolocationPosition);
      return 1;
    });

    const { result } = renderHook(() => useLiveGeolocation("dispatch-1"));
    act(() => result.current.start());

    expect(updatePosition).toHaveBeenCalledWith("dispatch-1", 14.1, 121.05);
    expect(result.current.sharing).toBe(true);
  });

  it("does not post anything when there is no dispatchId", () => {
    watchPosition.mockImplementation((success: PositionCallback) => {
      success({ coords: { latitude: 14.1, longitude: 121.05 } } as GeolocationPosition);
      return 1;
    });

    const { result } = renderHook(() => useLiveGeolocation(null));
    act(() => result.current.start());

    expect(updatePosition).not.toHaveBeenCalled();
  });

  it("throttles rapid position updates", () => {
    let onSuccess: PositionCallback = () => {};
    watchPosition.mockImplementation((success: PositionCallback) => {
      onSuccess = success;
      return 1;
    });

    const { result, rerender } = renderHook(() => useLiveGeolocation("dispatch-1"));
    act(() => result.current.start());

    act(() => onSuccess({ coords: { latitude: 14.1, longitude: 121.05 } } as GeolocationPosition));
    rerender();
    expect(updatePosition).toHaveBeenCalledTimes(1);

    // A second fix arriving immediately after should be throttled.
    act(() => onSuccess({ coords: { latitude: 14.11, longitude: 121.06 } } as GeolocationPosition));
    rerender();
    expect(updatePosition).toHaveBeenCalledTimes(1);
  });
});
