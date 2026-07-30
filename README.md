# DisasterResource+

A web-based resource optimization and visualization system for disaster response.

This repository contains three deployable units:

- **`backend/`** — Laravel application (Inertia + React/TypeScript frontend, Sanctum auth, Reverb realtime)
- **`solver-service/`** — Python FastAPI microservice running the OR-Tools / PuLP optimization and routing logic
- **`docker/`** — shared infrastructure config (PostGIS bootstrap)

See the project's `SUMMARY.md` / `PROJECT.md` (design phase documents) for architecture rationale, database schema, and the interactive UI/UX prototype this structure was scaffolded from.

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
php artisan queue:work      # processes RunOptimizationJob (separate terminal)
php artisan reverb:start    # realtime broadcasting (separate terminal)
```

**Solver service**
```bash
cd solver-service
python3 -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

## Folder structure

```
disaster-response-system/
├── backend/            Laravel (Inertia + Sanctum + Reverb)
├── solver-service/      FastAPI optimization microservice
└── docker/              Shared infra (PostGIS init)
```

Full annotated structure lives in the repo tree itself — each directory's purpose is documented inline via comments where the structure was originally specified.

## Notes

This scaffold intentionally matches an agreed folder structure exactly. Any structural changes (new top-level folders, moved files, added tooling configs like `composer.json`/`vite.config.ts`) should be proposed before being applied — see the note in each service's directory for what's still missing to make it a fully runnable install.
