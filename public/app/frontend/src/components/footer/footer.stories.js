import footer from './footer.html.twig';
export default {
    title: 'Components/Footer',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return footer(args);
};

export const Default = Template.bind({});
Default.args = {
    links: [
        {
            url: '#',
            label: 'Accessibility',
        },
        {
            url: '#',
            label: 'Cookies',
        },
        {
            url: '#',
            label: 'Contacts',
        },
        {
            url: '#',
            label: 'Copyright',
        },
        {
            url: '#',
            label: 'Help',
        },
        {
            url: '#',
            label: 'Privacy',
        },
        {
            url: '#',
            label: 'Webchats',
        },
        {
            url: '#',
            label: 'Website queries',
        },
    ],
};
