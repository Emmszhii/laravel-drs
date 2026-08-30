import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook, waitFor } from "@testing-library/react";
import useOfflineSync from "./useOfflineSync";
import useOnlineStatus from "./useOnlineStatus";
import { syncOutbox } from "@/Offline/sync";
import { listAll } from "@/Offline/outbox";
import type { OutboxItem } from "@/Offline/db";

vi.mock("./useOnlineStatus", () => ({
  default: vi.fn(),
}));

vi.mock("@/Offline/sync", () => ({
  syncOutbox: vi.fn(),
}));

vi.mock("@/Offline/outbox", () => ({
  listAll: vi.fn(),
}));

const mockedUseOnlineStatus = vi.mocked(useOnlineStatus);
const mockedSyncOutbox = vi.mocked(syncOutbox);
const mockedListAll = vi.mocked(listAll);

function item(overrides: Partial<OutboxItem> = {}): OutboxItem {
  return {
    clientRef: "ref-1",
    kind: "incident",
    payload: {},
    status: "pending",
    createdAt: Date.now(),
    ...overrides,
  };
}

describe("useOfflineSync", () => {
  beforeEach(() => {
    mockedListAll.mockResolvedValue([]);
    mockedSyncOutbox.mockResolvedValue(undefined);
    mockedUseOnlineStatus.mockReturnValue(true);
    vi.stubGlobal("navigator", { onLine: true });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
    vi.useRealTimers();
    vi.clearAllMocks();
  });

  it("refreshes the outbox contents on mount", async () => {
    mockedListAll.mockResolvedValue([item({ clientRef: "a" })]);

    const { result } = renderHook(() => useOfflineSync());

    await waitFor(() => expect(result.current.items).toHaveLength(1));
    expect(mockedListAll).toHaveBeenCalled();
  });

  it("syncs on mount when online", async () => {
    renderHook(() => useOfflineSync());

    await waitFor(() => expect(mockedSyncOutbox).toHaveBeenCalled());
  });

  it("does not sync on mount when offline", async () => {
    mockedUseOnlineStatus.mockReturnValue(false);
    vi.stubGlobal("navigator", { onLine: false });

    renderHook(() => useOfflineSync());

    await waitFor(() => expect(mockedListAll).toHaveBeenCalled());
    expect(mockedSyncOutbox).not.toHaveBeenCalled();
  });

  it("computes pendingCount and failedCount from the loaded items", async () => {
    mockedListAll.mockResolvedValue([
      item({ clientRef: "a", status: "pending" }),
      item({ clientRef: "b", status: "syncing" }),
      item({ clientRef: "c", status: "failed" }),
    ]);

    const { result } = renderHook(() => useOfflineSync());

    await waitFor(() => expect(result.current.items).toHaveLength(3));
    expect(result.current.pendingCount).toBe(2); // pending + syncing (anything not "failed")
    expect(result.current.failedCount).toBe(1);
  });

  it("exposes the online flag from useOnlineStatus", () => {
    mockedUseOnlineStatus.mockReturnValue(false);
    vi.stubGlobal("navigator", { onLine: false });

    const { result } = renderHook(() => useOfflineSync());

    expect(result.current.online).toBe(false);
  });

  it("re-syncs when transitioning from offline to online", async () => {
    mockedUseOnlineStatus.mockReturnValue(false);
    vi.stubGlobal("navigator", { onLine: false });

    const { rerender } = renderHook(() => useOfflineSync());
    await waitFor(() => expect(mockedListAll).toHaveBeenCalled());
    expect(mockedSyncOutbox).not.toHaveBeenCalled();

    mockedUseOnlineStatus.mockReturnValue(true);
    vi.stubGlobal("navigator", { onLine: true });
    rerender();

    await waitFor(() => expect(mockedSyncOutbox).toHaveBeenCalled());
  });

  it("manual sync() calls syncOutbox and refreshes items", async () => {
    const { result } = renderHook(() => useOfflineSync());
    await waitFor(() => expect(mockedSyncOutbox).toHaveBeenCalledTimes(1));

    mockedListAll.mockResolvedValue([item({ clientRef: "z" })]);

    await act(async () => {
      await result.current.sync();
    });

    expect(mockedSyncOutbox).toHaveBeenCalledTimes(2);
    expect(result.current.items).toHaveLength(1);
  });

  it("manual sync() is a no-op when navigator.onLine is false", async () => {
    const { result } = renderHook(() => useOfflineSync());
    await waitFor(() => expect(mockedSyncOutbox).toHaveBeenCalledTimes(1));

    vi.stubGlobal("navigator", { onLine: false });
    mockedSyncOutbox.mockClear();

    await act(async () => {
      await result.current.sync();
    });

    expect(mockedSyncOutbox).not.toHaveBeenCalled();
  });

  it("polls syncOutbox again after the interval while navigator is online", async () => {
    vi.useFakeTimers();

    renderHook(() => useOfflineSync());

    await act(async () => {
      await vi.advanceTimersByTimeAsync(0);
    });
    expect(mockedSyncOutbox).toHaveBeenCalledTimes(1);

    await act(async () => {
      await vi.advanceTimersByTimeAsync(15000);
    });

    expect(mockedSyncOutbox).toHaveBeenCalledTimes(2);
  });

  it("does not poll-sync when navigator goes offline", async () => {
    vi.useFakeTimers();

    renderHook(() => useOfflineSync());
    await act(async () => {
      await vi.advanceTimersByTimeAsync(0);
    });
    mockedSyncOutbox.mockClear();

    vi.stubGlobal("navigator", { onLine: false });

    await act(async () => {
      await vi.advanceTimersByTimeAsync(15000);
    });

    expect(mockedSyncOutbox).not.toHaveBeenCalled();
  });
});
