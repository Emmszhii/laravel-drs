export type PageProps<T = {}> = T & {
  auth: {
    user: { id: string; name: string; email: string; role: string; is_system_admin: boolean } | null;
  };
};

/** Shared props every Inertia page receives (see HandleInertiaRequests) — augments usePage()'s default typing. */
declare module '@inertiajs/core' {
  interface PageProps {
    auth?: {
      user:
        | ({ id: string; name: string; email: string; role: string; is_system_admin: boolean } & {
            roles?: string[];
            permissions?: string[];
          })
        | null;
    };
  }
}

export interface Depot {
  [key: string]: unknown;
  id: string;
  name: string;
  address: string;
  status: "OPERATIONAL" | "LIMITED CAPACITY" | "OFFLINE";
  /** m³; only used when depot space is modeled. */
  free_space: number | null;
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
  is_consumable: boolean;
  unit_cost: number | null;
  ved_class: "V" | "E" | "D";
  space_per_unit: number | null;
}

export interface AdminUser {
  [key: string]: unknown;
  id: string;
  first_name: string;
  last_name: string;
  name: string;
  email: string;
  role: "viewer" | "incident_management" | "ert" | "logistics" | "operations" | "research_planning" | "admin_training";
  is_active: boolean;
  is_system_admin: boolean;
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

// --- Quarterly replenishment (backend/docs/system-spec.md §3-4) ---

export interface ReplenishmentOrder {
  id: string;
  x: number;
  target: number | null;
  shortfall: number;
  shortfall_pct: number;
  reason: string;
  basis: "multiplier" | "limited_history" | "given" | "no_history" | "no_unit_cost";
  multiplier: number | null;
}

export interface BudgetCurvePoint {
  pct: number;
  budget: number;
  /** null when the solver didn't finish this level within its time limit (solved = false). */
  expected_shortage: number | null;
  objective: number | null;
  solved: boolean;
  status: string;
}

export interface ReplenishmentResult {
  status: "optimal" | "feasible" | "infeasible";
  rule: string;
  orders: ReplenishmentOrder[];
  objective: number | null;
  spent: number;
  binding: { budget: boolean; space: boolean };
  space_modeled: boolean;
  solve_ms: number;
  budget_curve?: BudgetCurvePoint[];
  next_step?: {
    /** false when the larger-budget solve didn't finish; changes is then []. */
    solved: boolean;
    amount: number;
    changes: { id: string; delta_x: number; shortfall_pct_before: number; shortfall_pct_after: number }[];
  };
}

export type ReplenishmentRunStatus = "queued" | "completed" | "failed" | "approved" | "rejected" | "superseded";

export interface ReplenishmentRun {
  id: string;
  year: number;
  quarter: number;
  budget: number;
  rule: string;
  status: ReplenishmentRunStatus;
  result: ReplenishmentResult | null;
  error: string | null;
  decided_at: string | null;
  decision_comment: string | null;
  created_at: string;
}

export interface DataCheckFlag {
  id: string;
  rule: string;
  resource_type_id: string;
  resource_type?: { id: string; name: string };
  year: number;
  quarter: number;
  detail: string;
  status: "open" | "confirmed" | "corrected";
}

export interface VedPolicy {
  ved_class: "V" | "E" | "D";
  coverage_k: number;
  coverage_m: number;
  weight: number;
  confirmed_by_position: string | null;
  confirmed_at: string | null;
}

export interface BacktestRuleScore {
  fill_rate: number;
  weighted_shortage: number;
  items_ran_out: number;
  budget_used: number;
  stock_left_value: number;
  feasible: boolean;
  solve_ms: number;
}

export interface Backtest {
  id: string;
  budget: number;
  quarters: [number, number][];
  created_at: string;
  result: {
    quarters: { t: number; budget: number; typhoon: boolean; stockout: boolean; rules: Record<string, BacktestRuleScore> }[];
    summary: {
      backtested_quarters: number;
      wins_vs_flat: Record<"per_item_multiplier" | "scenario", number>;
      target_met: Record<"per_item_multiplier" | "scenario", boolean>;
    };
  };
}
