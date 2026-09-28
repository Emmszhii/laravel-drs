# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 443 files · ~157,466 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2128 nodes · 4475 edges · 256 communities (183 shown, 73 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 308 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `6e67cd1b`
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

## God Nodes (most connected - your core abstractions)
1. `User` - 81 edges
2. `Disaster` - 63 edges
3. `Controller` - 54 edges
4. `Resource` - 52 edges
5. `ResourceType` - 48 edges
6. `TestCase` - 48 edges
7. `BaseController` - 44 edges
8. `Depot` - 37 edges
9. `ResourceRequest` - 36 edges
10. `StockLedger` - 31 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `DynamicFieldProps` --references--> `FieldDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/RecordModalField.tsx → backend/resources/js/Types/reusable/fields.ts
- `UseModuleOptions` --references--> `CrudQueryParams`  [EXTRACTED]
  backend/resources/js/Hooks/use-module.ts → backend/resources/js/Types/reusable/pagination.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (256 total, 73 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.15
Nodes (5): DisasterFeedIngestor, GeofenceService, Collection, GreedyAllocationSolver, OptimizationServiceClient

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.12
Nodes (32): OfflineBanner(), useOfflineSync(), useOnlineStatus(), DrsOfflineDB, getDb(), OutboxItem, OutboxItemKind, OutboxItemStatus (+24 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.12
Nodes (31): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+23 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.30
Nodes (10): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, RunReplenishmentJob, SnapshotQuarterlyStockJob, Dispatchable, InteractsWithQueue, Queueable (+2 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.08
Nodes (14): Depot, HasMany, Disaster, HasMany, HasMany, ResourceType, BelongsTo, HasMany (+6 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.11
Nodes (22): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DefaultRecordCardProps, DEFAULT_TABS (+14 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.09
Nodes (15): Allocation, BelongsTo, EvacuationCenter, BelongsTo, OptimizationRun, BelongsTo, HasMany, BelongsTo (+7 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.11
Nodes (20): ConfirmArchiveAccountModal(), ConfirmArchiveAccountModalProps, ConfirmDeleteModal(), ConfirmDeleteModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, Modal(), ModalCenter() (+12 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.10
Nodes (14): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+6 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.09
Nodes (29): evacuationCentersService, resourceTypesService, DataTableCardField(), normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), columns, EvacuationIndex() (+21 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.12
Nodes (8): ExampleTest, IdempotentCreateTest, TestCase, ExampleTest, GreedyAllocationSolverTest, BaseTestCase, GeofenceService, RefreshDatabase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.15
Nodes (27): baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue() (+19 more)

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
Cohesion: 0.13
Nodes (6): Authenticatable, User, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest, HasRoles, Notifiable

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.12
Nodes (22): FetchingSpinner(), DefaultRecordCard(), PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, ViewToggle(), ViewToggleProps (+14 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 19 - "Database Factories"
Cohesion: 0.08
Nodes (13): AllocationFactory, DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, QuarterlyStockSnapshotFactory, ReportFactory (+5 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.17
Nodes (15): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, buildRecordMenu(), SettingsListHeader(), SettingsRow(), TextCell() (+7 more)

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
Cohesion: 0.18
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, InventoryBurnRateCsvExporter, StreamedResponse

### Community 29 - "use-module.ts"
Cohesion: 0.15
Nodes (25): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+17 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.23
Nodes (13): BacktestPanel(), BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props (+5 more)

### Community 32 - "SerializesModels"
Cohesion: 0.13
Nodes (8): BelongsTo, QuarterlyStockSnapshot, BelongsTo, Resource, ReorderSuggestionCalculator, ResourceLoanReservationTest, SnapshotQuarterlyStockJobTest, ReorderSuggestionCalculatorTest

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.12
Nodes (15): Field(), ImageLightbox(), ImageLightboxProps, DynamicField(), DynamicFieldProps, emptyFileFieldValue, FileUploadField(), FileUploadFieldProps (+7 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.07
Nodes (39): ABC, Backend (Laravel), Coolify, Docker Compose, FastAPI, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, PuLP (+31 more)

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
Cohesion: 0.13
Nodes (17): dispatchesService, Depot, DispatchMarker, Incident, IncidentMap(), PH_CENTER, Props, RouteGeometry (+9 more)

### Community 41 - "DisasterAlertEvent"
Cohesion: 0.60
Nodes (3): JsonResponse, Request, ProfileController

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
Cohesion: 0.28
Nodes (5): Builder, JsonResponse, Model, Request, ResourceLoanController

### Community 50 - "api.php"
Cohesion: 0.14
Nodes (6): DashboardController, Response, BelongsTo, HasMany, ResourceRequest, AutoResolveIncidentsJobTest

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
Cohesion: 0.15
Nodes (10): Controller, DepotController, Response, EvacuationCenterController, Response, OptimizationController, RedirectResponse, Response (+2 more)

### Community 58 - "data-table.ts"
Cohesion: 0.15
Nodes (18): Borrow, Borrowed(), LABEL, NEXT, DIVISIONS, FileForm(), IcsRequest, Item (+10 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 61 - "RecordModal.tsx"
Cohesion: 0.20
Nodes (5): DisasterFeedSettingController, JsonResponse, Request, DisasterFeedSetting, DisasterFeedSettingTest

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.17
Nodes (8): DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 64 - "FormModal.tsx"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 65 - "DisasterFeedIngestorTest"
Cohesion: 0.07
Nodes (19): JsonResponse, Request, ReplenishmentController, Request, Response, ReplenishmentController, Backtest, DataCheckFlag (+11 more)

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.08
Nodes (51): backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     run, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, solve(), BacktestRequest, OrderLine (+43 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.21
Nodes (3): UsgsEarthquakeFeedClient, DisasterFeedIngestorTest, Carbon

### Community 70 - "UserController"
Cohesion: 0.26
Nodes (9): ActivityLogEntry, activityLogService, DispatchTicker(), getLastSeenId(), NotificationBell(), AdminLayout(), navItems, STAFF_ROLES (+1 more)

### Community 71 - "ResourceInventoryController"
Cohesion: 0.24
Nodes (4): Response, ResourceInventoryController, Response, SmtpSettingsController

### Community 72 - "SmtpSetting"
Cohesion: 0.26
Nodes (10): UserProfileSection(), UserProfileSectionProps, AuthUser, initialsFor(), useAuth(), detectSystemTheme(), getStoredTheme(), setStoredTheme() (+2 more)

### Community 73 - "Users.tsx"
Cohesion: 0.10
Nodes (19): queryClient, ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, SystemToastProvider(), ToastContext (+11 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 78 - "Index.tsx"
Cohesion: 0.24
Nodes (9): APP_ROLES, AppRole, normalizeRole(), ROLE_LABELS, columns, makeUserCard(), roleOptions, SettingsUsersIndex() (+1 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.17
Nodes (5): HistoryLine, BelongsTo, IcsRequestLine, BelongsTo, HasOne

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
Cohesion: 0.22
Nodes (8): sharesAdminAccess(), DashboardLayout(), Props, Dashboard(), Props, requestColumns, statusColor, utilizationBarColor()

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
Nodes (6): DispatchController, Response, IncidentController, Response, InertiaPageResponse, Inertia

### Community 238 - "Index.tsx"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 245 - "DirectionsController.php"
Cohesion: 0.60
Nodes (3): DirectionsController, JsonResponse, Request

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

### Community 254 - "ProviderQuotaExceededException.php"
Cohesion: 0.36
Nodes (5): JsonResponse, Request, RoutingSettingController, ProviderQuotaExceededException, RuntimeException

## Knowledge Gaps
- **352 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+347 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **73 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `DisasterFeedIngestorTest`, `PasswordResetMail.php`, `ResourceInventoryController`, `Auth Pages & Buttons`, `BaseController CRUD`, `InertiaPageResponse`, `api.php`, `Inertia`, `DirectionsController.php`, `AuthenticatedSessionController.php`, `Record Modal & Image Lightbox`, `PasswordResetController.php`, `PublicDashboardController.php`, `UsersController.php`, `PublicDashboardController.php`, `RecordModal.tsx`, `ProviderQuotaExceededException.php`, `cache.php`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `IncidentController`, `DisasterAlertEvent`, `DispatchController`, `EvacuationCenterController`, `RequestController.php`, `ResourceController.php`, `static`, `SmtpSetting`, `ResourceTypeController.php`, `DepotController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `User` connect `User Auth Model & Seeder` to `SerializesModels`, `DisasterFeedIngestorTest`, `OsrmDirectionsProvider`, `Filter/Pagination/Map Widgets`, `PasswordResetMail.php`, `Dashboard & Allocation Models`, `Dispatch`, `DispatchController`, `Disaster Feed Settings & Tests`, `TableLoading.tsx`, `api.php`, `DispatchTest`, `Service Providers`, `Record Modal & Image Lightbox`, `RecordModalField.tsx`, `cache.php`, `Seeder`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 54 inferred relationships involving `User` (e.g. with `.drivers()` and `.handle()`) actually correct?**
  _`User` has 54 INFERRED edges - model-reasoned connections that need verification._
- **Are the 28 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.index()`) actually correct?**
  _`Disaster` has 28 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _376 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.12280701754385964 - nodes in this community are weakly interconnected._