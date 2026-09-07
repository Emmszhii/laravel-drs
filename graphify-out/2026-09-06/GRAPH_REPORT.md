# Graph Report - laravel-drs  (2026-09-05)

## Corpus Check
- 380 files · ~107,738 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1576 nodes · 3098 edges · 242 communities (168 shown, 74 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 166 edges (avg confidence: 0.77)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5868008b`
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
- TableSpinner.jsx
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
- ActivityLogController.php
- watch-boneyard.mjs
- refresh-boneyard-session.mjs
- OptimizationController.php
- InertiaPageResponse
- build-yup-schema.ts
- EnsureUserIsActiveTest
- IncidentController.php
- RoutingSettingsController.php

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
- `DynamicFieldProps` --references--> `FieldDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/RecordModalField.tsx → backend/resources/js/Types/reusable/fields.ts
- `AsyncMultiSelectFieldProps` --references--> `FieldOption`  [EXTRACTED]
  backend/resources/js/Hooks/use-async-multi-select-field.tsx → backend/resources/js/Types/reusable/fields.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (242 total, 74 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.13
Nodes (10): LogPageVisit, Closure, Request, Response, AppLoggerObserver, Model, ActivityLogger, Model (+2 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.08
Nodes (35): ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner(), SystemToastProvider() (+27 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.13
Nodes (26): http, unwrap(), appendNestedObject(), appendNestedParam(), buildFormData(), buildQueryString(), hasBinaryFiles(), isFileFieldValue() (+18 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.12
Nodes (12): BelongsTo, Resource, HasMany, ResourceType, BelongsTo, HasMany, Zone, ResourceRequestFactory (+4 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.16
Nodes (11): Field(), DynamicFieldProps, AsyncSelectField(), valuesEqual(), formatDateShort(), ExistingFile, FieldOption, FieldType (+3 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.13
Nodes (17): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DefaultRecordCardProps, DEFAULT_TABS (+9 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.15
Nodes (11): Allocation, BelongsTo, OptimizationRun, BelongsTo, HasMany, BelongsTo, Report, GeofenceService (+3 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.26
Nodes (13): InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions(), buildArchiveUrl(), buildDeleteUrl() (+5 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.10
Nodes (13): DirectionsController, JsonResponse, Request, JsonResponse, Request, RoutingSettingController, RoutingSetting, DirectionsRouter (+5 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.12
Nodes (11): Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, INTERVAL_LABELS, Props, STATUS_COLORS (+3 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.09
Nodes (28): evacuationCentersService, incidentsService, resourceTypesService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), AdminLayout(), navItems (+20 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.13
Nodes (6): ExampleTest, IdempotentCreateTest, TestCase, ExampleTest, BaseTestCase, RefreshDatabase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.25
Nodes (16): buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue(), FormModal(), Values, FormModalBody(), Values (+8 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.07
Nodes (34): FilterBar(), Props, Modal(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER (+26 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (9): buildRecordMenu(), ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState, RenderModalProps (+1 more)

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.12
Nodes (5): Authenticatable, User, UserSeeder, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.14
Nodes (13): ConfirmInUseModal(), ConfirmInUseModalProps, FetchingSpinner(), DefaultRecordCard(), PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps (+5 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.24
Nodes (8): ConfirmArchiveAccountModal(), ConfirmArchiveAccountModalProps, ConfirmDeleteModal(), ConfirmDeleteModalProps, Modal(), ModalProps, UseModalBehaviorOptions, useScrollLock()

### Community 19 - "Database Factories"
Cohesion: 0.11
Nodes (9): DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, ReportFactory, ResourceTypeFactory, ZoneFactory (+1 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.18
Nodes (14): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, SettingsListHeader(), SettingsRow(), TextCell(), IsCurrentOrParentUrlFn (+6 more)

### Community 21 - "Service Providers"
Cohesion: 0.10
Nodes (10): Attempting, PreventInactiveLogin, ActivityLogServiceProvider, AppServiceProvider, EventServiceProvider, FortifyServiceProvider, InertiaServiceProvider, LoginResponse (+2 more)

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.09
Nodes (21): AdminUser, DashboardStats, Depot, DirectionsResult, DirectionsStep, Disaster, DisasterFeedSettingsData, Dispatch (+13 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.18
Nodes (3): DisasterFeedSetting, UsgsEarthquakeFeedClient, DisasterFeedSettingTest

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.17
Nodes (11): 0. Current state (what's real vs. skeleton vs. missing), Backend API, Database, Integration Plan: OSRM + OR-Tools/PuLP + Laravel + PostGIS, Matrix & Routing, Optimization Engine — the biggest gap, Phase 1 — Fix the capacity bug + wire OSRM's matrix service into the solver, Phase 2 — Real CVRPTW, two-stage with the existing PuLP LP (+3 more)

### Community 26 - "IncidentMap.tsx"
Cohesion: 0.10
Nodes (23): depotsService, DirectionsPanel(), FALLBACK_MESSAGES, formatDistance(), formatDuration(), Point, Props, Depot (+15 more)

### Community 28 - "ReportController.php"
Cohesion: 0.18
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, NdrrmcCsvExporter, StreamedResponse

### Community 29 - "use-module.ts"
Cohesion: 0.16
Nodes (18): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+10 more)

### Community 32 - "SerializesModels"
Cohesion: 0.37
Nodes (8): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldQueue

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.15
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

### Community 38 - "DispatchController.php"
Cohesion: 0.24
Nodes (10): ImageLightbox(), ImageLightboxProps, emptyFileFieldValue, fileNameFromUrl(), FileUploadField(), FileUploadFieldProps, formatBytes(), isImageName() (+2 more)

### Community 39 - "Users.tsx"
Cohesion: 0.18
Nodes (11): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, ToastContext, ToastContextValue, ToastItem (+3 more)

### Community 41 - "DisasterAlertEvent"
Cohesion: 0.19
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
Cohesion: 0.21
Nodes (7): DisasterFeedSettingsController, Response, Response, PublicDashboardController, Response, UsersController, Inertia

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
Cohesion: 0.21
Nodes (7): Controller, DepotController, Response, DispatchController, Response, Response, SmtpSettingsController

### Community 58 - "index.ts"
Cohesion: 0.27
Nodes (12): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+4 more)

### Community 60 - "client.ts"
Cohesion: 0.19
Nodes (12): ApiEnvelope, ApiError, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError(), columns (+4 more)

### Community 61 - "ResourceRequest"
Cohesion: 0.17
Nodes (5): BelongsTo, HasMany, ResourceRequest, AllocationFactory, AutoResolveIncidentsJobTest

### Community 62 - "Architecture"
Cohesion: 0.14
Nodes (12): Architecture, Automated disaster feed (backend/app/Services/DisasterFeed), Backend request layering, Commands, Directions / turn-by-turn navigation (backend/app/Services/Directions), Domain model (backend/app/Models, backend/database/migrations), Frontend (backend/resources/js), graphify (+4 more)

### Community 63 - "Seeder"
Cohesion: 0.38
Nodes (3): DisasterDataSeeder, RoleSeeder, Seeder

### Community 64 - "SkeletonLoader.tsx"
Cohesion: 0.23
Nodes (10): ModalCenter(), ModalCenterProps, ModalComponentProps, Size, WIDTHS, ModalSide(), ModalSideProps, Side (+2 more)

### Community 65 - "DisasterFeedIngestorTest"
Cohesion: 0.12
Nodes (7): Disaster, HasMany, DepotCapacityChecker, Collection, DisasterFeedIngestorTest, ReportDownloadTest, HasManyThrough

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.29
Nodes (3): DisasterFeedSettingController, JsonResponse, Request

### Community 68 - "ProfileController.php"
Cohesion: 0.60
Nodes (3): JsonResponse, Request, ProfileController

### Community 70 - "ResourceType"
Cohesion: 0.18
Nodes (6): DashboardController, Response, Depot, HasMany, ResourceFactory, DispatchTest

### Community 72 - "RoutingSettingController.php"
Cohesion: 0.24
Nodes (4): EvacuationCenter, BelongsTo, NdrrmcReportBuilder, NdrrmcReportBuilderTest

### Community 73 - "ScopesToAssignedBatches.php"
Cohesion: 0.70
Nodes (4): assertBatchAssigned(), assignedBatchIds(), Builder, scopeToAssignedBatches()

### Community 74 - "DisasterResource+"
Cohesion: 0.25
Nodes (7): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Notes, Quick start (Docker), Routing (OSRM)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.20
Nodes (5): Attribute, DatabaseSeeder, HasRoles, Notifiable, WithoutModelEvents

### Community 118 - "TableSpinner.jsx"
Cohesion: 0.43
Nodes (6): buildInitialValues(), collectErrors(), mapApiErrors(), RecordModal(), seedFieldValue(), isFieldDisabled()

### Community 233 - "ActivityLogController.php"
Cohesion: 0.43
Nodes (4): ActivityLogController, JsonResponse, Request, AppLogger

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 236 - "OptimizationController.php"
Cohesion: 0.50
Nodes (3): OptimizationController, RedirectResponse, RunOptimizationJob

### Community 238 - "build-yup-schema.ts"
Cohesion: 0.70
Nodes (4): baseFor(), buildYupSchema(), withCustom(), withRequired()

## Knowledge Gaps
- **220 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+215 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **74 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Auth Model & Seeder` to `DisasterFeedIngestorTest`, `Dashboard & Allocation Models`, `ResourceType`, `DispatchController`, `TableLoading.tsx`, `UserController`, `GlobalSearchController`, `SmtpSetting`, `static`, `Disaster Feed Settings & Tests`, `EnsureUserIsActiveTest`, `Database Factories`, `Service Providers`, `Dispatch`, `RoleGatingTest`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **Why does `Disaster` connect `DisasterFeedIngestorTest` to `SerializesModels`, `DisasterFeedIngestor`, `Depot & Resource Models`, `IncidentController`, `ResourceType`, `Dashboard & Allocation Models`, `RoutingSettingController.php`, `DisasterAlertEvent`, `OptimizationController.php`, `RoleGatingTest`, `Inertia`, `Database Factories`, `ReportController.php`, `ResourceRequest`, `Seeder`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Why does `Controller` connect `Controller` to `OsrmDirectionsProvider`, `Dashboard & Allocation Models`, `ResourceType`, `Directions & Routing Settings API`, `ActivityLogController.php`, `ResourceInventoryController`, `SmtpSetting`, `BaseController CRUD`, `GlobalSearchController`, `EvacuationCenterController.php`, `IncidentController.php`, `OptimizationController.php`, `ReportController.php`, `Inertia`, `RoutingSettingsController.php`, `AuthenticatedSessionController.php`, `PasswordResetController.php`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.downloadNdrrmcSitrep()`) actually correct?**
  _`Disaster` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _233 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.12807881773399016 - nodes in this community are weakly interconnected._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.0784313725490196 - nodes in this community are weakly interconnected._