import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Palet Warna "Eco-Soft"
                brand: {
                    50: '#ecfdf5',  // Background sangat muda (Mint)
                    100: '#d1fae5', // Highlight
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981', // Primary Brand Color (Emerald)
                    600: '#059669', // Hover state
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
                // Warna netral yang "hangat" (tidak kaku seperti gray standar)
                slate: {
                    50: '#f8fafc', // Background Aplikasi Utama
                    100: '#f1f5f9',
                    200: '#e2e8f0', // Border
                    800: '#1e293b', // Text Utama
                    900: '#0f172a',
                }
            },
            boxShadow: {
                'soft': '0 4px 6px -1px rgba(16, 185, 129, 0.1), 0 2px 4px -1px rgba(16, 185, 129, 0.06)', // Bayangan kehijauan halus
            }
        },
    },

    plugins: [forms],
};