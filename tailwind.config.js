/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./storage/framework/views/*.php",
        "./app/View/Components/**/*.php",
      ],
  theme: {
      extend: {
          colors: {
              primary: '#D4A373', // Warm brown for sidebar
              secondary: '#ecb99e', // Peach for buttons and accents
              neutral: '#FFFFFF', // White for main content background
              'header-color': '#FEFAE0', // Near-white ivory for header
              'hover-color': '#e2e8f0', // Soft cream beige for hover states
              'primary-dark': '#B8956A', // Darker brown for emphasis
              // Keep old colors for reference
              'paynes-gray': '#68727A',
              'charcoal': '#36535E',
          }
      },
  },
  plugins: [],
}