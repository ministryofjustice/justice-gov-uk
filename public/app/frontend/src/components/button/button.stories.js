import button from './button.html.twig';
import './index.js';

export default {
    title: 'Components/Button',
};

const Template = (args) => {
    return button(args);
};

export const Primary = Template.bind({});
Primary.args = {
    buttonText: 'Search',
    style: 'primary',
}

export const Secondary = Template.bind({});
Secondary.args = {
    ...Primary.args,
    style: 'secondary'
}

export const Input = Template.bind({});
Input.args = {
    ...Primary.args,
    type: 'input',
    inputType: 'submit'
}
