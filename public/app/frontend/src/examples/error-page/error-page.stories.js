import errorPage from './error-page.html.twig';

export default {
    title: 'Example pages/Error pages',
    parameters: {
        layout: 'fullscreen',
    },
};

const Template = (args) => {
    return errorPage(args);
};

export const Error401 = Template.bind({});
Error401.args = {
    title: 'Access to the requested resource has been denied',
    errorCode: '401',
    errorMessage: 'Authorisation Required',
    content: `
      <h2>Troubleshooting</h2>
      <p>If you feel that you have reached this page in error or believe that you should have access to this resource
        please contact the editor team <a href="mailto:web.comments@justice.gov.uk">web.comments@justice.gov.uk</a>
      </p>
    `,
};

export const Error403 = Template.bind({});
Error403.args = {
    title: 'Access to the requested resource has been denied',
    errorCode: '403',
    errorMessage: 'Forbidden',
    content: `
      <h2>Troubleshooting</h2>
      <p>If you feel that you have reached this page in error or believe that you should have access to this resource
        please contact the editor team <a href="mailto:web.comments@justice.gov.uk">web.comments@justice.gov.uk</a>
      </p>
    `,
};

export const Error404 = Template.bind({});
Error404.args = {
    title: 'Page not found',
    errorCode: '404',
    errorMessage: 'Page not found',
    content: `
        <h2>Try:</h2>
        <ul>
            <li>Checking that there are no typos in the page address.</li>
            <li>You can also use the <a href="#">search</a> or <a href="#">browse from the homepage</a> to find the information you need.</li>
            <li>If you've reached this page by clicking on a link or file, it is likely that the item has been moved or deleted. Contact the editor team to let them know they've got a broken link, <a href="#">web.comments@justice.gov.uk</a> and see if they can help you find what you were looking for.</li>
            <li>Retry your search using alternative words in case the document or page has been moved or renamed.</li>
        </ul>
    `,
};

export const Error500 = Template.bind({});
Error500.args = {
    title: 'Cannot connect to server',
    errorCode: '500',
    errorMessage: 'Server connection error',
    content: `
          <p>There is a problem connecting to the server. You could try to refresh your page several times.</p>
          <p>If the problem persist please be patient, we are aware of the issue.</p>
    `,
};
