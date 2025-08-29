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

export const InParagraph = Template.bind({});
InParagraph.args = {
    ...Default.args,
};
InParagraph.decorators = [
    (Story) => {
        return `<p>
            The ${Story()} follows the ${Story()} and ${Story()} PD Updates, 
            which made provision to require represented claimants to use the DCP.
        </p>`;
    },
];

export const TextWrap = Template.bind({});
TextWrap.args = {
    ...Default.args,
    filename: 'Practice Direction 51L New Bill of Costs Pilot Excel version of precedent'
};
TextWrap.decorators = [
    (Story) => {
        return `<p style="max-width: 450px;">
            ${Story()}
        </p>`;
    },
];
