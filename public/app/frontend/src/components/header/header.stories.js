import header from './header.html.twig';
import './index.js';

export default {
    title: 'Components/Header',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return header(args);
};

export const Default = Template.bind({});
Default.args = {
    showLogo: true,
    showSearch: true,
    links: [
        {
            url: '#',
            label: 'Courts',
        },
        {
            url: '#',
            label: 'Procedure rules',
        },
        {
            url: '#',
            label: 'Offenders',
        },
    ],
    search: {
        id: 'search-bar-header',
        action: '#',
        input: {
            labelHidden: true,
            label: 'Search the Justice UK website',
            id: 'search-bar-header-input',
        },
        button: {
            text: 'Search',
        },
    },
};

export const ActiveLink = Template.bind({});
ActiveLink.args = {
    ...Default.args,
    links: [
        {
            url: '#',
            label: 'Courts',
        },
        {
            active: true,
            url: '#',
            label: 'Procedure rules',
        },
        {
            url: '#',
            label: 'Offenders',
        },
    ],
};
