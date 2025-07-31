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

export const InParagraph = Template.bind({});
InParagraph.args = {
    ...Default.args,
    newTab: true,
};
InParagraph.decorators = [
    (Story) => {
        return `<p>Lorem ipsum dolor sit ${Story()} amet, consectetur adipiscing elit</p>`;
    },
];
