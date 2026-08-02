export type PageProps<T = {}> = T & {
  auth: {
    user: { id: string; name: string; email: string; role: string } | null;
  };
};

/** Shared props every Inertia page receives (see HandleInertiaRequests) — augments usePage()'s default typing. */
declare module '@inertiajs/core' {
  interface PageProps {
    auth?: {
      user:
        | ({ id: string; name: string; email: string; role: string } & {
            roles?: string[];
            permissions?: string[];
          })
        | null;
    };
  }
}

export interface Disaster {
  id: string;
  name: string;
  type: "Typhoon" | "Flood" | "Earthquake" | "Fire" | "Landslide";
  signal: 1 | 2 | 3 | 4 | 5;
  zone: string;
  lat: number;
  lng: number;
  status: "ACTIVE" | "MONITORING" | "RESOLVED";
  zones?: Zone[];
  created_at?: string;
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
  [key: string]: unknown;
  id: string;
  name: string;
  address: string;
  status: "OPERATIONAL" | "LIMITED CAPACITY" | "OFFLINE";
  lat: number;
  lng: number;
  contact_first_name?: string;
  contact_last_name?: string;
  contact_email?: string;
  contact_phone?: string;
}

export interface ResourceType {
  [key: string]: unknown;
  id: string;
  name: string;
  unit: string;
}

/** A stock (depot inventory) row — GET /api/resources. */
export interface ResourceStock {
  [key: string]: unknown;
  id: string;
  depot_id: string;
  resource_type_id: string;
  quantity_available: number;
  quantity_reserved: number;
  expiry_date: string | null;
  depot?: Depot;
  resource_type?: ResourceType;
}

export interface AdminUser {
  [key: string]: unknown;
  id: string;
  first_name: string;
  last_name: string;
  name: string;
  email: string;
  role: "developer" | "admin" | "coordinator" | "viewer" | "respondent";
  is_active: boolean;
}

export interface Report {
  [key: string]: unknown;
  id: string;
  disaster_id: string | null;
  name: string;
  type: "After-action summary" | "Resource utilization" | "Response timeline";
  generated_at: string;
  disaster?: Disaster;
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
