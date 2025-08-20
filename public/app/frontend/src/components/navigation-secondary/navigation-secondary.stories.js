import navigationSecondary from './navigation-secondary.html.twig';
import sidebarDecorator from '../../decorators/sidebar';

export default {
    title: 'Components/Navigation - Secondary',
    decorators: [(story) => sidebarDecorator(story)],
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
            expanded: true,
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
                    expanded: true,
                    children: [
                        {
                            url: '#',
                            label: 'Foreword and summary of the rules',
                        },
                        {
                            url: '#',
                            label: 'Rules and practice directions',
                            expanded: true,
                            children: [
                                {
                                    url: '#',
                                    label: 'Part 1 – General rules and definitions',
                                },
                                {
                                    url: '#',
                                    label: 'Part 2 – The court and its powers',
                                },
                                {
                                    url: '#',
                                    label: 'Part 3 – Case management',
                                    expanded: true,
                                    children: [
                                        {
                                            url: '#',
                                            label: 'Part 3A – Case management powers of the court',
                                        },
                                        {
                                            url: '#',
                                            label: 'Part 3B – Case management powers of the court in family proceedings',
                                            expanded: true,
                                            children: [
                                                {
                                                    url: '#',
                                                    label: 'Part 3B.1 – Case management powers of the court in family proceedings',
                                                    active: true,
                                                },
                                                {
                                                    url: '#',
                                                    label: 'Part 3B.2 – Case management powers of the court in family proceedings (children)',
                                                    expanded: true,
                                                },
                                            ]
                                        },
                                    ],
                                },
                            ],
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
