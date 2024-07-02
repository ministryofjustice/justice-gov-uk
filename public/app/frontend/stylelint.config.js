module.exports = {
    extends: ["stylelint-config-standard-scss", "stylelint-config-prettier-scss"],
    ignoreFiles: ["**/*.js", "**/*.jsx", "**/*.twig", "**/*.svg", "**/*.png", "**/*.jpeg", "**/*.gif", "**/*.mdx"],
    plugins: [
        "stylelint-selector-bem-pattern"
    ],
    rules: {
        "plugin/selector-bem-pattern": {
            preset: "bem"
        },
        "custom-property-pattern": "^([a-z][a-z0-9]*)(-[a-z0-9]+)*$"
    }
};
