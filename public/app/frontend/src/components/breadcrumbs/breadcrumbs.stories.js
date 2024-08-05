import breadcrumbs from './breadcrumbs.html.twig';

export default {
    title: 'Components/Breadcrumbs',
};

const Template = (args) => {
    return breadcrumbs(args);
};

export const Default = Template.bind({});
Default.args = {
    links: [
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
