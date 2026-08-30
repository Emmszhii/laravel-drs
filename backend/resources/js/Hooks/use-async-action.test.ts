import { describe, it, expect, vi } from "vitest";
import { act, renderHook, waitFor } from "@testing-library/react";
import { useAsyncAction } from "./use-async-action";

describe("useAsyncAction", () => {
  it("starts with loading false", () => {
    const fn = vi.fn().mockResolvedValue(undefined);
    const { result } = renderHook(() => useAsyncAction(fn));

    expect(result.current.loading).toBe(false);
  });

  it("sets loading true while the action is in flight, then false once it resolves", async () => {
    let resolveFn: () => void;
    const fn = vi.fn(
      () =>
        new Promise<void>((resolve) => {
          resolveFn = resolve;
        }),
    );
    const { result } = renderHook(() => useAsyncAction(fn));

    act(() => {
      result.current.run();
    });

    expect(result.current.loading).toBe(true);

    await act(async () => {
      resolveFn();
      await Promise.resolve();
    });

    await waitFor(() => expect(result.current.loading).toBe(false));
  });

  it("passes arguments through to the wrapped function", async () => {
    const fn = vi.fn().mockResolvedValue(undefined);
    const { result } = renderHook(() => useAsyncAction(fn));

    await act(async () => {
      await result.current.run("a", 2);
    });

    expect(fn).toHaveBeenCalledWith("a", 2);
  });

  it("resets loading to false even when the action throws", async () => {
    const fn = vi.fn().mockRejectedValue(new Error("boom"));
    const { result } = renderHook(() => useAsyncAction(fn));

    await act(async () => {
      await expect(result.current.run()).rejects.toThrow("boom");
    });

    expect(result.current.loading).toBe(false);
  });

  it("ignores a second call while the first is still in flight (prevents double-fire)", async () => {
    let resolveFn: () => void;
    const fn = vi.fn(
      () =>
        new Promise<void>((resolve) => {
          resolveFn = resolve;
        }),
    );
    const { result } = renderHook(() => useAsyncAction(fn));

    act(() => {
      result.current.run();
    });
    expect(fn).toHaveBeenCalledTimes(1);

    await act(async () => {
      // second call while loading — should be a no-op
      await result.current.run();
    });
    expect(fn).toHaveBeenCalledTimes(1);

    await act(async () => {
      resolveFn();
      await Promise.resolve();
    });

    await waitFor(() => expect(result.current.loading).toBe(false));
  });

  it("allows running again after a previous call has finished", async () => {
    const fn = vi.fn().mockResolvedValue(undefined);
    const { result } = renderHook(() => useAsyncAction(fn));

    await act(async () => {
      await result.current.run();
    });
    await act(async () => {
      await result.current.run();
    });

    expect(fn).toHaveBeenCalledTimes(2);
  });
});
