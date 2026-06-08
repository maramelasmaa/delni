import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/filament/**/*.blade.php',
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            white: '#FFFFFF',
            black: '#000000',
            primary: {
                50: '#FEF2E8',
                100: '#FDE5D0',
                200: '#FBCBA2',
                300: '#F8A965',
                400: '#F58043',
                500: '#F1620F',
                600: '#D9550C',
                700: '#B64608',
                800: '#933A07',
                900: '#7A2E05',
                950: '#431804',
            },
            navy: {
                50: '#F0F4F9',
                100: '#D9E2ED',
                200: '#B3C5DC',
                300: '#8DA8CA',
                400: '#678BB8',
                500: '#416EA6',
                600: '#1F4A7A',
                700: '#112240',
                750: '#0D1B2E',
                800: '#0B1A34',
                900: '#050D1A',
                950: '#02070F',
            },
            gray: {
                50: '#FCFBFB',
                100: '#F9F8F8',
                200: '#F3F4F6',
                300: '#E7E7E7',
                400: '#D1D5DB',
                500: '#9CA3AF',
                600: '#6B7280',
                700: '#374151',
                800: '#1F2937',
                900: '#0B1A34',
            },
            success: {
                500: '#22C55E',
                600: '#16A34A',
            },
            warning: {
                500: '#F59E0B',
                600: '#D97706',
            },
            danger: {
                500: '#EF4444',
                600: '#DC2626',
            },
        },
        fontFamily: {
            cairo: ['Cairo', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
            sans: ['Instrument Sans', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
            mono: defaultTheme.fontFamily.mono,
        },
        fontSize: {
            xs: ['12px', { lineHeight: '16px', letterSpacing: '-0.25px' }],
            sm: ['14px', { lineHeight: '20px', letterSpacing: '-0.25px' }],
            base: ['15px', { lineHeight: '24px', letterSpacing: '-0.2px' }],
            lg: ['17px', { lineHeight: '26px', letterSpacing: '-0.1px' }],
            xl: ['20px', { lineHeight: '28px', letterSpacing: '0px' }],
            '2xl': ['24px', { lineHeight: '32px', letterSpacing: '0px' }],
            '3xl': ['32px', { lineHeight: '40px', letterSpacing: '0px' }],
            '4xl': ['42px', { lineHeight: '52px', letterSpacing: '0px' }],
        },
        spacing: {
            0: '0',
            1: '0.25rem',
            2: '0.5rem',
            3: '0.75rem',
            4: '1rem',
            5: '1.25rem',
            6: '1.5rem',
            8: '2rem',
            10: '2.5rem',
            12: '3rem',
            16: '4rem',
            20: '5rem',
            24: '6rem',
            32: '8rem',
            40: '10rem',
            48: '12rem',
            56: '14rem',
            64: '16rem',
        },
        borderRadius: {
            none: '0',
            sm: '12px',
            md: '18px',
            lg: '28px',
            xl: '36px',
            full: '9999px',
        },
        boxShadow: {
            none: 'none',
            xs: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            sm: '0 4px 12px 0 rgba(11, 26, 52, 0.04)',
            base: '0 8px 24px 0 rgba(11, 26, 52, 0.07)',
            md: '0 12px 34px 0 rgba(11, 26, 52, 0.08)',
            lg: '0 20px 48px 0 rgba(11, 26, 52, 0.10)',
            xl: '0 24px 56px 0 rgba(11, 26, 52, 0.12)',
            nav: '0 12px 28px 0 rgba(11, 26, 52, 0.18)',
        },
        extend: {
            opacity: {
                8: '0.08',
                12: '0.12',
            },
            backdropBlur: {
                xs: '4px',
                sm: '8px',
                md: '12px',
                lg: '16px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        function ({ addComponents, theme, matchUtilities, e }) {
            addComponents({
                '.btn': {
                    '@apply px-6 py-3 rounded-md font-medium text-sm transition-all duration-200 inline-flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed': {},
                },
                '.btn-primary': {
                    '@apply bg-primary-500 text-white hover:bg-primary-600 active:bg-primary-700': {},
                },
                '.btn-outline': {
                    '@apply border border-gray-300 bg-white text-gray-900 hover:bg-gray-50 active:bg-gray-100': {},
                },
                '.btn-ghost': {
                    '@apply text-gray-700 hover:bg-gray-100 active:bg-gray-200': {},
                },
                '.card': {
                    '@apply bg-white rounded-md border border-gray-200 shadow-sm': {},
                },
                '.input': {
                    '@apply w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-base transition-colors duration-200 focus:outline-none focus:border-primary-500 focus:bg-white': {},
                },
            });

            // RTL/LTR modifiers
            matchUtilities(
                {
                    rtl: (value) => ({
                        "@supports (direction: rtl)": {
                            "[dir='rtl'] &": value,
                        },
                    }),
                    ltr: (value) => ({
                        "@supports (direction: ltr)": {
                            "[dir='ltr'] &": value,
                        },
                    }),
                },
                { values: { DEFAULT: {} } }
            );
        },
    ],
};
