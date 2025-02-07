import skipLink from './skip-link.html.twig';

export default {
    title: 'Components/Skip link',
};

const Template = (args) => {
    return skipLink(args);
};

export const Default = Template.bind({});
Default.args = {
    articleId: '#',
};