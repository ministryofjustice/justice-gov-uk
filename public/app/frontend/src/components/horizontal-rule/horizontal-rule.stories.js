import horizontalRule from './horizontal-rule.html.twig';
export default {
    title: 'Components/Horizontal Rule',
};

const Template = (args) => {
    return horizontalRule(args);
};

export const Default = Template.bind({});
Default.args = {
    // No args by default
};

export const Decorative = Template.bind({});
Decorative.args = {
    decorative: true,
};

export const FullWidth = Template.bind({});
FullWidth.args = {
    fullWidth: true,
};
