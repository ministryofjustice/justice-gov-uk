import twoSidebars from './two-sidebars.html.twig';
import './index.js';

export default {
    title: 'Layouts/Two sidebars',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return twoSidebars(args);
};

export const Default = Template.bind({});
Default.args = {};
