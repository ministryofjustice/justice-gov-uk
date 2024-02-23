// @ts-check

import {registerPlugin} from '@wordpress/plugins';

import PostMetaPlugin from "./plugin";

/**
 * The following code registers a plugin(s) to add custom meta fields to the page editor.
 * This setup was based on:
 * @see https://awhitepixel.com/how-to-add-post-meta-fields-to-gutenberg-document-sidebar/
 * This example has repeater and select fields:
 * @see https://bebroide.medium.com/how-to-easily-develop-with-react-your-own-custom-fields-within-gutenberg-wordpress-editor-b868c1e193a9
 */

justiceBlockEditorLocalized.forEach(plugin => {
  registerPlugin(`moj-justice-block-editor-meta-${plugin.name}`, {
    icon: null,
    render() {
      return (<PostMetaPlugin
        name={plugin.name}
        title={plugin.title}
        fields={plugin.fields}
      />)
    },
  });
});
