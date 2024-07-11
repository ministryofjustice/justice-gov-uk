import textInput from './text-input.html.twig';
import './index.js';

export default {
    title: 'Components/Text input',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return textInput(args);
};

export const Default = Template.bind({});
Default.args = {
    labelHidden: true,
    label: 'Name',
    id: 'name',
    placeholder: 'e.g. John Smith',
};

export const Label = Template.bind({});
Label.args = {
    ...Default.args,
    labelHidden: false,
    label: 'Name',
};

export const Error = Template.bind({});
Error.args = {
    ...Default.args,
    error: true,
};

export const Disabled = Template.bind({});
Disabled.args = {
    ...Default.args,
    disabled: true,
};
