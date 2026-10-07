# Graph Report - laravel-drs  (2026-10-03)

## Corpus Check
- 272 files · ~115,880 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1523 nodes · 3071 edges · 192 communities (131 shown, 61 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 222 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e3c63cec`
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
- types.ts
- QuarterHistory
- Kit
- Toast.tsx
- DataCheckFlag
- FieldOption
- DisasterResource+ — System Specification (from Capstone Chapters I–III)
- Directions and Turn-by-Turn Navigation
- Chapters 1–3: What to Change to Use the Novelty Options
- use-file-upload-field.tsx
- sync.ts
- Controller
- data-table.ts
- Allocation (Domain Model)
- ChangePasswordModal.tsx
- ReplenishmentServiceClient
- Architecture
- Seeder
- client.ts
- IcsRequestController.php
- OsrmDirectionsProvider
- DisasterResource+: Where a Real Technical Contribution Can Come From
- test_replenishment.py
- use-file-upload-field.tsx
- InventoryBurnRateCsvExporter.php
- README.md
- Users.tsx
- DisasterResource+
- Automated Disaster Feed Polling
- AppLayout
- test_table7.py
- Index.tsx
- DashboardController
- Dashboard (Page)
- Depot (Domain Model)
- CatalogTest
- 2026_08_02_085355_split_users_name_into_first_last.php
- 2026_08_02_085419_split_depots_contact_name_into_first_last.php
- DepotCapacityChecker
- ReplenishmentTest.php
- config.py
- Pagination.tsx
- ChangePasswordModal.tsx
- build-yup-schema.ts
- DirectionsPanel (React Component)
- configure_logging
- AutoResolveIncidentsJob
- Smtp.tsx
- vite-env.d.ts
- init-postgis.sh
- cache.php
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
1. `User` - 67 edges
2. `ResourceType` - 42 edges
3. `IcsRequest` - 36 edges
4. `StockEntry` - 35 edges
5. `StockLedger` - 33 edges
6. `Controller` - 32 edges
7. `ReplenishmentItem` - 32 edges
8. `BaseController` - 30 edges
9. `ReplenishmentRequest` - 29 edges
10. `RequestWorkflow` - 27 edges

## Surprising Connections (you probably didn't know these)
- `Solver Service (FastAPI)` --uses--> `Pydantic`  [EXTRACTED]
  README.md → solver-service/requirements.txt
- `Docker Compose` --orchestrates--> `Laravel Reverb`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `PostgreSQL with PostGIS`  [EXTRACTED]
  docker-compose.yml → CLAUDE.md
- `Docker Compose` --orchestrates--> `OSRM (Open Source Routing Machine)`  [EXTRACTED]
  docker-compose.yml → README.md
- `DynamicFieldProps` --references--> `FieldDef`  [EXTRACTED]
  backend/resources/js/Components/table/components/RecordModalField.tsx → backend/resources/js/Types/reusable/fields.ts

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Core Resource Optimization Flow** — runoptimizationjob, optimizationserviceclient, pulpalloc_solver, ortoolsrouting_solver, basesolver [EXTRACTED 1.00]
- **Domain Model Entity Relationships** — disaster_model, zone_model, resourcerequest_model, depot_model, resource_model, resourcetype_model, allocation_model, optimizationrun_model [EXTRACTED 1.00]
- **Automated Disaster Feed and Incident Processing** — polldisasterfeeds_job, disasterfeed_ingestor, usgsearth_feedclient, gdacs_feedclient, autoresolveincidents_job [EXTRACTED 1.00]

## Communities (192 total, 61 thin omitted)

### Community 0 - "Admin API Controllers"
Cohesion: 0.20
Nodes (21): BacktestRequest, Items carry their full encoded history; the backtest cuts it per quarter., ReplenishmentItem, ReplenishmentRequest, test_budget_never_exceeded_and_money_left_only_when_targets_met(), test_space_constraint_limits_orders_when_modeled(), by_id(), no_solution() (+13 more)

### Community 1 - "Frontend Bootstrap & Activity Log"
Cohesion: 0.16
Nodes (15): markFailed(), markPending(), remove(), StockSyncResult, syncOutbox(), SyncOutcome, upload(), Conflict (+7 more)

### Community 2 - "API HTTP Client"
Cohesion: 0.19
Nodes (19): http, unwrap(), appendNestedObject(), appendNestedParam(), buildFormData(), buildQueryString(), hasBinaryFiles(), isFileFieldValue() (+11 more)

### Community 3 - "Depot & Resource Models"
Cohesion: 0.21
Nodes (9): NewAccountMail, Content, Envelope, PasswordResetMail, Content, Envelope, Mailable, Queueable (+1 more)

### Community 4 - "Filter/Pagination/Map Widgets"
Cohesion: 0.12
Nodes (16): 1. The paper in one minute, 2. Numbers to have in your head, 3. Chapter by chapter: the argument and the support, 4. Glossary, 5. Questions a reviewer is likely to ask, 6. Gaps — what a reviewer could still find, 7. Before you present, DisasterResource+ — reviewer study guide (+8 more)

### Community 5 - "Form Field & Dropdown Components"
Cohesion: 0.21
Nodes (10): Dropdown(), DropdownOption, DropdownProps, CardFilterPanel(), CardFilterPanelProps, DEFAULT_TABS, StatusFilter(), StatusFilterProps (+2 more)

### Community 6 - "Dashboard & Allocation Models"
Cohesion: 0.17
Nodes (14): PuLP, backtest(), budget_curve(), flat_rule_orders(), _ms(), Budget-limited quarterly replenishment (Chapter III, Model Building).  Stage 1 —, Re-solves at 11 budget levels, 50%-150% of the entered budget in 10% steps     (, Textbook rule: average use plus z standard deviations of quarterly use (SAFETY_Z (+6 more)

### Community 7 - "Data Table Query Hooks"
Cohesion: 0.17
Nodes (10): Request, Response, ReplenishmentController, Backtest, KitLine, QuarterBudget, BelongsTo, VedPolicy (+2 more)

### Community 8 - "Directions & Routing Settings API"
Cohesion: 0.43
Nodes (3): IcsRequestLine, BelongsTo, HasOne

### Community 9 - "Auth Pages & Buttons"
Cohesion: 0.07
Nodes (16): ActivityLogController, JsonResponse, Request, AppLogger, AppLoggerObserver, Model, ActivityLogger, Model (+8 more)

### Community 10 - "Resource Types & Admin Layout"
Cohesion: 0.15
Nodes (14): normalizeQueryKey(), tableListInvalidateKeys(), usePermission(), CATEGORY_LABEL, categoryOptions, columns, DepotsIndex(), fields (+6 more)

### Community 11 - "Disaster Feed Settings & Tests"
Cohesion: 0.14
Nodes (9): Depot, HasMany, HasMany, ResourceType, StockConflict, CatalogTest, EvaluationTest, HasFactory (+1 more)

### Community 12 - "Form Modal Schema Builder"
Cohesion: 0.31
Nodes (11): buildInitialValues(), buildPayload(), mapApiErrorsToFormik(), seedFieldValue(), FormModal(), Values, FormModalConfig, FormModalLayout (+3 more)

### Community 13 - "BaseController CRUD"
Cohesion: 0.08
Nodes (15): DepotController, Model, JsonResponse, Request, ProfileController, Model, ResourceTypeController, JsonResponse (+7 more)

### Community 14 - "Confirmation Modals"
Cohesion: 0.25
Nodes (6): BaseModel, Pydantic, _check_items(), OrderLine, ReplenishmentResponse, _line()

### Community 15 - "Pagination Bar Components"
Cohesion: 0.29
Nodes (6): Canonical tabs, CLAUDE.md — Capstone thesis writing (Chapters 1–3), Editing the Google Doc safely, Hard formatting rules (from the pattern guides), Known open defects (as of 2026-09-07 analysis), Scope claims must trace to the interview

### Community 16 - "User Auth Model & Seeder"
Cohesion: 0.17
Nodes (11): Critical, Critique of the paper and the system (2026-09-30), Main weaknesses, Major, Minor, Status, Suggested order, The paper (+3 more)

### Community 17 - "Evacuation Centers Table UI"
Cohesion: 0.18
Nodes (9): FastAPI, backtest(), budget_curve(), Called by App\\Jobs\\RunReplenishmentJob when the Head of Office (Admin)     ru, Same payload as /solve; returns 11 {budget, expected_shortage} points and the ne, Rolling-origin backtest: flat 1.5 vs per-item multiplier vs scenario model (and, solve(), health() (+1 more)

### Community 18 - "Disaster Model & Auto-Resolve Job"
Cohesion: 0.16
Nodes (14): ConfirmDeleteModal(), ConfirmDeleteModalProps, ModalCenter(), ModalCenterProps, ModalComponentProps, Size, WIDTHS, ModalSide() (+6 more)

### Community 19 - "Database Factories"
Cohesion: 0.57
Nodes (6): office_sized_request(), Performance pass marks (Chapter III, Performance Testing Procedure): a Stage 2, test_budget_curve_of_11_resolves_under_20_seconds(), test_stage1_scales_to_200_items(), test_stage2_solves_200_items_12_scenarios_under_2_seconds(), timed()

### Community 20 - "Record Row Menu & Settings List"
Cohesion: 0.33
Nodes (5): ActivityLogEntry, activityLogService, getLastSeenId(), NotificationBell(), navItems

### Community 21 - "Service Providers"
Cohesion: 0.29
Nodes (3): ActivityLogServiceProvider, AppServiceProvider, ServiceProvider

### Community 22 - "Shared TypeScript Model Types"
Cohesion: 0.12
Nodes (15): AdminUser, Backtest, BacktestRuleScore, BudgetCurvePoint, DataCheckFlag, Depot, @inertiajs/core, PageProps (+7 more)

### Community 23 - "Record Modal & Image Lightbox"
Cohesion: 0.28
Nodes (12): InUseEntry, RowActionMutations, RowActionsConfig, ShowToast, ToastVariant, useRecordRowActions(), buildArchiveUrl(), buildDeleteUrl() (+4 more)

### Community 24 - "Role/Active Auth Middleware"
Cohesion: 0.16
Nodes (11): EnsureUserHasRole, Closure, Request, Response, EnsureUserIsActive, Closure, Request, Response (+3 more)

### Community 27 - "Dispatch"
Cohesion: 0.30
Nodes (10): getDb(), changed(), currentUser, deviceLabel(), enqueueStock(), listAll(), listPending(), ownedBy() (+2 more)

### Community 29 - "use-module.ts"
Cohesion: 0.20
Nodes (4): Borrow, BelongsTo, HasMany, IcsFormFieldsTest

### Community 31 - "RoleGatingTest"
Cohesion: 0.18
Nodes (17): errorMessage(), BacktestPanel(), BASIS_NOTE, BudgetCurve(), FLAGGED_REASONS, FlagList(), HistoryUpload(), peso() (+9 more)

### Community 33 - "DisasterFeedIngestor"
Cohesion: 0.23
Nodes (10): ConfirmInUseModal(), ConfirmInUseModalProps, FetchingSpinner(), DefaultRecordCard(), DefaultRecordCardProps, Toolbar(), ToolbarProps, DataTableCardField() (+2 more)

### Community 34 - "AllocationRequest"
Cohesion: 0.24
Nodes (10): Backend (Laravel), Coolify, Docker Compose, OSRM (Open Source Routing Machine), PostgreSQL with PostGIS, python-json-logger, Laravel Reverb, Solver Service (FastAPI) (+2 more)

### Community 36 - "PasswordResetMail.php"
Cohesion: 0.09
Nodes (10): IcsRequestController, JsonResponse, Request, IcsRequest, BelongsTo, HasMany, BelongsTo, StatusChange (+2 more)

### Community 39 - "Users.tsx"
Cohesion: 0.27
Nodes (3): Button(), Props, ResetPasswordProps

### Community 40 - ".log"
Cohesion: 0.12
Nodes (6): CatalogSeeder, DatabaseSeeder, RoleSeeder, UserSeeder, Seeder, WithoutModelEvents

### Community 42 - "types.ts"
Cohesion: 0.23
Nodes (11): ViewToggle(), ViewToggleProps, ParsedApiError, CardActions, ColumnFilterType, DataTableProps, InUseEntry, ModalState (+3 more)

### Community 44 - "QuarterHistory"
Cohesion: 0.13
Nodes (8): HistoryLine, BelongsTo, HistoryImporter, QuarterHistory, InventoryBurnRateCsvExporter, InventoryBurnRateReportBuilder, ReplenishmentDemoSeeder, Carbon

### Community 46 - "Kit"
Cohesion: 0.24
Nodes (4): ExampleTest, TestCase, ExampleTest, BaseTestCase

### Community 47 - "Toast.tsx"
Cohesion: 0.20
Nodes (9): ApiError, ICON_COLOR, SystemToastProvider(), ToastContext, ToastContextValue, ToastItem, VARIANT_ICON, VARIANT_STYLES (+1 more)

### Community 50 - "DataCheckFlag"
Cohesion: 0.24
Nodes (4): DataCheckFlag, BelongsTo, StockCount, DataChecks

### Community 51 - "FieldOption"
Cohesion: 0.27
Nodes (12): TableQueryParams, useDebouncedValue(), useInvalidateTable(), useTableQuery(), useTableRefresh(), clientMeta(), isFilterValueActive(), normalizeQueryKey() (+4 more)

### Community 52 - "DisasterResource+ — System Specification (from Capstone Chapters I–III)"
Cohesion: 0.06
Nodes (34): DisasterResource+ — System Specification, 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper], 11. Open questions to settle with CDRRMO before building, 1.1 In scope [Paper], 1.2 Out of scope [Paper], 1. What the system is, 2.1 Permission matrix [Proposed: default rules, confirm with the office], 2. Users and roles [Paper] (+26 more)

### Community 54 - "Chapters 1–3: What to Change to Use the Novelty Options"
Cohesion: 0.06
Nodes (31): 1. Front matter, 2.1 Background (pp. 1–3), 2.2 Research problem and research questions (pp. 3–5), 2.3 Research objectives (pp. 6–7), 2.4 Theoretical framework (pp. 7–9, Figure 1), 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model), 2.6 Scope and limitations (pp. 11–14), 2.7 Significance (pp. 14–16) (+23 more)

### Community 55 - "use-file-upload-field.tsx"
Cohesion: 0.29
Nodes (10): buildFormData(), errorMessage(), hasBinaryFiles(), useModule(), UseModuleOptions, UseModuleResult, CrudQueryParams, PaginatedResponse (+2 more)

### Community 56 - "sync.ts"
Cohesion: 0.26
Nodes (8): OfflineBanner(), useOfflineSync(), useOnlineStatus(), DrsOfflineDB, OutboxItem, OutboxItemKind, OutboxItemStatus, lastSyncOutcome()

### Community 57 - "Controller"
Cohesion: 0.06
Nodes (29): KitController, JsonResponse, Request, Response, JsonResponse, Request, SmtpSettingController, AuthenticatedSessionController (+21 more)

### Community 58 - "data-table.ts"
Cohesion: 0.11
Nodes (27): subjectOf(), isNetworkError(), Borrow, Borrowed(), day(), Equipment, LABEL, LendForm() (+19 more)

### Community 60 - "ChangePasswordModal.tsx"
Cohesion: 0.43
Nodes (7): ApiEnvelope, apiFetch(), apiFetchJson(), ApiFetchOptions, getCsrfToken(), readCookie(), xhrRequest()

### Community 61 - "ReplenishmentServiceClient"
Cohesion: 0.16
Nodes (6): Throwable, RunReplenishmentJob, Reservation, Reservations, InteractsWithQueue, ShouldQueue

### Community 62 - "Architecture"
Cohesion: 0.15
Nodes (11): Architecture, Backend request layering, Commands, Domain model conventions, Frontend (backend/resources/js), graphify, Repo layout (three deployable units), Roles (system-spec.md §2) (+3 more)

### Community 63 - "Seeder"
Cohesion: 0.23
Nodes (7): Field(), FormModalBody(), Values, DynamicField(), DynamicFieldProps, isFieldDisabled(), isFieldVisible()

### Community 64 - "client.ts"
Cohesion: 0.48
Nodes (6): ApiEnvelope, attachHeaders(), createHttpClient(), getCsrfToken(), readCookie(), toApiError()

### Community 65 - "IcsRequestController.php"
Cohesion: 0.27
Nodes (5): IcsRequestController, Request, Response, Kit, HasMany

### Community 66 - "OsrmDirectionsProvider"
Cohesion: 0.06
Nodes (10): Attempting, Attribute, Authenticatable, PreventInactiveLogin, User, RoleGatingTest, UserAccountLifecycleTest, EnsureUserIsActiveTest (+2 more)

### Community 67 - "DisasterResource+: Where a Real Technical Contribution Can Come From"
Cohesion: 0.11
Nodes (17): 10. Sources, 1. What already exists (so it can't be your novelty), 2. The options, ranked, 3. The options in detail, 4. Useful features that won't count as new, 5. What to skip in this scope, 6. How to state the novelty (outline only; write it yourself), 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3 (+9 more)

### Community 68 - "test_replenishment.py"
Cohesion: 0.17
Nodes (21): assert_never_rises(), backtest_fixture(), by_id(), Stage 1 replenishment tests — Chapter III, Unit Testing Procedure., Raw per-level objectives (not the carried-forward curve), allowing only the MIP, test_backtest_endpoint_round_trip(), test_backtest_includes_the_classical_safety_stock_rule(), test_backtest_includes_the_offices_actual_orders_when_recorded() (+13 more)

### Community 69 - "use-file-upload-field.tsx"
Cohesion: 0.09
Nodes (11): ReplenishmentRunUpdated, JsonResponse, Request, ReplenishmentController, ReplenishmentRun, ReplenishmentServiceClient, ReplenishmentTest, Dispatchable (+3 more)

### Community 70 - "InventoryBurnRateCsvExporter.php"
Cohesion: 0.24
Nodes (10): ImageLightbox(), ImageLightboxProps, fileNameFromUrl(), FileUploadField(), FileUploadFieldProps, formatBytes(), isImageName(), LocalThumb() (+2 more)

### Community 73 - "Users.tsx"
Cohesion: 0.13
Nodes (17): onUserChange(), queryClient, UserProfileSection(), UserProfileSectionProps, detectSystemTheme(), getStoredTheme(), setStoredTheme(), Theme (+9 more)

### Community 74 - "DisasterResource+"
Cohesion: 0.33
Nodes (5): DisasterResource+, Folder structure, Local dev without pdo_pgsql, Local development without Docker, Quick start (Docker)

### Community 77 - "test_table7.py"
Cohesion: 0.39
Nodes (6): expected_shortage(), items(), Table 7 (Chapter III, Worked Example): in-sample expected shortage of the three, Weighted shortage in quarters of average use, summed over items, averaged over t, test_per_item_multipliers_in_the_table_note(), test_table_7_values_and_the_sentences_under_it()

### Community 78 - "Index.tsx"
Cohesion: 0.18
Nodes (13): APP_ROLES, AppRole, normalizeRole(), ROLE_DESCRIPTIONS, ROLE_LABELS, AuthUser, initialsFor(), useAuth() (+5 more)

### Community 82 - "CatalogTest"
Cohesion: 0.30
Nodes (8): AsyncMultiSelectField(), AsyncMultiSelectFieldProps, AsyncSelectField(), valuesEqual(), FieldOption, FieldType, AsyncMultiSelectFieldProps, AsyncSelectFieldProps

### Community 86 - "ReplenishmentTest.php"
Cohesion: 0.25
Nodes (8): covering_level(), item_target(), Use that would have covered k of the last m quarters: the (m-k+1)-th highest. Wi, (target, basis, multiplier). Target None means the item is listed for Logistics, test_cold_start_rules(), test_covering_level_follows_ved_coverage(), test_multiplier_is_covering_level_over_average(), test_covering_level_scales_k_with_short_history()

### Community 106 - "Pagination.tsx"
Cohesion: 0.29
Nodes (4): PageBtnProps, PageNumbersProps, PaginationBar(), PaginationBarProps

### Community 107 - "ChangePasswordModal.tsx"
Cohesion: 0.40
Nodes (5): ChangePasswordModal(), ChangePasswordModalProps, ChangePasswordValues, fields, useToast()

### Community 108 - "build-yup-schema.ts"
Cohesion: 0.53
Nodes (5): baseFor(), buildYupSchema(), withCustom(), withRequired(), FileFieldValue

### Community 112 - "Smtp.tsx"
Cohesion: 0.40
Nodes (3): SettingsTabs(), TABS, Props

### Community 127 - "cache.php"
Cohesion: 0.10
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
- **278 isolated node(s):** `ChangePasswordValues`, `fields`, `ChangePasswordModalProps`, `Props`, `DropdownOption` (+273 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **61 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `IcsRequestController.php`, `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `Auth Pages & Buttons`, `QuarterHistory`, `BaseController CRUD`, `cache.php`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Why does `User` connect `OsrmDirectionsProvider` to `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `.log`, `Auth Pages & Buttons`, `Disaster Feed Settings & Tests`, `QuarterHistory`, `Controller`, `use-module.ts`, `Index.tsx`, `cache.php`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Why does `ResourceType` connect `Disaster Feed Settings & Tests` to `IcsRequestController.php`, `PasswordResetMail.php`, `use-file-upload-field.tsx`, `Data Table Query Hooks`, `.log`, `QuarterHistory`, `use-module.ts`, `DataCheckFlag`, `Controller`, `ReplenishmentServiceClient`, `Index.tsx`, `cache.php`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `User` (e.g. with `.handle()` and `.run()`) actually correct?**
  _`User` has 32 INFERRED edges - model-reasoned connections that need verification._
- **Are the 16 inferred relationships involving `ResourceType` (e.g. with `.backtest()` and `.borrowed()`) actually correct?**
  _`ResourceType` has 16 INFERRED edges - model-reasoned connections that need verification._
- **Are the 6 inferred relationships involving `IcsRequest` (e.g. with `.index()` and `.test_a_reduced_approval_needs_a_reason_for_that_line()`) actually correct?**
  _`IcsRequest` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `ChangePasswordValues`, `fields`, `ChangePasswordModalProps` to the rest of the system?**
  _301 weakly-connected nodes found - possible documentation gaps or missing edges._