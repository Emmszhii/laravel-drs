# DisasterResource+ — Client Demo Script, Q&A and Discovery (EN / Tagalog)

Built from: the 2026-09-03 interview (`docs/important/transcription summary.txt`), the photos of CDRRMO's real ICS 213-RR form and borrowed-items logbook, and `system-spec.md`. There is no video in the docs folder, only the two interview recordings (`.m4a`), so the "how it works" below comes from the transcript summary and the spec. **Check the transcript against the audio for anything you quote to the client.**

Audience: CDRRMO San Pablo (Head of Office, Logistics, Planning). ~8 minutes. Three windows: **Admin**, **Logistics**, **Operations and Warning** (the ERT teams file through it) (`logistics@disasterresource.local`, `operations@disasterresource.local`, password `password`; Admin per `UserSeeder`).

## 1. What they told us → what we show

| What CDRRMO said (interview) | Sagot ng sistema | Demo step |
|---|---|---|
| Logistics tracks with Excel, a logbook and paper backup; no real-time stock; shortages are found only when someone checks. | One live stock count, sum of every entry. | Step 3 |
| The most error-prone part is **human counting**. Automatic deduction when a request is released is the feature they want. | Approval reserves stock, release deducts it. No manual recount. | Step 3 |
| Must keep working when **internet or power is out**. | Offline entries sync later; paper backup is still advised. | Step 4 |
| Requests come on the **ICS 213-RR form**; returns go in a **logbook**. | The form is digital (requested **and** released qty). The logbook is the borrowed-equipment page. | Steps 3 and 5 |
| Stock is tracked **per quarter**, and one quarter's levels adjust the next quarter's request. | Quarterly replenishment from their own history, within the budget. | Step 2 |
| The **Head of Office must approve** before any plan takes effect. | Only the Admin runs the solver and approves or rejects. | Steps 2 and 3 |
| GSU does the bidding and picks the supplier, not Logistics. | We only **recommend**; we don't buy. | Close |
| Incident logging, the map and ICS roles are out of scope. | Not in the system. | Close |

## 2. Demo script

### Step 1: Problem and promise (30 sec)

**EN:** "You told us the hardest part is counting by hand, that you can't see stock in real time, and that the work must continue when the internet or power is down. DisasterResource+ keeps one live stock record that works offline, plans each quarter's order from your own history and budget, and shows its reasoning. The Head of Office approves everything."

**TL:** "Sabi po ninyo, ang pinakamahirap ay ang manu-manong pagbibilang, hindi nakikita ang stock nang real time, at dapat tuloy ang trabaho kahit walang internet o kuryente. Ang DisasterResource+ ay may iisang live na stock record na gumagana offline, gumagawa ng plano ng order kada quarter mula sa sarili ninyong history at budget, at ipinapakita ang dahilan. Ang Head of Office po ang nag-a-approve ng lahat."

### Step 2: Quarterly replenishment (2.5 min) — Admin

1. **Replenishment** → show the data-check flags and resolve one.
2. Enter the **budget** → **Run solver**. The other window updates live.
3. Open an item → read its **reason** (e.g. "partly covered: the budget ran out").
4. Show the **budget curve** and "**the next ₱10,000 goes to**".
5. **Approve** → this is the purchase plan. Export the **Quarterly report**.

**EN:** "Every item has a reason. If the budget is short, you see what gets cut and what ₱10,000 more would buy. It only recommends: purchases still go through GSU bidding."

**TL:** "May dahilan po ang bawat item. Kapag kulang ang budget, makikita kung ano ang natatabasan at ano ang mabibili ng dagdag na ₱10,000. Rekomendasyon lang po ito: dadaan pa rin sa bidding ng GSU ang pagbili."

### Step 3: ICS 213-RR request, released stock deducted automatically (2 min)

1. Operations window → **ICS 213-RR** → file a request with the same fields as your paper form (Control No., date and time, name and position, division/team, subject, items and quantities). Operations has no approve or release buttons.
2. Admin → **approve** → stock is **reserved**.
3. Logistics → **release** (less than requested is allowed) → stock deducts automatically. Show requested vs released side by side.

**EN:** "This is your RR form, but approval reserves the stock and release deducts it. No recounting, and both requested and released quantities are kept."

**TL:** "Ito po ang RR form ninyo, pero kapag inaprubahan, naka-reserve ang stock, at kapag nai-release, kusa nang nababawasan. Wala nang recount, at parehong naka-save ang hiniling at na-release."

### Step 4: Offline stock entries and tamper check (2 min) — Logistics

1. **Stock** → airplane mode (or DevTools → Network → Offline).
2. Record 2–3 entries (one **Priority**). Show "**Waiting to sync on this device**". Close and reopen the tab.
3. Go online → syncs by itself, priority first.
4. **Check records** → "Records intact".

**EN:** "If the internet drops, Logistics keeps recording. Entries sync when it's back, with no duplicates. Records can't be silently edited: a changed entry is caught by the check."

**TL:** "Kapag nawala ang internet, tuloy ang pag-record ng Logistics. Magsi-sync ito pagbalik ng connection, walang duplicate. Hindi rin puwedeng palihim na baguhin ang record: mahuhuli ito ng check."

### Step 5: Borrowed equipment, your logbook (1 min) — Logistics

Open **ICS 213-RR → Borrowed equipment** (shows the same columns as your logbook: date, item, qty, division, name, date returned). Issue one item, return it. Show borrower names visible to Logistics and Admin only.

**EN:** "Your logbook, one row per unit, with the status and who has it."

**TL:** "Ito ang logbook ninyo, isang row kada unit, may status at kung sino ang may hawak."

### Close (30 sec)

**EN:** "Out of scope on purpose: incident logging, the map, ICS roles, other offices' stock, and buying. We tested the solver against our worked example, ran a 30-entry offline test on 4 devices with none lost or duplicated, and checked each role's access. What we need from you now is your data and your user list."

**TL:** "Sinadya po naming hindi isama ang incident logging, mapa, ICS roles, stock ng ibang opisina, at pagbili. Na-test namin ang solver sa worked example, may 30-entry offline test sa 4 na device na walang nawala o nag-duplicate, at na-check ang access ng bawat role. Ang kailangan na lang po namin ay ang data ninyo at listahan ng users."

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

- [ ] `php artisan migrate:fresh --seed`, then the `ReplenishmentDemoSeeder`
- [ ] Running: `php artisan serve`, `npm run dev`, `php artisan queue:work`, `php artisan reverb:start`, solver on :8001
- [ ] Open **Stock**, **ICS 213-RR** and **Borrowed** once online (so they open offline)
- [ ] Backup: screenshots in `backend/docs/img/` and `docs/role-access/`
- [ ] Bring a printed copy of section 4 and the history CSV template

**If something breaks:** solver silent → check the queue worker and `solver-service` on :8001, then show a saved run. Live update missing → refresh and check Reverb. Offline step fails → use screenshots and say the 30-entry scripted run passed in `EvaluationTest`.
