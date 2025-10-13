import "./legacy/scripts";

// GA4 - Enable the gtag.js API
window.dataLayer = window.dataLayer || [];
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

/**
 * This script is used to handle post previews in WordPress.
 *
 * It's the equivalent of the inline script that WordPress adds to the head for post previews.
 * But, WordPress's inline script is blocked by our Content Security Policy (CSP),
 * and replicating it this way is more secure.
 *
 * See wp-includes/functions.php wp_post_preview_js
 */
(function () {
  if (!document.documentElement.dataset.previewPostId) {
    return;
  }

  const query = document.location.search;

  if (query && query.indexOf("preview=true") !== -1) {
    window.name =
      "wp-preview-" + document.documentElement.dataset.previewPostId;
  }

  if (window.addEventListener) {
    window.addEventListener("pagehide", function () {
      window.name = "";
    });
  }
})();
