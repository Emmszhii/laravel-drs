// Turns raw/demo.webm (from record.mjs) into DisasterResource-demo.mp4 (H.264, plays and seeks anywhere).
// This machine's ffmpeg (Playwright's) only writes VP8, so the installed Edge re-encodes the video in real time
// with MediaRecorder, and the fragmented MP4 it produces is rebuilt below into a normal one with a real duration.
// Run: node to-mp4.mjs   (takes as long as the video; Microsoft Edge must be installed)
import { chromium } from 'file:///D:/laravel-inertia/laravel-drs/backend/node_modules/playwright/index.mjs';
import fs from 'node:fs';

const DIR = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const input = `${DIR}raw/demo.webm`;
const output = `${DIR}DisasterResource-demo.mp4`;
const KBPS = 700;

// ---- 1. WebM -> fragmented H.264 MP4, played once in Edge ----
const webm = fs.readFileSync(input);
const browser = await chromium.launch({ channel: 'msedge', args: ['--autoplay-policy=no-user-gesture-required'] });
const page = await browser.newPage();
await page.route('http://transcode.local/**', (route) =>
  new URL(route.request().url()).pathname === '/in.webm'
    ? route.fulfill({ body: webm, contentType: 'video/webm' })
    : route.fulfill({ body: '<!doctype html><body></body>', contentType: 'text/html' }),
);
await page.goto('http://transcode.local/');
const b64 = await page.evaluate(async (bps) => {
  const v = document.createElement('video');
  v.src = '/in.webm';
  v.muted = true;
  document.body.append(v);
  await new Promise((r) => v.addEventListener('loadeddata', r, { once: true }));
  const rec = new MediaRecorder(v.captureStream(), { mimeType: 'video/mp4;codecs=avc1.640028', videoBitsPerSecond: bps });
  const chunks = [];
  rec.ondataavailable = (e) => e.data.size && chunks.push(e.data);
  const stopped = new Promise((r) => (rec.onstop = r));
  rec.start(1000);
  await v.play();
  await new Promise((r) => v.addEventListener('ended', r, { once: true }));
  rec.stop();
  await stopped;
  const buf = new Uint8Array(await new Blob(chunks, { type: 'video/mp4' }).arrayBuffer());
  let s = '';
  for (let i = 0; i < buf.length; i += 0x8000) s += String.fromCharCode(...buf.subarray(i, i + 0x8000));
  return btoa(s);
}, KBPS * 1000);
await browser.close();
const b = Buffer.from(b64, 'base64');

// ---- 2. Fragmented MP4 (one video track) -> progressive MP4 with full sample tables ----
function boxes(start, end) {
  const out = [];
  for (let p = start; p + 8 <= end; ) {
    let size = b.readUInt32BE(p);
    let h = 8;
    if (size === 1) { size = Number(b.readBigUInt64BE(p + 8)); h = 16; }
    if (size === 0) size = end - p;
    out.push({ type: b.toString('latin1', p + 4, p + 8), start: p, h, end: p + size });
    p += size;
  }
  return out;
}
const kids = (bx) => boxes(bx.start + bx.h, bx.end);
const find = (list, t) => list.find((x) => x.type === t);
const body = (bx) => Buffer.from(b.subarray(bx.start + bx.h, bx.end));
const box = (type, ...parts) => {
  const head = Buffer.alloc(8);
  const data = Buffer.concat(parts);
  head.writeUInt32BE(8 + data.length);
  head.write(type, 4, 'latin1');
  return Buffer.concat([head, data]);
};
const u32 = (...ns) => { const x = Buffer.alloc(4 * ns.length); ns.forEach((n, i) => x.writeUInt32BE(n >>> 0, 4 * i)); return x; };

const top = boxes(0, b.length);
const moov = find(top, 'moov');
const trex = body(find(kids(find(kids(moov), 'mvex')), 'trex'));
const trexDef = { dur: trex.readUInt32BE(12), size: trex.readUInt32BE(16), flags: trex.readUInt32BE(20) };

// ---- collect samples from every moof/traf/trun ----
const samples = [];
for (const moof of top.filter((x) => x.type === 'moof')) {
  let nextPos = moof.start;
  for (const traf of kids(moof).filter((x) => x.type === 'traf')) {
    const tk = kids(traf);
    const tfhd = body(find(tk, 'tfhd'));
    const tff = tfhd.readUInt32BE(0) & 0xffffff;
    let q = 8; // version/flags + track_ID
    let base = tff & 0x20000 ? moof.start : nextPos;
    if (tff & 0x1) { base = Number(tfhd.readBigUInt64BE(q)); q += 8; }
    if (tff & 0x2) q += 4;
    let { dur: dDur, size: dSize, flags: dFlags } = trexDef;
    if (tff & 0x8) { dDur = tfhd.readUInt32BE(q); q += 4; }
    if (tff & 0x10) { dSize = tfhd.readUInt32BE(q); q += 4; }
    if (tff & 0x20) { dFlags = tfhd.readUInt32BE(q); q += 4; }
    let pos = base;
    for (const trunBox of tk.filter((x) => x.type === 'trun')) {
      const t = body(trunBox);
      const ver = t[0];
      const f = t.readUInt32BE(0) & 0xffffff;
      const n = t.readUInt32BE(4);
      let r = 8;
      if (f & 0x1) { pos = base + t.readInt32BE(r); r += 4; }
      let first = null;
      if (f & 0x4) { first = t.readUInt32BE(r); r += 4; }
      for (let i = 0; i < n; i++) {
        const dur = f & 0x100 ? t.readUInt32BE((r += 4) - 4) : dDur;
        const size = f & 0x200 ? t.readUInt32BE((r += 4) - 4) : dSize;
        const flags = f & 0x400 ? t.readUInt32BE((r += 4) - 4) : i === 0 && first !== null ? first : dFlags;
        const cto = f & 0x800 ? (ver ? t.readInt32BE((r += 4) - 4) : t.readUInt32BE((r += 4) - 4)) : 0;
        samples.push({ pos, size, dur, sync: !(flags & 0x10000), cto });
        pos += size;
      }
    }
    nextPos = pos;
  }
}
// A recorder may leave the last sample's duration at 0; reuse the one before it.
for (let i = 1; i < samples.length; i++) if (!samples[i].dur) samples[i].dur = samples[i - 1].dur;

// ---- sample tables ----
const stts = [];
for (const s of samples) {
  const last = stts[stts.length - 1];
  if (last && last[1] === s.dur) last[0]++; else stts.push([1, s.dur]);
}
const hasCto = samples.some((s) => s.cto);
const negCto = samples.some((s) => s.cto < 0);
const syncs = samples.map((s, i) => (s.sync ? i + 1 : 0)).filter(Boolean);
const mediaDur = samples.reduce((a, s) => a + s.dur, 0);

function readTimescale(bx) {
  const x = body(bx);
  return x[0] === 1 ? x.readUInt32BE(20) : x.readUInt32BE(12);
}
const trak = find(kids(moov), 'trak');
const mdia = find(kids(trak), 'mdia');
const mediaTS = readTimescale(find(kids(mdia), 'mdhd'));
const movieTS = readTimescale(find(kids(moov), 'mvhd'));
const movieDur = Math.round((mediaDur * movieTS) / mediaTS);

function withDuration(bx, dur, v1Off, v0Off) {
  const x = body(bx);
  if (x[0] === 1) x.writeBigUInt64BE(BigInt(dur), v1Off); else x.writeUInt32BE(dur, v0Off);
  return box(bx.type, x);
}

function stbl(offsets) {
  const stsd = box('stsd', body(find(kids(find(kids(find(kids(mdia), 'minf')), 'stbl')), 'stsd')));
  const parts = [
    stsd,
    box('stts', u32(0, stts.length), ...stts.map(([c, d]) => u32(c, d))),
  ];
  if (hasCto) {
    const runs = [];
    for (const s of samples) {
      const last = runs[runs.length - 1];
      if (last && last[1] === s.cto) last[0]++; else runs.push([1, s.cto]);
    }
    parts.push(box('ctts', u32(negCto ? 0x01000000 : 0, runs.length), ...runs.map(([c, o]) => u32(c, o))));
  }
  parts.push(
    box('stsc', u32(0, 1, 1, 1, 1)),
    box('stsz', u32(0, 0, samples.length), u32(...samples.map((s) => s.size))),
    box('stco', u32(0, samples.length), u32(...offsets)),
  );
  if (syncs.length < samples.length) parts.push(box('stss', u32(0, syncs.length), u32(...syncs)));
  return box('stbl', ...parts);
}

// Rebuild moov: drop mvex, set the three durations, replace the sample table.
function rebuild(bx, offsets) {
  switch (bx.type) {
    case 'mvex': return null;
    case 'mvhd': return withDuration(bx, movieDur, 24, 16);
    case 'tkhd': return withDuration(bx, movieDur, 28, 20);
    case 'mdhd': return withDuration(bx, mediaDur, 24, 16);
    case 'stbl': return stbl(offsets);
    case 'moov': case 'trak': case 'mdia': case 'minf':
      return box(bx.type, ...kids(bx).map((c) => rebuild(c, offsets)).filter(Boolean));
    default: return Buffer.from(b.subarray(bx.start, bx.end));
  }
}

const ftyp = Buffer.from(b.subarray(find(top, 'ftyp').start, find(top, 'ftyp').end));
const sizeOfMoov = rebuild(moov, samples.map(() => 0)).length;
const mdatBytes = samples.reduce((a, s) => a + s.size, 0);
if (ftyp.length + sizeOfMoov + 8 + mdatBytes > 0xffffffff) throw new Error('over 4 GB: needs co64');
let at = ftyp.length + sizeOfMoov + 8;
const offsets = samples.map((s) => (at += s.size) - s.size);
const mdatHead = Buffer.alloc(8);
mdatHead.writeUInt32BE(8 + mdatBytes);
mdatHead.write('mdat', 4, 'latin1');

fs.writeFileSync(output, Buffer.concat([ftyp, rebuild(moov, offsets), mdatHead, ...samples.map((s) => b.subarray(s.pos, s.pos + s.size))]));
console.log(JSON.stringify({ samples: samples.length, keyframes: syncs.length, seconds: mediaDur / mediaTS, bytes: fs.statSync(output).size }));
