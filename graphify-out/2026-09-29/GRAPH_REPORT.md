# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 446 files · ~160,322 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2151 nodes · 4529 edges · 259 communities (187 shown, 72 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 319 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a4dfac9f`
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
- RequestController.php
- InertiaPageResponse
- Index.tsx
- DepotCapacityChecker
- GdacsFeedClient
- DispatchTest
- DirectionsController.php
- ResourceTypeController.php
- DepotController.php
- Open a Claude-in-Chrome tab
- PublicDashboardController.php
- UsersController.php
- PublicDashboardController.php
- ProviderQuotaExceededException.php
- .makeRequestAndDepot
- EvacuationCenterController.php
- permission.php

## God Nodes (most connected - your core abstractions)
1. `User` - 85 edges
2. `Disaster` - 63 edges
3. `Controller` - 54 edges
4. `Resource` - 52 edges
5. `ResourceType` - 50 edges
6. `TestCase` - 50 edges
7. `BaseController` - 44 edges
8. `Depot` - 39 edges
9. `ResourceRequest` - 36 edges
10. `StockLedger` - 31 edges

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

## Communities (259 total, 72 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.15
Nodes (5): DisasterFeedIngestor, GeofenceService, Collection, GreedyAllocationSolver, OptimizationServiceClient

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.12
Nodes (32): ApiError, useOfflineSync(), useOnlineStatus(), DrsOfflineDB, getDb(), OutboxItem, OutboxItemKind, OutboxItemStatus (+24 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.12
Nodes (30): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+22 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.30
Nodes (10): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, RunReplenishmentJob, SnapshotQuarterlyStockJob, Dispatchable, InteractsWithQueue, Queueable (+2 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.08
Nodes (15): Depot, HasMany, Disaster, HasMany, HasMany, ResourceType, BelongsTo, HasMany (+7 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.11
Nodes (22): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DefaultRecordCardProps, DEFAULT_TABS (+14 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.10
Nodes (16): Backtest, EvacuationCenter, BelongsTo, OptimizationRun, BelongsTo, HasMany, QuarterBudget, BelongsTo (+8 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.17
Nodes (8): ReplenishmentRunUpdated, JsonResponse, Request, ReplenishmentController, ReplenishmentRun, HistoryImporter, ShouldBroadcastNow, SymfonyResponse

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.10
Nodes (14): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+6 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.10
Nodes (32): resourceTypesService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), enqueue(), EvacuationIndex(), IncidentsIndex(), ALGORITHM_LABELS (+24 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.11
Nodes (10): DispatchDeliveryDeductionTest, ExampleTest, IdempotentCreateTest, SnapshotQuarterlyStockJobTest, TestCase, ExampleTest, GreedyAllocationSolverTest, BaseTestCase (+2 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.05
Nodes (61): Field(), baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik() (+53 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.09
Nodes (27): depotsService, FilterBar(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER, Props (+19 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.20
Nodes (14): buildRecordMenu(), ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState, RenderModalProps (+6 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 19 - "Database Factories"
Cohesion: 0.10
Nodes (10): AllocationFactory, DepotFactory, DisasterFactory, EvacuationCenterFactory, OptimizationRunFactory, ReportFactory, ResourceRequestFactory, ResourceTypeFactory (+2 more)

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
Cohesion: 0.11
Nodes (9): JsonResponse, Request, SmtpSettingController, self, self, self, SmtpSetting, UserFactory (+1 more)

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.17
Nodes (11): 0. Current state (what's real vs. skeleton vs. missing), Backend API, Database, Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS, Matrix & Routing, Optimization Engine — the biggest gap, Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver, Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP (+3 more)

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.21
Nodes (4): DisasterAlertEvent, DispatchPositionUpdated, InteractsWithSockets, ShouldBroadcast

### Community 27 - "Dispatch"
Cohesion: 0.21
Nodes (3): Dispatch, BelongsTo, DispatchTest

### Community 28 - "ReportController.php"
Cohesion: 0.18
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, InventoryBurnRateCsvExporter, StreamedResponse

### Community 29 - "use-module.ts"
Cohesion: 0.15
Nodes (25): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+17 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.23
Nodes (13): BacktestPanel(), BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props (+5 more)

### Community 32 - "SerializesModels"
Cohesion: 0.14
Nodes (7): BelongsTo, QuarterlyStockSnapshot, BelongsTo, Resource, ReorderSuggestionCalculator, ResourceLoanReservationTest, ReorderSuggestionCalculatorTest

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.16
Nodes (11): FetchingSpinner(), DefaultRecordCard(), PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, ViewToggle(), ViewToggleProps (+3 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.08
Nodes (36): ABC, Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, PuLP, Pydantic (+28 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.07
Nodes (17): IcsRequestController, JsonResponse, Request, IcsRequestController, Request, Response, Borrow, BelongsTo (+9 more)

### Community 37 - "IncidentController"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 39 - "Users.tsx"
Cohesion: 0.12
Nodes (11): Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, INTERVAL_LABELS, Props, STATUS_COLORS (+3 more)

### Community 40 - ".log"
Cohesion: 0.12
Nodes (18): Modal(), Props, Depot, DispatchMarker, Incident, IncidentMap(), PH_CENTER, Props (+10 more)

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
Nodes (6): Allocation, BelongsTo, BelongsTo, HasMany, ResourceRequest, AutoResolveIncidentsJobTest

### Community 51 - "Inertia"
Cohesion: 0.29
Nodes (4): Response, RoutingSettingsController, RoutingSetting, DirectionsRouter

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
Cohesion: 0.15
Nodes (18): Borrow, Borrowed(), LABEL, NEXT, DIVISIONS, FileForm(), IcsRequest, Item (+10 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 61 - "RecordModal.tsx"
Cohesion: 0.09
Nodes (9): DisasterFeedSettingController, JsonResponse, Request, DisasterFeedSetting, DepotCapacityChecker, Collection, GdacsFeedClient, UsgsEarthquakeFeedClient (+1 more)

### Community 62 - "Architecture"
Cohesion: 0.13
Nodes (13): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+5 more)

### Community 63 - "Seeder"
Cohesion: 0.22
Nodes (7): DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, ReplenishmentDemoSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 64 - "FormModal.tsx"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.10
Nodes (5): Authenticatable, User, RoleGatingTest, HasRoles, Notifiable

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.06
Nodes (60): FastAPI, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     run, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, solve(), BacktestRequest (+52 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.11
Nodes (5): HistoryLine, BelongsTo, DisasterFeedIngestorTest, EvaluationTest, Carbon

### Community 70 - "UserController"
Cohesion: 0.24
Nodes (10): ActivityLogEntry, activityLogService, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner(), AdminLayout(), navItems (+2 more)

### Community 72 - "SmtpSetting"
Cohesion: 0.26
Nodes (10): UserProfileSection(), UserProfileSectionProps, AuthUser, initialsFor(), useAuth(), detectSystemTheme(), getStoredTheme(), setStoredTheme() (+2 more)

### Community 73 - "Users.tsx"
Cohesion: 0.12
Nodes (14): queryClient, ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, SystemToastProvider(), ToastContext (+6 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 78 - "Index.tsx"
Cohesion: 0.21
Nodes (10): APP_ROLES, AppRole, normalizeRole(), ROLE_LABELS, sharesAdminAccess(), columns, makeUserCard(), roleOptions (+2 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.18
Nodes (5): DataCheckFlag, BelongsTo, DataChecks, QuarterHistory, InventoryBurnRateReportBuilder

### Community 108 - "EvacuationCenterController"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 127 - "cache.php"
Cohesion: 0.07
Nodes (16): JsonResponse, Request, StockController, Request, Response, StockController, IcsRequestLine, BelongsTo (+8 more)

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

### Community 236 - "RequestController.php"
Cohesion: 0.38
Nodes (4): Builder, JsonResponse, Request, RequestController

### Community 237 - "InertiaPageResponse"
Cohesion: 0.16
Nodes (6): IncidentController, Response, Response, SmtpSettingsController, InertiaPageResponse, Inertia

### Community 238 - "Index.tsx"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 239 - "DepotCapacityChecker"
Cohesion: 0.21
Nodes (5): Request, Response, ReplenishmentController, VedPolicy, ReplenishmentServiceClient

### Community 240 - "GdacsFeedClient"
Cohesion: 0.18
Nodes (7): evacuationCentersService, incidentsService, columns, fields, columns, reportFields, ReportsIndex()

### Community 244 - "DispatchTest"
Cohesion: 0.25
Nodes (7): 1. Automated (run before every evaluation session), 2. Offline resilience on devices (3 runs, then Safari separately), 3. Performance: run to dashboard (< 1 s on the office network), 4. User acceptance tasks, 5. Explanation task (target ≥ 80% correct), 6. ISO/IEC 25010:2023 survey, DisasterResource+ — Evaluation Protocol (Chapter III, Software Testing)

### Community 245 - "DirectionsController.php"
Cohesion: 0.60
Nodes (3): DirectionsController, JsonResponse, Request

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

### Community 249 - "PublicDashboardController.php"
Cohesion: 0.25
Nodes (4): DashboardController, Response, Response, PublicDashboardController

### Community 251 - "UsersController.php"
Cohesion: 0.50
Nodes (3): OptimizationController, RedirectResponse, RunOptimizationJob

### Community 254 - "ProviderQuotaExceededException.php"
Cohesion: 0.36
Nodes (5): JsonResponse, Request, RoutingSettingController, ProviderQuotaExceededException, RuntimeException

## Knowledge Gaps
- **359 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+354 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **72 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `EvacuationCenterController.php`, `Data Table Query Hooks`, `Auth Pages & Buttons`, `BaseController CRUD`, `Record Modal & Image Lightbox`, `PasswordResetMail.php`, `Inertia`, `AuthenticatedSessionController.php`, `PasswordResetController.php`, `RecordModal.tsx`, `ResourceInventoryController`, `InertiaPageResponse`, `DepotCapacityChecker`, `DirectionsController.php`, `PublicDashboardController.php`, `UsersController.php`, `PublicDashboardController.php`, `ProviderQuotaExceededException.php`, `cache.php`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `IncidentController`, `DisasterAlertEvent`, `DispatchController`, `EvacuationCenterController`, `RequestController.php`, `ResourceController.php`, `static`, `SmtpSetting`, `DepotController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `.makeRequestAndDepot`, `Filter/Pagination/Map Widgets`, `Dashboard & Allocation Models`, `Disaster Feed Settings & Tests`, `User Auth Model & Seeder`, `Service Providers`, `Record Modal & Image Lightbox`, `Dispatch`, `RecordModalField.tsx`, `SerializesModels`, `PasswordResetMail.php`, `DispatchController`, `api.php`, `Seeder`, `DisasterFeedIngestorTest`, `use-file-upload-field.tsx`, `api.php`, `ResourceTypeController.php`, `cache.php`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 58 inferred relationships involving `User` (e.g. with `.drivers()` and `.handle()`) actually correct?**
  _`User` has 58 INFERRED edges - model-reasoned connections that need verification._
- **Are the 28 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.index()`) actually correct?**
  _`Disaster` has 28 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _384 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.1214574898785425 - nodes in this community are weakly interconnected._