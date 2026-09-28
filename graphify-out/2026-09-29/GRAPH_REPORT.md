# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 414 files · ~142,117 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1857 nodes · 3713 edges · 251 communities (181 shown, 70 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 179 edges (avg confidence: 0.78)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `9becdc0a`
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
- IncidentMap.tsx
- Dispatch
- ReportController.php
- use-module.ts
- Composer
- RoleGatingTest
- SerializesModels
- DisasterFeedIngestor
- AllocationRequest
- BaseSolver
- PasswordResetMail.php
- IncidentController
- Users.tsx
- .log
- DisasterAlertEvent
- DispatchController
- SmtpSetting
- index.ts
- static
- api.php
- Inertia
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- AuthenticatedSessionController.php
- PasswordResetController.php
- Controller
- data-table.ts
- Allocation (Domain Model)
- ChangePasswordModal.tsx
- RecordModal.tsx
- Architecture
- Seeder
- FormModal.tsx
- DisasterFeedIngestorTest
- OsrmDirectionsProvider
- DisasterResource+: Where a Real Technical Contribution Can Come From
- test_replenishment.py
- use-file-upload-field.tsx
- UserController
- ResourceInventoryController
- RoutingSettingController.php
- Users.tsx
- DisasterResource+
- Automated Disaster Feed Polling
- AppLayout
- api.php
- Index.tsx
- DashboardController
- Dashboard (Page)
- Depot (Domain Model)
- Permissions
- 2026_08_02_085355_split_users_name_into_first_last.php
- 2026_08_02_085419_split_depots_contact_name_into_first_last.php
- DepotCapacityChecker
- use-flash-toast.ts
- config.py
- Button.tsx
- TableLoading.tsx
- EvacuationCenterController
- DirectionsPanel (React Component)
- configure_logging
- AutoResolveIncidentsJob
- solve
- vite-env.d.ts
- entrypoint.sh
- init-postgis.sh
- formatDate
- ResourceController.php
- replenishment.py
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
- ActivityLogController.php
- watch-boneyard.mjs
- refresh-boneyard-session.mjs
- OptimizationController.php
- InertiaPageResponse
- PulpAllocationSolver
- EnsureUserIsActiveTest
- IncidentController.php
- Docker Compose
- DisasterFeedIngestorTest
- EventServiceProvider.php
- DepotController.php
- Open a Claude-in-Chrome tab
- ReplenishmentServiceClient

## God Nodes (most connected - your core abstractions)
1. `User` - 89 edges
2. `Disaster` - 63 edges
3. `Resource` - 56 edges
4. `ResourceType` - 51 edges
5. `BaseController` - 44 edges
6. `Controller` - 44 edges
7. `TestCase` - 44 edges
8. `ResourceRequest` - 36 edges
9. `Zone` - 30 edges
10. `Depot` - 29 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Solver Service (FastAPI)`  [EXTRACTED]
  docker-compose.yml → README.md
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `ColumnFilterControlProps` --references--> `ColumnDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/ColumnFilterControl.tsx → backend/resources/js/Types/reusable/data-table.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (251 total, 70 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.10
Nodes (14): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+6 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.22
Nodes (17): OfflineBanner(), useOfflineSync(), useOnlineStatus(), DrsOfflineDB, getDb(), OutboxItem, OutboxItemKind, OutboxItemStatus (+9 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.12
Nodes (30): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+22 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.10
Nodes (12): JsonResponse, Request, ReplenishmentController, DataCheckFlag, BelongsTo, HistoryLine, BelongsTo, ReplenishmentRun (+4 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.09
Nodes (12): BelongsTo, QuarterlyStockSnapshot, BelongsTo, Resource, HasMany, ResourceType, ReorderSuggestionCalculator, InventoryBurnRateReportBuilder (+4 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.13
Nodes (19): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DEFAULT_TABS, StatusFilter() (+11 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.11
Nodes (16): Allocation, EvacuationCenter, BelongsTo, OptimizationRun, BelongsTo, HasMany, QuarterBudget, BelongsTo (+8 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.36
Nodes (5): JsonResponse, Request, RoutingSettingController, ProviderQuotaExceededException, RuntimeException

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.12
Nodes (26): resourceTypesService, tableListInvalidateKeys(), usePermission(), AdminLayout(), navItems, STAFF_ROLES, systemAdminNavItems, EvacuationIndex() (+18 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.11
Nodes (9): DispatchDeliveryDeductionTest, ExampleTest, IdempotentCreateTest, TestCase, ExampleTest, GreedyAllocationSolverTest, BaseTestCase, GeofenceService (+1 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.11
Nodes (20): ConfirmArchiveAccountModal(), ConfirmArchiveAccountModalProps, ConfirmDeleteModal(), ConfirmDeleteModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, Modal(), ModalCenter() (+12 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.07
Nodes (34): ApiError, FilterBar(), Props, Modal(), Props, Pagination(), Props, LocationPickMap() (+26 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.12
Nodes (5): Authenticatable, User, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest, EnsureUserIsActiveTest

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.11
Nodes (21): evacuationCentersService, incidentsService, FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, Toolbar(), ToolbarProps, DataTableCardField() (+13 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 19 - "Database Factories"
Cohesion: 0.09
Nodes (11): DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, QuarterlyStockSnapshotFactory, ReportFactory, ResourceRequestFactory (+3 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.06
Nodes (39): ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), RowMenu(), RowMenuAction (+31 more)

### Community 21 - "Service Providers"
Cohesion: 0.20
Nodes (4): ActivityLogServiceProvider, AppServiceProvider, InertiaServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.09
Nodes (21): AdminUser, DashboardStats, Depot, DirectionsResult, Disaster, DisasterFeedSettingsData, Dispatch, EvacuationCenter (+13 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.17
Nodes (4): DisasterFeedSetting, self, UsgsEarthquakeFeedClient, DisasterFeedSettingTest

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.17
Nodes (11): 0. Current state (what's real vs. skeleton vs. missing), Backend API, Database, Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS, Matrix & Routing, Optimization Engine — the biggest gap, Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver, Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP (+3 more)

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.18
Nodes (6): DashboardController, Response, DisasterFeedSettingsController, Response, Response, SmtpSettingsController

### Community 27 - "Dispatch"
Cohesion: 0.19
Nodes (3): Dispatch, BelongsTo, DispatchTest

### Community 28 - "ReportController.php"
Cohesion: 0.11
Nodes (18): Builder, JsonResponse, Model, Request, Response, ReportController, BelongsTo, InventoryBurnRateCsvExporter (+10 more)

### Community 29 - "use-module.ts"
Cohesion: 0.14
Nodes (26): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+18 more)

### Community 32 - "SerializesModels"
Cohesion: 0.35
Nodes (10): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, RunReplenishmentJob, SnapshotQuarterlyStockJob, Dispatchable, InteractsWithQueue, Queueable (+2 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.15
Nodes (5): DisasterFeedIngestor, GeofenceService, Collection, GreedyAllocationSolver, OptimizationServiceClient

### Community 34 - "AllocationRequest"
Cohesion: 0.18
Nodes (14): ABC, optimize_allocation(), Called by App\\Jobs\\RunOptimizationJob (Laravel) whenever an incident     is l, AllocationLine, AllocationRequest, AllocationResponse, DepotCapacity, BaseModel (+6 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 37 - "IncidentController"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 39 - "Users.tsx"
Cohesion: 0.12
Nodes (11): Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, INTERVAL_LABELS, Props, STATUS_COLORS (+3 more)

### Community 40 - ".log"
Cohesion: 0.11
Nodes (17): sharesAdminAccess(), Depot, DispatchMarker, Incident, IncidentMap(), PH_CENTER, Props, RouteGeometry (+9 more)

### Community 41 - "DisasterAlertEvent"
Cohesion: 0.20
Nodes (4): DisasterAlertEvent, DispatchPositionUpdated, InteractsWithSockets, ShouldBroadcast

### Community 42 - "DispatchController"
Cohesion: 0.30
Nodes (4): DispatchController, JsonResponse, Model, Request

### Community 44 - "SmtpSetting"
Cohesion: 0.25
Nodes (5): JsonResponse, Request, SmtpSettingController, self, SmtpSetting

### Community 46 - "index.ts"
Cohesion: 0.36
Nodes (8): GlobalModalContext, GlobalModalProvider(), createGlobalModalStore(), GlobalModalStore, Listener, ModalEntry, GlobalModalHandle, useGlobalModal()

### Community 47 - "static"
Cohesion: 0.32
Nodes (5): Builder, JsonResponse, Model, Request, ResourceLoanController

### Community 50 - "api.php"
Cohesion: 0.17
Nodes (8): Builder, JsonResponse, Request, RequestController, BelongsTo, HasMany, ResourceRequest, AllocationFactory

### Community 51 - "Inertia"
Cohesion: 0.17
Nodes (8): DirectionsController, JsonResponse, Request, Response, RoutingSettingsController, self, RoutingSetting, DirectionsRouter

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 55 - "AuthenticatedSessionController.php"
Cohesion: 0.36
Nodes (4): AuthenticatedSessionController, RedirectResponse, Request, Response

### Community 56 - "PasswordResetController.php"
Cohesion: 0.42
Nodes (4): PasswordResetController, RedirectResponse, Request, Response

### Community 57 - "Controller"
Cohesion: 0.16
Nodes (9): Controller, DepotController, Response, EvacuationCenterController, Response, IncidentController, Response, Response (+1 more)

### Community 58 - "data-table.ts"
Cohesion: 0.14
Nodes (17): PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, ViewToggle(), ViewToggleProps, ParsedApiError, ColumnFilterType (+9 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.14
Nodes (18): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, buildFormData(), errorMessage(), hasBinaryFiles(), useModule() (+10 more)

### Community 61 - "RecordModal.tsx"
Cohesion: 0.15
Nodes (13): Field(), FormModalBody(), Values, buildInitialValues(), collectErrors(), mapApiErrors(), RecordModal(), seedFieldValue() (+5 more)

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.20
Nodes (7): DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 64 - "FormModal.tsx"
Cohesion: 0.24
Nodes (17): baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue() (+9 more)

### Community 65 - "DisasterFeedIngestorTest"
Cohesion: 0.10
Nodes (11): Depot, HasMany, Disaster, HasMany, BelongsTo, HasMany, Zone, OptimizationFallbackTest (+3 more)

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.29
Nodes (3): DisasterFeedSettingController, JsonResponse, Request

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.26
Nodes (16): ReplenishmentItem, ReplenishmentRequest, item_target(), (target, basis, multiplier). Target None means the item is listed for Logistics, by_id(), Stage 1 replenishment tests — Chapter III, Unit Testing Procedure., test_budget_never_exceeded_and_money_left_only_when_targets_met(), test_cold_start_rules() (+8 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.22
Nodes (11): ImageLightbox(), ImageLightboxProps, emptyFileFieldValue, fileNameFromUrl(), FileUploadField(), FileUploadFieldProps, formatBytes(), isImageName() (+3 more)

### Community 70 - "UserController"
Cohesion: 0.27
Nodes (3): JsonResponse, Model, UserController

### Community 73 - "Users.tsx"
Cohesion: 0.23
Nodes (10): APP_ROLES, AppRole, normalizeRole(), ROLE_LABELS, useToast(), columns, makeUserCard(), roleOptions (+2 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 77 - "api.php"
Cohesion: 0.24
Nodes (5): JsonResponse, Request, ProfileController, Model, ResourceTypeController

### Community 78 - "Index.tsx"
Cohesion: 0.29
Nodes (8): dispatchesService, PositionUpdatePayload, useDispatchTracking(), useLiveGeolocation(), COORDINATOR_TIER_ROLES, DispatchIndex(), emptyForm, STATUS_LABELS

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.18
Nodes (5): Attribute, UserFactory, HasRoles, Notifiable, static

### Community 108 - "EvacuationCenterController"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 118 - "solve"
Cohesion: 0.20
Nodes (7): FastAPI, optimize_routing(), Depot-to-zone routing suggestions (straight-line v1; OSRM later)., Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     run, solve(), health(), Health check consumed by Docker/Compose and the backend's depends_on condition.

### Community 140 - "ResourceController.php"
Cohesion: 0.31
Nodes (4): Builder, JsonResponse, Model, ResourceController

### Community 155 - "replenishment.py"
Cohesion: 0.25
Nodes (7): PuLP, covering_level(), _ms(), Budget-limited quarterly replenishment (Chapter III, Model Building).  Stage 1 —, Use that would have covered k of the last m quarters: the (m-k+1)-th highest., solve(), test_covering_level_follows_ved_coverage()

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 236 - "OptimizationController.php"
Cohesion: 0.50
Nodes (3): OptimizationController, RedirectResponse, RunOptimizationJob

### Community 237 - "InertiaPageResponse"
Cohesion: 0.16
Nodes (6): DispatchController, Response, Response, ReportController, InertiaPageResponse, Inertia

### Community 238 - "PulpAllocationSolver"
Cohesion: 0.39
Nodes (8): PulpAllocationSolver, Minimizes unmet demand across zones, subject to depot capacity.     This is the, Performance and correctness bench tests for the allocation/routing solvers.  R, sample_payload(), test_ortools_routing_solver_returns_a_response(), test_pulp_solver_fully_satisfies_demand_within_capacity(), test_pulp_solver_reports_unmet_demand_when_capacity_insufficient(), test_pulp_solver_runs_within_reasonable_time()

### Community 240 - "IncidentController.php"
Cohesion: 0.29
Nodes (7): Pydantic, python-json-logger, Solver Service (FastAPI), OrderLine, BaseModel, ReplenishmentResponse, Uvicorn

### Community 244 - "Docker Compose"
Cohesion: 0.38
Nodes (7): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, Laravel Reverb, Traefik

### Community 246 - "EventServiceProvider.php"
Cohesion: 0.53
Nodes (3): Attempting, PreventInactiveLogin, EventServiceProvider

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

## Knowledge Gaps
- **324 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+319 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **70 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Auth Model & Seeder` to `DisasterFeedIngestorTest`, `Depot & Resource Models`, `Filter/Pagination/Map Widgets`, `UserController`, `Dashboard & Allocation Models`, `DispatchController`, `TableLoading.tsx`, `SmtpSetting`, `Disaster Feed Settings & Tests`, `EventServiceProvider.php`, `Dispatch`, `RoleGatingTest`, `Seeder`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `IncidentController`, `UserController`, `DispatchController`, `EvacuationCenterController`, `api.php`, `ResourceController.php`, `static`, `api.php`, `DepotController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Why does `Controller` connect `Controller` to `Admin API Controllers`, `OsrmDirectionsProvider`, `Depot & Resource Models`, `ResourceInventoryController`, `Directions & Routing Settings API`, `ActivityLogController.php`, `SmtpSetting`, `BaseController CRUD`, `InertiaPageResponse`, `OptimizationController.php`, `Inertia`, `AuthenticatedSessionController.php`, `PasswordResetController.php`, `IncidentMap.tsx`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Are the 28 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.index()`) actually correct?**
  _`Disaster` has 28 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _342 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.09841269841269841 - nodes in this community are weakly interconnected._
- **Should `API HTTP Client` be split into smaller, more focused modules?**
  _Cohesion score 0.12427409988385599 - nodes in this community are weakly interconnected._