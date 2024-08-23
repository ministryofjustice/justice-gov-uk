import selectionInput from './selection-input.html.twig';

export default {
    title: 'Components/Selection input',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return selectionInput(args);
};

export const Default = Template.bind({});
Default.args = {
    direction: 'vertical',
    title: 'Section',
    checked: 'all',
    options: [
        {
            label: 'All',
            value: 'all',
        },
        {
            label: 'Courts',
            value: 'courts',
        },
        {
            label: 'News',
            value: 'news',
        },
        {
            label: 'Publications',
            value: 'publications',
        },
    ],
};

export const NonDefaultSelection = Template.bind({});
NonDefaultSelection.args = {
    ...Default.args,
    checked: 'news',
};

export const Inline = Template.bind({});
Inline.args = {
    ...Default.args,
    direction: 'horizontal',
};

export const WithHint = Template.bind({});
WithHint.args = {
    ...Default.args,
    hint: 'Select courts to search all Courts content',
};

export const Error = Template.bind({});
Error.args = {
    ...Default.args,
    error: true,
    errorText: 'Please select a valid value',
};

export const Disabled = Template.bind({});
Disabled.args = {
    ...Default.args,
    disabled: true,
};
