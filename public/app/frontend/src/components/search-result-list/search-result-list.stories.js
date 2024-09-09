import searchResultList from './search-result-list.html.twig';

export default {
    title: 'Components/Search result list',
};

const Template = (args) => {
    return searchResultList(args);
};

export const Default = Template.bind({});
Default.args = {
    cards: [
        {
            title: 'Civil - Civil Procedure Rules',
            url: 'https://www.justice.gov.uk/courts/procedure-rules/civil',
            date: '26 February 2019',
            description:
                '…testing a new bill of costs, Precedent AA, to reflect the costs management and costs budgeting procedures is introduced. Practice Direction 51L New Bill of Costs Pilot Excel version of…',
        },
        {
            title: 'Family Procedure Rules',
            url: 'https://www.justice.gov.uk/?p=7983',
            date: '24 May 2024',
            description:
                '…is being tested across five regions, at eight Family Court locations. The amendment extends the testing sites to Central London, Liverpool and Reading Family Courts in order to increase the…',
        },
        {
            title: 'Family Procedure Rules',
            url: 'https://www.justice.gov.uk/courts/procedure-rules/family',
            date: '5 February 2018',
            description:
                '…is being tested across five regions, at eight Family Court locations. The amendment extends the testing sites to Central London, Liverpool and Reading Family Courts in order to increase the…',
        },
    ],
};
