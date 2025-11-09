import js from '@eslint/js';
import jsdoc from 'eslint-plugin-jsdoc';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.js'],
        plugins: {
            jsdoc,
        },
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                // Browser globals
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                setTimeout: 'readonly',
                setInterval: 'readonly',
                clearTimeout: 'readonly',
                clearInterval: 'readonly',
                requestAnimationFrame: 'readonly',
                fetch: 'readonly',
                alert: 'readonly',
                URLSearchParams: 'readonly',

                // Third-party libraries
                Swal: 'readonly',
                axios: 'readonly',
                $: 'readonly',
                jQuery: 'readonly',
                Alpine: 'readonly',
            },
        },
        rules: {
            // Error Prevention
            'no-console': ['warn', { allow: ['warn', 'error', 'info'] }],
            'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            'no-undef': 'error',
            'no-var': 'error',
            'prefer-const': 'error',

            // Code Quality
            eqeqeq: ['error', 'always'],
            curly: ['error', 'all'],
            'brace-style': ['error', '1tbs'],
            'comma-dangle': ['error', 'only-multiline'],
            quotes: ['error', 'single', { avoidEscape: true }],
            semi: ['error', 'always'],
            indent: ['error', 4, { SwitchCase: 1 }],
            'max-len': [
                'warn',
                {
                    code: 120,
                    ignoreStrings: true,
                    ignoreTemplateLiterals: true,
                },
            ],

            // Best Practices
            'no-eval': 'error',
            'no-implied-eval': 'error',
            'no-with': 'error',
            'no-alert': 'warn',
            'no-param-reassign': 'warn',
            'prefer-arrow-callback': 'warn',
            'prefer-template': 'warn',
            'object-shorthand': ['warn', 'always'],

            // JSDoc Rules
            'jsdoc/check-alignment': 'warn',
            'jsdoc/check-param-names': 'error',
            'jsdoc/check-tag-names': 'error',
            'jsdoc/check-types': 'error',
            'jsdoc/require-description': 'warn',
            'jsdoc/require-param': 'error',
            'jsdoc/require-param-description': 'warn',
            'jsdoc/require-param-name': 'error',
            'jsdoc/require-param-type': 'error',
            'jsdoc/require-returns': 'error',
            'jsdoc/require-returns-description': 'warn',
            'jsdoc/require-returns-type': 'error',
            'jsdoc/valid-types': 'error',
        },
    },
    {
        ignores: ['node_modules/**', 'public/build/**', 'vendor/**', '**/*.min.js'],
    },
];
