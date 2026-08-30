import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook } from "@testing-library/react";
import useWatchPosition from "./useWatchPosition";

describe("useWatchPosition", () => {
  let watchPosition: ReturnType<typeof vi.fn>;
  let clearWatch: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    watchPosition = vi.fn();
    clearWatch = vi.fn();
    vi.stubGlobal("navigator", {
      geolocation: { watchPosition, clearWatch },
    });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it("starts unwatched with no position or error", () => {
    const { result } = renderHook(() => useWatchPosition());

    expect(result.current.position).toBeNull();
    expect(result.current.error).toBeNull();
    expect(result.current.watching).toBe(false);
  });

  it("start() begins watching and updates position on a successful fix", () => {
    watchPosition.mockImplementation((success: PositionCallback) => {
      success({ coords: { latitude: 14.1, longitude: 121.05 } } as GeolocationPosition);
      return 1;
    });

    const { result } = renderHook(() => useWatchPosition());

    act(() => result.current.start());

    expect(result.current.watching).toBe(true);
    expect(result.current.position).toEqual({ lat: 14.1, lng: 121.05 });
    expect(result.current.error).toBeNull();
  });

  it("start() sets an error and stops watching when permission is denied", () => {
    // Real browsers only ever invoke watchPosition's callbacks after the
    // call returns (async) — capture the error callback and invoke it
    // afterwards so watchIdRef is already populated, same as production.
    let errorCallback: PositionErrorCallback = () => {};
    watchPosition.mockImplementation((_success: PositionCallback, error: PositionErrorCallback) => {
      errorCallback = error;
      return 1;
    });

    const { result } = renderHook(() => useWatchPosition());

    act(() => result.current.start());
    act(() => errorCallback({} as GeolocationPositionError));

    expect(result.current.error).toBe("Location permission denied.");
    expect(result.current.watching).toBe(false);
    expect(clearWatch).toHaveBeenCalledWith(1);
  });

  it("start() reports an error when geolocation isn't available at all", () => {
    vi.stubGlobal("navigator", {});

    const { result } = renderHook(() => useWatchPosition());

    act(() => result.current.start());

    expect(result.current.error).toBe("Geolocation isn't available in this browser.");
    expect(result.current.watching).toBe(false);
  });

  it("stop() clears the active watch", () => {
    watchPosition.mockReturnValue(42);

    const { result } = renderHook(() => useWatchPosition());

    act(() => result.current.start());
    expect(result.current.watching).toBe(true);

    act(() => result.current.stop());

    expect(clearWatch).toHaveBeenCalledWith(42);
    expect(result.current.watching).toBe(false);
  });

  it("clears the watch on unmount", () => {
    watchPosition.mockReturnValue(7);

    const { result, unmount } = renderHook(() => useWatchPosition());
    act(() => result.current.start());

    unmount();

    expect(clearWatch).toHaveBeenCalledWith(7);
  });
});
