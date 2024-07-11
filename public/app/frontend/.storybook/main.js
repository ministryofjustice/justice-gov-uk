import webpack from '../webpack.config.js';
import path from 'path';

const config = {
  stories: [
    // Make sure introduction is always the first page
    "../src/documentation/*.mdx",
    "../src/documentation/**/*.mdx",
    "../src/(components|layouts|examples|documentation)/**/*.stories.@(js|jsx|mjs|ts|tsx)"
  ],

  addons: [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@whitespace/storybook-addon-html",
    "@storybook/addon-a11y",
    "@storybook/addon-webpack5-compiler-swc"
  ],

  framework: {
    name: '@storybook/html-webpack5',
    options: {
      builder: {
        name: 'webpack5'
      }
    }
  },
  webpackFinal: async (config) => {
    config.resolve.alias['@components'] = webpack.resolve.alias['@components'];
    config.resolve.alias['@layouts'] = webpack.resolve.alias['@layouts'];
    config.plugins.push(...webpack.plugins);
    config.entry = {storybook: {import: config.entry, layer: 'storybook'}};
    config.experiments = {layers: true};
    return {
      ...config,
      module: { ...config.module, rules: [...config.module.rules, ...webpack.module.rules]},
    }
  },
  docs: {
    autodocs: false,
  }
};

export default config;
