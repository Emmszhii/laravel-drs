# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 428 files · ~149,488 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1989 nodes · 4080 edges · 256 communities (187 shown, 69 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 252 edges (avg confidence: 0.78)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c53dc9f9`
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
- HandlesFileUploads.php
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
- OptimizationController.php
- InertiaPageResponse
- Index.tsx
- EnsureUserIsActiveTest
- schemas.py
- HasFactory
- Docker Compose
- sample_payload
- DepotController.php
- Open a Claude-in-Chrome tab
- DirectionsController.php
- build-yup-schema.ts
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
8. `ResourceType` - 41 edges
9. `ResourceRequest` - 36 edges
10. `Zone` - 30 edges

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

## Communities (256 total, 69 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.05
Nodes (20): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+12 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.06
Nodes (51): ApiError, ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner() (+43 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.13
Nodes (29): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+21 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.06
Nodes (28): DisasterAlertEvent, DispatchPositionUpdated, DisasterFeedSettingController, JsonResponse, Request, AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob (+20 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.12
Nodes (9): Allocation, BelongsTo, BelongsTo, Resource, HasMany, ResourceType, ResourceLoanReservationTest, GreedyAllocationSolverTest (+1 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.13
Nodes (18): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DEFAULT_TABS, StatusFilter() (+10 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.14
Nodes (9): HasMany, EvacuationCenter, BelongsTo, QuarterBudget, BelongsTo, HasMany, StockCount, HasUuids (+1 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.21
Nodes (4): HistoryLine, BelongsTo, HistoryImporter, QuarterHistory

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.08
Nodes (35): evacuationCentersService, requestsService, resourcesService, resourceTypesService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), AdminLayout() (+27 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.10
Nodes (10): Report, DispatchDeliveryDeductionTest, ExampleTest, IdempotentCreateTest, ReportDownloadTest, SnapshotQuarterlyStockJobTest, TestCase, ExampleTest (+2 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.13
Nodes (18): ConfirmArchiveAccountModal(), ConfirmArchiveAccountModalProps, ConfirmDeleteModal(), ConfirmDeleteModalProps, Modal(), ModalCenter(), ModalCenterProps, ModalComponentProps (+10 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.09
Nodes (29): FilterBar(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER, Props, fetchJson() (+21 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.09
Nodes (8): Attribute, Authenticatable, User, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest, EnsureUserIsActiveTest, HasRoles, Notifiable

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.13
Nodes (15): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, Toolbar() (+7 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.06
Nodes (33): 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper], 3.1 Core entities [Paper, with fields from Table 3 and the text] (+25 more)

### Community 19 - "Database Factories"
Cohesion: 0.09
Nodes (11): DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, ReportFactory, ResourceRequestFactory, ResourceTypeFactory (+3 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.17
Nodes (15): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, buildRecordMenu(), SettingsListHeader(), SettingsRow(), TextCell() (+7 more)

### Community 21 - "Service Providers"
Cohesion: 0.14
Nodes (7): Attempting, PreventInactiveLogin, ActivityLogServiceProvider, AppServiceProvider, EventServiceProvider, InertiaServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.07
Nodes (28): AdminUser, BudgetCurvePoint, DashboardStats, DataCheckFlag, Depot, DirectionsResult, Disaster, DisasterFeedSettingsData (+20 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.18
Nodes (5): self, self, self, UserFactory, static

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.17
Nodes (11): 0. Current state (what's real vs. skeleton vs. missing), Backend API, Database, Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS, Matrix & Routing, Optimization Engine — the biggest gap, Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver, Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP (+3 more)

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.14
Nodes (7): DashboardController, Response, Depot, AllocationFactory, QuarterlyStockSnapshotFactory, ResourceFactory, DispatchTest

### Community 28 - "ReportController.php"
Cohesion: 0.20
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, InventoryBurnRateCsvExporter, StreamedResponse

### Community 29 - "use-module.ts"
Cohesion: 0.17
Nodes (17): ConfirmInUseModal(), ConfirmInUseModalProps, InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions() (+9 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.27
Nodes (11): BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props, ReplenishmentIndex() (+3 more)

### Community 32 - "SerializesModels"
Cohesion: 0.16
Nodes (5): BelongsTo, QuarterlyStockSnapshot, ReorderSuggestionCalculator, InventoryBurnRateReportBuilder, ReorderSuggestionCalculatorTest

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.11
Nodes (10): Disaster, HasMany, Zone, DisasterFeedIngestor, GeofenceService, Collection, GreedyAllocationSolver, OptimizationServiceClient (+2 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.19
Nodes (13): ABC, optimize_allocation(), Called by App\\Jobs\\RunOptimizationJob (Laravel) whenever an incident     is l, AllocationLine, AllocationRequest, AllocationResponse, BaseSolver, All solvers (allocation, routing) implement this interface so     `api/optimize (+5 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.36
Nodes (4): Request, Response, ReplenishmentController, VedPolicy

### Community 37 - "IncidentController"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 39 - "Users.tsx"
Cohesion: 0.08
Nodes (20): usersService, Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, ALGORITHM_LABELS, Props (+12 more)

### Community 40 - ".log"
Cohesion: 0.22
Nodes (9): Depot, DispatchMarker, Incident, IncidentMap(), PH_CENTER, Props, RouteGeometry, LatLng (+1 more)

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
Cohesion: 0.22
Nodes (7): Builder, JsonResponse, Model, Request, ResourceLoanController, BelongsTo, ResourceLoan

### Community 50 - "api.php"
Cohesion: 0.32
Nodes (4): Builder, JsonResponse, Request, RequestController

### Community 51 - "Inertia"
Cohesion: 0.20
Nodes (7): JsonResponse, Request, RoutingSettingController, Response, RoutingSettingsController, RoutingSetting, DirectionsRouter

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
Cohesion: 0.13
Nodes (11): Controller, DepotController, Response, DispatchController, Response, EvacuationCenterController, Response, IncidentController (+3 more)

### Community 58 - "data-table.ts"
Cohesion: 0.23
Nodes (13): parseApiError(), ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState, RenderModalProps (+5 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 61 - "RecordModal.tsx"
Cohesion: 0.16
Nodes (16): ImageLightbox(), ImageLightboxProps, buildInitialValues(), collectErrors(), mapApiErrors(), RecordModal(), seedFieldValue(), emptyFileFieldValue (+8 more)

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.11
Nodes (10): BelongsTo, HasMany, DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder (+2 more)

### Community 64 - "FormModal.tsx"
Cohesion: 0.25
Nodes (16): buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue(), FormModal(), Values, FormModalBody(), Values (+8 more)

### Community 65 - "DisasterFeedIngestorTest"
Cohesion: 0.23
Nodes (6): ReplenishmentRunUpdated, JsonResponse, Request, ReplenishmentController, ReplenishmentRun, ShouldBroadcastNow

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.20
Nodes (20): Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     run, solve(), OrderLine, BaseModel, ReplenishmentItem, ReplenishmentRequest, ReplenishmentResponse, by_id() (+12 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.30
Nodes (11): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+3 more)

### Community 70 - "UserController"
Cohesion: 0.19
Nodes (12): PuLP, budget_curve(), covering_level(), item_target(), _ms(), Budget-limited quarterly replenishment (Chapter III, Model Building).  Stage 1 —, Re-solves at 11 budget levels, 50%-150% of the entered budget in 10% steps     (, Use that would have covered k of the last m quarters: the (m-k+1)-th highest. (+4 more)

### Community 71 - "ResourceInventoryController"
Cohesion: 0.22
Nodes (5): OptimizationController, RedirectResponse, Response, ResourceInventoryController, RunOptimizationJob

### Community 72 - "SmtpSetting"
Cohesion: 0.32
Nodes (4): JsonResponse, Request, SmtpSettingController, SmtpSetting

### Community 73 - "Users.tsx"
Cohesion: 0.18
Nodes (11): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, ToastContext, ToastContextValue, ToastItem (+3 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 77 - "api.php"
Cohesion: 0.60
Nodes (3): JsonResponse, Request, ProfileController

### Community 78 - "Index.tsx"
Cohesion: 0.19
Nodes (11): depotsService, dispatchesService, Modal(), Props, PositionUpdatePayload, useDispatchTracking(), useLiveGeolocation(), COORDINATOR_TIER_ROLES (+3 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.30
Nodes (3): DataCheckFlag, BelongsTo, DataChecks

### Community 108 - "EvacuationCenterController"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 118 - "HandlesFileUploads.php"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 127 - "cache.php"
Cohesion: 0.09
Nodes (13): JsonResponse, Request, StockController, Request, Response, StockController, BelongsTo, StockConflict (+5 more)

### Community 140 - "ResourceController.php"
Cohesion: 0.31
Nodes (4): Builder, JsonResponse, Model, ResourceController

### Community 155 - "RecordModalField.tsx"
Cohesion: 0.24
Nodes (4): Field(), DynamicField(), formatDateShort(), FileFieldValue

### Community 233 - "Dashboard.tsx"
Cohesion: 0.24
Nodes (7): DashboardLayout(), Props, Dashboard(), Props, requestColumns, statusColor, utilizationBarColor()

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 236 - "OptimizationController.php"
Cohesion: 0.20
Nodes (7): FastAPI, optimize_routing(), Depot-to-zone routing suggestions (straight-line v1; OSRM later)., budget_curve(), Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, health(), Health check consumed by Docker/Compose and the backend's depends_on condition.

### Community 237 - "InertiaPageResponse"
Cohesion: 0.16
Nodes (6): DisasterFeedSettingsController, Response, Response, UsersController, InertiaPageResponse, Inertia

### Community 238 - "Index.tsx"
Cohesion: 0.25
Nodes (6): incidentsService, crud, reportsService, columns, reportFields, ReportsIndex()

### Community 239 - "EnsureUserIsActiveTest"
Cohesion: 0.16
Nodes (4): BelongsTo, ResourceRequest, AutoResolveIncidentsJobTest, RequestApprovalReservationTest

### Community 240 - "schemas.py"
Cohesion: 0.29
Nodes (7): Pydantic, python-json-logger, Solver Service (FastAPI), DepotCapacity, BaseModel, ZoneDemand, Uvicorn

### Community 244 - "HasFactory"
Cohesion: 0.43
Nodes (4): OptimizationRun, BelongsTo, HasMany, HasFactory

### Community 245 - "Docker Compose"
Cohesion: 0.38
Nodes (7): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, Laravel Reverb, Traefik

### Community 246 - "sample_payload"
Cohesion: 0.48
Nodes (6): Performance and correctness bench tests for the allocation/routing solvers.  R, sample_payload(), test_ortools_routing_solver_returns_a_response(), test_pulp_solver_fully_satisfies_demand_within_capacity(), test_pulp_solver_reports_unmet_demand_when_capacity_insufficient(), test_pulp_solver_runs_within_reasonable_time()

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

### Community 249 - "DirectionsController.php"
Cohesion: 0.60
Nodes (3): DirectionsController, JsonResponse, Request

### Community 251 - "build-yup-schema.ts"
Cohesion: 0.70
Nodes (4): baseFor(), buildYupSchema(), withCustom(), withRequired()

## Knowledge Gaps
- **339 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+334 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **69 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `Admin API Controllers`, `DisasterFeedIngestorTest`, `Depot & Resource Models`, `PasswordResetMail.php`, `ResourceInventoryController`, `SmtpSetting`, `BaseController CRUD`, `InertiaPageResponse`, `Inertia`, `AuthenticatedSessionController.php`, `PasswordResetController.php`, `DirectionsController.php`, `IncidentMap.tsx`, `PublicDashboardController.php`, `SmtpSettingsController.php`, `cache.php`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `IncidentController`, `DisasterAlertEvent`, `DispatchController`, `EvacuationCenterController`, `api.php`, `ResourceController.php`, `static`, `SmtpSetting`, `api.php`, `DepotController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `User` connect `User Auth Model & Seeder` to `OsrmDirectionsProvider`, `Filter/Pagination/Map Widgets`, `Dashboard & Allocation Models`, `Data Table Query Hooks`, `SmtpSetting`, `Auth Pages & Buttons`, `DispatchController`, `Disaster Feed Settings & Tests`, `EnsureUserIsActiveTest`, `Database Factories`, `HasFactory`, `Service Providers`, `Record Modal & Image Lightbox`, `IncidentMap.tsx`, `Dispatch`, `cache.php`, `Seeder`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 54 inferred relationships involving `User` (e.g. with `.drivers()` and `.handle()`) actually correct?**
  _`User` has 54 INFERRED edges - model-reasoned connections that need verification._
- **Are the 28 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.index()`) actually correct?**
  _`Disaster` has 28 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _359 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.05134575569358178 - nodes in this community are weakly interconnected._