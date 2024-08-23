import searchPage from './search-page.html.twig';

export default {
    title: 'Example pages/Search page',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return searchPage(args);
};

export const Default = Template.bind({});
Default.args = {};
