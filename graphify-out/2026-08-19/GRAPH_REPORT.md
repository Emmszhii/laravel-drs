# Graph Report - .  (2026-08-19)

## Corpus Check
- 386 files · ~104,384 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1540 nodes · 3120 edges · 195 communities (164 shown, 31 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 177 edges (avg confidence: 0.78)
- Token cost: 60,000 input · 11,812 output

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
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 70
- Community 71
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 120
- Community 121
- Community 122
- Community 136
- Community 193
- Community 194

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
- `TriggerSolver (React Component)` --calls--> `Solver Service (FastAPI)`  [INFERRED]
  CLAUDE.md → README.md
- `Backend (Laravel)` --uses--> `Inertia`  [EXTRACTED]
  README.md → CLAUDE.md
- `Backend (Laravel)` --uses--> `Leaflet.js`  [EXTRACTED]
  README.md → CLAUDE.md
- `Backend (Laravel)` --uses--> `PostgreSQL with PostGIS`  [EXTRACTED]
  README.md → CLAUDE.md
- `Backend (Laravel)` --uses--> `React`  [EXTRACTED]
  README.md → CLAUDE.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (195 total, 31 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.05
Nodes (21): ActivityLogController, JsonResponse, Request, DisasterFeedSettingController, JsonResponse, Request, LogPageVisit, Closure (+13 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.08
Nodes (35): ActivityLogEntry, activityLogService, queryClient, DispatchTicker(), getLastSeenId(), NotificationBell(), OfflineBanner(), SystemToastProvider() (+27 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.11
Nodes (33): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), http, readCookie(), toApiError(), unwrap() (+25 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.09
Nodes (15): Depot, HasMany, BelongsTo, Resource, BelongsTo, HasMany, ResourceRequest, HasMany (+7 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.08
Nodes (29): FilterBar(), Props, Pagination(), Props, LocationPickMap(), PH_CENTER, Props, ConfirmDeleteModal() (+21 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.09
Nodes (23): Dropdown(), DropdownOption, DropdownProps, Field(), CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DynamicField() (+15 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.16
Nodes (8): Allocation, BelongsTo, OptimizationRun, BelongsTo, HasMany, HasFactory, HasUuids, Model

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.13
Nodes (28): InUseEntry, TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive() (+20 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.10
Nodes (13): DirectionsController, JsonResponse, Request, JsonResponse, Request, RoutingSettingController, RoutingSetting, DirectionsRouter (+5 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.10
Nodes (16): Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, INTERVAL_LABELS, Props, STATUS_COLORS (+8 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.14
Nodes (22): resourceTypesService, tableListInvalidateKeys(), usePermission(), AdminLayout(), navItems, enqueue(), EvacuationIndex(), ALGORITHM_LABELS (+14 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.11
Nodes (8): ExampleTest, IdempotentCreateTest, OptimizationFallbackTest, TestCase, ExampleTest, NdrrmcReportBuilderTest, BaseTestCase, RefreshDatabase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.20
Nodes (21): baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue() (+13 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.22
Nodes (5): BaseController, Builder, JsonResponse, Model, Request

### Community 14 - "Confirmation Modals"
Cohesion: 0.13
Nodes (18): ConfirmArchiveAccountModal(), ConfirmArchiveAccountModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, Modal(), ModalCenter(), ModalCenterProps, ModalComponentProps (+10 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.14
Nodes (16): PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, ViewToggle(), ViewToggleProps, ColumnFilterType, DataTableProps (+8 more)

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.12
Nodes (5): Authenticatable, User, UserSeeder, DisasterFeedSettingsControllerTest, UserAccountLifecycleTest

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.14
Nodes (15): evacuationCentersService, FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, Toolbar(), ToolbarProps, DataTableCardField(), formatCell() (+7 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.13
Nodes (6): Disaster, HasMany, DepotCapacityChecker, Collection, NdrrmcReportBuilder, HasManyThrough

### Community 19 - "Database Factories"
Cohesion: 0.11
Nodes (12): AllocationFactory, DepotFactory, DisasterFactory, DispatchFactory, EvacuationCenterFactory, OptimizationRunFactory, ReportFactory, ResourceFactory (+4 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.17
Nodes (15): RowMenu(), RowMenuAction, AddRecordButton(), AddRecordButtonProps, buildRecordMenu(), SettingsListHeader(), SettingsRow(), TextCell() (+7 more)

### Community 21 - "Service Providers"
Cohesion: 0.12
Nodes (7): ActivityLogServiceProvider, AppServiceProvider, FortifyServiceProvider, InertiaServiceProvider, LoginResponse, LoginResponseContract, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.09
Nodes (21): AdminUser, DashboardStats, Depot, DirectionsResult, DirectionsStep, Disaster, DisasterFeedSettingsData, Dispatch (+13 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.16
Nodes (16): ImageLightbox(), ImageLightboxProps, buildInitialValues(), collectErrors(), mapApiErrors(), RecordModal(), seedFieldValue(), emptyFileFieldValue (+8 more)

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Community 25"
Cohesion: 0.15
Nodes (13): ApiError, dispatchesService, Modal(), Props, PositionUpdatePayload, useDispatchTracking(), useLiveGeolocation(), DispatchIndex() (+5 more)

### Community 26 - "Community 26"
Cohesion: 0.15
Nodes (15): DirectionsPanel(), FALLBACK_MESSAGES, formatDistance(), formatDuration(), Point, Props, Depot, DispatchMarker (+7 more)

### Community 27 - "Community 27"
Cohesion: 0.18
Nodes (3): Dispatch, BelongsTo, DispatchTest

### Community 28 - "Community 28"
Cohesion: 0.18
Nodes (8): Builder, JsonResponse, Model, Request, Response, ReportController, NdrrmcCsvExporter, StreamedResponse

### Community 29 - "Community 29"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 30 - "Community 30"
Cohesion: 0.13
Nodes (17): Backend (Laravel), Composer, DisasterAlertEvent, IncidentMap (React Component), Laravel Framework, Laravel Echo, Leaflet.js, npm (+9 more)

### Community 32 - "Community 32"
Cohesion: 0.35
Nodes (8): AutoResolveIncidentsJob, PollDisasterFeedsJob, RunOptimizationJob, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldQueue

### Community 33 - "Community 33"
Cohesion: 0.19
Nodes (4): DisasterFeedIngestor, GeofenceService, GreedyAllocationSolver, OptimizationServiceClient

### Community 34 - "Community 34"
Cohesion: 0.25
Nodes (13): BaseModel, AllocationLine, AllocationRequest, DepotCapacity, ZoneDemand, PulpAllocationSolver, Minimizes unmet demand across zones, subject to depot capacity.     This is the, Performance and correctness bench tests for the allocation/routing solvers.  Run (+5 more)

### Community 35 - "Community 35"
Cohesion: 0.14
Nodes (15): BaseSolver, DisasterResource+, Docker Infrastructure, Resource Optimization Flow, OptimizationServiceClient, OR-Tools, ORToolsRoutingSolver, PuLP (+7 more)

### Community 36 - "Community 36"
Cohesion: 0.19
Nodes (7): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable

### Community 37 - "Community 37"
Cohesion: 0.29
Nodes (4): IncidentController, JsonResponse, Model, Request

### Community 38 - "Community 38"
Cohesion: 0.21
Nodes (7): Controller, DispatchController, Response, Response, ReportController, Response, RoutingSettingsController

### Community 39 - "Community 39"
Cohesion: 0.18
Nodes (11): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, ToastContext, ToastContextValue, ToastItem (+3 more)

### Community 40 - "Community 40"
Cohesion: 0.24
Nodes (7): ABC, AllocationResponse, BaseSolver, All solvers (allocation, routing) implement this interface so     `api/optimize., ORToolsRoutingSolver, # NOTE: translating the raw OR-Tools route back into, Depot -> zone routing using OR-Tools' routing library.     v1 uses haversine str

### Community 41 - "Community 41"
Cohesion: 0.21
Nodes (4): DisasterAlertEvent, DispatchPositionUpdated, InteractsWithSockets, ShouldBroadcast

### Community 42 - "Community 42"
Cohesion: 0.30
Nodes (4): DispatchController, JsonResponse, Model, Request

### Community 43 - "Community 43"
Cohesion: 0.27
Nodes (3): JsonResponse, Model, UserController

### Community 44 - "Community 44"
Cohesion: 0.32
Nodes (4): JsonResponse, Request, SmtpSettingController, SmtpSetting

### Community 45 - "Community 45"
Cohesion: 0.35
Nodes (5): GlobalSearchController, Builder, JsonResponse, Request, ScopesToAssignedBatches

### Community 46 - "Community 46"
Cohesion: 0.36
Nodes (8): GlobalModalContext, GlobalModalProvider(), createGlobalModalStore(), GlobalModalStore, Listener, ModalEntry, GlobalModalHandle, useGlobalModal()

### Community 47 - "Community 47"
Cohesion: 0.20
Nodes (5): self, self, self, UserFactory, static

### Community 48 - "Community 48"
Cohesion: 0.22
Nodes (8): IncidentMap(), DashboardLayout(), Props, Dashboard(), Props, requestColumns, statusColor, utilizationBarColor()

### Community 49 - "Community 49"
Cohesion: 0.31
Nodes (4): EvacuationCenterController, Builder, JsonResponse, Model

### Community 50 - "Community 50"
Cohesion: 0.33
Nodes (4): Builder, JsonResponse, Request, RequestController

### Community 51 - "Community 51"
Cohesion: 0.22
Nodes (4): DepotController, Response, InertiaPageResponse, Inertia

### Community 52 - "Community 52"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 53 - "Community 53"
Cohesion: 0.22
Nodes (10): Coolify, Directions and Turn-by-Turn Navigation, DirectionsController, DirectionsRouter, Docker Compose, GeofenceService, OSRM (Open Source Routing Machine), OsrmDirectionsProvider (+2 more)

### Community 54 - "Community 54"
Cohesion: 0.31
Nodes (4): Builder, JsonResponse, Model, ResourceController

### Community 55 - "Community 55"
Cohesion: 0.36
Nodes (4): AuthenticatedSessionController, RedirectResponse, Request, Response

### Community 56 - "Community 56"
Cohesion: 0.42
Nodes (4): PasswordResetController, RedirectResponse, Request, Response

### Community 57 - "Community 57"
Cohesion: 0.28
Nodes (4): OptimizationController, RedirectResponse, Response, PublicDashboardController

### Community 58 - "Community 58"
Cohesion: 0.22
Nodes (7): FastAPI, optimize_allocation(), optimize_routing(), Called by App\\Jobs\\RunOptimizationJob (Laravel) whenever an incident     is lo, Depot-to-zone routing suggestions (straight-line v1; OSRM later)., health(), Health check consumed by Docker/Compose and the backend's depends_on condition.

### Community 59 - "Community 59"
Cohesion: 0.29
Nodes (8): Allocation (Domain Model), Depot (Domain Model), Disaster (Domain Model), OptimizationRun (Domain Model), Resource (Domain Model), ResourceRequest (Domain Model), ResourceType (Domain Model), Zone (Domain Model)

### Community 60 - "Community 60"
Cohesion: 0.36
Nodes (3): BelongsTo, Report, ReportDownloadTest

### Community 62 - "Community 62"
Cohesion: 0.29
Nodes (3): Attribute, HasRoles, Notifiable

### Community 63 - "Community 63"
Cohesion: 0.38
Nodes (4): DisasterDataSeeder, FakerDataSeeder, RoleSeeder, Seeder

### Community 66 - "Community 66"
Cohesion: 0.53
Nodes (3): Attempting, PreventInactiveLogin, EventServiceProvider

### Community 68 - "Community 68"
Cohesion: 0.60
Nodes (3): JsonResponse, Request, ProfileController

### Community 73 - "Community 73"
Cohesion: 0.70
Nodes (4): assertBatchAssigned(), assignedBatchIds(), Builder, scopeToAssignedBatches()

### Community 75 - "Community 75"
Cohesion: 0.50
Nodes (5): Automated Disaster Feed Polling, DisasterFeedIngestor, GdacsFeedClient, PollDisasterFeedsJob, UsgsEarthquakeFeedClient

### Community 76 - "Community 76"
Cohesion: 0.50
Nodes (4): AppLayout, DashboardController, Dashboard (Page), Login (Page)

### Community 87 - "Community 87"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 109 - "Community 109"
Cohesion: 0.67
Nodes (3): DirectionsPanel (React Component), useDirections (React Hook), useVoiceGuidance (React Hook)

## Knowledge Gaps
- **169 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+164 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **31 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Auth Model & Seeder` to `Community 66`, `Depot & Resource Models`, `Dashboard & Allocation Models`, `Community 42`, `Community 43`, `Community 44`, `Community 45`, `Disaster Feed Settings & Tests`, `Community 47`, `Community 74`, `Database Factories`, `Community 85`, `Community 27`, `Community 60`, `Community 62`, `Community 31`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Why does `Controller` connect `Community 38` to `Admin API Controllers`, `Dashboard & Allocation Models`, `Community 70`, `Directions & Routing Settings API`, `Community 71`, `Community 44`, `BaseController CRUD`, `Community 45`, `Community 77`, `Community 78`, `Community 79`, `Community 80`, `Community 51`, `Community 81`, `Community 55`, `Community 56`, `Community 57`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Why does `Inertia` connect `Community 51` to `Community 38`, `Dashboard & Allocation Models`, `Community 71`, `Community 77`, `Community 78`, `Community 79`, `Community 80`, `Community 81`, `Service Providers`, `Community 55`, `Community 56`, `Community 57`, `Community 30`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `Disaster` (e.g. with `.beforeSave()` and `.downloadNdrrmcSitrep()`) actually correct?**
  _`Disaster` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _178 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Admin API Controllers` be split into smaller, more focused modules?**
  _Cohesion score 0.05179982440737489 - nodes in this community are weakly interconnected._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.08078431372549019 - nodes in this community are weakly interconnected._