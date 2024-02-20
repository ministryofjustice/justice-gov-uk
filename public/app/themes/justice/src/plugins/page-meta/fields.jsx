const { ToggleControl, TextControl, PanelRow } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { PluginDocumentSettingPanel } = wp.editPost;

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
      initialOpen="false"
    >
      {fields.map((field, index) => {
        if ("boolean" === field.type) {
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
        if ("text" === field.type) {
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

export default compose([
  withSelect((select, props) => {
    return {
      postMeta: select("core/editor").getEditedPostAttribute("meta"),
      postType: select("core/editor").getCurrentPostType(),
      ...props,
    };
  }),
  withDispatch((dispatch) => {
    return {
      setPostMeta(newMeta) {
        dispatch("core/editor").editPost({ meta: newMeta });
      },
    };
  }),
])(AWP_Custom_Plugin);
