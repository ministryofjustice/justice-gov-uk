import contentPage from './content-page.html.twig';

export default {
    title: 'Example pages/Content page',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return contentPage(args);
};

export const Default = Template.bind({});
Default.args = {};
