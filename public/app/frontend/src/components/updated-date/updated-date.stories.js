import updatedDate from './updated-date.html.twig';

export default {
    title: 'Components/Updated date',
};

const Template = (args) => {
    return updatedDate(args);
};

export const Default = Template.bind({});
Default.args = {
    date: 'Monday, 30 January 2017',
};
