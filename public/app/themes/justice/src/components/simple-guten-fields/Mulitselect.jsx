import MultiSelect from "react-select";
import { BaseControl } from "@wordpress/components";
import { withSelect, select, withDispatch } from "@wordpress/data";

const isRepeater = (rowIndex) => {
  return typeof rowIndex !== "undefined";
};

const WithHelp = (props) => (
  <BaseControl
    key={props._key}
    id={props._key}
    help={props.help}
    className={props.classes}
    __nextHasNoMarginBottom
  >
    <MultiSelect key={props._key} {...props} />
  </BaseControl>
);

let ControlField = withSelect((select, props) => {
  const { help, label, meta_key, options, isMulti } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];
  const key = meta_key + row_index + property_key;
  let isMultiProp = isMulti ?? true;

  let defaultValue = [];
  // Setting labels by the options array

  if (!isRepeater(row_index)) {
    // If not inside repeater label is value, value is the array
    defaultValue = [];
    defaultValue = Array.isArray(value)
      ? value.map((arrayItem) => {
          let isOption = options.find((option) => option.value == arrayItem);
          let label = value;
          if (typeof isOption === "object" && isOption !== null) {
            label = isOption.label;
          }
          return { value: arrayItem, label: label };
        })
      : [];
    return {
      isMulti: isMultiProp,
      placeholder: label,
      defaultValue,
      _key: key,
      options,
      label: `Set ${label}`,
      help,
    };
  } else {
    // Inside repeater we fetching the value by row index
    defaultValue =
      value[row_index][property_key] &&
      Array.isArray(value[row_index][property_key])
        ? value[row_index][property_key].map((option) => {
            let labelOption = options.find(
              (propOption) => propOption.value == option,
            );
            let label = labelOption ? labelOption.label : option;
            return { value: option, label: label, help };
          })
        : [];
    return {
      placeholder: label,
      isMulti: isMultiProp,
      defaultValue,
      _key: key,
      options,
      label: `Set ${label}`,
    };
  }
})(WithHelp);

ControlField = withDispatch((dispatch, props) => {
  const { meta_key } = props.field;
  const { row_index, property_key } = props;

  return {
    onChange: (value) => {
      let flatArray = [];
      if (Array.isArray(value)) {
        flatArray = value.map((option) => option.value);
      } else {
        // When is multi false we saving the value in array of 1 item to beep the data type array
        flatArray = [value.value];
      }

      let newValue = flatArray;
      // In repeater fields we setting the value on the parent meta value before update
      if (isRepeater(row_index)) {
        let repeaterValues =
          select("core/editor").getEditedPostAttribute("meta")?.[meta_key];
        newValue = repeaterValues.map((row, innerIndex) => {
          return innerIndex === row_index
            ? { ...row, [property_key]: newValue }
            : row;
        });
      }
      dispatch("core/editor").editPost({ meta: { [meta_key]: newValue } });
    },
  };
})(ControlField);

export default ControlField;
