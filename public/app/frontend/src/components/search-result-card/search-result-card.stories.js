import searchResultCard from './search-result-card.html.twig';

export default {
    title: 'Components/Search result card',
};

const Template = (args) => {
    return searchResultCard(args);
};

export const Default = Template.bind({});
Default.args = {
    title: 'Civil - Civil Procedure Rules',
    url: 'https://www.justice.gov.uk/courts/procedure-rules/civil',
    date: '26 February 2019',
    description:
        '…testing a new bill of costs, Precedent AA, to reflect the costs management and costs budgeting procedures is introduced. Practice Direction 51L New Bill of Costs Pilot Excel version of…',
};

export const FileDownload = Template.bind({});
FileDownload.args = {
    title: 'Civil Procedure Rules - Update 7',
    url: 'https://www.justice.gov.uk/courts/procedure-rules/civil/documents/cpr_update7_pdf.zip',
    date: '16 May 2024',
    isDocument: true,
    format: 'PDF',
    filesize: '167 KB',
};
