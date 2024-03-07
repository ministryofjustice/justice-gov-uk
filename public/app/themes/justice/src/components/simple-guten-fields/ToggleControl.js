import { ToggleControl } from "@wordpress/components";
import { select, withDispatch, withSelect } from "@wordpress/data";

const ControlField = withSelect((select, props) => {
  const { help, label, meta_key } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];
  const key = meta_key + row_index + property_key;

  if (typeof row_index === "undefined") {
    return { checked: value, key, label, help };
  }

  return {
    checked: value[row_index][property_key],
    key,
    label,
    help,
  };
})(ToggleControl);

export default withDispatch((dispatch, props) => {
  const { meta_key } = props.field;
  const { row_index, property_key } = props;

  return {
    onChange: (value) => {
      let newValue = value;

      if (typeof row_index !== "undefined") {
        let repeaterValues =
          select("core/editor").getEditedPostAttribute("meta")?.[meta_key];
        newValue = repeaterValues.map((row, innerIndex) => {
          return innerIndex === row_index
            ? { ...row, [property_key]: value }
            : row;
        });
      }
      dispatch("core/editor").editPost({ meta: { [meta_key]: newValue } });
    },
  };
})(ControlField);
