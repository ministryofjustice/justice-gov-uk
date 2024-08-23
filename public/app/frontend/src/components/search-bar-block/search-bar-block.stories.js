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
   }
};

export const OneResult = Template.bind({});
OneResult.args = {
    ...Default.args,
    results: 1,
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
