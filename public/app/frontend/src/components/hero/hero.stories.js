import hero from './hero.html.twig';

export default {
    title: 'Components/Hero',
};

const Template = (args) => {
    return hero(args);
};

export const Default = Template.bind({});
Default.args = {
    title: 'Family Procedure Rules',
};

export const WithEyebrow = Template.bind({});
WithEyebrow.args = {
    ...Default.args,
    eyebrowText: 'Procedure rules'
};

export const WithBreadcrumbs = Template.bind({});
WithBreadcrumbs.args = {
    ...Default.args,
    breadcrumbs: [
    {
        url: '#',
        label: 'Home',
    },
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
        label: 'Family',
    },
    {
        url: '#',
        label: 'Updates and zips',
    }
]
};

export const BreadcrumbsAndEyebrow = Template.bind({});
BreadcrumbsAndEyebrow.args = {
    ...WithEyebrow.args,
    breadcrumbs: [
        {
            url: '#',
            label: 'Home',
        },
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
            label: 'Family',
        },
        {
            url: '#',
            label: 'Updates and zips',
        }
    ]
};