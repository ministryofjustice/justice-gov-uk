import template from './template.html.twig';

// Don't test this as it's an example for developers and isn't available in the Storybook UI
export default {
    title: 'Components/Template',
    tags: ['no-tests'] // If you've copied this file to build a new component, remove this line
};

const Template = (args) => {
    return template(args);
};

export const Default = Template.bind({});
Default.args = {
    templateVariable: 'This is an example of a variable',
};
