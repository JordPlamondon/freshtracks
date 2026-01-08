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
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
      },
      colors: {
        'sidebar-bg': '#f1f0ee',
        'content-bg': '#ffffff',
        'page-bg': '#f1f0ee',
        'text-primary': '#1a202c',
        'text-secondary': '#718096',
        'border-light': '#e2e8f0',
        'accent': '#cefb47',
        'accent-hover': '#b8e035',
        'accent-light': '#eef2ff',
        'link': '#7576f1',
        'link-hover': '#5f60d9',
        'profile-accent': '#cefb47',
        'active-day-bg': '#f1f0ee'
      }
    },
  },
  plugins: [],
}
