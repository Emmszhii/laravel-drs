type NominatimAddress = {
  village?: string;
  suburb?: string;
  neighbourhood?: string;
  city?: string;
  town?: string;
  municipality?: string;
  state?: string;
};

export type ReverseGeocodeResult = {
  display: string | null;
  barangay?: string;
  municipality?: string;
  province?: string;
};

/** Reverse-geocodes coordinates via Nominatim, returning both a display string ("Barangay, City/Municipality, Province") and the individual components for PSGC matching. */
export async function reverseGeocode(
  lat: number,
  lng: number,
): Promise<ReverseGeocodeResult> {
  try {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&countrycodes=ph`;
    const res = await fetch(url);
    const result = await res.json();
    const address: NominatimAddress | undefined = result?.address;
    if (!address) return { display: result?.display_name ?? null };

    const barangay = address.village ?? address.suburb ?? address.neighbourhood;
    const municipality = address.city ?? address.town ?? address.municipality;
    const province = address.state;

    const parts = [barangay, municipality, province].filter(Boolean);
    const display = parts.length > 0 ? parts.join(", ") : (result?.display_name ?? null);

    return { display, barangay, municipality, province };
  } catch {
    return { display: null };
  }
}
