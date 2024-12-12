import '../src/index';
import React from "react";

// TODO: add agreed screensizes for Viewport button

const preview = {
  parameters: {
    options: {
      storySort: {
        method: 'alphabetical',
        order: [
          'Documentation',
          'Development', ['Setup', 'Build a new component', 'Build a new layout', 'Connect a component to Wordpress'],
          'Design', ['Typography', 'Colours', 'Grid', 'Spacing', 'Breakpoints', 'Icons'],
          'Quality assurance', ['General', 'Compatibility', 'Accessibility', 'Performance'],
          'Components', 
          'Layouts', 
          'Example pages',
        ],
        locales: 'en-GB',
      }
    },
    docs: {
      components: {
        // Override lists just for the Storybook theme (we reset them in base.scss)
        ol: ({children, ...args}) => React.createElement('ol', {style: {'list-style-type': 'roman'}, ...args}, children),
        ul: ({children, ...args}) => React.createElement('ul', {style: {'list-style-type': 'disc'}, ...args}, children),
        // Override blockquotes just for Storybook (used for the 'review-block' Storybook component, see 'utils/documentation/review-block.js')
        blockquote: ({children, ...args}) => React.createElement('blockquote', {style: {'margin-top': '5rem'}, ...args}, children)
      }
    }
  },
};

export default preview;
