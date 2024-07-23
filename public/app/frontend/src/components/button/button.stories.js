import button from './button.html.twig';

export default {
    title: 'Components/Button',
    parameters: {
        layout: 'centered',
    },
    argTypes: {
        variant: {
            options: ['primary', 'dark', 'light'],
            control: { type: 'radio' },
        },
    },
};

const Template = (args) => {
    return button(args);
};

export const Primary = Template.bind({});
Primary.args = {
    buttonText: 'Search',
    variant: 'primary',
};

export const Dark = Template.bind({});
Dark.args = {
    ...Primary.args,
    variant: 'dark',
};

export const Light = Template.bind({});
Light.args = {
    ...Primary.args,
    variant: 'light',
};

export const Input = Template.bind({});
Input.args = {
    ...Primary.args,
    type: 'input',
    inputType: 'submit',
};
