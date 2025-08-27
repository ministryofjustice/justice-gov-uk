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
            id: 'courts',
            url: '#',
            label: 'Courts',
        },
        {
            id: 'procedure-rules',
            url: '#',
            label: 'Procedure rules',
            children: [
                {
                    id: 'procedure-rules-civil',
                    url: '#',
                    label: 'Civil',
                    children: [
                        {
                            id: 'procedure-rules-civil-rules-and-practice-directions',
                            url: '#',
                            label: 'Rules & Practice Directions',
                        },
                        {
                            id: 'procedure-rules-civil-standard-directions',
                            url: '#',
                            label: 'Standard directions',
                        },
                    ],
                },
                {
                    id: 'procedure-rules-family',
                    url: '#',
                    label: 'Family',
                    children: [
                        {
                            id: 'procedure-rules-family-foreword-and-summary',
                            url: '#',
                            label: 'Foreword and summary of the rules',
                        },
                        {
                            id: 'procedure-rules-family-rules-and-practice-directions',
                            url: '#',
                            label: 'Rules and practice directions',
                            children: [
                                {
                                    id: 'procedure-rules-family-general-rules-and-definitions',
                                    url: '#',
                                    label: 'Part 1 – General rules and definitions',
                                },
                                {
                                    id: 'procedure-rules-family-court-and-its-powers',
                                    url: '#',
                                    label: 'Part 2 – The court and its powers',
                                },
                                {
                                    id: 'procedure-rules-family-case-management',
                                    url: '#',
                                    label: 'Part 3 – Case management',
                                    children: [
                                        {
                                            id: 'procedure-rules-family-case-management-powers-of-the-court',
                                            url: '#',
                                            label: 'Part 3A – Case management powers of the court',
                                        },
                                    ],
                                },
                            ],
                        },
                    ],
                },
                {
                    id: 'other-procedure-rules',
                    url: '#',
                    label: "Other procedure rules for magistrates' courts and the Crown Court",
                },
                {
                    id: 'procedure-rules-criminal',
                    url: '#',
                    label: 'Criminal',
                },
            ],
        },
        {
            id: 'offenders',
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
            return {...link, active: true};
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
           return {...link, active: true, expanded: true };
        }
        return link;
    }),
};

export const ActiveNestedButton = Template.bind({});
ActiveNestedButton.args = {
    articleId: Default.args.articleId,
    title: Default.args.title,
    links: () => {
        // Clone Default.args.links so that the original is not modified.
        const linksWithNestedActive = JSON.parse(JSON.stringify(Default.args.links));

        // Set the expanded and active states for the nested links.
        linksWithNestedActive[1].expanded = true;
        linksWithNestedActive[1].children[1].expanded = true;
        linksWithNestedActive[1].children[1].children[1].expanded = true;
        linksWithNestedActive[1].children[1].children[1].children[2].expanded = true;
        linksWithNestedActive[1].children[1].children[1].children[2].children[0].active = true;

        return linksWithNestedActive;
    },
};
