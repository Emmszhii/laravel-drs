# Graph Report - laravel-drs  (2026-08-19)

## Corpus Check
- 379 files · ~104,490 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1562 nodes · 3080 edges · 233 communities (159 shown, 74 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 166 edges (avg confidence: 0.77)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `775fb7e9`
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
- DispatchController.php
- Users.tsx
- .log
- DisasterAlertEvent
- DispatchController
- UserController
- SmtpSetting
- GlobalSearchController
- index.ts
- static
- Dashboard.tsx
- EvacuationCenterController
- api.php
- Inertia
- HandlesFileUploads.php
- Directions and Turn-by-Turn Navigation
- ResourceController.php
- AuthenticatedSessionController.php
- PasswordResetController.php
- Controller
- index.ts
- Allocation (Domain Model)
- client.ts
- ResourceRequest
- Architecture
- Seeder
- SkeletonLoader.tsx
- DisasterFeedIngestorTest
- OsrmDirectionsProvider
- DepotController.php
- ProfileController.php
- ResourceTypeController.php
- ResourceType
- ResourceInventoryController
- RoutingSettingController.php
- ScopesToAssignedBatches.php
- DisasterResource+
- Automated Disaster Feed Polling
- AppLayout
- ReportController.php
- EvacuationCenterController.php
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
- About.tsx
- DirectionsPanel (React Component)
- configure_logging
- AutoResolveIncidentsJob
- vite-env.d.ts
- entrypoint.sh
- init-postgis.sh
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

## God Nodes (most connected - your core abstractions)
1. `User` - 73 edges
2. `Disaster` - 70 edges
3. `Controller` - 43 edges
4. `BaseController` - 42 edges
5. `TestCase` - 34 edges
6. `Zone` - 31 edges
7. `Dispatch` - 25 edges
8. `DisasterFeedSetting` - 22 edges
9. `Depot` - 21 edges
10. `usePermission()` - 21 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `ColumnFilterControlProps` --references--> `ColumnDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/ColumnFilterControl.tsx → backend/resources/js/Types/reusable/data-table.ts
- `Backend (Laravel)` --uses--> `PostgreSQL with PostGIS`  [EXTRACTED]
  README.md → CLAUDE.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (233 total, 74 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.10
Nodes (14): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+6 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.06
Nodes (44): ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner(), SettingsTabs() (+36 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.15
Nodes (23): http, unwrap(), appendNestedObject(), appendNestedParam(), buildFormData(), buildQueryString(), hasBinaryFiles(), isFileFieldValue() (+15 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.09
Nodes (14): Depot, HasMany, Disaster, HasMany, BelongsTo, Resource, BelongsTo, HasMany (+6 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.16
Nodes (15): fetchJson(), PsgcOption, sortByName(), usePsgc(), columns, DepotsIndex(), emptyForm, NO_INCIDENTS (+7 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.13
Nodes (18): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DEFAULT_TABS, StatusFilter() (+10 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.14
Nodes (11): Allocation, BelongsTo, EvacuationCenter, BelongsTo, OptimizationRun, BelongsTo, HasMany, GeofenceService (+3 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.26
Nodes (13): InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions(), buildArchiveUrl(), buildDeleteUrl() (+5 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.20
Nodes (7): DirectionsController, JsonResponse, Request, Response, RoutingSettingsController, RoutingSetting, DirectionsRouter

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.14
Nodes (8): incidentsService, crud, reportsService, Button(), Props, ResetPasswordProps, columns, reportFields

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.12
Nodes (22): evacuationCentersService, resourceTypesService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), AdminLayout(), columns, EvacuationIndex() (+14 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.10
Nodes (10): BelongsTo, Report, ExampleTest, IdempotentCreateTest, ReportDownloadTest, TestCase, ExampleTest, NdrrmcReportBuilderTest (+2 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.05
Nodes (61): Field(), baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik() (+53 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.12
Nodes (17): FilterBar(), Props, Modal(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER (+9 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.21
Nodes (12): ViewToggle(), ViewToggleProps, ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState (+4 more)

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.09
Nodes (8): Attribute, Authenticatable, User, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest, EnsureUserIsActiveTest, HasRoles, Notifiable

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.16
Nodes (12): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, Toolbar() (+4 more)

### Community 19 - "Database Factories"
Cohesion: 0.11
Nodes (9): DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, ReportFactory, ResourceTypeFactory, ZoneFactory (+1 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.17
Nodes (15): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, buildRecordMenu(), SettingsListHeader(), SettingsRow(), TextCell() (+7 more)

### Community 21 - "Service Providers"
Cohesion: 0.10
Nodes (10): Attempting, PreventInactiveLogin, ActivityLogServiceProvider, AppServiceProvider, EventServiceProvider, FortifyServiceProvider, InertiaServiceProvider, LoginResponse (+2 more)

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.09
Nodes (21): AdminUser, DashboardStats, Depot, DirectionsResult, DirectionsStep, Disaster, DisasterFeedSettingsData, Dispatch (+13 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.20
Nodes (5): DisasterFeedSettingController, JsonResponse, Request, DisasterFeedSetting, DisasterFeedSettingTest

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.26
Nodes (8): depotsService, dispatchesService, PositionUpdatePayload, useDispatchTracking(), useLiveGeolocation(), DispatchIndex(), emptyForm, STATUS_LABELS

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.14
Nodes (16): DirectionsPanel(), FALLBACK_MESSAGES, formatDistance(), formatDuration(), Point, Props, Depot, DispatchMarker (+8 more)

### Community 27 - "Dispatch"
Cohesion: 0.21
Nodes (3): Dispatch, BelongsTo, DispatchTest

### Community 28 - "ReportController.php"
Cohesion: 0.18
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, NdrrmcCsvExporter, StreamedResponse

### Community 29 - "use-module.ts"
Cohesion: 0.16
Nodes (18): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+10 more)

### Community 32 - "SerializesModels"
Cohesion: 0.32
Nodes (8): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldQueue

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.16
Nodes (5): DisasterFeedIngestor, GeofenceService, Collection, GreedyAllocationSolver, OptimizationServiceClient

### Community 34 - "AllocationRequest"
Cohesion: 0.07
Nodes (39): ABC, Backend (Laravel), BaseModel, Coolify, Docker Compose, FastAPI, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS (+31 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 37 - "IncidentController"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 39 - "Users.tsx"
Cohesion: 0.13
Nodes (16): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, ToastContext, ToastContextValue, ToastItem (+8 more)

### Community 41 - "DisasterAlertEvent"
Cohesion: 0.21
Nodes (4): DisasterAlertEvent, DispatchPositionUpdated, InteractsWithSockets, ShouldBroadcast

### Community 42 - "DispatchController"
Cohesion: 0.30
Nodes (4): DispatchController, JsonResponse, Model, Request

### Community 43 - "UserController"
Cohesion: 0.27
Nodes (3): JsonResponse, Model, UserController

### Community 44 - "SmtpSetting"
Cohesion: 0.32
Nodes (4): JsonResponse, Request, SmtpSettingController, SmtpSetting

### Community 45 - "GlobalSearchController"
Cohesion: 0.35
Nodes (5): GlobalSearchController, Builder, JsonResponse, Request, ScopesToAssignedBatches

### Community 46 - "index.ts"
Cohesion: 0.36
Nodes (8): GlobalModalContext, GlobalModalProvider(), createGlobalModalStore(), GlobalModalStore, Listener, ModalEntry, GlobalModalHandle, useGlobalModal()

### Community 47 - "static"
Cohesion: 0.18
Nodes (5): self, self, self, UserFactory, static

### Community 48 - "Dashboard.tsx"
Cohesion: 0.24
Nodes (7): DashboardLayout(), Props, Dashboard(), Props, requestColumns, statusColor, utilizationBarColor()

### Community 49 - "EvacuationCenterController"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 50 - "api.php"
Cohesion: 0.33
Nodes (4): Builder, JsonResponse, Request, RequestController

### Community 51 - "Inertia"
Cohesion: 0.14
Nodes (8): DisasterFeedSettingsController, Response, Response, PublicDashboardController, Response, UsersController, InertiaPageResponse, Inertia

### Community 52 - "HandlesFileUploads.php"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 54 - "ResourceController.php"
Cohesion: 0.31
Nodes (4): Builder, JsonResponse, Model, ResourceController

### Community 55 - "AuthenticatedSessionController.php"
Cohesion: 0.43
Nodes (4): AuthenticatedSessionController, RedirectResponse, Request, Response

### Community 56 - "PasswordResetController.php"
Cohesion: 0.36
Nodes (4): PasswordResetController, RedirectResponse, Request, Response

### Community 57 - "Controller"
Cohesion: 0.15
Nodes (10): Controller, DepotController, Response, IncidentController, Response, OptimizationController, RedirectResponse, Response (+2 more)

### Community 58 - "index.ts"
Cohesion: 0.27
Nodes (12): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+4 more)

### Community 60 - "client.ts"
Cohesion: 0.21
Nodes (11): ApiEnvelope, ApiError, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError(), enqueue() (+3 more)

### Community 61 - "ResourceRequest"
Cohesion: 0.16
Nodes (5): BelongsTo, HasMany, ResourceRequest, AllocationFactory, AutoResolveIncidentsJobTest

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.20
Nodes (7): DatabaseSeeder, DisasterDataSeeder, FakerDataSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.20
Nodes (3): OsrmDirectionsProvider, PremiumDirectionsProvider, DirectionsProvider

### Community 68 - "ProfileController.php"
Cohesion: 0.60
Nodes (3): JsonResponse, Request, ProfileController

### Community 70 - "ResourceType"
Cohesion: 0.18
Nodes (6): DashboardController, Response, HasMany, ResourceType, ResourceFactory, ResourceRequestFactory

### Community 72 - "RoutingSettingController.php"
Cohesion: 0.36
Nodes (5): JsonResponse, Request, RoutingSettingController, ProviderQuotaExceededException, RuntimeException

### Community 73 - "ScopesToAssignedBatches.php"
Cohesion: 0.70
Nodes (4): assertBatchAssigned(), assignedBatchIds(), Builder, scopeToAssignedBatches()

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

## Knowledge Gaps
- **208 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+203 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **74 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Auth Model & Seeder` to `Depot & Resource Models`, `Dashboard & Allocation Models`, `ResourceType`, `DispatchController`, `UserController`, `SmtpSetting`, `GlobalSearchController`, `Disaster Feed Settings & Tests`, `static`, `Database Factories`, `Service Providers`, `Dispatch`, `RoleGatingTest`, `Seeder`?**
  _High betweenness centrality (0.056) - this node is a cross-community bridge._
- **Why does `Disaster` connect `Depot & Resource Models` to `SerializesModels`, `DisasterFeedIngestor`, `DisasterFeedIngestorTest`, `IncidentController`, `ResourceType`, `Dashboard & Allocation Models`, `.log`, `DisasterAlertEvent`, `Disaster Feed Settings & Tests`, `Disaster Model & Auto-Resolve Job`, `Inertia`, `Database Factories`, `Controller`, `ReportController.php`, `ResourceRequest`, `RoleGatingTest`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `DepotController.php`, `ProfileController.php`, `IncidentController`, `ResourceTypeController.php`, `DispatchController`, `UserController`, `EvacuationCenterController`, `api.php`, `ResourceController.php`, `Controller`, `ReportController.php`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.downloadNdrrmcSitrep()`) actually correct?**
  _`Disaster` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _221 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.09841269841269841 - nodes in this community are weakly interconnected._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.05794556628621598 - nodes in this community are weakly interconnected._