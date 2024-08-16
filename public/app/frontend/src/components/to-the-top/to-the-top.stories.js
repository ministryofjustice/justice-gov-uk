import toTheTop from './to-the-top.html.twig';

export default {
    title: 'Components/To the top',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return toTheTop(args);
};

export const Default = Template.bind({});
Default.args = {
    label: 'To the top',
    url: '#',
};
