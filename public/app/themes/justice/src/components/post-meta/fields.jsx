// @ts-check

const { ToggleControl, TextControl, PanelRow } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { PluginDocumentSettingPanel } = wp.editPost;

/**
 * @typedef {Object} PluginProps
 * @property {string} postType
 * @property {import('../../js/block-editor.d.ts').MetaFieldValues} postMeta
 * @property {Function} setPostMeta
 * @property {string} name
 * @property {import('../../js/block-editor.d.ts').MetaField[]} fields
 * @property {string} title
 */

/** 
 * @type {React.FC<PluginProps>}
 */
const AWP_Custom_Plugin = ({
  postType,
  postMeta,
  setPostMeta,
  title,
  fields,
  name
}) => {
  if ("page" !== postType) return null; // Will only render component for post type 'post'

  return (
    <PluginDocumentSettingPanel
      name={name}
      title={title}
      icon="generic"
    >
      {fields.map((field, index) => {
        if ("boolean" === field.settings.type) {
          return (
            <PanelRow key={index}>
              <ToggleControl
                label={field.label}
                onChange={(value) => setPostMeta({ [field.name]: value })}
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
 * @type {React.FC<import('../../js/block-editor.d.ts').MetaGroup>}
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-compose/
 */
const ComposedComponent = /** @type {any} */(compose(
  applyWithSelect,
  applyWithDispatch,
)(AWP_Custom_Plugin));

export default ComposedComponent;
