// Records the proposal-defense demo of DisasterResource+ (sample dataset; Admin = Head of Office), following slide 30.
// Run: bash reset.sh, start solver-service (8001) and laravel-demo (8000), then: node record-defense.mjs, then node to-mp4.mjs
import { chromium } from 'file:///D:/laravel-inertia/laravel-drs/backend/node_modules/playwright/index.mjs';
import { readdirSync, renameSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:8000';
const OUT = new URL('./raw/', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const W = 1280, H = 720;
const shotsOnly = process.argv.includes('--shots');

rmSync(OUT, { recursive: true, force: true });
mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: W, height: H },
  recordVideo: shotsOnly ? undefined : { dir: OUT, size: { width: W, height: H } },
  serviceWorkers: 'block', // offline is shown with the page's own outbox, not the worker's cache
  locale: 'en-PH',
  timezoneId: 'Asia/Manila',
});
await context.addInitScript(() => {
  try { localStorage.setItem('disasterresource_theme', 'light'); } catch {}
  const mount = () => {
    if (document.getElementById('demo-overlay')) return;
    const style = document.createElement('style');
    style.textContent = `
      #demo-overlay{position:fixed;inset:0;pointer-events:none;z-index:2147483647;font-family:Inter,Segoe UI,Arial,sans-serif}
      #demo-cap{position:absolute;left:50%;bottom:28px;transform:translateX(-50%);max-width:1040px;padding:14px 26px;border-radius:12px;
        background:rgba(15,23,42,.92);color:#fff;font-size:24px;line-height:1.35;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,.35);
        opacity:0;transition:opacity .35s}
      #demo-cap.on{opacity:1}
      #demo-step{position:absolute;right:24px;top:18px;padding:8px 16px;border-radius:999px;background:#1f4e79;color:#fff;font-size:18px;
        font-weight:600;opacity:0;transition:opacity .35s}
      #demo-step.on{opacity:1}
      #demo-net{position:absolute;left:50%;top:18px;transform:translateX(-50%);padding:8px 18px;border-radius:999px;font-size:18px;font-weight:700;
        color:#fff;opacity:0;transition:opacity .3s}
      #demo-net.on{opacity:1}
      #demo-cursor{position:absolute;left:640px;top:360px;width:22px;height:22px;margin:-11px 0 0 -11px;border-radius:50%;
        background:rgba(255,255,255,.85);border:3px solid #e11d48;box-shadow:0 2px 8px rgba(0,0,0,.4);transition:left .6s ease,top .6s ease}
      #demo-cursor.press{transform:scale(.7)}
      .demo-ring{position:absolute;border:3px solid #e11d48;border-radius:8px;box-shadow:0 0 0 6px rgba(225,29,72,.18);transition:opacity .4s}`;
    const o = document.createElement('div');
    o.id = 'demo-overlay';
    o.innerHTML = '<div id="demo-step"></div><div id="demo-net"></div><div id="demo-cap"></div><div id="demo-cursor"></div>';
    document.documentElement.append(style, o);
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mount); else mount();
});

const page = await context.newPage();
const pause = (ms) => page.waitForTimeout(ms);
let shot = 0;
const snap = async (name) => { if (shotsOnly) await page.screenshot({ path: `${OUT}${String(++shot).padStart(2, '0')}-${name}.png` }); };

async function overlay(fn, arg) { await page.evaluate(fn, arg); }
async function caption(text, ms = 3200) {
  await overlay((t) => { const c = document.getElementById('demo-cap'); c.textContent = t; c.classList.toggle('on', !!t); }, text);
  if (ms) await pause(ms);
}
async function step(text) {
  await overlay((t) => { const s = document.getElementById('demo-step'); s.textContent = t; s.classList.toggle('on', !!t); }, text);
}
async function net(online) {
  await overlay((on) => {
    const n = document.getElementById('demo-net');
    n.textContent = on === null ? '' : on ? 'Back online' : 'No internet connection';
    n.style.background = on ? '#15803d' : '#b91c1c';
    n.classList.toggle('on', on !== null);
  }, online);
}
async function pointAt(locator, ring = true, onTarget = false) {
  // Keep the target in the upper middle of the screen, clear of the caption.
  await locator.evaluate((el) => {
    const r = el.getBoundingClientRect();
    if (r.top < 70 || r.bottom > innerHeight - 150) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
  });
  await pause(600);
  const b = await locator.boundingBox();
  if (!b) return;
  await overlay(({ x, y, w, h, ring, onTarget }) => {
    const c = document.getElementById('demo-cursor');
    // Point beside text, not on it; clicks land on the target.
    c.style.left = `${onTarget ? x + w / 2 : x + w + 16}px`; c.style.top = `${y + h / 2}px`;
    if (ring) {
      const r = document.createElement('div');
      r.className = 'demo-ring';
      Object.assign(r.style, { left: `${x - 6}px`, top: `${y - 6}px`, width: `${w + 12}px`, height: `${h + 12}px`, opacity: '0' });
      document.getElementById('demo-overlay').append(r);
      setTimeout(() => (r.style.opacity = '1'), 450);
      setTimeout(() => (r.style.opacity = '0'), 1700);
      setTimeout(() => r.remove(), 2200);
    }
  }, { x: b.x, y: b.y, w: b.width, h: b.height, ring, onTarget });
  await pause(700);
}
async function click(locator) {
  await pointAt(locator, true, true);
  await overlay(() => document.getElementById('demo-cursor').classList.add('press'));
  await locator.click();
  await overlay(() => document.getElementById('demo-cursor').classList.remove('press'));
  await pause(500);
}
async function type(locator, text) {
  await pointAt(locator, false, true);
  await locator.click();
  await locator.fill('');
  await locator.pressSequentially(text, { delay: 45 });
  await pause(300);
}
async function choose(locator, label) {
  await pointAt(locator, false, true);
  await locator.selectOption({ label });
  await pause(500);
}
async function card(title, sub, lines, ms) {
  await page.setContent(`<html><body style="margin:0;height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;
    background:linear-gradient(135deg,#0f2a44,#1f4e79);color:#fff;font-family:Inter,Segoe UI,Arial,sans-serif;text-align:center">
    <div style="font-size:64px;font-weight:800;letter-spacing:.5px">${title}</div>
    <div style="font-size:28px;margin-top:14px;opacity:.95">${sub}</div>
    <div style="font-size:20px;margin-top:34px;opacity:.8;line-height:1.6">${lines.join('<br>')}</div></body></html>`);
  await pause(ms);
}

const filterStock = async (text) => {
  const f = page.getByLabel('Filter items');
  await f.fill(text);
  await pause(600);
};
const rowHas = (item, text) => page.waitForFunction(([i, t]) => [...document.querySelectorAll('tr')].some((r) => r.textContent.includes(i) && r.textContent.includes(t)), [item, text], { timeout: 20000 });

// ---------- Title ----------
await card('DisasterResource+', 'Proposal defense · system demonstration',
  ['Replenishment with reasons · Backtest · Offline stock records · ICS 213-RR · Quarterly report',
   "Sample dataset built from public sources, not the office's records"], 5000);

// ---------- Sign in ----------
await page.goto(`${BASE}/login`);
await page.waitForSelector('input[type=email]');
await caption('Each user signs in with a role. Here: the Head of Office, the system Admin.', 0);
await type(page.locator('input[type=email]'), 'admin@disasterresource.local');
await type(page.locator('input[type=password]'), 'password');
await click(page.getByRole('button', { name: 'Sign in' }));
await page.waitForURL('**/replenishment');
await pause(800);

// ---------- 1. Replenishment ----------
await step('1 · Quarterly recommendation');
await caption("Each quarter, the model plans every item from its own past quarters, within the budget.", 3400);
await caption("The Head of Office enters the quarter's budget, ₱300,000, and runs the solver.", 0);
await type(page.getByLabel('Quarterly budget (₱)'), '300000');
await click(page.getByRole('button', { name: 'Run solver' }));
await page.getByRole('meter', { name: 'Budget spent' }).waitFor({ timeout: 90000 });
await pause(800);
const table = page.locator('table', { hasText: 'Target stock' }).first();
await caption('Every item gets an order, a target, and one reason read from the solution.', 0);
await pointAt(table);
await snap('plan');
await pause(2800);
const partly = table.locator('tr', { hasText: 'partly covered' }).first();
if (await partly.count()) {
  await caption('When the budget runs out, the reason says so: this item is only partly covered.', 0);
  await pointAt(partly);
  await pause(3000);
}
await caption('The budget curve re-solves the model at 11 budget levels, from 50% to 150%.', 0);
await pointAt(page.getByRole('img', { name: /Budget curve chart/ }));
await snap('curve');
await pause(3600);
await caption('It also shows exactly what the next ₱10,000 would buy.', 0);
await pointAt(page.getByText(/next ₱10,000 goes to/i).first());
await pause(3000);
await caption('Nothing is bought until the Head of Office approves the plan.', 0);
await click(page.getByRole('button', { name: 'Approve plan', exact: true }));
await page.waitForFunction(() => /recommendation\s*Approved/i.test(document.body.innerText), null, { timeout: 20000 });
await pause(1800);

// ---------- 2. Backtest ----------
await step('2 · Backtest');
await click(page.getByRole('link', { name: 'Backtest', exact: true }));
await page.waitForURL('**/backtest');
await caption('The backtest plans each past quarter using only the quarters before it, then scores it on real use.', 3800);
await type(page.getByLabel('Budget per quarter (₱)'), '300000');
await click(page.getByRole('button', { name: 'Run backtest' }));
await page.getByText(/over \d+ quarters/).waitFor({ timeout: 180000 });
await pause(800);
const btTable = page.locator('table').first();
await caption("The rules are compared quarter by quarter; the office's actual orders join once its records arrive. Lower weighted shortage is better.", 0);
await pointAt(btTable);
await snap('backtest');
await pause(4200);
const typhoon = page.getByText('typhoon event').first();
if (await typhoon.count()) {
  await caption('Typhoon quarters are marked and reported one by one.', 0);
  await pointAt(typhoon);
  await pause(2800);
}
await caption("On the office's own records, this is the test that answers Research Question 2.", 3200);

// ---------- 3. Stock records ----------
await step('3 · Stock records');
await click(page.getByRole('link', { name: 'Stock', exact: true }));
await page.waitForURL('**/stock');
await caption('Every stock change is an entry. Current stock is the sum of the entries.', 3000);
const entry = page.locator('form', { hasText: 'Record a stock entry' });
await caption('Logistics records 100 hygiene kits received into Main Storage…', 0);
await choose(entry.getByRole('combobox', { name: 'Item', exact: true }), 'Hygiene kits');
await choose(entry.getByLabel('Depot'), 'CDRRMO Main Storage');
await choose(entry.getByLabel('Movement'), 'Receipt (items in)');
await type(entry.getByLabel('Quantity'), '100');
await click(entry.getByRole('button', { name: 'Save entry' }));
await filterStock('Hygiene');
await rowHas('Hygiene kits', '100 kits');
await caption('…and the total updates at once.', 0);
await pointAt(page.locator('tr', { hasText: 'Hygiene kits' }).first());
await pause(1800);
await caption('…and 5 handheld radios.', 0);
await choose(entry.getByRole('combobox', { name: 'Item', exact: true }), 'Handheld radios');
await choose(entry.getByLabel('Movement'), 'Receipt (items in)');
await type(entry.getByLabel('Quantity'), '5');
await click(entry.getByRole('button', { name: 'Save entry' }));
await filterStock('Handheld');
await rowHas('Handheld radios', '5 units');
await pause(800);

await caption('No internet during a typhoon or brownout? Logistics keeps working.', 0);
await context.setOffline(true);
await net(false);
await pause(2200);
await choose(entry.getByRole('combobox', { name: 'Item', exact: true }), 'Hygiene kits');
await choose(entry.getByLabel('Movement'), 'Release (items out)');
await type(entry.getByLabel('Quantity'), '5');
await click(entry.getByRole('button', { name: 'Save entry' }));
await caption('A second offline release: 10 blankets, though none are recorded in stock.', 0);
await choose(entry.getByRole('combobox', { name: 'Item', exact: true }), 'Blankets');
await type(entry.getByLabel('Quantity'), '10');
await click(entry.getByRole('button', { name: 'Save entry' }));
const waiting = page.getByText(/Waiting to sync on this device/).first();
await waiting.waitFor();
await caption('Both entries wait on this device, each with its own device-made ID.', 0);
await pointAt(waiting);
await snap('offline');
await pause(2600);
await context.setOffline(false);
await page.evaluate(() => window.dispatchEvent(new Event('online')));
await net(true);
await caption('Back online, they sync by themselves. A repeated upload of the same ID is ignored, so nothing is counted twice.', 0);
await page.getByText(/Waiting to sync on this device/).first().waitFor({ state: 'detached', timeout: 30000 });
await pause(1500);
await page.reload();
await page.waitForURL('**/stock');
await filterStock('Hygiene');
await rowHas('Hygiene kits', '95 kits');
await pointAt(page.locator('tr', { hasText: 'Hygiene kits' }).first());
await pause(2200);
await net(null);
const conflicts = page.getByRole('alert').filter({ hasText: /conflicts? to resolve/ });
await conflicts.waitFor({ timeout: 15000 });
await caption('The blanket release would make stock negative. The goods already moved, so it is kept and flagged for Logistics, not refused.', 0);
await pointAt(conflicts);
await snap('conflict');
await pause(4200);
await caption("“Check records” recomputes every entry's fingerprint. Any later edit or deletion would show here.", 0);
await click(page.getByRole('button', { name: 'Check records' }));
const intact = page.getByText(/Records intact:/);
await intact.waitFor({ timeout: 20000 });
await pointAt(intact);
await snap('check');
await pause(3200);

// ---------- 4. ICS 213-RR ----------
await step('4 · ICS 213-RR requests');
await click(page.getByRole('link', { name: 'ICS 213-RR', exact: true }));
await page.waitForURL('**/ics-requests');
await caption('Requests use the same ICS 213-RR form the office already files, with requested and released quantities.', 3200);
const form = page.locator('form', { hasText: 'File an ICS 213-RR request' });
await caption('Here, the response team asks for 20 hygiene kits and 2 radios.', 0);
await type(form.getByLabel("Requester's name"), 'Juan Dela Cruz');
await type(form.getByLabel('Position', { exact: true }), 'ERT Team Leader');
const divTrigger = form.locator('div', { hasText: /^Division \/ team/ }).getByRole('button').first();
await click(divTrigger);
const divSearch = page.getByPlaceholder('Search divisions and teams…').last();
await divSearch.pressSequentially('ERT', { delay: 60 });
const divOption = page.getByRole('button', { name: /ERT$/ }).last();
await divOption.waitFor({ timeout: 10000 });
await pause(600);
await click(divOption);
await choose(form.getByLabel('Purpose'), 'Team kit');
await choose(form.getByLabel('Item 1', { exact: true }), 'Hygiene kits');
await type(form.getByLabel(/Quantity for item 1/), '20');
await click(form.getByRole('button', { name: '+ Add item' }));
await choose(form.getByLabel('Item 2', { exact: true }), 'Handheld radios');
await type(form.getByLabel(/Quantity for item 2/), '2');
await click(form.getByRole('button', { name: 'File request' }));
const req = page.locator('article', { hasText: 'Juan Dela Cruz' }).first();
await req.waitFor();
await caption('Only the Head of Office approves. Approval reserves the stock, so it cannot be promised twice.', 0);
await choose(req.getByLabel('Depot for Hygiene kits'), 'CDRRMO Main Storage (95 available)');
await choose(req.getByLabel('Depot for Handheld radios'), 'CDRRMO Main Storage (5 available)');
await click(req.getByRole('button', { name: 'Approve' }));
await req.locator('button[aria-expanded]').getByText('Approved', { exact: true }).waitFor();
await pause(1200);
await caption("Logistics releases the items. Stock goes down by itself, and the radios need a borrower's name.", 0);
await type(req.getByLabel(/Quantity to release for Hygiene kits/), '20');
await type(req.getByLabel(/Quantity to release for Handheld radios/), '2');
await type(req.getByLabel("Borrower's name"), 'Pedro Santos');
await click(req.getByRole('button', { name: 'Release' }));
await req.locator('button[aria-expanded]').getByText('Closed', { exact: true }).waitFor();
await snap('ics');
await pause(1500);
await caption('Every status change is logged with who made it and their position.', 0);
const log = req.getByText(/approved by Head of Office/);
if (!(await log.isVisible())) await req.locator('button').first().click();
await pointAt(log);
await pause(2600);

await click(page.getByRole('link', { name: 'Borrowed equipment' }));
await page.waitForURL('**/borrowed');
await caption('Borrowed equipment is followed until it comes back: issued, returned, for repair, condemned, or surrendered.', 0);
await pause(3200);
await click(page.getByRole('button', { name: 'Returned' }).first());
await pause(1800);
await snap('borrowed');

// ---------- 5. Report ----------
await step('5 · Quarterly report');
await click(page.getByRole('link', { name: 'Report', exact: true }));
await page.waitForURL('**/report');
await caption('The quarterly report shows stock, use per quarter, quarters of cover, and the approved reorder.', 3600);
const fp = page.getByText(/Latest fingerprint/).first();
await caption('It prints the latest record fingerprint, so a rebuilt chain would no longer match the printed copy.', 0);
await pointAt(fp);
await snap('report');
await pause(3800);
await caption("It downloads as a PDF or CSV for the purchase request and the office's paper files.", 0);
await pointAt(page.getByRole('link', { name: 'PDF' }));
await pause(3000);
await caption('', 0);
await step('');

// ---------- End ----------
await card('DisasterResource+', "Plan from the office's own history. Explain every cut. Trust the records.",
  ["Next: run the backtest on CDRRMO's own records, Q3 2023 – Q2 2026.", 'Thank you.'], 5000);

await context.close();
await browser.close();
if (!shotsOnly) {
  const [file] = readdirSync(OUT).filter((f) => f.endsWith('.webm'));
  renameSync(`${OUT}${file}`, `${OUT}demo.webm`);
  console.log(`${OUT}demo.webm`);
}
