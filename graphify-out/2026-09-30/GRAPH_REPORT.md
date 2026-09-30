# Graph Report - laravel-drs  (2026-09-30)

## Corpus Check
- 258 files · ~99,348 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1365 nodes · 2707 edges · 183 communities (122 shown, 61 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 168 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c0cbd350`
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
- ReportController.php
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
- fields.ts
- ConfirmInUseModal.tsx
- IcsRequestController.php
- index.ts
- syncOutbox
- ReplenishmentController.php
- ChangePasswordModal.tsx
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- AuthenticatedSessionController.php
- PreventInactiveLogin.php
- Controller
- data-table.ts
- Allocation (Domain Model)
- ChangePasswordModal.tsx
- Architecture
- Seeder
- FormModal.tsx
- DisasterFeedIngestorTest
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
- TableLoading.tsx
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
2. `ResourceType` - 32 edges
3. `StockEntry` - 32 edges
4. `IcsRequest` - 31 edges
5. `StockLedger` - 31 edges
6. `BaseController` - 30 edges
7. `Controller` - 30 edges
8. `RequestWorkflow` - 25 edges
9. `ReplenishmentRun` - 23 edges
10. `StockConflict` - 23 edges

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

## Communities (183 total, 61 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.21
Nodes (4): BelongsTo, QuarterHistory, InventoryBurnRateReportBuilder, Carbon

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.17
Nodes (14): DrsOfflineDB, OutboxItem, OutboxItemKind, OutboxItemStatus, CheckRecords(), Conflict, ConflictList(), CountForm() (+6 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.18
Nodes (21): http, unwrap(), appendNestedObject(), appendNestedParam(), buildFormData(), buildQueryString(), hasBinaryFiles(), isFileFieldValue() (+13 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.13
Nodes (9): ReplenishmentRunUpdated, RunReplenishmentJob, ReplenishmentServiceClient, Reservations, Dispatchable, InteractsWithQueue, InteractsWithSockets, ShouldBroadcastNow (+1 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.14
Nodes (8): Depot, HasMany, HistoryLine, HasMany, ResourceType, CatalogSeeder, CatalogTest, HasFactory

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.21
Nodes (10): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, DEFAULT_TABS, StatusFilter(), StatusFilterProps (+2 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.18
Nodes (6): IcsRequestLine, BelongsTo, QuarterBudget, HasOne, HasUuids, Model

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.18
Nodes (8): JsonResponse, Request, ReplenishmentController, Backtest, ReplenishmentRun, HistoryImporter, InventoryBurnRateCsvExporter, SymfonyResponse

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.27
Nodes (11): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+3 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.07
Nodes (16): ActivityLogController, JsonResponse, Request, AppLogger, AppLoggerObserver, Model, ActivityLogger, Model (+8 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.22
Nodes (9): depotsService, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), columns, DepotsIndex(), fields, STATUS_OPTIONS (+1 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.16
Nodes (5): ExampleTest, TestCase, EnsureUserIsActiveTest, ExampleTest, BaseTestCase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.27
Nodes (11): buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue(), FormModal(), Values, FormModalBody(), Values (+3 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.08
Nodes (15): DepotController, Model, JsonResponse, Request, ProfileController, Model, ResourceTypeController, JsonResponse (+7 more)

### Community 14 - "Confirmation Modals"
Cohesion: 0.17
Nodes (8): ActivityLogEntry, activityLogService, queryClient, getLastSeenId(), NotificationBell(), OfflineBanner(), navItems, AppLayout()

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.48
Nodes (6): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError()

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.25
Nodes (12): ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState, RenderModalProps, StatusFilterTab (+4 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.21
Nodes (11): ModalCenter(), ModalCenterProps, ModalComponentProps, Size, WIDTHS, ModalSide(), ModalSideProps, Side (+3 more)

### Community 19 - "Database Factories"
Cohesion: 0.22
Nodes (11): ImageLightbox(), ImageLightboxProps, emptyFileFieldValue, fileNameFromUrl(), FileUploadField(), FileUploadFieldProps, formatBytes(), isImageName() (+3 more)

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.26
Nodes (10): UserProfileSection(), UserProfileSectionProps, AuthUser, initialsFor(), useAuth(), detectSystemTheme(), getStoredTheme(), setStoredTheme() (+2 more)

### Community 21 - "Service Providers"
Cohesion: 0.29
Nodes (3): ActivityLogServiceProvider, AppServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.12
Nodes (15): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DataCheckFlag, Depot, @inertiajs/core, PageProps (+7 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.25
Nodes (3): Attribute, HasRoles, Notifiable

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.33
Nodes (7): AsyncMultiSelectField(), AsyncMultiSelectFieldProps, AsyncSelectField(), valuesEqual(), FieldOption, AsyncMultiSelectFieldProps, AsyncSelectFieldProps

### Community 27 - "Dispatch"
Cohesion: 0.49
Nodes (8): getDb(), listPending(), markFailed(), markPending(), remove(), setStatus(), StockSyncResult, upload()

### Community 28 - "ReportController.php"
Cohesion: 0.33
Nodes (5): resourceTypesService, TypeCard(), typeColumns, typeFields, VED_LABELS

### Community 29 - "use-module.ts"
Cohesion: 0.25
Nodes (14): InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions(), buildArchiveUrl(), buildDeleteUrl() (+6 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.22
Nodes (14): BacktestPanel(), BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props (+6 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.22
Nodes (11): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, Toolbar(), ToolbarProps, ViewToggle(), ViewToggleProps, DataTableCardField() (+3 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.22
Nodes (11): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, Pydantic, python-json-logger, Laravel Reverb (+3 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.08
Nodes (13): IcsRequestController, JsonResponse, Request, Borrow, BelongsTo, HasMany, IcsRequest, BelongsTo (+5 more)

### Community 39 - "Users.tsx"
Cohesion: 0.27
Nodes (3): Button(), Props, ResetPasswordProps

### Community 40 - ".log"
Cohesion: 0.28
Nodes (3): Field(), DynamicFieldProps, formatDateShort()

### Community 41 - "fields.ts"
Cohesion: 0.46
Nodes (6): FormModalConfig, FormModalLayout, FormModalProps, FieldDef, FieldType, ModalMode

### Community 42 - "ConfirmInUseModal.tsx"
Cohesion: 0.39
Nodes (5): ConfirmDeleteModal(), ConfirmDeleteModalProps, ConfirmInUseModal(), ConfirmInUseModalProps, useScrollLock()

### Community 44 - "IcsRequestController.php"
Cohesion: 0.62
Nodes (3): IcsRequestController, Request, Response

### Community 46 - "index.ts"
Cohesion: 0.29
Nodes (4): PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps

### Community 47 - "syncOutbox"
Cohesion: 0.52
Nodes (5): useOfflineSync(), useOnlineStatus(), listAll(), syncOutbox(), StockIndex()

### Community 50 - "ReplenishmentController.php"
Cohesion: 0.53
Nodes (4): Request, Response, ReplenishmentController, VedPolicy

### Community 51 - "ChangePasswordModal.tsx"
Cohesion: 0.40
Nodes (5): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, useToast()

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (34): DisasterResource+ — System Specification, 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper] (+26 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 55 - "AuthenticatedSessionController.php"
Cohesion: 0.53
Nodes (5): baseFor(), buildYupSchema(), withCustom(), withRequired(), FileFieldValue

### Community 57 - "Controller"
Cohesion: 0.07
Nodes (25): JsonResponse, Request, SmtpSettingController, AuthenticatedSessionController, RedirectResponse, Request, Response, PasswordResetController (+17 more)

### Community 58 - "data-table.ts"
Cohesion: 0.13
Nodes (23): deviceLabel(), enqueueStock(), isNetworkError(), Borrow, Borrowed(), LABEL, NEXT, pendingBorrows() (+15 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 62 - "Architecture"
Cohesion: 0.15
Nodes (11): Architecture, Backend request layering, Commands, Domain model conventions, Frontend (backend/resources/js), graphify, Repo layout (three deployable units), Roles (system-spec.md §2) (+3 more)

### Community 63 - "Seeder"
Cohesion: 0.23
Nodes (6): DatabaseSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 64 - "FormModal.tsx"
Cohesion: 0.21
Nodes (9): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable, Queueable (+1 more)

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.12
Nodes (4): Authenticatable, User, RoleGatingTest, UserAccountLifecycleTest

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.06
Nodes (61): BaseModel, FastAPI, PuLP, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     ru, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and (+53 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.18
Nodes (4): BelongsTo, StockConflict, EvaluationTest, RefreshDatabase

### Community 73 - "Users.tsx"
Cohesion: 0.22
Nodes (8): ICON_COLOR, SystemToastProvider(), ToastContext, ToastContextValue, ToastItem, VARIANT_ICON, VARIANT_STYLES, cn()

### Community 74 - "DisasterResource+"
Cohesion: 0.33
Nodes (5): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Quick start (Docker)

### Community 78 - "Index.tsx"
Cohesion: 0.18
Nodes (9): ApiError, SettingsTabs(), TABS, Props, columns, makeUserCard(), roleOptions, SettingsUsersIndex() (+1 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.26
Nodes (4): DataCheckFlag, BelongsTo, StockCount, DataChecks

### Community 127 - "cache.php"
Cohesion: 0.11
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
- **251 isolated node(s):** `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props`, `DropdownOption` (+246 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **61 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `PasswordResetMail.php`, `Data Table Query Hooks`, `Auth Pages & Buttons`, `IcsRequestController.php`, `BaseController CRUD`, `ReplenishmentController.php`, `cache.php`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `Admin API Controllers`, `DisasterFeedIngestorTest`, `Filter/Pagination/Map Widgets`, `use-file-upload-field.tsx`, `Dashboard & Allocation Models`, `PasswordResetMail.php`, `Auth Pages & Buttons`, `Disaster Feed Settings & Tests`, `Record Modal & Image Lightbox`, `PreventInactiveLogin.php`, `Controller`, `cache.php`, `Seeder`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `Controller`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Are the 27 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 27 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `ResourceType` (e.g. with `.backtest()` and `.index()`) actually correct?**
  _`ResourceType` has 13 INFERRED edges - model-reasoned connections that need verification._
- **Are the 12 inferred relationships involving `StockEntry` (e.g. with `.test_an_item_with_stock_entries_cannot_be_deleted()` and `.test_dashboard_figures_match_the_database()`) actually correct?**
  _`StockEntry` has 12 INFERRED edges - model-reasoned connections that need verification._
- **What connects `ChangePasswordValues`, `fields`, `ChangePasswordModalProps` to the rest of the system?**
  _269 weakly-connected nodes found - possible documentation gaps or missing edges._