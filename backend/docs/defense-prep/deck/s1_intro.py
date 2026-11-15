"""Slides 1-7: title, Chapter I context, problem, questions, objectives, alignment."""
import copy

from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN

from lib import (BLUE, INK, MUTED, NAVY, PALE, SOFT, TINT, WHITE, W, X0, Y0, arrow, box, bullets, card,
                 circle, notes, source, table, text)

TITLE = "DisasterResource+: A Web-Based Resource Optimization and Visualization System for Disaster Response"


def title_slide(d, adviser="[Adviser]", date="[Date of defense]", venue="[Venue]"):
    s = d.title_slide
    for sh in list(s.shapes):
        t = sh.text_frame.text if sh.has_text_frame else ""
        if "seal" in t or "Font size may" in t:
            sh._element.getparent().remove(sh._element)   # template instructions

    def put(sh, lines):
        """Replace the box's lines, keeping each paragraph's template formatting."""
        tf = sh.text_frame
        ps = [p._p for p in tf.paragraphs]
        while len(ps) < len(lines):
            n = copy.deepcopy(ps[-1])
            ps[-1].addnext(n)
            ps.append(n)
        for p in ps[len(lines):]:
            p.getparent().remove(p)
        for p, line in zip(tf.paragraphs, lines):
            runs = p.runs or [p.add_run()]
            runs[0].text = line
            for r in runs[1:]:
                r._r.getparent().remove(r._r)
        return tf

    for sh in s.shapes:
        if not sh.has_text_frame:
            continue
        t = sh.text_frame.text
        if t.startswith("Insert Title"):
            tf = put(sh, [TITLE])
            tf.paragraphs[0].runs[0].font.size = d.pt(26)
        elif t.startswith("Insert Sub-title"):
            put(sh, ["Capstone Project Proposal Defense"])
        elif t.startswith("Insert Name of Presenter"):
            put(sh, ["Emmanuel B. Manalo", "Master in Information Technology", "College of Computer Studies",
                     "Laguna State Polytechnic University", "San Pablo City Campus"])
        elif t.startswith("Insert Date"):
            put(sh, [x for x in (date, venue, f"Adviser: {adviser}") if x])
    notes(s, """Good morning, members of the panel. I am Emmanuel Manalo, Master in Information
    Technology. I will present my capstone proposal, DisasterResource+, a web-based resource optimization and
    visualization system for CDRRMO San Pablo City. The study is about disaster preparedness: keeping the
    office's relief and response supplies counted, stocked, and accounted for, so they are ready before a
    typhoon arrives. I will present the problem and objectives, the literature and the gap, and then the
    method, the system design, and how it will be evaluated. A short demonstration video follows.""")


def context(d):
    s = d.new("Each quarter: what should be in stock?")
    stats = [("19–20", "tropical cyclones enter the Philippine Area of Responsibility each year; about 9 make landfall",
              "Galloway et al., 2025"),
             ("5%", "of an LGU's estimated regular revenue goes to its local DRRM fund; 30% of that is the quick response fund",
              "RA 10121; DBM, 2025"),
             ("46 of 80", "San Pablo City barangays were affected by Kristine and Leon in October 2024: 811 families, 3,330 persons",
              "DSWD, 2025a")]
    cw = (W - 0.6) / 3
    for i, (big, lab, src) in enumerate(stats):
        x = X0 + i * (cw + 0.3)
        box(s, x, 1.95, cw, 2.95, TINT)
        text(s, x + 0.15, 2.05, cw - 0.3, 1.0, [big], 48, NAVY, True, font="Century Gothic")
        text(s, x + 0.15, 3.05, cw - 0.3, 1.4, [lab], 19)
        text(s, x + 0.15, 4.42, cw - 0.3, 0.4, [src], 12, MUTED)
    box(s, X0, 5.2, W, 1.2, PALE)
    text(s, X0 + 0.2, 5.25, W - 0.4, 1.1,
         [[("Preparedness starts with a quarterly question: ", {"bold": True, "color": NAVY}),
           ("what should be in stock, and how much of it can the budget buy? ", {"italic": True}),
           ("This study concerns that readiness work before an event, not response operations during it.", {})]],
         18, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """The Philippines sees about nineteen to twenty tropical cyclones a year, and about nine make
    landfall. Under Republic Act 10121, every local government sets aside at least five percent of its regular
    revenue as a DRRM fund, and part of that buys the supplies a city disaster office keeps on its shelves.
    In October 2024, Kristine and Leon affected 46 of San Pablo's 80 barangays and more than three thousand
    persons. So every quarter the office faces the same question: what should be in stock, and how much can
    the budget buy? I want to be clear about scope from the start: this study is about preparedness, the work
    before an event. Incident command, dispatch, and relief distribution are outside it.""")


def practice(d):
    s = d.new("How the office manages its supplies today")
    steps = ["Requests on the ICS 213-RR form", "Borrowed equipment signed out and in through a logbook",
             "Stock kept in Excel, backed by paper", "Quarterly review: leftover stock and consumption reports",
             "Specifications sent to the General Services Office (GSO) for bidding"]
    bw, gap = 2.05, 0.31
    for i, st in enumerate(steps):
        x = X0 + i * (bw + gap)
        card(s, x, 1.95, bw, 1.95, [(f"{i + 1}", {"size": 22})], [st], fill=TINT, size=16)
        if i < len(steps) - 1:
            arrow(s, x + bw + 0.04, 2.75, gap - 0.08, 0.3)
    card(s, X0, 4.1, W * 0.62, 2.4, "What Logistics reported (interview, September 3, 2026)",
         ["Counting errors are the most time-consuming, error-prone part of the work",
          "Shortages become known only when stock is checked",
          "Inventory is the first thing they would automate",
          "Any tool must keep working through internet and power outages",
          "No written rule sets how much extra to keep each quarter"], fill=SOFT, size=15, items=True)
    card(s, X0 + W * 0.62 + 0.3, 4.1, W * 0.38 - 0.3, 2.4, "Core idea: DisasterResource+",
         ["Plan each quarter's order from the office's own history within its budget, explain every cut, and keep "
          "stock records that survive outages and show any edit."], fill=PALE, size=17)
    source(s, "Source: requirements interview with three key informants (the head of the Administration and Training "
              "Division and two Logistics personnel); photographed ICS 213-RR form and logbook page, kept on file.")
    notes(s, """This is the office's current process, from the requirements interview with three key informants on
    September 3, 2026. Requests are written on the ICS 213-RR form. Borrowed equipment is signed out and in
    through a logbook. Stock is kept in Excel sheets backed by paper. Each quarter, Logistics adjusts the next
    request from the stock left over and from consumption reports, but no written formula was described.
    Specifications then go to the General Services Office for bidding. Logistics named counting errors as its
    most persistent problem, said that shortages show up only when stock is checked, and stressed that any tool
    must survive internet and power outages. These are reported practices, not findings. The core idea of the
    study answers this setting: plan each quarter's order from the office's own history within its budget,
    explain every cut, and keep stock records that survive outages and show any edit.""")


def problem(d):
    s = d.new("Research problem")
    text(s, X0, 1.85, W, 0.75, [[("Main problem: ", {"bold": True, "color": NAVY}),
         ("the office plans each quarter's purchases from hand-kept counts with no written rule, and lacks three "
          "things.", {})]], 19, anchor=MSO_ANCHOR.MIDDLE)
    items = [("A replenishment basis", "that plans each item's order from that item's own history, within the budget",
              "Orders sized for an ordinary quarter can fall short in a typhoon quarter"),
             ("Explained recommendations", "that show what was cut, by how much, and why, when the budget falls short",
              "A shortfall is hard to defend in the next budget discussion"),
             ("Trustworthy stock records", "that stay correct through outages and show any later edit",
              "A release can be counted twice or missed; an edited figure leaves no trace")]
    cw = (W - 0.6) / 3
    for i, (h, b, eff) in enumerate(items):
        x = X0 + i * (cw + 0.3)
        card(s, x, 2.7, cw, 2.15, f"{i + 1}. {h}", [b], size=19, head_size=21)
        box(s, x, 5.0, cw, 1.3, PALE)
        text(s, x + 0.12, 5.03, cw - 0.24, 1.25, [[("Possible effect: ", {"bold": True, "color": NAVY}), (eff, {})]], 17)
    text(s, X0, 6.4, W, 0.45, ["The effects are what can follow from the current practice, stated as possibilities, "
                                "not as findings."], 14, MUTED)
    notes(s, """The problem the study addresses is that the office lacks three things. First, a replenishment
    basis that plans each item from its own history under the budget; without it, orders sized for an ordinary
    quarter can fall short in a typhoon quarter. Second, recommendations that explain what was cut and why
    when the budget falls short; without that, a shortfall is hard to defend. Third, stock records that stay
    correct through outages and reveal later edits; otherwise a release can be counted twice or missed. As the
    panel advised, I present these effects as possibilities that can follow from the current practice, not as
    established findings.""")


def questions(d):
    s = d.new("Research questions")
    qs = [("Office practice", "What is the office's current practice, and in how many recorded request lines did the "
                              "quantity released fall short of the quantity requested?"),
          ("Model", "Is there a difference in weighted shortage, fill rate, items that ran out, and budget used among "
                    "the office's actual orders, the flat 1.5 rule, a safety-stock rule, the per-item multiplier, and "
                    "the scenario model, on past quarters each has not seen?"),
          ("System", "To what extent do the stock records and request module meet their test criteria: no entry lost "
                     "or duplicated, priority first, every edit detected, every test case passed, including every status?"),
          ("System", "To what extent can the Head of Office, Logistics, and Research and Planning answer questions about a "
                     "recommendation using its reason and the budget curve?"),
          ("System", "What is the level of acceptability of DisasterResource+ under ISO/IEC 25010:2023?")]
    y = 1.95
    for i, (tag, q) in enumerate(qs):
        circle(s, X0, y + 0.05, 0.55, str(i + 1))
        box(s, X0 + 0.7, y + 0.1, 1.55, 0.45, TINT)
        text(s, X0 + 0.7, y + 0.1, 1.55, 0.45, [tag], 14, NAVY, True, PP_ALIGN.CENTER, MSO_ANCHOR.MIDDLE)
        text(s, X0 + 2.4, y - 0.04, W - 2.4, 1.0, [q], 18)
        y += 1.0
    notes(s, """The study asks five questions, simplified as the panel suggested, and none of them asks how to build
    something. Question one describes the office's current practice and counts the request lines where less was
    released than requested. Question two concerns the model: whether the per-item multiplier or the scenario
    model gives less weighted shortage than the office's actual orders and two simple rules on past quarters.
    Questions three to five concern the system: whether the records and request module meet their test
    criteria, whether staff can use the explanations, and whether personnel find the system acceptable under
    ISO/IEC 25010.""")


def objectives(d):
    s = d.new("Research objectives: develop, then determine")
    box(s, X0, 1.9, W, 0.95, PALE)
    text(s, X0 + 0.15, 1.92, W - 0.3, 0.9, [[("General: ", {"bold": True, "color": NAVY}),
         ("design, develop, and evaluate DisasterResource+ to support quarterly replenishment decisions for "
          "CDRRMO San Pablo City", {})]], 17, anchor=MSO_ANCHOR.MIDDLE)
    rows = [["No.", "Develop or describe", "Determine (criterion)"],
            ["1", "Describe current practice and count short releases", "Descriptive counts"],
            ["2", "Design the model in two forms; backtest it", "Lower weighted shortage in a majority of backtested quarters"],
            ["3", "Develop stock records and the request module", "0 duplicates, 0 losses; priority first; every edit detected"],
            ["4", "Develop the dashboard with reasons and a budget curve", "At least 80% correct in the explanation task"],
            ["5", "Evaluate acceptability (ISO/IEC 25010:2023)", "Weighted mean 4.21–5.00 per characteristic"]]
    table(s, X0, 3.05, W, rows, [0.6, 5.2, 5.7], size=17, row_h=0.62)
    notes(s, """The general objective is to design, develop, and evaluate the system to support, not to improve,
    the office's quarterly decisions. Each specific objective now separates what will be built from what will be
    determined, and none promises a result in advance. Objective two determines whether either model form gives
    lower weighted shortage than the comparison rules in a majority of backtested quarters. Objective three
    tests the records for zero duplicates and losses. Objective four checks whether staff answer at least eighty
    percent of the explanation questions correctly. Objective five uses the Very Acceptable range as the
    criterion. Each objective matches one research question.""")


def alignment(d):
    s = d.new("One objective and one procedure per question")
    rows = [["RQ", "Concerns", "Chapter III procedure", "Criterion"],
            ["1", "Office practice", "Requirements gathering; encoded records", "Descriptive counts"],
            ["2", "Model", "Rolling-origin backtest (Model Evaluation)", "Lower weighted shortage in most quarters"],
            ["3", "System", "Unit, integration, offline resilience and integrity tests", "0 duplicates and losses; all cases pass"],
            ["4", "System, through the model's output", "UAT: explanation task", "At least 80% correct"],
            ["5", "System", "UAT: ISO/IEC 25010:2023 survey", "Weighted mean 4.21–5.00"]]
    table(s, X0, 2.0, W, rows, [0.6, 2.6, 4.8, 3.5], size=17, row_h=0.7)
    source(s, "Table 1 of the paper (Alignment of Research Questions, Objectives, and Procedures).")
    notes(s, """This table answers the panel's request for one-to-one alignment. Each research question has one
    objective and one procedure in Chapter Three, and each procedure has a stated criterion. Question one is
    answered from the interview and the encoded records. Question two is answered by the rolling-origin
    backtest. Question three by the unit, integration, and offline tests. Questions four and five by user
    acceptance testing: the explanation task and the ISO/IEC 25010 survey. So the panel can trace every
    question to how it will be answered and what counts as meeting it.""")


def add(d):
    title_slide(d, adviser="Mr. Joesua Manzanero", date="November 18, 2026", venue="")
    for f in (context, practice, problem, questions, objectives, alignment):
        f(d)
