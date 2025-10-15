import './legacy/scripts'

// GA4 - Enable the gtag.js API
window.dataLayer = window.dataLayer || []
window.gtag = () => dataLayer.push(arguments);

window.addEventListener("DOMContentLoaded", () => {
  // Track clicks on external links - i.e. in the header menu.
  [
    {
      externalUrl:
        "https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service",
      internalPath: document.location.origin + "/courts",
      pageTitle: "Courts",
    },
    {
      externalUrl:
        "https://www.gov.uk/government/organisations/hm-prison-and-probation-service",
      internalPath: "/offenders",
      pageTitle: "Offenders",
    },
  ].forEach((link) => {
    const anchor = document.querySelector(`a[href="${link.externalUrl}"]`);
    if (anchor) {
      anchor.addEventListener("click", () => {
        window.gtag?.("event", "page_view", {
          page_title: link.pageTitle,
          page_location: document.location.origin + link.internalPath,
        });
      });
    }
  });
});
