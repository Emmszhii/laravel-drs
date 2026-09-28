# CLAUDE.md — Capstone thesis writing (Chapters 1–3)

This file governs work on Emmanuel Manalo's MIT capstone thesis for DisasterResource+ (Chapters I–III), tracked in the Google Doc **"Capstone I - EMMANUEL MANALO"** (`https://docs.google.com/document/d/18ZK7IK0Am3Le8XvXVn8F7ccwyyZABRJmrrkELisAl1o/edit`), not in this repo. The three pattern files in this folder (`Chapter 1 Pattern.pdf`, `Chapter 2 Pattern.docx`, `Chapter 3 Pattern.pdf`) are the school's required section templates — the rules below are already distilled from them, so don't re-read the raw PDFs unless you need a section this file doesn't cover.

## Canonical tabs

The doc has many tabs (old drafts, staging areas, an interview transcript). The **only tabs that represent the current, submittable chapters** are:
- **"Revised of CHAPTER I"**
- **"Revised of CHAPTER II"**
- **"CHAPTER 3"**

The plain "CHAPTER I" / "CHAPTER II" tabs are stale, unrevised originals kept for reference — never treat them as current, and flag it if the user seems to be editing/citing them by mistake. "Key Information" and "Revised Additions (Ch. 1-2)" are staging/scratch tabs, not part of the paper. "Inteview Transcription" is the CDRRMO stakeholder interview (Tagalog) — the source of truth for every scope claim (see below). "Tab 8" is a progress-report log, not chapter content.

## Scope claims must trace to the interview

Any scope/limitation statement in Chapter I or III (what the system does or explicitly does not do) must be checkable against "Inteview Transcription". Confirmed-in-interview scope boundaries as of this analysis: CDRRMO-only inventory (no CSWDO/CHO visibility), no ICS modeling, no barangay-initiated requests, consumable-vs-borrowed dual workflow with lifecycle tracking, quarterly (Q1–Q4) reorder review, and route-line visualization only (no turn-by-turn navigation). Per explicit user direction, the interview's second named priority — a dedicated rescue vehicle — is **out of scope for this critique and should not be raised again**.

## Hard formatting rules (from the pattern guides)

- **Table and Figure numbering is continuous across the whole paper, not reset per chapter.** Proof: the Chapter 3 Pattern's own worked example starts its first table at "Table 3" (continuing after Chapter 2's Table 1–2) and its example figure at "Figure 2" (continuing after Chapter 1's Figure 1). Never restart numbering at 1 in Chapter II or III.
- **APA 7th edition figure/table labeling** (verified against `backend/docs/figure-table-setup/figure.png` and `table.png`, the school-provided reference images): for both figures and tables, the **number** (e.g. "Figure 1" / "Table 1") is bold and sits on its own line **above** the image/table, immediately followed by the **title** in italics, Title Case, on the next line — also above the image/table. An optional **Note.** (italic word "Note." only, rest plain) goes below, only if needed to explain abbreviations, symbols, or a citation/adaptation source. Do not bake the number+title into the image file itself — it must be live, selectable document text so it can be styled/checked, not pixels. Every table/figure must be cited in-text by its number ("Table 2", never "table 2").
- Applied Concepts and Techniques headings must follow a strict `Discipline → Subfield → Technique` chain, matching the pattern's own example ("Machine Learning → Unsupervised Learning → Clustering → K-Means"). Existing correct examples in Chapter 3: "Operations Research → Optimization → Linear Programming → Transportation/Assignment Problem", "Operations Research → Heuristic Optimization → Weighted Scoring", "Geographic Information Systems → Routing → Contraction Hierarchies", "Distributed Systems → Offline-First Computing → Local-First Storage". Any new subsection heading in this section must follow the same pattern (full chain, not a shortened form).
- Chapter I: every **Specific Objective** must have a matching **Research Problem** question — same count, same order. The pattern requires the Research Problem to have a minimum of 3–5 specific questions.
- Chapter II must end in a **Synthesis** section containing a **Technology Gap table** with columns `Aspect | Current State | Gap/Limitation | Impact of Gap | References` (exact pattern from the template).
- Every in-text citation needs a matching entry in that chapter's References list, and vice versa.
- Chapter III's Software Testing → Sampling Design must state the sampling technique **and** an actual or estimated respondent count with rationale, not just the technique name.

## Known open defects (as of 2026-09-07 analysis)

These were found by downloading the doc as plain text (File → Download → Plain Text, all tabs concatenate into one file) and diffing against the rules above — the canvas UI doesn't expose body text to automation, so always re-download rather than trying to scrape the rendered page. Re-verify each is still present before fixing (the user may have already addressed some) or before re-flagging them as new findings:

1. **Table/Figure renumbering needed across the whole paper.** Correct sequence should be Table 1 (Ch2 Technology Gaps) → Table 2 (Ch3 Dataset Attributes, currently mislabeled "Table 1") → Table 3 (Ch3 Quarterly Replenishment sample, currently mislabeled "Table 4") → Table 4 (Ch3 Dev Machine Spec, currently mislabeled "Table 2"). Figures: Figure 1 (Ch1 Theoretical Framework) → Figure 2 (Ch1 Conceptual Framework) → Figure 3 (Ch3 System Architecture, currently mislabeled "Figure 1").
2. **Corrupted text fragment** at the very top of the "CHAPTER 3" tab body: `Specifically, it aims toCHAPTER III` — a leftover phrase glued onto the "CHAPTER III" heading with no space. Delete the stray "Specifically, it aims to".
3. **Truncated sentence** in "Revised of CHAPTER I", Limitations paragraph: "...ICS is a separate, situational structure — distinct from CDRRMO'" cuts off mid-word with a dangling apostrophe and no closing punctuation. Do not guess the missing clause — ask the user what it was meant to say.
4. **Missing reference entry**: the new Chapter II subsection "Inventory Reorder-Point and Safety-Stock Theory" cites "(Silver, Pyke, & Peterson, *Inventory Management and Production Planning and Scheduling*)" in-text, but there is no matching entry in the Chapter II References list. Needs a full citation (edition, publisher, year) added.
5. **Heading convention violation**: "Heuristic Optimization → Quarterly Replenishment Advisory" (Chapter 3) is missing its "Operations Research → " root, unlike every sibling heading in Applied Concepts and Techniques.
6. **Stale glossary definitions**: "Key Information" and "Revised Additions (Ch. 1-2)" tabs still define OSRM as computing "turn-by-turn depot-to-zone directions" — contradicts the trimmed routing scope (route-line visualization only) now in Revised Ch1/Ch3. Only relevant if Definition of Terms is carried into the final paper from one of those staging tabs.
7. **Objectives outrun the Research Problem**: "Revised of CHAPTER I" has 8 Specific Objectives but only 5 Research Problem questions — objectives 6–8 (inventory deduction/reservation, quarterly reorder advisory, Inventory Burn-Rate Report) have no matching question. Either add matching questions 6–8 or fold those objectives under a broadened existing question.
8. **Incomplete Sampling Design**: states purposive/non-probability sampling across CDRRMO's six functional roles but never states or estimates a respondent count.

## Editing the Google Doc safely

- The doc body renders on a `<canvas>` — `get_page_text`/`read_page`/clipboard copy do not reliably return body text. To read current content, use **File → Download → Plain Text (.txt)** (downloads all tabs concatenated into one file) after getting the user's explicit OK (downloading any file requires permission).
- If typing directly into the doc (Ctrl+F to search, then typing a replacement), **always screenshot immediately after Ctrl+F to confirm the Find box actually has focus before typing** — typing while unfocused inserts text directly into the document body. This caused a real corruption incident in an earlier session (fixed via repeated Ctrl+Z).
- Clear the Find/Replace field (Ctrl+A) before typing a new query — leftover text from a prior search silently appends instead of being replaced, producing false "0 of 0" matches.
