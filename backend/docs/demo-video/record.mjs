// Records the client demo of DisasterResource+ as a captioned video (demo database, Admin = Head of Office).
// Run: node record.mjs   (servers: solver-service on 8001, laravel-demo on 8000)
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

// ---------- Title ----------
await card('DisasterResource+', 'Supply readiness for CDRRMO San Pablo City',
  ['Stock records · ICS 213-RR requests · Quarterly purchase plan', 'Demo with sample data'], 4500);

// ---------- Sign in ----------
await page.goto(`${BASE}/login`);
await page.waitForSelector('input[type=email]');
await caption('Each staff member signs in with their own account. Here: the Head of Office.', 0);
await type(page.locator('input[type=email]'), 'admin@disasterresource.local');
await type(page.locator('input[type=password]'), 'password');
await click(page.getByRole('button', { name: 'Sign in' }));
await page.waitForURL('**/replenishment');
await pause(800);

// ---------- 1. Stock ----------
await step('1 · Stock');
await click(page.getByRole('link', { name: 'Stock', exact: true }));
await page.waitForURL('**/stock');
await caption('The Stock page shows what the office has on hand, item by item.', 3000);
await snap('stock');
const entry = page.locator('form', { hasText: 'Record a stock entry' });
await caption('Logistics records items received: 100 hygiene kits into Main Storage…', 0);
await choose(entry.getByLabel('Item'), 'Hygiene kits');
await choose(entry.getByLabel('Depot'), 'CDRRMO Main Storage');
await choose(entry.getByLabel('Movement'), 'Receipt (items in)');
await type(entry.getByLabel('Quantity'), '100');
await click(entry.getByRole('button', { name: 'Save entry' }));
const hygieneRow = page.locator('tr', { hasText: 'Hygiene kits' }).first();
await page.waitForFunction(() => [...document.querySelectorAll('tr')].some((r) => r.textContent.includes('Hygiene kits') && r.textContent.includes('100 kits')));
await caption('…and the total updates at once.', 0);
await pointAt(hygieneRow);
await pause(1800);
await caption('…and 5 handheld radios.', 0);
await choose(entry.getByLabel('Item'), 'Handheld radios');
await type(entry.getByLabel('Quantity'), '5');
await click(entry.getByRole('button', { name: 'Save entry' }));
await page.waitForFunction(() => [...document.querySelectorAll('tr')].some((r) => r.textContent.includes('Handheld radios') && r.textContent.includes('5 units')));
await pause(800);

await caption('No internet during a typhoon or brownout? Logistics keeps working.', 0);
await context.setOffline(true);
await net(false);
await pause(2200);
await choose(entry.getByLabel('Item'), 'Hygiene kits');
await choose(entry.getByLabel('Movement'), 'Release (items out)');
await type(entry.getByLabel('Quantity'), '5');
await click(entry.getByRole('button', { name: 'Save entry' }));
const waiting = page.getByText(/Waiting to sync on this device/);
await waiting.waitFor();
await caption('The entry is saved on this device and waits for the connection.', 0);
await pointAt(waiting);
await snap('offline');
await pause(2400);
await context.setOffline(false);
await page.evaluate(() => window.dispatchEvent(new Event('online')));
await net(true);
await caption('When the connection returns, it uploads by itself. Nothing is lost.', 0);
await waiting.waitFor({ state: 'detached', timeout: 20000 });
await page.waitForFunction(() => [...document.querySelectorAll('tr')].some((r) => r.textContent.includes('Hygiene kits') && r.textContent.includes('95 kits')));
await pointAt(hygieneRow);
await pause(2000);
await net(null);
await caption('Records are never overwritten. A seal on the records shows if anything was changed or deleted.', 4200);

// ---------- 2. ICS 213-RR ----------
await step('2 · ICS 213-RR requests');
await click(page.getByRole('link', { name: 'ICS 213-RR', exact: true }));
await page.waitForURL('**/ics-requests');
await caption('Requests use the same ICS 213-RR form the office already uses.', 3000);
const form = page.locator('form', { hasText: 'File an ICS 213-RR request' });
await caption('Saved kits, like the office\'s Medical backpack #1, fill the form in one click.', 0);
await pointAt(form.getByLabel('Load a kit'));
await pause(2400);
await caption('Here, the response team asks for 20 hygiene kits and 2 radios.', 0);
await type(form.getByLabel("Requester's name"), 'Juan Dela Cruz');
await type(form.getByLabel("Requester's position"), 'ERT Team Leader');
await type(form.getByLabel('Division or team'), 'Operations and Warning / ERT');
await choose(form.getByLabel('Purpose'), 'Team kit');
await choose(form.getByLabel('Item 1', { exact: true }), 'Hygiene kits');
await type(form.getByLabel(/Quantity for item 1/), '20');
await click(form.getByRole('button', { name: '+ Add item' }));
await choose(form.getByLabel('Item 2', { exact: true }), 'Handheld radios');
await type(form.getByLabel(/Quantity for item 2/), '2');
await click(form.getByRole('button', { name: 'File request' }));
const req = page.locator('article', { hasText: 'Operations and Warning / ERT' }).first();
await req.waitFor();
await caption('Only the Head of Office approves. Approval reserves the stock, so it can\'t be promised twice.', 0);
await choose(req.getByLabel('Depot for Hygiene kits'), 'CDRRMO Main Storage (95 available)');
await choose(req.getByLabel('Depot for Handheld radios'), 'CDRRMO Main Storage (5 available)');
await click(req.getByRole('button', { name: 'Approve' }));
await req.getByText('approved', { exact: true }).waitFor();
await pause(1200);
await caption('Logistics releases the items. Stock goes down by itself; radios need the borrower\'s name.', 0);
await type(req.getByLabel(/Quantity to release for Hygiene kits/), '20');
await type(req.getByLabel(/Quantity to release for Handheld radios/), '2');
await type(req.getByLabel("Borrower's name"), 'Pedro Santos');
await click(req.getByRole('button', { name: 'Release' }));
await req.getByText('closed', { exact: true }).waitFor();
await snap('ics');
await pause(1500);
await caption('Every step is logged with who did it and when.', 0);
const log = req.getByText(/approved by Head of Office/);
if (!(await log.isVisible())) await req.locator('button').first().click(); // a closed request may fold
await pointAt(log);
await pause(2400);

await click(page.getByRole('link', { name: 'Borrowed equipment' }));
await page.waitForURL('**/borrowed');
await caption('Borrowed equipment is tracked until it comes back. Borrower names stay private.', 0);
await pause(3000);
await click(page.getByRole('button', { name: 'Returned' }).first());
await pause(1800);
await snap('borrowed');

// ---------- 3. Quarterly plan ----------
await step('3 · Quarterly purchase plan');
await click(page.getByRole('link', { name: 'Replenishment', exact: true }));
await page.waitForURL('**/replenishment');
await caption('Each quarter, the planner suggests what to buy, using the office\'s own past use.', 3400);
await caption('The Head of Office enters the quarter\'s budget, ₱300,000, and runs it.', 0);
await type(page.getByLabel('Quarterly budget (₱)'), '300000');
await click(page.getByRole('button', { name: 'Run solver' }));
const result = page.getByText(/Spends ₱/);
await result.waitFor({ timeout: 60000 });
await pause(800);
const table = page.locator('table', { hasText: 'Target stock' }).first();
await caption('Every item gets an order, a target, and a reason in plain words.', 0);
await pointAt(table);
await snap('plan');
await pause(2600);
await caption('When the money runs short, the reason says so.', 0);
await pointAt(table.locator('tr', { hasText: 'partly covered' }).first());
await pause(3000);
await caption('The budget curve shows the same plan at other budgets. Where it flattens, more money stops helping.', 0);
await pointAt(page.getByRole('img', { name: /Budget curve chart/ }));
await snap('curve');
await pause(3600);
await caption('It also shows where the next ₱10,000 would go.', 0);
await pointAt(page.getByText(/The next ₱10,000 goes to/i));
await pause(2600);
await caption('Nothing is bought until the Head of Office approves the plan.', 0);
await click(page.getByRole('button', { name: 'Approve', exact: true }));
await page.waitForFunction(() => /recommendation\s*·\s*approved/i.test(document.body.innerText));
await pause(1800);
await caption('The approved plan prints as a report for the purchase request.', 0);
await pointAt(page.getByRole('link', { name: 'Download PDF' }));
await pause(2800);
await caption('', 0);
await step('');

// ---------- End ----------
await card('DisasterResource+', 'Know what to buy, and why.',
  ['Next step: test the planner on the office\'s own records, July 2023 – June 2026.', 'Thank you.'], 5000);

await context.close();
await browser.close();
if (!shotsOnly) {
  const [file] = readdirSync(OUT).filter((f) => f.endsWith('.webm'));
  renameSync(`${OUT}${file}`, `${OUT}demo.webm`);
  console.log(`${OUT}demo.webm`);
}
