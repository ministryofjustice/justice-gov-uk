import sidebarBlock from './sidebar-block.html.twig';
import sidebarDecorator from '../../decorators/sidebar';

export default {
    title: 'Components/Sidebar block',
    argTypes: {
        variant: {
            options: ['brand', 'list', 'archive', 'search', 'search-filters', 'form'],
            control: { type: 'radio' },
        },
    },
    decorators: [(story) => sidebarDecorator(story)],
};

const Template = (args) => {
    return sidebarBlock(args);
};

export const Brand = Template.bind({});
Brand.args = {
    variant: 'brand',
};

export const List = Template.bind({});
List.args = {
    variant: 'list',
    title: 'Most popular',
    links: [
        {
            url: '#',
            label: 'Procedure rules',
        },
        {
            url: '#',
            label: 'Daily court lists',
        },
        {
            url: '#',
            label: 'Prison finder',
        },
        {
            url: '#',
            label: 'XHIBIT daily court status',
        },
        {
            url: '#',
            label: 'Prison service instructions (PSIs)',
        },
        {
            url: '#',
            label: 'Probation instructions',
        },
    ],
};

export const RelatedPages = Template.bind({});
RelatedPages.args = {
    variant: 'list',
    title: 'Related pages',
    links: [
        {
            url: '#',
            format: 'PDF',
            filesize: '167 KB',
            label: 'CPR email address list',
        },
        {
            url: '#',
            format: 'PDF',
            filesize: '100 KB',
            label: 'CPR email address list',
            language: 'Welsh',
        },
    ],
};

export const ArchivedPages = Template.bind({});
ArchivedPages.args = {
    variant: 'archive',
    title: 'Archived pages',
    links: [
        {
            url: '#',
            label: 'Procedure rules',
            newTab: true,
        },
    ],
};

export const Search = Template.bind({});
Search.args = {
    variant: 'form',
    title: 'Search this collection',
    description: 'Search standard directions',
    form: {
        id: 'search-bar-sidebar',
        action: '#',
        placeholder: 'e.g. Witness Statements',
        input: {
            labelHidden: true,
            label: 'Search the standard directions content',
            id: 'search-bar-sidebar-input',
        },
        button: {
            text: 'Search',
        },
    },
};

export const SearchFilters = Template.bind({});
SearchFilters.args = {
    variant: 'search-filter',
    title: 'Filter',
    subtitle: 'Filter results by',
    submitText: 'Apply filter',
    fields: [
    {
        title: 'Section',
        type: 'radio',
        default: 'all',
        direction: 'vertical',
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
    },
    {
        title: 'Organisation',
        type: 'radio',
        default: 'all',
        direction: 'vertical',
        options: [
            {
                label: 'All',
                value: 'all',
            }
        ],
    },
    {
        title: 'Audience',
        type: 'radio',
        default: 'all',
        direction: 'vertical',
        options: [
            {
                label: 'All',
                value: 'all',
            },
            {
                label: 'Academic researcher',
                value: 'academic-researcher',
            },
            {
                label: 'Legal profession',
                value: 'legal-profession',
            },
            {
                label: 'Mediator/advice worker',
                value: 'mediator-advice-worker',
            },
            {
                label: 'Public/citizen',
                value: 'public-citizen',
            },
            {
                label: 'Representing myself at court',
                value: 'self-representation',
            }
        ],
    },
    {
        title: 'Type',
        type: 'radio',
        default: 'all',
        direction: 'vertical',
        options: [
            {
                label: 'All',
                value: 'all',
            },
            {
                label: 'Guidance',
                value: 'guidance',
            },
            {
                label: 'Report',
                value: 'report',
            }
        ],
    },
    {
        type: 'checkbox',
        options: [
            {
                label: 'Limit your search to web pages only',
                value: 'web-pages-only',
                },
            ]}
],
};

export const EmailAlerts = Template.bind({});
EmailAlerts.args = {
    variant: 'form',
    title: 'Get email alerts',
    description: 'Enter your email address',
    form: {
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
    },
};
