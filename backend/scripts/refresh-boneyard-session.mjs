#!/usr/bin/env node
// Logs into the local Laravel dev server and writes a fresh session cookie
// into the root .env as BONEYARD_SESSION_COOKIE, which boneyard.config.json
// feeds to the boneyard-js CLI (via `env[BONEYARD_SESSION_COOKIE]`), used by
// scripts/watch-boneyard.mjs, so it can capture skeletons from auth-gated pages.
//
// The cookie rides Laravel's sliding session expiry (SESSION_LIFETIME),
// so it only goes stale after the dev server sits idle past that window.
// Re-run this script if `npm run dev` logs start showing redirects to
// /login instead of captured skeletons.
//
// Usage: node scripts/refresh-boneyard-session.mjs

import { readFileSync, writeFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { dirname, resolve } from "node:path";

const backendDir = dirname(dirname(fileURLToPath(import.meta.url)));
const envPath = resolve(backendDir, "..", ".env");

const appUrl = process.env.BONEYARD_APP_URL ?? "http://127.0.0.1:8000";
const email = process.env.BONEYARD_LOGIN_EMAIL ?? "admin@disasterresource.local";
const password = process.env.BONEYARD_LOGIN_PASSWORD ?? "password";

function parseCookies(setCookieHeaders) {
  const jar = new Map();
  for (const header of setCookieHeaders) {
    const [pair] = header.split(";");
    const idx = pair.indexOf("=");
    jar.set(pair.slice(0, idx), pair.slice(idx + 1));
  }
  return jar;
}

function cookieHeader(jar) {
  return [...jar.entries()].map(([k, v]) => `${k}=${v}`).join("; ");
}

async function main() {
  const jar = new Map();

  const loginPage = await fetch(`${appUrl}/login`, { redirect: "manual" });
  for (const [k, v] of parseCookies(loginPage.headers.getSetCookie?.() ?? [])) jar.set(k, v);

  const xsrfRaw = jar.get("XSRF-TOKEN");
  if (!xsrfRaw) throw new Error("No XSRF-TOKEN cookie returned by /login — is the dev server running?");
  const xsrfToken = decodeURIComponent(xsrfRaw);

  const loginRes = await fetch(`${appUrl}/login`, {
    method: "POST",
    redirect: "manual",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-XSRF-TOKEN": xsrfToken,
      Cookie: cookieHeader(jar),
    },
    body: new URLSearchParams({ email, password }),
  });
  for (const [k, v] of parseCookies(loginRes.headers.getSetCookie?.() ?? [])) jar.set(k, v);

  const sessionCookieName = [...jar.keys()].find((k) => k.endsWith("-session"));
  if (!sessionCookieName) {
    throw new Error(
      `Login didn't return a session cookie (status ${loginRes.status}) — check BONEYARD_LOGIN_EMAIL/BONEYARD_LOGIN_PASSWORD.`,
    );
  }
  const sessionValue = jar.get(sessionCookieName);

  const verify = await fetch(`${appUrl}/depots`, { headers: { Cookie: cookieHeader(jar) } });
  if (verify.status !== 200) {
    throw new Error(`Session verification against /depots failed (status ${verify.status}).`);
  }

  const envContent = readFileSync(envPath, "utf-8");
  const line = `BONEYARD_SESSION_COOKIE=${sessionValue}`;
  const updated = /^BONEYARD_SESSION_COOKIE=.*$/m.test(envContent)
    ? envContent.replace(/^BONEYARD_SESSION_COOKIE=.*$/m, line)
    : `${envContent.trimEnd()}\n\n# Local-only: session cookie for the boneyard-js CLI/watcher (see backend/scripts/refresh-boneyard-session.mjs)\n${line}\n`;
  writeFileSync(envPath, updated);

  console.log(`✓ BONEYARD_SESSION_COOKIE refreshed in ${envPath}`);
  console.log("  Restart `npm run dev` for the boneyard watcher to pick it up.");
}

main().catch((err) => {
  console.error(`✗ ${err.message}`);
  process.exit(1);
});
