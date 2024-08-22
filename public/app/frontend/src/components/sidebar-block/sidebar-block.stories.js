import sidebarBlock from './sidebar-block.html.twig';
import sidebarDecorator from '../../decorators/sidebar';

export default {
    title: 'Components/Sidebar block',
    argTypes: {
        variant: {
            options: ['list', 'archive', 'search'],
            control: { type: 'radio' },
        },
    },
    decorators: [(story) => sidebarDecorator(story)],
};

const Template = (args) => {
    return sidebarBlock(args);
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
    variant: 'search',
    title: 'Search this collection',
    description: 'Search standard directions',
    search: {
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
