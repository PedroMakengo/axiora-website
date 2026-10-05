/** @type {import('tailwindcss').Config} */
// Tailwind só é usado no painel administrativo e nas páginas de login
// (assets/css/admin.css). O site público usa o CSS próprio em assets/css/main.css.
module.exports = {
  content: [
    "./app/Views/admin/**/*.php",
    "./app/Views/auth/**/*.php",
    "./app/Views/layouts/admin-*.php",
    "./app/Views/layouts/auth-*.php",
    "./assets/js/admin.js",
    "./assets/js/app.js",
  ],
  theme: {
    extend: {
      colors: {
        ink: "#0e1b2b",
        paper: "#f4f7fa",
        marca: {
          DEFAULT: "#0b1f33",
          light: "#1c3d63",
          dark: "#071624",
        },
        teal: {
          DEFAULT: "#12a596",
          dark: "#0c8478",
          soft: "#e3f5f2",
        },
        gold: {
          DEFAULT: "#e3a935",
          soft: "#fdf3dd",
        },
        line: "#e2e8ef",
        muted: "#627285",
      },
      fontFamily: {
        body: ["'Manrope'", "system-ui", "sans-serif"],
        display: ["'Manrope'", "system-ui", "sans-serif"],
      },
      borderRadius: {
        card: "12px",
      },
    },
  },
  plugins: [],
};
