import template from './template.html.twig';
import './index.js';

export default {
    title: 'Components/Template',
};

const Template = (args) => {
    return template(args);
};

export const Default = Template.bind({});
Default.args = {
    templateVariable: 'This is an example of a variable',
}
