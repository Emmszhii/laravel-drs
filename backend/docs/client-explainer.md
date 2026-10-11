# DisasterResource+ in Plain Words — for CDRRMO staff (EN / Tagalog)

For non-technical listeners: the Head of Office, Logistics, Planning, Operations. About 4 minutes, no slides needed. Use it before or after the live demo ([demo-script.md](demo-script.md)).

Rows 1–9 come from the 2026-09-03 interview summary (`important/transcription summary.txt`), paraphrased, so check the wording against the recordings before quoting it to them. Row 10 is our own (the paper's audit goal). Say it back to them in their own words first, then give the benefit.

**Words to avoid:** hash, SHA-256, MILP, solver, PWA, sync, API. **Say instead:** "seal on the records", "the planner", "saved on the device, uploads later".

---

## 1. Opening (30 sec)

**EN:** "Earlier you told us how the supply work goes today: a logbook, Excel, and paper backups. It works, but it takes a lot of counting, and mistakes creep in. DisasterResource+ does one job: it helps CDRRMO always know what supplies it has, who took what, and what to buy next quarter. It doesn't replace your people or your process. It takes the counting and the bookkeeping off their hands."

**TL:** "Kanina po, ikinuwento ninyo kung paano ang supply work ngayon: logbook, Excel, at paper backup. Gumagana po ito, pero maraming bilangan at may nakakalusot na mali. Ang DisasterResource+ ay may isang trabaho: tulungan ang CDRRMO na laging alam kung anong supplies ang meron, sino ang kumuha ng ano, at ano ang bibilhin sa susunod na quarter. Hindi nito pinapalitan ang mga tao o ang proseso ninyo. Inaalis nito sa kanila ang pagbibilang at paglilista."

---

## 2. Your problems → what it does for you

| # | What you told us (paraphrased from the interview summary) | What the system does | Benefit in one line |
|---|---|---|---|
| 1 | **"The most time-consuming, most error-prone part is counting by hand."** | Stock goes up when items are received and down when they are released. The system keeps the running total. Nobody recounts. | Fewer counting mistakes, less time counting. |
| 2 | **"We can't see stock in real time. We only learn about a shortage when someone checks."** | One screen shows what you have, item by item and storage area by storage area, as of now. | You see a shortage before it hurts, not after. |
| 3 | **"We wish stock would be deducted automatically when a request is made."** | When the Head of Office approves a request, the items are set aside. When Logistics hands them over, the stock goes down by itself. | No double promising, no manual deduction. |
| 4 | **"Records are kept separately by section: Logistics, Planning, Operations. There is no single system."** | Requests, releases, borrowed items, and the quarterly plan all use the same stock record. | Everyone looks at the same numbers. |
| 5 | **"Requests go through an RR form; returns go in a logbook."** | The same ICS 213-RR form, filled in on screen. Borrowed equipment has its own list: who has it, since when, and whether it is back. | Same form and logbook they know, but searchable and never lost. |
| 6 | **"The Head of Office must approve before anything happens."** | Only the Head of Office can approve a request or a purchase plan. Each approval is logged with name and time. | Your approval rule is built in, with a trail for audit. |
| 7 | **"Work must continue when internet or power is out."** | Logistics can keep recording on a laptop or phone with no internet. The entries are saved on the device and upload by themselves when the connection returns. Nothing is lost or counted twice. | A brownout doesn't stop the work. Keep your paper backup as before. |
| 8 | **"We track stock each quarter, and use one quarter to adjust the next. We want to avoid too much or too little."** | Each quarter the planner suggests how much of each item to buy, using your own past use and your budget. It gives a reason for every item. | A starting point for the quarterly request, based on your records, and you can explain it. |
| 9 | **"Planning asks Logistics for updated data every year."** | The quarterly report is printed from the system in one click. | No yearly chase for figures. |
| 10 | **Not from the interview: the paper's audit goal.** "If a record is changed later, how would we know?" | Records are never overwritten. If a record is later edited or deleted, a check finds it. | If someone changes a record, you can tell. |

---

## 3. Explain each feature with an everyday picture (2 min)

| Feature | Say (EN) | Sabihin (TL) |
|---|---|---|
| **Stock record** | "Think of your bank passbook. It doesn't just show a balance. Every deposit and withdrawal is a line, and the balance comes from the lines. Our stock record is the same, so you can always see how you got to the number." | "Isipin po ninyo ang passbook sa bangko. Hindi lang balanse ang ipinapakita. Bawat deposito at withdrawal ay may linya, at galing sa mga linya ang balanse. Ganyan din ang stock record natin, kaya laging makikita kung paano nakuha ang numero." |
| **Working without internet** | "Like writing in a notebook when the power is out, then copying it into the logbook when the lights are back. Except the system copies it for you, and never twice." | "Parang pagsusulat sa notebook kapag walang kuryente, tapos ikokopya sa logbook pagbalik ng ilaw. Pero ang sistema ang kokopya para sa inyo, at hindi kailanman dalawang beses." |
| **Reserving stock on approval** | "Like reserving a table in a restaurant. Once the Head of Office approves, those items are set aside, so the same items can't be promised to two teams." | "Parang pag-reserve ng mesa sa restawran. Kapag inaprubahan ng Head of Office, nakatabi na ang items, kaya hindi mapapangako sa dalawang team ang parehong items." |
| **Seal on the records** | "Like the sticker on a sealed envelope. You can't open it without leaving a sign. If anything is edited later, the check shows where." | "Parang sticker sa selyadong sobre. Hindi mabubuksan nang walang bakas. Kapag may binago pagkatapos, ipapakita ng check kung saan." |
| **Quarterly planner** | "Like an experienced staff member who has read every past request. You give the budget, and they say: 'Buy this much of each item, here is why, and if you had ₱10,000 more, it would go here.' You and the Head of Office make the final decision." | "Parang beteranong staff na nabasa ang lahat ng nakaraang request. Ibibigay ninyo ang budget, at sasabihin nila: 'Ganito karami ang bilhin sa bawat item, ito ang dahilan, at kung may dagdag na ₱10,000, dito mapupunta.' Kayo at ang Head of Office pa rin ang magpapasya." |
| **Reasons per item** | "Every item has a sentence in plain words, such as 'budget ran out' or 'current stock is enough'. You can read it out in a meeting." | "May pangungusap sa simpleng salita ang bawat item, tulad ng 'naubos ang budget' o 'sapat na ang kasalukuyang stock'. Puwede ninyong basahin sa meeting." |

---

## 4. What it does **not** do (say this before they ask)

| Not included | Say (EN) | Sabihin (TL) |
|---|---|---|
| **Buying** | "It only recommends. Buying still goes through GSU bidding." | "Rekomendasyon lang po. Dadaan pa rin sa bidding ng GSU ang pagbili." |
| **Other offices' stock** (CSWDO, CHO, GSO) | "It covers only what CDRRMO holds. Other offices' stock is a possible future step." | "Ang hawak lang ng CDRRMO ang sakop. Posibleng susunod na hakbang ang stock ng ibang opisina." |
| **Incident logging and the map** | "That's a different job. We kept this system to supplies, as agreed." | "Ibang trabaho po iyon. Nanatili kaming sa supplies lang, ayon sa napagkasunduan." |
| **Expiry dates** | "Your expiry labels and the 1–3 year buffer stay as they are." | "Mananatili ang expiry labels at ang 1–3 taong buffer ninyo." |
| **A power cut on the device itself** | "If the laptop or phone is off, it can't record. That's why we keep the paper backup you already have." | "Kapag patay ang laptop o phone, hindi ito makakapag-record. Kaya mananatili ang paper backup ninyo." |
| **Choosing who gets supplies in a big emergency** | "That stays a decision for your people and the ICS. The system shows what you have and what's reserved." | "Mananatili po itong desisyon ng mga tao ninyo at ng ICS. Ipinapakita lang ng sistema kung ano ang meron at ano ang naka-reserve." |

---

## 5. Closing (30 sec)

**EN:** "In short: less counting, a clear picture of stock any time, requests that follow your own approval rule, work that continues when the internet doesn't, and a quarterly plan you can explain. To try it on your own supplies, we need two things from you: your past records, and a list of who will use it."

**TL:** "Sa madaling sabi: mas kaunting bilangan, malinaw na larawan ng stock anumang oras, requests na sumusunod sa sarili ninyong approval rule, tuloy ang trabaho kahit walang internet, at quarterly plan na kaya ninyong ipaliwanag. Para masubukan sa sarili ninyong supplies, dalawa po ang kailangan namin sa inyo: ang mga nakaraang records, at listahan ng gagamit."

---

## 6. Honest claims only

- Say "fewer mistakes" and "less time", not "no mistakes". The 30-entry offline test and the solver tests are real evidence. Time savings on your side are not measured yet. The user survey will measure them.
- Say "a starting point you can explain", not "the right answer". Whether the planner beats the office's own orders is being tested on your records, and the result is not in yet.
- If asked "is it safe?", say records can't be quietly changed and every approval is logged. Don't claim it can't be hacked.
