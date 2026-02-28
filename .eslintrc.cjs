module.exports = {
  root: true,
  env: {
    browser: true,
    node: true,
  },
  extends: ["plugin:vue/essential", "eslint:recommended", "plugin:prettier/recommended"],
  parserOptions: {
    parser: "@babel/eslint-parser",
    requireConfigFile: false,
  },
  rules: {
    "vue/multi-word-component-names": "off",
    curly: "error",
    "default-case": "error",
    "default-param-last": "error",
    eqeqeq: "error",
    "no-else-return": "error",
    "no-lonely-if": "error",
    "no-var": "error",
    "require-await": "error",
  },
  globals: {
    $: "readonly",
  },
};