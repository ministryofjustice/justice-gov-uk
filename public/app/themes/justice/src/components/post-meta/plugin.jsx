// @ts-check

import { ToggleControl, TextControl, PanelRow } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';

/**
 * @typedef {Object} PluginProps
 * @property {string} postType
 * @property {import('../../js/block-editor.js').MetaFieldValues} postMeta
 * @property {Function} setPostMeta
 * @property {string} name
 * @property {import('../../js/block-editor.js').MetaField[]} fields
 * @property {string} title
 */

/** 
 * @type {React.FC<PluginProps>}
 */
const PostMetaPlugin = ({
  postType,
  postMeta,
  setPostMeta,
  title,
  fields,
  name
}) => {
  if ("page" !== postType) return null; // Will only render component for post type 'page'

  return (
    <PluginDocumentSettingPanel
      name={name}
      title={title}
      icon="admin-generic"
    >
      {fields.map((field, index) => {
        if ("boolean" === field.settings.type) {
          return (
            <PanelRow key={index}>
              <ToggleControl
                label={field.label}
                onChange={(value) => setPostMeta({ [field.name]: value })}
                // @ts-ignore
                checked={postMeta[field.name]}
              />
            </PanelRow>
          );
        }
        if ("string" === field.settings.type) {
          return (
            <PanelRow key={index}>
              <TextControl
                label={field.label}
                // @ts-ignore
                value={postMeta[field.name]}
                onChange={(value) => setPostMeta({ [field.name]: value })}
              />
            </PanelRow>
          );
        }
      })}
    </PluginDocumentSettingPanel>
  );
};


const applyWithSelect = withSelect((select, props) => {
  return {
    postMeta: select("core/editor").getEditedPostAttribute("meta"),
    postType: select("core/editor").getCurrentPostType(),
    ...props,
  };
});

const applyWithDispatch = withDispatch((dispatch) => {
  return {
    setPostMeta(newMeta) {
      dispatch("core/editor").editPost({ meta: newMeta });
    },
  };
});


/** 
 * A react functional component, composed with withSelect and withDispatch.
 * Because of the way the compose function works, the props are not inferred correctly.
 * This is why the type casting to 'any' is necessary.
 * @type {React.FC<import('../../js/block-editor.js').MetaGroup>}
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-compose/
 */
const ComposedComponent = /** @type {any} */(compose(
  applyWithSelect,
  applyWithDispatch,
)(PostMetaPlugin));

export default ComposedComponent;
