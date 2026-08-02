/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        "bg-void": "var(--bg-void)",
        "bg-panel": "var(--bg-panel)",
        "bg-panel-raised": "var(--bg-panel-raised)",
        line: "var(--line)",
        "line-strong": "var(--line-strong)",
        amber: "var(--amber)",
        critical: "var(--critical)",
        ok: "var(--ok)",
        pending: "var(--pending)",
      },
    },
  },
  plugins: [],
};
