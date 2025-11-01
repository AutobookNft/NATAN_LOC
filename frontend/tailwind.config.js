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
        // NATAN Blue Trust (Istituzionale - 60% superficie)
        "natan-blue": {
          DEFAULT: "#1B365D", // Blu Trust principale
          light: "#2C4A7C",   // Hover, backgrounds chiari
          dark: "#0F1E36",    // Testi su backgrounds chiari
          "extra-light": "#E8EDF4", // Backgrounds sezioni
        },
        
        // NATAN Gray Structural (30% superficie)
        "natan-gray": {
          900: "#2D3748", // Headings secondari
          700: "#4A5568", // Body text principale
          500: "#718096", // Testi secondari
          300: "#CBD5E0", // Bordi, separatori
          100: "#EDF2F7", // Backgrounds chiari
          50: "#F7FAFC",  // Backgrounds sezioni alternate
        },
        
        // NATAN Trust & Certificazione
        "natan-trust": "#0F4C75",      // Blu Sicurezza (inviolabilità)
        "natan-green": "#2D5016",      // Verde Certificazione
        "natan-green-light": "#3D6B22", // Verde Certificazione light
        "natan-green-extra-light": "#E8F4E3", // Verde backgrounds
        "natan-gold": "#B89968",       // Oro Sobrio (accenti 10%)
        
        // URS Colors (Ultra Reliability Score)
        "urs-a": "#10b981", // Emerald-500 (0.85-1.0)
        "urs-b": "#3b82f6", // Blue-500 (0.70-0.84)
        "urs-c": "#f59e0b", // Amber-500 (0.50-0.69)
        "urs-x": "#ef4444", // Red-500 (0.0-0.49)
        
        // Functional Colors
        "natan-red": "#C13120",     // Rosso Urgenza
        "natan-orange": "#E67E22",  // Arancio Attenzione
        "natan-info": "#3B82F6",    // Blu Info
        "natan-disabled": "#A0AEC0", // Grigio Disabilitato
      },
      
      fontFamily: {
        institutional: ['IBM Plex Sans', 'Inter', 'sans-serif'],
        body: ['Source Sans Pro', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
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





