"""Slides 16-22: design and participants, requirements, applied concepts, model, worked example,
evaluation, data."""
import os

from pptx.enum.text import MSO_ANCHOR, PP_ALIGN

from lib import (BLUE, INK, MATH_FONT, MUTED, NAVY, PALE, SOFT, TINT, WHITE, W, X0, box, card, circle, notes,
                 picture, source, table, text)
from s2_frame import FIGS


def design(d):
    s = d.new("Research design, locale, and participants")
    lw = 4.9
    card(s, X0, 1.9, lw, 2.3, "Developmental design",
         ["Design science: build the artifact, then evaluate it, with descriptive-evaluative and comparative "
          "(backtest) parts",
          [("Why it fits: ", {"bold": True, "color": NAVY}),
           ("the study must build a system and show whether it works for the office.", {})]], size=16)
    card(s, X0, 4.35, lw, 2.2, "Locale: CDRRMO San Pablo City",
         ["Head of Office and an assistant head; four divisions; Logistics is a section under Administration and "
          "Training; 80 barangays"], size=17, fill=SOFT)
    rows = [["Activity", "Participants", "Number"],
            ["Requirements interview", "Head of Admin and Training, 2 Logistics staff", "3"],
            ["Offline resilience test", "Logistics on 2 laptops, 2 phones", "4 devices"],
            ["User acceptance tasks", "All users, by role", "≥ 20 (estimate)"],
            ["Explanation task", "Head of Office, Logistics, Research and Planning", "All available"],
            ["ISO/IEC 25010 survey", "All users", "≥ 20 (estimate)"]]
    table(s, X0 + lw + 0.3, 1.9, W - lw - 0.3, rows, [2.3, 3.0, 1.4], size=15, row_h=0.62, bold_first=True)
    text(s, X0 + lw + 0.3, 5.75, W - lw - 0.3, 0.8, ["Inclusion: CDRRMO personnel who hold or will hold an account, "
         "work on supplies, requests, or planning, and consent. The number of users is to be confirmed with the office."],
         13, MUTED)
    notes(s, """The study uses a developmental design grounded in design science: build the artifact, then
    evaluate it, with a descriptive-evaluative part and a comparative backtest. The locale is CDRRMO San Pablo
    City, with four divisions; Logistics is a section under Administration and Training. I separate stakeholders,
    users, and respondents. Three key informants joined the requirements interview: the head of Administration and
    Training and two Logistics staff. All system users, at least twenty by estimate, take the user acceptance
    tasks and the survey, which is total enumeration. That number is still to be confirmed with the office's staff
    list.""")


def requirements(d):
    s = d.new("Requirements gathered from the office")
    half = (W - 0.3) / 2
    card(s, X0, 1.9, half, 4.75, "Functional",
         ["Every stock change is an entry; stock is their sum", "Stock deducted when a request is released",
          "Digital ICS 213-RR with requested and released quantities", "Borrowed items: issued, returned, for repair, "
          "condemned, surrendered", "Consumable and borrowable items handled apart", "Head of Office approves "
          "recommendations and requests", "Quarterly orders within budget, with reasons", "Suspicious entries flagged "
          "before the model runs", "Quarterly report as PDF and CSV", "Access by division and section"],
         size=15, head_size=18, items=True)
    card(s, X0 + half + 0.3, 1.9, half, 3.35, "Non-functional",
         ["Works through internet and power outages; nothing lost or doubled", "Any later edit is detectable",
          "Office server; Chrome or Edge on laptops and Android phones; HTTPS", "Borrower data protected",
          "Fast enough for interactive use", "Explanations non-technical staff can act on"],
         size=15, head_size=18, items=True, fill=SOFT)
    box(s, X0 + half + 0.3, 5.4, half, 1.25, PALE)
    text(s, X0 + half + 0.45, 5.43, half - 0.3, 1.2, ["To be reviewed with the head of Logistics (Product Owner) and "
         "confirmed by the Head of Office before development; later changes enter the backlog."], 15, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """The interview produced ten functional and six non-functional requirements, listed in Table 6.
    Functionally, every stock change is an entry and stock is their sum; releases deduct stock; the ICS 213-RR
    form records requested and released quantities; borrowed equipment follows five statuses; the Head of Office
    approves; and the system recommends quarterly orders with reasons and flags suspicious entries. The
    non-functional requirements are working through outages without losing or doubling entries, detecting later
    edits, running on the office's devices over HTTPS, protecting borrower data, speed, and plain explanations.
    Before development, the head of Logistics, as Product Owner, will review them and the Head of Office will
    confirm them.""")


def concepts(d):
    s = d.new("Applied concepts and algorithm analysis")
    picture(s, os.path.join(FIGS, "fig03-applied-concepts.png"), X0, 1.85, 5.3, 4.2, "left")
    rows = [["Component", "Purpose", "Evaluated by"],
            ["Stage 1 multiplier (MILP)", "Order within budget from each item's history", "Weighted shortage, backtest (RQ2)"],
            ["Stage 2 scenario model (SAA)", "Order against past quarters as scenarios", "Weighted shortage, fill rate (RQ2)"],
            ["Reasons and budget curve", "Show what was cut and why", "Explanation task ≥ 80% (RQ4)"],
            ["Entries, outbox, merging", "Keep stock right through outages", "Offline tests: 0 lost, 0 duplicated (RQ3)"],
            ["SHA-256 hash chain", "Show any later edit", "Integrity tests: every edit found (RQ3)"],
            ["Pre-solve data checks", "Stop a run on suspicious data", "IT-04: run blocked until resolved"]]
    table(s, X0 + 5.55, 1.85, W - 5.55, rows, [2.0, 2.1, 2.1], size=13, row_h=0.6, bold_first=True)
    box(s, X0, 6.15, W, 0.6, PALE)
    text(s, X0 + 0.15, 6.17, W - 0.3, 0.56, [[("Algorithm analysis: ", {"bold": True, "color": NAVY}),
         ("the model is an optimizer, not a classifier, so it is judged by weighted shortage, feasibility on every "
          "constraint, and measured solve time, not accuracy.", {})]], 14, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """Figure 3 shows the applied concepts and techniques. It maps each technique to its discipline
    and to the part of the system that uses it. From operations research, two mixed-integer linear programs: the
    per-item multiplier and the scenario model, solved with PuLP and the open-source CBC solver. From distributed
    systems and information security, append-only stock entries with device-made IDs, and a hash chain. From data
    quality, three checks before every run: a quarter's use more than three times the usual with no event tagged,
    a stock figure below zero, and a difference from the weekly physical count. The table gives each component one
    purpose and one way it is evaluated, so no technique is claimed to do every task. Because the model is an
    optimizer, not a classifier, it is analyzed by weighted shortage, by feasibility on every constraint, and by
    measured solve time, not by accuracy or precision.""")


def model(d):
    s = d.new("The replenishment model: two stages")
    half = (W - 0.3) / 2
    m = {"font": MATH_FONT}
    for i, (h, sub, lines) in enumerate([
            ("Stage 1: per-item multiplier", "Target Tᵢ = covering level: use covered in k of the last 8 quarters",
             ["minimize Σᵢ wᵢ·uᵢ ÷ Tᵢ", "uᵢ ≥ Tᵢ − (qᵢ + xᵢ)", "Σᵢ cᵢ·xᵢ ≤ B,   Σᵢ vᵢ·xᵢ ≤ V", "xᵢ whole, 0 ≤ xᵢ ≤ max(0, Tᵢ − qᵢ)"]),
            ("Stage 2: scenario model (SAA)", "Each past quarter s is an equally likely next quarter",
             ["minimize (1 ÷ S) Σₛ Σᵢ wᵢ·uᵢₛ ÷ mᵢ", "uᵢₛ ≥ Dᵢₛ − (qᵢ + xᵢ)", "same budget and space limits", "xᵢ whole, xᵢ ≥ 0"])]):
        x = X0 + i * (half + 0.3)
        card(s, x, 1.9, half, 2.75, h, [sub] + [[(ln, m)] for ln in lines], size=16, head_size=18)
    rows = [["VED class", "Examples", "Covering level", "Weight"],
            ["Vital", "Medicines, first aid, rescue consumables", "Highest of last 8 quarters", "3"],
            ["Essential", "Hygiene kits, food packs, water", "2nd highest (7 of 8)", "2"],
            ["Desirable", "Office and routine items", "3rd highest (6 of 8)", "1"]]
    table(s, X0, 4.8, W * 0.66, rows, [1.4, 3.6, 2.6, 0.9], size=14, row_h=0.44, bold_first=True)
    text(s, X0 + W * 0.66 + 0.25, 4.8, W * 0.34 - 0.25, 1.85,
         ["q = stock on hand less reserved", "Proposed by Logistics, confirmed by the Head of Office, fixed before "
          "the backtest", "Under 4 quarters of history: flat 1.5, \"limited history\""], 13, MUTED, space=4)
    notes(s, """The model has two stages, and they are kept distinct. Stage one, the per-item multiplier,
    first sets each item's target from its own history: the amount that would have covered its use in k of its
    last eight quarters, with k set by the VED class. It then chooses whole-number orders that minimize the
    weighted shortfall below the targets, within the budget and the depot space. Stage two, the scenario model,
    sets no single target: it treats each past quarter as an equally likely next quarter and minimizes the average
    weighted shortage across them. Current stock counts after subtracting reserved quantities. Logistics proposes
    the classes and weights, and they are fixed before the backtest.""")


def example(d):
    s = d.new("Worked example at a ₱200,000 budget")
    rows = [["Item", "Least total shortage", "Even spread", "Same % cut"],
            ["Family food packs", "127 (42% short)", "218 (23%)", "218 (23%)"],
            ["Hygiene kits", "240 (0%)", "170 (23%)", "159 (27%)"],
            ["Bottled water (cases)", "350 (0%)", "210 (23%)", "232 (20%)"],
            ["Spent", "₱200,000", "₱200,000", "₱199,450"]]
    table(s, X0, 1.95, 7.3, rows, [2.4, 2.2, 1.6, 1.6], size=17, row_h=0.72, bold_first=True)
    text(s, X0, 5.75, 7.3, 1.1, ["Needs total ₱301,500. Illustrative values: the example shows how the model works and is not evidence of "
         "its performance. The least-shortage and same-% orders and the next ₱10,000 are reproduced by automated tests (UT-01 to UT-03)."], 14, MUTED)
    x = X0 + 7.6
    card(s, x, 1.95, W - 7.6, 2.85, "Reasons the system gives",
         [[("Water, hygiene kits: ", {"bold": True}), ("fully covered", {"italic": True})],
          [("Medical kits: ", {"bold": True}), ("not ordered: current stock already covers it", {"italic": True})],
          [("Food packs: ", {"bold": True}), ("partly covered: the budget ran out", {"italic": True})]], size=17)
    box(s, x, 4.95, W - 7.6, 1.75, PALE)
    text(s, x + 0.15, 5.0, W - 7.9, 0.75, ["+ ₱10,000"], 34, NAVY, True, font="Century Gothic")
    text(s, x + 0.15, 5.75, W - 7.9, 0.9, ["buys 20 more food packs: from 42% to 38% short"], 17)
    notes(s, """This illustrative example has four items and a two-hundred-thousand-peso budget against three hundred
    one thousand five hundred pesos of needs. The least-total-shortage objective spends first where each peso
    closes the most shortage, so water and hygiene kits are fully covered and food packs are left 42 percent short;
    the priority weights are how the office controls which items come first. The even spread and the same
    percentage cut leave every item about a quarter short. The explanation layer reads each item's reason from
    the solution, and the budget curve shows that the next ten thousand pesos buys twenty more food packs. These
    orders are reproduced by automated tests.""")


def evaluation(d):
    s = d.new("Evaluation: a rolling-origin backtest")
    qs = ["Q3'23", "Q4'23", "Q1'24", "Q2'24", "Q3'24", "Q4'24", "Q1'25", "Q2'25", "Q3'25", "Q4'25", "Q1'26", "Q2'26"]
    bw = W / 12
    for i, q in enumerate(qs):
        fill = TINT if i < 4 else (NAVY if i == 5 else BLUE)
        sh = box(s, X0 + i * bw + 0.03, 1.95, bw - 0.06, 0.6, fill)
        text(s, 0, 0, 0, 0, [q], 13, NAVY if i < 4 else WHITE, True, PP_ALIGN.CENTER, MSO_ANCHOR.MIDDLE, shape=sh, space=0)
    text(s, X0, 2.6, W, 0.45, ["First 4 quarters: history only. From the 5th, each quarter is planned only from the "
         "quarters before it, then scored on its recorded use. Dark: Kristine and Leon (Q4 2024)."], 13, MUTED)
    card(s, X0, 3.15, 3.25, 3.45, "Five rules compared",
         ["Office's actual orders (main comparison)", "Flat 1.5 rule", "Classical safety stock", "Per-item multiplier",
          "Scenario model"], size=16, items=True)
    rows = [["Measure", "Role", "Better"], ["Weighted shortage", "Primary", "Lower"], ["Fill rate", "Supplementary", "Higher"],
            ["Items that ran out", "Supplementary", "Lower"], ["Budget used", "Context", "≤ 100%"],
            ["Stock left over", "Context", "Less tied up"]]
    table(s, X0 + 3.45, 3.15, 4.45, rows, [2.0, 1.6, 0.85], size=14, row_h=0.57)
    card(s, X0 + 8.1, 3.15, W - 8.1, 3.45, "Decision rule",
         ["Better if it wins (lower weighted shortage) in more than half of the comparable quarters",
          "Mixed results reported as mixed", "Typhoon quarters shown one by one", "Equal-weights rerun as a check"],
         size=15, items=True, fill=PALE)
    notes(s, """The model is evaluated with a rolling-origin backtest. Each quarter from the fifth onward is planned
    using only the quarters before it, then scored against the use actually recorded. Five rules are compared:
    the office's actual orders, which are the main comparison, the flat 1.5 rule, a classical safety-stock rule,
    the per-item multiplier, and the scenario model. Weighted shortage is the primary measure; fill rate, items that
    ran out, budget used, and stock left over are supplementary. A model form is judged better if it wins in more
    than half the comparable quarters. Mixed results are reported as mixed, typhoon quarters individually, and the
    weights are fixed before the backtest.""")


def data(d):
    s = d.new("Data: sources, period, and preparation")
    half = (W - 0.3) / 2
    card(s, X0, 1.9, half, 1.6, "Primary data", ["Interview notes and transcript (RQ1); test results and logs (RQ3); "
         "explanation-task answers (RQ4); survey responses (RQ5)"], size=16)
    card(s, X0 + half + 0.3, 1.9, half, 1.6, "Secondary data", ["Forms, logbook, stock sheets: use and stock (RQ1, RQ2); "
         "delivery documents: actual orders; budget records, unit prices: costs; DSWD DROMIC: typhoon quarters"],
         size=16, fill=SOFT)
    card(s, X0, 3.7, half, 3.0, "Period and preparation",
         ["Q3 2023 to Q2 2026: up to 12 quarters; at least 8 needed", "Second encoder checks each quarter against the "
          "stock sheet", "Duplicates checked against the form; a file imports once", "No names or signatures encoded"],
         size=16, items=True)
    box(s, X0 + half + 0.3, 3.7, half, 3.0, PALE)
    text(s, X0 + half + 0.45, 3.75, half - 0.3, 2.9,
         [[("Interim sample dataset. ", {"bold": True, "color": NAVY}),
           ("The office's records are still being requested. Until they arrive, the system is developed and "
            "demonstrated on a sample dataset built from public sources, in the office's form fields. It is not "
            "evidence about the office; RQ1 and RQ2 need the office's records.", {})]], 17, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """The primary data are the interview, the test results, the explanation-task answers, and the survey
    responses. The secondary data are the office's own records: the forms, the logbook, the Excel sheets, the
    delivery documents for actual orders, the budget records, and published situation reports to tag typhoon
    quarters. The period is the third quarter of 2023 to the second quarter of 2026. A second encoder checks each
    quarter, duplicates are checked against the form, and no names are encoded. One honest note: the office's
    records are still being requested. Until they arrive, I use a sample dataset built from public sources. It
    shows the procedure works, but it is not evidence about the office.""")


def add(d):
    for f in (design, requirements, concepts, model, example, data, evaluation):
        f(d)
