# DisasterResource+ — Easy Study Guide for the Defense

Source: the latest manuscript, `panel-review/2026-11-08/Capstone 1 Chapter1-3 (panel revision, accepted).docx` (130 pages, November 2026). Page numbers below are the paper's printed page numbers.

---

## 1. The whole study in one minute (memorize this)

> CDRRMO San Pablo City buys its disaster supplies every quarter, but it plans by hand: it looks at the stock left over and adds "a little extra," with no written rule. This can leave too little for a typhoon quarter, hides which items were cut when the budget is short, and lets offline or paper counts get lost, doubled, or edited without a trace.
>
> DisasterResource+ fixes three things:
> 1. **Plans the order** from each item's own past quarters, within the budget and depot space.
> 2. **Explains the order**: a reason for every item, and a budget curve showing what more money would buy.
> 3. **Keeps stock records safe**: they work offline, never count a change twice, and show any later edit.
>
> It also digitizes the ICS 213-RR request form and the borrowed-equipment logbook. I test it with a backtest on past quarters, software tests, an explanation task, and an ISO/IEC 25010 survey.

**The key idea:** the study does not invent one new technique. Its contribution is the **combination** of three things no reviewed system documents together: budget-limited planning from the office's own quarters, explained recommendations, and offline-safe, tamper-evident stock records (Synthesis, p. 39–40).

---

## 2. Chapter I — the problem and the plan

### The story (p. 1–3)
- About 19–20 typhoons enter the Philippines each year, and about 9 make landfall.
- RA 10121 requires each LGU to set aside **at least 5%** of its revenue as a DRRM fund. **30%** of that fund is the Quick Response Fund. Part of the fund buys the office's supplies.
- DSWD has tools (75% refill rule, PAHR forecasts), but those serve the **national** stockpile, not a city office.
- Kristine + Leon (October 2024) hit **46 of 80 barangays**, 811 families, and 3,330 persons in San Pablo.
- The **September 3, 2026 interview** showed how the office works now:
  - requests go on the ICS 213-RR form;
  - borrowed items go in a logbook;
  - stock lives in Excel plus paper;
  - the office reviews stock each quarter, with no written formula;
  - the GSO runs the bidding.
- Logistics said **counting errors** are its biggest problem. It said the system must survive **internet and power outages**.

### Three weaknesses → three fixes
| Weakness now | Fix in DisasterResource+ |
|---|---|
| Order sized for an ordinary quarter, too little for a typhoon quarter | Plan from each item's own history, typhoon quarters included |
| When the budget is short, nobody can see what was cut or why | A reason for every item, plus a budget curve |
| Offline or paper counts can be lost, doubled, or silently edited | Append-only stock entries, ordered offline sync, hash chain |

**"Disaster response" in the title** means *readiness to respond*. The study covers preparedness, the work done **before** a disaster. Live response work, such as dispatch and incident command, is **out of scope** (p. 3).

### The 5 research questions in plain words (p. 4–5)
| # | Plain question | How it is answered | Pass mark |
|---|---|---|---|
| 1 | How does the office work now, and how often did "released" fall short of "requested"? | Interview + encoded records | Descriptive counts |
| 2 | Does my model give less shortage than the other 4 rules on past quarters? | Rolling-origin backtest | Wins in **more than half** of the backtested quarters |
| 3 | Do the stock records and request module work correctly? | Unit, integration, offline tests | 0 duplicates, 0 losses, priority first, every edit detected, all cases pass |
| 4 | Can staff understand the reasons and the budget curve? | Explanation task | **At least 80%** correct answers |
| 5 | Do the users accept the system? | ISO/IEC 25010:2023 survey | Weighted mean **4.21–5.00** ("Very Acceptable") |

The **5 rules compared** in RQ2:
1. the office's actual orders (the main comparison);
2. the flat 1.5 rule;
3. a classical safety-stock rule (average + 1.65 SD, 95% service level);
4. the per-item multiplier;
5. the scenario model.

The **5 ISO characteristics** in RQ5: functional suitability, reliability, interaction capability, security, and safety.

### Theoretical framework: three theories → three parts (p. 8–10)
| Theory | Simple meaning | Used for |
|---|---|---|
| **Optimization under uncertainty** (LP, stochastic programming, sample average approximation) | Pick the best order while the future is unknown. Treat each past quarter as one possible next quarter. | The replenishment model |
| **Explainable decision support** (De Bock 2024; situation awareness: perceive → understand → project) | People must understand *why* before they trust a model | Reasons + dashboard + budget curve |
| **Local-first, CRDT counter, tamper-evident log** | Work offline, merge by adding and subtracting, and chain records with hashes | Offline outbox, sync, "check records" |

Situation awareness on the dashboard: **current stock and use** = perceive; **reason per item** = understand; **budget curve** = project.

### Conceptual framework: IPO (p. 11–12)
- **Input (7 items):**
  1. past quarterly use (requested and released);
  2. current stock;
  3. unit prices;
  4. budget (entered by the Head of Office);
  5. depot space and space per unit;
  6. VED class;
  7. coverage level and weight.
- **Process:** the system is built with Scrum. Each quarter's run then follows 4 steps: data checks → solver → reasons + budget curve → Head of Office approves or rejects.
- **Output:** the system itself, with explained orders, an approved purchase plan, tamper-evident records, and the evaluation results.

### Scope and limitations (p. 13–17)
- **Five parts of the system:**
  1. the model;
  2. the explanations;
  3. the stock records;
  4. the request module (ICS 213-RR + borrowed items);
  5. the dashboard (plus roles and the PDF/CSV report).
- **Data:** Q3 2023 – Q2 2026, up to 12 quarters. The Kristine/Leon quarter is included.
- **Out of scope:**
  - other offices' stock (CSWDO, CHO, GSO);
  - barangay requests;
  - the ICS command structure;
  - live response.
- **Limitations to say honestly:**
  - The system **recommends; it does not buy**. The Head of Office approves, and the GSO runs the bidding under RA 12009.
  - The study does not measure whether preparedness outcomes improve.
  - 8–12 quarters show a **direction, not statistical proof**.
  - The model has no lead time or expiry, and depot space is one pool.
  - An item with fewer than 4 quarters of history uses the flat 1.5 rule and is marked "limited history".
  - Background Sync works only in Chromium browsers, and the app needs HTTPS.
  - There is no off-grid (radio) sync.
  - Someone with full database access could rebuild the hash chain. That is why the latest fingerprint is **printed on each quarterly report**.
  - Until the office releases its records, testing uses a **sample dataset built from public sources**. Never call it "public data" or "the office's data".

---

## 3. Chapter II — what others did, and the gap

| Topic | What the literature says | What my study does differently |
|---|---|---|
| Relief inventory planning | Many models exist, but mostly for location and routing (Uichanco 2022; De Veluz 2023), and they are rarely used in the field (Rodríguez-Espíndola 2023: only 10% involved practitioners) | Decides only *how many units to buy*, using the office's own quarters as scenarios |
| VED analysis | Used to rank medical stores (Meena & Mathaiyan 2024; Gizaw & Jemal 2021) | Uses VED as **weights and coverage levels** inside a budget model, which no reviewed study does |
| Explaining optimization | A research agenda (De Bock 2024). User studies did not test government staff | Reasons are read from the solution. The explanation task tests them with the office's own staff |
| Dashboards | Good dashboards know their audience and link data to decisions (Ivanković 2021) | The dashboard explains a decision, not just the situation |
| Offline-first / CRDT | Save +/− entries, not totals. Example: 100 units, two devices release 10 and 5 → entries −10 and −5 give **85** in any order. Saving totals (90, 95) would lose one | Append-only entries, device-made IDs, conflicts kept and flagged |
| Tamper evidence | SQL Ledger hash chain (Antonopoulos 2021). Blockchain helps when *many* organizations share data | One office holds the data, so a hash chain is enough, without blockchain |
| Laws | RA 10121, RA 12287 (imminent disaster), RA 12009 (procurement), COA Circular 2022-004 (semi-expendable items **below ₱50,000**), Data Privacy Act 2012 | The borrowed-item statuses follow COA rules, and borrower data is limited by role |
| Related systems | DSWD tools, PAHR, PIMS, IRespondPH, E-LIGTAS, **Calamba CDRRMO system (Yaneza 2025, LSPU)**, Sablayan | Those support live response or reporting. Mine supports **supply readiness** |
| Forecasting with short records | ML needs long records (Cerqueira 2022). Deciding directly from past data works with few observations (Besbes & Mouchtaki 2023). Use rolling-origin evaluation (Hewamalage 2023) | No ML forecasting. Plan from past quarters, then backtest them in time order |
| Record accuracy | Ethiopia: about 70–75% accuracy. COA 2021: the OCD's ₱552M inventory "could not be relied upon" | Every change is an entry that is checked |

**Table 2 (feature comparison):** Yes = the sources document the feature; No = the sources describe the system and the feature is not there; ND = not documented. Say clearly: **"No and ND do not prove a system lacks the feature; they show only what the published sources cover."**

**The gaps (Table 3):**
1. replenishment planning;
2. explanations;
3. offline stock records;
4. record integrity;
5. inventory accountability (borrowed items);
6. decision visibility (Research and Planning gets figures only yearly).

---

## 4. Chapter III — how the study is done

### Design (p. 43)
**Developmental design with descriptive-evaluative and comparative parts.** It is based on design science research: build an artifact, then evaluate it.
- Developmental part = building the system.
- Comparative part = the backtest.
- Descriptive-evaluative part = current practice + the acceptability survey.

### Locale and people (p. 43–46)
- **Locale:** CDRRMO San Pablo City. It has a Head of Office, an assistant head, and 4 divisions: Research and Planning; Operations and Warning; Administration and Training (Logistics is a section here); and Administrative. The city has 80 barangays.
- **Key informants: 3** (the head of Administration and Training + 2 Logistics staff), chosen by purposive sampling. The division head was the highest-ranking official available during the office's restructuring.
- **Users: at least 20** (an estimate, to be confirmed). Total enumeration.
- **Offline test:** 2 laptops + 2 phones, run by Logistics.
- **Explanation task:** Head of Office + Logistics + Research and Planning.
- **Stakeholders, users, and respondents are different:** stakeholders are the *groups affected*; users *hold accounts*; respondents *answer the instruments*.

### Requirements (Table 6)
- **10 functional requirements:**
  - stock entries;
  - auto-deduct on release;
  - digital ICS 213-RR;
  - borrowed-item statuses;
  - consumable vs borrowable items;
  - Head of Office approval;
  - recommendations with reasons;
  - data checks;
  - PDF/CSV report;
  - access by division.
- **6 non-functional requirements:**
  - works offline with no loss and no duplicates;
  - edits are detectable;
  - runs on Chrome/Edge over HTTPS;
  - privacy;
  - fast solve;
  - plain-language explanations.

### The model — explain it like this

**Step 0: data checks (3 rules).** The run is blocked until every flag is resolved.
1. A quarter's use is more than **3× the item's median** with no event tagged.
2. A stock figure is **below zero**.
3. Recorded stock **differs from the weekly physical count**.

**Stage 1: per-item multiplier** (simple form)
- Each item gets a **target** = the amount that would have covered its use in **k of its last 8 quarters**:
  - **Vital** (medicines, first aid): all 8 of 8, weight **3**;
  - **Essential** (hygiene, food, water): 7 of 8, weight **2**;
  - **Desirable** (office supplies): 6 of 8, weight **1**.
- Then the solver picks whole-number orders that **minimize the weighted shortfall below the targets**, without going over the budget or the space.
- "Multiplier" = target ÷ average use. Every item gets its own multiplier, not one 1.5 for all.

**Stage 2: scenario model** (full form, sample average approximation)
- It sets **no single target**. It pretends the next quarter could be **any of the past quarters**, each equally likely, and picks orders that make the **average weighted shortage across all of them** as small as possible.
- A typhoon quarter stays **its own scenario** instead of being averaged away.

**Both stages:**
- are mixed-integer linear programs (orders are whole units);
- are solved with **PuLP + CBC** in a separate **FastAPI solver service**;
- stop within 0.01% of the optimum or at a time limit;
- assume orders arrive before the quarter starts.

### Worked example (memorize these numbers, p. 63–66)
4 items, a **₱200,000** budget, flat 1.5 targets:

| Item | Target | Stock | Unit cost | Cost to reach target |
|---|---|---|---|---|
| Family food packs | 480 | 150 | ₱500 | ₱165,000 |
| Hygiene kits | 300 | 60 | ₱350 | ₱84,000 |
| Bottled water (cases) | 600 | 250 | ₱150 | ₱52,500 |
| Medical kits | 128 | 200 | — | ₱0 |
| **Total** | | | | **₱301,500**, so **₱101,500 unfunded** |

| Method | Food | Hygiene | Water |
|---|---|---|---|
| Least total shortage (my Stage 1) | **127** (42% short) | **240** (0%) | **350** (0%) |
| Even spread | 218 (23%) | 170 (23%) | 210 (23%) |
| Same % cut (how offices often do it) | 218 (23%) | 159 (27%) | 232 (20%) |

- Least total shortage funds the items that close the **most shortage per peso** first.
- That is why food is left short, and why the **VED weights** are the office's control. With weight 3, food would be funded first.
- The reasons it gives:
  - water and hygiene: "fully covered";
  - medical: "not ordered: current stock already covers it";
  - food: "partly covered: the budget ran out".
- The next **₱10,000 buys 20 food packs** (food goes from 42% to 38% short).

**Table 12 (an honest point the panel may ask about):**
- The multiplier beats the flat rule only at tight budgets (₱150k–200k).
- From ₱250k up, the flat rule wins, because the multiplier's 7-of-8 target **leaves out the typhoon quarter by design**.
- The **scenario model is best or tied at every budget**. That is why there are two stages.
- These values are illustrative and in-sample. The real evidence will be the backtest on the office's records.

### Explanation layer (p. 60–61)
Every item gets **one reason, read from the solution** (not written by hand):
- fully covered;
- not ordered: stock already covers it;
- partly covered: the budget ran out;
- limited by depot space;
- partly covered: the solver stopped early;
- no computed order: set manually;
- no feasible order.

**Budget curve:** the system re-solves the model at **11 levels (50%–150%, in 10% steps)**. The "**next ₱10,000** goes to" view shows which items and quantities would change.

**Consistency checks:**
- "budget ran out" may appear only if the money left is less than the price of one more unit;
- the curve must **never rise** as the budget rises.

### Backtest (rolling-origin, p. 61–63)
- For each quarter from the **5th** onward: stand at the start of that quarter, plan using **only the earlier quarters**, then compare with the real use.
- The 5 rules are scored with Table 7's measures:

| Measure | Role | Better when |
|---|---|---|
| **Weighted shortage** | Primary | Lower |
| Fill rate | Supplementary | Higher |
| Items that ran out | Supplementary | Lower |
| Budget used | Supplementary | Cannot exceed 1 |
| Stock left over | Supplementary | Lower (less money tied up) |
| Feasibility | Check | Must pass |
| Solver time | Check | Within the pass marks |

- **Win rule:** the rule with the lower weighted shortage wins the quarter. A model form is "better" if it wins **more than half** of the comparable quarters.
- **Mixed results are reported as mixed**, not averaged into one verdict.
- Typhoon quarters are reported **one by one**.
- A repeat with **equal weights** checks the effect of the VED weights.
- 8–12 quarters give only **4–8 backtested quarters**, so the result shows a direction, not proof.
- Quarters where stock ran out understate the real need. They are marked, and every measure is computed with and without them.

### Offline records and hash chain (how to explain it simply)
- **Every stock change is an entry** (+ or −). Current stock = the sum of the entries. Entries are never edited or deleted.
- Offline, the entry waits in the device's **IndexedDB outbox** with a **device-made ID**.
- On reconnect, it syncs: **priority entries first**, dependent entries after the entry they depend on.
- Same ID uploaded twice → the **unique index** ignores the duplicate.
- A conflict (stock would go below zero, a return of a unit that is not out) is **kept and flagged** for Logistics, not refused, because the goods already moved.
- **Hash chain:** each entry's SHA-256 fingerprint = hash(its contents + the previous fingerprint). Change one old entry → every later fingerprint breaks → "Check records" (run nightly) catches it.

### Development and testing
- **Scrum**, 2-week sprints:
  - researcher = development team + Scrum Master;
  - head of Logistics = Product Owner;
  - each sprint ends with a review of the working increment.
- **Tools:**
  - Laravel 13 / PHP 8.4;
  - Inertia + React 18 + TypeScript;
  - PostgreSQL 16;
  - vite-plugin-pwa + IndexedDB;
  - Recharts;
  - SHA-256;
  - FastAPI + PuLP + CBC;
  - Reverb;
  - Docker.
- **Server:** at the office, 4-core, 16 GB RAM, 256 GB SSD, with a **1,500 VA UPS**.
- **Testing:**
  - unit;
  - integration;
  - offline resilience and integrity (**3 runs**: 100% saved, 0 duplicates, 0 losses, priority first);
  - performance;
  - user acceptance (tasks + explanation task + survey).
- **Explanation task:** 3 recommendations × 3 questions each:
  - why was this item cut?
  - what would the next ₱10,000 buy?
  - which limit mattered?
- **Questionnaire validity:**
  - **3 IT faculty** check face and content validity;
  - a **pilot test** with non-sample personnel;
  - **Cronbach's alpha of at least 0.70**.
- **Statistics:**
  - weighted mean `WM = Σ(f × x) ÷ n` + SD, read with the Likert scale (4.21–5.00 Very Acceptable … 1.00–1.80 Not Acceptable);
  - division results reported descriptively only (3–5 people per division is too few for significance tests).
- **Ethics:**
  - written permission from the Head of Office;
  - informed consent, with the right to withdraw;
  - names and signatures are not encoded;
  - borrower contacts are limited by role;
  - follows the Data Privacy Act 2012;
  - data is returned or deleted after the study.

---

## 5. Likely panel questions — short answers

**Q: What is new in your study? Is your technique new?**
No single technique is new; the **combination** is. Every part already exists:

| Part | Technique | Already known from |
|---|---|---|
| Stage 1 | Per-item target covering k of the last 8 quarters, solved as a MILP within the budget | Hillier & Lieberman (2021) |
| Stage 2 | Sample average approximation over past quarters | Shapiro et al. (2021); Shehadeh & Tucker (2022) |
| Priorities | VED classes | Gizaw & Jemal (2021) |
| Explanations | Reason from the binding limit; budget curve | De Bock et al. (2024) |
| Records | Add-or-subtract entries, hash chain, PWA outbox | Almeida (2025); Antonopoulos et al. (2021); Cuenca-Enrique et al. (2024) |

What is new is one system for a city disaster office that plans each quarter from its own short history within its budget, explains every cut, and keeps records that survive outages and show any edit. None of the **reviewed** systems documents all three together.

Say: *"No single technique is new. The contribution is combining them in one system for a city DRRM office's quarterly supplies, which none of the systems I reviewed documents, and testing it on the office's own records with a backtest."*
Don't say: "I created a new algorithm", "my model is novel", or "no one has done this before".

**Q: Then why is this research and not just software?**
Because it answers questions with evidence: RQ2 tests in a backtest whether planning from the office's own quarters beats its current practice, and RQ4 tests whether staff can use the explanations, each with a criterion.

**Q: Why not use machine learning or forecasting?**
The study covers at most twelve quarters per item, from Q3 2023 to Q2 2026, and some items will have fewer. Machine learning needs much longer records (Cerqueira et al., 2022). (Say "the study covers", not "the office has": the records are not released yet.) Croston-type methods need weekly or monthly data and a safety factor. Deciding directly from past quarters works with few observations (Besbes & Mouchtaki, 2023), and it plans for the bad quarters on record directly.

**Q: Why two stages, and not one? What is the difference?**
| | **Stage 1: per-item multiplier** | **Stage 2: scenario model** |
|---|---|---|
| Idea | One target per item: the amount that would have covered its use in **k of its last 8 quarters** (k set by the VED class) | **No single target**: each past quarter is treated as an equally likely next quarter |
| Minimizes | Weighted shortage below the targets, within budget and depot space | **Average** weighted shortage across all past quarters, within the same limits |
| Typhoon quarter | Can be left out by design (e.g., essential items cover 7 of 8 quarters) | Stays in as one scenario; the model decides how far to protect against it |
| Strength | Simple; one clear target per item to explain | Stronger when the budget is tight and big quarters matter |
| Weakness | Can miss the rare big quarter | Harder to explain; no single target |
| Source | Inventory theory; MILP (Hillier & Lieberman, 2021) | Sample average approximation (Shapiro et al., 2021) |

Neither is guaranteed to be better on the office's records, so **RQ2 tests both** (with the office's orders and the flat 1.5 rule) on quarters they have not seen, and the backtest tells the office which to use. In Table 12 the scenario model was least or equal-least at every budget, but the flat rule beat Stage 1 from ₱250,000 up, because Stage 1 leaves out the typhoon quarter by design.

Say: *"Stage 1 is simple and explainable but can miss the typhoon quarter; Stage 2 keeps it. Neither is guaranteed to win, so RQ2 tests both on unseen quarters, and the backtest tells the office which to use."*
Don't say: "Stage 2 is always better."

**Q: Where did the weights 3, 2, 1 come from?**
They are the study's proposal. Logistics proposes them, and the Head of Office will confirm them in the system before the first run; the system records the position and the date. They are fixed **before** the backtest, and an equal-weights repeat checks their effect.

**Q: Do you already have the office's data?**
Not yet; it is still being requested. For now, the system runs on a **sample dataset built from public sources**: real typhoon quarters and real prices, but no office records. It shows that the procedure works. It is **not** evidence. RQ1 and RQ2 will be answered from the office's own records.

**Q: Is 8 to 12 quarters enough?**
It is enough to show a direction, not statistical proof. I say this in the limitations. Typhoon quarters are reported one by one, and mixed results are reported as mixed.

**Q: Why a hash chain and not blockchain?**
Blockchain helps when many organizations must trust each other. Here, one office holds the data, so a hash chain in its own database already makes edits visible (Antonopoulos et al., 2021).

**Q: Can the admin cheat by rebuilding the chain?**
Someone with full database access could. That is why the latest fingerprint is printed on every quarterly report. A rebuilt chain would no longer match the printed value, and the COA can compare them.

**Q: What if two phones release the same item while offline?**
Each saves a −quantity entry, not a new total, so both count. If the stock would go below zero, the entry is still kept, because the goods already moved, and it is flagged for Logistics to check.

**Q: Why is it called "disaster response" if you only do preparedness?**
"Response" here means *readiness to respond*: supplies counted and stocked before a disaster. Live response operations are outside the scope (Chapter I).

**Q: Does the system buy items?**
No. It recommends. The Head of Office approves, and the GSO runs the bidding under RA 12009.

**Q: Why 80% for the explanation task?**
It is the study's target for "staff can use the explanations". It is pooled across respondents. *(Be honest if pressed: no earlier baseline exists, so it is a set criterion, not one taken from a past study.)*

**Q: Why only at least 20 respondents? How about statistics?**
That is the whole user population (total enumeration). The results are descriptive (weighted mean and SD). Per-division groups are too small for significance tests, and the paper says so.

**Q: Why Scrum with one developer?**
Scrum is commonly adapted (Hron & Obwegeser, 2022), including for student projects. I am the development team and Scrum Master, the head of Logistics is the Product Owner, and each sprint ends with a review of the working part.

**Q: What if the solver fails?**
The run is marked "failed" and the error is shown. Only a **completed** run can be approved, so a failed result can never become a purchase plan.

**Q: What does your study NOT do?**
- lead time and expiry;
- separate depots;
- other offices' stock;
- barangay requests;
- off-grid (radio) sync;
- proof that the office's outcomes improved.

**If you don't know an answer:** "Thank you, Ma'am/Sir. That is a good point that the study has not covered yet. I will note it as a recommendation for the study."

---

## 6. Key numbers cheat sheet

| Number | Meaning |
|---|---|
| 19–20 / 9 | Typhoons entering the Philippines per year / making landfall |
| 5% / 30% | DRRM fund share of LGU revenue / Quick Response Fund share of that |
| 46 of 80 | Barangays hit by Kristine + Leon (Oct 2024); 811 families, 3,330 persons |
| Sept 3, 2026 | Requirements interview; 3 key informants |
| Q3 2023 – Q2 2026 | Study period, up to 12 quarters (at least 8 needed) |
| 8 / 7 / 6 of 8 | Coverage for Vital / Essential / Desirable; weights 3 / 2 / 1 |
| 3× median | Data check 1 (unusual use with no event) |
| 1.5 | The flat rule |
| 1.65 SD | Safety-stock rule (95% service level) |
| 11 levels, 50–150% | Budget curve |
| ₱10,000 | "Next ₱10,000" view |
| ₱200,000 / ₱301,500 / ₱101,500 | Worked example: budget / full cost / unfunded |
| 127 / 240 / 350 | Worked example orders (food / hygiene / water) |
| > half of quarters | Backtest win rule |
| 80% | Explanation task target |
| 4.21–5.00 | "Very Acceptable" |
| 0.70 | Minimum Cronbach's alpha |
| at least 20 | Users / survey respondents (to confirm) |
| ₱50,000 | COA semi-expendable property limit |
| 0.01% | Solver accuracy (MIP gap) |

## 7. Words to say right

| Term | Say it like this (simple) |
|---|---|
| Replenishment | Buying stock for the next quarter |
| Per-item multiplier | Each item's own "extra factor" from its history |
| Sample average approximation (SAA) | Treat past quarters as equally likely futures and pick the best average |
| Scenario | One past quarter used as a possible next quarter |
| Weighted shortage | How short we are, times how important the item is |
| Fill rate | The share of real use that the stock covered |
| Rolling-origin backtest | Test on each past quarter using only the quarters before it |
| Hash chain | Each record's fingerprint includes the previous one's, so edits show |
| Append-only | You can add records, never change or delete them |
| VED | Vital, Essential, Desirable |
| MILP | An optimization problem with whole-number answers |
| CBC | The free solver that finds the best orders |
| PWA | A website that installs like an app and works offline |
