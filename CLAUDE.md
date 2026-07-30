# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

DisasterResource+ — a resource optimization and visualization system for disaster response. Coordinators log incidents (disasters/zones), track depot inventory, and let an optimization microservice suggest resource allocations, which a human then approves.

`backend/` is a full runnable Laravel 13 project (scaffolded via `composer create-project laravel/laravel:^13.0` and merged with the original hand-placed domain files). `solver-service/`'s pinned `ortools==9.10.4067` predates Python 3.14 wheels — bump that pin before relying on `pip install -r requirements.txt` there.

## Repo layout (three deployable units)

- `backend/` — Laravel (Inertia + React/TypeScript, Sanctum auth, Reverb realtime broadcasting)
- `solver-service/` — Python FastAPI microservice wrapping OR-Tools (routing) and PuLP (allocation LP)
- `docker/` — shared infra (PostGIS bootstrap script for the `postgres` service)

Services are wired together via `docker-compose.yml`: Postgres/PostGIS on 5432, `backend` (web) on 8000, `queue` (queue worker, no exposed port), `reverb` (websocket server) on 8080, `solver-service` on 8001. `queue` and `reverb` build from the same `backend/Dockerfile` image as `backend` but override its `CMD` — see the Docker Compose section below.

There is a single `.env` at the repo root, not one per service. `backend/bootstrap/app.php` calls `Application::useEnvironmentPath()` to point Laravel's env lookup at the repo root (two directories up from `backend/bootstrap/`) instead of the default `backend/.env` — so `php artisan` commands run from inside `backend/` still read/write the root file. Docker gets the same file via `docker-compose.yml`'s `env_file: - .env` on every service (that mechanism is independent of Laravel's own env-file lookup — it injects real container env vars either way).

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
php artisan queue:work      # separate terminal — processes RunOptimizationJob
php artisan reverb:start    # separate terminal — realtime broadcasting
```

Frontend typecheck: `npm run typecheck` (tsc --noEmit). Frontend build: `npm run build`.

**Solver service:**

```bash
cd solver-service
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

**Solver service tests:**

```bash
cd solver-service
pytest tests/ -v
pytest tests/test_solvers.py::test_pulp_solver_fully_satisfies_demand_within_capacity -v   # single test
```

## Architecture

### Domain model (backend/app/Models, backend/database/migrations)

`Disaster` (UUID PK) has many `Zone`s (barangay-level areas with `population_affected`/`severity_level`), many `ResourceRequest`s (table `requests`, model deliberately renamed to avoid clashing with Laravel's HTTP `Request`), and many `OptimizationRun`s. `Depot` holds `Resource` rows keyed by `ResourceType` (quantity available/reserved). `Allocation` links a `ResourceRequest` + `Depot` + optional `OptimizationRun`.

All domain models (including `User`) use `HasUuids` — the migrations declare `uuid('id')->primary()` with no default, so Eloquent must generate the key on `create()`. If you add a new model backed by a `uuid()` primary key, add `HasUuids` or inserts will fail with a NOT NULL constraint on `id`.

Lat/lng are plain `double` columns for now; migrations have commented-out `$table->geography(...)` lines for the PostGIS columns to enable later. `GeofenceService` (`backend/app/Services/Gis/GeofenceService.php`) currently does haversine distance in pure PHP as a stand-in until PostGIS geography queries are wired up — check whether that's still true before adding PostGIS-dependent code.

### Optimization flow (the core cross-service interaction)

1. An incident is created/escalates → `RunOptimizationJob` (`backend/app/Jobs/RunOptimizationJob.php`, queued) is dispatched.
2. The job calls `OptimizationServiceClient::optimizeAllocation()` (`backend/app/Services/Optimization/OptimizationServiceClient.php`), which POSTs zone demand + depot list to the solver-service at `{OPTIMIZATION_SERVICE_URL}/api/v1/optimize/allocation`.
3. solver-service's `app/api/optimize.py` routes `/allocation` to `PulpAllocationSolver` (minimizes unmet demand subject to depot capacity, `app/solvers/pulp_solver.py`) and `/routing` to `ORToolsRoutingSolver` (haversine-based routing skeleton, `app/solvers/ortools_solver.py` — the OR-Tools solution isn't yet mapped back into concrete allocation lines, see the NOTE in that file).
4. The job persists the response as an `OptimizationRun` (status `completed`, suggested — never auto-applied) and broadcasts `DisasterAlertEvent` over Reverb on the `disasters` channel so the dashboard updates live.
5. `TriggerSolver.tsx` (Inertia page) lets a coordinator manually re-run the solver for a given incident in addition to the automatic trigger.

Both solvers implement `BaseSolver` (`solver-service/app/solvers/base.py`) so `optimize.py` can swap implementations without route changes.

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

### Solver-service structure

- `main.py` — FastAPI app entrypoint, mounts `app/api/optimize.py` under `/api/v1/optimize`, plus `/health`.
- `app/models/schemas.py` — Pydantic request/response contracts (`AllocationRequest`, `AllocationResponse`, etc.) shared between the two solver endpoints.
- `app/core/config.py` — `pydantic_settings.BaseSettings`, env vars prefixed `SOLVER_`.
- `app/core/logging.py` — JSON logging setup (`python-json-logger`).

Seed data (`backend/database/seeders/DisasterDataSeeder.php`) uses real PH locations (Albay, Marikina, Tacloban, Itogon, Cagayan de Oro) matching an interactive prototype this scaffold was built from — reuse these when adding fixtures/tests instead of inventing new coordinates.
