# Short Demo Script: Replenishment Screen + Data Request (EN / Tagalog)

About 5 minutes. Use with the Replenishment page open (sample data). The longer versions are [script-tagalog.md](script-tagalog.md), [client-explainer.md](client-explainer.md) and [replenishment-screen-script.md](replenishment-screen-script.md).

---

## English

### 1. Opening (20 sec)

"Good morning, and thank you for your time. This is DisasterResource+. In one sentence: it looks at what your office actually used in past quarters, and suggests what to buy next quarter within your budget, with a reason for every item. What you see today is sample data, not your real stock."

### 2. How it works (3 min, point at the screen top to bottom)

1. **Year, quarter, budget.** "The Head of Office picks the quarter and types the budget, here ₱200,000, then clicks Run Solver."
2. **The recommendation table.** "For each item it shows how many to order, the target stock, how far below target we are, and why. 'Fully covered' means we reach the target. 'The budget ran out' means the money was used on more critical items first. Vital items like water and medical kits come before desirable ones."
3. **Budget curve.** "This answers 'what if we had more or less money?' The line shows the expected shortage from 50% to 150% of the budget. Where the line flattens, extra money no longer helps much."
4. **The next ₱10,000.** "If you get ₱10,000 more, this is exactly where it would go."
5. **Approve or reject.** "Only the Head of Office approves. A rejection needs a comment. The approved plan becomes the purchase plan for that quarter."
6. **Backtest.** "This is our proof. We replay past quarters using only the data available at that time, and compare our method with the usual 'times 1.5' rule. Lower numbers mean less shortage."
7. **VED policy.** "Vital, Essential, Desirable. You decide how strict each class is, and the confirming officer and date are recorded."
8. **Encode history and report.** "Past requests are uploaded here as a CSV file. The report prints the quarterly plan as PDF or CSV in one click."

"In short: you give it your past records and your budget, and it gives you a quarterly order you can explain."

### 3. Our request: your data (1 min)

"To make this work with your real supplies, we would like to politely ask for your records, if it's okay with you.

If possible, in **Excel or CSV**. But if some are only in the logbook or on RR forms, a photo or photocopy is fine too, and we will encode them ourselves.

What we need:
- Past requests and releases: the date, item, unit, quantity requested, quantity released, purpose, the event if any, and the requesting division.
- The list of items with their unit and latest price.
- Current stock per storage area, if available.

The data will be used only for our study and for setting up the system. We can remove names if you prefer. When would be a convenient time for you to send it?"

### 4. Our question: number of users (30 sec)

"One more question, for our research paper. Approximately how many Logistics staff would use the system to encode supplies, such as receiving, releasing, and counting? An estimate is enough. We need it to plan the user evaluation and the training. And if you can, how many from Operations would file requests?"

### 5. Closing (10 sec)

"Thank you very much. Once we receive your data, we will show you the plan using your own records. We're happy to take any questions."

---

## Tagalog (magalang na Taglish)

### 1. Pagbati (20 seg)

"Magandang umaga po, at salamat po sa oras ninyo. Ito po ang DisasterResource+. Sa madaling salita po: tinitingnan nito kung ano talaga ang nagamit ng opisina ninyo sa mga nakaraang quarter, at nagmumungkahi kung ano ang bibilhin sa susunod na quarter, pasok sa budget, at may dahilan po bawat item. Sample data lang po ang nakikita ninyo ngayon, hindi po ang totoong stock ninyo."

### 2. Paano ito gumagana (3 min, ituro ang screen mula taas pababa)

1. **Taon, quarter, budget.** "Pipili po ang Hepe ng quarter at ilalagay ang budget, dito po ₱200,000, tapos pipindutin ang Run Solver."
2. **Ang talaan ng rekomendasyon.** "Bawat item po, makikita kung ilan ang o-orderin, ang target na stock, gaano tayo kababa sa target, at kung bakit. Ang 'fully covered' po ay naabot ang target. Ang 'the budget ran out' po ay naubos ang pera sa mas kritikal na items. Nauuna po ang vital tulad ng tubig at medical kits bago ang desirable."
3. **Budget curve.** "Sinasagot po nito ang tanong na 'paano kung mas malaki o mas maliit ang budget?' Ipinapakita ng linya ang inaasahang kakulangan mula 50% hanggang 150% ng budget. Kapag pumatag na po ang linya, hindi na gaanong nakakatulong ang dagdag na pera."
4. **Ang susunod na ₱10,000.** "Kung madagdagan po ng ₱10,000, dito po eksaktong mapupunta."
5. **Approve o reject.** "Ang Hepe lang po ang puwedeng mag-approve. Kailangan po ng komento kapag ire-reject. Ang na-approve na plano po ang magiging purchase plan ng quarter."
6. **Backtest.** "Ito po ang patunay namin. Inuulit po namin ang mga nakaraang quarter gamit lang ang data na meron noon, at ikinukumpara sa karaniwang 'times 1.5' na paraan. Mas mababa pong numero, mas kaunting kakulangan."
7. **VED policy.** "Vital, Essential, Desirable po. Kayo po ang magpapasya kung gaano kahigpit ang bawat klase, at nakatala kung sino ang nag-confirm at kailan."
8. **Encode history at report.** "Dito po ina-upload ang mga nakaraang request bilang CSV file. Ang report po ay naipi-print bilang PDF o CSV sa isang click."

"Sa madaling salita po: ibibigay ninyo ang nakaraang records at ang budget, at magbibigay ito ng quarterly order na kaya ninyong ipaliwanag."

### 3. Ang aming hiling: ang data ninyo (1 min)

"Para po gumana ito sa totoong supplies ninyo, magalang po sana naming hihilingin ang inyong records, kung okay lang po sa inyo.

Kung maaari po, sa **Excel o CSV**. Pero kung ang iba ay nasa logbook o RR forms lang, okay lang po ang picture o photocopy, at kami na po ang mag-e-encode.

Ang kailangan po namin:
- Mga nakaraang request at release: petsa, item, unit, dami ng hiniling, dami ng na-release, layunin, ang event kung meron, at ang division na humiling.
- Listahan ng items, kasama ang unit at pinakahuling presyo.
- Kasalukuyang bilang ng stock kada imbakan, kung meron po.

Gagamitin lang po ang data para sa aming pag-aaral at sa pag-setup ng sistema. Puwede po naming tanggalin ang mga pangalan kung gusto ninyo. Kailan po kaya kayo maluwag para maipadala ito?"

### 4. Ang aming tanong: ilan ang gagamit (30 seg)

"Isa pa pong tanong, para sa aming research paper. Humigit-kumulang po, ilan ang Logistics staff na gagamit ng sistema para mag-encode ng supplies, tulad ng pagtanggap, pag-release, at pagbilang? Tantiya lang po ay sapat na. Kailangan po namin ito para sa user evaluation at sa training. At kung maaari po, ilan po sa Operations ang magpa-file ng request?"

### 5. Pagtatapos (10 seg)

"Maraming salamat po. Pagkatanggap po namin ng data ninyo, ipapakita po namin ang plano gamit ang sarili ninyong records. Bukas po kami sa anumang tanong."
