#!/usr/bin/env node
// Watches component source for changes and re-runs `boneyard-js build` against
// the real Laravel dev server (not Vite's) whenever something that could
// affect a <Skeleton>-wrapped layout changes.
//
// boneyard-js ships an official Vite plugin (`boneyard-js/vite`), but it
// always targets routes on Vite's OWN dev server address. In this Laravel +
// Inertia app, Vite only serves the JS/CSS module graph — actual pages
// (/depots, /resources, ...) are rendered by Laravel on a different port —
// so the official plugin can never find them. This script re-implements the
// "capture on change" behavior using the CLI, which accepts an explicit URL.
//
// Usage: node scripts/watch-boneyard.mjs

import { spawn } from "node:child_process";
import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { dirname, resolve } from "node:path";
import chokidar from "chokidar";

const backendDir = dirname(dirname(fileURLToPath(import.meta.url)));
const envPath = resolve(backendDir, "..", ".env");

const appUrl = process.env.BONEYARD_APP_URL ?? "http://127.0.0.1:8000";
// Routes that render a <Skeleton>-wrapped component. Extend this list as
// more components adopt boneyard-js.
const routes = ["/depots"];

function loadRootEnv() {
  let content;
  try {
    content = readFileSync(envPath, "utf-8");
  } catch {
    return;
  }
  for (const line of content.split("\n")) {
    const match = /^([A-Z0-9_]+)=(.*)$/.exec(line.trim());
    if (match && process.env[match[1]] === undefined) {
      process.env[match[1]] = match[2];
    }
  }
}

function runBuild() {
  return new Promise((resolveRun) => {
    const args = ["boneyard-js", "build", ...routes.map((r) => `${appUrl}${r}`)];
    const child = spawn("npx", args, { cwd: backendDir, stdio: "inherit", shell: true });
    child.on("exit", () => resolveRun());
  });
}

async function main() {
  loadRootEnv();
  if (!process.env.BONEYARD_SESSION_COOKIE) {
    console.log(
      "  [boneyard-watch] no BONEYARD_SESSION_COOKIE in .env — run `node scripts/refresh-boneyard-session.mjs` first.",
    );
  }

  console.log(`  [boneyard-watch] watching for changes, capturing against ${appUrl}`);
  await runBuild();

  let debounceTimer = null;
  const watcher = chokidar.watch("resources/js/**/*.{tsx,ts,jsx,js}", {
    cwd: backendDir,
    ignored: ["**/bones/**", "**/node_modules/**"],
    ignoreInitial: true,
  });

  watcher.on("all", (_event, path) => {
    if (path.includes("/bones/")) return;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      debounceTimer = null;
      console.log(`  [boneyard-watch] change detected (${path}) — recapturing...`);
      runBuild();
    }, 1000);
  });
}

main();
