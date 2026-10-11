# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

DisasterResource+ — a web-based supply-readiness system for CDRRMO San Pablo City (Capstone Chapters I–III; spec in `backend/docs/system-spec.md`). It plans each quarter's replenishment from the office's own history under its budget, explains every recommendation, and keeps offline-safe, tamper-evident stock records, plus an ICS 213-RR request module with a borrowed-equipment lifecycle. The old incident/dispatch/map/disaster-feed/OSRM stack was removed (migration `2026_09_30_000001_drop_old_scope`); it is not part of the paper's scope — don't reintroduce it.

`backend/` is a full runnable Laravel 13 project (scaffolded via `composer create-project laravel/laravel:^13.0`).

## Repo layout (three deployable units)

- `backend/` — Laravel (Inertia + React/TypeScript, Sanctum auth, Reverb realtime broadcasting)
- `solver-service/` — Python FastAPI microservice running the PuLP 2.8.0 + CBC replenishment model
- `docker/` — shared infra (Postgres bootstrap script for the local `postgres` service)

Services are wired together via `docker-compose.yml`: `backend` (web) on 8000, `queue` (queue worker, runs `RunReplenishmentJob`), `scheduler` (nightly `stock:verify`), `reverb` (websocket server) on 8080, `solver-service` on 8001. Postgres on 5432 is defined only in `docker-compose.override.yml` (local dev); production runs on the CDRRMO office's own server (the paper's deployment; Coolify there, if used, runs on that server) with a standalone Postgres. `queue`, `scheduler`, and `reverb` build from the same `backend/Dockerfile` image as `backend` but override its `CMD`.

There is a single `.env` at the repo root, not one per service. `backend/bootstrap/app.php` calls `Application::useEnvironmentPath()` to point Laravel's env lookup at the repo root (two directories up from `backend/bootstrap/`) instead of the default `backend/.env` — so `php artisan` commands run from inside `backend/` still read/write the root file. Docker gets the same file via `docker-compose.yml`'s `env_file: - .env` on every service. `backend/vite.config.ts` similarly sets `envDir` to the repo root, since Vite only auto-loads `.env` from its own project root by default — without that, every `VITE_*` var (Reverb config, `VITE_APP_NAME`) silently resolves to `undefined` in the frontend.

## Commands

**Docker (all services):**

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
php artisan queue:work      # separate terminal — processes RunReplenishmentJob
php artisan reverb:start    # separate terminal — realtime broadcasting
php artisan schedule:work   # separate terminal — nightly stock:verify; Docker runs this as the `scheduler` service
php artisan data:refresh    # clear all office data except users and settings, restore the catalog (asks first; --force skips)
php artisan data:mock       # load the mock history and prices (ReplenishmentDemoSeeder); safe to re-run
```

Backend tests: `php artisan test`. Frontend typecheck: `npm run typecheck` (tsc --noEmit). Frontend build: `npm run build`.

**Local dev without `pdo_pgsql`:** if `php artisan migrate` errors with `could not find driver (Connection: pgsql...)`, the local PHP install has no `pdo_pgsql` (common with minimal single-binary distributions like php.new/Herd-Lite — check via `php -m`). Don't switch the project's real DB engine — use SQLite as a local-only fallback: `touch backend/database/database.sqlite`, then in the root `.env` set `DB_CONNECTION=sqlite` and comment out/clear the `pgsql` block (`DB_DATABASE` must be unset for `config/database.php`'s `database_path('database.sqlite')` default to kick in). Switch back to `pgsql` before using Docker. The schema has no Postgres-specific raw SQL, so SQLite runs the full migration set.

**Solver service:**

```bash
cd solver-service
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

**Solver service tests** (this machine has no system Python; `uv` manages one):

```bash
cd solver-service
uv run --python 3.11 --with-requirements requirements.txt --with pytest --with httpx python -m pytest tests -v
```

## Architecture

### Roles (system-spec.md §2)

`EnsureUserHasRole` (the `role:` middleware alias) gates routes by `users.role`. The roles follow the office structure confirmed in the 2026-09-03 interview (`backend/docs/important/transcription summary.txt`): `research_planning`, `operations` (Operations and Warning; ERT and the other response teams are teams inside it), `admin_training` (Administration and Training), `administrative` (read-only), and `logistics` (a section under Administration and Training), plus `viewer` for unassigned accounts. Not ICS roles. `ert` and `incident_management` no longer exist (migration `2026_10_03_000002` moved those accounts to `operations`). The **Admin** (`users.is_system_admin`) **is the Head of Office / Department Head and passes every role check**, so `role:system_admin` means Admin only. The Assistant Department Head is a `users.position` title, not a role. Role labels live in `resources/js/api-service/modules/roles.ts`.

- The Admin enters the budget, runs the solver, confirms the VED policy, and approves or rejects both recommendations and ICS 213-RR requests (including their own).
- Logistics encodes: history import, data-check flags, stock entries, conflicts, weekly counts, releases, the borrowed-item logbook.
- Requesters (Logistics, Operations) file ICS 213-RR requests. Admin and Training keeps the item catalog and depots. Research and Planning reads and exports the report. Administrative and Viewer only read. Every role can open every page read-only.
- Pages get a `can` prop from their `Web\*Controller`; the frontend's Spatie `can()` (`Hooks/use-permissions.ts`) is only used for the catalog pages (`manage-depots`). `User::toInertiaPayload()` gives the Admin every Spatie role and permission.

### The system (`backend/docs/system-spec.md`)

- **Solver** (`solver-service/app/solvers/replenishment.py`, `/api/v1/replenishment/{solve,budget-curve,backtest}`): Stage 1 per-item multiplier MILP (the covering level covers k of the last M quarters) and Stage 2 scenario model (sample average approximation). Both run on PuLP 2.8.0 + CBC with `MIP_GAP = 1e-4` (without it Stage 2 takes ~20× longer). Every item gets one reason read from the solution. The budget curve covers 11 levels (50–150%) plus a "next ₱10,000" diff. The rolling-origin backtest compares flat 1.5, the multiplier, the scenario model and the office's own orders. The paper's worked example is the test fixture (127/240/350 and 218/159/232). The solver never touches the database.
- **Replenishment** (`App\Services\Replenishment`, `Api/Web\ReplenishmentController`, `Pages/Replenishment/{Index,Backtest,Inputs}.tsx`: the Recommendation tab is the home page, `/` redirects here; Backtest and Policy and history are its other subtabs): `QuarterHistory` merges the encoded history (`app_history_lines`, CSV template in `backend/docs/templates/`, `php artisan history:import`) with live ICS requests. `DataChecks` applies 3 pre-solve rules and blocks the run until every flag is resolved. `RunReplenishmentJob` → solver → `app_replenishment_runs` → `ReplenishmentRunUpdated` (Reverb `replenishment` channel). Run statuses: `queued → completed → approved | rejected`, plus `failed` and `superseded`; one approved run per quarter is the purchase plan. The VED policy lives in `app_ved_policies` (Table 4 defaults); the Admin edits and confirms it (`PATCH /api/replenishment/policies/{class}`, records position and date).
- **Item catalog and depots** (`Pages/Resources/Types.tsx` at `/resources/types`, `Pages/Depots/Index.tsx`): items carry `unit`, `unit_cost` (the solver skips items without one), `ved_class`, optional `space_per_unit`, and `is_consumable` (false = borrowable equipment). Depots carry optional `free_space` (m³). Depot space is modeled only when every depot has `free_space` and every item has `space_per_unit` (`RunReplenishmentJob::freeSpace()` + the solver's `space_modeled`).
- **Stock records** (`App\Services\Stock\StockLedger`, `Pages/Stock/Index.tsx`): `app_stock_entries` is append-only (Eloquent refuses update/delete), and current stock = sum of entries. Each entry has a device-made ID with a unique index and a SHA-256 chain in `seq` order. Conflicts (`app_stock_conflicts`) are kept and flagged, never dropped. "Check records" = `php artisan stock:verify` (also nightly; a pass saves a checkpoint in `app_chain_checkpoints`, so deleted newest rows or a rebuilt chain fail the next check, and a failure emails the Admins). On Postgres the chain lock is `pg_advisory_xact_lock` inside `append` (held until the caller's outer transaction commits); other drivers use a cache lock. Stock-page entries go to the IndexedDB outbox first (`resources/js/Offline/`); ICS releases and borrowed-item returns go there only when the online call fails for lack of a connection. Every outbox item is a stock entry (an offline borrowed unit gets a device-made `borrow_id`, and its return `depends_on_device_id` the issue). Each outbox item belongs to the user who made it, and only that user's items sync. They sync as one batch to `POST /api/stock/sync`, priority first and dependencies after; each entry is validated on its own (`rejected` with errors, and the quantity's sign must fit the reason), so one bad entry never blocks the batch; `RequestWorkflow::applyDeviceEntry` also updates the request line and logbook, flagging what the online checks would refuse. Sync runs on app open, on reconnect, and via Workbox Background Sync (Chromium).
- **ICS 213-RR + borrowed items** (`App\Services\Requests\RequestWorkflow`, `/ics-requests`, `/ics-requests/borrowed`): approval reserves stock (`app_reservations`), release deducts through stock entries, and borrowable lines issue one `Borrow` per unit. Every status change goes in `app_status_changes` and is shown with who made it and their `users.position`. Borrower names and contacts are hidden except for the Admin and Logistics. The request carries the paper form's fields (control no., time, requester name and position, Logistics' reply in `remarks`, a reason on every line approved for less); its Subject is derived from the items' `category`. Kits (`app_kits`, saved by Logistics from the request form) prefill request lines. `POST /api/borrows` lends equipment with no request, to any office, with a `location`; offline it is an `issue` stock entry with no request line. The office's real items and the "Medical backpack #1" kit come from `CatalogSeeder`.
- **Report** (`Web\ReportController`, `Pages/Report/Index.tsx` at `/report`, its own sidebar entry): `InventoryBurnRateReportBuilder` builds the rows for both the on-screen copy (any year, a quarter range, every role) and the PDF + CSV export (`GET /api/replenishment/report`, Admin, Logistics, Research and Planning), and prints the latest hash-chain fingerprint.
- **PWA**: Laravel serves no `index.html`, so `app.tsx` registers the service worker itself from `/sw.js`, a `routes/web.php` route serving `public/build/sw.js` with root scope. `navigateFallback: null` must stay explicit, since the plugin's `index.html` default breaks worker evaluation. Service workers need HTTPS (or `localhost`), so the office server must serve the app over HTTPS. `/stock`, `/ics-requests` and `/ics-requests/borrowed` open offline (NetworkFirst).
- **Evaluation**: `backend/docs/evaluation-protocol.md`, `backend/tests/Feature/EvaluationTest.php`, `solver-service/tests/test_performance.py`. Demo data: `CatalogSeeder` (items and depots) then `ReplenishmentDemoSeeder` (mock history for every consumable, Jul 2023 – Jun 2026, anchored on the real events that hit Laguna and San Pablo and on real prices; no public item-level dataset exists).

### Domain model conventions

All custom domain tables use an `app_` prefix; Laravel's own framework tables (`users`, `cache`, `jobs`, `sessions`, `password_reset_tokens`, `migrations`) do not. Eloquent has no global table-prefix setting, so every domain model sets `protected $table = 'app_...'` explicitly — new domain migrations/models should follow the same prefix.

All domain models (including `User`) use `HasUuids` — the migrations declare `uuid('id')->primary()` with no default, so Eloquent must generate the key on `create()`. A new model backed by a `uuid()` primary key needs `HasUuids` or inserts fail with a NOT NULL constraint on `id`.

### Backend request layering

- `Http/Controllers/Web/*` — Inertia-rendering controllers; each passes the page its `can` flags.
- `Http/Controllers/Api/*` — JSON endpoints under `auth:sanctum`. CRUD modules (depots, resource types, users) extend `BaseController` (fixed index/pagination-search/store/update/destroy convention matching `resources/js/api-service/http.ts`); `$inUseRelations` blocks deleting a row still referenced (e.g. an item with stock entries or history).
- `routes/auth.php` (required from `routes/web.php`) holds a minimal hand-written session login/logout plus forgot/reset password — not Breeze/Fortify/Jetstream.
- `routes/channels.php` authorizes the `replenishment` broadcast channel (any authenticated user). Registered via `bootstrap/app.php`'s `withRouting(channels: ...)`, which also enables Reverb's `/broadcasting/auth` route.

### Frontend (backend/resources/js)

Inertia + React + TypeScript, Tailwind v3, Recharts for charts. Every page sets `AdminLayout` (sidebar groups: Planning = Replenishment, Report; Records = Stock, ICS 213-RR; Catalog = Items, Depots; plus Settings for the Admin) as its `.layout` and opens with `Components/PageHeader.tsx` (which also sets the browser tab title); auth pages opt out. Colors come from the theme tokens in `resources/css/app.css`, exposed as Tailwind colors (`border-line`, `bg-panel`, `bg-tint`, `text-amber`, `text-ok`, `text-info`, `text-critical`) plus the `.panel` surface class: never `white/…`, `black/…` or fixed palette colors, which read right in only one theme. Form controls use `field`/`label` from `lib/utils.ts`; widen or narrow one with `cn(field, "w-24")`, since a plain string append loses to `field`'s `w-full`. Shared prop/model shapes are declared in `Types/models.d.ts`, imported via the `@/` path alias — keep these in sync with the Eloquent models' `$fillable`/`$casts`.

`resources/js/bootstrap.ts` wires Axios (CSRF header) and a Laravel Echo client over Reverb (`window.Echo`), configured from `VITE_REVERB_*` env vars. `Pages/Replenishment/Index.tsx` subscribes to the `replenishment` channel.

**Subtabs must be real routes, not `useState`.** When a page has switchable subtabs (e.g. Settings' Users/SMTP, ICS 213-RR's requests/borrowed), each subtab is its own Inertia route + `Web\*Controller` action + `Pages/**/*.tsx` page, with a shared tab-bar component using `<Link>` + `usePage().url` for the active state (`Components/RouteTabs.tsx`, used by Settings, ICS 213-RR and Replenishment). Do not gate subtab content behind a local `useState` — it isn't bookmarkable/shareable/back-button-able.

### Solver-service structure

- `main.py` — FastAPI app entrypoint, mounts `app/api/replenishment.py` under `/api/v1/replenishment`, plus `/health`.
- `app/models/replenishment.py` — Pydantic request/response contracts.
- `app/core/config.py` — `pydantic_settings.BaseSettings`, env vars prefixed `SOLVER_`.
- `app/core/logging.py` — JSON logging setup (`python-json-logger`).

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
