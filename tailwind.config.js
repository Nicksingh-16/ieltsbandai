import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Dark Electric palette
                brand: {
                    50:  '#ecfeff',
                    100: '#cffafe',
                    200: '#a5f3fc',
                    300: '#67e8f9',
                    400: '#22d3ee',
                    500: '#06b6d4', // primary accent
                    600: '#0891b2',
                    700: '#0e7490',
                    800: '#155e75',
                    900: '#164e63',
                },
                surface: {
                    950: '#0f172a', // page background
                    900: '#131c2e', // slightly lighter bg
                    800: '#1e293b', // card bg
                    700: '#263348', // card hover / elevated
                    600: '#334155', // borders
                    500: '#475569', // muted borders
                    400: '#64748b', // placeholder text
                    300: '#94a3b8', // secondary text
                    200: '#cbd5e1', // body text
                    100: '#e2e8f0', // headings
                    50:  '#f1f5f9', // bright headings
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.4s ease-out',
                'slide-up': 'slideUp 0.4s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'recording': 'recording 1.5s ease-in-out infinite',
                'waveform': 'waveform 1s ease-in-out infinite alternate',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(-8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                recording: {
                    '0%, 100%': { transform: 'scale(1)', opacity: '1' },
                    '50%': { transform: 'scale(1.08)', opacity: '0.85' },
                },
                waveform: {
                    '0%': { transform: 'scaleY(0.3)' },
                    '100%': { transform: 'scaleY(1)' },
                },
            },
            boxShadow: {
                'glow': '0 0 20px rgba(6, 182, 212, 0.25)',
                'glow-lg': '0 0 40px rgba(6, 182, 212, 0.3)',
                'card': '0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.5)',
                'card-hover': '0 8px 25px rgba(0,0,0,0.5)',
            },
        },
    },

    plugins: [forms],
};
