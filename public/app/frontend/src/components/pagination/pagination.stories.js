import pagination from './pagination.html.twig';

export default {
    title: 'Components/Pagination',
};

const Template = (args) => {
    return pagination(args);
};

export const Start = Template.bind({});
Start.args = {
    total: '5',
    next: '#',
    pages: [
        {
            title: 1,
            current: true,
            link: '#',
        },
        {
            title: 2,
            link: '#',
        },
        {
            title: 3,
            link: '#',
        },
        {
            title: 4,
            link: '#',
        },
        {
            title: 5,
            link: '#',
        },
    ],
};

export const Middle = Template.bind({});
Middle.args = {
    ...Start.args,
    previous: '#',
    pages: [
        {
            title: 1,
            link: '#',
        },
        {
            title: 2,
            link: '#',
        },
        {
            title: 3,
            current: true,
            link: '#',
        },
        {
            title: 4,
            link: '#',
        },
        {
            title: 5,
            link: '#',
        },
    ],
};

export const End = Template.bind({});
End.args = {
    previous: '#',
    pages: [
        {
            title: 1,
            link: '#',
        },
        {
            title: 2,
            link: '#',
        },
        {
            title: 3,
            link: '#',
        },
        {
            title: 4,
            link: '#',
        },
        {
            title: 5,
            link: '#',
            current: true,
        },
    ],
};

export const LotsOfResults = Template.bind({});
LotsOfResults.args = {
    next: '#',
    pages: [
        {
            title: 1,
            link: '#',
        },
        {
            title: 2,
            link: '#',
        },
        {
            title: 3,
            link: '#',
        },
        {
            title: 4,
            link: '#',
        },
        {
            title: '...',
        },
        {
            title: 12,
            link: '#',
        },
    ],
};
