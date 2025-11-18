/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          bg: '#0a0f1f',
          text: '#e6ecff',
          accent: '#4f46e5'
        }
      }
    },
  },
  plugins: [],
}
