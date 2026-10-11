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
        panel: "var(--bg-panel)",
        "panel-raised": "var(--bg-panel-raised)",
        tint: "var(--tint)",
        line: "var(--line)",
        "line-strong": "var(--line-strong)",
        amber: "var(--amber)",
        critical: "var(--critical)",
        ok: "var(--ok)",
        pending: "var(--pending)",
        info: "var(--info)",
      },
      keyframes: {
        ticker: {
          from: { transform: "translateX(0)" },
          to: { transform: "translateX(-50%)" },
        },
        fadeIn: {
          from: { opacity: "0" },
          to: { opacity: "1" },
        },
        slideInRight: {
          from: { transform: "translateX(100%)" },
          to: { transform: "translateX(0)" },
        },
        slideInLeft: {
          from: { transform: "translateX(-100%)" },
          to: { transform: "translateX(0)" },
        },
        shimmer: {
          from: { backgroundPosition: "200% 0" },
          to: { backgroundPosition: "-200% 0" },
        },
      },
      animation: {
        ticker: "ticker 40s linear infinite",
        fadeIn: "fadeIn 0.2s ease-out",
        slideInRight: "slideInRight 0.25s ease-out",
        slideInLeft: "slideInLeft 0.25s ease-out",
        shimmer: "shimmer 1.5s ease-in-out infinite",
      },
    },
  },
  plugins: [],
};
