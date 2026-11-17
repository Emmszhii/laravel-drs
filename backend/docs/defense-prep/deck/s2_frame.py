"""Slides 8-15: frameworks, scope, limitations and significance, Chapter II matrices and gap."""
import os

from pptx.enum.text import MSO_ANCHOR, PP_ALIGN

from lib import (INK, MUTED, NAVY, PALE, SOFT, TINT, W, X0, arrow, box, card, circle, notes, picture, source,
                 table, text)

HERE = os.path.dirname(os.path.abspath(__file__))
FIGS = os.path.normpath(os.path.join(HERE, "..", "..", "panel-review", "2026-11-08", "figures"))
ASSETS = os.path.join(HERE, "assets")


def theory(d):
    s = d.new("Theoretical framework: one theory per part")
    picture(s, os.path.join(ASSETS, "fig01-theoretical-framework.png"), X0, 1.85, 6.7, 5.0, "left")
    x = X0 + 6.95
    cards = [("Optimization under uncertainty", "Per-item multiplier (Stage 1) and sample average approximation (Stage 2)"),
             ("Explainable decision support", "A reason for each order; the budget curve; dashboard levels of situation awareness"),
             ("Local-first, CRDT counter, hash chain", "Offline outbox, entries that add or subtract, and the record check")]
    for i, (h, b) in enumerate(cards):
        card(s, x, 1.9 + i * 1.6, W - 6.95, 1.45, h, [b], size=17, head_size=18)
    text(s, x, 6.7 - 0.05, W - 6.95, 0.35, ["Used as design guides; the system does not implement each theory in full."],
         12, MUTED)
    notes(s, """Figure 1 shows three theories, one for each part of the system. First, optimization under uncertainty.
    Linear programming chooses the best decisions within limits. Here the orders are the decisions, the budget
    and depot space are the limits, and the shortage left after ordering is what the model minimizes. But a
    disaster's time and size are not known in advance, so instead of one forecast, sample average approximation
    treats each of the office's past quarters as one possible next quarter and chooses the orders with the
    lowest average shortage. That is Stage 2, the scenario model. Stage 1, the per-item multiplier, is the
    simpler form: each item's target is what would have covered most of its own past quarters. Second,
    explainable decision support. Decision-makers need to understand what drives a model's output before they
    rely on it. Situation awareness gives three levels: see the current state, understand what it means, and
    project what comes next. The dashboard follows them: current stock and use, a reason for each order, and the
    budget curve, which shows how the shortage changes with the budget. Third, local-first computing and
    tamper-evident logging. Work continues on the device without a connection and merges later. Because every
    stock change is an entry that adds or subtracts, the entries give the same total in any order. Each entry is
    linked to the one before it by a hash, so a later edit shows. This guides the offline records and Check
    records. These theories guide the design. I do not measure situation awareness, and the system uses only the
    add-and-subtract counter, not a full replication framework.""")


def ipo(d):
    s = d.new("Conceptual framework: input, process, output")
    picture(s, os.path.join(FIGS, "fig02-conceptual-framework-ipo.png"), X0, 1.85, 7.0, 4.95, "left")
    x = X0 + 7.25
    card(s, x, 1.9, W - 7.25, 1.7, "Input",
         ["Seven model inputs; VED classes, coverage, and weights proposed by Logistics, confirmed by the Head of Office"],
         size=16, head_size=19)
    card(s, x, 3.75, W - 7.25, 1.45, "Process",
         ["Scrum sprints and the model generation steps; each run: data checks, solve, reasons, decision"],
         size=16, head_size=19)
    card(s, x, 5.35, W - 7.25, 1.45, "Output",
         ["The system itself: recommendation, then approval, then the purchase plan; plus the evaluation results"],
         size=16, head_size=19, fill=PALE)
    notes(s, """Figure 2 is the conceptual framework, an Input–Process–Output model. Input: the seven things the model
    needs. Each item's past quarterly use, recorded as both requested and released quantities; current stock,
    computed from the stock entries; unit prices; the quarterly budget entered by the Head of Office; the free
    depot space and each item's space per unit; each item's VED class; and each class's coverage level and
    priority weight. Logistics proposes the VED classes and weights, and the Head of Office confirms them before
    any run. Process: two strands. The system is built through Scrum sprints of planning, execution, review, and
    retrospective, and the model inside it through the optimization model generation steps, from problem
    definition and data cleaning to evaluation and deployment. Once built, every quarter's run has four steps:
    data checks flag doubtful entries for staff to confirm or correct; the solver computes the orders; the
    system gives a reason for each order and builds the budget curve; and the Head of Office approves or
    rejects. Output: DisasterResource+ itself. It gives recommended orders with their reasons and the budget
    curve, and once the Head of Office approves, the quarter's purchase plan, with received items recorded in
    tamper-evident stock records. The evaluation results, from the backtest, the tests, the explanation task,
    and the acceptability survey, complete the output.""")


def scope(d):
    s = d.new("Scope and delimitations")
    half = (W - 0.3) / 2
    card(s, X0, 1.9, half, 4.6, "In scope: preparedness",
         ["Keeping CDRRMO-held supplies counted, stocked, and accounted for, across its depots",
          "Records from Q3 2023 to Q2 2026: up to 12 quarters, including Kristine and Leon",
          "Five parts: the model, explanations, stock records, the ICS 213-RR and borrowed-equipment module, "
          "and the dashboard with a quarterly report",
          "Access by the office's divisions; the Head of Office is the system Admin",
          "Office server; Chrome or Edge on the office's laptops and Android phones"], size=17, items=True)
    card(s, X0 + half + 0.3, 1.9, half, 4.6, "Outside the scope",
         ["Response operations: incident command, dispatch, and relief distribution",
          "Stock of the city's social welfare, health, and general services offices; barangay-initiated requests",
          "Buying and bidding, which stay with the GSO under RA 12009",
          "Supplier lead time and item expiry in the model",
          "Measuring better preparedness or response outcomes"], size=17, items=True, fill=SOFT)
    notes(s, """The scope is preparedness: keeping CDRRMO's own supplies counted, stocked, and accounted for. The
    study uses the office's records from the third quarter of 2023 to the second quarter of 2026, up to twelve
    quarters, which include Kristine and Leon. It covers five parts: the model, the explanations, the stock
    records, the request and borrowed-equipment module, and the dashboard. Outside the scope are response
    operations, the stock of other city offices, barangay requests, procurement and bidding, which stay with the
    General Services Office, and supplier lead time and expiry. The study also does not measure whether
    preparedness improves; that would need observation over actual typhoon seasons.""")


def limits(d):
    s = d.new("Limitations and significance")
    lw = 6.2
    lims = [("Data", "8–12 quarters show a direction, not statistical proof; released quantities understate need "
                     "in stock-out quarters, which are marked"),
            ("Model", "No lead time or expiry; orders arrive before the quarter; one pool of depot space; items with "
                      "fewer than 4 quarters use the flat 1.5 rule"),
            ("Operating environment", "One approver (an acting Admin covers absences); HTTPS required; Background Sync "
                                      "is Chromium-only; no off-grid link; a full rebuild of the chain needs the printed fingerprint")]
    for i, (h, b) in enumerate(lims):
        card(s, X0, 1.9 + i * 1.6, lw, 1.45, h, [b], size=16, head_size=18)
    x = X0 + lw + 0.3
    card(s, x, 1.9, W - lw - 0.3, 4.65, "The study may support…",
         ["Logistics: orders fitted to each item's history, with a reason to cite",
          "Research and Planning: a budget curve for annual planning",
          "Head of Office and City Government: records that show edits, ready for COA audits",
          "Operations and Warning: team kits restocked from their own requests",
          "Residents: supplies on hand before a typhoon (not measured)",
          "Future researchers: a backtest other LGU offices can repeat"], size=16, items=True, fill=PALE)
    notes(s, """The limitations fall into three groups. Data: eight to twelve quarters is a small sample, so
    the backtest shows a direction, not statistical proof, and quarters when stock ran out understate the real
    need. Model: it ignores lead time and expiry and assumes orders arrive before the quarter starts. Operating
    environment: only the Admin approves, Background Sync works only in Chromium browsers, and there is no
    off-grid link. On significance, I use potential-contribution language: the system may support Logistics,
    Research and Planning, the Head of Office, and the response teams. The study does not claim it will reduce
    shortages or improve response outcomes, because those are not measured.""")


def lit_planning(d):
    s = d.new("Literature: planning stock from short records")
    rows = [["Author (year)", "Focus", "Method", "Key finding", "Relevance to this study"],
            ["Shariati et al. (2026)", "Review of 97 prepositioning studies", "Systematic review", "Models are underused in practice", "Need for usable planning tools"],
            ["Acar & Kaya (2022)", "Budget across relief items", "Optimization model", "Splits one budget before or after a disaster", "Closest formulation"],
            ["Shehadeh & Tucker (2022)", "Scenario-based stock levels", "Stochastic optimization", "Optimizes against scenarios, not one forecast", "Basis of the scenario model"],
            ["Uichanco (2022)", "DSWD prepositioning (PH)", "Stochastic optimization", "Plans stock for uncertain typhoon paths", "Closest local precedent"],
            ["Rodríguez-Espíndola et al. (2023)", "105 humanitarian OR studies", "Literature investigation", "Only 10% involved practitioners", "Design for staff, not experts"],
            ["Besbes & Mouchtaki (2023)", "Deciding from past data", "Data-driven analysis (SAA)", "Tens of observations can already work well", "Plan from the office's own quarters"],
            ["Hewamalage et al. (2023)", "Forecast evaluation", "Methods review", "Rolling origin with simple benchmarks", "Basis of the backtest"],
            ["Meena & Mathaiyan (2024); Gizaw & Jemal (2021)", "VED analysis", "ABC–VED analysis", "Classes by the harm an item's absence causes", "Priority weights and coverage"]]
    table(s, X0, 1.9, W, rows, [2.7, 2.3, 1.9, 2.8, 2.3], size=14, row_h=0.56, bold_first=True)
    notes(s, """I present the literature as matrices. On planning: relief inventory models are well studied
    but underused in practice. Acar and Kaya's budget model is the closest formulation, and Shehadeh and Tucker's
    scenario approach is the basis of my scenario model; locally, Uichanco planned DSWD prepositioning against
    uncertain typhoon paths. Two studies justify planning from short records instead of forecasting: Besbes and
    Mouchtaki showed that deciding directly from tens of past observations can already work well, and Hewamalage
    and colleagues recommend rolling-origin evaluation against simple benchmarks, which is my backtest. VED
    analysis supplies the priority classes.""")


def lit_records(d):
    s = d.new("Literature: explanations and record integrity")
    rows = [["Author (year)", "Focus", "Method", "Key finding", "Relevance to this study"],
            ["De Bock et al. (2024)", "Explainable operations research", "Framework and review", "Explanations of optimization lag behind", "Reasons read from the solved model"],
            ["Korikov et al. (2021); Kurtz et al. (2026)", "Counterfactual explanations", "Counterfactual methods", "What must change for a different answer", "Budget curve and next ₱10,000"],
            ["Ivanković et al. (2021)", "158 public COVID-19 dashboards", "Descriptive assessment", "Actionable ones knew their audience", "Dashboard design"],
            ["Almeida (2025); Borrego et al. (2025)", "Replicated counters", "CRDT survey; formal method", "Entries converge; negatives only preventable within reservations", "Entries, not totals; conflicts flagged"],
            ["Antonopoulos et al. (2021)", "Ledger databases", "Ledger database design", "A hash chain makes old edits detectable", "Hash chain; fingerprint on the report"],
            ["COA (2021); Mantel et al. (2025)", "Public inventory accuracy", "Audit; impact study", "OCD inventory of ₱552M unreliable; parallel records less accurate", "One system of record"],
            ["NPC (2025); Cobrado et al. (2024)", "Privacy and access control", "Guidelines; systematic review", "Least privilege; access logs rarely studied", "Borrower data for Admin and Logistics only"]]
    table(s, X0, 1.9, W, rows, [2.7, 2.2, 1.9, 3.0, 2.3], size=14, row_h=0.62, bold_first=True)
    notes(s, """The second matrix covers the other two contributions and the accountability topics. De Bock
    and colleagues show that explanations of optimization lag behind those of predictive models; counterfactual
    explanations ask what would have to change, which is what my budget curve asks of the budget. On offline
    records, replicated counters converge when entries add or subtract, but Borrego and colleagues show negatives
    can be prevented without coordination only within reservations, so my system flags conflicts instead of
    refusing them. Ledger databases make old edits detectable. COA audits show public inventory records drift, and
    the privacy rules call for least-privilege access.""")


def related(d):
    s = d.new("What existing systems document")
    Y, N, ND = "Yes", "No", "ND"
    rows = [["System", "Stock", "Expiry", "Reorder basis", "Budget limit", "Scenarios", "Explained", "Offline", "Merging",
             "Tamper-evident", "Data checks"],
            ["DSWD DRC tools", Y, Y, "75% call-off", N, N, N, ND, ND, ND, ND],
            ["PAHR (DSWD)", N, N, "Rainfall forecast", N, N, N, ND, ND, ND, ND],
            ["PIMS", Y, ND, ND, N, N, N, Y, ND, ND, ND],
            ["IRespondPH", N, ND, N, N, N, N, ND, ND, ND, ND],
            ["E-LIGTAS", Y, N, N, N, N, N, ND, ND, ND, ND],
            ["Calamba City CDRRMO", Y, ND, "Text analysis", N, N, N, ND, ND, ND, ND],
            ["MDRRMO Sablayan", N, N, N, N, N, N, ND, ND, ND, ND],
            ["DisasterResource+", Y, N, "Multiplier, scenarios", Y, Y, Y, Y, Y, Y, Y]]
    gt = table(s, X0, 1.85, W, rows, [2.3, 0.8, 0.85, 1.45, 0.95, 1.05, 1.15, 0.9, 0.95, 1.2, 0.95], size=14, row_h=0.5,
               bold_first=True)
    last = len(rows) - 1
    for j in range(len(rows[0])):
        c = gt.table.cell(last, j)
        c.fill.solid(); c.fill.fore_color.rgb = PALE
    source(s, "Yes = documented in the cited sources; No = described, but the feature is not among them; ND = not documented. "
              "Neither No nor ND proves a system lacks a feature (Table 2).")
    notes(s, """This matrix compares only what each system's published sources document. Yes means
    documented; No means the sources describe the system and the feature is not among them; ND means not
    documented. Neither No nor ND proves a system lacks a feature. Stock tracking, expiry tracking, and offline
    use are already documented elsewhere, so I do not claim them. What no reviewed system documents is
    budget-limited planning, scenarios, explained recommendations, conflict-safe merging, tamper-evident records,
    and data checks. The closest local system, Calamba City's, supports live response; this study addresses supply
    readiness.""")


def synthesis(d):
    s = d.new("Synthesis: three gaps, three questions")
    gaps = [("Replenishment planning", "Budget-limited planning from an office's own quarters is in research, but in no "
                                       "reviewed system", "RQ2"),
            ("Explanations", "No reviewed system documents explanations for its recommendations; the methods are untested with "
                             "government staff", "RQ4"),
            ("Offline and tamper-evident records", "No reviewed relief system documents how offline stock changes "
                                                   "merge, or how its records show later edits", "RQ3")]
    for i, (h, b, rq) in enumerate(gaps):
        y = 1.9 + i * 1.25
        card(s, X0, y, 8.9, 1.15, h, [b], size=17, head_size=18)
        arrow(s, X0 + 9.05, y + 0.38, 0.75, 0.35)
        circle(s, X0 + 9.95, y + 0.12, 0.85, rq, size=17)
    box(s, X0, 5.75, W, 0.9, PALE)
    text(s, X0 + 0.2, 5.78, W - 0.4, 0.85,
         [[("What the study adds: ", {"bold": True, "color": NAVY}),
           ("not one new technique, but a combination not documented in the reviewed systems. RQ1 describes the "
            "practice it supports; RQ5 asks if personnel accept it.", {})]], 17, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """The synthesis turns the review into three gaps, and each leads to a research question. First,
    budget-limited planning from an office's own quarters appears in research but in no reviewed system; question
    two tests it on the office's past quarters. Second, no reviewed system documents explanations for its recommendations, and the
    methods have not been tested with government staff; question four tests that. Third, no reviewed relief system
    documents how offline stock changes are merged or how its records reveal later edits; question three tests
    both. The contribution is a combination, not a single new technique, and the gaps belong together: a model fed
    wrong stock figures gives wrong orders.""")


def add(d):
    for f in (theory, ipo, scope, limits, lit_planning, lit_records, related, synthesis):
        f(d)
