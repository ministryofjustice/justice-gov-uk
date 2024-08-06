import navigationSecondary from './navigation-secondary.html.twig';

export default {
    title: 'Components/Navigation - Secondary',
};

const Template = (args) => {
    return navigationSecondary(args);
};

export const Default = Template.bind({});
Default.args = {
    articleId: '#',
    title: 'Justice UK',
    links: [
        {
            url: '#',
            label: 'Courts',
        },
        {
            url: '#',
            label: 'Procedure rules',
            children: [
                {
                    url: '#',
                    label: 'Civil',
                    children: [
                        {
                            url: '#',
                            label: 'Rules & Practice Directions',
                        },
                        {
                            url: '#',
                            label: 'Standard directions',
                        },
                    ],
                },
                {
                    url: '#',
                    label: 'Family',
                    children: [
                        {
                            url: '#',
                            label: 'Foreword and summary of the rules',
                        },
                        {
                            url: '#',
                            label: 'Rules and practice directions',
                        },
                    ],
                },
                {
                    url: '#',
                    label: "Other procedure rules for magistrates' courts and the Crown Court",
                },
                {
                    url: '#',
                    label: 'Criminal',
                },
            ],
        },
        {
            url: '#',
            label: 'Offenders',
        },
    ],
};

export const ActiveTopLevelLink = Template.bind({});
ActiveTopLevelLink.args = {
    articleId: Default.args.articleId,
    title: Default.args.title,
    links: Default.args.links.map((link) => {
        if (link.label === 'Courts') {
            return {
                url: link.url,
                label: link.label,
                active: true,
            };
        }
        return link;
    }),
};

export const ActiveTopLevelButton = Template.bind({});
ActiveTopLevelButton.args = {
    articleId: Default.args.articleId,
    title: Default.args.title,
    links: Default.args.links.map((link) => {
        if (link.label === 'Procedure rules') {
            return {
                url: link.url,
                label: link.label,
                active: true,
                children: link.children,
            };
        }
        return link;
    }),
};
