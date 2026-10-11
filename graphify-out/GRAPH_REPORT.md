# Graph Report - laravel-drs  (2026-10-11)

## Corpus Check
- 337 files · ~150,233 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1779 nodes · 3512 edges · 228 communities (166 shown, 62 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 242 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `24e19e31`
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
- types.ts
- QuarterHistory
- Kit
- Toast.tsx
- DataCheckFlag
- FieldOption
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- use-file-upload-field.tsx
- sync.ts
- Controller
- data-table.ts
- Allocation (Domain Model)
- ChangePasswordModal.tsx
- reset.sh
- Architecture
- ReplenishmentController.php
- ReplenishmentServiceClient
- English
- OsrmDirectionsProvider
- DisasterResource+: Where a Real Technical Contribution Can Come From
- test_replenishment.py
- use-file-upload-field.tsx
- InventoryBurnRateCsvExporter.php
- README.md
- Users.tsx
- DisasterResource+
- Automated Disaster Feed Polling
- AppLayout
- test_table7.py
- Tagalog (magalang na Taglish)
- DashboardController
- Dashboard (Page)
- Depot (Domain Model)
- 2026_08_02_085355_split_users_name_into_first_last.php
- 2026_08_02_085419_split_depots_contact_name_into_first_last.php
- DepotCapacityChecker
- ReplenishmentTest.php
- config.py
- DirectionsPanel (React Component)
- configure_logging
- AutoResolveIncidentsJob
- vite-env.d.ts
- init-postgis.sh
- cache.php
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
1. `User` - 75 edges
2. `ResourceType` - 44 edges
3. `Controller` - 36 edges
4. `IcsRequest` - 36 edges
5. `StockEntry` - 35 edges
6. `StockLedger` - 33 edges
7. `BaseController` - 32 edges
8. `ReplenishmentItem` - 32 edges
9. `ReplenishmentRequest` - 29 edges
10. `RequestWorkflow` - 27 edges

## Surprising Connections (you probably didn't know these)
- `Solver Service (FastAPI)` --uses--> `Pydantic`  [EXTRACTED]
  README.md → solver-service/requirements.txt
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `createModuleResource()` --indirect_call--> `body()`  [INFERRED]
  backend/resources/js/api-service/http.ts → backend/docs/demo-video/to-mp4.mjs

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (228 total, 62 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.20
Nodes (21): BacktestRequest, Items carry their full encoded history; the backtest cuts it per quarter., ReplenishmentItem, ReplenishmentRequest, test_budget_never_exceeded_and_money_left_only_when_targets_met(), test_space_constraint_limits_orders_when_modeled(), by_id(), no_solution() (+13 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.15
Nodes (25): useOfflineSync(), useOnlineStatus(), DrsOfflineDB, getDb(), OutboxItem, OutboxItemKind, OutboxItemStatus, changed() (+17 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.07
Nodes (45): ApiEnvelope, ApiError, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError() (+37 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.16
Nodes (5): DepotFactory, ResourceTypeFactory, UserFactory, Factory, static

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.12
Nodes (16): 1. The paper in one minute, 2. Numbers to have in your head, 3. Chapter by chapter: the argument and the support, 4. Glossary, 5. Questions a reviewer is likely to ask, 6. Gaps — what a reviewer could still find, 7. Before you present, DisasterResource+ — reviewer study guide (+8 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.21
Nodes (10): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, DEFAULT_TABS, StatusFilter(), StatusFilterProps (+2 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.17
Nodes (14): PuLP, backtest(), budget_curve(), flat_rule_orders(), _ms(), Budget-limited quarterly replenishment (Chapter III, Model Building).  Stage 1, Re-solves at 11 budget levels, 50%-150% of the entered budget in 10% steps, Textbook rule: average use plus z standard deviations of quarterly use (SAFETY_Z (+6 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.14
Nodes (10): Backtest, DataCheckFlag, BelongsTo, KitLine, QuarterBudget, Reservation, BelongsTo, StockCount (+2 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.12
Nodes (24): errorMessage(), Props, ReplenishmentBacktest(), BASIS_NOTE, BudgetCurve(), BudgetMeter(), DecisionBar(), FLAGGED_REASONS (+16 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.12
Nodes (10): ActivityLogController, JsonResponse, Request, AppLogger, AppLoggerObserver, Model, ActivityLogger, Model (+2 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.15
Nodes (6): HasMany, Division, BelongsTo, HasMany, DivisionTest, HasFactory

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.28
Nodes (6): IcsRequestController, Request, Response, Kit, HasMany, self

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.15
Nodes (23): baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue() (+15 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.09
Nodes (15): DepotController, Model, DivisionController, Builder, Model, JsonResponse, Request, ProfileController (+7 more)

### Community 14 - "Confirmation Modals"
Cohesion: 0.08
Nodes (33): b, body(), box(), boxes(), DIR, find(), ftyp, hasCto (+25 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.17
Nodes (11): Critical, Critique of the paper and the system (2026-09-30), Main weaknesses, Major, Minor, Status, Suggested order, The paper (+3 more)

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.18
Nodes (9): FastAPI, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     ru, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, solve(), health() (+1 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.14
Nodes (16): ConfirmDeleteModal(), ConfirmDeleteModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, ModalCenter(), ModalCenterProps, ModalComponentProps, Size (+8 more)

### Community 19 - "Database Factories"
Cohesion: 0.57
Nodes (6): office_sized_request(), Performance pass marks (Chapter III, Performance Testing Procedure): a Stage 2, test_budget_curve_of_11_resolves_under_20_seconds(), test_stage1_scales_to_200_items(), test_stage2_solves_200_items_12_scenarios_under_2_seconds(), timed()

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.15
Nodes (21): caption(), card(), choose(), click(), entry, form, hygieneRow, log (+13 more)

### Community 21 - "Service Providers"
Cohesion: 0.31
Nodes (3): ActivityLogServiceProvider, AppServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.12
Nodes (16): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DataCheckFlag, Depot, Division, @inertiajs/core (+8 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.15
Nodes (26): InUseEntry, DataTableCardField(), TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta() (+18 more)

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.08
Nodes (25): divisionsService, searchDivisions(), Button(), Props, PageHeader(), normalizeQueryKey(), tableListInvalidateKeys(), usePermission() (+17 more)

### Community 27 - "Dispatch"
Cohesion: 0.14
Nodes (16): Fingerprint(), ICON_COLOR, SystemToastProvider(), ToastContext, ToastContextValue, ToastItem, VARIANT_ICON, VARIANT_STYLES (+8 more)

### Community 29 - "use-module.ts"
Cohesion: 0.15
Nodes (13): 1. Pagbati at layunin (1 min), 2. Ulitin ang problema nila (2 min), 3. Ang solusyon sa isang tingin (1 min), 4. Demo 1: Stock at offline (3 min), 5. Demo 2: RR request at logbook (3 min), 6. Demo 3: Quarterly plan (3 min), 7. Ano ang hindi nito ginagawa (1 min), 8. Ang kailangan namin sa inyo (2 min) (+5 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.22
Nodes (9): 1. What they told us → what we show, 3. Likely questions (client and panel), 4. Ask the client, 5. Before you go on, A. Data to import, B. Users per role, C. Does our form match yours?, D. Setup (+1 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.27
Nodes (3): JsonResponse, Model, UserController

### Community 34 - "AllocationRequest"
Cohesion: 0.24
Nodes (10): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, python-json-logger, Laravel Reverb, Solver Service (FastAPI) (+2 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.06
Nodes (15): IcsRequestController, JsonResponse, Request, Borrow, BelongsTo, HasMany, IcsRequest, BelongsTo (+7 more)

### Community 39 - "Users.tsx"
Cohesion: 0.43
Nodes (3): IcsRequestLine, BelongsTo, HasOne

### Community 40 - ".log"
Cohesion: 0.11
Nodes (6): CatalogSeeder, DatabaseSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 42 - "types.ts"
Cohesion: 0.11
Nodes (11): Depot, HasMany, ResourceType, StockConflict, CatalogTest, EvaluationTest, ExampleTest, TestCase (+3 more)

### Community 44 - "QuarterHistory"
Cohesion: 0.18
Nodes (5): HistoryLine, BelongsTo, HistoryImporter, ReplenishmentDemoSeeder, Carbon

### Community 46 - "Kit"
Cohesion: 0.13
Nodes (14): PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, Conflict, ConflictList(), CountForm(), EntryForm() (+6 more)

### Community 47 - "Toast.tsx"
Cohesion: 0.10
Nodes (21): onUserChange(), queryClient, OfflineBanner(), UserProfileSection(), UserProfileSectionProps, detectSystemTheme(), getStoredTheme(), setStoredTheme() (+13 more)

### Community 50 - "DataCheckFlag"
Cohesion: 0.18
Nodes (11): 1. The idea in one breath, 2. Top bar: Year, Quarter, Budget, Model, 3. The recommendation table, 4. Budget curve and "the next ₱10,000", 5. Approve or Reject, 6. Right column: VED policy, History, Report, Runs, 7. Backtest on past quarters, 8. Closing line (+3 more)

### Community 51 - "FieldOption"
Cohesion: 0.13
Nodes (22): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, Toolbar(), ToolbarProps, ViewToggle(), ViewToggleProps, ParsedApiError (+14 more)

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (34): DisasterResource+ — System Specification, 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper] (+26 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 55 - "use-file-upload-field.tsx"
Cohesion: 0.15
Nodes (18): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, useToast(), buildFormData(), errorMessage(), hasBinaryFiles() (+10 more)

### Community 56 - "sync.ts"
Cohesion: 0.25
Nodes (6): BaseModel, Pydantic, _check_items(), OrderLine, ReplenishmentResponse, _line()

### Community 57 - "Controller"
Cohesion: 0.05
Nodes (34): KitController, JsonResponse, Request, Response, JsonResponse, Request, SmtpSettingController, AuthenticatedSessionController (+26 more)

### Community 58 - "data-table.ts"
Cohesion: 0.10
Nodes (31): StatusBadge(), Tone, subjectOf(), isNetworkError(), Borrow, Borrowed(), day(), Equipment (+23 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.29
Nodes (7): 1. Opening (30 sec), 2. Your problems → what it does for you, 3. Explain each feature with an everyday picture (2 min), 4. What it does **not** do (say this before they ask), 5. Closing (30 sec), 6. Honest claims only, DisasterResource+ in Plain Words — for CDRRMO staff (EN / Tagalog)

### Community 62 - "Architecture"
Cohesion: 0.15
Nodes (11): Architecture, Backend request layering, Commands, Domain model conventions, Frontend (backend/resources/js), graphify, Repo layout (three deployable units), Roles (system-spec.md §2) (+3 more)

### Community 63 - "ReplenishmentController.php"
Cohesion: 0.29
Nodes (7): 2. Demo script (same order and sample values as the video), Close (30 sec), Opening (30 sec), Step 1: Stock, offline and the records seal (2 min), Step 2: ICS 213-RR request and borrowed equipment (3 min), Step 3: Quarterly purchase plan (2 min), Step 4: Quarterly report (1 min)

### Community 65 - "English"
Cohesion: 0.33
Nodes (6): 1. Opening (20 sec), 2. How it works (3 min, point at the screen top to bottom), 3. Our request: your data (1 min), 4. Our question: number of users (30 sec), 5. Closing (10 sec), English

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.05
Nodes (11): Attempting, Attribute, Authenticatable, PreventInactiveLogin, User, ReplenishmentTest, RoleGatingTest, UserAccountLifecycleTest (+3 more)

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.17
Nodes (21): assert_never_rises(), backtest_fixture(), by_id(), Stage 1 replenishment tests — Chapter III, Unit Testing Procedure., Raw per-level objectives (not the carried-forward curve), allowing only the MIP, test_backtest_endpoint_round_trip(), test_backtest_includes_the_classical_safety_stock_rule(), test_backtest_includes_the_offices_actual_orders_when_recorded() (+13 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.05
Nodes (29): ReplenishmentRunUpdated, JsonResponse, Request, ReplenishmentController, Throwable, RunReplenishmentJob, NewAccountMail, Content (+21 more)

### Community 70 - "InventoryBurnRateCsvExporter.php"
Cohesion: 0.13
Nodes (17): Field(), ImageLightbox(), ImageLightboxProps, AsyncMultiSelectField(), AsyncMultiSelectFieldProps, AsyncSelectField(), valuesEqual(), FileUploadField() (+9 more)

### Community 73 - "Users.tsx"
Cohesion: 0.60
Nodes (3): Request, Response, StockController

### Community 74 - "DisasterResource+"
Cohesion: 0.33
Nodes (5): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Quick start (Docker)

### Community 77 - "test_table7.py"
Cohesion: 0.39
Nodes (6): expected_shortage(), items(), Table 7 (Chapter III, Worked Example): in-sample expected shortage of the three, Weighted shortage in quarters of average use, summed over items, averaged over t, test_per_item_multipliers_in_the_table_note(), test_table_7_values_and_the_sentences_under_it()

### Community 78 - "Tagalog (magalang na Taglish)"
Cohesion: 0.33
Nodes (6): 1. Pagbati (20 seg), 2. Paano ito gumagana (3 min, ituro ang screen mula taas pababa), 3. Ang aming hiling: ang data ninyo (1 min), 4. Ang aming tanong: ilan ang gagamit (30 seg), 5. Pagtatapos (10 seg), Tagalog (magalang na Taglish)

### Community 86 - "ReplenishmentTest.php"
Cohesion: 0.25
Nodes (8): covering_level(), item_target(), Use that would have covered k of the last m quarters: the (m-k+1)-th highest. Wi, (target, basis, multiplier). Target None means the item is listed for Logistics, test_cold_start_rules(), test_covering_level_follows_ved_coverage(), test_multiplier_is_covering_level_over_average(), test_covering_level_scales_k_with_short_history()

### Community 127 - "cache.php"
Cohesion: 0.10
Nodes (8): JsonResponse, Request, StockController, BelongsTo, StockEntry, StockLedger, StockLedgerTest, Collection

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
- **372 isolated node(s):** `OUT`, `shotsOnly`, `entry`, `hygieneRow`, `waiting` (+367 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **62 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Auth Pages & Buttons`, `Users.tsx`, `Disaster Feed Settings & Tests`, `BaseController CRUD`, `cache.php`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `Depot & Resource Models`, `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `.log`, `Resource Types & Admin Layout`, `types.ts`, `QuarterHistory`, `Controller`, `cache.php`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `.log`, `DisasterFeedIngestor`, `Controller`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Are the 33 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 33 INFERRED edges - model-reasoned connections that need verification._
- **Are the 15 inferred relationships involving `ResourceType` (e.g. with `.backtest()` and `.borrowed()`) actually correct?**
  _`ResourceType` has 15 INFERRED edges - model-reasoned connections that need verification._
- **What connects `OUT`, `shotsOnly`, `entry` to the rest of the system?**
  _395 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.14623655913978495 - nodes in this community are weakly interconnected._