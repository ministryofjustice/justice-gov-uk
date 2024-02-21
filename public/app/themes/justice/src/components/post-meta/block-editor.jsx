// @ts-check

const { registerPlugin } = wp.plugins;

import AWP_Custom_Plugin from "./fields";

/**
 * The following code registers a plugin(s) to add custom meta fields to the page editor.
 * @see https://awhitepixel.com/how-to-add-post-meta-fields-to-gutenberg-document-sidebar/
 */


justiceBlockEditorLocalized.forEach(plugin => {
  registerPlugin(`moj-justice-block-editor-meta-${plugin.name}`, {
    render() {
      return (<AWP_Custom_Plugin
        name={plugin.name}
        title={plugin.title}
        fields={plugin.fields}
      />)
    },
  });
});
