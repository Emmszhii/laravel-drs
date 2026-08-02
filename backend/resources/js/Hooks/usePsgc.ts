import { useCallback, useState } from "react";

export type PsgcOption = { code: string; name: string };

const PSGC_BASE = "https://psgc.gitlab.io/api";

async function fetchJson(url: string): Promise<any[]> {
  const res = await fetch(url);
  if (!res.ok) throw new Error(`PSGC request failed: ${res.status}`);
  return res.json();
}

const sortByName = (items: PsgcOption[]) => items.slice().sort((a, b) => a.name.localeCompare(b.name));

/**
 * Cascading region → province → city/municipality → barangay selects
 * against the public PSGC API, mirroring the design prototype's
 * loadRegionsInto/loadProvincesInto/etc. Some regions (e.g. NCR) have no
 * provinces — cities sit directly under the region in that case.
 */
export default function usePsgc() {
  const [regions, setRegions] = useState<PsgcOption[]>([]);
  const [provinces, setProvinces] = useState<PsgcOption[]>([]);
  const [cities, setCities] = useState<PsgcOption[]>([]);
  const [barangays, setBarangays] = useState<PsgcOption[]>([]);
  const [loading, setLoading] = useState<string | null>(null);

  const loadRegions = useCallback(async () => {
    setLoading("regions");
    try {
      const data = await fetchJson(`${PSGC_BASE}/regions/`);
      setRegions(sortByName(data.map((r) => ({ code: r.code, name: r.name }))));
    } catch {
      setRegions([]);
    } finally {
      setLoading(null);
    }
  }, []);

  const loadProvinces = useCallback(async (regionCode: string) => {
    setProvinces([]);
    setCities([]);
    setBarangays([]);
    if (!regionCode) return;
    setLoading("provinces");
    try {
      const data = await fetchJson(`${PSGC_BASE}/regions/${regionCode}/provinces/`);
      const list = sortByName(data.map((p) => ({ code: p.code, name: p.name })));
      setProvinces(list);
      if (list.length === 0) {
        // Regions like NCR have no provinces — load cities directly.
        await loadCitiesForRegion(regionCode);
      }
    } catch {
      setProvinces([]);
    } finally {
      setLoading(null);
    }
  }, []);

  const loadCitiesForRegion = useCallback(async (regionCode: string) => {
    setLoading("cities");
    try {
      const data = await fetchJson(`${PSGC_BASE}/regions/${regionCode}/cities-municipalities/`);
      setCities(sortByName(data.map((c) => ({ code: c.code, name: c.name }))));
    } catch {
      setCities([]);
    } finally {
      setLoading(null);
    }
  }, []);

  const loadCities = useCallback(async (provinceCode: string) => {
    setCities([]);
    setBarangays([]);
    if (!provinceCode) return;
    setLoading("cities");
    try {
      const data = await fetchJson(`${PSGC_BASE}/provinces/${provinceCode}/cities-municipalities/`);
      setCities(sortByName(data.map((c) => ({ code: c.code, name: c.name }))));
    } catch {
      setCities([]);
    } finally {
      setLoading(null);
    }
  }, []);

  const loadBarangays = useCallback(async (cityCode: string) => {
    setBarangays([]);
    if (!cityCode) return;
    setLoading("barangays");
    try {
      const data = await fetchJson(`${PSGC_BASE}/cities-municipalities/${cityCode}/barangays/`);
      setBarangays(sortByName(data.map((b) => ({ code: b.code, name: b.name }))));
    } catch {
      setBarangays([]);
    } finally {
      setLoading(null);
    }
  }, []);

  return {
    regions,
    provinces,
    cities,
    barangays,
    loading,
    loadRegions,
    loadProvinces,
    loadCitiesForRegion,
    loadCities,
    loadBarangays,
  };
}
