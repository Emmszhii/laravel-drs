# Script sa Pagpapaliwanag ng DisasterResource+ sa CDRRMO (Tagalog)

Para sa meeting at demo sa client. Kabuuang oras: mga 15 minuto, hindi kasama ang tanungan. Magalang na Taglish ang gamit, dahil ganito ang karaniwang usapan sa opisina. Ang mga salitang English (stock, request, budget, approve) ay hindi na isinasalin.

Kasama nito ang [demo-script.md](demo-script.md) (mga hakbang sa screen, may Q&A) at [client-explainer.md](client-explainer.md) (bersyong English/Tagalog para sa non-technical).

## Daloy (flow)

| # | Bahagi | Oras | Ano ang nasa screen | Layunin |
|---|---|---|---|---|
| 1 | Pagbati at layunin | 1 min | Title card | Ipakilala ang sarili at ang pakay |
| 2 | Ulitin ang problema nila | 2 min | Wala (kausap lang) | Ipakitang nakinig kayo |
| 3 | Ang solusyon sa isang tingin | 1 min | Wala | Ibigay ang malaking larawan |
| 4 | Demo 1: Stock at offline | 3 min | Stock page | Sagot sa pagbibilang at sa brownout |
| 5 | Demo 2: RR request at logbook | 3 min | ICS 213-RR, Borrowed | Sagot sa form at logbook, at awtomatikong bawas |
| 6 | Demo 3: Quarterly plan | 3 min | Replenishment | Sagot sa pag-aayos ng request kada quarter |
| 7 | Ano ang hindi nito ginagawa | 1 min | Wala | Iwasan ang maling inaasahan |
| 8 | Ang kailangan namin sa inyo | 2 min | Wala | Makuha ang data at listahan ng users |
| 9 | Pagtatapos | 30 sec | End card | Malinaw na susunod na hakbang |

Bago magsimula: patakbuhin ang `reset.sh`, buksan ang demo (mga detalye sa seksyon 5 ng demo-script.md), at ihanda ang naka-print na listahan ng hihingin (seksyon 4 ng demo-script.md).

---

## 1. Pagbati at layunin (1 min)

> "Magandang umaga po sa inyong lahat. Ako po si [pangalan], at kasama ko po si [pangalan]. Salamat po sa oras ninyo.
>
> Ang ipapakita po namin ngayon ay ang DisasterResource+, isang sistemang ginawa namin para sa supply ng CDRRMO. Mga labinlimang minuto lang po ito, tapos bukas po kami sa tanong at puna ninyo.
>
> Sample data lang po ang makikita ninyo ngayon, hindi po ang totoong stock ninyo. Ang gusto po naming malaman ay kung tama ang takbo ng sistema para sa trabaho ninyo."

## 2. Ulitin ang problema nila (2 min)

> "Bago po kami magpakita, gusto naming ulitin ang narinig namin noong nag-interview kami sa inyo, para po masabi ninyo kung tama.
>
> Una, ang Logistics ay gumagamit ng logbook at Excel, at may paper backup kapag walang kuryente. Gumagana po ito.
>
> Pangalawa, ang pinakamahirap at pinakamadalas magkamali ay ang pagbibilang nang manu-mano. Kapag may isang mali, kumakalat po ito.
>
> Pangatlo, hindi po nakikita ang stock nang real time. Kadalasan, nalalaman lang ang kakulangan kapag may nag-check na.
>
> Pang-apat, ang records ay magkakahiwalay ayon sa seksyon: Logistics, Planning, Operations. Walang iisang sistema.
>
> Panglima, ang request ay dumadaan sa RR form, at ang pagbabalik ng hiniram ay sa logbook. At bago po maisakatuparan ang anumang plano, kailangan ang approval ng Hepe.
>
> Pang-anim, dapat tuloy ang trabaho kahit mawalan ng internet o kuryente.
>
> At panghuli, kada quarter po ninyo sinusubaybayan ang stock, at ginagamit ang isang quarter para ayusin ang request sa susunod, para hindi sumobra o kumulang.
>
> Tama po ba iyon? May gusto po ba kayong idagdag?"

*(Hintayin ang sagot. Isulat ang idinagdag nila.)*

## 3. Ang solusyon sa isang tingin (1 min)

> "Ang DisasterResource+ po ay gumagawa ng tatlong bagay.
>
> Una, **iisang talaan ng stock** na laging updated, at gumagana kahit walang internet.
>
> Pangalawa, ang **ICS 213-RR request** na nasa screen na, kasama ang logbook ng hiniram na kagamitan. Kapag inaprubahan ng Hepe, naka-reserve ang items. Kapag na-release, kusa nang nababawasan ang stock.
>
> Pangatlo, ang **quarterly plan**: nagmumungkahi ito kung gaano karami ang bibilhin sa bawat item, base sa sarili ninyong nakaraang gamit at sa budget, at may dahilan ang bawat mungkahi.
>
> Hindi po nito pinapalitan ang mga tao ninyo. Inaalis lang nito ang paulit-ulit na bilangan at lista."

## 4. Demo 1: Stock at offline (3 min)

*(Buksan ang **Stock** page, naka-login bilang Head of Office.)*

> "Ito po ang Stock page. Dito makikita kung ano ang meron ang opisina, bawat item at bawat imbakan.
>
> Ipagpalagay po nating may dumating na 100 hygiene kits. Si Logistics ay magre-record ng receipt: hygiene kits, sa Main Storage, 100. *(I-save.)* Pansinin po ninyo, nag-update agad ang total. Wala pong nagbilang ulit.
>
> Dagdag pa po natin ang 5 handheld radios. *(I-save.)*"

*(I-off ang internet: airplane mode o Network → Offline.)*

> "Ngayon po, ipagpalagay nating may bagyo, at nawalan ng internet. Tuloy pa rin ang trabaho ng Logistics. Mag-release po tayo ng 5 hygiene kits. *(I-save.)*
>
> Makikita po ninyo ang sabi dito, 'Waiting to sync on this device'. Ibig sabihin, naka-save ito sa device na ito at naghihintay lang ng connection."

*(Ibalik ang internet.)*

> "Pagbalik po ng internet, kusa itong nag-upload. Tingnan po ninyo, 95 na ang hygiene kits. Walang nawala, at hindi nadoble.
>
> At ang mga record po ay hindi kailanman ino-overwrite. May 'seal' po ang mga ito. Kapag may nagbago o nagbura ng record sa dakong huli, may check na makakahuli nito."

## 5. Demo 2: RR request at logbook (3 min)

*(Pumunta sa **ICS 213-RR**.)*

> "Ito po ang RR form na ginagamit na ninyo, pero nasa screen na. Nandito ang mga nasa papel: pangalan at posisyon ng humihiling, division o team, purpose, at ang mga item at dami.
>
> May 'Load a kit' din po. Halimbawa, ang Medical backpack #1 ninyo. Isang click lang, napupuno na ang mga item.
>
> Sa halimbawa po natin, ang response team ay humihiling ng 20 hygiene kits at 2 radios." *(I-file ang request.)*
>
> "Ngayon po, ang Hepe lang ang puwedeng mag-approve. Pipili po tayo ng depot, tapos Approve. *(I-click.)* Pansinin po: **naka-reserve** na ang stock. Hindi na ito mapapangako sa ibang team.
>
> Sunod, si Logistics ang magre-release. 20 at 2, at dahil ang radio ay hiniram, kailangan ang pangalan ng borrower. *(I-click ang Release.)* Closed na ang request, at **kusa nang nabawasan ang stock**. Wala nang manu-manong bawas.
>
> Naka-log din po ang bawat hakbang: sino ang nag-approve, sino ang nag-release, at kailan."

*(Buksan ang **Borrowed equipment**.)*

> "At ito po ang logbook ninyo, nasa sistema na. Isang row kada unit, kung sino ang may hawak, at kung naibalik na. Pag-click po ng 'Returned', tapos na. Ang pangalan ng borrower ay makikita lang ng Admin at Logistics."

## 6. Demo 3: Quarterly plan (3 min)

*(Pumunta sa **Replenishment**.)*

> "Ito naman po ang quarterly plan. Ang tanong po rito: ngayong quarter, gaano karami ang bibilhin sa bawat item?
>
> Ang Hepe po ang maglalagay ng budget. Halimbawa, tatlong daang libong piso. *(I-type, tapos Run.)*
>
> Makikita po ninyo, bawat item ay may order, may target na stock, at may **dahilan sa simpleng salita**. Halimbawa dito, 'partly covered: the budget ran out'. Ibig sabihin, kulang ang budget para sa buong target. Puwede po ninyong basahin ito sa meeting at maipaliwanag.
>
> Ang budget curve naman po ay nagpapakita ng parehong plano sa ibang budget. Kapag pumatag ang linya, wala nang gaanong naitutulong ang dagdag na pera. At ipinapakita rin nito kung **saan mapupunta ang susunod na ₱10,000**.
>
> Walang bibilhin hangga't hindi inaaprubahan ng Hepe. *(I-click ang Approve.)* At may report na puwedeng i-print para sa purchase request. *(Ituro ang Download PDF.)*
>
> Mahalaga po: **rekomendasyon lang ito**. Ang pagbili ay dadaan pa rin sa bidding ng GSU."

## 7. Ano ang hindi nito ginagawa (1 min)

> "Para po malinaw ang inaasahan:
>
> Hindi po ito bumibili at hindi pumipili ng supplier. Ang GSU pa rin iyon.
>
> Ang stock lang po ng CDRRMO ang sakop, hindi ng CSWDO, CHO, o GSO. Posibleng susunod na hakbang iyon.
>
> Wala po itong incident logging o mapa. Sinadya po naming supplies lang.
>
> Hindi po sakop ang expiry dates. Ganoon pa rin ang label at ang buffer ninyo.
>
> At kapag patay mismo ang laptop o phone, hindi ito makakapag-record. Kaya panatilihin po ang paper backup ninyo."

## 8. Ang kailangan namin sa inyo (2 min)

> "Para po masubukan ito sa totoong supplies ninyo, dalawang bagay ang hihingin namin.
>
> **Una, ang data.** Kailangan po namin ng mga nakaraang request at release, mula Hulyo 2023 hanggang Hunyo 2026, galing sa RR forms, logbook, at Excel ninyo. Kami po ang mag-e-encode, at may pangalawang tao na magche-check. Kailangan din po namin ang listahan ng items, ang unit, at ang huling presyo; kung alin ang hinihiram at alin ang nauubos; kung alin ang hindi puwedeng maubusan; ang kasalukuyang bilang ng stock kada imbakan; at ang karaniwang budget kada quarter.
>
> **Pangalawa, ang mga gagamit.** Ilan po ang Logistics na gagamit? Sino ang mag-e-encode at sino ang magre-release? Ilan po sa Operations at sa mga response team ang magpa-file ng request? Sino po ang Hepe at ang kapalit kapag wala? Sino sa Research and Planning ang mangangailangan ng report? At sino po ang magma-maintain ng item list?
>
> Gusto rin po naming itanong: anong device ang gamit ng staff, may server o PC ba na laging bukas, at puwede ba itong gawing HTTPS? Kailangan po iyon para gumana ang offline mode.
>
> At para sa RR form, tingnan po ninyo kung may kulang o sobra sa nasa screen kumpara sa papel ninyo."

*(Ibigay ang naka-print na listahan. Itakda ang petsa ng pagbalik ng data.)*

## 9. Pagtatapos (30 sec)

> "Para po buodin: mas kaunting bilangan, malinaw na larawan ng stock anumang oras, requests na sumusunod sa approval rule ninyo, tuloy ang trabaho kahit walang internet, at quarterly plan na kaya ninyong ipaliwanag.
>
> Ang susunod na hakbang po: ibibigay ninyo ang data at listahan ng users, ie-encode namin, at ipapakita namin ang plano gamit ang sarili ninyong records. Pagkatapos po, mag-training at pagsubok kasama ang staff.
>
> Maraming salamat po. Bukas po kami sa tanong at puna ninyo."

---

## Kapag may tanong

Mas kumpletong sagot ang nasa seksyon 3 ng [demo-script.md](demo-script.md). Maikling bersyon dito:

| Tanong | Sagot (Tagalog) |
|---|---|
| "Bakit hindi na lang Excel?" | "Gumagana po ang Excel, pero hindi ito kusang nagbabawas kapag may release, hindi nagpaplano sa budget, hindi nagpapaliwanag, at puwedeng baguhin ng kahit sino nang walang bakas." |
| "Paano kung walang kuryente o internet?" | "Tuloy po ang pag-record hangga't buhay ang device, at magsi-sync pagbalik ng internet. Kapag patay mismo ang device, ang paper backup pa rin ninyo ang sasalo." |
| "Sigurado ba ang numero ng plano?" | "Panimulang mungkahi po ito na kaya ninyong ipaliwanag, hindi ang huling sagot. Sinusubok pa namin ito sa mga records ninyo, at kayo at ang Hepe ang magpapasya." |
| "Ligtas ba ito?" | "Hindi po puwedeng palihim na baguhin ang records, at naka-log ang bawat approval. Hindi po namin sinasabing hindi ito mahahack, pero may bakas ang anumang pagbabago." |
| "Magkano o gaano katagal ang paglipat?" | "Nakadepende po sa dami ng nakaraang records na ie-encode. Sasabihin namin pagkatapos naming makita ang data." *(Huwag magbigay ng numero kung hindi pa kayo sigurado.)* |
| "Puwede bang makita ang stock ng CSWDO, CHO, GSO?" | "Hindi pa po. Ang hawak lang ng CDRRMO ang sakop, pero posibleng susunod na hakbang." |

## Mga paalala sa pagsasalita

- Gamitin ang **"po"** at ang salitang **"Hepe"** o **"Head of Office"** sa bawat banggit ng approval.
- Iwasan ang: hash, SHA-256, solver, sync, API. Sabihin: "seal sa records", "planner", "naka-save sa device at mag-a-upload mamaya".
- Sabihin ang **"mas kaunting pagkakamali"**, hindi ang "walang pagkakamali". Hindi pa sukat ang natipid na oras, at tinitest pa ang planner sa records nila.
- Isulat ang bawat sagot nila sa seksyon 2 at 8. Iyon ang ilalagay ninyo sa follow-up.
