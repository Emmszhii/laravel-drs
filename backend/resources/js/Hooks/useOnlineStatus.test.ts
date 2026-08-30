import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { act, renderHook } from "@testing-library/react";
import useOnlineStatus from "./useOnlineStatus";

describe("useOnlineStatus", () => {
  let originalOnLine: boolean;

  beforeEach(() => {
    originalOnLine = navigator.onLine;
  });

  afterEach(() => {
    vi.spyOn(navigator, "onLine", "get").mockRestore();
    Object.defineProperty(navigator, "onLine", {
      value: originalOnLine,
      configurable: true,
    });
  });

  it("reflects navigator.onLine as its initial value (online)", () => {
    vi.spyOn(navigator, "onLine", "get").mockReturnValue(true);

    const { result } = renderHook(() => useOnlineStatus());

    expect(result.current).toBe(true);
  });

  it("reflects navigator.onLine as its initial value (offline)", () => {
    vi.spyOn(navigator, "onLine", "get").mockReturnValue(false);

    const { result } = renderHook(() => useOnlineStatus());

    expect(result.current).toBe(false);
  });

  it("flips to false when the window 'offline' event fires", () => {
    vi.spyOn(navigator, "onLine", "get").mockReturnValue(true);

    const { result } = renderHook(() => useOnlineStatus());
    expect(result.current).toBe(true);

    act(() => {
      window.dispatchEvent(new Event("offline"));
    });

    expect(result.current).toBe(false);
  });

  it("flips to true when the window 'online' event fires", () => {
    vi.spyOn(navigator, "onLine", "get").mockReturnValue(false);

    const { result } = renderHook(() => useOnlineStatus());
    expect(result.current).toBe(false);

    act(() => {
      window.dispatchEvent(new Event("online"));
    });

    expect(result.current).toBe(true);
  });

  it("removes its event listeners on unmount", () => {
    const addSpy = vi.spyOn(window, "addEventListener");
    const removeSpy = vi.spyOn(window, "removeEventListener");

    const { unmount } = renderHook(() => useOnlineStatus());

    expect(addSpy).toHaveBeenCalledWith("online", expect.any(Function));
    expect(addSpy).toHaveBeenCalledWith("offline", expect.any(Function));

    unmount();

    expect(removeSpy).toHaveBeenCalledWith("online", expect.any(Function));
    expect(removeSpy).toHaveBeenCalledWith("offline", expect.any(Function));

    addSpy.mockRestore();
    removeSpy.mockRestore();
  });
});
