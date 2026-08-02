export type PageProps<T = {}> = T & {
  auth: {
    user: { id: string; name: string; email: string; role: string } | null;
  };
};

export interface Disaster {
  id: string;
  name: string;
  type: "Typhoon" | "Flood" | "Earthquake" | "Fire" | "Landslide";
  signal: 1 | 2 | 3 | 4 | 5;
  zone: string;
  lat: number;
  lng: number;
  status: "ACTIVE" | "MONITORING" | "RESOLVED";
}

export interface Zone {
  id: string;
  disaster_id: string;
  name: string;
  population_affected: number;
  severity_level: "High" | "Medium";
  lat: number;
  lng: number;
}

export interface Depot {
  id: string;
  name: string;
  address: string;
  status: "OPERATIONAL" | "LIMITED CAPACITY" | "OFFLINE";
  lat: number;
  lng: number;
  contact_name?: string;
  contact_email?: string;
  contact_phone?: string;
}

export interface ResourceType {
  id: string;
  name: string;
  unit: string;
}

export interface ResourceRequest {
  id: string;
  zone_id: string;
  resource_type_id: string;
  quantity_requested: number;
  priority: "low" | "medium" | "high" | "critical";
  status: "pending" | "enroute" | "unmet" | "fulfilled";
}

/** Minimal GeoJSON point, for future PostGIS-backed API responses. */
export interface GeoJsonPoint {
  type: "Point";
  coordinates: [number, number]; // [lng, lat]
}

export interface DirectionsStep {
  instruction: string;
  distance_m: number;
  duration_s: number;
  maneuver_type: string;
  maneuver_modifier: string | null;
  location: [number, number]; // [lng, lat]
}

/** Response shape from GET /api/directions — see DirectionsRouter (backend). */
export interface DirectionsResult {
  ok: boolean;
  error?: string;
  distance_m?: number;
  duration_s?: number;
  geometry?: [number, number][]; // [lng, lat] pairs
  steps?: DirectionsStep[];
  provider?: "osrm" | "premium";
  fallback_reason?: "cap_exceeded" | "provider_error";
}

export interface RoutingSettingsData {
  active_provider: "osrm" | "premium";
  premium_base_url: string | null;
  premium_api_key_masked: string | null;
  premium_monthly_cap: number | null;
  usage_period: string | null;
  usage_count: number;
  fallback_triggered_at: string | null;
  fallback_reason: "cap_exceeded" | "provider_error" | null;
  last_test_status: "ok" | "failed" | null;
  last_test_at: string | null;
}
