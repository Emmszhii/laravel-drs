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
