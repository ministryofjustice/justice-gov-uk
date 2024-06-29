// Documentation on theming Storybook: https://storybook.js.org/docs/configurations/theming/
import { create } from '@storybook/theming';
import logo from '../src/assets/moj-full-logo.png';

export default create({
  base: 'light',
  // Typography
  fontBase: '"Open Sans", sans-serif',
  fontCode: 'monospace',
  
  // Branding
  brandTitle: 'Justice UK',
  brandUrl: 'https://www.justice.gov.uk/',
  brandTarget: '_self',
  brandImage: logo,

  // Base
  colorSecondary: '#215591',

  // UI
  appBg: '#ffffff',
  appContentBg: '#ffffff',
  appPreviewBg: '#ffffff',

  // Text colors
  textColor: '#000000',
  textInverseColor: '#ffffff',

  // Toolbar default and active colors
  barTextColor: '#000000',
  barSelectedColor: '#215591',
  barHoverColor: '#215591',
});