// @ts-check
import { TextControl } from "@wordpress/components";
import { withSelect, select, withDispatch } from "@wordpress/data";

// @ts-ignore
const ControlField = withSelect((select, props) => {
  const { help, label, meta_key } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];

  if (typeof row_index === "undefined") {
    return {
      value,
      label: `Set ${label}`,
      help,
      __next40pxDefaultSize: true,
      __nextHasNoMarginBottom: true,
    };
  }

  const defaultValue = props.field.default || "";

  return {
    value:
      typeof value[row_index][property_key] !== "undefined"
        ? value[row_index][property_key]
        : defaultValue,
    label: `Set ${property_key.replace("_", " ")}`,
    help,
    __next40pxDefaultSize: true,
    __nextHasNoMarginBottom: true,
  };
})(TextControl);

// @ts-ignore
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
