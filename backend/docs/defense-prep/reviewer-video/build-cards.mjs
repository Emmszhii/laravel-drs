// Splits study-guide.md (the Defense Reviewer) into on-screen cards with spoken lines:
// voice/captions.json (one entry per spoken sentence, with its card index) and cards.json (card HTML).
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { pathToFileURL } from 'node:url';
const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const MARKED = process.env.MARKED; // path to marked@12's lib/marked.esm.js (e.g. from: npx -y marked@12.0.2 --version)
if (!MARKED) throw new Error('Set MARKED to the path of marked/lib/marked.esm.js');
const { marked } = await import(pathToFileURL(MARKED).href);

const md = readFileSync(DIR + '../study-guide.md', 'utf8');
const tokens = marked.lexer(md);

const plain = (s) => s.replace(/\n\s*(?:[-*]|\d+\.) /g, '; ').replace(/\*\*|__|\*|`/g, '').replace(/\[([^\]]+)\]\([^)]+\)/g, '$1').replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').replace(/:\s*;/g, ':').replace(/;\s*;/g, ';').trim();
const speak = (s) => s
  .replace(/DisasterResource\+/g, 'Disaster Resource Plus').replace(/E-LIGTAS/g, 'E Ligtas').replace(/\bNOT\b/g, 'not')
  .replace(/WM = Σ\(f × x\) ÷ n/g, 'W M equals the sum of f times x, divided by n').replace(/\+\/−/g, 'plus or minus')
  .replace(/(\d) GB\b/g, '$1 gigabytes').replace(/(\d) VA\b/g, '$1 volt-amperes').replace(/\bRAM\b/g, 'ram').replace(/\bMIP gap/g, 'M I P gap')
  .replace(/\bSD\b/g, 'standard deviation').replace(/\bLP\b/g, 'linear programming').replace(/\bCRDTs?\b/g, 'C R D T')
  .replace(/₱(\d{1,3}(?:,\d{3})*)/g, '$1 pesos')
  .replace(/ICS 213-RR/g, 'I C S 2 13 R R').replace(/ISO\/IEC/g, 'I S O, I E C')
  .replace(/\bpp?\. (\d+)[–-](\d+)/g, 'pages $1 to $2').replace(/\bpp?\. (\d+)/g, 'page $1')
  .replace(/\bChapter III\b/g, 'Chapter 3').replace(/\bChapter II\b/g, 'Chapter 2').replace(/\bChapter I\b/g, 'Chapter 1')
  .replace(/\bRQ(\d)\b/g, 'R Q $1').replace(/\bQ(\d) (\d{4})\b/g, 'quarter $1 of $2').replace(/\bQ(\d)\b/g, 'quarter $1')
  .replace(/\bQ:\s*/g, 'Question: ').replace(/\bIDs\b/g, 'I Ds').replace(/\bRA (\d+)/g, 'R A $1')
  .replace(/\b(CDRRMO|LGU|LGUs|VED|ND|HTTPS|ID|IT|GSO|DRRM|COA|DSWD|PAHR|PWA|PWAs|MILP|CBC|IPO|SAA|UAT|CSV|PDF|ML|CSWDO|CHO|NPC|ERT|ISO|IEC|API|VPN|UI|ICS|SQL|PIMS|LSPU|OCD|SHA|PHP|SSD|UPS|WM)\b/g, (m) => m.replace(/s$/, '').split('').join(' ') + (m.endsWith('s') && m.length > 3 ? 's' : ''))
  .replace(/(\d+)%/g, '$1 percent').replace(/3½/g, 'three and a half')
  .replace(/→/g, ' to ').replace(/÷/g, ' divided by ').replace(/−/g, ' minus ').replace(/Σ/g, 'the sum of ').replace(/×/g, ' times ').replace(/≥/g, 'at least ').replace(/≤/g, 'at most ').replace(/±/g, 'plus or minus ')
  .replace(/ \+ /g, ' and ').replace(/–/g, ' to ').replace(/\s+/g, ' ').trim();
const split = (t) => t.split(/(?<=[.?!])\s+(?=["“A-Z0-9(])/).map((x) => x.trim()).filter(Boolean);

const cards = []; let h2 = '', h3 = '', cur = null;
// Card budget: characters of text plus a cost per line (each bullet, row or paragraph starts a new line on screen).
const MAX = 470;
function open(cont = false) { cur = { h2, h3: cont ? (h3 || h2) + ' (continued)' : h3, toks: [], lines: [], size: 0 }; cards.push(cur); }
function add(tok, lines, size) {
  const last = cur && cur.toks[cur.toks.length - 1];
  const leadIn = last && last.type === 'paragraph' && /:\s*$/.test(last.text) && cur.size + size <= MAX * 1.35; // keep "...:" with what follows
  if (!cur || (!leadIn && cur.size && cur.size + size > MAX)) open(!!cur && cur.h2 === h2 && cur.h3.replace(' (continued)', '') === (h3 || (cur.h3.endsWith('(continued)') ? h2 : '')));
  cur.toks.push(tok); cur.lines.push(...lines); cur.size += size;
}
function sentences(text) { return split(plain(text)); }
function listItems(list) { return list.items.map((it) => ({ it, text: plain(it.text), cost: plain(it.text).length + 45 * (1 + (it.text.match(/\n\s*(?:[-*]|\d+\.) /g) || []).length) })); }

let skipIntro = true;
for (const t of tokens) {
  if (t.type === 'space' || t.type === 'hr') continue;
  if (t.type === 'heading' && t.depth === 1) { cards.push({ h2: '', h3: '', title: plain(t.text), toks: [], lines: [plain(t.text).replace(/ — /, '. ') + '.'], size: 0 }); continue; }
  if (skipIntro && t.type === 'paragraph' && /^Source:/.test(t.text)) { skipIntro = false; continue; }
  if (t.type === 'heading') {
    if (t.depth === 2) { h2 = plain(t.text); h3 = ''; } else h3 = plain(t.text);
    open(); cur.lines.push(plain(t.text).replace(/^\d+\.\s*/, '') + '.'); continue;
  }
  if (t.type === 'list') {
    const items = listItems(t); let chunk = [], size = 0;
    const flush = () => { if (!chunk.length) return; const tok = { ...t, items: chunk.map((c) => c.it) }; add(tok, chunk.flatMap((c) => split(c.text)), size); chunk = []; size = 0; };
    for (const c of items) { if (size && size + c.cost > MAX - 60) flush(); chunk.push(c); size += c.cost; }
    flush(); continue;
  }
  if (t.type === 'table') {
    const rowText = (r) => r.map((c) => plain(c.text)).filter(Boolean).join(': ').replace(/: /, ' — ').replace(/ — /, ': ');
    const per = t.header.length > 2 ? 4 : 5;
    for (let i = 0; i < t.rows.length; i += per) {
      const rows = t.rows.slice(i, i + per);
      add({ ...t, rows }, rows.map((r) => r.map((c) => plain(c.text)).filter(Boolean).join(', ') + '.'), rows.reduce((a, r) => a + rowText(r).length + 70, 0) + 80);
    }
    continue;
  }
  if (t.type === 'blockquote') {
    for (const b of t.tokens) {
      if (b.type === 'space') continue;
      const lines = b.type === 'list' ? listItems(b).flatMap((c) => split(c.text)) : sentences(b.text || b.raw);
      add({ type: 'blockquote', raw: '> ' + b.raw, tokens: [b], text: b.raw }, lines, plain(b.raw).length);
    }
    continue;
  }
  if (t.type === 'paragraph' && /^\*\*Q:/.test(t.text)) { // each panel question starts its own card, headed by the question
    h3 = plain(t.text.match(/^\*\*(Q:[^*]+)\*\*/)[1]);
    if (cur && cur.toks.length) open(); else cur.h3 = h3;
  }
  add(t, sentences(t.text || t.raw), plain(t.raw).length + 30);
}

const out = [], htmls = [];
cards.forEach((c, ci) => {
  const html = c.title ? `<h1>${c.title}</h1><p class="lede">Defense Reviewer · read aloud</p>` : marked.parser(Object.assign(c.toks, { links: {} }));
  htmls.push({ h2: c.h2, h3: c.h3, html });
  for (const t of c.lines) out.push({ card: ci, t, s: speak(t) });
});
mkdirSync(DIR + 'voice', { recursive: true });
writeFileSync(DIR + 'voice/captions.json', JSON.stringify(out, null, 1));
writeFileSync(DIR + 'cards.json', JSON.stringify(htmls));
const words = out.reduce((a, l) => a + l.s.split(/\s+/).length, 0);
console.log(cards.length, 'cards,', out.length, 'lines,', words, 'words, about', Math.round(words / 150), 'min');
