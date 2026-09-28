# DisasterResource+ — System Specification (from Capstone Chapters I–III)

Source: the "Copy of Revised of Chapter 1 to 3" tab of *Capstone 1 Chapter1-3* (Google Doc), as revised on 2026-09-29.
This spec describes what the paper commits the system to build. It is written from the paper alone and **ignores the existing codebase**: build to this, not to what is there now.

Each item is tagged:
- **[Paper]**: stated in the paper; the system must do this, because the defense and the evaluation depend on it.
- **[Proposed]**: the paper implies it but leaves the detail open. It's a sensible default that can change.
- **[Open]**: the paper is silent or inconsistent. Settle it before building that part.

---

## 1. What the system is

DisasterResource+ is a web-based **supply-readiness** system for **CDRRMO San Pablo City**. Its job is to keep relief and response supplies counted, stocked and accounted for before a disaster response begins. **[Paper]**

It makes three contributions, and each one fills a gap from Chapter II:

| # | Contribution | Gap it fills |
|---|---|---|
| 1 | **Budget-limited replenishment**: each item's target comes from its own past quarters (the per-item multiplier). The full form plans against past quarters as scenarios, within the budget and depot space. | No deployed city-level tool plans quarterly orders from the office's own history under its budget. |
| 2 | **Explained recommendations**: a reason for every item, a budget curve, and a "next ₱10,000 goes to" view. | No system reviewed explains its recommendations. |
| 3 | **Offline-safe, tamper-evident stock records**: append-only stock entries, device-made IDs, ordered offline sync, conflict flags, a SHA-256 hash chain, and a "check records" command. | No relief system documents offline merging without loss or double counting, or tamper-evident records. |

It also has two supporting modules: an **ICS 213-RR request module** with a **borrowed-equipment lifecycle**, and a **dashboard** with a quarterly report export. **[Paper]**

### 1.1 In scope [Paper]
- Only resources that CDRRMO itself holds: medical, cleaning, office and rescue supplies, plus borrowed equipment, across the office's depots and storage areas.
- Requests from within CDRRMO's own divisions and teams.
- A quarterly replenishment cycle.
- A server on-premises at the office. Clients are Chrome or Edge on Windows laptops and Chrome on Android phones, installed as a PWA.
- Historical data from Q3 2023 to Q2 2026 (up to 12 quarters, at least 8 needed), encoded from ICS 213-RR forms, the logbook and Excel sheets.

### 1.2 Out of scope [Paper]
- Live incident operations, and the Incident Command System (ICS) command structure. The ICS 213-RR *form* is used; ICS *roles* are not modeled.
- Stock held by CSWDO, CHO and GSO (cross-office visibility is future work).
- Requests started by barangays.
- Actual purchasing. The system **recommends** only; purchases go through GSO bidding under RA 12009.
- Supplier lead time and item expiry inside the model. Orders are assumed to arrive before the quarter starts.
- Stock changes made outside the system (the system cannot see them).

> Removed from the paper: disaster-feed ingestion (USGS/GDACS), incident logging, dispatch, OSRM/turn-by-turn directions, zone-level allocation, and NDRRMC sitreps. None of these are part of this system.

---

## 2. Users and roles [Paper]

Role-based access follows the **office's own six functional roles** (not ICS roles):

| Role | What the paper has them do |
|---|---|
| **Head of Office (Admin)** | Department head / assistant department head. **System administrator**, and the **only role that gives final approval** of a recommendation, which then becomes the purchase plan. Accountable for COA audits. |
| **Logistics** (under Admin and Training) | Keeps stock; enters the quarterly budget; confirms or corrects flagged data; runs the solver, reviews the recommendation and **endorses it to the Head** (or sends it back); approves and releases requests; resolves conflict flags; product owner. |
| **Research and Planning** | Reads recommendations and the budget curve for annual planning; takes the explanation task. |
| **ERT** | Files most ICS 213-RR requests; borrows equipment. |
| **Operations** | Files requests; borrows equipment. |
| **Incident Management** | Files requests; views the dashboard. |
| **Admin and Training** | Keeps the item catalog and depot list; trains users. |

**Decided (2026-09-29): the Head of Office approves, as the admin.** This follows Chapter III's interview requirement: "approval by the head of office before any allocation plan takes effect."
- Logistics **reviews and endorses**; the Head **approves or rejects**. A rejection goes back to Logistics with a comment.
- The paper still says "Logistics approves or rejects" in the Conceptual Framework (Process step 4), Model Deployment and System Architecture. Update those sentences to "Logistics reviews it and the Head of Office approves or rejects it" so the paper matches the system.

### 2.1 Permission matrix [Proposed: default rules, confirm with the office]

| Capability | Head (Admin) | Logistics | R&P | ERT | Ops | Incident Mgmt | Admin&Training |
|---|---|---|---|---|---|---|---|
| View dashboard (stock, use, recommendation, budget curve) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| File an ICS 213-RR request | | ✓ | | ✓ | ✓ | ✓ | |
| Approve, release or reject a request | ✓ | ✓ | | | | | |
| Lend and receive borrowed items; change borrowed-item status | ✓ | ✓ | | | | | |
| See borrowers' names and contact details | ✓ | ✓ | | | | | |
| Record stock receipts and adjustments | ✓ | ✓ | | | | | |
| Enter the quarterly budget; run the solver | | ✓ | | | | | |
| Endorse a recommendation (or send it back) | | ✓ | | | | | |
| **Approve or reject a recommendation (final)** | **✓** | | | | | | |
| Resolve conflict flags and data-check flags | | ✓ | | | | | |
| Confirm the VED policy (classes, weights, coverage) | ✓ | ✓ | | | | | |
| Export the quarterly report | ✓ | ✓ | ✓ | | | | |
| Run "check records" | ✓ | ✓ | | | | | |
| Manage the item catalog and depots | ✓ | | | | | | ✓ |
| Manage users and assign roles | ✓ | | | | | | |

These defaults follow three rules:
1. **Whoever files can't approve their own work.** Requesters (ERT, Ops, Incident Mgmt) file; Logistics or the Head approves. Logistics endorses a recommendation; only the Head approves it.
2. **Personal data is limited to the roles that lend equipment.** Only the Head and Logistics see borrower names and contacts (NPC safeguards).
3. **Stock changes only through the stock keepers.** Only Logistics and the Head create stock entries directly; everyone else changes stock only through requests and borrowing.

A user can hold more than one role. For example, a Logistics staff member who also files requests gets both roles, but still can't approve their own request.

---

## 3. Domain model

### 3.1 Core entities [Paper, with fields from Table 3 and the text]

**Depot**: a storage area. `id` (e.g., `D-002`), `name`, `free_space` (m³, when space is modeled).

**ResourceType (item)**: `id` (e.g., `RT-004`), `name`, `standard_unit` (one unit per item: piece, box or set; the item level is agreed with Logistics), `unit_cost` (latest purchase price per standard unit), `ved_class` (`V` | `E` | `D`), `space_per_unit` (m³, optional), `kind` (`consumable` | `borrowable`; the office handles consumable and borrowed items separately). For borrowable equipment, track each unit individually (serial or property number). **[Proposed]**

**StockEntry**: the **only** way stock changes. Append-only.
- `device_id`: made on the device (UUID) and **unique** (unique index), so duplicates are rejected.
- `resource_type_id`, `depot_id`
- `quantity`: signed; positive adds, negative removes.
- `reason`: e.g., receipt, release, return, adjustment, count-correction. **[Proposed list]**
- `user_id`, `device` (source device), `created_at_device` (time on the device), `received_at` (server time)
- `priority` (bool or level), `depends_on_device_id` (nullable; e.g., a return depends on its borrow)
- `request_line_id` / `borrow_id` (nullable links)
- `event_tag` (nullable, e.g., "Kristine")
- `hash`, `prev_hash`: see §5.2
- `conflict_flag` (nullable reason), `conflict_resolved_by`, `conflict_resolved_at`
- **Current stock = the sum of an item's entries** (per depot). Stock is never stored as an overwritable total.

**Reservation**: stock held for an approved request so that it can't be promised twice. Made when a request is approved, removed when the request is fulfilled or cancelled. **Available stock = on hand − reserved.** Approving a *recommendation* does **not** change reservations. **[Paper]**

**ResourceRequest (ICS 213-RR)**: header fields are `requesting_division/team`, `requested_by` (user), `date`, `purpose` (`team_kit` | `relief` | `office_use`), `event_tag`, `status`. Each line has `resource_type_id`, `quantity_requested`, `quantity_released`. **Requested and released quantities are both recorded.** **[Paper]**

**Borrow (borrowed-equipment logbook)**: `item unit`, `borrower_division`, `borrower_name`, `borrower_contact` (personal data, access restricted by role), `issued_at`, `status`, and a history of status changes. **[Paper]**

**QuarterHistory** (derived or encoded): per item and quarter, `year`, `quarter`, `quantity_requested`, `quantity_released`, `quantity_available_at_close`, `event_tag`, and `stockout_flag` (quarters where recorded use understates need). **[Paper]**

**QuarterBudget**: `year`, `quarter`, `budget_amount` (₱), and who entered it. **[Paper]**

**OptimizationRun**: inputs snapshot, `rule` (`per_item_multiplier` | `scenario`), per-item `order`, `reason`, `target`, `shortfall`; `budget_curve` (11 points); `next_10k` diff; solver status and time; `status`; `endorsed_by`/`endorsed_at` (Logistics); `approved_by`/`decided_at` (Head); `decision_comment`. An approved run becomes the quarter's **purchase plan**. **[Paper]**
- Status flow: `queued → completed → endorsed (Logistics) → approved | rejected (Head)`, plus `failed` (solver error or infeasible) and `superseded` (a newer run for the same quarter was endorsed).
- Only **one approved run per quarter**. A rejected run can't be edited; Logistics re-runs it with a changed budget or data.
- Approving a run creates the purchase plan only. It doesn't touch stock or reservations; stock changes when the items are **received** (receipt entries).

**DataCheckFlag**: the rule hit, the entry or quarter involved, and the resolution (confirmed | corrected), with who resolved it and when. **[Paper]**

**VedPolicy**: per class, the coverage level `k of M` and the priority weight, plus **who confirmed it and when** (the paper requires the date and the position of the person who confirmed it). **[Paper]**

### 3.2 Request statuses [Proposed; the paper says "every request status" without listing them]

```
draft ──submit──▶ submitted ──approve──▶ approved ──release──▶ partially_released ──release──▶ released ──▶ closed
                      │                     │                       │
                      └──reject──▶ rejected  └──cancel──▶ cancelled  └──close──▶ closed
```

| Status | Who moves it here | Stock effect |
|---|---|---|
| `draft` | requester | none (can be edited or deleted) |
| `submitted` | requester | none; locked for editing |
| `approved` | Logistics / Head | **reserves** the approved quantity per line (it can be lowered from the requested quantity) |
| `partially_released` | Logistics | writes **release entries** for what was handed over; that much is released from the reservation, and the rest stays reserved |
| `released` | Logistics | every approved quantity has been handed over; the reservation is fully used |
| `closed` | Logistics (or automatic when released) | any leftover reservation is **freed** |
| `rejected` | Logistics / Head, from `submitted` (reason required) | none |
| `cancelled` | requester (before approval) or Logistics / Head (before any release) | frees the reservation |

Rules:
- Each line keeps `quantity_requested` (never changed after submit), `quantity_approved`, and `quantity_released` (the running total of releases). This way the paper's "requested and released" are both kept. The gap between requested and released is a recorded shortfall, which later feeds the history (it reduces the "understated need" limitation).
- **Partial release is allowed.** Approval conflicts when the approved quantity exceeds *available* stock (on hand − reserved) or when two approvals together exceed stock. In that case, flag it; don't block it silently.
- A request line for a **borrowable** item creates a **Borrow** at release time instead of a consumable release (§3.3).
- Every status change is logged with the user and time.

### 3.3 Borrowed-item lifecycle [Paper]
Statuses are **issued → returned | for repair | condemned (written off) | surrendered**, and for repair → returned | condemned.
- Under COA Circular No. 2022-004, equipment below ₱50,000 is **semi-expendable property**. Its issue, return and disposal must be documented, which is where the returned, condemned and surrendered statuses come from.
- Each status change writes a stock entry where stock is affected: issue −1, return +1, condemn or surrender with no return.
- **Conflict:** returning an item twice gets flagged, not silently accepted (§5.3).

---

## 4. Replenishment model (solver service) [Paper]

### 4.1 Inputs (IPO "Input")
For each item: (1) past quarterly use, both requested and released; (2) current stock from stock entries **minus reserved**; (3) unit price; (4) the quarterly budget (entered by Logistics); (5) free depot space; (6) VED class, which gives the weight and coverage level.

### 4.2 Pre-solve data checks (they must pass, or be confirmed, before any solve)
Flag an entry, and ask Logistics to **confirm or correct** it, when:
1. A quarter's use is more than **3×** the item's usual quarterly use and no event is tagged.
2. A stock figure is **below zero**.
3. Recorded stock differs from the **weekly physical count**.

Also: all quantities must be non-negative, and every flag must be resolved before the model runs. The checks are rule-based on purpose; the history is too short for anomaly detection.

### 4.3 Parameters
- **M = 8** past quarters when the history allows.
- **Covering level Lᵢ** = the amount that would have covered the item's use in *k* of the last M quarters.
- **Per-item multiplier** = Lᵢ ÷ mᵢ (mᵢ = average quarterly use). Recalculate every quarter.

| VED | Coverage | Covering level used | Weight wᵢ |
|---|---|---|---|
| Vital (medicines, first aid, rescue consumables) | 8 of 8 | highest quarterly use | 3 |
| Essential (hygiene/cleaning, food packs, water) | 7 of 8 | 2nd-highest | 2 |
| Desirable (office/routine) | 6 of 8 | 3rd-highest | 1 |

These values are proposals until Logistics confirms them, so store them as editable policy, not constants.

**Cold start:**
- Fewer than 4 quarters of history: use the **flat 1.5 rule** (target = 1.5 × average) on the quarters available, and mark the item **"limited history"**.
- No recorded use: **no computed order**; list the item for Logistics to set manually.

### 4.4 Stage 1: per-item multiplier (MILP)
Notation: qᵢ = stock at start − reserved; Tᵢ = Lᵢ; cᵢ = cost; vᵢ = space per unit; B = budget; V = free space.

```
minimize   Z = Σᵢ wᵢ · uᵢ / Tᵢ
subject to uᵢ ≥ Tᵢ − (qᵢ + xᵢ)              ∀i
           Σᵢ cᵢ · xᵢ ≤ B
           Σᵢ vᵢ · (qᵢ + xᵢ) ≤ V            (only when space is modeled)
           0 ≤ xᵢ ≤ max(0, Tᵢ − qᵢ),  xᵢ ∈ ℤ,  uᵢ ≥ 0
```

### 4.5 Stage 2: scenario model (sample average approximation, MILP)
Each past quarter s = 1…S is an equally likely scenario, and Dᵢₛ is its use.

```
minimize   Z = (1/S) · Σₛ Σᵢ wᵢ · uᵢₛ / mᵢ
subject to uᵢₛ ≥ Dᵢₛ − (qᵢ + xᵢ)            ∀i, s
           Σᵢ cᵢ · xᵢ ≤ B
           Σᵢ vᵢ · (qᵢ + xᵢ) ≤ V
           xᵢ ≥ 0, xᵢ ∈ ℤ,  uᵢₛ ≥ 0
```
Typhoon quarters stay in as their own scenarios; don't average them away.

**Depot space: supported, but switched on only when the data exists. [Proposed]**
- The paper keeps the space constraint in both stages ("within the budget and depot space"), so build it. But the office hasn't measured space yet, and Table 3 marks `space_per_unit` as "where space is modeled."
- The constraint is **included** for a run only when (a) free space is set for the depot(s) being planned, **and** (b) every item in the run has a `space_per_unit`. Otherwise it's **left out**, and the run records `space_modeled = false`.
- When space isn't modeled, the "limited by depot space" reason can never appear, and the dashboard shows "Depot space not modeled" next to the recommendation.
- Measure free space **per depot**. Stage 1 and Stage 2 both use the total free space across the depots being planned (one V). Planning space depot by depot is future work. For the thesis, start with it **off** until Logistics provides the measurements, and switch it on for the backtest if the data arrives in time.

**Solver:** PuLP **2.8.0** (pinned), with the bundled **CBC**. Set a solve time limit (finiteness). Return an order for every item, or "no feasible order."

### 4.6 Explanation layer (read from the solution, never written by hand)
Every item gets exactly one reason:
- **"fully covered"**: the order brings the item to its target.
- **"partly covered: the budget ran out"**: below target *and* the budget constraint is binding.
- **"limited by depot space"**: below target *and* the space constraint is binding.
- **"not ordered: current stock already covers it"**: stock already meets the target.

The following are also required:
- **Budget curve:** re-solve at **11 levels, 50%–150% of the entered budget in 10% steps**; plot expected shortage against budget. It must **never rise** as the budget rises.
- **"The next ₱10,000 goes to"**: the diff between the solution at B and at B + ₱10,000, listing the items and quantities that change.

### 4.7 Solver service contract [Proposed shape]
A FastAPI service with no DB access. The backend sends everything the solver needs.
- `POST /api/v1/replenishment/solve`: `{rule, budget, free_space, items:[{id, q, reserved, unit_cost, space_per_unit, weight, target|history[], avg_use}], scenarios?: [[D_i_s...]], time_limit_s}` → `{status, orders:[{id, x, shortfall, reason}], objective, binding:{budget:bool, space:bool}, solve_ms}`
- `POST /api/v1/replenishment/budget-curve`: the same payload → 11 `{budget, expected_shortage}` points, plus `next_10k: [{id, delta_x}]`.
- `POST /api/v1/replenishment/backtest`: history → per-quarter metrics for each rule (§4.8).

### 4.8 Backtest (rolling origin) and metrics
For each quarter from the **5th encoded quarter onward**, build the order using **only earlier quarters**, then score it against that quarter's actual use.

Compare four rules:
1. The office's actual orders (where the records show them).
2. The **flat 1.5 rule**, cut by the same percentage for every item to fit the budget.
3. The per-item multiplier.
4. The scenario model.

Report per quarter, **with typhoon quarters shown separately**, and mark stockout quarters, where use understates need.

With Dᵢₜ = actual use, qᵢₜ = start stock, xᵢₜ = order:
- **Fill rate** FRₜ = Σᵢ min(Dᵢₜ, qᵢₜ+xᵢₜ) / Σᵢ Dᵢₜ (higher is better)
- **Weighted shortage** WSₜ = Σᵢ wᵢ · max(0, Dᵢₜ − qᵢₜ − xᵢₜ) / mᵢ (lower is better)
- **Items that ran out**: count where Dᵢₜ > qᵢₜ + xᵢₜ
- **Budget used** = Σᵢ cᵢxᵢₜ / Bₜ
- **Stock left over** = Σᵢ max(0, qᵢₜ + xᵢₜ − Dᵢₜ), valued at unit cost
- **Feasibility**: every run is checked against budget, space and integrality; an infeasible run counts as a failure.
- **Solver time** in ms, as items and scenarios grow.

**Target:** the per-item multiplier and the scenario model **each** give a lower weighted shortage than the flat 1.5 rule in a **majority** of backtested quarters. Report the results as per-quarter differences and a win count.

### 4.9 Worked example (use as a solver test fixture)
Four items, budget **₱200,000**, flat 1.5 targets, equal weights:

| Item | Avg/qtr | Target | Stock | Needed | Unit cost | Cost to target |
|---|---|---|---|---|---|---|
| Family food packs | 320 | 480 | 150 | 330 | ₱500 | ₱165,000 |
| Hygiene kits | 200 | 300 | 60 | 240 | ₱350 | ₱84,000 |
| Bottled water (cases) | 400 | 600 | 250 | 350 | ₱150 | ₱52,500 |
| Medical kits | 85 | 128 | 200 | 0 | — | ₱0 |

The full cost is ₱301,500, leaving ₱101,500 unfunded. Expected results:
- **Least total shortage (Stage 1):** food **127**, hygiene **240**, water **350**; spends ₱200,000. Food is 42% short, the other two 0%.
- **Same-percentage cut:** food **218**, hygiene **159**, water **232**; spends ₱199,450.
- **Even spread** (minimize the largest % shortfall): 218, 170, 210 (23% each).
- **Reasons:** water and hygiene are "fully covered"; medical kits "not ordered: current stock already covers it"; food "partly covered: the budget ran out."
- **Next ₱10,000:** +20 food packs, taking food from 42% to 38% below target.

---

## 5. Stock records: offline, sync, integrity [Paper]

### 5.1 Client (PWA, offline-first)
- Build with **vite-plugin-pwa**: a service worker caches the app and reference data after the first online login.
- Keep an **outbox in IndexedDB (idb)**. Each entry made offline gets a **device-made ID**, a **creation time** and a **priority**.
- Queued entries **survive closing and reopening the browser**.
- **Sync order:** priority entries first, then by creation time. An entry that **depends on** another record syncs after it (e.g., a return after its borrow).
- Sync triggers: Background Sync (Chromium), plus **retry on every app open and on reconnect**, because Background Sync is Chromium-only.
- Safari may delete IndexedDB after 7 days without use unless the app is added to the Home Screen. Tell iPhone users to install it.

### 5.2 Server acceptance pipeline (Figure 4)
For each incoming entry:
1. **Duplicate check:** a unique index on the device-made ID. If the entry was already received, **ignore** it; the upload must be idempotent.
2. **Conflict check:** if the entry would make stock **negative**, or would **return a borrowed item twice**, **keep the entry** and **set a conflict flag** for Logistics. Never drop it.
3. **Hash chain:** `hₙ = SHA-256(hₙ₋₁ ‖ canonical(entry))`, computed with PHP `hash('sha256', …)`. Store it on the row and append it to the chain.
4. Broadcast the update (Reverb) so the dashboard refreshes.

Entries merge by **addition in any order** (a counter CRDT idea), so the order of arrival doesn't change the totals.

### 5.3 Conflicts
The following must raise flags:
- Two approvals that together exceed an item's stock.
- Two devices changing the same item offline.
- A return of an item that isn't currently issued.

Each flag is shown to Logistics to resolve. Every resolution is recorded, and it is recorded as a new entry, not an edit.

### 5.4 "Check records" command
- Recomputes the chain from the **first entry**, and reports the **first entry whose fingerprint no longer matches**. A row edited directly in the DB must be detected.
- **Print the latest fingerprint on each quarterly report.** A rebuilt chain would then no longer match the printed value; this is the paper's mitigation for someone with full DB access.
- Run it as an artisan command, from the UI (for permitted roles), and on a schedule. **[Proposed: schedule]**

---

## 6. Request module (ICS 213-RR) [Paper]
- Digitize the office's **ICS 213-RR resource request form**, with requested **and** released quantities per line, the requesting division or team, and purpose and event tag. Use only the form's fields; don't model ICS roles.
- **Approval reserves stock; fulfillment deducts it** (writes stock entries). Released quantities may be lower than requested, and both are kept.
- Keep **consumables and borrowed items separate**. Borrowed items follow §3.3.
- **Privacy:** borrower names and contacts are personal data. Restrict them by role (the NPC Circular 2023-06 safeguards) and never send them to the solver. Historical encoding leaves out names and signatures.

---

## 7. Dashboard and reports [Paper]
Built on the three situation-awareness levels:
1. **Perceive**: current stock and use per item and depot (Recharts).
2. **Understand**: the quarterly recommendation, with **a reason for each item**.
3. **Project**: the **budget curve** and the **"next ₱10,000 goes to"** view.

Also required:
- **Endorse** (Logistics) and **approve or reject** (Head of Office) the recommendation; an approved run becomes the purchase plan. The Head sees a queue of endorsed runs waiting for a decision.
- Items marked **"limited history"** or "set manually," and any open data-check or conflict flags.
- **Live updates:** the dashboard refreshes when a run completes or entries arrive (Laravel Reverb), and as soon as a device is back online. Target: under 1 s on the office network.
- **Export:** a quarterly **burn-rate and reorder report** in **PDF and CSV**, with the **latest hash-chain fingerprint printed** on it.
- Figures shown must **match the database** (an integration test).

---

## 8. Architecture and stack [Paper]
- **Client:** PWA built with Inertia + React (TypeScript); vite-plugin-pwa; idb outbox; Recharts.
- **Backend:** Laravel 13, handling web + API (Sanctum), RBAC, requests and approvals, stock entries, the hash chain, the "check records" command, queued solver jobs, the queue worker and the scheduler.
- **Database:** PostgreSQL, with a unique index on the device-made ID, and the stock-entry table plus its hash chain.
- **Solver:** a FastAPI service with PuLP 2.8.0 and CBC. It **never touches the database**; the backend calls it through a **queued job** whenever Logistics submits or updates the quarter's budget.
- **Realtime:** Laravel Reverb (self-hosted WebSockets).
- **Deploy:** Docker Compose on the office server (web, queue worker, scheduler, Reverb, solver, PostgreSQL), on a UPS (≥1,500 VA).
- **Tests:** PHPUnit (backend) and pytest (solver).

### Typical quarterly cycle
1. Logistics enters the quarter's budget.
2. The backend runs the data checks, and Logistics resolves any flags.
3. A queued job sends each item's history, stock (less reserved), price, space and weight to the solver.
4. The solver returns orders, reasons, the budget curve and the next-₱10k diff.
5. The backend stores the run, and Reverb pushes it to the dashboard.
6. Logistics reviews and endorses the recommendation, and the **Head of Office approves or rejects** it; an approved run becomes the purchase plan.
7. When the items arrive, they are recorded as stock entries. That quarter's entries feed back as new history, so the multipliers and scenarios update.

### Hardware (Table 9)
- **Office server:** 4 cores, 16 GB RAM, 256 GB SSD, 64-bit OS with Docker, wired to the office network.
- **Development machine:** i5 8th gen or Ryzen 5; 8 GB RAM (16 GB recommended); 15 GB free disk.

---

## 9. Acceptance criteria (what the evaluation will test) [Paper]

| Area | Pass condition |
|---|---|
| Worked example | Stage 1 gives **127 / 240 / 350**; the same-percentage cut gives **218 / 159 / 232**. |
| Solver unit tests | The multiplier is correct from a known history; every reason matches its constraint status; the budget curve never rises; the budget is **never exceeded**; money is left over **only** when every target is met; edge cases pass (zero budget, no history, <4 quarters). |
| Integration | Budget submit → queued job → solver → stored run → dashboard update; every request status and borrowed-item status; reserve on approve and deduct on fulfill; RBAC for each role; borrower data protected; dashboard figures match the DB. |
| Offline resilience | Two Windows laptops and two Android phones, airplane mode, **30 scripted entries** (5 priority, 3 two-device conflicts), browser closed and reopened, then reconnect. **100% saved, 0 duplicates, 0 losses, priority first, every conflict flagged.** Run 3 times; Safari on iPhone tested separately. |
| Integrity | The same batch uploaded 3× gives 0 duplicates; an offline return syncs after its offline borrow; two approvals exceeding stock raise a flag; a directly edited DB row makes "check records" **fail at that row**. |
| Performance | A Stage 2 solve in **< 2 s** for 200 items and 12 scenarios; an 11-point budget curve in **< 20 s**; run to dashboard in **< 1 s**. |
| Explanation task (UAT) | Logistics and R&P answer **≥ 80%** correctly: why an item was cut, what ₱20,000 more would change, and which limit mattered. |
| UAT tasks | File an ICS 213-RR request; lend and return an item; record offline and sync; review and endorse a recommendation (Logistics) and approve it (Head); export the quarterly report. |
| Backtest | The multiplier and the scenario model each beat flat 1.5 on weighted shortage in a majority of quarters. |
| ISO/IEC 25010:2023 survey | Functional suitability, reliability, interaction capability and safety (fail safe) each reach **Very Acceptable** (weighted mean 4.21–5.00), with 15–20 respondents across the six roles. |

---

## 10. Build order (Sprint plan, Table 8; 2-week sprints) [Paper]
1. **Per-item multiplier and data encoding:** the encoding template and encoded history; data checks; the multiplier; the Stage 1 model.
2. **Explanations:** reasons; the budget curve; the "next ₱10,000" view.
3. **Stock records:** append-only entries; the hash chain and "check records"; conflict flags; ordered outbox sync.
4. **Scenario model:** Stage 2 in the solver; the rolling-origin backtest.
5. **Requests and dashboard integration:** ICS 213-RR with requested and released quantities; the borrowed-item lifecycle; the quarterly report export.
6. **Testing and evaluation:** unit, integration, offline, performance and UAT tests; the survey.

**Historical encoding template** (one row per request line):
`date, item, standard_unit, quantity_requested, quantity_released, purpose (team kit | relief | office use), event_tag, requesting_division/team`
- A second encoder checks each quarter against the stock sheet.
- Released quantities missing from the form come from Logistics' remarks or the stock sheet.
- Names and signatures are not encoded.
- Mixed units are converted to each item's standard unit.

---

## 11. Open questions to settle with CDRRMO before building
1. ~~Who gives final approval~~. **Decided:** the Head of Office, as admin (§2). Still to do: update the three paper sentences that say "Logistics approves or rejects."
2. **Confirm the permission matrix** (§2.1). It has default rules, so development can start with it; ask the office only about the rows they disagree with.
3. **Confirmation of the VED classes, weights and coverage levels**, recorded with the date and the confirming person's position.
4. **Depot space measurements.** It's built as an optional constraint that stays off until the data exists (§4.5). Ask Logistics for each depot's free space and each item's space per unit.
5. **Confirm the request statuses** (§3.2): partial release is allowed by default, and Logistics can approve less than was requested.
6. **The item level**: for example, individual medical supplies versus kits.
7. **The weekly physical count**: who records it and in what form, since data check 3 depends on it.
8. **The follow-up interview with Research and Planning** on how procurement figures are requested and used.
