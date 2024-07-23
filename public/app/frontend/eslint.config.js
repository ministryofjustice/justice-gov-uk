const js= require("@eslint/js");
const eslintConfigPrettier= require("eslint-config-prettier");
const globals = require("globals");

module.exports = [
  js.configs.recommended,
  eslintConfigPrettier,
  {
    languageOptions: {
      globals: {
        ...globals.browser,
      },
    },
    rules: {
      "no-console": "error",
    }
  }
];
