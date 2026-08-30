import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { act, renderHook } from "@testing-library/react";
import usePsgc from "./usePsgc";

function jsonResponse(data: unknown, ok = true) {
  return {
    ok,
    status: ok ? 200 : 500,
    json: async () => data,
  } as Response;
}

describe("usePsgc", () => {
  let fetchMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    fetchMock = vi.fn();
    vi.stubGlobal("fetch", fetchMock);
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it("starts with empty cascades and no loading", () => {
    const { result } = renderHook(() => usePsgc());

    expect(result.current.regions).toEqual([]);
    expect(result.current.provinces).toEqual([]);
    expect(result.current.cities).toEqual([]);
    expect(result.current.barangays).toEqual([]);
    expect(result.current.loading).toBeNull();
  });

  it("loadRegions() fetches and sorts regions by name", async () => {
    fetchMock.mockResolvedValue(
      jsonResponse([
        { code: "02", name: "Zeta Region" },
        { code: "01", name: "Alpha Region" },
      ]),
    );

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadRegions();
    });

    expect(fetchMock).toHaveBeenCalledWith("https://psgc.gitlab.io/api/regions/");
    expect(result.current.regions).toEqual([
      { code: "01", name: "Alpha Region" },
      { code: "02", name: "Zeta Region" },
    ]);
    expect(result.current.loading).toBeNull();
  });

  it("loadRegions() clears regions on a failed request", async () => {
    fetchMock.mockResolvedValue(jsonResponse(null, false));

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadRegions();
    });

    expect(result.current.regions).toEqual([]);
    expect(result.current.loading).toBeNull();
  });

  it("loadProvinces() fetches provinces for a region and resets deeper cascades", async () => {
    fetchMock.mockResolvedValue(
      jsonResponse([{ code: "p1", name: "Laguna" }]),
    );

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadProvinces("04");
    });

    expect(fetchMock).toHaveBeenCalledWith("https://psgc.gitlab.io/api/regions/04/provinces/");
    expect(result.current.provinces).toEqual([{ code: "p1", name: "Laguna" }]);
    expect(result.current.cities).toEqual([]);
    expect(result.current.barangays).toEqual([]);
  });

  it("loadProvinces() with an empty region code does not call fetch", async () => {
    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadProvinces("");
    });

    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("loadProvinces() falls back to loading cities directly when a region has no provinces (e.g. NCR)", async () => {
    fetchMock
      .mockResolvedValueOnce(jsonResponse([])) // provinces: empty
      .mockResolvedValueOnce(jsonResponse([{ code: "c1", name: "Manila" }])); // cities for region

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadProvinces("13");
    });

    expect(fetchMock).toHaveBeenNthCalledWith(1, "https://psgc.gitlab.io/api/regions/13/provinces/");
    expect(fetchMock).toHaveBeenNthCalledWith(
      2,
      "https://psgc.gitlab.io/api/regions/13/cities-municipalities/",
    );
    expect(result.current.provinces).toEqual([]);
    expect(result.current.cities).toEqual([{ code: "c1", name: "Manila" }]);
  });

  it("loadCities() fetches cities for a province", async () => {
    fetchMock.mockResolvedValue(jsonResponse([{ code: "c2", name: "Santa Rosa" }]));

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadCities("p1");
    });

    expect(fetchMock).toHaveBeenCalledWith(
      "https://psgc.gitlab.io/api/provinces/p1/cities-municipalities/",
    );
    expect(result.current.cities).toEqual([{ code: "c2", name: "Santa Rosa" }]);
  });

  it("loadBarangays() fetches barangays for a city", async () => {
    fetchMock.mockResolvedValue(jsonResponse([{ code: "b1", name: "Brgy 1" }]));

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadBarangays("c2");
    });

    expect(fetchMock).toHaveBeenCalledWith(
      "https://psgc.gitlab.io/api/cities-municipalities/c2/barangays/",
    );
    expect(result.current.barangays).toEqual([{ code: "b1", name: "Brgy 1" }]);
  });

  it("loadBarangays() with an empty city code does not call fetch", async () => {
    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadBarangays("");
    });

    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("reset() clears provinces, cities, and barangays but keeps regions", async () => {
    fetchMock
      .mockResolvedValueOnce(jsonResponse([{ code: "01", name: "Region 1" }]))
      .mockResolvedValueOnce(jsonResponse([{ code: "p1", name: "Province 1" }]));

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadRegions();
    });
    await act(async () => {
      await result.current.loadProvinces("01");
    });

    expect(result.current.provinces).toHaveLength(1);

    act(() => {
      result.current.reset();
    });

    expect(result.current.regions).toHaveLength(1);
    expect(result.current.provinces).toEqual([]);
    expect(result.current.cities).toEqual([]);
    expect(result.current.barangays).toEqual([]);
  });

  it("swallows a thrown fetch error and clears the corresponding list", async () => {
    fetchMock.mockRejectedValue(new Error("network down"));

    const { result } = renderHook(() => usePsgc());

    await act(async () => {
      await result.current.loadRegions();
    });

    expect(result.current.regions).toEqual([]);
    expect(result.current.loading).toBeNull();
  });
});
