# DisasterResource+ — Evaluation Protocol (Chapter III, Software Testing)

What each Chapter III test checks, how to run it, and its pass condition. Automated tests run unattended; the on-device and user tests need people and devices at the office.

## 1. Automated (run before every evaluation session)

```bash
cd solver-service && venv/Scripts/python -m pytest tests -v -s   # -s prints the measured solve times
cd backend && php artisan test
cd backend && php artisan stock:verify                            # "check records" on the live data
```

| Test                                | File                                                                                   | Pass condition                                                                                                                                             |
| ----------------------------------- | -------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Worked example (Stage 1)            | `solver-service/tests/test_replenishment.py`                                           | 127 / 240 / 350, spends ₱200,000; same-percentage cut 218 / 159 / 232 (₱199,450); next ₱10,000 = +20 food packs                                            |
| Solver unit tests                   | same                                                                                   | multiplier from a known history; every reason matches its constraint; curve never rises; budget never exceeded; money left only when every target is met |
| Scenario model and backtest         | same                                                                                   | Stage 2 buys for the typhoon quarter; backtest scores every rule from the 5th quarter on earlier quarters only                                             |
| Performance                         | `solver-service/tests/test_performance.py`                                             | Stage 2, 200 items × 12 scenarios < 2 s (measured 0.56 s); 11-point curve < 20 s (measured 10.1 s)                                                         |
| Integration: full cycle             | `backend/tests/Feature/EvaluationTest.php`                                             | budget submit → queued job → solver → stored run → dashboard update (Reverb event)                                                                         |
| Role-based access                   | same (`accessMatrix`)                                                                  | every role allowed / refused exactly as the permission matrix (system-spec.md §2.1)                                                                        |
| Request statuses, reserve / deduct  | `backend/tests/Feature/IcsRequestWorkflowTest.php`                                     | every request and borrowed-item status; approval reserves, release deducts; borrower data hidden from other roles                                         |
| Integrity                           | `backend/tests/Feature/StockLedgerTest.php`                                            | same batch 3× = 0 duplicates; offline return syncs after its borrow; two approvals over stock flagged; a directly edited row fails "check records" there |
| Scripted offline run                | `EvaluationTest::test_scripted_offline_run_…`                                          | 30 entries on 4 devices (5 priority, 3 two-device conflicts), uploaded out of order 3×: 100% saved, 0 duplicates, priority first, 3 conflicts flagged     |
| Dashboard figures match the DB      | `EvaluationTest::test_dashboard_figures_match_the_database`                            | stock shown = sum of entries; fingerprint shown = latest entry's hash                                                                                      |

## 2. Offline resilience on devices (3 runs, then Safari separately)

Devices: two Windows laptops (Chrome or Edge) and two Android phones (Chrome), each with the app installed as a PWA. Log in on each device as a Logistics user.

The service worker only registers over HTTPS or on `localhost`. At the office, serve the app over HTTPS: give the office server a certificate, or put Caddy/FrankenPHP in front.

1. Online, open **Stock** once on every device, so the page and the reference data are cached.
2. Switch every device to airplane mode.
3. Record the 30 scripted entries from the script sheet: stock receipts, consumable releases, and borrowed-item releases and returns. 5 are marked **Priority**, and 3 are cases where two devices change the same item.
4. Close the browser on each device, reopen it, and open **Stock**. The "Waiting to sync on this device" list must still show every entry.
5. Reconnect the devices one at a time. Let each finish syncing (the list empties), and note the time from reconnecting to empty.
6. On a laptop, open **Stock** and compare it with the script. Record entries saved against entries made, any duplicates or losses, whether priority entries arrived first, and each conflict and how it was flagged.
7. Run `php artisan stock:verify`. It must print "Records intact".

**Pass:** every run saves 100% of the entries, with 0 duplicates and 0 losses. Priority entries sync first, and every two-device conflict is flagged.

On iPhone Safari, install the app to the Home Screen first. Safari can delete site data after 7 days of browser use without a visit, but an installed app is exempt.

## 3. Performance: run to dashboard (< 1 s on the office network)

Open **Replenishment** on two devices. On one, the Admin runs the solver. On the other, time the gap from the Admin's click to the new run appearing (it arrives by Reverb). Repeat 10 times and report the median.

The solver's own time is shown next to each run ("solved in N ms").

## 4. User acceptance tasks

| Task                                                          | Who                         | Where                            |
| ------------------------------------------------------------- | --------------------------- | -------------------------------- |
| File an ICS 213-RR request                                    | Operations, Logistics       | ICS 213-RR                       |
| Lend and return a borrowed item                               | Logistics                   | ICS 213-RR → Borrowed equipment  |
| Record entries while offline and sync them                    | Logistics                   | Stock                            |
| Run the solver and approve or reject a recommendation         | Head of Office (Admin)      | Replenishment                    |
| Export the quarterly report                                   | Admin, Logistics, R&P       | Replenishment → Quarterly report |

## 5. Explanation task (target ≥ 80% correct)

Show Logistics and Research and Planning personnel three recommendations on the **Replenishment** page. For each one, ask:

1. *Why was this item cut?* It's correct if they read the item's reason, e.g. "partly covered: the budget ran out".
2. *What would ₱20,000 more change?* It's correct if they use **the next ₱10,000 goes to** list twice, or the budget curve.
3. *Which limit mattered?* It's correct if they name the budget or the depot space, from the reasons and the "Depot space not modeled" note.

Record the share of correct answers per person and overall.

## 6. ISO/IEC 25010:2023 survey

Give the survey after the UAT, to 15–20 respondents with at least 2–3 per role. It covers functional suitability, reliability, interaction capability and safety (fail safe). Use the weighted mean and Table 10's scale; the target is **Very Acceptable** (4.21–5.00) for each characteristic.
