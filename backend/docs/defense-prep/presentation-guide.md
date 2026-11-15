# Presentation Study Guide — DisasterResource+ Proposal Defense

A slide-by-slide guide to the deck `DisasterResource+ Proposal Defense.pptx` (31 slides). For each slide it gives:
- the time to spend;
- what the slide must prove;
- what to say, in plain words;
- what to point at;
- the question it may trigger, with its number in the **Panel Q&A Reviewer**.

**The whole defense (about 1 hour):** slides 30 min → demo video 5–15 min → panel Q&A 15 min.

**The one question the guide says every defense must answer:**
> "Why is this study needed, and why is your approach appropriate?"

Every slide below is one step toward that answer. The order follows the guide:
**Problem → Objectives → Framework → Literature → Gap → Data & Method → System → Evaluation.**

---

## Delivery rules

1. **Talk to the panel, not to the slide.** Glance at the slide, then face the panel.
2. **Never read a table row by row.** Point to two or three cells that matter.
3. **One idea per slide.** Say the slide's main point first, then the detail.
4. **Use the transition line** at the end of each slide. It is what makes the slides one argument.
5. **Plans, not results.** Say "will test" or "may support", never "proves" or "will improve".
6. **Keep time.** Glance at the clock at slides 11, 15 and 22 (time marks below).
7. **Don't mention your adviser** as a reason for anything. Give your reason from the paper.

---

## Part 1 — The need (Chapter I) · slides 1–11 · about 10 min

### Slide 1 · Title · 0:30
**Prove:** who you are and what the study is.
**Say:**
> "Good morning, members of the panel. I am Emmanuel Manalo, Master in Information Technology. My proposal is DisasterResource+, a system that helps CDRRMO San Pablo City plan and track its disaster supplies before a typhoon arrives."

**Transition:** "I will show the problem, the literature and gap, the method and system, and how it will be evaluated, then a short demo."

### Slide 2 · Each quarter: what should be in stock? · 1:00
**Prove:** the need is real and local.
**Point at:** the three numbers. **19–20** typhoons a year; **5%** DRRM fund; **46 of 80** barangays hit by Kristine and Leon.
**Say:**
> "The Philippines gets about 20 typhoons a year. Each LGU sets aside 5% of its revenue for disaster funds, and part of it buys supplies. In October 2024, Kristine and Leon hit 46 of San Pablo's 80 barangays. So every quarter the office asks: what should be in stock, and how much can the budget buy? My study is about that readiness work before a disaster, not the response operations during it."

**Transition:** "Here is how the office answers that question today."
**Likely Q:** A1 (what "response" means).

### Slide 3 · How the office manages its supplies today · 1:00
**Prove:** the current process is manual and has no rule.
**Point at:** the five steps, then the yellow box ("No written rule").
**Say:**
> "From my interview with three key informants on September 3, the head of Administration and Training and two Logistics staff: requests go on the ICS 213-RR form, borrowed equipment goes in a logbook, and stock is kept in Excel and paper. Each quarter they adjust the order from leftover stock, but no written rule says how much extra to keep. Logistics said counting errors are their biggest problem, and any tool must survive power and internet outages."

**Transition:** "This leads to three things the office lacks."
**Likely Q:** B2 (evidence for the problem), K4 (why three informants).

### Slide 4 · Research problem · 1:00
**Prove:** the problem in one sentence, then its three parts.
**Say:**
> "The main problem: the office plans each quarter's purchases from hand-kept counts with no written rule. It lacks three things: a planning basis from each item's own history under the budget, recommendations that show what was cut and why, and stock records that stay correct through outages and show any later edit. The effects on the slide are possibilities, not findings."

**Transition:** "These become five research questions."
**Likely Q:** B1.

### Slide 5 · Research questions · 1:00
**Prove:** each question is answerable and none asks "how to build".
**Say:**
> "Question 1 describes current practice. Question 2 tests the model against four other rules on past quarters. Questions 3 to 5 test the system: the records, whether staff can use the explanations, and whether users accept it."

**Transition:** "Each question has one objective with a clear pass mark."
**Likely Q:** C1.

### Slide 6 · Research objectives · 0:30
**Prove:** each objective separates *build* from *determine*, with a criterion.
**Point at:** the right column (the criteria).
**Say:**
> "Each objective says what I build and what I determine. For example, Objective 4 builds the dashboard and determines whether staff answer at least 80% of the questions correctly. Objective 5 uses 'Very Acceptable', 4.21 to 5.00."

**Transition:** "Here is how each question, objective and procedure line up."
**Likely Q:** C2 (why 80%), C3 (why 4.21).

### Slide 7 · One objective and one procedure per question · 0:30
**Prove:** alignment, which the guide's checklist asks for.
**Say:**
> "This is Table 1: each question has one objective, one Chapter III procedure, and one criterion, so every question can be traced to how it is answered."

**Transition:** "The study rests on three theories."

### Slide 8 · Theoretical framework · 1:30
**Prove:** each part of the system has a theory behind it, and you can explain each one.
**Point at:** the three boxes of Figure 1, left to right, as you name each theory.
**Say:**
> "Figure 1 shows three theories, one for each part of the system.
>
> First, optimization under uncertainty. Linear programming chooses the best decisions within limits. Here the orders are the decisions, the budget and depot space are the limits, and the shortage left after ordering is what the model minimizes. But a disaster's time and size are not known in advance, so instead of one forecast, sample average approximation treats each of the office's past quarters as one possible next quarter and chooses the orders with the lowest average shortage. That is Stage 2, the scenario model. Stage 1, the per-item multiplier, is the simpler form: each item's target is what would have covered most of its own past quarters.
>
> Second, explainable decision support. Decision-makers need to understand what drives a model's output before they rely on it. Situation awareness gives three levels: see the current state, understand what it means, and project what comes next. The dashboard follows them: current stock and use, a reason for each order, and the budget curve, which shows how the shortage changes with the budget.
>
> Third, local-first computing and tamper-evident logging. Work continues on the device without a connection and merges later. Because every stock change is an entry that adds or subtracts, the entries give the same total in any order. Each entry is linked to the one before it by a hash, so a later edit shows. This guides the offline records and Check records.
>
> These theories guide the design. I do not measure situation awareness, and the system uses only the add-and-subtract counter, not a full replication framework."

**Transition:** "Those theories come together in the conceptual framework."
**Likely Q:** D2 (situation awareness not measured), D3 (CRDTs).

### Slide 9 · Conceptual framework (IPO) · 1:30
**Prove:** the framework matches Chapter III, and you can explain each box.
**Point at:** Input, then the two strands of Process (Scrum + model generation), then Output (recommendation → approval → purchase plan).
**Say:**
> "Figure 2 is the conceptual framework, an Input–Process–Output model.
>
> Input: the seven things the model needs. Each item's past quarterly use, recorded as both requested and released quantities; current stock, computed from the stock entries; unit prices; the quarterly budget entered by the Head of Office; the free depot space and each item's space per unit; each item's VED class; and each class's coverage level and priority weight. Logistics proposes the VED classes and weights, and the Head of Office confirms them before any run.
>
> Process: two strands. The system is built through Scrum sprints of planning, execution, review, and retrospective, and the model inside it through the optimization model generation steps, from problem definition and data cleaning to evaluation and deployment. Once built, every quarter's run has four steps: data checks flag doubtful entries for staff to confirm or correct; the solver computes the orders; the system gives a reason for each order and builds the budget curve; and the Head of Office approves or rejects.
>
> Output: DisasterResource+ itself. It gives recommended orders with their reasons and the budget curve, and once the Head of Office approves, the quarter's purchase plan, with received items recorded in tamper-evident stock records. The evaluation results, from the backtest, the tests, the explanation task, and the acceptability survey, complete the output."

**Transition:** "Next, what the study covers and what it leaves out."
**Likely Q:** D4, and "Why is Scrum in your IPO?" Answer: "Because the output is the system itself; Scrum is how it is produced."

### Slide 10 · Scope and delimitations · 0:30
**Prove:** clear boundaries.
**Say:**
> "In scope: CDRRMO's own supplies, records from Q3 2023 to Q2 2026, and five parts of the system. Out of scope: response operations, other offices' stock, barangay requests, bidding, which stays with the General Services Office, and lead time and expiry."

**Transition:** "Within that scope, the study also has limits I want to state clearly."
**Likely Q:** A4, A5.

### Slide 11 · Limitations and significance · 1:00
**Prove:** honesty about limits, and careful claims about benefits.
**Say:**
> "Limits: 8 to 12 quarters show a direction, not proof; the model has no lead time or expiry; there is one approver, and offline sync needs a network at some point. The study **may** support Logistics, Research and Planning, the Head of Office and the response teams. It does not claim to reduce shortages, because that is not measured."

**⏱ Time check: about 10:00.**
**Transition:** "Next, what the literature says and where the gap is."
**Likely Q:** F3, F4.

---

## Part 2 — The gap (Chapter II) · slides 12–15 · about 4.5 min

### Slide 12 · Literature: planning stock from short records · 1:15
**Prove:** the literature justifies the **method**, not just the topic.
**Point at:** three rows only: **Shehadeh & Tucker** (basis of the scenario model), **Besbes & Mouchtaki** (deciding from few observations works), **Hewamalage** (basis of the backtest).
**Say:**
> "Relief inventory models exist but are rarely used in the field. Three studies shape my method: scenario-based planning is the basis of my scenario model, deciding from tens of past observations can work well, and rolling-origin evaluation is the basis of my backtest. VED analysis gives the priority classes."

**Transition:** "The second matrix covers the explanations and the records."
**Likely Q:** G3 (why not machine learning), E4 (VED).

### Slide 13 · Literature: explanations and record integrity · 1:15
**Point at:** **De Bock** (explanations lag behind), **Borrego** (negatives preventable only within reservations), **Antonopoulos** (hash chain).
**Say:**
> "Explaining optimization results is still a research agenda, and it has not been tested with government staff. For offline records, entries that add or subtract merge safely, but conflicts must be flagged, not refused. A hash chain makes old edits detectable without needing a blockchain."

**Transition:** "Next, what existing systems already document."
**Likely Q:** I5 (why not blockchain).

### Slide 14 · What existing systems document · 1:00
**Prove:** the gap, fairly stated.
**Point at:** the DisasterResource+ row, then the footnote.
**Say:**
> "This compares only what each system's published sources document. Stock, expiry and offline use already exist elsewhere, so I don't claim them. What no reviewed system documents is budget-limited planning, scenarios, explanations, safe merging, tamper-evident records and data checks. No and ND do not prove a system lacks a feature."

**Transition:** "From these, three gaps remain."
**Likely Q:** E3, B5 (Calamba system).

### Slide 15 · Synthesis: three gaps, three questions · 1:00
**Prove:** gap → research question.
**Say:**
> "Three gaps, each tested by a question: planning from the office's own quarters (RQ2), explanations staff can use (RQ4), and offline, tamper-evident records (RQ3). My contribution is not one new technique but this combination, which no reviewed system documents."

**⏱ Time check: about 14:30.**
**Transition:** "Now, how the study will address these gaps."
**Likely Q:** B3 (what is new), B6 (is the technique new?), B7 (why research, not just software).

---

## Part 3 — The method and system (Chapter III) · slides 16–29 · about 15 min

### Slide 16 · Research design, locale, participants · 1:00
**Say:**
> "A developmental design based on design science: build the system, then evaluate it on the office's real problem. It fits because the study must build a system and show whether it works. The locale is CDRRMO San Pablo. There were three key informants, and at least 20 users, an estimate still to be confirmed, will take the tasks and survey by total enumeration."

**Transition:** "These are the requirements the interview produced."
**Likely Q:** K1, J4.

### Slide 17 · Requirements · 0:30
**Don't read the list.** **Say:**
> "The interview produced ten functional and six non-functional requirements. The key ones are: every stock change is an entry, the Head of Office approves, and the system keeps working through outages with nothing lost or doubled."

**Transition:** "To meet them, the study applies these concepts and techniques."

### Slide 18 · Applied concepts and algorithm analysis · 1:15
**Prove:** each technique has a purpose **and** an evaluation (the guide's checklist).
**Point at:** the "Evaluated by" column, then the yellow box.
**Say:**
> "Each component has one purpose and one way it is evaluated. Both model stages are judged by weighted shortage in the backtest, the explanations by the 80% task, and the records by the offline and integrity tests. The model is an optimizer, not a classifier, so it is judged by shortage, feasibility and solve time, not accuracy."

**Transition:** "The center of the method is the two-stage model."
**Likely Q:** G4 (why MILP), G5 (why PuLP and CBC).

### Slide 19 · The replenishment model: two stages · 2:00 (key slide)
**Point at:** Stage 1 box → Stage 2 box → VED table.
**Say:**
> "Stage 1, the per-item multiplier: each item gets one target, the amount that covered its use in k of its last 8 quarters. Vital items cover all 8, essential 7, desirable 6. The solver buys toward the targets within the budget and space.
> Stage 2, the scenario model: there is no single target. Every past quarter, including typhoon quarters, is treated as a possible next quarter, and the model minimizes the average shortage within the budget. The weights are proposed by Logistics, will be confirmed by the Head of Office, and are fixed before the backtest."

**Transition:** "A small worked example shows how it behaves under a tight budget."
**Likely Q:** G1, G2, G6, G7, G11.

### Slide 20 · Worked example at ₱200,000 · 1:30
**Point at:** the 127 / 240 / 350 column, then "+ ₱10,000".
**Say:**
> "Reaching every target would cost ₱301,500, but the budget is ₱200,000. My model funds first what closes the most shortage per peso: water and hygiene kits are fully covered, and food packs are left 42% short. Each item gets a reason, and the next ₱10,000 would buy 20 more food packs. These values are illustrative, not evidence."

**Transition:** "The real test needs the office's own data, so here is the data plan."
**Likely Q:** G9 (why food is left short).

### Slide 21 · Data: sources, period, preparation · 1:00
**Point at:** the yellow box (interim sample dataset).
**Say:**
> "Primary data come from the interview, tests and survey. Secondary data are the office's forms, logbook, stock sheets, delivery documents and budget records, from Q3 2023 to Q2 2026. A second encoder checks each quarter, and no names are encoded. One honest note: the records are still being requested, so the system currently runs on a sample dataset built from public sources. It is not evidence about the office."

**Transition:** "With that data, the model is evaluated by a backtest."
**Likely Q:** F1, F2, F7, F8.

### Slide 22 · Evaluation: rolling-origin backtest · 1:30 (key slide)
**Point at:** the quarter strip (first 4 = history; Q4 2024 dark = Kristine) → the five rules → the decision rule.
**Say:**
> "From the 5th quarter on, each quarter is planned using only the quarters before it, then compared with real use. Five rules are compared, with the office's actual orders as the main comparison. Weighted shortage is the primary measure. A model wins if it has lower shortage in more than half the quarters; mixed results are reported as mixed, and typhoon quarters one by one."

**⏱ Time check: about 22:30.**
**Transition:** "Next, the system that runs all this, starting with its data model."
**Likely Q:** H1, H2, H4, H5.

### Slide 23 · Data model (ERD) · 0:30
**Say:**
> "The item is the center: history, stock entries and request lines all refer to it. Each stock entry carries the previous entry's hash, which forms the chain, and each run stores its inputs and who decided it."

**Transition:** "The system is built in six Scrum sprints."

### Slide 24 · Scrum in six sprints · 1:00
**Point at:** the sprint table, then the tools line.
**Say:**
> "Six two-week sprints. Sprints 1 to 4 build the three contributions, Sprint 5 integrates requests and the dashboard, and Sprint 6 is testing. I am the developer and Scrum Master, the head of Logistics is the Product Owner, and each sprint ends with a review. Each tool is chosen for a job, for example PostgreSQL for unique device IDs and the chain lock."

**Don't read the "adviser" label in the figure.** Point to the sprint cycle.
**Transition:** "This is the architecture those sprints build."
**Likely Q:** K2, K3.

### Slide 25 · System architecture · 1:00
**Say:**
> "Five parts: the web app with its offline outbox, the Laravel backend, PostgreSQL with the hash chain, a separate solver service with no database access, and Reverb for live updates. Borrower data goes only to the Admin and Logistics, over HTTPS."

**Transition:** "Here is how one quarterly run moves through it."
**Likely Q:** I1, I8, I9.

### Slide 26 · Sequence of a quarterly run · 0:45
**Say:**
> "The Head of Office enters the budget. Open data-check flags stop the run until Logistics resolves them. The solver runs in a queued job, and the result appears on the dashboard. A failed run is never retried silently, and only a completed run can be approved."

**Transition:** "The records side has its own flow: syncing offline entries."
**Likely Q:** G13.

### Slide 27 · Syncing offline entries · 1:00
**Say:**
> "A repeated upload has the same device-made ID, so it is ignored. A return sent before its borrow waits and is sent later. An entry that would make stock negative is kept and flagged, because the goods already moved. Stock is the sum of all entries, so the total is correct in any order."

**Transition:** "Each part is tested with its own test cases."
**Likely Q:** I3, I4.

### Slide 28 · Testing plan · 1:00
**Don't read the table.** **Say:**
> "Five test types with my own test cases: 13 unit, 11 integration, 13 offline and integrity tests run three times on the office's devices, 4 performance tests, and 6 user acceptance tasks. Each has a pass criterion, and the cases include exceptional inputs such as a zero budget or a repeated upload."

**Transition:** "Finally, how the users will evaluate it, and the ethics."
**Likely Q:** J1.

### Slide 29 · User acceptance testing and ethics · 1:00
**Say:**
> "The explanation task has nine questions, with a target of at least 80% correct. The survey uses five ISO/IEC 25010 characteristics; it is validated by three IT faculty, pilot-tested, and must reach a Cronbach's alpha of at least 0.70. It is anonymous and not given by me. For ethics: written permission, informed consent, no names encoded, and compliance with the Data Privacy Act."

**Transition:** "Now, a short demonstration of the prototype."
**Likely Q:** J2, J3, J5, L2.

---

## Part 4 — Demo and close · slides 30–31

### Slide 30 · System demonstration · 0:30, then the video
**Say before the video:**
> "This short video shows the prototype on the sample dataset: the recommendation with reasons and the budget curve, the backtest, offline entries syncing with a conflict flag, an ICS 213-RR request through release, and the quarterly report with its fingerprint."

**After the video (closing line):**
> "To sum up: the office needs a way to plan its supplies from its own records, explain each cut, and trust its stock records. My study builds that system and tests it with clear criteria. Thank you, and I welcome your questions."

### Slide 31 · Staple slide
The LSPU template requires it at the end. Leave it up during Q&A.

---

## Time plan at a glance

| Slides | Part | Time | Clock at end |
|---|---|---|---|
| 1–11 | The need (Ch I) | 10:00 | 10:00 |
| 12–15 | The gap (Ch II) | 4:30 | 14:30 |
| 16–22 | Method and model | 8:00 | 22:30 |
| 23–29 | System and testing | 7:00 | 29:30 |
| 30 | Demo intro | 0:30 | 30:00 |

**If you are behind:** shorten slides 10, 17, 23 and 26 to one sentence each. **Never skip** slides 4, 15, 19 or 22. They carry the argument.

## The 6 slides to know best
1. **Slide 4:** the problem.
2. **Slide 15:** the gap and the contribution.
3. **Slide 19:** the two-stage model.
4. **Slide 20:** the worked example numbers.
5. **Slide 21:** the honest data note.
6. **Slide 22:** the backtest and the win rule.
