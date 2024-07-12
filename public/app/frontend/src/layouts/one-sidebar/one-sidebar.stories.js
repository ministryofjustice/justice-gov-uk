import oneSidebar from './one-sidebar.html.twig';
import './index.js';

export default {
    title: 'Layouts/One sidebar',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return oneSidebar(args);
};

export const Right = Template.bind({});
Right.args = {
    direction: 'right',
};

export const Left = Template.bind({});
Left.args = {
    direction: 'left',
};
