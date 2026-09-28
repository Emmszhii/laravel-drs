# DisasterResource+: Where a Real Technical Contribution Can Come From

Prepared September 27, 2026, to answer your question: what technology can you offer within the current scope, since what you have now doesn't look new?

These are notes to help you choose. They are not chapter text. Write the chapters in your own words, and read any source before you cite it.

---

## The short answer

- **You're right to worry.** Your current novelty claim is that no one has put an LP model, an offline PWA, and an ISO/IEC 25010 evaluation together for a city DRRM office. That's a combination claim, and your own Synthesis already says each piece is "well studied and proven on its own." A panel will ask what is new in any single piece.
- **Much of your system already exists elsewhere.** The research check (Section 1) found these in DSWD and international tools:
  - tracking relief goods;
  - expiry dates;
  - barcodes;
  - stock dashboards;
  - reordering when stock falls below a set level;
  - prepositioning guided by typhoon forecasts.

  A 2025 study from LSPU built a CDRRMO system for Calamba City with GIS, machine learning, and an ISO 25010 evaluation.
- **Five things turned up in no system checked:**
  - budget-limited replenishment optimization in a working tool (it exists only in research);
  - planning stock against several possible quarters;
  - explaining why a recommendation came out the way it did;
  - stock records that show when someone edits them later;
  - automatic checks on the data before the model uses it.

  Offline sync that merges stock changes safely is rare too. Only Sahana Eden documents its sync conflict rules, and those keep one copy of a changed record.
- **Recommendation:** make the contribution come from the model and the stock records you already have, not from new modules:
  1. Plan stock against the office's own past quarters, typhoon quarters included, instead of 1.5 × the average (Option 1).
  2. Explain each recommendation in pesos: why an item was cut and what more money would change (Option 2).
  3. Keep stock records that can't double-count after an outage and that show if someone edits them later (Option 3).
- **Scope:** all three stay inside it. There are no maps and no new user groups, and the stack stays Laravel, React, FastAPI, and PuLP.
- **If time is tight:** do these three, which take roughly two weeks and still give you three claims you can test:
  - Option 1's per-item multiplier (the simple fallback);
  - Option 2's budget curve;
  - Option 3's sync-order fix, plus stock entries with the hash chain.

---

## 1. What already exists (so it can't be your novelty)

| Feature | Who already has it | New for you? |
|---|---|---|
| Tracking relief goods from receipt to release | DSWD's RGIMS, built on Sahana Eden (2012–2013); WFP LESS; PAHO LSS/SUMA | No |
| Expiry dates, first-expiry-first-out, near-expiry flags | Sahana Eden; RGIMS; WFP LESS; WHO PIMS; DSWD warehouses (AO No. 7, s. 2025) | No |
| Barcodes or QR codes | DSWD food packs carry barcodes with production and expiry dates (2026); Dagas, a DLSU thesis (2022) | No |
| Stock dashboard showing expiring items and wastage | Required by DSWD AO No. 7, s. 2025; DSWD's Qlik dashboards (2026) | No |
| Reordering when stock drops below a set level | DSWD files a call-off request when stock falls below 75% of the mandated level; HELIOS orders from past use; UNHRD sets stock ceilings from past dispatches | No |
| Prepositioning guided by typhoon forecasts | DSWD's PAHR, used since 2016 (PAGASA rainfall forecasts laid over hazard and population data); DROMIC preparedness reports | No, not at the national level |
| Working offline | HELIOS; LSS; Sahana Eden | Not by itself |
| GIS, machine learning, text-based recommendations, ISO 25010 survey | Calamba City CDRRMO system (Yaneza et al., 2025) | No |
| Budget-limited replenishment optimization in a working tool | Research only (e.g., Uichanco, 2022) | Yes, as a working tool for a city office |
| Planning stock against several possible quarters | None found | Yes |
| Explaining a recommendation (reasons, what more budget would do) | None found | Yes |
| Offline merging that never loses a stock change | Only Sahana Eden documents sync conflict rules, and they pick one copy of a changed record | Yes, if you test it |
| Stock records that show later edits (tamper-evident) | None found; Eden and WHO PIMS keep ordinary audit logs | Yes |
| Automatic checks on the data before it's used | None found | Yes (small) |

The evidence links are in Section 10.

**The Calamba study matters most.**
- Yaneza, Bartolome, Lerios, and Villarica (2025) are from LSPU's College of Computer Studies, and their system serves the CDRRMO of Calamba City, in your own province. Your panel may know it.
- Their system covers hazard monitoring, incident reports, evacuation planning, and resource tracking. Its resource-allocation advice comes from text analysis of incident reports.
- Yours is about supply readiness: what to keep in stock, what to buy with the budget, and whether the stock records can be trusted.
- Cite it in Chapter 2 and state that difference plainly. Dropping GIS and routing makes the difference sharper.

**What to claim:** budget-limited optimization appears only in research. So the claim is its use in a working tool for a city office, with that office's own records. The LP alone isn't the claim.

---

## 2. The options, ranked

| Rank | Option | Evidence it's new | Effort for one developer (rough) | Main risk | Objectives |
|---|---|---|---|---|---|
| 1 | Plan against past quarters (scenario model) | No working system found; research only | 1–2 weeks | Needs about 8 or more quarters of usage records | 1 |
| 2 | Explain the recommendation in pesos | Not found in any system; the research papers found don't test explanations with non-expert staff | About 1 week | Low | 1, 3 |
| 3 | Stock records with no double counts and visible edits | Only record-level conflict rules found; no tamper-evident stock log found | About 1–2 weeks (your code already has device IDs and skips duplicates) | Low | 2, 4 |
| 4 | Imminent-disaster mode (RA 12287) | DSWD already does forecast-led prepositioning; a city-level tool for the 2025 law wasn't found | 1–2 weeks | Depends on the law's implementing rules and on whether the office would use it | 1 |
| 5 | Data checks before the model runs | None found | 2–4 days | Low | 1, 4 |

The effort figures are my rough estimates for one developer whose base system already runs. Check them against your Capstone 2 timeline with your adviser.

**Suggested package**
- Options 1, 2, and 3 are the contribution.
- Put Option 5 inside Option 3, since it's small.
- Add Option 4 only if both of these hold:
  - your adviser wants the "Disaster Response" part of your title backed more strongly;
  - Logistics confirms they would use it.

---

## 3. The options in detail

### Option 1. Plan against past quarters instead of one average

**What changes**
- **Now:** each item's target is 1.5 × its average use over recent quarters, and the LP fills toward that target within the budget. This has three weaknesses:
  - averaging hides the typhoon quarters;
  - the 1.5 has no stated basis;
  - when the budget covers every target, the LP just repeats the rule.
- **New:** the model keeps each past quarter as a possible "next quarter," called a scenario. It picks the orders with the lowest average shortage across all of those quarters, within the budget and the depot space. A typhoon quarter stays in the data as one of the possibilities instead of being averaged away.
- **Method:** this is sample average approximation (Kleywegt et al., 2002). The model is still linear, so PuLP and CBC stay. It grows from one shortage variable per item to one per item per scenario.

**Model sketch** (this would replace the formulation in Outline 3 of your guide once you decide)
- **Items and quarters:** items i; past quarters s = 1, …, S, each treated as equally likely.
- **Data:**
  - q_i: stock on hand, after reservations;
  - D_is: use in quarter s;
  - m_i: average quarterly use;
  - c_i: unit price;
  - B: budget;
  - w_i: priority weight;
  - v_i: space per unit;
  - V: free depot space.
- **Decisions:**
  - x_i: the order, in whole units, which makes this a MILP;
  - u_is: the shortage if next quarter turns out like quarter s.
- **Objective:** minimize (1/S) × Σ_s Σ_i w_i × u_is / m_i.
  - Dividing by m_i puts every item on the same scale, "quarters of average use short."
  - That lets food packs and medical kits be added together, which fixes the unit-mixing problem the guide flags.
- **Constraints:**
  - u_is ≥ D_is − (q_i + x_i), and u_is ≥ 0, for every item and quarter;
  - Σ_i c_i × x_i ≤ B;
  - Σ_i v_i × (q_i + x_i) ≤ V, if depot space is a real limit;
  - x_i ≥ 0, in whole numbers.
- **Weights:** rank items with Logistics as vital, essential, or desirable (VED). Hospitals use this ranking for critical supplies (Devnani et al., 2010), and it gives the weights a stated basis.
  - **Weights change the answer a lot.** In a test run with illustrative quarters and a ₱200,000 budget:
    - with equal weights, the model bought few food packs, because each peso on water or hygiene kits removed more shortage;
    - with food weighted 3×, it bought 302 food packs instead of 105, and fewer water cases and hygiene kits.

    So the weights have to come from Logistics, and Option 2 is what makes the trade-off visible.

**Simple fallback if time runs short**
- Set each target to the amount that would have covered 7 of the last 8 quarters, which is the second-highest use. Keep your current LP.
- It's a one-line change in parameter preparation, and it already plans for a bad quarter rather than an average one.
- **It works as a per-item multiplier:** each item's multiplier is its covering level divided by its average use, recomputed every quarter.
  - In the illustrative run, the multipliers came out at 1.62 (food), 1.55 (hygiene), 1.62 (water), and 1.35 (medical), instead of a flat 1.5.
  - In your code, this replaces `SAFETY_FACTOR = 1.5` in `ReorderSuggestionCalculator.php`.
- **How it compared in that run** (expected shortage; lower is better):

  | Budget | Flat 1.5 | Per-item multiplier | Full scenario model |
  |---|---|---|---|
  | ₱150,000 | 0.55 | 0.58 | 0.49 |
  | ₱200,000 | 0.34 | 0.32 | 0.32 |
  | ₱250,000 | 0.27 | 0.24 | 0.23 |
  | ₱300,000 | 0.19 | 0.16 | 0.15 |
  | ₱350,000 | 0.18 | 0.11 | 0.07 |

  - The per-item multiplier beat the flat rule except when money was very tight, where it bought almost no food.
  - The full model did best at every budget.
  - Build the multiplier first, then the full model, and let the backtest show what each step adds.

**Why it counts as new**
- Scenario models for relief stock are well established in research (Rawls & Turnquist, 2010; Balcik et al., 2016), and one Philippine case covers DSWD's prepositioning ahead of a typhoon (Uichanco, 2022).
- The other Philippine optimization studies found use scenarios to place hubs or route goods, not to restock an office each quarter (de Veluz et al., 2023; Putong & De Leon, 2018).
- Ye et al. (2020) describe a gap between relief-inventory research and what agencies actually do. No working system in Section 1 plans against scenarios.

**What you need from the office**
- At least 8 quarters of use per item, from logbooks or Excel. More is better.
- Which of those quarters had a typhoon or another event.
- Unit prices, a rough figure for depot space, and the VED ranking.

**How to prove it: a backtest**
- **Setup:** for each past quarter from the fifth one on, pretend it's the start of that quarter. Build the orders using only the earlier quarters, then compare them with what was actually used.
- **Four rules to compare:**
  - what the office actually ordered, if that was recorded;
  - the 1.5× rule, cut by the same percentage to fit the budget;
  - your current LP;
  - the scenario model.
- **Measures:**
  - fill rate (the share of use covered from stock);
  - weighted shortage;
  - number of items that ran out;
  - pesos spent;
  - stock left over.

  Report the typhoon quarters separately.
- **Two limits to state:**
  - 8 to 12 quarters is a small sample, so the backtest shows direction, not statistical proof;
  - when stock ran out, recorded use understates the real need, so mark those quarters.

**Sources:** Kleywegt et al. (2002); Shapiro et al. (2021) or Birge and Louveaux (2011) for stochastic programming; Rawls and Turnquist (2010); Balcik et al. (2016); Uichanco (2022); Ye et al. (2020); de Veluz et al. (2023); Devnani et al. (2010).

### Option 2. Explain every recommendation in pesos

**What changes**
- **One reason per item**, read from the solution itself, not written by AI:
  - "fully covered";
  - "partly covered: the budget ran out";
  - "not ordered: current stock already covers it";
  - "limited by depot space."
- **A budget curve:**
  - the solver re-runs at, say, 50% to 150% of the entered budget;
  - the dashboard plots expected shortage against budget;
  - staff see what each extra ₱10,000 buys and where more money stops helping.
- **An illustration**, using the illustrative prices in Outline 3 of your guide:
  - At ₱200,000 with the least-total-shortage objective, food packs are the item left short. Each extra ₱10,000 buys 20 more packs and brings food from 42% to 38% below target.
  - Under the even-spread objective, each extra ₱10,000 lowers every item's shortage by about 2.3 percentage points.
- **Technical note:**
  - For a plain LP, PuLP reports each constraint's shadow price (`constraint.pi`) and slack (`constraint.slack`) after solving.
  - For a whole-unit model (MILP), those numbers aren't valid shadow prices. Use the budget curve instead, which works for both, or solve the LP relaxation (the solver's `mip=False` option).
  - Start with the curve.

**Why it counts as new**
- No system in Section 1 explains its recommendations.
- Research on explaining optimization results is recent and mostly methods work:
  - a framework for explainable AI in operations research (De Bock et al., 2024);
  - optimization models that are interpretable by design (Goerigk & Hartisch, 2023);
  - "what would have to change" explanations (Korikov et al., 2021; Kurtz et al., 2026).

  None of the papers found tests explanations with non-expert government staff, and your UAT would.
- A study of an AI-based typhoon trigger in the Philippines argues that decision-makers need to understand what drives a recommendation, for accountability (van den Homberg et al., 2020).
- It has a direct practical use: the budget curve gives Logistics figures to bring to LDRRMF planning and to purchase requests under RA 12009.

**How to prove it**
- **UAT task:** show three recommendations, and for each one, ask:
  - Why was this item cut?
  - What would ₱20,000 more change?
  - Which limit mattered?

  Count the correct answers.
- **Survey:** ISO/IEC 25010:2023 lists "self-descriptiveness" and "user assistance" under interaction capability. Add survey items for both.

**Sources:** De Bock et al. (2024); Goerigk and Hartisch (2023); Korikov et al. (2021); Kurtz et al. (2026); van den Homberg et al. (2020); the PuLP documentation.

### Option 3. Stock records that stay right offline and show later edits

**What changes**
- **Entries, not totals.** Save every stock change as an entry with an ID made on the device. Each entry records:
  - item and depot;
  - quantity added or removed;
  - reason;
  - user, device, and time.

  Current stock is the sum of the entries, like a passbook.
- **No double counts.** PostgreSQL gets a unique index on the entry ID, so a retried upload can't be counted twice. That's a database rule, not extra app code.
- **Why entries beat totals.** Say an item has 100 units. While offline, device A releases 10 and device B releases 5.
  - If each device saves a new total, the server receives "90" and "95" and can keep only one, so one release is lost.
  - Entries of −10 and −5 add up to 85 whatever order they arrive in.
  - This is the idea behind conflict-free replicated data types (Shapiro et al., 2011).
- **Conflicts.** If merged entries would push stock below zero, or return the same borrowed item twice, the server keeps both entries and flags them for Logistics. Nothing is dropped silently.
  - Flagging is enough for now.
  - If you later want offline releases never to overdraw, see the bounded-counter work by Balegas et al. (2015).
- **Priority.** Releases and returns sync before routine edits. You already have priority sync.
- **Tamper evidence** (Schneier & Kelsey, 1999):
  - When the server accepts an entry, it stores a hash that includes the previous entry's hash.
  - A "check records" function recomputes the chain, so an edited or deleted row breaks the chain at that row.
  - The server builds the chain in the order it accepts entries, so offline devices never have to agree on an order.
  - Crosby and Wallach (2009) show faster proofs for very large logs, which you won't need at this size.
  - **Limit:** someone with full database access could rebuild the whole chain. To guard against that, print the latest hash on each quarterly report, or keep it outside the system. Any later rewrite then won't match the printed value.
- **Keep the offline queue safe:**
  - Call `navigator.storage.persist()` so the browser doesn't clear saved entries when storage runs low. All current major browsers support it.
  - Background Sync works only in Chromium browsers, so also retry when the app opens or the connection returns.
  - Safari can delete a site's data after seven days without use unless the app was added to the Home Screen. The Wilander (2020) source for this is already in your guide.

**Why it counts as new**
- Offline web apps are well established (Cuenca-Enrique et al., 2024; Kleppmann et al., 2019).
- Of the relief systems checked, only Sahana Eden documents how it settles sync conflicts, and its rules keep one copy of a changed record.
- None of them keeps a tamper-evident stock record; Eden and WHO PIMS keep ordinary audit logs.
- The search found no peer-reviewed paper from 2018 to 2026 that applies entry-based merging to inventory counts. Balegas et al. (2015) is the closest.
- It also backs up your own argument that an optimizer is only as good as its stock data.

**What your code already has** (checked September 27, 2026):
- **Device IDs:** the offline outbox (`resources/js/Offline/outbox.ts`) gives each queued request, incident, and area an ID made on the device.
- **No duplicates:** `RequestController::store()` treats a repeat of the same ID as a no-op, so retries can't create duplicates.
- **Offline creates requests only:** stock changes happen on the server when an allocation is fulfilled.

**What's left for Option 3:**
- **Stock entries:** record each stock change as an append-only entry that links to its request or allocation.
- **Tamper evidence:** add the hash chain and the "check records" command.
- **Conflict flag:** flag approvals that would push stock below zero.
- **Sync order:** fix the order in which the outbox syncs.
  - `syncOutbox()` reads pending items through a status index. Within that index they come out ordered by their random IDs, so the outbox keeps neither creation order nor request priority.
  - Sort pending items by `createdAt`, then by priority, keeping an incident's areas after the incident.
  - Until then, the paper can't claim priority sync as built.

**How to prove it**
- **Tests:** run the offline test in Outline 4 of your guide, plus three more:
  - send the same batch three times; expect 0 duplicates;
  - two devices release the last 10 units of one item while offline; expect a flag;
  - edit an old row directly in the database; the check should fail and name that row.
- **ISO/IEC 25010:2023 terms that fit:**
  - Reliability: fault tolerance, recoverability;
  - Safety: fail safe;
  - Security: integrity, accountability, non-repudiation.

**Why not blockchain:** one office holds the data. Blockchain research on relief supply chains is about trust among many organizations (Dubey et al., 2020). A hash chain gives you tamper evidence without extra servers or tokens.

**Sources:** Shapiro et al. (2011); Kleppmann et al. (2019); Balegas et al. (2015); Schneier and Kelsey (1999); Crosby and Wallach (2009); Dubey et al. (2020); the MDN pages on persist() and Background Sync.

### Option 4 (optional). Imminent-disaster mode under RA 12287

**Why this is timely.** RA 12287, the Declaration of State of Imminent Disaster Act, was approved on September 12, 2025. Under it:
- A local chief executive can declare a state of imminent disaster in the city, on the regional DRRM council's recommendation (Sec. 5).
- The declaration needs a pre-disaster risk assessment (PDRA). The PDRA must rate the forecast impact as severe, with 3 to 5 days of lead time (Sec. 6).
- The law allows buying, prepositioning, and distributing food and non-food items before the disaster (Sec. 9).
- Local DRRM funds may be used (Sec. 10).

**What the mode does**
- Staff switch it on when the city declares or when the PDRA flags San Pablo. PAGASA's wind signals can mark readiness stages: Signal No. 1 comes about 36 hours ahead, and Signal No. 2 about 24 hours ahead.
- The system reruns the model using only the typhoon quarters in your records, or an event staff choose.
- It compares that need with current stock, minus reserved and borrowed items.
- It lists what to release or preposition first, and what to buy with the amount the office names. If you track expiry, the soonest-expiring stock goes first.
- It logs who switched the mode on and what was decided.
- Start with a manual switch. Later, the system could read the GDACS cyclone feed, which is public and machine-readable (Kristine appears there as TRAMI-24, orange alert). The search found no official public PAGASA API for bulletins.

**Local numbers you can build a scenario from.** DROMIC Report #101 (DSWD, December 6, 2025) gives San Pablo City's cumulative figures for Kristine and Leon (October 2024):
- **Affected:** 46 barangays; 811 families (3,330 persons).
- **Displaced:** 629 families in 41 evacuation centers, and 182 families outside them.
- **Damaged houses:** 275, of which 40 were totally damaged.
- **Assistance:** ₱1,593,951 in all, with ₱1,516,620 from DSWD and ₱77,331 from the city.
- **Treat it as one event:** the report counts both storms together.
- **Worked example:**
  - A DSWD family food pack is meant for a family of five for two days.
  - A Kristine-sized event with four days of displacement would need about 811 × 4 ÷ 2 = 1,622 packs.
  - The four days is an assumption; confirm the real length with Logistics.

**Why the novelty is limited**
- Forecast-based action, where money is released for agreed actions once a forecast crosses a threshold, is well established (Coughlan de Perez et al., 2015).
- DSWD has used PAHR for prepositioning since 2016.
- The UN's anticipatory action framework for Philippine typhoons triggers on damage forecasts (OCHA, 2025), and Philippine studies test those forecast models (Kooshki Forooshani et al., 2024; Sedhain et al., 2025).
- What wasn't found is a city-level tool that ties the 2025 law to the city's own stock and budget. So this is a timely local application, not a new technique.
- It does give the "for Disaster Response" in your title a concrete meaning without taking on live incident operations.

**Check before you commit**
- Whether the law's implementing rules are out. A news report says OCD finalized them in October 2025; that wasn't confirmed.
- Whether the city has ever declared under it.
- How the office acts on a PDRA today.

**How to prove it:** run a tabletop exercise with Logistics using Kristine. Compare the system's list with what was actually needed and released, and time both.

### Option 5. Data checks before the model runs

- **What it does:** before solving, the system flags entries that look wrong and asks staff to confirm or correct them. Examples:
  - a quarter's use more than three times the usual, with no event tagged;
  - stock below zero;
  - a gap found at the weekly count.
- **Why rules, not machine learning:** simple rules suit the office's data size. An anomaly-detection method such as isolation forest (Liu et al., 2008) needs more history than the office likely has.
- **Why it's worth adding:**
  - no system in Section 1 checks data before using it;
  - it takes days, not weeks.

---

## 4. Useful features that won't count as new

- **QR labels on borrowed equipment.** Staff check items out and back in with a phone camera. This cuts counting errors and supports the property forms.
  - The browser's built-in barcode reader doesn't work in Chrome on Windows.
  - The @zxing/browser library (MIT license) was updated in July 2026, though its core library says it's in maintenance mode.
  - The html5-qrcode library hasn't had a release since April 2023, and its author is looking for new owners.
- **Expiry dates and first-expiry-first-out.** Already common, but they give your SDG 12 claim something to measure: units expired per quarter. For perishables in relief work, see Ferreira et al. (2018).
- **Demand forecasting.** Croston-type methods (SBA, TSB) suit demand that comes in bursts (Croston, 1972; Syntetos & Boylan, 2005; Teunter et al., 2011). But they need monthly data, and they give one number, which brings back the safety-factor question. Option 1 avoids that.

---

## 5. What to skip in this scope

| Idea | Why skip it |
|---|---|
| Chatbot or LLM assistant | Needs the internet or a heavy local model, can state wrong stock numbers, raises RA 10173 issues with borrower data, and doesn't fix the stock problem |
| Camera-based stock counting | Needs a custom-trained model and many photos, and accuracy on stacked boxes is uncertain. Too risky for one semester |
| Blockchain | One office holds the data. Option 3's hash chain gives tamper evidence without it |
| IoT sensors | Hardware to buy and install, which is outside your budget and timeline |
| SMS requests from barangays | Adds a new user group and new respondents |
| Maps and routing | You removed them on purpose, and the Calamba system already has them |

---

## 6. How to state the novelty (outline only; write it yourself)

- Drop "no single system combines…" as the main claim. Keep it as background.
- State two or three specific contributions, each tied to a test:
  - stock planned against the office's own past quarters, including typhoon quarters, compared with the current 1.5× rule on quarters the model didn't see;
  - recommendations that say why and what more money would change, checked with staff in UAT;
  - stock records that stay correct through outages and show later edits, checked with fault and tamper tests.
- Keep the claim bounded: one city DRRM office, its own records, the period you studied.
- Don't write "first" or "novel." Say what the system does differently, and show the test.
- When you say something wasn't found, give your search scope (databases, keywords, years), as Part D.2 of your guide explains.
- Table 1 rows to add:
  - scenario planning;
  - explained recommendations;
  - stock-safe offline merging;
  - tamper-evident records;
  - imminent-disaster support, if you take Option 4.

---

## 7. What changes in Chapters 1–3 if you take Options 1, 2, and 3

- **Chapter 1**
  - **Background:** name the national practice (DSWD's 75% call-off rule and PAHR) and the local event (the DROMIC figures above).
  - **Objectives:**
    - Objective 1 becomes the scenario model plus the explanations.
    - Objective 2 becomes the stock records and the sync rules.
    - Objective 3's dashboard adds the budget curve and the reasons.
    - Objective 4's requests write to the stock records.
    - Objective 5 adds the UAT comprehension task.
  - **Theoretical framework:** add stochastic programming (sample average approximation) beside LP. Add conflict-free replication and tamper-evident logging beside local-first computing.
- **Chapter 2**
  - **Related systems:** add DSWD's tools (AO No. 7, PAHR, RGIMS), LSS/SUMA and WHO PIMS, WFP LESS, UNHRD, and Yaneza et al. (2025).
  - **New short subsections:**
    - planning under uncertain disaster demand;
    - explaining optimization results;
    - offline merging and tamper-evident records;
    - anticipatory action, if you take Option 4.
- **Chapter 3**
  - the scenario model and the backtest;
  - the UAT task;
  - the fault and tamper tests;
  - the data checks;
  - survey items for self-descriptiveness, user assistance, and fail safe, plus hazard warning if you take Option 4.
- **Your guide:** this affects Steps 3, 4, 6, 7, and 8 and Outlines 1 to 4. Once you choose, the guide can be updated to match.

---

## 8. PuLP 4.0: your pinned version keeps you safe

- **What changed:** PuLP 4.0.0 came out on September 25, 2026, and it breaks code written for 3.x:
  - `PULP_CBC_CMD` is gone; install `pulp[cbc]` and use `COIN_CMD`;
  - `solve()` returns a stats object, and `prob.status` is gone;
  - `prob.constraints` is now a method.
- **Why it matters to you:** your `solver-service/requirements.txt` already pins `pulp==2.8.0`, so a rebuild won't pull 4.0.
  - Keep the pin.
  - Write "PuLP 2.8.0" in Chapter 3's tools section.
  - Change the pin only on purpose.

---

## 9. Questions to settle with Logistics and your adviser

1. How many quarters of usage records exist for each item, and which quarters had a typhoon or other event?
2. Did stock run out in any quarter? If so, recorded use understates the need for that quarter.
3. Roughly how much space does each depot have?
4. Would Logistics accept a rule like "keep enough to have covered 7 of the last 8 quarters"?
5. Would Logistics rank the items as vital, essential, or desirable?
6. How far back do the ICS 213-RR resource request forms go? Are older requests kept in logbooks or Excel? Do the forms show both what was requested and what was released?
7. What does the office do today when PAGASA raises a signal or the PDRA flags the city? Has San Pablo ever declared under RA 12287?
8. Does your adviser want two contributions or three?

---

## 10. Sources

All of these were checked to exist. Sources already in your guide aren't repeated here:
- Rawls and Turnquist (2010);
- Uichanco (2022);
- Ye et al. (2020);
- Kleppmann et al. (2019);
- Blecken and Hellingrath (2008);
- Wilander (2020).

Read the part you plan to cite before citing it.

**Planning under uncertainty (Option 1)**
- Balcik, B., Bozkir, C. D. C., & Kundakcioglu, O. E. (2016). A literature review on inventory management in humanitarian supply chains. *Surveys in Operations Research and Management Science, 21*(2), 101–116. https://doi.org/10.1016/j.sorms.2016.10.002
- Birge, J. R., & Louveaux, F. (2011). *Introduction to stochastic programming* (2nd ed.). Springer. https://doi.org/10.1007/978-1-4614-0237-4
- de Veluz, M. R. D., Redi, A. A. N. P., Maaliw, R. R., Persada, S. F., Prasetyo, Y. T., & Young, M. N. (2023). Scenario-based multi-objective location-routing model for pre-disaster planning: A Philippine case study. *Sustainability, 15*(6), Article 4882. https://doi.org/10.3390/su15064882
- Devnani, M., Gupta, A. K., & Nigah, R. (2010). ABC and VED analysis of the pharmacy store of a tertiary care teaching, research and referral healthcare institute of India. *Journal of Young Pharmacists, 2*(2), 201–205. https://doi.org/10.4103/0975-1483.63170
- Kleywegt, A. J., Shapiro, A., & Homem-de-Mello, T. (2002). The sample average approximation method for stochastic discrete optimization. *SIAM Journal on Optimization, 12*(2), 479–502. https://doi.org/10.1137/S1052623499363220
- Putong, L. L., & De Leon, M. M. (2018). A modified Balcik last mile distribution model for relief operations using open road networks. *Procedia Engineering, 212*, 133–140. https://doi.org/10.1016/j.proeng.2018.01.018
- Shapiro, A., Dentcheva, D., & Ruszczyński, A. (2021). *Lectures on stochastic programming: Modeling and theory* (3rd ed.). Society for Industrial and Applied Mathematics. https://doi.org/10.1137/1.9781611976595

**Forecasting and perishables (Section 4)**
- Croston, J. D. (1972). Forecasting and stock control for intermittent demands. *Operational Research Quarterly, 23*(3), 289–303. https://doi.org/10.1057/jors.1972.50
- Ferreira, G. O., Arruda, E. F., & Marujo, L. G. (2018). Inventory management of perishable items in long-term humanitarian operations using Markov decision processes. *International Journal of Disaster Risk Reduction, 31*, 460–469. https://doi.org/10.1016/j.ijdrr.2018.05.010
- Syntetos, A. A., & Boylan, J. E. (2005). The accuracy of intermittent demand estimates. *International Journal of Forecasting, 21*(2), 303–314. https://doi.org/10.1016/j.ijforecast.2004.10.001
- Teunter, R. H., Syntetos, A. A., & Babai, M. Z. (2011). Intermittent demand: Linking forecasting to inventory obsolescence. *European Journal of Operational Research, 214*(3), 606–615. https://doi.org/10.1016/j.ejor.2011.05.018

**Explaining optimization results (Option 2)**
- De Bock, K. W., Coussement, K., De Caigny, A., Słowiński, R., Baesens, B., Boute, R. N., Choi, T.-M., Delen, D., Kraus, M., Lessmann, S., Maldonado, S., Martens, D., Óskarsdóttir, M., Vairetti, C., Verbeke, W., & Weber, R. (2024). Explainable AI for operational research: A defining framework, methods, applications, and a research agenda. *European Journal of Operational Research, 317*(2), 249–272. https://doi.org/10.1016/j.ejor.2023.09.026
- Goerigk, M., & Hartisch, M. (2023). A framework for inherently interpretable optimization models. *European Journal of Operational Research, 310*(3), 1312–1324. https://doi.org/10.1016/j.ejor.2023.04.013
- Korikov, A., Shleyfman, A., & Beck, J. C. (2021). Counterfactual explanations for optimization-based decisions in the context of the GDPR. In *Proceedings of the Thirtieth International Joint Conference on Artificial Intelligence* (pp. 4097–4103). International Joint Conferences on Artificial Intelligence Organization. https://doi.org/10.24963/ijcai.2021/564
- Kurtz, J., Birbil, Ş. İ., & den Hertog, D. (2026). Counterfactual explanations for linear optimization. *European Journal of Operational Research, 329*(1), 24–41. https://doi.org/10.1016/j.ejor.2025.06.016
- van den Homberg, M. J. C., Gevaert, C. M., & Georgiadou, Y. (2020). The changing face of accountability in humanitarianism: Using artificial intelligence for anticipatory action. *Politics and Governance, 8*(4), 456–467. https://doi.org/10.17645/pag.v8i4.3158
- PuLP documentation on migrating to version 4, including the constraint attributes: https://coin-or.github.io/pulp/guides/how_to_migrate_to_v4.html

**Offline sync, tamper-evident records, data checks (Options 3 and 5)**
- Balegas, V., Serra, D., Duarte, S., Ferreira, C., Shapiro, M., Rodrigues, R., & Preguiça, N. (2015). Extending eventually consistent cloud databases for enforcing numeric invariants. In *2015 IEEE 34th Symposium on Reliable Distributed Systems (SRDS)* (pp. 31–36). IEEE. https://doi.org/10.1109/SRDS.2015.32
- Crosby, S. A., & Wallach, D. S. (2009). Efficient data structures for tamper-evident logging. In *Proceedings of the 18th USENIX Security Symposium*. USENIX Association. https://www.usenix.org/legacy/event/sec09/tech/full_papers/crosby.pdf **(page numbers not confirmed)**
- Dubey, R., Gunasekaran, A., Bryde, D. J., Dwivedi, Y. K., & Papadopoulos, T. (2020). Blockchain technology for enhancing swift-trust, collaboration and resilience within a humanitarian supply chain setting. *International Journal of Production Research, 58*(11), 3381–3398. https://doi.org/10.1080/00207543.2020.1722860
- Liu, F. T., Ting, K. M., & Zhou, Z.-H. (2008). Isolation forest. In *2008 Eighth IEEE International Conference on Data Mining* (pp. 413–422). IEEE. https://doi.org/10.1109/ICDM.2008.17
- Schneier, B., & Kelsey, J. (1999). Secure audit logs to support computer forensics. *ACM Transactions on Information and System Security, 2*(2), 159–176. https://doi.org/10.1145/317087.317089
- Shapiro, M., Preguiça, N., Baquero, C., & Zawirski, M. (2011). Conflict-free replicated data types. In X. Défago, F. Petit, & V. Villain (Eds.), *Stabilization, safety, and security of distributed systems* (pp. 386–400). Springer. https://doi.org/10.1007/978-3-642-24550-3_29
- MDN pages:
  - StorageManager.persist(): https://developer.mozilla.org/en-US/docs/Web/API/StorageManager/persist
  - Background Synchronization API: https://developer.mozilla.org/en-US/docs/Web/API/Background_Synchronization_API
  - BarcodeDetector: https://developer.mozilla.org/en-US/docs/Web/API/BarcodeDetector

**Anticipatory action, Philippine policy, and local data (Option 4 and Chapter 1)**
- Coughlan de Perez, E., van den Hurk, B., van Aalst, M. K., Jongman, B., Klose, T., & Suarez, P. (2015). Forecast-based financing: An approach for catalyzing humanitarian action based on extreme weather and climate forecasts. *Natural Hazards and Earth System Sciences, 15*(4), 895–904. https://doi.org/10.5194/nhess-15-895-2015
- Declaration of State of Imminent Disaster Act, Rep. Act No. 12287 (2025). https://lawphil.net/statutes/repacts/ra2025/ra_12287_2025.html
- Department of Social Welfare and Development. (2025). *Guidelines and procedures on the maintenance of DSWD Disaster Resource Centers* (Administrative Order No. 7, s. 2025). https://dswd-file-assets.s3.ap-southeast-1.amazonaws.com/wp-content/uploads/2025/09/AO-No.-7-S-2025.pdf
- Department of Social Welfare and Development. (2025, December 6). *DSWD DROMIC report #101 on Severe Tropical Storm "Kristine" and Super Typhoon "Leon" as of 06 December 2025, 6AM*. https://dromic.dswd.gov.ph/wp-content/uploads/2025/12/DSWD-DROMIC-Report-101-on-Severe-Tropical-Storm-Kristine-and-Super-Typhoon-Leon-as-of-06-December-2025-6AM.pdf
- Kooshki Forooshani, M., van den Homberg, M., Kalimeri, K., Kaltenbrunner, A., Mejova, Y., Milano, L., Ndirangu, P., Paolotti, D., Teklesadik, A., & Turner, M. L. (2024). Towards a global impact-based forecasting model for tropical cyclones. *Natural Hazards and Earth System Sciences, 24*(1), 309–329. https://doi.org/10.5194/nhess-24-309-2024
- National Disaster Risk Reduction and Management Council. (2014). *Institutionalization of the pre-disaster risk assessment* (NDRRMC Memorandum No. 17, s. 2014). https://www.rcrc-resilience-southeastasia.org/wp-content/uploads/2018/01/2014-NDRRMC-Memorandum-Institutionalization-of-the-Pre-disaster-risk-assessment.pdf **(the memo number is handwritten on the scan, so confirm it)**
- Riveral, C. C. Y. (n.d.). *DSWD climate and hazard information in scaling up social protection programs* [Presentation slides]. Anticipation Hub. https://www.anticipation-hub.org/Documents/Presentations/FbF_Social_Protection_PHL_gov_DRMB_Asia_Pacific_Dialogue_Platform_ppt.pdf
- Sedhain, S., van den Homberg, M., Teklesadik, A., van Aalst, M., & Kerle, N. (2025). Evaluating impact-based forecasting models for tropical cyclone anticipatory action. *International Journal of Disaster Risk Reduction, 129*, Article 105782. https://doi.org/10.1016/j.ijdrr.2025.105782
- United Nations Office for the Coordination of Humanitarian Affairs. (2025, October 3). *Anticipatory action framework: Philippines tropical cyclones*. https://www.unocha.org/publications/report/philippines/anticipatory-action-framework-philippines-tropical-cyclones-03-october-2025
- Other pages used:
  - PAGASA tropical cyclone wind signals: https://www.pagasa.dost.gov.ph/learning-tools/tropical-cyclone-wind-signal
  - GDACS feeds: https://www.gdacs.org/feed_reference.aspx
  - DSWD family food pack contents (2024): https://www.dswd.gov.ph/dswd-chief-reiterates-warning-against-tampering-of-family-food-packs/
  - DSWD Field Office I, one pack for a family of five for two days: https://fo1.dswd.gov.ph/relief-augmentation-2/

**Related systems (Section 1 evidence)**
- Yaneza, J. S., Bartolome, J. A., Lerios, J. L., & Villarica, M. V. (2025). Online emergency response management system for City Disaster Risk Reduction Management Office of Calamba City, Laguna. *International Journal of Advanced Research in Computer Science, 16*(3), 138–145. https://doi.org/10.26483/ijarcs.v16i3.7254
- **Evidence links** (open them before citing; they aren't formatted as APA references yet):
  - Sahana Eden inventory, sync, and audit code: https://github.com/sahana/eden/blob/master/modules/s3db/inv.py, https://github.com/sahana/eden/blob/master/modules/s3db/sync.py, https://github.com/sahana/eden/blob/master/modules/core/aaa/audit.py
  - DSWD and WFP create RGIMS (2012): https://old.dswd.gov.ph/dswd-un-wfp-create-system-to-track-inventory-of-relief-goods/
  - DSWD food pack barcodes (2026): https://www.dswd.gov.ph/dswds-nlrmb-marks-3-3m-stockpile-of-food-packs-highlights-departments-readiness-in-disaster-response/
  - DSWD and Qlik dashboards (2026): https://www.dswd.gov.ph/dswd-partners-with-qlik-to-speed-up-relief-delivery-improve-nationwide-coordination/
  - LSS and its successor WHO PIMS (Rasheed et al., 2019): https://www.frontiersin.org/journals/pharmacology/articles/10.3389/fphar.2019.00473/full
  - WFP LESS internal audit (2021): https://www.wfp.org/audit-reports/internal-audit-less-functionalities-and-application-controls-february-2021
  - UNHRD Stock Task Force: https://unhrd.org/unhrd-stock-taskforce
  - Dagas, DLSU (2022): https://animorepository.dlsu.edu.ph/etdb_ece/18/
  - Blecken and Hellingrath (2008), archived copy, since the ISCRAM site is down: https://web.archive.org/web/20250120180456/https://idl.iscram.org/files/blecken/2008/329_Blecken+Hellingrath2008.pdf
