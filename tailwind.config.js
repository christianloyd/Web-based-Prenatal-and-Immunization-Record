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
              primary: '#243b55',
              secondary: '#141e30',
              'paynes-gray': '#68727A',
              'charcoal': '#36535E',
              '': '#3B82F6',
              '': '#2563EB',
              
          }
      },
  },
  plugins: [],
}