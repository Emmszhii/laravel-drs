const PSGC_BASE = "https://psgc.gitlab.io/api";

type PsgcCity = {
  code: string;
  name: string;
  provinceCode: string | false;
  regionCode: string;
};
type PsgcProvince = { code: string; name: string; regionCode: string };

export type PsgcMatch = {
  regionCode: string;
  provinceCode: string | null;
  cityCode: string;
};

let citiesCache: Promise<PsgcCity[]> | null = null;
let provincesCache: Promise<PsgcProvince[]> | null = null;

function loadCitiesFlat(): Promise<PsgcCity[]> {
  citiesCache ??= fetch(`${PSGC_BASE}/cities-municipalities/`).then((r) => r.json());
  return citiesCache;
}

function loadProvincesFlat(): Promise<PsgcProvince[]> {
  provincesCache ??= fetch(`${PSGC_BASE}/provinces/`).then((r) => r.json());
  return provincesCache;
}

/** Loosely normalizes PH place names so "City of Legazpi" / "Legazpi City" / "legazpi" all line up. */
function normalize(name: string): string {
  return name
    .toLowerCase()
    .replace(/^city of /, "")
    .replace(/^municipality of /, "")
    .replace(/ city$/, "")
    .trim();
}

/** Matches a reverse-geocoded municipality/province name pair against the PSGC dataset, for auto-selecting the region/province/city dropdowns. */
export async function matchPsgcLocation(
  municipality?: string | null,
  province?: string | null,
): Promise<PsgcMatch | null> {
  if (!municipality) return null;

  const [cities, provinces] = await Promise.all([loadCitiesFlat(), loadProvincesFlat()]);
  const normMunicipality = normalize(municipality);
  let candidates = cities.filter((c) => normalize(c.name) === normMunicipality);

  if (candidates.length > 1 && province) {
    const normProvince = normalize(province);
    const matchedProvince = provinces.find((p) => normalize(p.name) === normProvince);
    if (matchedProvince) {
      const filtered = candidates.filter((c) => c.provinceCode === matchedProvince.code);
      if (filtered.length > 0) candidates = filtered;
    }
  }

  if (candidates.length === 0) return null;

  const city = candidates[0];
  return {
    regionCode: city.regionCode,
    provinceCode: city.provinceCode || null,
    cityCode: city.code,
  };
}
