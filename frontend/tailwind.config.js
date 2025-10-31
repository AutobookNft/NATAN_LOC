/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
    "../laravel_backend/resources/views/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        // USE Pipeline URS colors
        'urs-a': '#10b981',      // Green (High reliability)
        'urs-b': '#3b82f6',      // Blue (Medium-high reliability)
        'urs-c': '#f59e0b',      // Yellow (Medium reliability)
        'urs-x': '#ef4444',      // Red (Low reliability / blocked)
      },
    },
  },
  plugins: [],
};

