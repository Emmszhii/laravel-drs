# Panel Q&A Reviewer — DisasterResource+

Every question a panelist is likely to ask about the manuscript, with an answer you can say out loud, **why that answer is right** (the part of the paper it rests on), and what **not** to say. Page numbers are from the current manuscript (14 Nov 2026).

**How to answer any question**
1. Answer first, in one sentence.
2. Give the reason in one or two sentences, naming the chapter or table.
3. Admit the limit if there is one. The paper already states it, so admitting it shows control, not weakness.
4. Stop. Don't add new claims that are not in the paper.

**If you don't know:** "Thank you, Ma'am/Sir. The study has not covered that yet. I will note it as a recommendation for the study."

---

## A. Title and scope

**A1. What does "disaster response" in your title mean?**
> It means readiness to respond. The actual response happens during and right after a disaster, but how well it goes depends on the supplies stocked before. My system helps plan and track those supplies. The response operations themselves are outside my scope.

*Why it's right:* Chapter I (p. 3) defines it this way, and the Scope (pp. 13–15) excludes incident command, dispatch and relief distribution.
*Avoid:* saying the study "measures readiness" or "improves response". The Limitations (p. 15) say outcomes are not measured.

**A2. Then why not change the title to "Disaster Preparedness"?**
> I kept "Response" because the supplies exist to support the response, and Chapter I defines it as readiness to respond. If the panel recommends "Preparedness," I will revise it.

*Why it's right:* it gives your own reason from the paper (Chapter I, p. 3) and stays open to the panel, without leaning on your adviser. *Avoid:* "My adviser decided the title." The panel wants your reasoning.

**A3. Why CDRRMO San Pablo City only? Can it be used by other LGUs?**
> The study is built and tested for one office because the model plans from that office's own records. The method can be repeated by other offices on their own records, and the paper lists that as a benefit for future researchers. It does not claim the results apply to other LGUs.

*Why it's right:* the Significance section (p. 18) says "a backtest design that other LGU offices can repeat on their own records"; the Locale is one office.
*Avoid:* "It works for all LGUs."

**A4. Why are other offices' stocks and barangay requests excluded?**
> CSWDO, CHO and GSO keep separate stock that CDRRMO can see only through a formal request, so cross-office visibility is future work. Barangays have their own 5% DRRM fund and are not expected to request from CDRRMO. The interview confirmed both.

*Why it's right:* Scope (pp. 14–15) and Requirements Gathering (p. 47) say so.

**A5. Does your system buy the supplies?**
> No. It recommends. The Head of Office approves the recommendation, and the General Services Office still runs the bidding under RA 12009.

*Why it's right:* Limitations (p. 15); Model Deployment (p. 66).

---

## B. Problem and novelty

**B1. What exactly is the problem?**
> The office plans each quarter's purchases from hand-kept counts with no written rule for how much extra to keep. It lacks three things: a planning basis from each item's own history under the budget, recommendations that show what was cut and why, and stock records that stay correct through outages and show later edits.

*Why it's right:* Research Problem (p. 4), word for word in substance.

**B2. What is your evidence that this is a problem?**
> The requirements interview on September 3, 2026, with three key informants: the head of the Administration and Training Division and two Logistics personnel. Logistics described the manual process, named counting errors as their most persistent problem, said shortages become known only when stock is checked, and said any tool must survive internet and power outages. I also photographed their ICS 213-RR form and logbook.

*Why it's right:* Chapter I (p. 2) and Requirements Gathering (pp. 46–47).
*Avoid:* saying the office "has shortages every typhoon". The paper states those effects only as possibilities (p. 3).

**B3. What is new in your study?**
> No single technique is new. The contribution is combining them in one system for a city DRRM office's quarterly supplies: planning each quarter's orders from its own short history within its budget, explaining every cut in words staff can act on, and keeping stock records that survive outages and show any later edit. None of the systems I reviewed documents all three together, and the study tests the system on the office's own records with a backtest.

*Why it's right:* the Synthesis (pp. 39–40) says "not a single new technique but a combination not documented in the reviewed systems."
*Avoid:* "No one has ever done this." Table 2's note says No and ND don't prove absence.

**B4. DSWD already has stockpile tools and PAHR. Why build another?**
> DSWD's tools plan the national stockpile using a 75% refill threshold and rainfall forecasts. A city office decides its own stock from its own records and its own budget. None of those tools plans a city office's quarterly purchases under its budget.

*Why it's right:* Chapter I (p. 1) and Related Systems (p. 29).

**B5. How is your system different from the Calamba City CDRRMO system (Yaneza et al., 2025)?**
> The Calamba system supports live response: hazard monitoring, incident reports, evacuation and resource tracking. Mine addresses supply readiness: what to keep in stock, what to buy with the budget, and whether the stock records can be trusted.

*Why it's right:* Related Systems (p. 31) makes exactly this contrast.

**B6. Is your technique new? Did you create a new algorithm?**
> No. Every technique already exists. Stage 1 sets each item's target to cover k of its last 8 quarters and solves a mixed-integer linear program within the budget (Hillier & Lieberman, 2021). Stage 2 uses sample average approximation over past quarters (Shapiro et al., 2021; Shehadeh & Tucker, 2022). The priority classes come from VED analysis (Gizaw & Jemal, 2021). The reasons and budget curve follow explainable operations research (De Bock et al., 2024). The records use add-or-subtract entries, a hash chain and an offline outbox (Almeida, 2025; Antonopoulos et al., 2021; Cuenca-Enrique et al., 2024). What is new is putting them together for a city disaster office and testing them on its own records.

*Why it's right:* the Synthesis (pp. 39–40) says the study adds "not a single new technique but a combination not documented in the reviewed systems", and each technique is cited in Chapter II and the Theoretical Framework (pp. 9–11).
*Avoid:* "I created a new algorithm", "My model is novel", or "No one has done this before." Say "none of the **reviewed** systems documents it"; you can only speak for what you reviewed.

**B7. Then why is this research, and not just software development?**
> Because it answers questions with evidence. RQ2 tests, in a backtest on the office's own quarters, whether planning from its history does better than its current practice, and RQ4 tests whether staff can use the explanations, each with a stated criterion. The system is the artifact; the research is the evaluation.

*Why it's right:* the research questions and Table 1 (pp. 4–7) give each question a procedure and a criterion, and the Research Design (p. 43) is design science: build the artifact, then evaluate it.
*Avoid:* "Because I built a system." Building alone is development; the tests make it research.

---

## C. Research questions and objectives

**C1. Why five research questions?**
> Each one matches one part of the study. RQ1 describes current practice, RQ2 tests the model, RQ3 tests the records and request module, RQ4 tests the explanations, and RQ5 measures acceptability. Table 1 maps each to one objective, one procedure and one criterion.

*Why it's right:* Table 1 (p. 7).

**C2. Why is the explanation-task target 80%?**
> It is the study's criterion for "staff can use the explanations", pooled across respondents. There is no earlier baseline for this kind of task, so it is a set criterion, not one taken from a past study.

*Why it's right:* Objective 4 (p. 6). Admitting there is no baseline is accurate.
*Avoid:* inventing a source for 80%.

**C3. Why "Very Acceptable" (4.21–5.00) as the criterion?**
> It is the top band of the standard five-point interpretation scale in Table 22, so the system is judged acceptable only if users rate each characteristic in the highest band.

*Why it's right:* Objective 5 and Table 22.

**C4. Why "a majority of backtested quarters" for RQ2?**
> With only four to eight backtested quarters, an average could hide a big loss in one quarter. Counting wins per quarter is simpler and more honest, and mixed results are reported as mixed.

*Why it's right:* Model Evaluation (pp. 61–62).

**C5. RQ1 asks about released vs requested. Why does that matter?**
> It shows how often the office could not fully supply a request, which is the shortage the model tries to reduce. It also tells us how much recorded use understates real need.

*Why it's right:* RQ1 (p. 4) and the Data limitation (p. 15) on understated need.

---

## D. Frameworks

**D1. Explain your theoretical framework.**
> Three theories, one per contribution. Optimization under uncertainty guides the model. Explainable decision support guides the reasons and the budget curve. Local-first computing, the add-and-subtract counter, and tamper-evident logging guide the offline records.

*Why it's right:* Theoretical Framework (pp. 8–10), Figure 1.

**D2. Do you measure situation awareness?**
> No. I use its three levels (perceive, understand, project) only as a guide for designing the dashboard. What I measure is whether staff can answer questions correctly in the explanation task.

*Why it's right:* p. 9 says this explicitly.

**D3. Do you implement CRDTs?**
> Only the idea of a counter that adds and subtracts, so entries give the same total in any order. Devices sync through the server, not with each other, so a full replication framework is not needed.

*Why it's right:* p. 10 and Chapter II (p. 27).

**D4. Explain your conceptual framework.**
> It is an input–process–output model. The inputs are the seven things the model needs: past use, current stock, prices, budget, depot space, VED class, and the coverage level and weight. The process is building the system with Scrum, and then each quarter: data checks, solve, reasons and budget curve, and the Head of Office's decision. The output is the system and the approved purchase plan, plus the evaluation results.

*Why it's right:* Conceptual Framework (pp. 11–12), Figure 2.

---

## E. Literature

**E1. Is your literature within five years?**
> Yes. All studies cited are from 2021 to 2026. The only older sources are the laws themselves (RA 10121 of 2010, the Data Privacy Act of 2012, and its 2016 implementing rules), because the law in force must be cited.

*Why it's right:* every reference in the list is from 2021 to 2026 except the three laws.

**E2. Why so few local studies?**
> Local studies apply optimization mostly to location and routing, such as Uichanco, De Veluz, Rico and Peralta. I included them and the local LGU systems, such as IRespondPH, E-LIGTAS, Sablayan and Calamba. The gap is that none of them plans a city office's quarterly purchases.

*Why it's right:* Chapter II (p. 20) and Related Systems (pp. 30–31).

**E3. Table 2 says other systems lack these features. How do you know?**
> I only compared what each system's published sources document. "No" means the sources describe the system and the feature is not there; "ND" means not documented. Neither proves the system lacks the feature, and the table note says so.

*Why it's right:* the Table 2 note (p. 32).

**E4. Where does VED come from?**
> VED (vital, essential, desirable) is a standard way to rank medical stores by how much harm an item's absence causes (Meena & Mathaiyan, 2024; Gizaw & Jemal, 2021). No reviewed study uses VED classes as weights inside a budget-limited ordering model, which is how I use them.

*Why it's right:* Chapter II (p. 22) and Parameter Preparation (p. 57).

---

## F. Data

**F1. Do you already have the office's data?**
> Not yet. The records are still being requested. For now the system runs on a sample dataset built from public sources, using real typhoon quarters and real prices but no office records. It shows that the procedure works; it is not evidence about the office. RQ1 and RQ2 will be answered from the office's own records.

*Why it's right:* Data Collection Methods (pp. 53–54) and the Data limitation (p. 16).
*Avoid:* calling it "public data" or "the office's data".

**F2. What if the records never arrive in time?**
> Then the backtest will be reported on the sample dataset as a demonstration of the procedure only, and the results will say so.

*Why it's right:* p. 54 states this exact plan.

**F3. Why 8 to 12 quarters?**
> The study period is Q3 2023 to Q2 2026, which is at most 12 quarters. The backtest needs at least 8: the first 4 are history, and the next 4 are tested.

*Why it's right:* Data Collection Methods (p. 53) and Model Evaluation (p. 61).

**F4. Is 8 to 12 quarters enough?**
> Enough to show a direction, not statistical proof, and the limitations say so. Typhoon quarters are reported one by one, and mixed results are reported as mixed.

*Why it's right:* Limitations (p. 15) and Model Evaluation (p. 62).

**F5. What if the office has data from 2015 or earlier?**
> The method works with any number of quarters. More quarters make the backtest stronger and allow a statistical test. I would keep a recent window for the targets, because items, prices and the office have changed, and use the long history for the rare typhoon quarters.

*Why it's right:* the model uses "the last 8 quarters" (p. 57), so it scales. This answer is your plan, not a claim in the paper.

**F6. Released quantities understate need when stock runs out. How do you handle that?**
> Recording requested quantities on the ICS 213-RR form reduces it. Quarters when stock ran out are marked, and every measure is computed with and without them.

*Why it's right:* Limitations (p. 15) and Model Evaluation (p. 63).

**F7. How do you make sure the encoded data is correct?**
> A second encoder checks every quarter against that quarter's stock sheet, and differences are resolved with Logistics. Possible duplicates are checked against the original form, and the system refuses to import the same file twice. Three data checks also run before every solve.

*Why it's right:* Data Collection Methods (pp. 54–55) and the data checks (p. 56).

**F8. What about personal data on the forms?**
> Names and signatures are not encoded, because the model needs the division, not the person. Borrower contacts in the system are visible only to the Admin and Logistics. This follows the Data Privacy Act of 2012.

*Why it's right:* p. 55 and Ethical Considerations (p. 95).

---

## G. The model

**G1. Explain your model simply.**
> Stage 1 gives each item one target, the amount that covered most of its past quarters, and buys toward the targets within the budget. Stage 2 has no single target: it treats every past quarter, including typhoon quarters, as a possible next quarter and minimizes the average shortage within the budget.

*Why it's right:* Model Building (pp. 58–60).

**G2. Why do you need both stages, and not just one? What is the difference?**
> Stage 1 is the simple version: one target per item from its own history, easy to explain, but it can leave out the rare typhoon quarter. Stage 2 keeps every past quarter, including the typhoon, and minimizes the average shortage. Neither is guaranteed to be better on the office's records, so RQ2 tests both, against the office's own orders and the flat 1.5 rule, on quarters they have not seen. The backtest result tells the office which one to use. Both are mixed-integer programs on the same inputs and solver, so the second stage adds little cost.

| | **Stage 1: per-item multiplier** | **Stage 2: scenario model** |
|---|---|---|
| Idea | One target per item: the amount that would have covered its use in **k of its last 8 quarters** (k set by the VED class) | **No single target**: each past quarter is treated as an equally likely next quarter |
| Minimizes | Weighted shortage below the targets, within budget and depot space | **Average** weighted shortage across all past quarters, within the same limits |
| Typhoon quarter | Can be left out by design (e.g., essential items cover 7 of 8 quarters) | Stays in as one scenario; the model decides how far to protect against it |
| Strength | Simple; one clear target per item to explain | Stronger when the budget is tight and big quarters matter |
| Weakness | Can miss the rare big quarter | Harder to explain; no single target |
| Source | Inventory theory; MILP (Hillier & Lieberman, 2021) | Sample average approximation (Shapiro et al., 2021) |

*Why it's right:* Applied Concepts (pp. 49–50) defines both stages; Model Building (p. 60) says a typhoon quarter "remains one of the S scenarios instead of being averaged away"; Table 12 and the text after it (pp. 65–66) show the flat rule beating Stage 1 from ₱250,000 up because Stage 1's targets "leave out by design" the typhoon quarter, while the scenario model is least or equal-least at every budget; RQ2 (p. 5) tests each "on past quarters it has not seen".
*Avoid:* "Stage 2 is always better" (Table 12 is illustrative and in-sample; the evidence is the backtest), or "Stage 1 is useless" (it is the explainable baseline).

**G3. Why optimization, and not machine learning or forecasting?**
> The study covers at most twelve quarters per item, from Q3 2023 to Q2 2026, and some items will have fewer. Machine learning needs much longer records (Cerqueira et al., 2022), while deciding directly from past data works with few observations (Besbes & Mouchtaki, 2023). And this is a decision about how much to buy under a budget, which is an optimization problem, not a prediction problem.

*Why it's right:* Chapter II (pp. 21, 33), Algorithm Analysis (p. 51), and the Scope (p. 13), which sets the period Q3 2023 to Q2 2026. The records are not yet released, so this states the study's window, not a count from the office's records.
*Avoid:* "The office has only 8 to 12 quarters per item." You have not seen the records yet. If asked, say: "Not yet; the records are still to be released. Items with fewer than four quarters use the flat 1.5 rule and are marked limited history (p. 17)."

**G4. Why MILP?**
> Orders come in whole units, and the budget and depot space are linear limits, so the problem is a mixed-integer linear program.

*Why it's right:* p. 59.

**G5. Why PuLP and CBC?**
> PuLP is a Python modeling library, and CBC is the open-source branch-and-cut solver it uses by default. Both are free, so the office pays no license.

*Why it's right:* Applied Concepts (pp. 49–50).

**G6. Where do the weights 3, 2 and 1 come from?**
> They are the study's proposal. Logistics proposes them, and the Head of Office will confirm them in the system before the first run; the system records the position and the date. They are fixed before the backtest, and a rerun with equal weights checks their effect.

*Why it's right:* Parameter Preparation (pp. 57–58) and Table 9.
*Avoid:* claiming a study proved 3/2/1.

**G7. Why 8 of 8, 7 of 8 and 6 of 8 quarters?**
> Vital items must cover every recent quarter, essential items all but the worst, and desirable items all but the two worst. It is the study's proposal, which the Head of Office will confirm before the first run and can change in the system.

*Why it's right:* Table 9 (p. 58), with the note "the study's proposal".

**G8. What is weighted shortage?**
> How short each item falls, times its priority weight, summed over items. In the model, each shortfall is divided by the item's target. In the backtest, it is divided by average use, so all rules are judged on one scale.

*Why it's right:* Definition of Terms (p. x) and p. 59.

**G9. In the worked example, why are food packs left 42% short?**
> The objective funds first the items that close the most shortage per peso. Food packs cost the most, so with equal weights they are funded last. The priority weights are the office's control: if food packs were given the Vital weight of 3, they would be funded first.

*Why it's right:* Worked Example (p. 64).

**G10. What about lead time, expiry and individual depots?**
> They are limitations. Orders are assumed to arrive before the quarter starts, expiry is not modeled, and depot space is one pool. The paper states all three.

*Why it's right:* Limitations, Model (p. 16).

**G11. Every past quarter is treated as equally likely, but you know the season. Isn't that wrong?**
> It is a stated limitation. Treating quarters as equally likely is the standard sample average approximation, and with so few quarters, splitting them by season would leave too few scenarios. The paper names it as a limit.

*Why it's right:* Limitations (p. 16) and Shapiro et al. (2021) on SAA.

**G12. What about an item with little or no history?**
> An item with fewer than 4 quarters uses the flat 1.5 rule and is marked "limited history". An item with no recorded use or no price gets no computed order and is listed for Logistics to set manually.

*Why it's right:* the cold-start rule (pp. 16 and 58).

**G13. What if the solver fails or finds no answer?**
> The run is marked failed, the error is shown, and the Head of Office runs it again. Only a completed run can be approved, so a failed result never becomes a purchase plan.

*Why it's right:* Model Deployment (p. 66).

**G14. How do you know the solution is optimal?**
> CBC stops within 0.01% of the optimum or at a set time limit. If it stops early, the item's reason says "partly covered: the solver stopped early", so the user knows.

*Why it's right:* Algorithm Analysis (p. 52) and Explanation Layer (p. 60).

**G15. What is the budget curve?**
> The system re-solves the model at 11 budget levels, from 50% to 150% of the entered budget, and plots the shortage at each. It shows what more money would buy. The curve must never rise as the budget rises.

*Why it's right:* Explanation Layer (p. 61) and Algorithm Analysis (p. 52).

**G16. How are the reasons produced? Are they written by AI?**
> No. Each reason is read from the solved model by checking which limit stopped the item from reaching its target: budget, depot space, or current stock already enough. It is not written by hand or generated afterward.

*Why it's right:* Explanation Layer (pp. 60–61); Chapter II (p. 23) calls it "interpretable by design".

---

## H. Evaluation (backtest)

**H1. What is a rolling-origin backtest?**
> For each quarter from the 5th onward, the system plans using only the quarters before it, then compares the plan with what was actually used. Every rule is tested on quarters it has not seen.

*Why it's right:* Model Evaluation (p. 61) and Hewamalage et al. (2023).

**H2. Why compare five rules?**
> The office's actual orders are the main comparison, because they are current practice. The flat 1.5 rule stands for a fixed factor used by habit. Safety stock is the textbook method. The multiplier and the scenario model are the two forms of my model.

*Why it's right:* pp. 61–62.

**H3. What if the office's actual orders were not recorded?**
> Comparisons with actual orders use only the quarters whose orders are recorded. For the other quarters, the flat 1.5 rule is the comparison.

*Why it's right:* p. 62.

**H4. What if your model loses the backtest?**
> Then I report it. The objective is to determine whether either form does better, not to prove that it does. Mixed or negative results are reported quarter by quarter, and the system's other contributions, the records and the explanations, are tested separately.

*Why it's right:* Objective 2 says "to determine whether" (p. 6), and p. 62 says mixed results are reported as mixed.
*Avoid:* "It will definitely win."

**H5. Couldn't you tune the weights until your model wins?**
> No. The weights will be confirmed by the Head of Office and fixed before the backtest runs. They are not changed after the results are seen. The equal-weights rerun is a sensitivity check, not a way to choose weights.

*Why it's right:* p. 58.

**H6. Table 12 shows the multiplier losing at higher budgets. Isn't that bad?**
> It is honest. At higher budgets, the flat rule's higher targets partly cover the typhoon quarter, which the multiplier leaves out by design. That is exactly why Stage 2 exists, and the scenario model was best or tied at every budget. The table is illustrative and in-sample; the real evidence will be the backtest on the office's records.

*Why it's right:* text after Table 12 (pp. 65–66).

**H7. What statistics will you use?**
> For the backtest: per-quarter differences and the number of quarters each rule wins, with typhoon quarters shown separately. For the survey: weighted mean and standard deviation per characteristic. For the explanation task: the percentage of correct answers. Division results are descriptive only, because 3 to 5 people per division are too few for significance tests.

*Why it's right:* Statistical Treatment (p. 94).

---

## I. The system

**I1. Explain your architecture.**
> Five parts. A progressive web app in the browser with an offline outbox. A Laravel backend for requests, approvals and sync. PostgreSQL for the stock entries and the hash chain. A separate FastAPI solver service with no database access. Reverb for live updates.

*Why it's right:* System Architecture (pp. 71–72), Figure 5.

**I2. How does it work offline?**
> Entries made offline wait in the device's IndexedDB outbox, each with a device-made ID. When the connection returns, they sync as one batch: priority entries first, and dependent entries after the entry they depend on.

*Why it's right:* pp. 26–27 and System Architecture.

**I3. What if two devices change the same item offline?**
> Each saves a plus or minus entry, not a new total, so both count. From 100 units, releases of 10 and 5 give 85 in any order. If an entry would push stock below zero, it is kept and flagged for Logistics, because the goods already moved.

*Why it's right:* Chapter II (pp. 25–27).

**I4. How do you prevent double counting?**
> Every entry has a device-made ID, and the database has a unique index on it. If the same entry is uploaded twice, the second copy is ignored.

*Why it's right:* p. 26 and Figure 10.

**I5. Why a hash chain, and not blockchain?**
> Blockchain helps when many organizations must trust each other. Here one office holds the data, so a hash chain in its own database already makes edits visible (Antonopoulos et al., 2021).

*Why it's right:* Chapter II (p. 26).

**I6. Can the admin cheat by rebuilding the chain?**
> Someone with full database access could. That is why the latest fingerprint is printed on every quarterly report. A rebuilt chain would no longer match the printed value, and COA can compare them.

*Why it's right:* Limitations (p. 17); the hash chain section (p. 27).

**I7. Does the hash chain stop wrong entries?**
> No. It detects a later edit or deletion of a stored entry. It does not stop someone from entering a wrong figure in the first place. That is what the weekly counts and the data checks are for.

*Why it's right:* p. 27 states this limit.

**I8. Why a web app (PWA) and not a mobile app?**
> One codebase serves the office's Windows laptops and Android phones. A PWA can be installed and works offline (Cuenca-Enrique et al., 2024).

*Why it's right:* Chapter II (p. 24) and Software Tools (p. 69).

**I9. Why run it on the office server and not the cloud?**
> The office needs it to keep working during internet outages, and its data stays on its own premises. Devices outside the office connect through a VPN, all traffic is HTTPS, and a UPS powers the server.

*Why it's right:* the deployment and hardware sections (pp. 70–72) and the interview requirement.

**I10. What are the browser limits?**
> Background Sync works only in Chromium browsers, so the app also retries whenever it opens or reconnects. Service workers need HTTPS. Safari can clear site data after seven days without use, but Home Screen apps are exempt. The office uses Chrome or Edge.

*Why it's right:* Limitations (p. 16), citing MDN and WebKit.

**I11. What if the Head of Office is absent?**
> Only an Admin account can approve, so the Head of Office assigns an acting Admin for absences. It is stated as a limitation.

*Why it's right:* p. 16.

**I12. How does the ICS 213-RR module work?**
> It digitizes the office's own form. Approval reserves stock, release deducts it through stock entries, and the requested and released quantities are both kept. Borrowed items follow issued, returned, for repair, condemned and surrendered.

*Why it's right:* Scope (pp. 13–14); COA Circular 2022-004 for the statuses (p. 28).

**I13. Does the system replace the paper forms and COA property records?**
> No. It digitizes how the office tracks them. It does not replace the signed forms or the official procurement and property records, and records in the system do not by themselves prove compliance.

*Why it's right:* Legal and Accountability Basis (pp. 28–29).

---

## J. Testing and user acceptance

**J1. What tests will you run?**
> Unit tests (13 cases), integration tests (11), offline resilience and integrity tests (13, three runs on the office server with two laptops and two phones), performance tests (4), and user acceptance testing (6 tasks, the explanation task, and the survey).

*Why it's right:* Table 16 and Tables 17–21.

**J2. Why only five ISO/IEC 25010 characteristics?**
> Studies usually evaluate the characteristics that fit the system (Rojas et al., 2025). I chose the five tied to what this system must do: functional suitability, reliability, interaction capability, security, and safety.

*Why it's right:* the instrument section in Chapter III, as the panel previously asked.

**J3. How is the questionnaire validated?**
> Three IT faculty check face and content validity against the ISO definitions. Then it is pilot-tested with CDRRMO personnel who will not hold accounts, and internal consistency is reported as Cronbach's alpha, with 0.70 or higher acceptable.

*Why it's right:* Validation of the Questionnaire (p. 94).

**J4. Only about 20 respondents. Isn't that too small?**
> That is the whole user population, so it is total enumeration, not a sample. The results are descriptive, and the paper does not run significance tests on small groups. The number is an estimate still to be confirmed with the office's staff list.

*Why it's right:* Respondents (p. 45) and Statistical Treatment (p. 94).

**J5. You built the system. Won't respondents be biased toward you?**
> The survey is anonymous, and someone other than me gives it. Each item asks about a task the respondent actually did, not general satisfaction.

*Why it's right:* the instrument section in Chapter III.

**J6. Users rate reliability and security. Can users really judge those?**
> Their ratings are reported as perceptions. Reliability, security and safety are also tested technically in the offline and integrity tests, and the two are reported separately.

*Why it's right:* the UAT and testing sections; the earlier panel comment was answered this way.

**J7. What is the explanation task?**
> The Head of Office, Logistics, and Research and Planning see three recommendations and answer three questions about each: why an item was cut, what the next ₱10,000 would buy, and which limit mattered. The target is at least 80% correct, pooled.

*Why it's right:* User Acceptance Testing Procedure (pp. 90–91).

---

## K. Methodology and development

**K1. What is your research design?**
> A developmental design with descriptive-evaluative and comparative parts, based on design science: build the artifact, then evaluate it on the real problem. It fits because the study must build a system and show whether it works for the office.

*Why it's right:* Research Design (p. 43).

**K2. Why Scrum when you are the only developer?**
> Scrum is commonly adapted, including in student projects. I am the development team and Scrum Master, the head of Logistics is the Product Owner, and each sprint ends with a review of the working part.

*Why it's right:* System Development Methodology (p. 67).

**K3. How long is development?**
> Six two-week sprints. Sprints 1 to 4 build the contributions, Sprint 5 integrates requests and the dashboard, and Sprint 6 is testing and evaluation.

*Why it's right:* Table 13 (p. 68).

**K4. Who were your key informants, and why only three?**
> The head of the Administration and Training Division and two Logistics personnel, chosen by purposive sampling because they are the people who handle the office's supplies, requests and approvals. The division head was the highest-ranking official available during the office's restructuring, and not every staff member could be interviewed because of their duties. I checked what they said against the office's own ICS 213-RR form and logbook, and a follow-up interview with the Research and Planning Division is planned.

*Why it's right:* Respondents and Requirements Gathering (pp. 45–47). Key informants are chosen for their role and knowledge, not for numbers, and the requirements are confirmed by the office's documents and will be reviewed by the head of Logistics and confirmed by the Head of Office.
*Avoid:* "I only interviewed three because the others were busy." Lead with why these three are the right people.

---

## L. Ethics and law

**L1. What laws apply?**
> RA 10121 for the DRRM fund, RA 12287 for anticipatory action, RA 12009 for procurement, COA Circular 2022-004 for semi-expendable property, and the Data Privacy Act of 2012 for borrower data.

*Why it's right:* Legal and Accountability Basis (pp. 27–28).

**L2. What ethical steps will you take?**
> Written permission from the Head of Office, informed consent with the right to withdraw, no names or signatures encoded, borrower details limited by role, and data returned or deleted after the study. The survey and consent form will be reviewed before use.

*Why it's right:* Ethical Considerations (p. 95).

---

## M. Hard "what if" questions

**M1. What is your contribution to IT?**
> An integrated, explainable decision-support system for a city disaster office that plans with optimization from small datasets and keeps offline-safe, tamper-evident records, plus a backtest design other offices can repeat.

*Why it's right:* the Synthesis (p. 40) and Significance (p. 18).

**M2. Who will maintain the system after the study?**
> It runs with Docker on the office's own server using open-source tools, so there is no license cost. Handover with documentation and training for Logistics would be part of deployment. I would include it as a recommendation.

*Why it's right:* the tools and hardware sections. **Note: the handover plan is your answer, not text in the paper.**

**M3. What if the office doesn't follow the recommendation?**
> That is allowed. The system recommends, and the Head of Office decides. Every run, with its inputs and who approved or rejected it, is kept, so the decision and the reasons are on record.

*Why it's right:* Model Deployment (p. 66) and Figure 12.

**M4. What will you do with the panel's suggestions?**
> I will note each one in a revision matrix, revise the paper, and submit the revised version before the next stage, as I did after the last review.

*Why it's right:* this is how you handled the November 8 panel review (the revision matrix).

---

### Five lines to remember
1. "The study recommends; the Head of Office decides."
2. "It is a combination not documented in the reviewed systems, not one new technique."
3. "Eight to twelve quarters show a direction, not statistical proof."
4. "The sample dataset shows the procedure works; it is not evidence about the office."
5. "The weights will be confirmed and fixed before the backtest."
