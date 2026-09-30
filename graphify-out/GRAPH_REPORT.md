# Graph Report - laravel-drs  (2026-09-30)

## Corpus Check
- 263 files · ~106,185 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1436 nodes · 2881 edges · 172 communities (109 shown, 63 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 199 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `b8bc06ef`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Admin API Controllers
- Frontend Bootstrap & Activity Log
- API HTTP Client
- Depot & Resource Models
- Filter/Pagination/Map Widgets
- Form Field & Dropdown Components
- Dashboard & Allocation Models
- Data Table Query Hooks
- Directions & Routing Settings API
- Auth Pages & Buttons
- Resource Types & Admin Layout
- Disaster Feed Settings & Tests
- Form Modal Schema Builder
- BaseController CRUD
- Confirmation Modals
- Pagination Bar Components
- User Auth Model & Seeder
- Evacuation Centers Table UI
- Disaster Model & Auto-Resolve Job
- Database Factories
- Record Row Menu & Settings List
- Service Providers
- Shared TypeScript Model Types
- Record Modal & Image Lightbox
- Role/Active Auth Middleware
- Index.tsx
- Dispatch
- use-module.ts
- Composer
- RoleGatingTest
- DisasterFeedIngestor
- AllocationRequest
- BaseSolver
- PasswordResetMail.php
- IncidentController
- Users.tsx
- .log
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- Controller
- data-table.ts
- Allocation (Domain Model)
- ChangePasswordModal.tsx
- Architecture
- Seeder
- OsrmDirectionsProvider
- DisasterResource+: Where a Real Technical Contribution Can Come From
- test_replenishment.py
- use-file-upload-field.tsx
- Users.tsx
- DisasterResource+
- Automated Disaster Feed Polling
- AppLayout
- Index.tsx
- DashboardController
- Dashboard (Page)
- Depot (Domain Model)
- 2026_08_02_085355_split_users_name_into_first_last.php
- 2026_08_02_085419_split_depots_contact_name_into_first_last.php
- DepotCapacityChecker
- config.py
- DirectionsPanel (React Component)
- configure_logging
- AutoResolveIncidentsJob
- vite-env.d.ts
- init-postgis.sh
- cache.php
- formatDate
- IncidentController
- User (Domain Model)
- DirectionsController
- DirectionsRouter
- Disaster (Domain Model)
- DisasterAlertEvent
- DisasterFeedIngestor
- Docker Infrastructure
- GdacsFeedClient
- IncidentMap (React Component)
- Laravel Framework
- Laravel Echo
- Leaflet.js
- Login (Page)
- npm
- Resource Optimization Flow
- OptimizationRun (Domain Model)
- OptimizationServiceClient
- OR-Tools
- ORToolsRoutingSolver
- OsrmDirectionsProvider
- PollDisasterFeedsJob
- PremiumDirectionsProvider
- PulpAllocationSolver
- React
- Recharts
- Resource (Domain Model)
- ResourceRequest (Domain Model)
- ResourceType (Domain Model)
- Laravel Sanctum
- Tailwind CSS
- TriggerSolver (React Component)
- TypeScript
- useDirections (React Hook)
- useVoiceGuidance (React Hook)
- UsgsEarthquakeFeedClient
- Vite
- WebSocket Real-time Broadcasting
- Zone (Domain Model)
- watch-boneyard.mjs
- refresh-boneyard-session.mjs
- DispatchTest
- Open a Claude-in-Chrome tab
- UsersController.php

## God Nodes (most connected - your core abstractions)
1. `User` - 57 edges
2. `StockEntry` - 34 edges
3. `ResourceType` - 33 edges
4. `StockLedger` - 33 edges
5. `IcsRequest` - 31 edges
6. `ReplenishmentItem` - 31 edges
7. `BaseController` - 30 edges
8. `Controller` - 30 edges
9. `ReplenishmentRequest` - 28 edges
10. `RequestWorkflow` - 26 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Solver Service (FastAPI)`  [EXTRACTED]
  docker-compose.yml → README.md
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `office_sized_request()` --calls--> `ReplenishmentItem`  [INFERRED]
  solver-service/tests/test_performance.py → solver-service/app/models/replenishment.py

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (172 total, 63 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.22
Nodes (20): ReplenishmentItem, ReplenishmentRequest, test_budget_never_exceeded_and_money_left_only_when_targets_met(), test_scenario_objective_is_the_average_weighted_shortage(), test_space_constraint_limits_orders_when_modeled(), by_id(), no_solution(), Paper revision + review fixes: space after stock, short-history covering level, (+12 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.20
Nodes (7): Conflict, EntryForm(), Level, MOVEMENTS, Named, Props, REASON_LABEL

### Community 2 - "API HTTP Client"
Cohesion: 0.13
Nodes (28): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError(), unwrap(), appendNestedObject() (+20 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.09
Nodes (18): ReplenishmentRunUpdated, Throwable, RunReplenishmentJob, NewAccountMail, Content, Envelope, PasswordResetMail, Content (+10 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.22
Nodes (3): Depot, HasMany, CatalogTest

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.14
Nodes (17): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, DEFAULT_TABS, StatusFilter(), StatusFilterProps (+9 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.13
Nodes (20): PuLP, backtest(), budget_curve(), covering_level(), flat_rule_orders(), item_target(), _line(), _ms() (+12 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.06
Nodes (25): JsonResponse, Request, ReplenishmentController, Request, Response, ReplenishmentController, Backtest, DataCheckFlag (+17 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.07
Nodes (16): ActivityLogController, JsonResponse, Request, AppLogger, AppLoggerObserver, Model, ActivityLogger, Model (+8 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.18
Nodes (12): normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), columns, DepotsIndex(), fields, STATUS_OPTIONS, TypeCard() (+4 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.16
Nodes (5): ExampleTest, TestCase, EnsureUserIsActiveTest, ExampleTest, BaseTestCase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.10
Nodes (33): baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue() (+25 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.08
Nodes (15): DepotController, Model, JsonResponse, Request, ProfileController, Model, ResourceTypeController, JsonResponse (+7 more)

### Community 14 - "Confirmation Modals"
Cohesion: 0.16
Nodes (12): BaseModel, Pydantic, python-json-logger, Solver Service (FastAPI), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     ru, solve(), BacktestRequest, _check_items() (+4 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.17
Nodes (11): Critical, Critique of the paper and the system (2026-09-30), Main weaknesses, Major, Minor, Status, Suggested order, The paper (+3 more)

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.22
Nodes (7): FastAPI, backtest(), budget_curve(), Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, health(), Health check consumed by Docker/Compose and the backend's depends_on condition.

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.14
Nodes (16): ConfirmDeleteModal(), ConfirmDeleteModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, ModalCenter(), ModalCenterProps, ModalComponentProps, Size (+8 more)

### Community 19 - "Database Factories"
Cohesion: 0.57
Nodes (6): office_sized_request(), Performance pass marks (Chapter III, Performance Testing Procedure): a Stage 2, test_budget_curve_of_11_resolves_under_20_seconds(), test_stage1_scales_to_200_items(), test_stage2_solves_200_items_12_scenarios_under_2_seconds(), timed()

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.13
Nodes (18): http, ActivityLogEntry, activityLogService, ChangePasswordModal(), getLastSeenId(), NotificationBell(), OfflineBanner(), UserProfileSection() (+10 more)

### Community 21 - "Service Providers"
Cohesion: 0.31
Nodes (3): ActivityLogServiceProvider, AppServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.12
Nodes (15): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DataCheckFlag, Depot, @inertiajs/core, PageProps (+7 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.16
Nodes (5): Attempting, Attribute, PreventInactiveLogin, HasRoles, Notifiable

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 27 - "Dispatch"
Cohesion: 0.14
Nodes (26): useOfflineSync(), useOnlineStatus(), DrsOfflineDB, getDb(), OutboxItem, OutboxItemKind, OutboxItemStatus, changed() (+18 more)

### Community 29 - "use-module.ts"
Cohesion: 0.13
Nodes (28): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+20 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.16
Nodes (17): errorMessage(), BacktestPanel(), BASIS_NOTE, BudgetCurve(), FLAGGED_REASONS, FlagList(), HistoryUpload(), peso() (+9 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.12
Nodes (22): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, Toolbar() (+14 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.38
Nodes (7): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, Laravel Reverb, Traefik

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.09
Nodes (15): IcsRequestController, JsonResponse, Request, Borrow, BelongsTo, HasMany, IcsRequest, BelongsTo (+7 more)

### Community 39 - "Users.tsx"
Cohesion: 0.21
Nodes (4): Button(), Props, ResetPasswordProps, Props

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (34): DisasterResource+ — System Specification, 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper] (+26 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 57 - "Controller"
Cohesion: 0.07
Nodes (25): JsonResponse, Request, SmtpSettingController, AuthenticatedSessionController, RedirectResponse, Request, Response, PasswordResetController (+17 more)

### Community 58 - "data-table.ts"
Cohesion: 0.13
Nodes (19): isNetworkError(), Borrow, Borrowed(), LABEL, NEXT, pendingBorrows(), DIVISIONS, IcsRequest (+11 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.14
Nodes (20): ChangePasswordModalProps, ChangePasswordValues, fields, buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions (+12 more)

### Community 62 - "Architecture"
Cohesion: 0.15
Nodes (11): Architecture, Backend request layering, Commands, Domain model conventions, Frontend (backend/resources/js), graphify, Repo layout (three deployable units), Roles (system-spec.md §2) (+3 more)

### Community 63 - "Seeder"
Cohesion: 0.19
Nodes (7): CatalogSeeder, DatabaseSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.11
Nodes (4): Authenticatable, User, RoleGatingTest, UserAccountLifecycleTest

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.18
Nodes (20): assert_never_rises(), backtest_fixture(), by_id(), Stage 1 replenishment tests — Chapter III, Unit Testing Procedure., Raw per-level objectives (not the carried-forward curve), allowing only the MIP, test_backtest_endpoint_round_trip(), test_backtest_includes_the_offices_actual_orders_when_recorded(), test_backtest_scores_every_rule_from_the_fifth_quarter_on_earlier_quarters_only() (+12 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.19
Nodes (7): Request, Response, StockController, BelongsTo, StockConflict, HasFactory, RefreshDatabase

### Community 73 - "Users.tsx"
Cohesion: 0.12
Nodes (17): onUserChange(), queryClient, ICON_COLOR, SystemToastProvider(), ToastContext, ToastContextValue, ToastItem, VARIANT_ICON (+9 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.33
Nodes (5): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Quick start (Docker)

### Community 78 - "Index.tsx"
Cohesion: 0.22
Nodes (8): ApiError, SettingsTabs(), TABS, columns, makeUserCard(), roleOptions, SettingsUsersIndex(), userFields

### Community 127 - "cache.php"
Cohesion: 0.07
Nodes (12): JsonResponse, Request, StockController, BelongsTo, StockEntry, QuarterHistory, InventoryBurnRateReportBuilder, StockLedger (+4 more)

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 244 - "DispatchTest"
Cohesion: 0.25
Nodes (7): 1. Automated (run before every evaluation session), 2. Offline resilience on devices (3 runs, then Safari separately), 3. Performance: run to dashboard (< 1 s on the office network), 4. User acceptance tasks, 5. Explanation task (target ≥ 80% correct), 6. ISO/IEC 25010:2023 survey, DisasterResource+ — Evaluation Protocol (Chapter III, Software Testing)

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

## Knowledge Gaps
- **264 isolated node(s):** `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props`, `DropdownOption` (+259 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **63 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `Auth Pages & Buttons`, `BaseController CRUD`, `cache.php`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `Controller`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `Filter/Pagination/Map Widgets`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `Directions & Routing Settings API`, `Auth Pages & Buttons`, `Disaster Feed Settings & Tests`, `Record Modal & Image Lightbox`, `Controller`, `cache.php`, `Index.tsx`, `Seeder`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Are the 27 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 27 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `StockEntry` (e.g. with `.test_an_item_with_stock_entries_cannot_be_deleted()` and `.test_dashboard_figures_match_the_database()`) actually correct?**
  _`StockEntry` has 13 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `ResourceType` (e.g. with `.backtest()` and `.index()`) actually correct?**
  _`ResourceType` has 13 INFERRED edges - model-reasoned connections that need verification._
- **What connects `ChangePasswordValues`, `fields`, `ChangePasswordModalProps` to the rest of the system?**
  _284 weakly-connected nodes found - possible documentation gaps or missing edges._