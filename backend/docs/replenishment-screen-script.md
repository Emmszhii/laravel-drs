# Replenishment Page Walkthrough: English and Tagalog

For the client demo, using the screen as shown (2027 Q1, ₱200,000, per-item multiplier). About 4–5 minutes. Say each part while pointing at the matching block. The numbers below come from your current screen, so if you re-run the solver, check them first.

## Before you start

- **Change "Confirmed by" from `Test`** to a real position (e.g. Head of Office) and re-confirm the policy, or the client will see "Last confirmed … by Test".
- There are two identical "2027 Q1 · ₱200,000 · completed" rows under RUNS. Delete the duplicate or know why it is there (a re-run supersedes the older one only once approved).
- Expect the question "why does the backtest show 0.00 in 2026 Q3/Q4 and huge leftovers?" Answer is in the Q&A at the end.

---

## 1. The idea in one breath

**English**
> "This is the Replenishment page. It answers one question: *with this quarter's budget, how much of each item should we order?* It learns from your own past requests, stays inside the budget, and gives a plain-language reason for every line, so you can explain it in a meeting."

**Tagalog**
> "Ito po ang Replenishment page. Isa lang ang sinasagot nito: *sa budget ngayong quarter, ilan ang dapat i-order sa bawat item?* Ang pinagbabatayan po ay ang sarili ninyong history ng request. Hindi lalampas sa budget, at may dahilan sa simpleng salita ang bawat linya, kaya madali itong maipaliwanag sa meeting."

---

## 2. Top bar: Year, Quarter, Budget, Model

*(Point to Year 2027, Quarter Q1, Budget ₱200,000, Model.)*

**English**
> "The Head of Office enters the year, the quarter and the budget, here ₱200,000. The model is the method: *per-item multiplier* is the default. Then one click on **Run Solver**."

**Tagalog**
> "Ang Hepe po ang maglalagay ng year, quarter at budget, dito ay ₱200,000. Ang model ay ang paraan ng pagkuwenta; ang *per-item multiplier* ang default. Tapos isang click lang sa **Run Solver**."

*(Click Run Solver. Note "solved in 9 ms".)*

**English**
> "It solved in milliseconds and spent ₱200,000 of ₱200,000. It never goes over."

**Tagalog**
> "Wala pang isang segundo, tapos na. Nagamit ang ₱200,000 sa ₱200,000. Hindi po ito lumalampas sa budget."

---

## 3. The recommendation table

*(Point to the heading "2027 Q1 Recommendation · Completed".)*

**English**
> "Each row is an item. **Order** is how many to buy. **Target stock** is what your history says you should be covered for. **% below target** is how short we still are. **Reason** tells you why, in plain words."

**Tagalog**
> "Bawat hilera ay isang item. Ang **Order** ay ilan ang bibilhin. Ang **Target stock** ay ang dapat na saklaw base sa history ninyo. Ang **% below target** ay kung gaano pa tayo kulang. At ang **Reason** ang nagsasabi kung bakit, sa simpleng salita."

*(Point to the rows.)*

**English**
> "Blankets, drinking water and shelter repair kits say *fully covered*: target met. Hygiene kits are almost there, 1% below. Medical kits are 62% below the target, and **family food packs got zero**, because the budget ran out. The system chose to cover the other items first, and it tells us so honestly. If the Head wants food packs, the next section shows what that costs."

**Tagalog**
> "Ang blankets, drinking water at shelter repair kits ay *fully covered*, abot ang target. Ang hygiene kits ay halos abot, 1% na lang ang kulang. Ang medical kits ay 62% pa ang kulang, at ang **family food packs ay zero**, dahil naubos ang budget. Inuna ng sistema ang ibang item, at sinasabi nito nang tapat. Kung gusto ng Hepe na magkaroon ng food packs, ipapakita sa susunod na bahagi kung magkano ang kailangan."

*(Point to "Set manually by Logistics: Handheld radios".)*

**English**
> "Items with no unit cost, like handheld radios, are left out of the calculation and flagged here, so Logistics sets those by hand. Nothing is silently skipped."

**Tagalog**
> "Ang mga item na walang unit cost, gaya ng handheld radios, ay hindi kasama sa kuwenta at nakalista rito, kaya si Logistics ang mag-aayos nang mano-mano. Walang item na basta na lang nakakalimutan."

*(Point to "Depot space not modeled".)*

**English**
> "Depot space is only considered once every depot and item has its size entered. For now it's not modeled, and the page says so."

**Tagalog**
> "Isasama lang ang espasyo ng depot kapag nailagay na ang sukat ng bawat depot at item. Sa ngayon, hindi pa, at sinasabi iyon ng page."

---

## 4. Budget curve and "the next ₱10,000"

*(Point to the chart.)*

**English**
> "This chart answers *what if the budget were different?* The left side is a ₱100,000 budget, the right side ₱300,000. The vertical line is the ₱200,000 we entered. Shortage falls from 6.44 quarters of average use at ₱100,000, to 3.88 at ₱200,000, to 1.75 at ₱300,000. Where the line flattens, more money stops helping much."

**Tagalog**
> "Sinasagot ng chart na ito ang tanong na *paano kung iba ang budget?* Sa kaliwa, ₱100,000. Sa kanan, ₱300,000. Ang patayong guhit ay ang ₱200,000 na inilagay natin. Bumababa ang kakulangan mula 6.44 na quarter ng average na gamit sa ₱100,000, hanggang 3.88 sa ₱200,000, at 1.75 sa ₱300,000. Kapag pumatag ang linya, hindi na gaanong nakakatulong ang dagdag na pera."

*(Point to "The next ₱10,000 goes to".)*

**English**
> "And if the Head finds another ₱10,000, this shows exactly where it would go: a bit more hygiene kits, a lot more medical kits, and it trims two containers of water. So the Head can justify a budget request with a number, not a feeling."

**Tagalog**
> "At kung may madagdag pang ₱10,000, ipinapakita rito kung saan ito mapupunta: dagdag na hygiene kits, mas maraming medical kits, at bawas ng dalawang container ng tubig. Kaya may numerong maipapakita ang Hepe kapag humihingi ng dagdag na budget, hindi basta pakiramdam."

---

## 5. Approve or Reject

*(Point to Comment, Approve, Reject.)*

**English**
> "Nothing becomes the purchase plan until the Head of Office approves it. Rejecting needs a comment, so there's always a recorded reason. Once approved, this run is the one plan for the quarter. This is a **recommendation**; the buying itself still goes through the GSU's bidding."

**Tagalog**
> "Walang magiging purchase plan hangga't hindi inaaprubahan ng Hepe. Kailangan ng comment kapag nag-reject, kaya laging may nakatalang dahilan. Kapag naaprubahan, ito na ang nag-iisang plano para sa quarter. **Rekomendasyon** lang ito; ang pagbili ay dadaan pa rin sa bidding ng GSU."

---

## 6. Right column: VED policy, History, Report, Runs

**VED policy**

**English**
> "VED means Vital, Essential, Desirable. For each class the Head sets *k of the last M quarters* to cover, and a weight. For Vital items it's 8 of the last 8 quarters, weight 3, so a shortage of a Vital item counts three times more. The Head confirms this policy, and it records who and when."

**Tagalog**
> "Ang VED ay Vital, Essential, Desirable. Sa bawat klase, ang Hepe ang nagtatakda kung ilan sa huling M na quarter ang dapat saklaw, at ang bigat. Sa Vital, 8 sa huling 8 quarter ang saklaw, bigat 3, kaya mas mabigat ang bilang ng kakulangan sa Vital na item. Kinukumpirma ito ng Hepe, at nakatala kung sino at kailan."

**Encode History**

**English**
> "This is how your past requests get in: upload the CSV. If any single row is bad, the whole file is rejected, so bad data never gets mixed in."

**Tagalog**
> "Dito pumapasok ang dating request ninyo: i-upload ang CSV. Kapag may isang maling hilera, tinatanggihan ang buong file, kaya hindi nahahalo ang maling data."

**Report and Runs**

**English**
> "Report downloads a PDF or CSV for any year and quarter range, for the purchase request. It prints the latest records fingerprint so the stock numbers can be checked. Runs is the history of every plan we generated."

**Tagalog**
> "Ang Report ay nagda-download ng PDF o CSV para sa kahit anong year at hanay ng quarter, para sa purchase request. May naka-print ding fingerprint ng stock records para ma-verify ang numero. Ang Runs ay listahan ng bawat planong nabuo."

---

## 7. Backtest on past quarters

*(Scroll down. Point to the heading and the line under it.)*

**English**
> "To check the system isn't guessing, we test it on the past. For each quarter, every method orders using **only earlier quarters**, then we score it on what was actually used. Lower is better. Over six quarters, the per-item multiplier beat the flat 1.5 rule in 4 of 6, and the scenario model also in 4 of 6. In 2025 Q3 for example, flat 1.5 scored 3.86, the multiplier 3.15, and the scenario model 2.00."

**Tagalog**
> "Para patunayang hindi hula lang, sinubukan namin ito sa nakaraan. Sa bawat quarter, gumagamit ang bawat paraan ng **mga naunang quarter lang**, tapos sinusukat sa aktuwal na nagamit. Mas mababa, mas maganda. Sa anim na quarter, tinalo ng per-item multiplier ang flat 1.5 na patakaran sa 4 sa 6, at ganoon din ang scenario model. Halimbawa, sa 2025 Q3, ang flat 1.5 ay 3.86, ang multiplier ay 3.15, at ang scenario model ay 2.00."

*(Point to the safety-stock column.)*

**English**
> "We also compared against the classic safety-stock formula, so it's judged against the textbook method, not just a simple rule."

**Tagalog**
> "Ikinumpara rin namin sa klasikong safety-stock formula, kaya hindi lang simpleng patakaran ang kalaban kundi ang kilalang paraan."

---

## 8. Closing line

**English**
> "In short: you enter the budget, the system proposes an order from your own history, explains every line, shows what more money would buy, and nothing is final until the Head approves."

**Tagalog**
> "Sa madaling sabi: ilalagay ninyo ang budget, magmumungkahi ang sistema ng order mula sa sarili ninyong history, ipapaliwanag ang bawat linya, ipapakita kung ano ang mabibili ng dagdag na pera, at wala pong final hangga't hindi inaaprubahan ng Hepe."

---

## Likely questions

| Question | Answer (English / Tagalog) |
|---|---|
| Why 0 family food packs? | The budget ran out after higher-weight items. Raise the budget or change the VED weights. / Naubos ang budget pagkatapos ng mas mabibigat na item. Dagdagan ang budget o baguhin ang VED weights. |
| Why is the backtest 0.00 with big leftovers in 2026 Q3/Q4? | In those quarters the budget exceeded the full need, so nothing ran short and money was left. Say this plainly; don't present 0.00 as a win. / Sa mga quarter na iyon, higit sa kailangan ang budget kaya walang kulang at may natira. Sabihin nang malinaw; huwag ipakitang panalo ang 0.00. |
| Is this sample data? | Yes. Real numbers come after we import your request history. / Oo, sample data. Ang totoong numero ay kapag na-import na ang history ninyo. |
| Can we override a line? | The Head can decide not to approve and re-run with a different budget or policy; items without cost are set by hand. / Puwedeng hindi aprubahan ng Hepe at mag-run ulit na iba ang budget o policy; ang item na walang cost ay mano-mano. |
| Does it buy for us? | No. It recommends; GSU bidding still applies. / Hindi. Nagrerekomenda lang; dadaan pa rin sa bidding ng GSU. |
