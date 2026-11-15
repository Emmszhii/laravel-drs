"""Slides 23-30: ERD, Scrum and tools, architecture, sequence, offline sync, testing, UAT and ethics, demo."""
import os

from pptx.enum.text import MSO_ANCHOR

from lib import INK, MUTED, NAVY, PALE, SOFT, W, X0, box, card, notes, picture, source, table, text
from s2_frame import FIGS


def erd(d):
    s = d.new("Data model (Figure 11)")
    picture(s, os.path.join(FIGS, "fig11-erd.png"), X0, 1.8, 4.3, 5.1, "left")
    x = X0 + 4.6
    card(s, x, 1.9, W - 4.6, 4.7, "How the data fits together",
         ["The item is the center: history, data-check flags, counts, stock entries, and request lines all refer to it",
          "A request has lines; an approved line holds a reservation; a borrowable line issues one borrow per unit",
          "Stock entries carry a sequence number and the previous entry's hash; a conflict points to the correction "
          "entry that settles it",
          "Each run stores its inputs and result; links to User record who encoded, ran, or decided",
          [("Model inputs: ", {"bold": True}), ("history lines (use D), item (unit cost c, VED class, space v), "
           "stock entries less reservations (q), quarter budget (B), depot free space (V)", {})]],
         size=15, head_size=19, items=True)
    notes(s, """The course guide asks for the actual data model, so the paper now includes this entity-relationship
    diagram, drawn from the migrations. The item is the center of the model: the encoded history, the data-check
    flags, weekly counts, stock entries, and request lines all refer to it. A request has lines; an approved line
    holds a reservation, and a borrowable line issues one borrow per unit. Each stock entry carries its sequence
    number and the previous entry's hash, which forms the chain, and a conflict points to the correction entry
    that settles it. Each run stores the inputs it sent and the result, and the links to User record who did
    what. Every model input comes from these tables: use from the history lines, unit cost, VED class, and
    space from the item, stock on hand from the entries less reservations, the budget from the quarter budget,
    and free space from the depots, so each objective has the data it needs.""")


def scrum(d):
    s = d.new("System development: Scrum in six sprints")
    picture(s, os.path.join(FIGS, "fig04-scrum.png"), X0, 1.85, 6.3, 3.55, "left")
    rows = [["Sprint", "Goal"], ["1", "Multiplier and data encoding"], ["2", "Explanations"], ["3", "Stock records"],
            ["4", "Scenario model"], ["5", "Requests and dashboard"], ["6", "Testing and evaluation"]]
    table(s, X0 + 6.6, 1.9, W - 6.6, rows, [0.9, 3.9], size=16, row_h=0.5)
    # roles are already in the figure's caption box
    box(s, X0, 5.55, W, 1.15, SOFT)
    text(s, X0 + 0.15, 5.58, W - 0.3, 1.1, [[("Tools, and why: ", {"bold": True, "color": NAVY}),
         ("Laravel 13: queues, scheduling, and roles built in · React 18 with Inertia: one interface for laptops and "
          "phones · PostgreSQL 16: unique device IDs and the chain lock · FastAPI with PuLP 2.8.0 and CBC: open-source "
          "solver, kept apart from the data · Reverb: live updates with no cloud service · PWA and IndexedDB: works "
          "offline · Docker Compose: one command on the office server", {})]], 14, anchor=MSO_ANCHOR.MIDDLE)
    notes(s, """Development follows Scrum, shown in Figure 4 as Panel B asked. I am the development team and Scrum
    Master, the head of Logistics is the Product Owner, and each sprint ends with a review of the working increment. There are six
    two-week sprints. Sprints one to four build the three contributions, sprint five integrates the request module
    and the dashboard, and sprint six is reserved for testing and evaluation, so at least two weeks of testing
    follow integration. Features outside the objectives go to the backlog as future work. Each tool is chosen for
    a function: Laravel for the backend and queues, React for one interface on laptops and phones, PostgreSQL for
    the unique index and the chain lock, and PuLP with CBC for the model.""")


def architecture(d):
    s = d.new("System architecture (Figure 5)")
    picture(s, os.path.join(FIGS, "fig05-system-architecture.png"), X0, 1.8, 7.4, 5.1, "left")
    x = X0 + 7.65
    card(s, x, 1.9, W - 7.65, 4.7, "Five components",
         ["PWA: offline outbox in IndexedDB, one per user", "Laravel backend: requests, approvals, sync, queue, "
          "scheduler", "PostgreSQL: entries, hash chain, checkpoint", "Solver service: no database access",
          "Reverb: pushes run updates", "Borrower data only for Admin and Logistics; HTTPS; VPN outside the office"],
         size=17, head_size=19, items=True)
    notes(s, """The architecture has five components. The progressive web app runs in the browser; its outbox in
    IndexedDB holds entries made offline, each user's separately. The Laravel backend handles requests, approvals,
    and the sync endpoint, and runs the queue worker and the nightly record check. PostgreSQL holds the stock
    entries, the hash chain, and the last passed checkpoint. The solver service receives the items and budget and
    returns orders, reasons, and the budget curve; it never touches the database. Reverb pushes updates to the
    dashboard. Borrower names and contacts go only to the Admin and Logistics, and all traffic runs over HTTPS.""")


def sequence(d):
    s = d.new("Sequence of a quarterly run (Figure 12)")
    picture(s, os.path.join(FIGS, "fig12-sequence-run.png"), X0, 1.8, 4.35, 5.1, "left")
    x = X0 + 4.65
    card(s, x, 1.9, W - 4.65, 4.7, "What the sequence guarantees",
         ["Open data-check flags stop the run until Logistics confirms or corrects them",
          "The solve runs in a queued job; the dashboard updates when it ends",
          "A failed or infeasible solve marks the run failed with its error; it is not retried in the background",
          "Only a completed run can be approved or rejected, and an approval supersedes the quarter's earlier one",
          "Each run keeps its inputs, result, and who ran and decided it"], size=17, head_size=19, items=True)
    notes(s, """This sequence diagram follows one quarterly run. The Head of Office enters the budget; the backend
    saves it and runs the data checks, and an open flag stops the run until Logistics resolves it. Otherwise the run
    is created and queued. The worker reads each item's history, stock less reservations, price, and VED
    parameters, and calls the solver service, which returns the orders, reasons, and budget curve. The result is
    stored, and Reverb pushes it to the dashboard. This answers the panel's questions on deployment: a failed run
    is marked failed and never retried silently, only a completed run can be approved, and every run keeps its
    inputs.""")


def offline(d):
    s = d.new("Syncing offline entries (Figure 10)")
    picture(s, os.path.join(FIGS, "fig10-dfd-level-2-sync.png"), X0, 1.8, 6.4, 5.1, "left")
    x = X0 + 6.65
    cases = [("Repeated upload", "same device-made ID: ignored"),
             ("Dependent entry", "a return sent before its borrow: held, sent later"),
             ("Rejected entry", "fails validation: marked failed so its user sees it"),
             ("Conflicting entry", "negative stock, a return not out, or a concurrent edit: kept and flagged"),
             ("Final stock", "the sum of all entries, so it is correct in any arrival order")]
    for i, (h, b) in enumerate(cases):
        card(s, x, 1.9 + i * 0.96, W - 6.65, 0.86, [(h + ": ", {}), (b, {"bold": False, "color": MUTED})], [],
             size=16, head_size=17, fill=PALE if i == 4 else SOFT)
    notes(s, """Panel A asked how offline entries are merged; this Level 2 data flow diagram shows one batch from a
    device. A repeated upload carries a device-made ID the server already holds, so it is ignored. A dependent
    entry, such as a return sent before its borrow, waits on the device and is sent again. An entry that fails
    validation is rejected and marked failed in the device's queue, so its user sees it. A conflicting entry, one
    that would make stock negative or return a unit that is not out, is kept and flagged, because the goods have
    already moved; Logistics settles it with a correction entry. Since stock is the sum of all entries, the total is
    correct in any arrival order.""")


def testing(d):
    s = d.new("Testing plan")
    rows = [["Test type", "What is tested", "Where", "Pass criterion"],
            ["Unit (13 cases)", "Solver functions; ledger and request rules", "Development machine", "Every case passes"],
            ["Integration (11)", "Run cycle, requests, roles, report", "Docker Compose stack", "Every case passes"],
            ["Offline and integrity (13)", "Sync, priority, conflicts, edits", "Office server, 2 laptops, 2 phones",
             "3 runs: 100% saved, 0 duplicates, every edit found"],
            ["Performance (4)", "Solve, budget curve, dashboard", "Development; office network", "Stage 1, 2 < 2 s; curve < 20 s; "
             "dashboard < 1 s"],
            ["User acceptance (6)", "Role tasks, explanation task, survey", "Office server and devices", "≥ 80% correct; "
             "4.21–5.00 per characteristic"]]
    table(s, X0, 1.9, W, rows, [2.5, 3.2, 2.6, 3.3], size=16, row_h=0.74, bold_first=True)
    source(s, "Expected results come from the worked example (the reference test), the system's rules, and the objectives' "
              "targets; cases cover normal and exceptional inputs (Tables 16–21).")
    notes(s, """As both panelists asked, the paper now has a testing plan and test-case tables for every type of
    test. Unit tests cover the solver and the stock and request rules; the worked example is the reference test.
    Integration tests cover the full run cycle, requests and borrowed items, role access, and the report. The
    offline and integrity test runs on the office's server with two laptops and two phones, three times, and must
    save every entry with zero duplicates and detect every edit. Performance tests set time limits for the solve,
    the budget curve, and the dashboard update. User acceptance testing covers the role tasks, the explanation
    task, and the survey. The cases include exceptional inputs: a zero budget, no history, a repeated upload, and a
    failed solver.""")


def uat(d):
    s = d.new("User acceptance testing and ethics")
    cw = (W - 0.6) / 3
    card(s, X0, 1.9, cw, 4.7, "Explanation task",
         ["3 recommendations × 3 questions = 9 questions", "Head of Office, Logistics, Research and Planning",
          "Criterion: at least 80% correct, pooled"], size=18, items=True)
    card(s, X0 + cw + 0.3, 1.9, cw, 4.7, "ISO/IEC 25010:2023 survey",
         ["Functional suitability, reliability, interaction capability, security, safety", "5-point scale; Very "
          "Acceptable = 4.21–5.00", "Validated by 3 IT faculty; pilot-tested; Cronbach's α ≥ 0.70", "Anonymous; given "
          "by someone other than the developer"], size=17, items=True, fill=SOFT)
    card(s, X0 + 2 * (cw + 0.3), 1.9, cw, 4.7, "Ethics",
         ["Written permission of the Head of Office", "Informed consent; may withdraw", "No names or signatures "
          "encoded; borrower details by role", "Data Privacy Act of 2012; data returned or deleted after the study"],
         size=18, items=True, fill=PALE)
    notes(s, """User acceptance has two instruments. The explanation task gives the Head of Office, Logistics, and
    Research and Planning three recommendations with three questions each; the criterion is at least eighty percent
    correct. The survey uses five ISO/IEC 25010 characteristics chosen for this system, and each item describes
    something the respondent did, not overall satisfaction. Users' perceptions are reported separately from the
    technical tests of reliability, security, and safety. Three IT faculty validate the instrument, it is
    pilot-tested, and its reliability is reported as Cronbach's alpha. Ethically, the study needs the Head of
    Office's written permission and each participant's informed consent, and it follows the Data Privacy Act.""")


def demo(d):
    s = d.new("System demonstration")
    picture(s, os.path.join(FIGS, "fig07-wireframes.png"), X0, 1.8, 6.6, 5.1, "left")
    card(s, X0 + 6.9, 1.9, W - 6.9, 4.7, "The video shows (about 3½ minutes)",
         ["Replenishment: reasons, budget curve, next ₱10,000, approval", "Backtest on the sample dataset", "Stock "
          "entries offline, then synced; a conflict flag; Check records", "ICS 213-RR request, approval, release; "
          "borrowed equipment", "Quarterly report with the record fingerprint"], size=17, head_size=19, items=True)
    text(s, X0 + 6.9, 6.62, W - 6.9, 0.35, ["Demo data is the sample dataset, not the office's records."], 12, MUTED)
    notes(s, """I will now play a short demonstration of the prototype, built on the sample dataset because the
    office's records are still being requested. It shows the recommendation with its reasons, the budget curve and
    the next ten thousand pesos, and the approval; the backtest procedure; stock entries made offline and synced,
    with a conflict flag and the record check; an ICS 213-RR request from filing to release, with borrowed
    equipment; and the quarterly report with its fingerprint. [Play the video.] That ends my presentation. Thank
    you, and I welcome the panel's questions.""")


def add(d):
    for f in (erd, scrum, architecture, sequence, offline, testing, uat, demo):
        f(d)
