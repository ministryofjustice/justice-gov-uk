import navigationMain from './navigation-main.html.twig';

export default {
    title: 'Components/Navigation - Main',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return navigationMain(args);
};

export const Default = Template.bind({});
Default.args = {
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
};

export const ActiveLink = Template.bind({});
ActiveLink.args = {
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
