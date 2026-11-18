import defaultTheme from 'tailwindcss/defaultTheme';

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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    950: '#050810',
                    900: '#0a0f1c',
                    800: '#0f1729',
                    700: '#17233b',
                    600: '#223152',
                    500: '#334670',
                },
            },
            boxShadow: {
                card: '0 1px 0 0 rgba(255,255,255,0.04) inset, 0 14px 34px -14px rgba(0,0,0,0.65)',
                glow: '0 0 0 1px rgba(52,211,153,0.28), 0 12px 40px -12px rgba(52,211,153,0.35)',
            },
            keyframes: {
                'fade-up': { '0%': { opacity: '0', transform: 'translateY(14px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                'fade-in': { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                'scale-in': { '0%': { opacity: '0', transform: 'scale(.94)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
                'slide-left': { '0%': { opacity: '0', transform: 'translateX(24px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                float: { '0%,100%': { transform: 'translate3d(0,0,0)' }, '50%': { transform: 'translate3d(0,-18px,0)' } },
                'pulse-ring': { '0%': { transform: 'scale(.8)', opacity: '.7' }, '100%': { transform: 'scale(2.2)', opacity: '0' } },
                shimmer: { '100%': { transform: 'translateX(100%)' } },
                'spin-slow': { to: { transform: 'rotate(360deg)' } },
                'gradient-pan': { '0%,100%': { backgroundPosition: '0% 50%' }, '50%': { backgroundPosition: '100% 50%' } },
                flow: { '0%': { backgroundPosition: '0 0' }, '100%': { backgroundPosition: '40px 0' } },
                shake: { '0%,100%': { transform: 'translateX(0)' }, '20%,60%': { transform: 'translateX(-3px)' }, '40%,80%': { transform: 'translateX(3px)' } },
                toast: { '0%': { transform: 'scaleX(1)' }, '100%': { transform: 'scaleX(0)' } },
            },
            animation: {
                'fade-up': 'fade-up .6s cubic-bezier(.22,1,.36,1) backwards',
                'fade-in': 'fade-in .5s ease-out backwards',
                'scale-in': 'scale-in .35s cubic-bezier(.22,1,.36,1) backwards',
                'slide-left': 'slide-left .45s cubic-bezier(.22,1,.36,1) backwards',
                float: 'float 9s ease-in-out infinite',
                'pulse-ring': 'pulse-ring 1.8s cubic-bezier(.2,.6,.4,1) infinite',
                'spin-slow': 'spin-slow 14s linear infinite',
                'gradient-pan': 'gradient-pan 8s ease infinite',
                flow: 'flow 1s linear infinite',
                shake: 'shake .5s ease-in-out infinite',
            },
        },
    },

    plugins: [],
};