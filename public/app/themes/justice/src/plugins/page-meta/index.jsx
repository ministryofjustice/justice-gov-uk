const { __ } = wp.i18n;
const { registerPlugin } = wp.plugins;

import { metaFields, panelFields } from "./constants";
import AWP_Custom_Plugin from "./awp-custom-postmeta-fields";

/**
 * The following code registers a plugin(s) to add custom meta fields to the page editor.
 * @see https://awhitepixel.com/how-to-add-post-meta-fields-to-gutenberg-document-sidebar/
 */

registerPlugin("moj-justice-postmeta-plugin", {
  render() {
    return (
      <AWP_Custom_Plugin
        name="meta"
        title={__("Meta", "justice")}
        fields={metaFields}
      />
    );
  },
});

registerPlugin("moj-justice-postmeta-plugin-2", {
  render() {
    return (
      <AWP_Custom_Plugin
        name="panels"
        title={__("Panels", "justice")}
        fields={panelFields}
      />
    );
  },
});
