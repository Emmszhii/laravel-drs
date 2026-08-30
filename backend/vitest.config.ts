import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vitest/config";
import react from "@vitejs/plugin-react";

// Separate from vite.config.ts on purpose — the Laravel/PWA plugins there
// only matter for the real asset build and would just add noise/failure
// modes to the test run.
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
    },
  },
  test: {
    environment: "jsdom",
    setupFiles: ["./resources/js/test-setup.ts"],
  },
});
