// Quarter-speed run: one screenshot per caption change, plus an overlap check of all visible text.
import { chromium } from 'file:///D:/laravel-inertia/laravel-drs/backend/node_modules/playwright/index.mjs';
const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const SH = process.argv[2] || DIR + 'shots/';
import('node:fs').then((fs) => fs.mkdirSync(SH, { recursive: true }));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1280, height: 720 } });
await p.goto(`file:///${DIR}model-explainer.html?speed=0.25`);
const overlaps = (sel) => p.evaluate(() => {
  const vis = (el) => { for (let e = el; e && e !== document.body; e = e.parentElement) { const s = getComputedStyle(e); if (s.display === 'none' || s.visibility === 'hidden' || +s.opacity < 0.5) return false; } return true; };
  const boxes = [];
  const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  for (let n; (n = walk.nextNode());) {
    if (!n.textContent.trim() || n.parentElement.closest('script,style') || !vis(n.parentElement)) continue;
    const r = document.createRange(); r.selectNodeContents(n);
    for (const q of r.getClientRects()) if (q.width > 1 && q.height > 1) boxes.push({ t: n.textContent.trim().slice(0, 24), el: n.parentElement, x: q.left, y: q.top, r: q.right, b: q.bottom });
  }
  const out = [];
  for (let i = 0; i < boxes.length; i++) {
    const a = boxes[i];
    if (a.x < 0 || a.r > 1280 || a.y < 0 || a.b > 720) out.push(`OFF-FRAME "${a.t}"`);
    const inStage = a.el.closest('#stage'); if (inStage && a.b > 600.5) out.push(`INTO-CAPTION "${a.t}"`);
    const svg = a.el.closest('svg'); if (svg) { const s = svg.getBoundingClientRect(); if (a.x < s.left - 1 || a.r > s.right + 1 || a.y < s.top - 1 || a.b > s.bottom + 1) out.push(`CLIPPED-IN-CHART "${a.t}"`); }
    for (let j = i + 1; j < boxes.length; j++) {
      const c = boxes[j]; if (a.el === c.el) continue;
      const w = Math.min(a.r, c.r) - Math.max(a.x, c.x), h = Math.min(a.b, c.b) - Math.max(a.y, c.y);
      if (w > 2 && h > 3) out.push(`"${a.t}" x "${c.t}"`);
    }
  }
  return [...new Set(out)];
});
let i = 0, last = ''; const report = [];
while (!(await p.evaluate(() => window.__done))) {
  const ch = await p.evaluate(() => document.getElementById('chap').textContent + '|' + document.getElementById('cap').textContent.slice(0, 30));
  if (ch !== last) {
    last = ch; await p.waitForTimeout(700);
    await p.screenshot({ path: `${SH}s${String(i).padStart(2, '0')}.jpg`, quality: 70 });
    const o = await overlaps(); if (o.length) report.push(`s${String(i).padStart(2, '0')} [${ch.split('|')[0]}]: ${o.join('; ')}`);
    i++;
  }
  await p.waitForTimeout(150);
}
await b.close(); console.log('shots', i); console.log(report.join('\n') || 'NO OVERLAPS');
