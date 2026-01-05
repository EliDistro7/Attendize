/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'bari-navy': '#2d4563',
        'bari-gold': '#d4af37',
        'bari-light': '#f8f9fa',
      },
    },
  },
}