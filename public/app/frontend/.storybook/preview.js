import '../src/index';

// TODO: add agreed screensizes for Viewport button

const preview = {
  parameters: {
    options: {
      storySort: {
        method: 'alphabetical',
        order: [
          'Documentation',
          'Development', ['Setup', 'Build a new component', 'Build a new layout', 'Connect a component to Wordpress'],
          'Design', ['Typography', 'Colours', 'Grid', 'Icons'], 
          'Quality assurance', ['Testing'], 
          'Components', 
          'Layouts', 
          'Example pages',
        ],
        locales: 'en-GB',
      }
    }
  },
};

export default preview;
