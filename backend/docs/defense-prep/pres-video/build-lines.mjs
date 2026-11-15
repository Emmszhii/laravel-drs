// Builds lines.json: every sentence of the Presentation Guide's scripts (via the Study Coach's PRES data), in order,
// with the slide it belongs to and a speech-friendly version for the offline voice.
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const h = readFileSync(DIR + '../study-coach.html', 'utf8');
const P = JSON.parse(h.slice(h.indexOf('const PRES=') + 11, h.indexOf(';\nconst NUMS=')));
const speak = (s) => s
  .replace(/DisasterResource\+/g, 'Disaster Resource Plus')
  .replace(/₱(\d{1,3}(?:,\d{3})*)/g, '$1 pesos')
  .replace(/ICS 213-RR/g, 'I C S 2 13 R R')
  .replace(/ISO\/IEC/g, 'I S O, I E C')
  .replace(/\bChapter III\b/g, 'Chapter 3').replace(/\bChapter II\b/g, 'Chapter 2').replace(/\bChapter I\b/g, 'Chapter 1')
  .replace(/\bRQ(\d)\b/g, 'R Q $1')
  .replace(/\bQ(\d) (\d{4})\b/g, 'quarter $1 of $2').replace(/\bQ(\d)\b/g, 'quarter $1')
  .replace(/\bIDs\b/g, 'I Ds')
  .replace(/\b(CDRRMO|LGU|VED|ND|HTTPS|ID|IT|GSO|DRRM|COA|DSWD|PWA|MILP|CBC)\b/g, (m) => m.split('').join(' '))
  .replace(/(\d+)%/g, '$1 percent').replace(/3½/g, 'three and a half');
const split = (t) => t.split(/(?<=[.?!])\s+(?=["“A-Z0-9])/).map((x) => x.trim()).filter(Boolean);
const lines = [];
for (const x of P) {
  if (x.n === 31) { lines.push({ n: 31, title: x.title, t: '', s: '', hold: 4000 }); continue; }
  let say = x.say.slice();
  if (x.n === 30 && say.length === 2) {
    split(say[0]).forEach((t) => lines.push({ n: 30, title: x.title, t, s: speak(t) }));
    lines.push({ n: 30, title: x.title, t: '[ The demo video plays here: about 3½ minutes. ]', s: '', hold: 4000, card: true });
    say = [say[1].replace(/^After the video:\s*/, '')];
  }
  const all = [...say, ...(x.trans ? [x.trans] : [])];
  for (const part of all) for (const t of split(part)) lines.push({ n: x.n, title: x.title, t, s: speak(t), time: x.time });
}
mkdirSync(DIR + 'voice', { recursive: true });
writeFileSync(DIR + 'voice/captions.json', JSON.stringify(lines, null, 1));
const plan = P.map((x) => ({ n: x.n, title: x.title, time: x.time }));
writeFileSync(DIR + 'plan.json', JSON.stringify(plan));
console.log(lines.length, 'lines over', new Set(lines.map((l) => l.n)).size, 'slides');
