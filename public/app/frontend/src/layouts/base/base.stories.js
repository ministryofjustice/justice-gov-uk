import base from './base.html.twig';
import './index.js';

// !dev hides the story from the sidebar - this story is only used for the example pages so doesn't need to appear
export default {
    title: 'Layouts/Base',
    parameters: {
        layout: 'fullscreen',
    },
    tags: ['!dev']
};

const Template = (args) => {
    return base(args);
};

export const Default = Template.bind({});
Default.args = {};

