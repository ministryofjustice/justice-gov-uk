import navigationSections from './navigation-sections.html.twig';

export default {
    title: 'Components/Navigation - Sections',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return navigationSections(args);
};

// Quickly generate example links
const array = Array(10)
    .fill(0)
    .map((_, i) => 0 * 10 + 1 + i * 10);
const links = [];

array.forEach((val) => {
    links.push({
        url: '#',
        label: `${val}-${val + 10}`,
    });
});

export const Default = Template.bind({});
Default.args = {
    links: links,
};
