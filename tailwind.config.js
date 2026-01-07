import defaultTheme from 'tailwindcss/defaultTheme';

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
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
