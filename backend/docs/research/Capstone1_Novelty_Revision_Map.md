# Chapters 1–3: What to Change to Use the Novelty Options

Prepared September 27, 2026.

This file assumes you take the recommended core from `Capstone1_Novelty_Options.md`:
- **Option 1:** replenishment that plans from each item's own past quarters. Start with the per-item multiplier and move to the full scenario model.
- **Option 2:** explained recommendations, meaning a reason per item plus a budget curve.
- **Option 3:** stock records that stay correct offline and show later edits. The data checks from Option 5 are included here.
- **Option 4** (imminent-disaster mode) is optional and has its own short section at the end.

**How to read this file**
- Page numbers are from `Capstone 1 Chapter1-3.pdf`.
- Each section says what to cut, what to add, and what the new text must cover, in order.
- These are outlines. Write the sentences yourself, in your own words, from your own notes.
- Where this file and Part C of the guide (`Capstone1_Ch1-3_What_To_Do_Next.md`) differ, follow this file.

---

## The change in one view

| | Now | After |
|---|---|---|
| **Main claim** | No single system combines LP, an offline PWA, and an ISO evaluation for an LGU (p. 23) | Three specific contributions, each with its own test |
| **Contribution 1** | Target = 1.5 × average use, for every item | Each item's target comes from its own past quarters and is recalculated every quarter. The full model plans against those quarters as scenarios. Backtested against the flat 1.5 |
| **Contribution 2** | The dashboard shows the recommended order | The dashboard also shows why: a reason for each item, and what more budget would buy. Tested with staff in UAT |
| **Contribution 3** | Offline entries queue in IndexedDB and sync later | Stock changes become entries that can't be double-counted, that sync in a set order, and that show later edits. Tested with fault and tamper tests |
| **Stays the same** | Consumable and borrowed requests, RBAC, dashboard updates, ISO survey, Scrum, Laravel + FastAPI + PuLP | Same, but each part now serves one of the three contributions |

---

## 1. Front matter

**Title**
- Keep the title you chose, but settle the name first: the guide says "DisasterResponse+" and the manuscript says "DisasterResource+".
- You may add a subtitle that names the contribution, for example one that mentions explainable replenishment and outage-tolerant stock records. That's your decision.

**Definition of terms: add these**
- scenario (a past quarter used as a possible next quarter)
- per-item multiplier (covering level)
- sample average approximation
- fill rate
- weighted shortage
- budget curve
- stock entry
- tamper-evident record (hash chain)
- device-made ID
- conflict flag
- VED classification
- backtest
- ICS 213-RR resource request form

**Definition of terms: remove these**
- PSGC
- GIS-related terms
- "allocation" in its old depot-to-request sense

**Acronyms: add these**
- MILP
- SAA
- VED
- UAT
- ICS
- DROMIC
- PAHR, if you mention it

**Lists of figures and tables:** add the new tables and figures listed in Sections 2–4.

---

## 2. Chapter 1

### 2.1 Background (pp. 1–3)

**Cut**
- the spatial-intelligence and desktop-mapping sentences (p. 2);
- "allocation imbalances across evacuation centers" (p. 1), because distribution isn't in your scope;
- the generic global opening, or shorten it to one sentence.

**Add, in this order**
1. **The national picture.** DSWD plans relief stock with rules and forecasts at the national level:
   - a call-off request when stock drops below 75% of the mandated level (AO No. 7, s. 2025);
   - prepositioning guided by PAGASA rainfall forecasts through PAHR, used since 2016.

   Cities decide their own stock.
2. **What a typhoon quarter looks like in San Pablo.** Use the DROMIC Report #101 figures for Kristine and Leon:
   - 811 families affected;
   - 629 families in 41 evacuation centers.

   Say in one sentence why that quarter is unlike an ordinary one.
3. **How CDRRMO decides now:**
   - requests on the ICS 213-RR form;
   - the borrowed-equipment logbook;
   - Excel;
   - next quarter's order adjusted from last quarter's stock (your 2026-09-03 interview).

   State the office's actual rule.
4. **Three weaknesses, one sentence each:**
   - a fixed rule averages away the typhoon quarters;
   - when the budget falls short, nothing shows what was cut or why;
   - paper and offline records can double-count or be changed without a trace.
5. **What DisasterResource+ does about each weakness,** one sentence each. This is where your three contributions first appear.

**Sources:** DSWD AO No. 7 (2025); Riveral (n.d.) for PAHR; DSWD DROMIC Report #101 (2025); RA 10121; the Sendai Framework. Describe the consultation in Chapter 3 and refer back to it.

**SDG 12:** keep it only if you track expiry, so you can report units expired per quarter. Otherwise drop it.

**Make it yours**
- the office's real reorder rule, in your own paraphrase of the interview
- how many item types and depots there are

### 2.2 Research problem and research questions (pp. 3–5)

**General problem.** Follow Outline 1 in the guide, points 1–6. Then change point 6 so it names the three weaknesses from 2.1:
- a fixed rule;
- no explanation when the budget is short;
- records that break during outages.

**The seven research questions.** This list replaces Outline 1's.

| # | Topic | What the question must ask |
|---|---|---|
| 1 | Current practice | How stock is recorded, requests are made (ICS 213-RR, logbook), and quarterly orders are decided now, and how often recorded need exceeded stock |
| 2 | Model design | How a budget-limited model can set each item's target from its own past quarters (per-item multiplier) and, in its full form, plan against those quarters as scenarios |
| 3 | Model vs. current rule | How the flat 1.5, the per-item multiplier, and the scenario model compare on past quarters the model hasn't seen, by fill rate, weighted shortage, items that ran out, and budget used, with typhoon quarters reported separately |
| 4 | Offline records | How entries made during outages can sync with none lost or duplicated, in priority order, while the stock records show any later edit |
| 5 | Requests and accountability | How the system can digitize the ICS 213-RR form (recording requested and released quantities) and follow borrowed equipment from issue to return, repair, condemnation, or surrender |
| 6 | Dashboard and explanations | How the dashboard can show stock, use, and the recommendation, together with a reason for each item and a budget curve that Logistics and Research and Planning can act on |
| 7 | Acceptability | How personnel rate the system under ISO/IEC 25010:2023: functional suitability, reliability, and interaction capability (including self-descriptiveness), plus safety (fail safe) if your adviser agrees |

- If your adviser wants fewer questions, merge 2 and 3.
- Question 1 gives the backtest its baseline, so keep it.

**Measurable targets to adapt** (put them in the objectives):
- **Offline test:** 100% of queued entries saved, 0 duplicates, 0 lost, and priority entries first.
- **Tamper test:** every edited row is detected.
- **Backtest:** the per-item multiplier and the scenario model each give a lower weighted shortage than the flat 1.5 in [x] of [n] quarters.
- **UAT explanation task:** at least [80]% correct answers.
- **ISO survey:** each characteristic reaches the ["Very Acceptable"] range.

### 2.3 Research objectives (pp. 6–7)

- Write the objectives in the same order as the seven questions, one verb each (describe, design, compare, implement, build, evaluate).
- In the general objective, add the three contributions after "to improve quarterly resource replenishment decisions."

### 2.4 Theoretical framework (pp. 7–9, Figure 1)

**What's there now**
- LP theory, supported by Husein et al. and Ilabaca et al.
- Eventual consistency and local-first computing, supported by Cuenca-Enrique et al., Huber et al., and Melvin and Nguyen.

**What to use instead:** three theories, one for each contribution.
1. **Optimization under uncertainty** (Contribution 1)
   - Start from linear programming, then explain stochastic programming.
   - Sample average approximation treats observed past quarters as equally likely scenarios (Kleywegt et al., 2002; Shapiro et al., 2021 or Birge & Louveaux, 2011).
   - Link it to safety stock and service level (Silver et al., 1998, already in your list) and to relief inventory (Balcik et al., 2016; Rawls & Turnquist, 2010).
   - Stop citing Ilabaca et al. as theory. It's a routing model.
2. **Explainable decision support** (Contribution 2)
   - Explainability in operations research (De Bock et al., 2024).
   - Situation awareness for the dashboard: seeing, understanding, and projecting (Endsley, 1995).
3. **Local-first computing with conflict-free replication and tamper-evident logging** (Contribution 3)
   - Local-first (Kleppmann et al., 2019).
   - Eventual consistency (Vogels, 2009).
   - The conflict-free replication idea (Shapiro et al., 2011).
   - Hash-chained audit logs (Schneier & Kelsey, 1999).
   - Huber et al. (an energy study) and the Melvin and Nguyen preprint can stay in Chapter 2 as related studies, but not as the theory.

**Figure 1:** redraw it so each theory points to its component and its objective.

**The Research Framework heading (p. 7):** use your one-to-two-sentence introduction there to name the three theories.

### 2.5 Conceptual framework (pp. 9–11, Figure 2, the IPO model)

**Input:**
- past quarterly use per item, as both requested and released quantities;
- current stock, from the stock entries;
- unit prices;
- the budget;
- depot space;
- the VED ranking.

**Process:**
1. Data checks.
2. The per-item multiplier or the scenario model (solver service).
3. Reasons and the budget curve.
4. Logistics review.

**Output:**
- recommended orders, each with its reason;
- the budget curve;
- approved orders recorded as stock entries.

**Add these to the figure**
- **Offline path:** device → outbox (device-made IDs, priority order) → server stock entries (hash chain, conflict flags).
- **Feedback loop:** each quarter's entries become new history, and the multipliers update. This loop is what makes the multiplier dynamic.

**Fix:** approving a reorder doesn't change the reserved quantity. Reservation belongs to outgoing requests (guide, Step 4).

### 2.6 Scope and limitations (pp. 11–14)

**Scope: change these**
- Rewrite "four main areas" around:
  - the three contributions;
  - the request module;
  - the dashboard.
- **Request module:** say it digitizes the office's ICS 213-RR resource request form and borrowed-equipment logbook, but doesn't model the ICS command structure. Your current text excludes ICS, and the office uses the ICS form, so say both plainly.
- **Data period:** state it, along with the number of item types and depots.
- **Code outside the scope:** your code still has incident, dispatch, disaster-feed, and OSRM parts (`docker/osrm`). Either describe them in the scope or remove them before UAT. The paper and the system must match.

**Limitations: add these**
- **Small sample:** 8 to 12 quarters of history, so the backtest shows direction, not statistical proof.
- **Understated need:** where only released quantities exist, need is understated in quarters when stock ran out. Requests recorded on the ICS 213-RR form reduce this.
- **Policy choices:** the priority weights and coverage levels (for example "7 of 8 quarters") are set by Logistics, and results change with them.
- **Hash-chain limit:** the chain shows edits, but it can't stop someone with full database access from rebuilding it. Printing the latest fingerprint on quarterly reports reduces this risk.
- **Browser limits:**
  - Background Sync works only in Chromium browsers.
  - Safari can clear site data after seven days without use, unless the app was added to the Home Screen.
- **What the model ignores:** lead time and expiry, unless you add them.
- **Cold start:**
  - Items with too little history fall back to the flat 1.5 until [n] quarters exist.
  - Make p. 13 ("zero-suggestion") and p. 33 ("flagged for manual review") say the same thing.
  - Your code currently returns zero with an "insufficient history" note.

### 2.7 Significance (pp. 14–16)

- **Logistics:** orders fitted to each item's history, plus reasons they can defend in LDRRMF planning and purchase requests under RA 12009.
- **Research and Planning:** the budget curve for annual planning.
- **The head of office and the city:** stock records that show later edits, which helps with COA audits.
- **ERT and Operations:** team kits restocked from their actual request history. They're the ones filing ICS 213-RR forms.
- **Future researchers:** a tested way to plan with small LGU datasets.
- **Delete:** "Taken together, the additions above…" (pp. 15–16).

---

## 3. Chapter 2

**Reorganize the chapter by contribution.** Add a sentence at the start that says so. The page 17 promise of "foreign and local" literature can stay if you label studies as foreign or local inside each section.

### 3.1 Relief inventory under uncertain demand (replaces the optimization section)

1. **What the research knows:**
   - review of relief inventory models (Balcik et al., 2016);
   - stock levels set before disasters under uncertain demand (Rawls & Turnquist, 2010);
   - a Philippine typhoon prepositioning model (Uichanco, 2022).
2. **The gap between research and practice** (Ye et al., 2020).
3. **What Philippine optimization studies have optimized:** hub locations and routing (de Veluz et al., 2023; Putong & De Leon, 2018). That wasn't an office's quarterly restocking.
4. **What LP gains look like:** keep Husein et al. (2026) as the example.
5. **Why not forecasting:** Croston-type methods fit demand that comes in bursts (Croston, 1972; Syntetos & Boylan, 2005). They need monthly data and give one number, which still needs a safety factor.
6. **Ilabaca et al. (2022):** keep it only as an example of routing aid deliveries after a disaster, and describe it correctly.

### 3.2 Explaining optimization results (new)

1. Explainability as a research agenda in operations research (De Bock et al., 2024).
2. Methods:
   - interpretable models (Goerigk & Hartisch, 2023);
   - "what would have to change" explanations (Korikov et al., 2021; Kurtz et al., 2026).
3. The gap: these methods haven't been tested with non-expert government staff.
4. Accountability in a Philippine AI typhoon trigger (van den Homberg et al., 2020).
5. Dashboard design and situation awareness (Endsley, 1995; Few, 2013).

### 3.3 Offline-first systems and record integrity (replaces the PWA section)

1. **Offline web apps:** PWAs where connections are unreliable (Cuenca-Enrique et al., 2024).
2. **The local-first idea** (Kleppmann et al., 2019).
3. **Merging concurrent changes:**
   - the conflict-free replication idea (Shapiro et al., 2011);
   - keeping stock from going negative across replicas (Balegas et al., 2015).
4. **Priority caching for emergency data:** Melvin and Nguyen (2025), labeled as a preprint.
5. **Tamper-evident logs** (Schneier & Kelsey, 1999; Crosby & Wallach, 2009).
6. **Why not blockchain:** one office holds the data (Dubey et al., 2020).
7. **Fix page 19:** describe the sync your system actually has:
   - device-made IDs;
   - duplicate uploads ignored;
   - the sync order, once you fix it.

   Don't describe priority sync as built until it is.

### 3.4 Related systems (expand)

**Systems to add:**
- DSWD:
  - RGIMS on Sahana Eden (Prutsalis, 2013);
  - the warehouse tools and dashboard required by AO No. 7 (2025);
  - PAHR (Riveral, n.d.).
- International tools:
  - PAHO LSS/SUMA, with HELIOS assessed by Blecken and Hellingrath (2008);
  - WHO PIMS (Rasheed et al., 2019; get the full reference from the article page);
  - WFP LESS and UNHRD, if you read their pages.
- Philippine LGU systems:
  - Anduyog (Rodriguez et al., 2017);
  - Clarito et al. (2025);
  - Yaneza et al. (2025), the Calamba City CDRRMO system from LSPU, which you must cite;
  - Macose (2022) and Albano (2024);
  - E-LIGTAS, with its description corrected.

**New table: features of existing systems.**
- **Rows:** systems. **Columns:** the features from Section 1 of the options file.
- Mark each cell Yes, No, or "not documented," based only on sources you've read.
- This table carries your gap claim.

**State your search scope:** databases, keywords, and years (guide Part D.2).

### 3.5 ISO/IEC 25010:2023 (keep, with small changes)

- It has nine characteristics. That's fixed in your Revised tab.
- Name the sub-characteristics you'll measure:
  - **Functional suitability:** completeness, correctness, appropriateness.
  - **Reliability:** faultlessness, availability, fault tolerance, recoverability.
  - **Interaction capability:** including self-descriptiveness and user assistance, which is how your explanations are rated.
  - **Safety:** fail safe, if included.

### 3.6 Synthesis and Table 1 (pp. 22–24)

**Rewrite the gap paragraph** (this replaces Outline 2's points 2–8):
1. Each piece exists and has been studied: optimization, PWAs, and ISO evaluation, with sources from 3.1–3.5.
2. **Gap 1:**
   - Budget-limited replenishment that plans from past quarters appears only in research.
   - No deployed city-level tool was found.
   - Relief systems use thresholds, past-usage ordering, or historical ceilings.
3. **Gap 2:** no system explains its recommendations, and explanation methods haven't been tested with government staff.
4. **Gap 3:**
   - Among relief systems, only Sahana Eden documents its sync conflict rules, and they keep one copy of a changed record.
   - No system keeps tamper-evident stock records.
5. **Why these belong together:** a model fed wrong stock figures gives wrong orders, and an order staff can't explain won't be trusted or funded.
6. **Close with what your study does,** and bound it: one city office, its own records, and your data period.

**Table 1 rows** (keep the column layout from Outline 2):
- replenishment planning;
- explanations;
- offline stock records;
- record integrity;
- inventory accountability (from Outline 2);
- decision visibility (from Outline 2).

**Drop "quality evaluation" as a gap row.** ISO evaluation is a method, not a gap.

---

## 4. Chapter 3

### 4.1 Research design (p. 25)

- Keep developmental plus descriptive-evaluative.
- Add a comparative component: a backtest of the replenishment rules on the office's historical records.
- Cite a design-and-development research source (Richey & Klein; see guide Part D.2).

### 4.2 Locale, respondents, and requirements gathering (pp. 25–27)

**Office profile:** divisions, headcount, depots, item categories, and current tools (ICS 213-RR forms, the logbook, Excel).

**Respondents:**
- **ISO survey:** all six divisions.
- **Explanation UAT task:** Logistics and Research and Planning, who read the recommendations.
- **Note:** ERT and Operations are requesters on the ICS 213-RR form.

**New subsection: requirements gathering**
- Cover the 2026-09-03 interview and any others: dates, positions (not names), and the topics asked.
- Put the interview guide in an appendix. It's visible under the form in your photos.
- List the documents you collected. Put copies of the form and logbook in an appendix, with names and signatures covered.

### 4.3 Data (pp. 28–29, Table 2)

**Replace** "data comes from the system's own operational records" (p. 28). It's circular, because the system isn't in use yet. Say instead:
- historical data is encoded from the paper forms and logbooks for [period];
- system records are used after deployment.

**The encoding template** (one row per request line):
- date;
- item;
- standard unit;
- quantity requested;
- quantity released;
- purpose (team kit, relief, or office use);
- event tag (for example, Kristine);
- requesting division or team.

**Privacy:** leave out names and signatures (RA 10173). The model needs the division, not the person.

**Units:** pick one standard unit per item.
- The form mixes boxes, packs, pieces, sets, and bottles.
- Your seeder groups about 30 medical supplies under "Medical kits."
- Decide the item level with Logistics.

**Table 2: add these columns**
- unit cost;
- standard unit;
- VED class;
- quantity requested next to quantity released;
- event tag;
- depot space per unit, if you model space.

**Data checks (Option 5).** List the rules that flag an entry before the model runs:
- a quarter's use more than three times the usual with no event tag;
- stock below zero;
- a gap at the weekly count.

### 4.4 Algorithm analysis (pp. 27–28)

**Replace PSR**, which counts items but ignores how short they are, with these measures, reported per quarter and for typhoon quarters separately:
- fill rate;
- weighted shortage;
- number of items that ran out;
- budget used;
- stock left over.

**Keep** the feasibility check.

**Fix these**
- Replace the O(n) claim with measured solver time.
- Correct the definition of "definiteness." It isn't the same as determinism.

**Add two checks that prove the explanations are consistent**
- Each reason must match the solution: "budget ran out" only when the budget constraint is fully used.
- The budget curve must never go up as the budget rises.

### 4.5 Optimization model generation (pp. 29–32)

**Framing.** Replace the "machine-learning pipeline" with an operations-research modeling process (Hillier & Lieberman, 2021):
1. problem definition;
2. data;
3. formulation;
4. solution;
5. validation;
6. deployment.

**Parameter preparation**
- Each item's per-item multiplier is its covering level divided by its average use.
- The covering level is the amount that would have covered k of the last M quarters, with k set by VED class. For example: vital = every quarter, essential = 7 of 8, desirable = 6 of 8.
- It's recalculated every quarter.
- State the cold-start rule.
- Use M = 8 when the history allows. Your code currently averages 4 quarters.

**Model building, in two stages**
- **Stage 1:** the guide's Outline 3 model, with T_i set to the per-item covering level instead of 1.5 × average.
- **Stage 2:** the scenario model from the options file (Option 1, "Model sketch"). It has one shortage variable per item per past quarter, the objective divided by average use, and the budget and space limits.

**Explanation layer**
- how each reason is derived from the solution;
- how the budget curve is produced by re-solving at budget steps;
- how "next ₱10,000 goes to" is read from the next point on the curve.

**Model evaluation: the backtest**
- **Setup:** rolling origin. For each quarter from the fifth on, use only earlier quarters to build the order, then compare the order with actual use.
- **Rules compared:**
  - the office's actual orders, if recorded;
  - the flat 1.5;
  - the per-item multiplier;
  - the scenario model.
- **Measures:** those listed in 4.4.
- **Limitations:** the small sample and the understated need.

**Worked example.** Replace Table 3 with the eight-quarter illustrative example. Label it illustrative, and swap in the office's data when you have it.

| Budget | Flat 1.5 | Per-item multiplier | Scenario model |
|---|---|---|---|
| ₱150,000 | 0.55 | 0.58 | 0.49 |
| ₱200,000 | 0.34 | 0.32 | 0.32 |
| ₱250,000 | 0.27 | 0.24 | 0.23 |
| ₱300,000 | 0.19 | 0.16 | 0.15 |
| ₱350,000 | 0.18 | 0.11 | 0.07 |

- The values are expected shortage: quarters of average use short, summed over items, averaged over the eight past quarters. Lower is better.
- Explain in your own words:
  - The multiplier beats the flat rule except when money is very tight.
  - The scenario model does best at every budget.
  - These numbers are in-sample and illustrative. The real evidence is the backtest.

**Model deployment**
- The solver service returns orders, reasons, and the budget curve.
- Laravel stores the run, and the coordinator approves or rejects it.
- Move the reservation and deduction paragraph out of this subsection (guide, Step 7).

### 4.6 Scrum, tools, and hardware (pp. 33–36)

- **Sprint plan:** give the three contributions their own sprints, in this order:
  1. per-item multiplier and data encoding;
  2. explanations;
  3. stock entries, hash chain, and sync order;
  4. scenario model.
- **Tools:**
  - PuLP 2.8.0 (your pinned version);
  - `idb` (the IndexedDB wrapper you use);
  - the chart library for the budget curve;
  - SHA-256 for the hash chain (PHP's `hash()`).
- **Keep the other hardware notes** from guide Step 7: server, UPS, and client devices.

### 4.7 System architecture (pp. 36–38, Figure 3)

- Remove PostGIS, OSRM, and routing from the text and the figure, unless you keep them in the code (see 2.6).
- **Add to the figure:**
  - the outbox with device-made IDs and ordered sync;
  - the stock-entry table with the hash chain and the "check records" command;
  - the solver service returning reasons and the budget curve.
- **Add a small data-flow diagram** for sync and stock entries, from an entry on a device to its place in the chain.

### 4.8 Software testing (pp. 38–41)

**Unit tests**
- The solver reproduces the guide's worked example: 127/240/350 for least shortage and 218/159/232 for the same-percent cut.
- The per-item multiplier is computed correctly.
- Each reason matches its constraint status.
- The budget curve never goes up.

**Fix the old unit test** that expects the solver to spend the whole budget (p. 41). Replace it with two checks:
- the solver never exceeds the budget;
- money is left over only when every target is met.

**Offline tests** (Outline 4, plus these):
- upload the same batch three times: 0 duplicates;
- **sync order:** an area queued under an incident created offline syncs after that incident, and priority entries go first;
- **conflict:** two approvals that together exceed stock raise a flag;
- **tamper test:** edit an old row directly in the database, and the check fails at that row.

**UAT tasks**
- file a request with the ICS 213-RR fields;
- lend and return an item;
- work offline;
- the explanation task: three recommendations, three questions each (why was this item cut, what would ₱20,000 more change, which limit mattered).

**Instrument**
- Build the items from the ISO/IEC 25010:2023 sub-characteristics in 3.5.
- Add an interpretation scale for the Likert points.
- Pilot-test it and report Cronbach's alpha.

**Statistics**
- **Survey:** weighted mean with the interpretation scale.
- **Explanation task:** percent of correct answers.
- **Backtest:**
  - per-quarter differences between the rules;
  - the number of quarters each rule wins.
- **Drop the cross-role significance tests:** two or three people per role is too few.

---

## 5. If you also take Option 4 (imminent-disaster mode)

- **Chapter 1**
  - Add RA 12287 to the background.
  - Add one research question and objective on supporting pre-landfall buying and prepositioning.
  - Add safety (hazard warning) to the ISO characteristics.
- **Chapter 2:** add a subsection on anticipatory action:
  - Coughlan de Perez et al. (2015);
  - Kooshki Forooshani et al. (2024);
  - Sedhain et al. (2025);
  - OCHA (2025);
  - RA 12287;
  - the NDRRMC PDRA memo (2014).

  Note that DSWD's PAHR already does forecast-led prepositioning nationally.
- **Chapter 3**
  - Describe the mode's trigger (manual first) and the Kristine scenario built from DROMIC figures.
  - Test it with a tabletop exercise with Logistics.
- **Scope:** say it covers the days before landfall only, not live incident operations.

---

## 6. Updated consistency matrix

| RQ | Objective | Theory | Method | Test or measure | ISO/IEC 25010:2023 |
|---|---|---|---|---|---|
| 1 Current practice | Describe | none | Interviews; review of forms and logbooks | Themes; counts of requests and stockouts | none |
| 2 Model design | Design the per-item multiplier and scenario model | Optimization under uncertainty | Formulation; PuLP and CBC | Unit tests; feasibility; solver time | Functional correctness |
| 3 Model vs. rule | Compare | Optimization under uncertainty | Rolling-origin backtest | Fill rate; weighted shortage; stockouts; budget used | none |
| 4 Offline records | Implement stock entries and ordered sync | Local-first; conflict-free replication; tamper-evident logs | Outbox; stock-entry table; hash chain | Offline, order, conflict, and tamper tests | Reliability; safety (fail safe) |
| 5 Requests | Build the request module | Accountability (COA forms) | ICS 213-RR digitization; borrowed-item lifecycle | Functional test cases | Functional completeness |
| 6 Dashboard and explanations | Build | Explainable decision support; situation awareness | Reasons; budget curve | UAT explanation task | Interaction capability (self-descriptiveness, user assistance) |
| 7 Acceptability | Evaluate | none | Survey | Weighted means | All three (or four) characteristics |

Every row should have something in each column. Check it again after you rewrite.

---

## 7. Order of work

1. **Settle the inputs** (Section 8), then choose your options with your adviser.
2. **Rewrite Chapter 1's spine:** problem, questions, objectives.
3. **Rewrite the theoretical framework and Figure 1,** then the conceptual framework and Figure 2.
4. **Reorganize Chapter 2,** build the features table, and rewrite the synthesis and Table 1.
5. **Rewrite Chapter 3:**
   1. data;
   2. the model;
   3. the backtest;
   4. testing;
   5. the instrument.
6. **Do the scope and limitations last,** once you know what you built and tested.
7. **Finish with a consistency pass:** the matrix, the definitions, the TOC and page numbers, and one term per concept.

---

## 8. Things only you can supply

- **Rules and history**
  - the office's actual reorder rule;
  - how far back the ICS 213-RR forms go, and where older requests are kept;
  - whether the forms show both requested and released quantities.
- **Items and priorities**
  - the item list, a standard unit for each item, and unit prices;
  - the VED ranking and the coverage level for each class, agreed with Logistics (record who agreed and when).
- **Office facts**
  - rough depot space;
  - consultation dates and attendees' positions.
- **Scope decision:** whether the incident, dispatch, and routing code stays or goes.
