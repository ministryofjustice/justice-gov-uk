window.mojLocalizedDataEntries = [
  { id: "ccfw-script-js-extra", loaded: false, vars: ["ccfwData"] },
  { id: "wp-sentry-browser-js-extra", loaded: false, vars: ["wp_sentry"] },
];

/**
 * A script to load localized data for scripts that use wp_localize_script().
 *
 * This script get's the JSON data for the Sentry script and assigns it to
 * the global window.wp_sentry variable.
 *
 * This script can be tested by visiting a frontend page, with Sentry enabled,
 * and checking the browser console for the window.wp_sentry variable.
 *
 * We can see if Sentry initializes correctly by checking for that window.wp_sentry
 * has properties added by initialization, e.g. window.wp_sentry.integrations
 *
 * @returns {void}
 */
window["mojLoadLocalizedData"] = function () {
  for (const entry of mojLocalizedDataEntries) {
    if (entry.loaded) {
      continue;
    }

    const script = document.querySelector(
      `script#${entry.id}[type="application/json"]`,
    );

    if (!script) {
      continue;
    }

    try {
      const object = JSON.parse(script.textContent);
      for (const [key, value] of Object.entries(object)) {
        if (entry.vars && !entry.vars.includes(key)) {
          continue;
        }
        window[key] = value;
      }
      entry.loaded = true;
    } catch (e) {
      console.error(
        `Error parsing localization JSON for 'wp-sentry-browser'`,
        e,
      );
    }
  }
};
