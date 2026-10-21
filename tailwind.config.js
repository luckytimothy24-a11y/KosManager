import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'from-primary-500', 'to-primary-700', 'from-warn-50', 'to-warn-100',
        'bg-emerald-50', 'bg-amber-50', 'bg-red-50', 'bg-primary-50',
        'text-emerald-600', 'text-amber-600', 'text-red-600', 'text-primary-600',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', '"Segoe UI"', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', '"Segoe UI"', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'xs': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.025em' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                'base': ['1rem', { lineHeight: '1.5rem' }],
                'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                'xl': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.01em' }],
                '2xl': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.02em' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem', letterSpacing: '-0.02em' }],
                '4xl': ['2.25rem', { lineHeight: '2.5rem', letterSpacing: '-0.03em' }],
                '5xl': ['3rem', { lineHeight: '1', letterSpacing: '-0.03em' }],
            },
            colors: {
                primary: {
                    50:  '#fdf8f5',
                    100: '#f0ebe5',
                    200: '#e0d5c9',
                    300: '#cdbba8',
                    400: '#b89c84',
                    500: '#a88468',
                    600: '#8d6d55',
                    700: '#6e5443',
                    800: '#4d3728',
                    900: '#3d2d22',
                    950: '#1f1510',
                },
                success: {
                    50:  '#f0faf4',
                    100: '#dbf3e4',
                    200: '#b9e5cc',
                    300: '#89d0a8',
                    400: '#55b47e',
                    500: '#339962',
                    600: '#247c4e',
                    700: '#1f6340',
                },
                warn: {
                    50:  '#fef9e7',
                    100: '#fdf0c7',
                    200: '#fae7a0',
                    300: '#f6d978',
                    400: '#f0c94a',
                    500: '#e6b83d',
                    600: '#cc952a',
                    700: '#a97124',
                },
                surface: {
                    light: '#f4f2ef',
                    card: '#faf8f5',
                    dark: '#0f172a',
                    elevated: '#1e293b',
                },
            },
            borderRadius: {
                '4xl': '2rem',
            },
            boxShadow: {
                'soft': '0 2px 8px rgba(141,109,85,0.10)',
                'card': '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06)',
                'card-hover': '0 8px 24px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04)',
            },
            transitionTimingFunction: {
                'bounce-out': 'cubic-bezier(0.22, 1, 0.36, 1)',
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },
    plugins: [forms],
};
