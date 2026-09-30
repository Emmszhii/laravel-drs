# DisasterResource+

A web-based supply-readiness system for CDRRMO San Pablo City: it plans each quarter's replenishment from the office's own history under its budget, explains every recommendation, and keeps offline-safe, tamper-evident stock records. It also digitizes the ICS 213-RR request form and the borrowed-equipment logbook.

The spec it is built to is `backend/docs/system-spec.md` (from the Capstone paper, Chapters I–III).

This repository contains three deployable units:

- **`backend/`** — Laravel application (Inertia + React/TypeScript frontend, Sanctum auth, Reverb realtime)
- **`solver-service/`** — Python FastAPI microservice running the PuLP/CBC replenishment model
- **`docker/`** — shared infrastructure config (Postgres bootstrap)

## Quick start (Docker)

```bash
cp .env.example .env
docker compose up --build
```

- Laravel: http://localhost:8000
- Solver service: http://localhost:8001
- Postgres: localhost:5432

## Local development without Docker

The `.env` at the repo root is the single source of config for both Docker and local dev — `backend/bootstrap/app.php` points Laravel's env lookup there instead of `backend/.env`, so run `cp .env.example .env` once at the root before either flow.

**Backend**
```bash
cp .env.example .env   # once, at the repo root
cd backend
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
php artisan queue:work      # processes RunReplenishmentJob (separate terminal)
php artisan reverb:start    # realtime broadcasting (separate terminal)
php artisan schedule:work   # nightly "check records" (separate terminal)
```

**Solver service**
```bash
cd solver-service
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

### Local dev without pdo_pgsql

If `php artisan migrate` fails with `could not find driver (Connection: pgsql...)`, your PHP install doesn't have the `pdo_pgsql` extension (common with minimal single-binary distributions like php.new/Herd-Lite, which only ship `pdo_mysql`/`pdo_sqlite` and have no `ext/` directory to add it to). Rather than switching the project off Postgres, use SQLite as a **local-only** fallback:

```bash
touch backend/database/database.sqlite
```

Then in the root `.env`, set `DB_CONNECTION=sqlite` (comment out the `pgsql` block below it — `DB_DATABASE` must be empty/unset for SQLite to use `database_path('database.sqlite')` by default). Switch back to `pgsql`/`DB_HOST=postgres` before using Docker, which already has `pdo_pgsql` installed (see `backend/Dockerfile`).

## Folder structure

```
disaster-response-system/
├── backend/            Laravel (Inertia + Sanctum + Reverb)
├── solver-service/     FastAPI replenishment solver
└── docker/             Shared infra (Postgres init)
```
