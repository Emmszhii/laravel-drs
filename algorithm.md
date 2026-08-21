# Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS

Target architecture:

| Component | Tech | Target role |
|---|---|---|
| Matrix & Routing | OSRM (MLD Algorithm) | Supplies fast distance/duration matrices between Depots and Incidents |
| Live Navigation | Mapbox Directions v5 (`driving-traffic`) via `PremiumDirectionsProvider`, OSRM as static fallback | Turn-by-turn, traffic-aware driving guidance for a driver en route to a depot/incident, with live position tracking and best-of-N route selection |
| Optimization Engine | Google OR-Tools / Python (PuLP) | Solves supply allocation (MILP) and vehicle dispatch (CVRPTW) |
| Backend API | Laravel | Receives incident reports, queries OSRM, runs the optimizer, and saves dispatches |
| Database | PostgreSQL + PostGIS | Stores spatial geometry of depots, inventory levels, road closures, and request statuses |

This document is a phased roadmap for closing the gap between that target and the current codebase. It does not change any code — it's the plan to work from.

## 0. Current state (what's real vs. skeleton vs. missing)

### Optimization Engine — the biggest gap

- `solver-service/app/solvers/pulp_solver.py:6-54` — `PulpAllocationSolver` is a working transportation LP: minimize `Σ unmet[z]` subject to per-zone demand balance and per-depot capacity. It answers "how much of each resource should each depot ship to each zone" — a real, tested piece of the MILP half of the Optimization Engine row.
- **Bug**: `backend/app/Services/Optimization/OptimizationServiceClient.php:33-46` builds the payload to the solver from `Depot::all()->map()`, projecting only `id/lat/lng` — **depot `capacity` is never sent**. Combined with `pulp_solver.py:38`'s `if d.capacity > 0` guard, every real allocation call today silently runs with the capacity constraint disabled.
- `solver-service/app/solvers/ortools_solver.py:7-53` — `ORToolsRoutingSolver` builds a `RoutingIndexManager`/`RoutingModel` over depots+zones with a haversine transit callback, but registers **no vehicle capacity dimension and no time-window dimension**, and its NOTE comment (`ortools_solver.py:45-47`) confirms it **always returns `allocations: []`** — it computes an internal distance-optimal tour and discards it. This is not CVRPTW; it's a distance-minimization skeleton. Laravel never even calls this endpoint — `RunOptimizationJob.php:32` only POSTs to `/allocation`.
- `solver-service/app/models/schemas.py` — only one contract pair, `AllocationRequest`/`AllocationResponse`, reused verbatim for both `/allocation` and `/routing`. No `Vehicle` entity, no time-window field, anywhere in the schema — there's structurally nowhere to put CVRPTW inputs yet.

### Matrix & Routing

- OSRM is confirmed built with **MLD** (`osrm-partition`/`osrm-customize` in `docker/osrm/prepare/entrypoint.sh:41-45`, and `osrm-routed --algorithm mld` in `docker-compose.yml:232`), preprocessed from an OSM extract clipped to a Laguna province bounding box (`entrypoint.sh:17-19`) — routing only works within that box.
- The app only ever calls OSRM's single-pair `/route/v1/driving/{2 coords}` endpoint (`backend/app/Services/Directions/OsrmDirectionsProvider.php:23-35`, via `DirectionsController.php`). OSRM's `/table` (many-to-many matrix) service is **never invoked anywhere** in the codebase.
- Every distance figure that actually feeds the optimizer today is **haversine straight-line distance** — in `ortools_solver.py:56-69` (`_distance_matrix`) and in the Laravel-side fallback path (`backend/app/Services/Gis/GeofenceService.php:17-32`, used by `GreedyAllocationSolver.php`). OSRM is completely decoupled from the optimization pipeline; it's only wired to the human-facing turn-by-turn navigation panel.

### Live navigation — no traffic, no auto-advance, but the position plumbing already exists

- Neither routing engine is traffic-aware today. `OsrmDirectionsProvider` is confirmed static (its own docblock: "no live traffic", `OsrmDirectionsProvider.php:10-12`). `PremiumDirectionsProvider` calls Mapbox's `.../directions/v5/mapbox/driving/{coords}` — the plain **`driving` profile, not `driving-traffic`** (`PremiumDirectionsProvider.php:26`) — so even the paid tier isn't pulling live conditions, and neither provider requests `alternatives=true`, so there's no "best of N routes" choice happening anywhere; `DirectionsRouter` picks a *provider* (premium vs. OSRM), never a *route*.
- `useDirections.ts` fetches a route once per origin/destination pair (`:54`) with no refresh loop, so a route never updates as traffic changes even if the provider had traffic data.
- `useVoiceGuidance.ts` explicitly defers GPS auto-advance (own docblock, `:3-9`) — today the driver taps Prev/Next in `DirectionsPanel.tsx` to move through steps; nothing advances automatically off device position.
- `IncidentMap.tsx` only takes a **one-shot** `navigator.geolocation.getCurrentPosition()` (`:101-117`) to seed the route's starting point — there's no live "you are here" marker while navigating and no continuous tracking on this screen.
- Continuous GPS *does* already exist, just for a different feature: `useLiveGeolocation.ts` wraps `navigator.geolocation.watchPosition` with an 8s throttle and POSTs to `DispatchController::updatePosition` (`DispatchController.php:75-98`), which broadcasts `DispatchPositionUpdated` — built for the dispatch-tracking board, not wired into the navigation panel. This is the natural mechanism to reuse for turn-by-turn auto-advance rather than building a second GPS watcher.

### Backend API

- `Dispatch` already exists as a full model + table + CRUD + live GPS tracking (`backend/app/Models/Dispatch.php`, `backend/app/Http/Controllers/Api/DispatchController.php` — `updatePosition()`, `updateStatus()`). This is **not** a greenfield addition.
- But `Dispatch.allocation_id` is nullable and unused in practice — dispatches today are logged manually via a form (driver + depot + destination lat/lng), never auto-generated from an `OptimizationRun`/`Allocation`.
- No `Vehicle` model/table exists — only a free-text `vehicle_label` string column on `Dispatch`. No fleet capacity/type tracking.
- No `Route` model/table exists — `Dispatch` stores only `origin_lat/lng`, `destination_lat/lng`, and a live `current_lat/lng`; there's no ordered multi-stop list or persisted route polyline.
- Request approval has no endpoint at all — `backend/app/Http/Controllers/Api/RequestController.php` only has `index()`/`store()`. This is a real, uncoded gap (not commented-out code to restore).
- Correction for the record: `CLAUDE.md` currently claims depot/resource-type/request-approval routes are "commented out" in `routes/web.php`/`routes/api.php`. That's stale — depot and resource-type routes are fully live today; only request-approval is genuinely missing. Worth fixing in `CLAUDE.md` separately; out of scope here.

### Database

- PostGIS `geography` columns are present only as **commented-out** lines in all three relevant migrations: `app_disasters` and `app_zones` (`backend/database/migrations/2026_01_01_000001_create_disasters_and_zones_tables.php:19,33`) and `app_depots` (`.../2026_01_01_000002_create_depots_and_resources_tables.php:18`). Active columns are plain `double lat`/`double lng`. The PostGIS extension itself is not enabled anywhere in the schema.
- **Road closures are entirely unmodeled** — no table, no column, no service logic anywhere in `app/` or `database/`.

## Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver

Goal: make the existing MILP allocation solver actually respect depot capacity, and replace haversine with real road-network distances.

1. Fix `OptimizationServiceClient.php:33-46` to include depot capacity in the payload — derive it from `Resource::quantity_available - quantity_reserved`, summed per depot across its resource types (`Depot::resources()`).
2. Add a small OSRM matrix client to solver-service: `solver-service/app/services/osrm_matrix.py`, calling OSRM's `/table/v1/driving/{coords}?sources=...&destinations=...&annotations=distance,duration`. Solver-service and the `osrm` container already share the docker-compose network (OSRM on port 5001), so this is a direct HTTP call, no new infra.
3. Replace `ortools_solver.py:56-69`'s pure-haversine `_distance_matrix` with a call to this new client, falling back to haversine on failure or when coordinates fall outside the Laguna bbox — mirroring `OsrmDirectionsProvider`'s existing never-throw convention (`['ok' => bool, ...]`-style result).

## Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP

Goal: make `/api/v1/optimize/routing` actually produce vehicle routes instead of discarding its solution.

1. Extend `schemas.py` with a `VehicleSpec` (id, capacity, home `depot_id`) and dedicated `RoutingRequest`/`RoutingResponse` schemas — stop reusing `AllocationRequest`/`AllocationResponse` for routing. `RoutingResponse.routes` should be `[{vehicle_id, stops: [{location_id, arrival_time, quantity}]}]`.
2. Two-stage pipeline: keep `PulpAllocationSolver` deciding *how much* each depot ships to each zone (Phase 1's fixed capacity constraint), then feed those depot→zone quantities into `ORToolsRoutingSolver` as delivery demands.
3. In `ORToolsRoutingSolver`, add a real capacity dimension (`AddDimensionWithVehicleCapacity`) and a time-window dimension (`AddDimension` + `SetTimeWindow` per stop), and actually walk the solved model via `routing.NextVar` to emit stop sequences — this is the piece the current NOTE flags as missing.
4. Wire `RunOptimizationJob.php` to call `/api/v1/optimize/routing` after `/allocation` completes.

## Phase 3 — Persist routing output as real dispatches; add a Vehicle model

Goal: close the gap where `Dispatch.allocation_id` exists but is never populated.

1. New migration + `Vehicle` model: `label`, `capacity`, home `depot_id`, `status`. Migrate the existing `Dispatch.vehicle_label` free-text data into proper `Vehicle` rows and swap the column for a `vehicle_id` FK.
2. Have `RunOptimizationJob` (or a new `RunDispatchPlanningJob`) persist each CVRPTW stop from Phase 2 as a `Dispatch` row with `allocation_id` and `vehicle_id` set, status `pending` — so dispatches become optimizer output instead of purely manual entries.
3. Flag, don't build yet: the request-approval endpoint gap (`RequestController`) is a product decision — needed if allocations should only draw from *approved* requests rather than all pending ones.

## Phase 4 — PostGIS + road closures

Goal: satisfy the Database row's "spatial geometry ... road closures" scope.

1. Enable the PostGIS extension in the Postgres bootstrap script under `docker/`. Add **new** migrations (don't edit the old commented-out ones) that add `geography` columns to `app_disasters`, `app_zones`, `app_depots`, and backfill them from the existing `lat`/`lng` columns.
2. Swap `GeofenceService::distanceKm()` for a native `ST_Distance` query when PostGIS is available, keeping the current haversine implementation as a fallback — this matches the TODO already in that file's docblock.
3. New `app_road_closures` table: point/segment geometry, radius, active time window. **MVP approach** (flagged as such — real-time OSRM avoidance would require re-running `osrm-partition`/`osrm-customize`, which takes minutes and isn't feasible per-closure): post-hoc check with `ST_DWithin` of a computed route's geometry against active closures, surfaced to the coordinator as "route may be blocked" rather than live OSRM rerouting.

## Phase 5 — Traffic-aware "best path to depot" + Google Maps-style turn-by-turn navigation

Goal: give a driver an actual navigation experience — live traffic on the route, automatic selection of the fastest path (not just the shortest), and hands-free step advancement as they drive to a depot or incident — reusing the GPS plumbing `useLiveGeolocation.ts` already has for dispatch tracking instead of inventing a second one.

1. **Traffic-aware routing.** Switch `PremiumDirectionsProvider`'s URL from the `driving` profile to `driving-traffic` (`PremiumDirectionsProvider.php:26`) and request `alternatives=true&annotations=congestion,duration`. Extend the shared `DirectionsProvider` contract (`DirectionsProvider.php:10-22`) with an optional `routes: [{geometry, distance_m, duration_s, duration_in_traffic_s, congestion: [...]}]` array alongside the existing single-route fields, so `OsrmDirectionsProvider` (which has none of this) can keep returning `routes: null` without breaking the interface.
2. **Best-path selection.** In `DirectionsRouter::getRoute()` (`DirectionsRouter.php`), when the provider returns multiple `routes`, pick the one with the lowest `duration_in_traffic_s` (falling back to `duration_s` when traffic data is absent) as the route surfaced to the frontend — this is the "select the best path to go to the depot" behavior, computed server-side so the frontend doesn't need routing logic. Add a `route_choice_reason` field (`fastest_with_traffic` | `only_option`) alongside the existing `fallback_reason`, following that field's precedent.
3. **Refresh loop, not one-shot.** Change `useDirections.ts` (`:54`) from a single fetch-on-mount to a polling interval (e.g. every 60–120s) while a navigation session is active, so `duration_in_traffic_s` and the chosen route can change mid-drive the way Google Maps re-routes — but only re-draw the polyline if the newly chosen route actually differs, to avoid flicker on the map.
4. **Live "you are here" + auto-advance.** Reuse `useLiveGeolocation.ts`'s `watchPosition` (currently only feeding `DispatchController::updatePosition`) as a shared hook consumed by `DirectionsPanel.tsx`/`IncidentMap.tsx` too: drop the one-shot `getCurrentPosition()` in `IncidentMap.tsx:101-117` for a live marker that moves during navigation, and in `useVoiceGuidance.ts` replace the deferred-GPS note (`:3-9`) with distance-to-next-maneuver logic — advance `currentStepIndex` automatically once the driver's live position falls within a threshold radius of the next step's `location`, instead of requiring a manual Next tap. Manual Prev/Next stays as an override.
5. **Traffic on the map.** Draw route polylines as colored segments by congestion level (using the `annotations=congestion` data from step 1) instead of the current flat `#f2a63c` line (`IncidentMap.tsx:264-289`) — e.g. green/amber/red per segment, matching the Google Maps convention the user is asking for.
6. **Secure-context caveat, unresolved:** `watchPosition` requires HTTPS (`useVoiceGuidance.ts`'s own docblock already flags this for `navigator.geolocation` generally). This phase only works once the app is served over HTTPS in whichever environment it runs — local HTTP dev will need a workaround (e.g. mkcert) or this phase degrades back to manual step advancement.

## Verification (once each phase is implemented)

- **Phase 1**: `pytest solver-service/tests/test_solvers.py` should show the capacity-insufficient test (`test_pulp_solver_reports_unmet_demand_when_capacity_insufficient`) actually exercising a real capacity constraint end-to-end from a Laravel-triggered run, not just the unit fixture. Manually trigger `/incidents/{disaster}/optimize` and diff the `OptimizationRun.response_payload` before/after the capacity fix.
- **Phase 2**: New unit tests for `ORToolsRoutingSolver` asserting non-empty `routes` with valid capacity/time-window adherence; a manual `POST /api/v1/optimize/routing` call against a multi-zone/multi-depot fixture.
- **Phase 3**: After a live optimization run, confirm new `Dispatch` rows appear on the `/dispatch` Inertia board with `allocation_id`/`vehicle_id` populated, and that `updatePosition()`/`updateStatus()` still work unchanged on optimizer-generated dispatches.
- **Phase 4**: Confirm `SELECT PostGIS_version()` succeeds in the `postgres` container; confirm `ST_DWithin`-based closure checks flag a deliberately-placed test closure against a known route.
- **Phase 5**: Manually trigger `/api/directions` between two points with known differing-congestion routes and confirm the response's chosen route matches the lower `duration_in_traffic_s`, not the shortest distance; drive-test (or simulate via mocked `watchPosition` updates) that `currentStepIndex` advances without a manual tap once simulated position crosses a maneuver's threshold radius; confirm the map polyline recolors by congestion segment and that a stale one-shot origin no longer leaves a stuck "you are here" marker.
