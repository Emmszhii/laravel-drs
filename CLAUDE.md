# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

DisasterResource+ — a resource optimization and visualization system for disaster response. Coordinators log incidents (disasters/zones), track depot inventory, and let an optimization microservice suggest resource allocations, which a human then approves.

`backend/` is a full runnable Laravel 13 project (scaffolded via `composer create-project laravel/laravel:^13.0` and merged with the original hand-placed domain files). `solver-service/`'s pinned `ortools==9.10.4067` predates Python 3.14 wheels — bump that pin before relying on `pip install -r requirements.txt` there.

## Repo layout (three deployable units)

- `backend/` — Laravel (Inertia + React/TypeScript, Sanctum auth, Reverb realtime broadcasting)
- `solver-service/` — Python FastAPI microservice wrapping OR-Tools (routing) and PuLP (allocation LP)
- `docker/` — shared infra (PostGIS bootstrap script for the `postgres` service)

Services are wired together via `docker-compose.yml`: Postgres/PostGIS on 5432, `backend` (web) on 8000, `queue` (queue worker, no exposed port), `scheduler` (Laravel scheduler, no exposed port), `reverb` (websocket server) on 8080, `solver-service` on 8001, `osrm` (routing engine) on 5001. `queue`, `scheduler`, and `reverb` build from the same `backend/Dockerfile` image as `backend` but override its `CMD` — see the Docker Compose section below.

There is a single `.env` at the repo root, not one per service. `backend/bootstrap/app.php` calls `Application::useEnvironmentPath()` to point Laravel's env lookup at the repo root (two directories up from `backend/bootstrap/`) instead of the default `backend/.env` — so `php artisan` commands run from inside `backend/` still read/write the root file. Docker gets the same file via `docker-compose.yml`'s `env_file: - .env` on every service (that mechanism is independent of Laravel's own env-file lookup — it injects real container env vars either way). `backend/vite.config.ts` similarly sets `envDir` to the repo root, since Vite (unlike Laravel) only auto-loads `.env` from its own project root by default — without that, every `VITE_*` var (Reverb config, `VITE_APP_NAME`) silently resolves to `undefined` in the frontend.

## Commands

**Docker (all services — backend, queue worker, Reverb, solver-service, Postgres):**

```bash
cp .env.example .env
docker compose up --build
```

**Backend:**

```bash
cp .env.example .env   # once, at the repo root — not inside backend/
cd backend
composer install && npm install
php artisan key:generate    # writes into the root .env
php artisan migrate --seed
npm run dev                 # vite dev server for Inertia/React assets
php artisan serve
php artisan queue:work      # separate terminal — processes RunOptimizationJob, PollDisasterFeedsJob, AutoResolveIncidentsJob
php artisan reverb:start    # separate terminal — realtime broadcasting
php artisan schedule:work   # separate terminal — ticks routes/console.php's Schedule:: entries every minute (disaster feed polling + auto-resolve); Docker runs this as the `scheduler` service instead
```

Frontend typecheck: `npm run typecheck` (tsc --noEmit). Frontend build: `npm run build`.

**Local dev without `pdo_pgsql`:** if `php artisan migrate` errors with `could not find driver (Connection: pgsql...)`, the local PHP install has no `pdo_pgsql` (common with minimal single-binary distributions like php.new/Herd-Lite — check via `php -m`; if there's no `ext/` directory or `extension_dir` at all, it can't be added). Don't switch the project's real DB engine — use SQLite as a local-only fallback instead: `touch backend/database/database.sqlite`, then in the root `.env` set `DB_CONNECTION=sqlite` and comment out/clear the `pgsql` block (`DB_DATABASE` must be unset, not just emptied to a stray value, for `config/database.php`'s `env('DB_DATABASE', database_path('database.sqlite'))` default to kick in). Switch back to `pgsql`/`DB_HOST=postgres` before using Docker, whose `backend/Dockerfile` already installs `pdo_pgsql`. This schema has no Postgres-specific raw SQL and the PostGIS geography columns are still commented out, so SQLite runs the full migration set cleanly.

**Routing (OSRM)** — data is prepared automatically. The `osrm-prepare` service (`docker/osrm/prepare/`, built from `osrm/osrm-backend` + `osmctools`) downloads the Philippines OSM extract, clips it to Laguna province (the app's operational area — routing only works within this bounding box; seeded demo incidents outside Laguna won't get real directions), and runs `osrm-extract`/`osrm-partition`/`osrm-customize` into the named volume `osrm` serves from (`osrm-data-x11oqof2fbwxdq26ev780h1c`, `external: true` in `docker-compose.yml` since it's populated by `osrm-prepare` rather than seeded by Compose). `osrm`'s `depends_on: osrm-prepare: condition: service_completed_successfully` blocks it from starting until this finishes. It's idempotent (skips already-completed steps), so `docker compose up` is a fast no-op here after the first run — no manual script to run, locally or in production.

**Solver service:**

```bash
cd solver-service
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
IN CDM = python -m venv venv && venv\Scripts\activate && pip install -r requirements.txt && uvicorn main:app --reload --port 8001
```

**Solver service tests:**

```bash
cd solver-service
pytest tests/ -v
pytest tests/test_solvers.py::test_pulp_solver_fully_satisfies_demand_within_capacity -v   # single test
```

## Architecture

### Domain model (backend/app/Models, backend/database/migrations)

All custom domain tables use an `app_` prefix (`app_disasters`, `app_zones`, `app_depots`, `app_resource_types`, `app_resources`, `app_requests`, `app_allocations`, `app_optimization_runs`, `app_settings_routing`, `app_settings_disaster_feed`) — Laravel's own framework tables (`users`, `cache`, `jobs`, `sessions`, `password_reset_tokens`, `migrations`) do not get the prefix. Since Eloquent has no global table-prefix setting, every domain model sets `protected $table = 'app_...'` explicitly (its name no longer matches the class-name convention) — new domain migrations/models should follow the same `app_` prefix.

`Disaster` (UUID PK) has many `Zone`s (barangay-level areas with `population_affected`/`severity_level`), many `ResourceRequest`s (table `app_requests`, model deliberately renamed to avoid clashing with Laravel's HTTP `Request` — note `Disaster::requests()` is a `hasManyThrough(Zone)`, since `app_requests` links to a disaster only via `app_zones.disaster_id`, not a direct column), and many `OptimizationRun`s. `Depot` holds `Resource` rows keyed by `ResourceType` (quantity available/reserved). `Allocation` links a `ResourceRequest` + `Depot` + optional `OptimizationRun`.

All domain models (including `User`) use `HasUuids` — the migrations declare `uuid('id')->primary()` with no default, so Eloquent must generate the key on `create()`. If you add a new model backed by a `uuid()` primary key, add `HasUuids` or inserts will fail with a NOT NULL constraint on `id`.

Lat/lng are plain `double` columns for now; migrations have commented-out `$table->geography(...)` lines for the PostGIS columns to enable later. `GeofenceService` (`backend/app/Services/Gis/GeofenceService.php`) currently does haversine distance in pure PHP as a stand-in until PostGIS geography queries are wired up — check whether that's still true before adding PostGIS-dependent code.

### Optimization flow (the core cross-service interaction)

1. An incident is created/escalates → `RunOptimizationJob` (`backend/app/Jobs/RunOptimizationJob.php`, queued) is dispatched.
2. The job calls `OptimizationServiceClient::optimizeAllocation()` (`backend/app/Services/Optimization/OptimizationServiceClient.php`), which POSTs zone demand + depot list to the solver-service at `{OPTIMIZATION_SERVICE_URL}/api/v1/optimize/allocation`.
3. solver-service's `app/api/optimize.py` routes `/allocation` to `PulpAllocationSolver` (minimizes unmet demand subject to depot capacity, `app/solvers/pulp_solver.py`) and `/routing` to `ORToolsRoutingSolver` (haversine-based routing skeleton, `app/solvers/ortools_solver.py` — the OR-Tools solution isn't yet mapped back into concrete allocation lines, see the NOTE in that file).
4. The job persists the response as an `OptimizationRun` (status `completed`, suggested — never auto-applied) and broadcasts `DisasterAlertEvent` over Reverb on the `disasters` channel so the dashboard updates live.
5. `TriggerSolver.tsx` (Inertia page) lets a coordinator manually re-run the solver for a given incident in addition to the automatic trigger.

Both solvers implement `BaseSolver` (`solver-service/app/solvers/base.py`) so `optimize.py` can swap implementations without route changes.

### Automated disaster feed (backend/app/Services/DisasterFeed)

Pulls incidents automatically from two free, keyless external sources — USGS (earthquakes) and GDACS (typhoons/floods/volcanoes/etc.) — separate from the coordinator-driven manual "Log incident" flow.

- `UsgsEarthquakeFeedClient` / `GdacsFeedClient` — normalize each source's response into a common incident shape (`source`, `external_id`, `name`, `type`, `signal`, `zone`, `lat`, `lng`, `source_url`, `external_updated_at`). Never throw — return `['ok' => bool, 'error' => ?string, 'incidents' => array]`, same convention as `OsrmDirectionsProvider`. GDACS's JSON shape is less stable than USGS's, so its client parses defensively via `data_get()` and skips (logs) malformed features rather than failing the whole run.
- `DisasterFeedIngestor` — dedups/upserts into `app_disasters` by `(source, external_id)` (unique index), with a near-duplicate guard (same type, non-resolved, within 25km via `GeofenceService::distanceKm()`) so the feed adopts a coordinator's manually-logged incident instead of duplicating it. Never overwrites `status` on update — a coordinator's manual status change always wins.
- `PollDisasterFeedsJob` (queued) — reads `DisasterFeedSetting::current()` (singleton, `app_settings_disaster_feed`, admin-configured at **Settings → Disaster feed**, `/settings/disaster-feed`), calls enabled clients, ingests, records `last_run_*`. Dispatched by `routes/console.php`'s `Schedule::call(...)->everyFiveMinutes()`, which checks `DisasterFeedSetting::dueToPoll()` — the admin-picked poll interval (15/30/60/360/1440 min) is enforced by this in-code check, not by the scheduler itself, since `Schedule::` only supports fixed frequencies. Also runnable manually via `php artisan disaster-feed:poll --force` or the Settings page's "Run now" button (`POST /api/disaster-feed-settings/run`).
- `AutoResolveIncidentsJob` (queued, `Schedule::job(...)->hourly()`) — resolves **any** `ACTIVE`/`MONITORING` incident (feed-sourced or manual) once it has had no open resource requests for `DISASTER_FEED_AUTO_RESOLVE_MAX_DAYS` days and depot stock covers any remaining demand (`DepotCapacityChecker`, a plain aggregate comparison — not a solver call). Toggle lives on the same settings row.
- No incident created by the feed auto-triggers `RunOptimizationJob` — feed incidents arrive with zero `app_zones`, so there's nothing yet for the solver to optimize against.

### Directions / turn-by-turn navigation (backend/app/Services/Directions)

Separate from the optimizer above — this is human-facing point-to-point navigation (draw a route, show turn-by-turn steps, speak them via the browser's Web Speech API), not the solver's depot-assignment optimization.

- `DirectionsProvider` — interface both engines implement (`getRoute(fromLat, fromLng, toLat, toLng): array`, normalized shape with `ok`/`distance_m`/`duration_s`/`geometry`/`steps`).
- `OsrmDirectionsProvider` — free, self-hosted, no API key, no usage cap, no live traffic. Calls the `osrm` docker-compose service (`OSRM_URL`/`services.osrm.url`), which requires `docker/osrm/prepare.sh` to have been run once (see Commands above) — it downloads a Philippines OSM extract and preprocesses it; the compose service only serves pre-built data, it doesn't build it. Builds plain-English instructions itself (`OsrmDirectionsProvider::humanize()`) since raw OSRM only returns structured maneuver data, not sentences.
- `PremiumDirectionsProvider` — optional paid tier, generic to any Mapbox Directions API v5-shaped endpoint (not hardcoded to Mapbox specifically). Base URL/API key are admin-configured at runtime (`RoutingSetting`), not baked into env — configured from **Settings → Routing** (`/settings/routing`, `role:admin`-gated). Throws `ProviderQuotaExceededException` on a 429 or a quota-shaped error message.
- `DirectionsRouter` — the actual entry point `Api\DirectionsController` calls. Reads `RoutingSetting::current()` (a lazily-created singleton row), resets the monthly usage counter when the calendar month rolls over, and picks OSRM vs. premium: skips premium (falls back to OSRM) if usage is at/over the admin-set `premium_monthly_cap`, or if the premium call throws `ProviderQuotaExceededException`. Either fallback tags the response with `fallback_reason` (`cap_exceeded` | `provider_error`) so the frontend can explain why a route came from the free engine.
- `GET /api/directions?from_lat=&from_lng=&to_lat=&to_lng=` — the endpoint the frontend calls; always goes through `DirectionsRouter`, never a provider directly.
- Frontend: `Hooks/useDirections.ts` (fetches + owns the current turn-by-turn step index), `Hooks/useVoiceGuidance.ts` (speaks the current step via `SpeechSynthesisUtterance` — no live GPS auto-advance yet, since `navigator.geolocation` needs a secure context and this stack currently serves plain HTTP), `Components/Map/DirectionsPanel.tsx` (origin/destination pickers + step list), and `IncidentMap.tsx`'s `route` prop (draws the polyline — stays imperative/vanilla Leaflet like the rest of that file).

### Backend request layering

- `Http/Controllers/Web/*` — Inertia-rendering controllers for the authenticated coordinator UI (e.g. `DashboardController` renders `Pages/Dashboard.tsx` with active incidents, depots, and recent requests).
- `Http/Controllers/Api/*` — pure JSON REST endpoints under `auth:sanctum`, for external/mobile callers and solver-service callbacks (e.g. `IncidentController`).
- `EnsureUserHasRole` middleware is registered as the `role` alias in `bootstrap/app.php` (e.g. `Route::middleware('role:admin,coordinator')`), gating routes by `$user->role` (`users.role`, default `viewer`).
- `routes/auth.php` (required from `routes/web.php`) holds a minimal hand-written session login/logout (`AuthenticatedSessionController` + `Pages/Auth/Login.tsx`) — not Breeze/Fortify/Jetstream, since those would scaffold their own conflicting views.
- `routes/channels.php` authorizes the `disasters` broadcast channel (any authenticated user) and an `eoc-dashboard` presence channel (admin/coordinator only). Registered via `bootstrap/app.php`'s `withRouting(channels: ...)`, which also enables Reverb's `/broadcasting/auth` route.

Many routes in `routes/web.php` and `routes/api.php` are commented out (depots, resource types, request approval) — these are the intended next endpoints, not dead code.

### Frontend (backend/resources/js)

Inertia + React + TypeScript, Tailwind v3 for styling (`tailwind.config.js`/`postcss.config.js`), Leaflet for maps (`Components/Map/IncidentMap.tsx`), Recharts for charts. Pages resolve via `resolvePageComponent` against `Pages/**/*.tsx` and default to `AppLayout` unless a page sets its own `.layout` (e.g. `Login.tsx` opts out since the dashboard chrome doesn't apply). Shared prop/model shapes are declared in `Types/models.d.ts`, imported via the `@/` path alias (`tsconfig.json` + `vite.config.ts`) — keep these in sync with the Eloquent models' `$fillable`/`$casts` when either side changes.

`resources/js/bootstrap.ts` wires Axios (CSRF header) and a Laravel Echo client over Reverb (`window.Echo`), configured from `VITE_REVERB_*` env vars — no page currently subscribes to the `disasters` channel from the frontend yet; that's the next piece to wire up for realtime updates.

**Subtabs must be real routes, not `useState`.** When a page has switchable subtabs (e.g. Resources' Stock/Resource-types, Settings' Users/Routing), each subtab is its own Inertia route + `Web\*Controller` action + `Pages/**/*.tsx` page (see `routes/web.php`'s `/resources` + `/resources/types`, and `/settings/users` + `/settings/routing`), with a shared tab-bar component using `<Link>` + `usePage().url` for the active state (see `Pages/Resources/shared.tsx`'s `ResourcesTabs`). Do not gate subtab content behind a local `useState` — it isn't bookmarkable/shareable/back-button-able and breaks the "everything is a URL" convention the rest of the app follows.

### Solver-service structure

- `main.py` — FastAPI app entrypoint, mounts `app/api/optimize.py` under `/api/v1/optimize`, plus `/health`.
- `app/models/schemas.py` — Pydantic request/response contracts (`AllocationRequest`, `AllocationResponse`, etc.) shared between the two solver endpoints.
- `app/core/config.py` — `pydantic_settings.BaseSettings`, env vars prefixed `SOLVER_`.
- `app/core/logging.py` — JSON logging setup (`python-json-logger`).

Seed data (`backend/database/seeders/DisasterDataSeeder.php`) uses real PH locations (Albay, Marikina, Tacloban, Itogon, Cagayan de Oro) matching an interactive prototype this scaffold was built from — reuse these when adding fixtures/tests instead of inventing new coordinates.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
