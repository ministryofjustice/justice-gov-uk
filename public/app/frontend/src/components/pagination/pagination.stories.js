import pagination from './pagination.html.twig';

export default {
    title: 'Components/Pagination',
};

const Template = (args) => {
    return pagination(args);
};

export const Start = Template.bind({});
Start.args = {
    totalPages: 5,
    currentPage: 1,
};

export const Middle = Template.bind({});
Middle.args = {
    totalPages: 5,
    currentPage: 3,
};

export const End = Template.bind({});
End.args = {
    totalPages: 5,
    currentPage: 5,
};