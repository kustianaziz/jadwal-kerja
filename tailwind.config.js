/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        display: ['Roboto Slab', 'Zilla Slab', 'serif'],
      },
      colors: {
        'ice-blue': '#F0F8FF', // Alice Blue
        'tech-blue': '#28A8EA', // The logo cyan/blue
        'steel-gray': '#94A3B8', // Slate 400
        'dark-navy': '#0F172A', // Slate 900
        'cyan-glow': '#38BDF8', // Light blue/cyan for highlights
        'gauge-green': '#10B981',
        'gauge-amber': '#F59E0B',
        'gauge-orange': '#F97316',
        'gauge-red': '#EF4444',
      },
      boxShadow: {
        'skeuo-card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.6)',
        'skeuo-btn': '0 2px 4px rgba(0,0,0,0.2), inset 0 1px 1px rgba(255,255,255,0.3), inset 0 -2px 1px rgba(0,0,0,0.2)',
        'skeuo-btn-pressed': 'inset 0 2px 4px rgba(0,0,0,0.3)',
        'skeuo-input': 'inset 0 2px 4px rgba(0, 0, 0, 0.1), 0 1px 0 rgba(255, 255, 255, 0.5)',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.5s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        }
      }
    },
  },
  plugins: [],
}
