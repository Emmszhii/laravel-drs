// Records qa-player.html with narration into "DisasterResource+ Panel Q&A Reviewer (read aloud).mp4".
// Before: node caps.mjs; powershell -File voice.ps1   (voice/c00.wav ... and voice/durations.json)
// Run:    node record-model.mjs <path-to-ffmpeg.exe>     (ffmpeg with libx264 + aac, e.g. from the imageio-ffmpeg package)
//         node record-model.mjs --fast                    (checks the page runs end to end; no video)
import { chromium } from 'file:///D:/laravel-inertia/laravel-drs/backend/node_modules/playwright/index.mjs';
import { readdirSync, renameSync, rmSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';

const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const fast = process.argv.includes('--fast');
const FFMPEG = process.argv[2] && !fast ? process.argv[2] : null;
const OUT = `${DIR}raw/`;
const durs = JSON.parse(readFileSync(`${DIR}voice/durations.json`, 'utf8'));
rmSync(OUT, { recursive: true, force: true }); mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1280, height: 720 }, deviceScaleFactor: 1,
  recordVideo: fast ? undefined : { dir: OUT, size: { width: 1280, height: 720 } },
});
const lines = JSON.parse(readFileSync(`${DIR}voice/captions.json`, 'utf8')); const cards = JSON.parse(readFileSync(`${DIR}cards.json`, 'utf8'));
await context.addInitScript(([d, l, c]) => { window.__voice = d; window.__lines = l; window.__cards = c; }, [durs, lines, cards]);
const page = await context.newPage();
const t0 = Date.now(); // the video starts when the page is created
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
await page.goto(`file:///${DIR}qa-player.html${fast ? '?fast' : ''}`);
await page.waitForFunction(() => window.__done === true, null, { timeout: 120 * 60 * 1000, polling: 500 });
await page.waitForTimeout(fast ? 100 : 2500);
const times = await page.evaluate(() => window.__capTimes);
const check = null;
const total = (Date.now() - t0) / 1000;
await context.close(); await browser.close();
console.log(JSON.stringify({ seconds: total, captions: times.length, errors, check }));
if (fast) process.exit(0);

const [f] = readdirSync(OUT).filter((x) => x.endsWith('.webm')); renameSync(OUT + f, OUT + 'model.webm');

// ---- voice track: each clip placed when its caption appears ----
const RATE = +readFileSync(`${DIR}voice/rate.txt`, 'utf8'); // set by kokoro_voice.py (24000)
const pcm = Buffer.alloc(Math.ceil((total + 1) * RATE) * 2);
times.forEach((t, i) => {
  if (!durs[i]) return; const clip = readFileSync(`${DIR}voice/c${String(i).padStart(2, '0')}.wav`).subarray(44);
  const at = Math.round(((t - t0) / 1000 + 0.15) * RATE) * 2;
  clip.copy(pcm, at, 0, Math.min(clip.length, pcm.length - at));
});
const head = Buffer.alloc(44);
head.write('RIFF', 0); head.writeUInt32LE(36 + pcm.length, 4); head.write('WAVEfmt ', 8);
head.writeUInt32LE(16, 16); head.writeUInt16LE(1, 20); head.writeUInt16LE(1, 22); head.writeUInt32LE(RATE, 24);
head.writeUInt32LE(RATE * 2, 28); head.writeUInt16LE(2, 32); head.writeUInt16LE(16, 34); head.write('data', 36); head.writeUInt32LE(pcm.length, 40);
writeFileSync(OUT + 'voice.wav', Buffer.concat([head, pcm]));

// ---- H.264 + AAC MP4 ----
const mp4 = `${DIR}DisasterResource+ Panel Q&A Reviewer (read aloud).mp4`;
execFileSync(FFMPEG, ['-y', '-loglevel', 'error', '-i', OUT + 'model.webm', '-i', OUT + 'voice.wav',
  '-map', '0:v', '-map', '1:a', '-c:v', 'libx264', '-preset', 'slow', '-crf', '24', '-tune', 'stillimage', '-pix_fmt', 'yuv420p', '-r', '25',
  '-c:a', 'aac', '-b:a', '64k', '-shortest', '-movflags', '+faststart', mp4]);
console.log(mp4);
