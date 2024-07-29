import button from './button.html.twig';
import './index.js';

export default {
    title: 'Components/Button',
    parameters: {
        layout: 'centered',
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

export const Secondary = Template.bind({});
Secondary.args = {
    ...Primary.args,
    variant: 'secondary',
};

export const Input = Template.bind({});
Input.args = {
    ...Primary.args,
    type: 'input',
    inputType: 'submit',
};
