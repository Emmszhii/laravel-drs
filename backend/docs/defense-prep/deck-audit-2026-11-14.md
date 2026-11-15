# Presentation Audit — 14 November 2026

**Deck:** `deck/DisasterResource+ Proposal Defense.pptx` (31 slides with speaker notes, last saved Nov 12, 02:21).
**Checked against:**
- the course's *Proposal Defense Presentation Guide* (20 pages);
- the manuscript as fixed today;
- the time budget: 30 minutes of slides, a 5–15 minute demo, 15 minutes of Q&A.

**Not checked:** the look of each slide. I read the slide text and notes; I did not look at a picture of each slide.

**Status (14 Nov 2026):**
- **Applied:** every fix in sections 2 and 3, by updating the build scripts and rebuilding the deck. Your adviser name and date were kept.
- **Order:** the data slide is now slide 21 and the backtest slide is 22.
- **Also in the rebuild:** the "Core idea" line, the "Main problem" line, and "Tools, and why", which were already in the scripts.
- **Checked by eye:** slides 1, 12, 13, 16, 18, 20 and 28.
- **Backup:** the deck before the rebuild is in the session scratchpad (`deck/backup-deck.pptx`).

---

## 1. Against the guide's structure

| Guide section (page) | Deck slide(s) | Status |
|---|---|---|
| Title: complete title, proponent, program, adviser, institution, date (p. 4) | 1 | ✅ Every field is there. The date needs a comma: "November 18, 2026". |
| Ch I background and context, ending with the core idea (p. 5) | 2–3 | ✅ |
| Research problem: one main problem plus specific questions (p. 6) | 4–5 | ✅ |
| Objectives: general plus specific (p. 7) | 6–7 | ✅ Slide 7 adds the alignment table, which the guide's checklist also asks for. |
| Framework explained in layers (p. 8) | 8–9 | ✅ Theoretical and conceptual frameworks (IPO). |
| Scope, delimitations, significance (p. 9) | 10–11 | ✅ |
| Ch II as a thematic matrix: Author/Year, Focus, Method, Key finding, Relevance (p. 10) | 12–13 | ⚠️ There is no **Method / Technology** column. The guide's matrix has five columns; yours has four. |
| Ch II matrix justifying the method (p. 11) | 12 (Besbes, Hewamalage) | ✅ It is mixed into slide 12. That's fine. |
| Ch II integration-gap matrix (p. 12) | 14 | ✅ This is Table 2 with Yes/No/ND. |
| Ch II synthesis and gap, with "Therefore…" (p. 13) | 15 | ✅ |
| Ch III design and locale, with "why this fits" (p. 14) | 16 | ⚠️ It states the design but not **why it fits**. Add one line: "Design science fits because the study builds an artifact and tests it on the office's own problem." |
| Applied concepts and **algorithm analysis**: Component, Purpose, Approach, **Evaluation / output** (p. 15) | 18 | ⚠️ Slide 18 lists the techniques but gives no evaluation for each. The guide's checklist says "Each algorithm has a defined purpose and evaluation metric." Turn it into a 4-column table (see section 3). |
| Data collection: where the data come from, preparation, privacy (p. 16) | 22 | ✅ |
| Data model / ERD (p. 17) | 23 | ✅ Figure 11. |
| Development methodology and tools, with the tools justified (p. 18) | 24 | ✅ The justification is in the notes. Consider one "why" word per tool on the slide. |
| System design: sequence and architecture (p. 19) | 25–27 | ✅ Figures 5, 12 and 10. |
| Testing and evaluation, with **your own test cases** (p. 20) | 28–29 | ✅ The counts match the paper: 13 unit, 11 integration, 13 offline, 4 performance, 6 user acceptance. |
| Readiness checklist "NOT INCLUDED IN YOUR PPT" (p. 21) | — | ✅ Correctly left out. |
| Guide's flow: … DATA & METHOD → SYSTEM → EVALUATION (p. 3) | 21 before 22 | ⚠️ The backtest (slide 21, an evaluation) comes **before** the data slide (22), but the backtest uses those data. Swap slides 21 and 22, or move 21 next to the testing slides (28–29). |

**Guide's principles** ("present the argument, not the manuscript"; no paragraphs; no claims stated as findings):
- ✅ Every claim is framed as a possibility or a plan. Slide 4 says so outright, and slides 20 and 22 label the example and the sample data.
- ⚠️ Slides **17** (16 requirements), **19** (two formulations plus the VED table) and **28** (5-row table) are dense. They are fine to show, but don't read them. Point to two or three items each.

**Timing:** 29 content slides in 30 minutes is about 1 minute per slide, which is on target. Slides 19–21 will need about 2 minutes each, so keep slides 10–11 and 17 short.

---

## 2. Out of date after today's manuscript fixes (must fix)

| Slide | Now | Change to | Why |
|---|---|---|---|
| 12 | "Gupta et al. (2007); Gizaw & Jemal (2021)" | "Meena & Mathaiyan (2024); Gizaw & Jemal (2021)" | Gupta 2007 was removed from the paper (5-year rule). A panelist comparing the deck with the paper would see a source missing from the reference list. |
| 20 | "Every value is reproduced by the solver's automated tests (UT-01 to UT-03, UT-12)". Notes: "Every number here is reproduced by an automated test." | "The least-shortage and same-% orders are reproduced by automated tests (UT-01 to UT-03)". Notes: "These orders are reproduced by automated tests." | The paper now says that, because no test covers the even-spread column. |
| 28 | Performance pass: "Stage 2 < 2 s; curve < 20 s; dashboard < 1 s" | "Stage 1 and 2 < 2 s; curve < 20 s; dashboard < 1 s" | The paper now gives Stage 1 a pass mark (PT-03). |
| 5 | RQ3: "…every edit detected, every status recorded?" | "…every edit detected, every test case passed, including every status?" | It now matches the paper's RQ3. |
| 15 | "No reviewed system explains its recommendations" | "No reviewed system documents explanations for its recommendations" | Same wording as the paper. It avoids stating that something is absent. |
| 10 | "Stock of the CSWDO, CHO, and GSO" | "Stock of the city's social welfare, health, and general services offices" | The paper no longer uses these abbreviations, and the deck never spells them out. |
| 1 | "November 18 2026" | "November 18, 2026" | Missing comma. |

## 3. Suggested improvements (should do)

1. **Slide 18:** make the guide's 4-column table.

   | Component | Purpose | Approach | Evaluation |
   |---|---|---|---|
   | Per-item multiplier | Plan within the budget | MILP (PuLP + CBC) | Weighted shortage (backtest) |
   | Scenario model | Cover typhoon quarters | SAA MILP | Weighted shortage (backtest) |
   | Reasons + budget curve | Explain | Read from the solution, re-solve | ≥ 80% (explanation task) |
   | Entries + hash chain | Trustworthy records | Append-only, SHA-256 | 0 duplicates or losses, every edit detected |
   | Data checks | Clean inputs | 3 rules | Test cases |

2. **Slides 12–13:** add a short **Method** column, as in the guide's matrix. Examples: "Review", "Stochastic program", "MILP", "User study", "Ledger DB".
3. **Slide 16:** add the "why this design fits" line.
4. **Order:** put slide 22 (Data) before slide 21 (Backtest).
5. **Preview PDF:** `DisasterResource+ Proposal Defense (preview).pdf` is older than the .pptx (01:19 vs 02:21 on Nov 12). Make a new one after the fixes.

## 4. What is already strong

- Every research question maps to one objective, one procedure and one criterion (slides 5–7), as the guide's checklist asks.
- Ch II follows the guide: matrices, a gap table and a synthesis that leads into the research questions.
- Honest framing throughout: possibilities rather than findings, illustrative data, and the sample dataset labeled as such.
- The ERD, sequence diagram and architecture figures are all in the deck, as the guide requires.
- The speaker notes answer the panel's earlier comments one by one ("As the panel asked…").
