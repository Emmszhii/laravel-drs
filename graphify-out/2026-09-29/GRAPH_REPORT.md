# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 430 files · ~151,951 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2022 nodes · 4169 edges · 248 communities (177 shown, 71 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 271 edges (avg confidence: 0.78)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `209c90d1`
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
- SmtpSetting
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
- vite-env.d.ts
- entrypoint.sh
- init-postgis.sh
- cache.php
- formatDate
- ResourceController.php
- RecordModalField.tsx
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
- Dashboard.tsx
- watch-boneyard.mjs
- refresh-boneyard-session.mjs
- InertiaPageResponse
- Index.tsx
- DepotController.php
- Open a Claude-in-Chrome tab
- PublicDashboardController.php
- SmtpSettingsController.php
- ProviderQuotaExceededException.php

## God Nodes (most connected - your core abstractions)
1. `User` - 75 edges
2. `Disaster` - 63 edges
3. `Resource` - 57 edges
4. `Depot` - 54 edges
5. `Controller` - 50 edges
6. `TestCase` - 46 edges
7. `BaseController` - 44 edges
8. `ResourceType` - 42 edges
9. `ResourceRequest` - 36 edges
10. `Zone` - 30 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `ReportsIndex()` --calls--> `tableListInvalidateKeys()`  [EXTRACTED]
  backend/resources/js/Pages/Reports/Index.tsx → backend/resources/js/Components/table/utils/index.ts
- `UseModuleOptions` --references--> `CrudQueryParams`  [EXTRACTED]
  backend/resources/js/Hooks/use-module.ts → backend/resources/js/Types/reusable/pagination.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (248 total, 71 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.05
Nodes (16): DisasterFeedSettingController, JsonResponse, Request, DisasterFeedSetting, DepotCapacityChecker, Collection, DisasterFeedIngestor, GdacsFeedClient (+8 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.06
Nodes (50): ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner(), SystemToastProvider() (+42 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.13
Nodes (30): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+22 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.30
Nodes (10): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, RunReplenishmentJob, SnapshotQuarterlyStockJob, Dispatchable, InteractsWithQueue, Queueable (+2 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.12
Nodes (11): Depot, BelongsTo, Resource, ResourceType, Zone, ResourceFactory, RequestApprovalReservationTest, ResourceLoanReservationTest (+3 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.11
Nodes (22): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DefaultRecordCardProps, DEFAULT_TABS (+14 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.10
Nodes (14): Allocation, HasMany, EvacuationCenter, BelongsTo, HistoryLine, BelongsTo, OptimizationRun, BelongsTo (+6 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.13
Nodes (10): LogPageVisit, Closure, Request, Response, AppLoggerObserver, Model, ActivityLogger, Model (+2 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.12
Nodes (23): resourceLoansService, resourcesService, resourceTypesService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), ALGORITHM_LABELS, Props (+15 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.09
Nodes (11): BelongsTo, HasMany, DispatchDeliveryDeductionTest, DispatchTest, ExampleTest, IdempotentCreateTest, SnapshotQuarterlyStockJobTest, TestCase (+3 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.05
Nodes (62): Field(), baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik() (+54 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.10
Nodes (19): Button(), Props, FilterBar(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER (+11 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.13
Nodes (6): Authenticatable, User, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest, HasRoles, Notifiable

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.16
Nodes (11): FetchingSpinner(), DefaultRecordCard(), PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, ViewToggle(), ViewToggleProps (+3 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 19 - "Database Factories"
Cohesion: 0.09
Nodes (11): DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, QuarterlyStockSnapshotFactory, ReportFactory, ResourceRequestFactory (+3 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.18
Nodes (14): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, SettingsListHeader(), SettingsRow(), TextCell(), IsCurrentOrParentUrlFn (+6 more)

### Community 21 - "Service Providers"
Cohesion: 0.14
Nodes (7): Attempting, PreventInactiveLogin, ActivityLogServiceProvider, AppServiceProvider, EventServiceProvider, InertiaServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.06
Nodes (30): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DashboardStats, DataCheckFlag, Depot, DirectionsResult (+22 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.12
Nodes (9): JsonResponse, Request, SmtpSettingController, self, self, self, SmtpSetting, UserFactory (+1 more)

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.17
Nodes (11): 0. Current state (what's real vs. skeleton vs. missing), Backend API, Database, Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS, Matrix & Routing, Optimization Engine — the biggest gap, Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver, Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP (+3 more)

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.13
Nodes (6): DisasterAlertEvent, DispatchPositionUpdated, ReplenishmentRunUpdated, InteractsWithSockets, ShouldBroadcast, ShouldBroadcastNow

### Community 28 - "ReportController.php"
Cohesion: 0.11
Nodes (18): Builder, JsonResponse, Model, Request, Response, ReportController, BelongsTo, InventoryBurnRateCsvExporter (+10 more)

### Community 29 - "use-module.ts"
Cohesion: 0.13
Nodes (27): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+19 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.23
Nodes (13): BacktestPanel(), BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props (+5 more)

### Community 32 - "SerializesModels"
Cohesion: 0.16
Nodes (5): BelongsTo, QuarterlyStockSnapshot, ReorderSuggestionCalculator, InventoryBurnRateReportBuilder, ReorderSuggestionCalculatorTest

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.13
Nodes (5): Disaster, HasMany, AutoResolveIncidentsJobTest, OptimizationFallbackTest, HasManyThrough

### Community 34 - "AllocationRequest"
Cohesion: 0.08
Nodes (36): ABC, Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, PuLP, Pydantic (+28 more)

### Community 37 - "IncidentController"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 39 - "Users.tsx"
Cohesion: 0.10
Nodes (18): usersService, SettingsTabs(), TABS, AdminLayout(), navItems, STAFF_ROLES, systemAdminNavItems, INTERVAL_LABELS (+10 more)

### Community 40 - ".log"
Cohesion: 0.22
Nodes (9): Depot, DispatchMarker, Incident, IncidentMap(), PH_CENTER, Props, RouteGeometry, LatLng (+1 more)

### Community 41 - "DisasterAlertEvent"
Cohesion: 0.24
Nodes (5): JsonResponse, Request, ProfileController, Model, ResourceTypeController

### Community 42 - "DispatchController"
Cohesion: 0.30
Nodes (4): DispatchController, JsonResponse, Model, Request

### Community 44 - "SmtpSetting"
Cohesion: 0.27
Nodes (3): JsonResponse, Model, UserController

### Community 46 - "index.ts"
Cohesion: 0.36
Nodes (8): GlobalModalContext, GlobalModalProvider(), createGlobalModalStore(), GlobalModalStore, Listener, ModalEntry, GlobalModalHandle, useGlobalModal()

### Community 47 - "static"
Cohesion: 0.32
Nodes (5): Builder, JsonResponse, Model, Request, ResourceLoanController

### Community 50 - "api.php"
Cohesion: 0.13
Nodes (10): Builder, JsonResponse, Request, RequestController, DashboardController, Response, BelongsTo, HasMany (+2 more)

### Community 51 - "Inertia"
Cohesion: 0.20
Nodes (7): DirectionsController, JsonResponse, Request, Response, RoutingSettingsController, RoutingSetting, DirectionsRouter

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
Nodes (9): Controller, DepotController, Response, DispatchController, Response, Response, ReportController, Response (+1 more)

### Community 58 - "data-table.ts"
Cohesion: 0.26
Nodes (11): buildRecordMenu(), CardActions, ColumnFilterType, DataTableProps, ModalState, RenderModalProps, StatusFilterTab, CrudQueryParams (+3 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 61 - "RecordModal.tsx"
Cohesion: 0.16
Nodes (15): fetchJson(), PsgcOption, sortByName(), usePsgc(), columns, DepotsIndex(), emptyForm, NO_INCIDENTS (+7 more)

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.13
Nodes (9): HasMany, DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder, Seeder (+1 more)

### Community 64 - "FormModal.tsx"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 65 - "DisasterFeedIngestorTest"
Cohesion: 0.15
Nodes (13): JsonResponse, Request, ReplenishmentController, Request, Response, ReplenishmentController, Backtest, DataCheckFlag (+5 more)

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.07
Nodes (54): FastAPI, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     run, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, solve(), BacktestRequest (+46 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.25
Nodes (7): ApiError, requestsService, approvalColor, columns, makeRequestCard(), RequestsIndex(), statusColor

### Community 70 - "UserController"
Cohesion: 0.36
Nodes (3): BelongsTo, Report, ReportDownloadTest

### Community 72 - "SmtpSetting"
Cohesion: 0.43
Nodes (4): ActivityLogController, JsonResponse, Request, AppLogger

### Community 73 - "Users.tsx"
Cohesion: 0.18
Nodes (11): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, ToastContext, ToastContextValue, ToastItem (+3 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 78 - "Index.tsx"
Cohesion: 0.19
Nodes (11): depotsService, dispatchesService, Modal(), Props, PositionUpdatePayload, useDispatchTracking(), useLiveGeolocation(), COORDINATOR_TIER_ROLES (+3 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 108 - "EvacuationCenterController"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 127 - "cache.php"
Cohesion: 0.08
Nodes (14): JsonResponse, Request, StockController, Request, Response, StockController, BelongsTo, StockConflict (+6 more)

### Community 140 - "ResourceController.php"
Cohesion: 0.31
Nodes (4): Builder, JsonResponse, Model, ResourceController

### Community 233 - "Dashboard.tsx"
Cohesion: 0.24
Nodes (7): DashboardLayout(), Props, Dashboard(), Props, requestColumns, statusColor, utilizationBarColor()

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 237 - "InertiaPageResponse"
Cohesion: 0.16
Nodes (6): EvacuationCenterController, Response, IncidentController, Response, InertiaPageResponse, Inertia

### Community 238 - "Index.tsx"
Cohesion: 0.17
Nodes (8): evacuationCentersService, incidentsService, columns, EvacuationIndex(), fields, columns, reportFields, ReportsIndex()

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

### Community 252 - "PublicDashboardController.php"
Cohesion: 0.18
Nodes (7): DisasterFeedSettingsController, Response, OptimizationController, RedirectResponse, Response, PublicDashboardController, RunOptimizationJob

### Community 254 - "ProviderQuotaExceededException.php"
Cohesion: 0.36
Nodes (5): JsonResponse, Request, RoutingSettingController, ProviderQuotaExceededException, RuntimeException

## Knowledge Gaps
- **342 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+337 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **71 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `Admin API Controllers`, `DisasterFeedIngestorTest`, `ResourceInventoryController`, `SmtpSetting`, `BaseController CRUD`, `InertiaPageResponse`, `api.php`, `Inertia`, `AuthenticatedSessionController.php`, `Record Modal & Image Lightbox`, `PasswordResetController.php`, `PublicDashboardController.php`, `SmtpSettingsController.php`, `ProviderQuotaExceededException.php`, `cache.php`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `IncidentController`, `DisasterAlertEvent`, `DispatchController`, `EvacuationCenterController`, `ResourceController.php`, `SmtpSetting`, `static`, `api.php`, `DepotController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Why does `Disaster` connect `DisasterFeedIngestor` to `Admin API Controllers`, `OsrmDirectionsProvider`, `Depot & Resource Models`, `Filter/Pagination/Map Widgets`, `IncidentController`, `Dashboard & Allocation Models`, `UserController`, `Disaster Feed Settings & Tests`, `api.php`, `Database Factories`, `PublicDashboardController.php`, `IncidentMap.tsx`, `ReportController.php`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 54 inferred relationships involving `User` (e.g. with `.drivers()` and `.handle()`) actually correct?**
  _`User` has 54 INFERRED edges - model-reasoned connections that need verification._
- **Are the 28 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.index()`) actually correct?**
  _`Disaster` has 28 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _366 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.05174825174825175 - nodes in this community are weakly interconnected._