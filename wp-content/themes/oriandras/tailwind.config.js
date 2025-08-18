/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{php,html,js}",
    "!./node_modules/**",
    "!./dist/**"
  ],
  theme: {
    extend: {
      container: {
        center: true,
        padding: '1rem'
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
    require('@tailwindcss/aspect-ratio')
  ],
}
