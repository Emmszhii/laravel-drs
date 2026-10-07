# DisasterResource+ — Client Demo Script, Q&A and Discovery (EN / Tagalog)

Built from: the 2026-09-03 interview (`docs/important/transcription summary.txt`), the photos of CDRRMO's real ICS 213-RR form and borrowed-items logbook, `system-spec.md`, and the recorded demo (`demo-video/record.mjs`). The steps in section 2 follow that recording in order, with the same sample values, so the live demo and the video say the same thing. **Check the transcript against the two interview recordings (`.m4a`) before you quote the client.**

Audience: CDRRMO San Pablo (Head of Office, Logistics, Planning). One window, signed in as the **Admin** (`admin@disasterresource.local`, password `password` on the demo database). The Admin passes every role check, so one login covers the whole demo, as in the video. To show a separate role at the end, use `logistics@disasterresource.local` or `operations@disasterresource.local` (the ERT teams file through Operations and Warning), same password.

**Reset and record (demo database, not your real data):**

```bash
bash backend/docs/demo-video/reset.sh      # fresh demo.sqlite; the Admin shows as "Head of Office"
# servers: solver-service on :8001, the "laravel-demo" launch entry on :8000
node backend/docs/demo-video/record.mjs    # writes demo-video/raw/demo.webm (raw/ is git-ignored)
```

Run `reset.sh` before **every** live run too: the steps below depend on a clean start (hygiene kits and radios at zero, no approved plan yet).

## 1. What they told us → what we show

| What CDRRMO said (interview) | Sagot ng sistema | Demo step |
|---|---|---|
| Logistics tracks with Excel, a logbook and paper backup; no real-time stock; shortages are found only when someone checks. | One live stock count, the sum of every entry. | Step 1 |
| The most error-prone part is **human counting**. Automatic deduction when a request is released is the feature they want. | Approval reserves stock, release deducts it. No manual recount. | Step 2 |
| Must keep working when **internet or power is out**. | Offline entries sync later; paper backup is still advised. | Step 1 |
| Requests come on the **ICS 213-RR form**; returns go in a **logbook**. | The form is digital (requested **and** released qty). The logbook is the borrowed-equipment page. | Step 2 |
| Stock is tracked **per quarter**, and one quarter's levels adjust the next quarter's request. | Quarterly replenishment from their own history, within the budget. | Step 3 |
| The **Head of Office must approve** before any plan takes effect. | Only the Admin approves requests and the plan. | Steps 2 and 3 |
| GSU does the bidding and picks the supplier, not Logistics. | We only **recommend**; we don't buy. | Step 3 and close |
| Incident logging, the map and ICS roles are out of scope. | Not in the system. | Close |

## 2. Demo script (same order and sample values as the video)

### Opening (30 sec)

On screen: the title card ("Supply readiness for CDRRMO San Pablo City"), then sign in as the Head of Office.

**EN:** "You told us the hardest part is counting by hand, that you can't see stock in real time, and that the work must continue when the internet or power is down. DisasterResource+ keeps one live stock record that works offline, handles your RR requests, and plans each quarter's order from your own history and budget. The Head of Office approves everything. Everything you'll see uses sample data."

**TL:** "Sabi po ninyo, ang pinakamahirap ay ang manu-manong pagbibilang, hindi nakikita ang stock nang real time, at dapat tuloy ang trabaho kahit walang internet o kuryente. Ang DisasterResource+ ay may iisang live na stock record na gumagana offline, tumatanggap ng RR requests, at gumagawa ng plano ng order kada quarter mula sa sarili ninyong history at budget. Ang Head of Office po ang nag-a-approve ng lahat. Sample data lang po ang makikita ninyo."

### Step 1: Stock, offline and the records seal (2 min)

1. **Stock** → "Record a stock entry": **Hygiene kits**, depot **CDRRMO Main Storage**, **Receipt (items in)**, quantity **100** → Save. The row shows **100 kits** at once.
2. Same form: **Handheld radios**, quantity **5** → Save (**5 units**).
3. Go offline (airplane mode, or DevTools → Network → Offline). **Hygiene kits**, **Release (items out)**, **5** → Save. The page shows "**Waiting to sync on this device**".
4. Go online. The entry uploads by itself and the row reads **95 kits**.

| Say (EN) | Sabihin (TL) |
|---|---|
| "The Stock page shows what the office has on hand, item by item." | "Ipinapakita ng Stock page kung ano ang nasa opisina, bawat item." |
| "Logistics records items received: 100 hygiene kits into Main Storage, and the total updates at once. And 5 handheld radios." | "Nire-record ng Logistics ang mga natanggap: 100 hygiene kits sa Main Storage, at kusang nag-a-update ang total. At 5 handheld radios." |
| "No internet during a typhoon or brownout? Logistics keeps working." | "Walang internet kapag may bagyo o brownout? Tuloy ang trabaho ng Logistics." |
| "The entry is saved on this device and waits for the connection." | "Naka-save ang entry sa device na ito at naghihintay ng connection." |
| "When the connection returns, it uploads by itself. Nothing is lost." | "Pagbalik ng connection, kusa itong nag-a-upload. Walang nawawala." |
| "Records are never overwritten. A seal on the records shows if anything was changed or deleted." | "Hindi kailanman ino-overwrite ang records. May seal ang records na nagpapakita kung may binago o binura." |

*Optional live extras (not in the video):* close and reopen the tab while offline and show the entry is still waiting; mark an entry **Priority**; click **Check records** and show "Records intact".

### Step 2: ICS 213-RR request and borrowed equipment (3 min)

1. **ICS 213-RR**. Point at **Load a kit** (the office's "Medical backpack #1").
2. Fill the form: requester **Juan Dela Cruz**, position **ERT Team Leader**, division/team **Operations and Warning / ERT**, purpose **Team kit**, item 1 **Hygiene kits × 20**, **+ Add item**, item 2 **Handheld radios × 2** → **File request**.
3. On the new request, pick the depot for each line (**CDRRMO Main Storage**, 95 and 5 available) → **Approve**. The status becomes *approved* and the stock is reserved.
4. Release **20** and **2**, borrower **Pedro Santos** → **Release**. The status becomes *closed*. Open the log: "approved by Head of Office".
5. **Borrowed equipment** → click **Returned** on the radio.

| Say (EN) | Sabihin (TL) |
|---|---|
| "Requests use the same ICS 213-RR form the office already uses." | "Ang requests ay gumagamit ng parehong ICS 213-RR form na gamit na ng opisina." |
| "Saved kits, like the office's Medical backpack #1, fill the form in one click." | "Ang mga naka-save na kit, tulad ng Medical backpack #1, ay pupuno sa form sa isang click." |
| "Here, the response team asks for 20 hygiene kits and 2 radios." | "Dito, humihiling ang response team ng 20 hygiene kits at 2 radios." |
| "Only the Head of Office approves. Approval reserves the stock, so it can't be promised twice." | "Ang Head of Office lang ang nag-a-approve. Nire-reserve ng approval ang stock, kaya hindi ito mapapangako nang dalawang beses." |
| "Logistics releases the items. Stock goes down by itself; radios need the borrower's name." | "Nire-release ng Logistics ang items. Kusang bumababa ang stock; kailangan ng pangalan ng borrower para sa radios." |
| "Every step is logged with who did it and when." | "Naka-log ang bawat hakbang kung sino ang gumawa at kailan." |
| "Borrowed equipment is tracked until it comes back. Borrower names stay private." | "Sinusubaybayan ang hiniram na kagamitan hanggang maibalik. Pribado ang pangalan ng borrower." |

**EN (tie to their logbook):** "This is your logbook, one row per unit, with the status and who has it."

**TL:** "Ito ang logbook ninyo, isang row kada unit, may status at kung sino ang may hawak."

### Step 3: Quarterly purchase plan (2.5 min)

1. **Replenishment**. Enter the quarterly budget **₱300,000** → **Run solver** (wait for "Spends ₱…").
2. Point at the table: each item has an order, a target stock and a reason. Point at a row that says **partly covered**.
3. Show the **budget curve** and "**The next ₱10,000 goes to**".
4. **Approve** (the status reads "recommendation · approved"). Point at **Download PDF**.

| Say (EN) | Sabihin (TL) |
|---|---|
| "Each quarter, the planner suggests what to buy, using the office's own past use." | "Kada quarter, nagmumungkahi ang planner ng bibilhin, gamit ang sariling nakaraang gamit ng opisina." |
| "The Head of Office enters the quarter's budget, ₱300,000, and runs it." | "Ang Head of Office ang naglalagay ng budget ng quarter, ₱300,000, at pinapatakbo ito." |
| "Every item gets an order, a target, and a reason in plain words." | "May order, target, at dahilan sa simpleng salita ang bawat item." |
| "When the money runs short, the reason says so." | "Kapag kulang ang pera, sinasabi ito ng dahilan." |
| "The budget curve shows the same plan at other budgets. Where it flattens, more money stops helping." | "Ipinapakita ng budget curve ang parehong plano sa ibang budget. Kapag pumatag ito, wala nang naitutulong ang dagdag na pera." |
| "It also shows where the next ₱10,000 would go." | "Ipinapakita rin nito kung saan mapupunta ang susunod na ₱10,000." |
| "Nothing is bought until the Head of Office approves the plan." | "Walang bibilhin hangga't hindi inaaprubahan ng Head of Office ang plano." |
| "The approved plan prints as a report for the purchase request." | "Ang naaprubahang plano ay nagpi-print bilang report para sa purchase request." |

**EN:** "It only recommends. Purchases still go through GSU bidding."

**TL:** "Rekomendasyon lang po ito. Dadaan pa rin sa bidding ng GSU ang pagbili."

*Optional live extras (not in the video):* show the data-check flags on this page and resolve one before running; export the quarterly report as CSV.

### Close (30 sec)

On screen: the end card ("Know what to buy, and why. Next step: test the planner on the office's own records, July 2023 – June 2026.").

**EN:** "Out of scope on purpose: incident logging, the map, ICS roles, other offices' stock, and buying. Next step is to test the planner on your own records from July 2023 to June 2026. We tested the solver against our worked example, ran a 30-entry offline test on 4 devices with none lost or duplicated, and checked each role's access. What we need from you now is your data and your user list."

**TL:** "Sinadya po naming hindi isama ang incident logging, mapa, ICS roles, stock ng ibang opisina, at pagbili. Susunod na hakbang: subukan ang planner sa sarili ninyong records mula Hulyo 2023 hanggang Hunyo 2026. Na-test namin ang solver sa worked example, may 30-entry offline test sa 4 na device na walang nawala o nag-duplicate, at na-check ang access ng bawat role. Ang kailangan na lang po namin ay ang data ninyo at listahan ng users."

## 3. Likely questions (client and panel)

| # | Question (EN / TL) | Answer (EN) | Sagot (TL) |
|---|---|---|---|
| 1 | **Why not keep Excel?** / Bakit hindi na lang Excel? | Excel works, but it can't deduct stock when a request is released, can't plan under a budget, can't explain, and anyone can edit a cell without a trace. | Gumagana po ang Excel, pero hindi ito kusang nagbabawas kapag may release, hindi nagpaplano sa budget, hindi nagpapaliwanag, at puwedeng baguhin ng kahit sino nang walang bakas. |
| 2 | **What if there's no power or internet?** / Paano kung walang kuryente o internet? | The Stock, ICS and Borrowed pages open offline on a device that already loaded them once online, and sync later. It cannot work with the device itself off, so keep the paper backup you already use. | Bukas offline ang Stock, ICS, at Borrowed pages sa device na nabuksan na online dati, at magsi-sync mamaya. Hindi ito gagana kung patay ang device, kaya panatilihin ang paper backup. |
| 3 | **Does the Head of Office have to approve?** / Kailangan bang aprubahan ng Hepe? | Yes. Only the Admin runs the solver and approves or rejects a plan or request. | Opo. Ang Admin lang ang nagpapatakbo ng solver at nag-a-approve o nagre-reject. |
| 4 | **Can the Admin approve their own request?** / Puwede bang i-approve ng Admin ang sarili niyang request? | Yes, by design, and it's logged. | Opo, sinadya ito, at naka-log. |
| 5 | **Will it buy or choose the supplier?** / Ito ba ang bibili o pipili ng supplier? | No. It recommends quantities. GSU bidding stays as is. | Hindi po. Nagrerekomenda lang ito ng dami. Ang bidding ng GSU ay ganoon pa rin. |
| 6 | **Can it see CSWDO, CHO or GSO stock?** / Nakikita ba nito ang stock ng CSWDO, CHO, GSO? | No. It covers only what CDRRMO holds. Cross-office visibility is future work. | Hindi po. Sakop lang ang hawak ng CDRRMO. Future work ang cross-office. |
| 7 | **How does the solver decide?** / Paano nagpapasya ang solver? | Stage 1: each item's target covers k of the last M quarters (multiplier model). Stage 2: past quarters as scenarios, within the budget. PuLP + CBC. Each item gets one reason read from the solution. | Stage 1: sakop ng target ang k sa huling M quarters. Stage 2: mga nakaraang quarter bilang scenario, sa loob ng budget. PuLP + CBC. May isang dahilan ang bawat item. |
| 8 | **How do you know it's better?** / Paano n'yo nasabing mas mabuti? | A rolling backtest compares the office's own orders, flat 1.5, the multiplier and the scenario model, using only earlier quarters. | May rolling backtest na kinukumpara ang aktwal na order ng opisina, flat 1.5, multiplier, at scenario model, gamit lang ang naunang quarters. |
| 9 | **What if history is short?** / Paano kung kaunti ang history? | Under 4 quarters: flat 1.5 rule, marked "limited history". No recorded use: Logistics sets it manually. 8+ quarters recommended. | Wala pang 4 na quarter: flat 1.5 rule, may markang "limited history". Walang record ng gamit: si Logistics ang magtatakda. Inirerekomenda ang 8+ quarters. |
| 10 | **What about expiry dates?** / Paano ang expiry? | Not modeled. Your labels and 1–3 year buffer stay as is. | Hindi po sakop. Mananatili ang label at 1–3 taong buffer ninyo. |
| 11 | **How is tampering detected?** / Paano nade-detect ang pagbabago? | Each stock entry has a SHA-256 hash of the one before it. "Check records" and a nightly check recompute the chain, and a failure emails the Admins. The latest fingerprint prints on the quarterly report. | Bawat entry ay may SHA-256 hash ng nauna. Ni-re-recompute ito ng "Check records" at ng nightly check, at may email sa Admin kapag bumagsak. Naka-print ang fingerprint sa quarterly report. |
| 12 | **Two people edit the same item offline?** / Paano kung dalawa ang nag-edit nang offline? | Both entries are kept and flagged for Logistics to resolve. Nothing is overwritten. | Parehong entry ay itinatago at nilalagyan ng flag para ma-resolve ni Logistics. Walang na-o-overwrite. |
| 13 | **Who sees borrower names?** / Sino ang nakakakita ng pangalan ng borrower? | Only the Admin and Logistics. | Ang Admin at Logistics lang. |
| 14 | **Why no incident log or map?** / Bakit walang incident log o mapa? | You and we agreed they are out of scope. This is supply readiness. | Napagkasunduan po na labas sa scope. Supply readiness ito. |
| 15 | **How did you test it?** / Paano n'yo ito na-test? | Solver worked example, performance (200 items × 12 scenarios in 0.56 s), integration tests, an access test for every role, a scripted 30-entry offline run on 4 devices, then user tasks and an ISO/IEC 25010 survey. | Worked example ng solver, performance (200 items × 12 scenarios sa 0.56 s), integration tests, access test sa bawat role, scripted 30-entry offline run sa 4 na device, saka user tasks at ISO/IEC 25010 survey. |
| 16 | **Limitations?** / Limitasyon? | It needs your encoded history. It can't see stock changes made outside the system. Depot space is modeled only if every depot and item has a size. The app needs HTTPS to work offline. | Kailangan nito ang na-encode na history. Hindi nito nakikita ang pagbabago sa stock sa labas ng sistema. Ang depot space ay mamomodelo lang kung may sukat ang bawat depot at item. Kailangan ng HTTPS para gumana offline. |

## 4. Ask the client

### A. Data to import

| # | Ask for | Goes to | EN | TL |
|---|---|---|---|---|
| 1 | Past requests and releases, **Q3 2023 to Q2 2026** (at least 8 quarters; up to 12) from the RR forms, logbook and Excel | History CSV (`docs/templates/history-encoding-template.csv`: `date, item, standard_unit, quantity_requested, quantity_released, purpose, event_tag, requesting_division`) | "Can we copy your RR forms, logbook and Excel sheets for the last 2–3 years? We'll encode them. A second person will check each quarter." | "Pwede po bang makuha ang kopya ng RR forms, logbook, at Excel ng nakaraang 2–3 taon? Kami po ang mag-e-encode, at may pangalawang tao na magche-check kada quarter." |
| 2 | **Where released quantity is missing** on a form | History | "When the released column is blank, is it in the remarks or the stock sheet?" | "Kapag walang nakasulat na na-release, nasa remarks o stock sheet po ba?" |
| 3 | Item list with unit and **unit price** | Item catalog (the solver skips unpriced items) | "What do you stock, in what unit, and what's the latest price per unit?" | "Anu-ano po ang stock, anong unit, at magkano ang pinakabagong presyo?" |
| 4 | **Item level**: individual supplies or kits? | Catalog | "Should we track gloves or a medical kit, or both?" | "Ita-track po ba ang guwantes o ang medical kit, o pareho?" |
| 5 | Consumable vs **borrowable** (tools, bolt cutter, etc.) | `is_consumable` | "Which items go in the logbook and come back?" | "Alin po ang nasa logbook at ibinabalik?" |
| 6 | **VED priority** of each item, and who confirms it | VED policy (Admin confirms, with position and date) | "Which items can never run out? Who confirms this?" | "Alin po ang hindi puwedeng maubusan? Sino ang magkukumpirma?" |
| 7 | Current stock per item and depot, and the **weekly physical count** (who and what form) | Opening stock; data check 3 | "Who counts weekly and how is it written down?" | "Sino po ang nagbibilang kada linggo at paano isinusulat?" |
| 8 | Depots and **free space (m³)**, size per item | Depots, optional space limit | "Do you know how much room each storage area has?" | "Alam po ba ang laki ng bakanteng espasyo sa bawat imbakan?" |
| 9 | Quarterly **budget** and who decides | Entered by the Admin per run | "How much is a typical quarter, and where does the figure come from?" | "Magkano po ang karaniwang budget kada quarter at saan galing ang halaga?" |
| 10 | **Items lent out now** | Borrow logbook | "What is out right now, with whom, since when?" | "Ano po ang nakahiram ngayon, kanino, at mula kailan?" |

### B. Users per role

Ask for name, role and email. **Key question: how many Logistics staff will use it?**

| Role | Does | EN | TL |
|---|---|---|---|
| **Admin (Head of Office)** | Budget, run solver, approve, users | "Who is the Head and the assistant, and who covers when absent?" | "Sino po ang Hepe at Assistant, at sino ang pumapalit kapag wala?" |
| **Logistics** | Stock, history, releases, borrowed, flags | "**How many Logistics staff will use it?** Who encodes and who releases? Office or field?" | "**Ilan po ang Logistics na gagamit?** Sino ang nag-e-encode at nagre-release? Sa opisina o field?" |
| **Operations and Warning** (ERT and the other response teams file through it) | File requests | "How many people in Operations and in each team file requests?" | "Ilan po sa Operations at sa bawat team ang nagpa-file ng request?" |
| **Administrative** | Read-only, no extra actions | "Who in Administrative needs to see the system?" | "Sino po sa Administrative ang kailangang makakita ng sistema?" |
| **Research and Planning** | Read and export the report | "Who needs the quarterly report? Is there a contact for the follow-up interview?" | "Sino po ang nangangailangan ng report? May contact po ba para sa follow-up interview?" |
| **Admin and Training** | Catalog, depots, training | "Who keeps the item list and trains staff?" | "Sino po ang nagma-maintain ng item list at nagte-train?" |
| **Viewer** | Read-only | "Anyone who only needs to look?" | "May gustong makakita lang po ba?" |

### C. Does our form match yours?

Since the 2026-10-03 update, our request carries the fields on the photo of your RR form: **Control No., date and Time, name and position, division/team, Subject (Medical / Cleaning / Office / Others / General, taken from each item's category), requested and released quantities per line, and Logistics' Reply/Remarks**. Show it next to the paper form and ask:

| EN | TL |
|---|---|
| "Is anything on your form missing here, or something here you don't use?" | "May kulang po ba rito na nasa form ninyo, o may sobra na hindi ninyo ginagamit?" |
| "Does the Control No. follow a pattern we should enforce (e.g. 0040)?" | "May sinusunod po bang pattern ang Control No. (hal. 0040)?" |
| "Do you need a printable copy with signatures?" | "Kailangan po ba ng printable na kopya na may pirma?" |

### D. Setup

| EN | TL |
|---|---|
| "Which PC can act as the server, stay on, and have a UPS? Who maintains it?" | "Aling PC po ang magiging server na laging bukas at may UPS? Sino ang nagma-maintain?" |
| "Can it be served over HTTPS? (Needed for offline mode.)" | "Puwede po bang HTTPS? (Kailangan para sa offline mode.)" |
| "Staff devices: Windows laptops, Android, iPhone?" | "Anong device po ang gamit: Windows laptop, Android, iPhone?" |
| "Which email sends alerts to the Admins?" | "Anong email ang magpapadala ng alerts sa Admin?" |
| "When can we train staff and run the user tests (15–20 people)?" | "Kailan po puwede ang training at user test (15–20 tao)?" |

## 5. Before you go on

- [ ] `bash backend/docs/demo-video/reset.sh` (fresh demo database, Admin shown as "Head of Office")
- [ ] Servers up: `solver-service` on :8001, and the **laravel-demo** launch entry on :8000 (it runs the queue inline, so no queue worker or Reverb is needed for this demo)
- [ ] Sign in as `admin@disasterresource.local` and open **Stock** once online
- [ ] Play the recording once, `node backend/docs/demo-video/record.mjs`, to confirm the steps still work, and keep the mp4 as a fallback
- [ ] Backup: screenshots in `backend/docs/img/` and `docs/role-access/`
- [ ] Bring a printed copy of section 4 and the history CSV template

**If something breaks:** solver silent → check `solver-service` on :8001, then play the video for that part. Offline step fails → play the video and say the 30-entry scripted run passed in `EvaluationTest`. Numbers look wrong (not 100 / 95 kits) → you skipped `reset.sh`.
