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
  type: "Typhoon" | "Flood" | "Earthquake" | "Fire" | "Landslide" | "Volcano" | "Drought";
  signal: 1 | 2 | 3 | 4 | 5;
  zone: string;
  lat: number;
  lng: number;
  status: "ACTIVE" | "MONITORING" | "RESOLVED";
  zones?: Zone[];
  created_at?: string;
  /** null = manually logged; otherwise which feed created/adopted this incident. */
  source?: "usgs" | "gdacs" | null;
  external_id?: string | null;
  source_url?: string | null;
  /** Latest solver run only (see Api\IncidentController's optimizationRuns eager-load). */
  optimization_runs?: OptimizationRun[];
}

export interface OptimizationRun {
  id: string;
  disaster_id: string;
  algorithm_used: string;
  objective_value: number | null;
  status: "completed" | "failed";
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
  /** NDRRMC/Sendai DRR-standard indicator fields — see Disaster::ndrrmcSummary(). */
  casualties_dead: number;
  casualties_injured: number;
  casualties_missing: number;
  families_affected: number;
  houses_totally_damaged: number;
  houses_partially_damaged: number;
  estimated_damage_cost: number | null;
  damage_cost_currency: string;
}

/** GET /api/evacuation-centers. */
export interface EvacuationCenter {
  [key: string]: unknown;
  id: string;
  disaster_id: string;
  zone_id: string | null;
  name: string;
  address: string | null;
  lat: number;
  lng: number;
  capacity: number | null;
  families_served: number;
  individuals_served: number;
  status: "OPEN" | "CLOSED";
  disaster?: Disaster;
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

/** One depot-to-destination resource-in-transit trip — GET /api/dispatches. */
export interface Dispatch {
  [key: string]: unknown;
  id: string;
  disaster_id: string;
  zone_id: string | null;
  depot_id: string;
  allocation_id: string | null;
  resource_type_id: string | null;
  driver_id: string;
  vehicle_label: string | null;
  status: "pending" | "en_route" | "delivered" | "cancelled";
  origin_lat: number;
  origin_lng: number;
  destination_lat: number;
  destination_lng: number;
  current_lat: number | null;
  current_lng: number | null;
  last_position_at: string | null;
  dispatched_at: string | null;
  delivered_at: string | null;
  depot?: Depot;
  driver?: { id: string; name: string };
  disaster?: Disaster;
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
  type: "After-action summary" | "Resource utilization" | "Response timeline" | "NDRRMC SitRep";
  generated_at: string;
  disaster?: Disaster;
}

export interface ResourceRequest {
  [key: string]: unknown;
  id: string;
  zone_id: string;
  resource_type_id: string;
  quantity_requested: number;
  priority: "low" | "medium" | "high" | "critical";
  status: "pending" | "enroute" | "unmet" | "fulfilled";
  zone?: Zone & { disaster?: Disaster };
  resource_type?: ResourceType;
}

/** Aggregate dashboard stats — GET / (Web\DashboardController). */
export interface DashboardStats {
  unmet_pct: number;
  deployed: number;
}

/** Per-resource-type stock utilization — GET / (Web\DashboardController). */
export interface UtilizationRow {
  id: string;
  name: string;
  unit: string;
  pct: number;
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

export interface SmtpSettingsData {
  is_enabled: boolean;
  host: string | null;
  port: number | null;
  username: string | null;
  password_set: boolean;
  encryption: "tls" | "ssl" | null;
  from_address: string | null;
  from_name: string | null;
  is_configured: boolean;
  last_test_status: "ok" | "failed" | null;
  last_test_at: string | null;
}

export interface DisasterFeedSettingsData {
  usgs_enabled: boolean;
  usgs_min_magnitude: number;
  gdacs_enabled: boolean;
  gdacs_min_alert_level: "Green" | "Orange" | "Red";
  poll_interval_minutes: number;
  poll_interval_options: number[];
  auto_resolve_enabled: boolean;
  auto_resolve_max_days: number;
  last_run_at: string | null;
  last_run_status: "ok" | "partial" | "failed" | null;
  last_run_error: string | null;
  last_run_created_count: number;
  last_run_updated_count: number;
}
