import type { Config } from 'tailwindcss';

export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f2f8ff',
          100: '#ddebff',
          500: '#1f64e0',
          600: '#1a50b5',
          700: '#173f8f',
        },
      },
    },
  },
  plugins: [],
} satisfies Config;
