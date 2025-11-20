/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
    "../laravel_backend/resources/views/**/*.blade.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],       // UI generale
        serif: ['Lora', 'serif'],            // Titoli, Intestazioni, Documenti
        mono: ['IBM Plex Mono', 'monospace'], // Dati, Log, Claim ID, URS Scores
        // Legacy fonts preserved
        institutional: ['IBM Plex Sans', 'Inter', 'sans-serif'],
        body: ['Source Sans Pro', 'sans-serif'],
      },
      colors: {
        // Bureaucratic Chic Palette
        paper: '#F9F9F7',          // Sfondo principale (non bianco puro)
        ink: '#1A202C',            // Testo principale (quasi nero)
        'border-tech': '#CBD5E1',  // Bordi strutturali
        'alert-red': '#B91C1C',    // Errori critici
        'status-emerald': '#047857',// Successi / URS Alto

        // Legacy Colors preserved
        "natan-blue": {
          DEFAULT: "#1B365D",
          light: "#2C4A7C",
          dark: "#0F1E36",
          "extra-light": "#E8EDF4",
        },
        "natan-gray": {
          900: "#2D3748",
          700: "#4A5568",
          500: "#718096",
          300: "#CBD5E0",
          100: "#EDF2F7",
          50: "#F7FAFC",
        },
        "natan-trust": "#0F4C75",
        "natan-green": "#2D5016",
        "natan-green-light": "#3D6B22",
        "natan-green-extra-light": "#E8F4E3",
        "natan-gold": "#B89968",
        "urs-a": "#10b981",
        "urs-b": "#3b82f6",
        "urs-c": "#f59e0b",
        "urs-x": "#ef4444",
        "natan-red": "#C13120",
        "natan-orange": "#E67E22",
        "natan-info": "#3B82F6",
        "natan-disabled": "#A0AEC0",
      },
      boxShadow: {
        'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)', // Ombre minime
        'none': 'none' // Preferire i bordi alle ombre
      },
      borderRadius: {
        DEFAULT: '0.125rem', // 2px (rounded-sm) - Look squadrato
        'md': '0.25rem',      // 4px - Massimo arrotondamento consentito
        'lg': '0.5rem',
        'xl': '0.75rem',
        '2xl': '1rem',
        'full': '9999px',
      },
      spacing: {
        'xs': '8px',
        'sm': '16px',
        'md': '24px',
        'lg': '32px',
        'xl': '48px',
        '2xl': '64px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
};





