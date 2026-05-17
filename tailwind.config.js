// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                void: {
                    black:   '#0a0a0a',
                    dark:    '#111111',
                    darker:  '#0d0d0d',
                    card:    '#1a1a1a',
                    border:  '#2a2a2a',
                    muted:   '#3a3a3a',
                    gray:    '#888888',
                    light:   '#cccccc',
                    white:   '#f5f5f5',
                    accent:  '#ffffff',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            },
            animation: {
                'fade-in':    'fadeIn 0.3s ease-in-out',
                'slide-up':   'slideUp 0.4s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn:  { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            },
        },
    },
    plugins: [],
}