import searchBar from './search-bar.html.twig';
import './index.js';

export default {
    title: 'Components/Search bar',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return searchBar(args);
};

export const Default = Template.bind({});
Default.args = {
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
};
