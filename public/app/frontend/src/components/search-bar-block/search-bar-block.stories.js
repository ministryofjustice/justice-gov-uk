import searchBarBlock from './search-bar-block.html.twig';

export default {
    title: 'Components/Search bar block',
};

const Template = (args) => {
    return searchBarBlock(args);
};

export const Default = Template.bind({});
Default.args = {
    results: 11,
    query: 'civil form',
    search: {
        id: 'search-bar-top',
        action: '#',
        background: 'dark',
        input: {
            labelHidden: true,
            label: 'Search the Justice UK website',
            id: 'search-bar-top-input',
        },
        button: {
            text: 'Search',
        },
    },
};

export const OneResult = Template.bind({});
OneResult.args = {
    ...Default.args,
    results: 1,
};

export const NoResults = Template.bind({});
NoResults.args = {
    ...Default.args,
    results: 0,
};

export const NoResultsDidYouMean = Template.bind({});
NoResultsDidYouMean.args = {
    ...Default.args,
    query: 'civil formes',
    didYouMean: 'civil forms',
    results: 0,
};

export const WithFilters = Template.bind({});
WithFilters.args = {
    ...Default.args,
    filters: [
        {
            url: '#',
            label: 'Relevance',
            active: true,
        },
        {
            url: '#',
            label: 'Most recent',
        },
    ],
};
