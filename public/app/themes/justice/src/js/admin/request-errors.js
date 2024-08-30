// npm
import { HttpClient as HttpClientIntegration } from "@sentry/integrations";
// Local
import { roundMins, stringToHash, comboDebounce } from "../utils";

/**
 * Add the HttpClient integration to Sentry in order to track failed client-side requests.
 *
 * @see https://docs.sentry.io/platforms/javascript/configuration/integrations/httpclient
 */

Sentry.addIntegration(
  new HttpClientIntegration({
    failedRequestStatusCodes: [403],
  }),
);

// Assign window.fetch - It's important to do this *after* HttpClientIntegration has been added to Sentry.
const { fetch: originalFetch } = window;

/**
 * Add an event processor to Sentry.
 *
 * Use the processor to alert the user when an error occurred on a client-side (XHR/ajax/fetch) request.
 * A self-init script has been used to keep variables scoped.
 *
 * @see https://docs.sentry.io/platforms/javascript/enriching-events/event-processors/
 */

(() => {
  let errorCount = 0;
  let errorCountAlerted = 0;
  const message =
    "Something went wrong. The error has been sent to support. Error count: ";

  const debouncedAlert = comboDebounce(() => {
    if (errorCountAlerted === errorCount) {
      return;
    }
    errorCountAlerted = errorCount;
    alert(message + errorCount);
  }, 2000);

  Sentry.addEventProcessor(function (event, hint) {
    // If the event has a status code and it's in > 400.
    if (
      event.contexts?.response?.status_code &&
      event.contexts.response.status_code >= 400
    ) {
      errorCount++;
      debouncedAlert();
    }

    return event;
  });
})();

/**
 * A function to generate an OpenSearch url for reviewing the logs related to a request.
 * 
 * @param {string} requestUri
 * @returns {string}
 */

const getModsecLogUrl = (requestUri) => {
  const time = {
    from: roundMins(new Date(), "down").toISOString(),
    to: roundMins(new Date(), "up").toISOString(),
  };

  const query = `${encodeURIComponent(
    '"transaction.request.uri"',
  )}:${encodeURIComponent(` "${requestUri}"`)}`;

  const logUrlString =
    `https://logs.cloud-platform.service.justice.gov.uk/_dashboards/app/discover#/` +
    `?_g=(filters:!(),refreshInterval:(pause:!t,value:0),time:(from:'${time.from}',to:'${time.to}'))` +
    `&_a=(columns:!(transaction.request.uri,transaction.time_stamp,transaction.messages,log),` +
    `filters:!(),index:b95d8900-dd15-11ed-87c8-170407f57c9c,interval:auto,` +
    `query:(language:kuery,query:'${query}'),sort:!())`;

  return logUrlString;
};

/**
 * Intercept fetch requests and handle errors.
 *
 * Fetch requests are used by the WordPress block editor.
 * If one of these request returns a 403 (for e.g.) then WP does not handle it.
 *
 * A 403 is likely a modsec false positive, so send an event to Sentry
 * and signpost to the full log in OpenSearch.
 *
 * @see https://blog.logrocket.com/intercepting-javascript-fetch-api-requests-responses/
 *
 * @param {RequestInfo | URL} input
 * @param {?RequestInit} init
 * @returns {Promise<Response>}
 */

window.fetch = async (...args) => {
  let [resource, config] = args;

  const response = await originalFetch(resource, config);

  if (response.status === 200) {
    return response;
  }

  // Parse the request url.
  const requestUrlObject = new URL(resource);
  const requestUri = `${requestUrlObject.pathname}${requestUrlObject.search}`;

  // Generate a unique hash base on the request uri and it's body.
  // Appending this to the Sentry event message prevents similar issues from being grouped.
  const requestHash = stringToHash(JSON.stringify(config.body) + requestUri);

  // Compose a message for the Sentry issue.
  const sentryMessage = `Failed client-side fetch request. Request hash ${requestHash}.`;

  // Set the url as context for the Sentry event.
  Sentry.setContext("cloud_platform_log", {
    url: getModsecLogUrl(requestUri),
  });

  Sentry.captureEvent({
    message: sentryMessage,
  });

  // response interceptor here
  return response;
};
