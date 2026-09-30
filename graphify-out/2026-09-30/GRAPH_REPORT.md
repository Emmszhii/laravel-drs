# Graph Report - laravel-drs  (2026-09-29)

## Corpus Check
- 288 files · ~111,636 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1459 nodes · 2852 edges · 183 communities (115 shown, 68 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 158 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `9c70e400`
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
- index.ts
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- AuthenticatedSessionController.php
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
- use-flash-toast.ts
- config.py
- Button.tsx
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
- InertiaPageResponse
- Index.tsx
- DepotCapacityChecker
- DispatchTest
- Open a Claude-in-Chrome tab
- UsersController.php

## God Nodes (most connected - your core abstractions)
1. `User` - 57 edges
2. `ResourceType` - 32 edges
3. `StockLedger` - 31 edges
4. `BaseController` - 30 edges
5. `Controller` - 30 edges
6. `IcsRequest` - 30 edges
7. `StockEntry` - 30 edges
8. `ReplenishmentRun` - 23 edges
9. `ReplenishmentTest` - 23 edges
10. `StockLedgerTest` - 22 edges

## Surprising Connections (you probably didn't know these)
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `ColumnFilterControlProps` --references--> `ColumnDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/ColumnFilterControl.tsx → backend/resources/js/Types/reusable/data-table.ts
- `UseModuleOptions` --references--> `CrudQueryParams`  [EXTRACTED]
  backend/resources/js/Hooks/use-module.ts → backend/resources/js/Types/reusable/pagination.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (183 total, 68 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.15
Nodes (10): JsonResponse, Request, StockController, Request, Response, StockController, StockConflict, QuarterHistory (+2 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.07
Nodes (45): ActivityLogEntry, activityLogService, getLastSeenId(), NotificationBell(), OfflineBanner(), UserProfileSection(), UserProfileSectionProps, AuthUser (+37 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.19
Nodes (19): http, unwrap(), appendNestedObject(), appendNestedParam(), buildFormData(), buildQueryString(), hasBinaryFiles(), isFileFieldValue() (+11 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.20
Nodes (7): ReplenishmentRunUpdated, RunReplenishmentJob, Dispatchable, InteractsWithQueue, InteractsWithSockets, ShouldBroadcastNow, ShouldQueue

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.14
Nodes (7): Depot, HasMany, HasMany, ResourceType, CatalogTest, EvaluationTest, HasFactory

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.13
Nodes (18): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, ColumnFilterControlProps, DEFAULT_TABS, StatusFilter() (+10 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.16
Nodes (9): Request, Response, ReplenishmentController, Backtest, QuarterBudget, BelongsTo, VedPolicy, HasUuids (+1 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.27
Nodes (5): JsonResponse, Request, ReplenishmentController, ReplenishmentRun, SymfonyResponse

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.27
Nodes (12): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+4 more)

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.10
Nodes (14): ActivityLogController, JsonResponse, Request, LogPageVisit, Closure, Request, Response, AppLogger (+6 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.15
Nodes (14): AddRecordButton(), AddRecordButtonProps, normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), columns, DepotsIndex(), fields (+6 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.14
Nodes (6): ExampleTest, TestCase, ExampleTest, BaseTestCase, Collection, RefreshDatabase

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.05
Nodes (62): Field(), baseFor(), buildYupSchema(), withCustom(), withRequired(), buildInitialValues(), buildPayload(), mapApiErrorsToFormik() (+54 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.08
Nodes (15): DepotController, Model, JsonResponse, Request, ProfileController, Model, ResourceTypeController, JsonResponse (+7 more)

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.33
Nodes (7): ApiEnvelope, ApiError, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError()

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.22
Nodes (13): buildRecordMenu(), ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState, RenderModalProps (+5 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.43
Nodes (3): IcsRequestLine, BelongsTo, HasOne

### Community 19 - "Database Factories"
Cohesion: 0.16
Nodes (5): DepotFactory, ResourceTypeFactory, UserFactory, Factory, static

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.38
Nodes (6): RowMenu(), RowMenuAction, SettingsListHeader(), SettingsRow(), TextCell(), cn()

### Community 21 - "Service Providers"
Cohesion: 0.14
Nodes (7): Attempting, PreventInactiveLogin, ActivityLogServiceProvider, AppServiceProvider, EventServiceProvider, InertiaServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.12
Nodes (15): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DataCheckFlag, Depot, @inertiajs/core, PageProps (+7 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.32
Nodes (4): JsonResponse, Request, SmtpSettingController, SmtpSetting

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 25 - "Index.tsx"
Cohesion: 0.33
Nodes (6): IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn, toUrl()

### Community 29 - "use-module.ts"
Cohesion: 0.26
Nodes (13): InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions(), buildArchiveUrl(), buildDeleteUrl() (+5 more)

### Community 31 - "RoleGatingTest"
Cohesion: 0.22
Nodes (14): BacktestPanel(), BASIS_NOTE, BudgetCurve(), errorMessage(), FlagList(), HistoryUpload(), peso(), Props (+6 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.13
Nodes (15): FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps, Toolbar() (+7 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.22
Nodes (11): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, Pydantic, python-json-logger, Laravel Reverb (+3 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.07
Nodes (17): IcsRequestController, JsonResponse, Request, IcsRequestController, Request, Response, Borrow, BelongsTo (+9 more)

### Community 39 - "Users.tsx"
Cohesion: 0.17
Nodes (6): Button(), Props, SettingsTabs(), TABS, ResetPasswordProps, Props

### Community 46 - "index.ts"
Cohesion: 0.36
Nodes (8): GlobalModalContext, GlobalModalProvider(), createGlobalModalStore(), GlobalModalStore, Listener, ModalEntry, GlobalModalHandle, useGlobalModal()

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (34): DisasterResource+ — System Specification, 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper] (+26 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 55 - "AuthenticatedSessionController.php"
Cohesion: 0.19
Nodes (8): AuthenticatedSessionController, RedirectResponse, Request, Response, PasswordResetController, RedirectResponse, Request, Response

### Community 57 - "Controller"
Cohesion: 0.14
Nodes (10): Controller, DepotController, Response, Response, ResourceInventoryController, Response, UsersController, Response (+2 more)

### Community 58 - "data-table.ts"
Cohesion: 0.15
Nodes (18): Borrow, Borrowed(), LABEL, NEXT, DIVISIONS, FileForm(), IcsRequest, Item (+10 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.20
Nodes (14): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, ApiEnvelope, ApiError (+6 more)

### Community 62 - "Architecture"
Cohesion: 0.15
Nodes (11): Architecture, Backend request layering, Commands, Domain model conventions, Frontend (backend/resources/js), graphify, Repo layout (three deployable units), Roles (system-spec.md §2) (+3 more)

### Community 63 - "Seeder"
Cohesion: 0.11
Nodes (10): Attribute, CatalogSeeder, DatabaseSeeder, ReplenishmentDemoSeeder, RoleSeeder, UserSeeder, HasRoles, Notifiable (+2 more)

### Community 64 - "FormModal.tsx"
Cohesion: 0.21
Nodes (9): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable, Queueable (+1 more)

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.10
Nodes (5): Authenticatable, User, RoleGatingTest, UserAccountLifecycleTest, EnsureUserIsActiveTest

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.06
Nodes (61): BaseModel, FastAPI, PuLP, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     ru, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and (+53 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.13
Nodes (6): HistoryLine, BelongsTo, HistoryImporter, Permissions, Carbon, self

### Community 73 - "Users.tsx"
Cohesion: 0.12
Nodes (14): queryClient, ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, ICON_COLOR, SystemToastProvider(), ToastContext (+6 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.33
Nodes (5): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Quick start (Docker)

### Community 78 - "Index.tsx"
Cohesion: 0.24
Nodes (9): APP_ROLES, AppRole, normalizeRole(), ROLE_LABELS, columns, makeUserCard(), roleOptions, SettingsUsersIndex() (+1 more)

### Community 87 - "config.py"
Cohesion: 0.50
Nodes (3): BaseSettings, Config, Settings

### Community 107 - "TableLoading.tsx"
Cohesion: 0.26
Nodes (4): DataCheckFlag, BelongsTo, StockCount, DataChecks

### Community 127 - "cache.php"
Cohesion: 0.20
Nodes (3): BelongsTo, StockEntry, StockLedgerTest

### Community 234 - "watch-boneyard.mjs"
Cohesion: 0.38
Nodes (6): backendDir, envPath, loadRootEnv(), main(), routes, runBuild()

### Community 235 - "refresh-boneyard-session.mjs"
Cohesion: 0.47
Nodes (5): backendDir, cookieHeader(), envPath, main(), parseCookies()

### Community 238 - "Index.tsx"
Cohesion: 0.40
Nodes (9): beforeSave(), deleteStoredFile(), fileWasRemoved(), Model, Request, removedFileIds(), storeUploadedFile(), storeUploadedFiles() (+1 more)

### Community 244 - "DispatchTest"
Cohesion: 0.25
Nodes (7): 1. Automated (run before every evaluation session), 2. Offline resilience on devices (3 runs, then Safari separately), 3. Performance: run to dashboard (< 1 s on the office network), 4. User acceptance tasks, 5. Explanation task (target ≥ 80% correct), 6. ISO/IEC 25010:2023 survey, DisasterResource+ — Evaluation Protocol (Chapter III, Software Testing)

### Community 248 - "Open a Claude-in-Chrome tab"
Cohesion: 0.40
Nodes (4): Context, Open a Claude-in-Chrome tab, Steps, Verification

## Knowledge Gaps
- **262 isolated node(s):** `Props`, `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **68 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `Admin API Controllers`, `PasswordResetMail.php`, `Dashboard & Allocation Models`, `Data Table Query Hooks`, `Auth Pages & Buttons`, `BaseController CRUD`, `AuthenticatedSessionController.php`, `Record Modal & Image Lightbox`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `DisasterFeedIngestorTest`, `Filter/Pagination/Map Widgets`, `use-file-upload-field.tsx`, `Dashboard & Allocation Models`, `PasswordResetMail.php`, `Disaster Feed Settings & Tests`, `Database Factories`, `Service Providers`, `Record Modal & Image Lightbox`, `cache.php`, `Seeder`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Why does `BaseController` connect `BaseController CRUD` to `Controller`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Are the 27 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 27 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `ResourceType` (e.g. with `.backtest()` and `.index()`) actually correct?**
  _`ResourceType` has 13 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Props`, `ChangePasswordValues`, `fields` to the rest of the system?**
  _280 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Bootstrap & Activity Log` be split into smaller, more focused modules?**
  _Cohesion score 0.07175141242937853 - nodes in this community are weakly interconnected._