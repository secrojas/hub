import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: {
                    950: '#020617',
                    900: '#0f172a',
                    800: '#1e293b',
                    700: '#334155',
                    600: '#475569',
                },
                accent: {
                    violet: '#7c3aed',
                    blue:   '#2563eb',
                    cyan:   '#06b6d4',
                },
            },
            boxShadow: {
                'glow-violet': '0 0 20px rgba(124, 58, 237, 0.15)',
                'glow-cyan':   '0 0 20px rgba(6, 182, 212, 0.15)',
                'glow-blue':   '0 0 20px rgba(37, 99, 235, 0.15)',
            },
        },
    },

    plugins: [forms],
};
