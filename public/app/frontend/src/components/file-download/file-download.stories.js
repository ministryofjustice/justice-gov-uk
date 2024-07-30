import fileDownload from './file-download.html.twig';

export default {
    title: 'Components/File download',
    parameters: {
        layout: 'centered',
    },
    argTypes: {
        format: {
            options: ['PDF', 'ZIP'],
            control: { type: 'radio' },
        },
    },
};

const Template = (args) => {
    return fileDownload(args);
};

export const Default = Template.bind({});
Default.args = {
    format: 'PDF',
    link: '#',
    filesize: '167 KB',
    filename: 'CPR email address list',
};

export const Welsh = Template.bind({});
Welsh.args = {
    ...Default.args,
    language: 'Welsh',
};
