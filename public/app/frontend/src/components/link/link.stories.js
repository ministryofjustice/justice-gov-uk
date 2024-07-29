import link from './link.html.twig';
import './index.js';

export default {
    title: 'Components/Link',
};

const Template = (args) => {
    return link(args);
};

export const Default = Template.bind({});
Default.args = {
    label: 'Find out more',
    url: '#',
};

export const NewTab = Template.bind({});
NewTab.args = {
    ...Default.args,
    newTab: true,
};
