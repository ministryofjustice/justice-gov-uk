import textInputForm from './text-input-form.html.twig';

export default {
    title: 'Components/Text input form',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return textInputForm(args);
};

export const Search = Template.bind({});
Search.args = {
    id: 'search-bar-top',
    action: '#',
    background: 'dark',
    input: {
        labelHidden: true,
        label: 'Search the Justice UK website',
        id: 'search-bar-top-input',
    },
    button: {
        text: 'Search',
    },
    hiddenInputs: [{}],
};

export const EmailAlerts = Template.bind({});
EmailAlerts.args = {
    id: 'email-alerts-sidebar',
    action: '#',
    input: {
        labelHidden: true,
        label: 'Enter your email address',
        id: 'email-alerts-sidebar-input',
    },
    button: {
        text: 'Subscribe',
    },
};
