import link from './link.html.twig';

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

export const NewTabVisuallyHidden = Template.bind({});
NewTabVisuallyHidden.args = {
    ...Default.args,
    newTab: true,
    newTabVisuallyHidden: true,
};
