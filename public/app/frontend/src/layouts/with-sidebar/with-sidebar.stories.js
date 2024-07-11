import withSidebar from './with-sidebar.html.twig';
import './index.js';

export default {
    title: 'Layouts/With sidebar',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return withSidebar(args);
};

export const Right = Template.bind({});
Right.args = {
    direction: 'right',
};

export const Left = Template.bind({});
Left.args = {
    direction: 'left',
};
