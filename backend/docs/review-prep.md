# DisasterResource+ — reviewer study guide

Written 2026-10-03 against the live paper (87 pages, 10 tables, 4 figures). Read parts 1–3 to know the paper, then answer part 5 out loud. Part 6 is the new gap list: fix what you can, and rehearse answers for the rest.

---

## 1. The paper in one minute

CDRRMO San Pablo City plans its supplies by hand: ICS 213-RR forms, a borrowing logbook, Excel sheets backed by paper. Logistics named counting errors as the biggest problem (interview, 3 Sept 2026). DisasterResource+ is a web system that does three things for one city office:

1. **Plans each quarter's replenishment** under the budget from the office's own past quarters, keeping typhoon quarters as separate scenarios instead of averaging them away.
2. **Explains every recommendation**: a reason per item and a budget curve showing what more money buys.
3. **Keeps stock records that survive outages and expose edits**: additive stock entries with device-made IDs, ordered offline sync, conflict flags and a hash chain.

Around that sits an ICS 213-RR request module with a borrowed-equipment lifecycle. The Head of Office (the system **Admin**) approves everything. The evidence plan is a rolling-origin backtest on the office's records, offline and integrity tests, and an ISO/IEC 25010:2023 survey of staff.

**Your honest framing:** it's applied design science. The pieces exist in the literature; the combination for one LGU office, tested on its own data and users, was not found (Tables 1–2).

---

## 2. Numbers to have in your head

| Topic | Fact |
|---|---|
| Context | 19–20 tropical cyclones enter PAR yearly, ~9 make landfall (Galloway 2025). RA 10121: ≥5% of revenue to the local DRRM fund, 30% of it quick response, 70% prevention/preparedness |
| San Pablo | Kristine + Leon, Oct 2024: 46 of 80 barangays hit; 811 families (3,330 persons); 629 in 41 evacuation centers, 182 outside |
| DSWD | call-off request when stock falls below 75% of the mandated level; PAHR forecasts food packs from rainfall |
| Interview | 3 Sept 2026, Filipino, recorded; an officer + Logistics personnel; follow-up with Research and Planning is *planned* |
| Data | Q3 2023 – Q2 2026, up to 12 quarters; ≥8 needed; backtest starts at the 5th quarter, so 4–8 backtested quarters; Kristine/Leon is the 6th |
| Model | M = 8 recent quarters; covering level = use that would have covered k of 8. Vital k=8, weight 3; Essential k=7, weight 2; Desirable k=6, weight 1. Multiplier = covering level ÷ average use |
| Backtest rules | 5: office's actual orders, flat 1.5, classical safety stock (average use + 1.65 sd, 95% service level), per-item multiplier, scenario model. The safety-stock rule is a comparison only (not part of the win-count target). Fill rate reported next to weighted shortage; comparison repeated with equal weights |
| Cold start | < 4 quarters → flat 1.5 rule, marked "limited history"; no use at all → listed for Logistics to set |
| Data checks | (1) use > 3× the item's usual (median) with no event tag; (2) stock < 0; (3) stock ≠ weekly physical count |
| Explanation | 4 reasons: fully covered / partly covered: budget ran out / limited by depot space / not ordered: stock already covers it. Budget curve = 11 levels, 50–150% in 10% steps. "Next ₱10,000" view |
| Solver | PuLP 2.8.0 + CBC, MIP gap 0.01% |
| Worked example | avg use 320/200/400/85; stock 150/60/250/200; costs ₱500/350/150. Flat-1.5 targets 480/300/600/128 cost ₱301,500 |
| At ₱200,000 | Least-total-shortage: 127 food, 240 hygiene, 350 water, food 42% short, spends all. Even spread: 218/170/210, all 23% short. Same-% cut: 218/159/232, hygiene 27% short, water 20%, spends ₱199,450. Next ₱10,000 = +20 food packs (42% → 38%) |
| Table 7 | flat / multiplier / scenario: 150k 0.88/0.68/0.68; 200k 0.53/0.50/0.46; 250k 0.37/0.41/0.28; 300k 0.29/0.41/0.15; 350k 0.29/0.41/0.07. Reproduced by `solver-service/tests/test_table7.py` |
| Offline test | 2 laptops + 2 Android phones, airplane mode, 30 scripted entries (5 priority, 3 two-device conflicts), 3 runs; Safari/iPhone separately |
| Four extra tests | triple upload → 0 duplicates; dependent return syncs after its issue; over-stock approvals raise a flag; edited DB row makes "check records" fail at that row |
| Performance | targets: Stage 2 < 2 s (200 items × 12 scenarios), curve < 20 s, dashboard update < 1 s. **Measured on the dev machine:** 0.56 s, 10.2 s (budget curve), Stage 1 0.04 s |
| Survey | 15–20 respondents, ≥3 per division, purposive; Cronbach α ≥ 0.70; 5-point scale; "Very Acceptable" = 4.21–5.00 |
| Targets | O2 beat actual orders (where recorded) and flat 1.5 in a majority of backtested quarters; O3 100% saved, 0 duplicates, 0 losses; O4 ≥ 80% correct on the explanation task; O5 each of 5 ISO characteristics "Very Acceptable" |
| ISO 25010:2023 | 9 characteristics; you test 5: functional suitability, reliability, interaction capability, security, safety |
| Scrum | 6 two-week sprints |

---

## 3. Chapter by chapter: the argument and the support

**Chapter I.** Problem → three weaknesses (fixed rule averages away typhoons; cuts can't be defended; paper/offline records double-count or lose, and edits leave no trace) → five questions ↔ five objectives, one to one. Theory has three legs: optimization under uncertainty (LP, safety stock, stochastic programming and sample average approximation), explainable decision support (De Bock 2024, situation awareness), and local-first computing with tamper-evident logging (CRDTs, hash chains). IPO framework. Scope is CDRRMO-held resources only; no ICS command structure; no barangay requests. Limitations are grouped into four bullets (data and inputs, hash-chain limit, connectivity including the missing off-grid link, model limits).

**Chapter II.** Foreign and local review, in the same three legs, plus legal basis and related systems. Shariati et al. (2026), 97 studies, say prepositioning models remain underused. Rodríguez-Espíndola (2023), 105 studies, found only 10% involved practitioners. Closest Philippine precedent: Uichanco (2022). Closest system: Yaneza et al. (2025), LSPU, Calamba CDRRMO (live response, not supply readiness). Table 1 compares features; "ND" means not documented, "No" means the sources describe the functions and the feature isn't there. Three gaps → Table 2.

**Chapter III.** Design science. Two-stage model (per-item multiplier MILP, then scenario model). Explanation layer. Backtest. Worked example (Tables 5–7). Scrum, tools, hardware, architecture. Testing: unit, integration, offline, performance, UAT. Ethics.

### The model in plain words
- **Stage 1.** Each item's target is its covering level. Choose whole-number orders that minimize the priority-weighted shortfall below target, as a share of target, without exceeding the budget (or depot space).
- **Stage 2.** Treat each past quarter as an equally likely next quarter and minimize the average weighted shortage across them, in quarters of average use. The typhoon quarter stays in as one scenario, and the model decides how much to protect against it given the budget.
- **Why the budget changes the answer (Table 6).** The least-total-shortage objective funds the cheapest big gaps first, so an expensive item can end up far short. The weights are the office's control over priority.
- **What Table 7 says.** The multiplier beats flat 1.5 only when money is tight (₱150k–₱200k), because it funds the cheapest gaps first instead of cutting everything by the same percentage. From ₱250k the flat rule wins because its higher targets partly cover the typhoon quarter, which the multiplier leaves out by design. The scenario model is best or tied everywhere. All in-sample and illustrative; the backtest is the real evidence.

---

## 4. Glossary

- **Covering level (L):** the use that would have covered k of the last M quarters (the (M−k+1)-th highest).
- **Per-item multiplier:** L ÷ average quarterly use.
- **SAA (sample average approximation):** treat observed outcomes as equally likely scenarios and choose the decision with the best average.
- **VED:** vital / essential / desirable ranking (Gupta et al., 2007).
- **Fill rate:** share of actual use covered by stock + order.
- **Weighted shortage:** shortfall in quarters of average use × priority weight, summed.
- **Hash chain:** each stock entry's fingerprint (SHA-256) covers its contents plus the previous fingerprint, so editing or deleting an old entry breaks the chain from there.
- **CRDT / additive entries:** record quantities added or removed, not totals, so entries merge in any order to the same total.
- **Device-made ID:** unique ID created offline; the server's unique index makes a repeated upload a no-op.
- **Rolling-origin backtest:** for each quarter from the 5th, build the order from earlier quarters only, then score against that quarter's actual use.

---

## 5. Questions a reviewer is likely to ask

Honest answers beat clever ones. "Concede" marks a point where you should admit the limit first.

### Problem and novelty
1. **What is new?** The combination: budget-limited replenishment planned from the office's own past quarters as scenarios, per-item reasons with a budget curve, and offline-safe tamper-evident records, for one city office, evaluated on its own data and staff. Concede: no new algorithm; each part exists elsewhere.
2. **Isn't the multiplier just an empirical quantile or base-stock level?** Yes, on purpose. The contribution is using it under a budget with explanations and testing it against the office's practice.
3. **Why not forecast (ARIMA, Croston)?** Demand is lumpy, there are 8–12 quarterly points, and a point forecast still needs a variance and service target for safety stock (Petropoulos 2022; Pinçe 2021). Scenarios use the data directly. The backtest now includes the textbook safety-stock rule (average use + 1.65 standard deviations) as a comparison. Concede: there is no Croston/ARIMA forecasting baseline.
4. **Why is a readiness system called "disaster response"?** The paper defines it as keeping supplies counted, stocked and accounted for so they're ready when a response begins; live incident operations are out of scope (the interview agreed).
5. **Why only CDRRMO-held stock?** The interview advised it; CSWDO, CHO and GSO keep separate stock that CDRRMO sees only by formal request.
6. **Why not blockchain?** One office holds the data, so a hash chain in its own database gives tamper evidence without a network (Antonopoulos 2021). Blockchain's value in humanitarian supply chains is multi-organization visibility (Baharmand 2021; Hunt 2022).

### Method
7. **Does the office record a quarterly budget and its actual orders?** Not confirmed. The interview described quarterly stock review and a yearly data request, not budgets. The paper's fallback: actual orders come from delivery inspection documents, and where no budget was recorded, use the cost of the order actually placed. The follow-up interview with Research and Planning is meant to settle this. Concede.
8. **Four to eight backtested quarters and one typhoon: is that evidence?** It shows direction, not proof (stated limitation). Report per-quarter differences and wins, with typhoon quarters as cases.
9. **You optimize weighted shortage and then judge by it. Circular?** Partly. The paper reports fill rate next to weighted shortage, plus items out, budget used and leftover stock, and repeats the comparison with equal weights to check the VED weights. The weights themselves come from VED and are confirmed by the Head of Office before the backtest.
10. **Why 1.5?** It stands for a fixed factor applied by habit; no written formula was described. It's the comparison where actual orders are missing. Concede: it's a modeling assumption.
11. **How were weights and k chosen?** Proposed with Logistics, confirmed by the Head of Office (date and position recorded) before the backtest. Chapter II now reviews VED: it is an established way to rank medical supplies by criticality (Gupta et al., 2007; Meena & Mathaiyan, 2024), recent studies assign the classes by expert consultation (Gizaw & Jemal, 2021, Ethiopian supply hub; Zekioğlu et al., 2026, Turkish emergency health station), and Ayalew et al. (2025) combine it with multi-criteria analysis. Concede: none of those uses VED as weights and coverage levels in a budget-limited model, and the numbers 3/2/1 and 8/7/6 are this study's proposal. Gizaw and Jemal also note VED analysis cannot capture deliberate emergency stockpiling.
12. **Why equal-likelihood scenarios?** That's SAA with the office's own history. Concede: it ignores that the coming season is known (now stated under "What the model ignores"), and with 12 quarters the SAA guarantees don't apply; the backtest is what validates it.
13. **What about lead time and expiry?** Out of scope: orders are assumed to arrive before the quarter starts. The interview gave a 1–3 year expiry buffer. Stated limitation.
14. **Recorded use understates need when stock ran out.** Stated limitation; those quarters are marked and requested quantities reduce it.
15. **How is a typhoon quarter defined?** A quarter Logistics tags with a typhoon in `event_tag`. Concede: the paper doesn't give a rule beyond the tag.
16. **Why does Stage 1 divide by target but the backtest by average use?** Stage 1's target is what the rule aims at; the backtest judges every rule on one scale. The paper says so.

### System
17. **How does sync avoid duplicates and losses?** Device-made IDs with a unique index, additive entries, priority-first ordered sync, per-entry validation (one bad entry never blocks the batch). Tested with 30 scripted entries × 3 runs plus four targeted tests.
18. **Two devices release the last units. Negative stock?** The entries are kept and flagged as a conflict for Logistics, never dropped. Bounded counters (Borrego 2025) are the alternative; you chose flagging.
19. **Can the hash chain be defeated?** Someone with full DB access could rebuild it. Mitigation in the paper: the latest fingerprint is printed on each quarterly report. In the system, a nightly check also saves a checkpoint and emails the Admins on failure (that's in the code, not the paper).
20. **What happens in a long outage?** The PWA keeps working and queues entries; they sync when a connection returns. There's no off-grid hardware (stated limitation). Digital → Excel → paper backups stay as the office's chain.
21. **Why Laravel + React + a separate Python solver?** The solver has no database access and can be updated on its own; PuLP/CBC are open source.
21a. **Device clocks can be wrong. How can sync be "ordered"?** The outbox orders by priority, then the device's creation time, then dependencies. Entries only add or subtract quantities, so a skewed clock changes the syncing order, not the totals; the server chains entries in the order it accepts them (stated in the Figure 4 paragraph).
21b. **What if the server fails, and how do devices reach it over HTTPS?** Deployment plan in the Hardware paragraph: nightly database backup on a separate drive, HTTPS with a certificate the office's devices trust, outside devices through a VPN. Concede: this is a plan; confirm the office's actual setup (certificate, VPN) before the defense.
21c. **The Head of Office is the only approver. What if they're away?** Only an Admin account can approve, so the Head assigns an acting Admin account for absences (stated under Limitations).

### Evaluation
22. **Who are the respondents, and isn't the developer running the survey?** Purposive, 15–20, ≥3 per division. Responses are anonymous and someone other than the developer, such as the adviser, gives the survey. The adviser and panel review the survey and consent form before use. Concede that formal ethics clearance, if your university requires it, is not yet stated.
23. **Why those five ISO characteristics?** Each ties to a contribution: suitability, reliability (offline), interaction capability (explanations), security (hash chain, roles), safety (fail safe).
24. **Why 80% on the explanation task?** A target, not a benchmark. Concede there's no baseline (see the open list).
25. **Is the instrument valid?** Three IT faculty review it, a pilot with non-sample staff, Cronbach α ≥ 0.70.
26. **Performance?** Measured 0.56 s for Stage 2 with 200 items × 12 scenarios (target 2 s) and 10.2 s for the full budget curve (target 20 s) on the development machine. The dashboard-update time on the office network is still to be measured.

### Trap questions
27. **"Your own Table 7 shows the multiplier losing to flat 1.5 at ₱250k+."** True, and the paper says why. The multiplier is the simple, explainable form; the scenario model is the full form and wins everywhere. The backtest decides what each step adds.
28. **"Your worked example uses food packs and water. Does CDRRMO stock those?"** The examples are illustrative; the interview lists medical, cleaning, office and rescue items, and relief food belongs mostly to CSWDO/DSWD. The paper now says the items are relief goods chosen for easy comparison, not CDRRMO stock, and will be replaced with the office's own items once the data are encoded.
29. **"Why does the title say visualization?"** The dashboard shows stock, use, reasons and the budget curve; you were told to keep the title.
30. **"Is the system built?"** Know exactly what is done (system and tests) versus planned (data encoding, backtest results, survey). The paper says it is not yet in use at the office.

---

## 6. Gaps — what a reviewer could still find

### Found on 2026-10-03 and what was done
| # | Gap | Status |
|---|---|---|
| N1 | No classical baseline in the backtest | **Fixed.** Paper: five rules, including a safety-stock rule (average use + 1.65 sd, 95% service level; Jacobs & Chase, 2024). System: `safety_stock` rule in the solver backtest, a test and a UI label; spec §4.8 updated |
| N2 | Evaluation measure matches the model objective | **Fixed in the paper.** Fill rate reported next to weighted shortage; comparison repeated with equal weights to check the VED weights. Still to *run* (needs the data) |
| N3 | Seasonality ignored | **Stated as a limitation** under "What the model ignores" (not modeled) |
| N4 | Sync order uses device clocks | **Fixed.** One sentence in the Figure 4 paragraph |
| N5 | No server backup, HTTPS-on-LAN or remote-access plan | **Added as a deployment plan** in the Hardware paragraph (nightly backup on a separate drive, HTTPS with a trusted certificate, VPN). It is a plan, not something the repo implements; confirm the office's real setup |
| N6 | Single approver | **Stated** under Limitations: only an Admin account approves; the Head assigns an acting Admin for absences |
| N7 | Worked example items are not CDRRMO stock | **Clarified** in the Worked Example intro (relief goods chosen for easy comparison, not CDRRMO stock) |
| N8 | Developer administers the survey | **Mitigated in the paper:** anonymous responses, someone other than the developer gives the survey |
| N9 | No ethics review mentioned | **Partly:** the adviser and panel review the survey and consent form before use. Add formal ethics clearance if your university requires it |
| N10 | Offline test is small | **Added** a three-day offline run to the offline test plan |

### Still open from earlier
- Whether the office actually records a quarterly budget (answers items 7 and the backtest design).
- The office's headcount, so "15–20 respondents" has a population.
- No baseline for the 80% explanation target.
- The history import template has no received-quantity column (paper wording is fine; this is a code follow-up).
- No appendix (you chose to defer it).

---

## 7. Before you present

1. Know which sentences are plans and which are done.
2. Decide a short answer for the title (item 29), the typhoon-quarter rule (15) and the budget question (7).
3. Be ready to open `solver-service/tests/test_table7.py` and `test_replenishment.py` if asked "how do I know your numbers?".
4. Practice the one-minute pitch (part 1) and the Table 6 story (why the least-total-shortage plan leaves food 42% short).
