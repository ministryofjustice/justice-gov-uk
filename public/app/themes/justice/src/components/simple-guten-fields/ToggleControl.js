import { ToggleControl } from "@wordpress/components";
import { select, withDispatch, withSelect } from "@wordpress/data";

const ControlField = withSelect((select, props) => {
  const { help, label, meta_key } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];

  return {
    checked: typeof row_index === "undefined" ? value : value[row_index][property_key],
    label,
    help,
    __nextHasNoMarginBottom: true,
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
