const ERROR = 'error'
const WARN = 'warn'
const OFF = 'off'

const PRODUCTIVE_ENV = process.env.NODE_ENV === 'production'

module.exports = {
  // https://eslint.org/docs/user-guide/configuring#configuration-cascading-and-hierarchy
  // This option interrupts the configuration hierarchy at this file
  // Remove this if you have a higher level ESLint config file (it usually happens into a mono-repos)
  root: true,

  parserOptions: {
    ecmaVersion: '2022', // Allows for the parsing of modern ECMAScript features
  },

  env: {
    node: true,
    browser: true,
    'vue/setup-compiler-macros': true
  },

  // Rules order is important, please avoid shuffling them
  extends: [
    // Base ESLint recommended rules
    'eslint:recommended',

    // Uncomment any of the lines below to choose desired strictness,
    // but leave only one uncommented!
    // See https://eslint.vuejs.org/rules/#available-rules
    'plugin:vue/vue3-essential', // Priority A: Essential (Error Prevention)
    'plugin:vue/vue3-strongly-recommended', // Priority B: Strongly Recommended (Improving Readability)
    'plugin:vue/vue3-recommended', // Priority C: Recommended (Minimizing Arbitrary Choices and Cognitive Overhead)

    'standard'
  ],

  plugins: [
    // https://eslint.vuejs.org/user-guide/#why-doesn-t-it-work-on-vue-files
    // required to lint *.vue files
    'vue',
  ],

  globals: {
    ga: 'readonly', // Google Analytics
    cordova: 'readonly',
    __statics: 'readonly',
    __QUASAR_SSR__: 'readonly',
    __QUASAR_SSR_SERVER__: 'readonly',
    __QUASAR_SSR_CLIENT__: 'readonly',
    __QUASAR_SSR_PWA__: 'readonly',
    process: 'readonly',
    Capacitor: 'readonly',
    chrome: 'readonly',
    Nova: 'readonly',
    _: 'readonly'
  },

  // add your custom rules here
  rules: {
    //// Quasar defaults
    // allow async-await
    'generator-star-spacing': 'off',
    // allow paren-less arrow functions
    'arrow-parens': 'off',
    'one-var': 'off',
    'no-void': 'off',
    'multiline-ternary': 'off',

    'import/first': 'off',
    'import/named': 'error',
    'import/namespace': 'error',
    'import/default': 'error',
    'import/export': 'error',
    'import/extensions': 'off',
    'import/no-unresolved': 'off',
    'import/no-extraneous-dependencies': 'off',

    'prefer-promise-reject-errors': 'off',

    // allow debugger during development only
    'no-debugger': PRODUCTIVE_ENV ? 'error' : 'off',

    //// Custom settings
    'no-shadow': ERROR,
    'no-console': PRODUCTIVE_ENV ? ERROR : WARN,
    'operator-linebreak': [ERROR, 'before'],
    'comma-dangle': [ERROR, {
      'arrays': 'always-multiline',
      'objects': 'always-multiline',
      'imports': 'always-multiline',
      'exports': 'always-multiline',
      'functions': 'never'
    }],
    'sort-imports': [ERROR, {
      "ignoreCase": true,
      "ignoreDeclarationSort": true,
      "ignoreMemberSort": false,
      "memberSyntaxSortOrder": ["none", "all", "multiple", "single"],
      "allowSeparatedGroups": true
    }],
    'object-curly-newline': [ERROR, {
      'ObjectExpression': {
        'multiline': true,
        'minProperties': 3,
        'consistent': true,
      },
      'ObjectPattern': {
        'multiline': true,
        'minProperties': 3,
        'consistent': true,
      },
      'ImportDeclaration': 'always',
      'ExportDeclaration': {
        'multiline': true,
        'minProperties': 3
      }
    }],
    'vue/component-name-in-template-casing': [ERROR, 'kebab-case'],
    'vue/match-component-file-name': [ERROR, {
      'extensions': ['jsx', 'vue'],
      'shouldMatchCase': false
    }],
    'vue/attributes-order': [ERROR, {
      "alphabetical": true
    }]
  }
}
