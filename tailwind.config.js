import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/css/**/*.css',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        'fi-btn',
        'fi-btn-primary',
        'fi-badge',
        'fi-btn-secondary',
        'fi-btn-danger',
        'fi-badge-primary',
        'fi-badge-success',
        'fi-badge-warning',
        'fi-badge-danger',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Inter',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            // Ensure Tailwind knows our "primary" palette so classes like bg-primary-600 work in production
            colors: {
                primary: {
                    50: '#eef7ff',
                    100: '#d9edff',
                    200: '#bce0ff',
                    300: '#8eccff',
                    400: '#59b0ff',
                    500: '#3b8def',
                    600: '#2570e3',
                    700: '#1e5bc7',
                    800: '#1e4ba3',
                    900: '#1e4081',
                    950: '#162951',
                },
            },
        },
    },
    plugins: [
        forms,
    ],
};
