import help from './help.html.twig';

export default {
    title: 'Example pages/Help',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return help(args);
};

export const Default = Template.bind({});
Default.args = {};
