import homepage from './homepage.html.twig';

export default {
    title: 'Example pages/Homepage',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return homepage(args);
};

export const Default = Template.bind({});
Default.args = {};
