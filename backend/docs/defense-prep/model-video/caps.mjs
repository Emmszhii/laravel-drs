// Collect every caption in order (fast run) into voice/captions.json, with a speech-friendly version.
import { chromium } from 'file:///D:/laravel-inertia/laravel-drs/backend/node_modules/playwright/index.mjs';
import { writeFileSync, mkdirSync } from 'node:fs';
const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const b = await chromium.launch(); const p = await b.newPage();
await p.goto(`file:///${DIR}model-explainer.html?fast`);
await p.waitForFunction(() => window.__done === true, null, { timeout: 120000 });
const caps = await p.evaluate(() => window.__caps); await b.close();
const speak = (s) => s
  .replace(/₱(\d{1,3}(?:,\d{3})*)/g, '$1 pesos')
  .replace(/\bVED\b/g, 'V E D').replace(/\bGSO\b/g, 'G S O').replace(/\bDRRM\b/g, 'D R R M')
  .replace(/\bQ(\d) (\d{4})\b/g, 'quarter $1 of $2').replace(/\bQ(\d)\b/g, 'quarter $1')
  .replace(/(\d+)%/g, '$1 percent').replace(/1-in-8/g, 'one in eight');
mkdirSync(DIR + 'voice', { recursive: true });
writeFileSync(DIR + 'voice/captions.json', JSON.stringify(caps.map((t) => ({ t, s: speak(t) })), null, 1));
console.log(caps.length, 'captions');
