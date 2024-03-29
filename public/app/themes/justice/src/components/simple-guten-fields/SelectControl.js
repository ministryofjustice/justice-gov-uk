import { select, withDispatch, withSelect } from "@wordpress/data";
import { SelectControl } from "@wordpress/components";

const SelectControlComponent = ({ field }) => {
  const { help, meta_key, options, label } = field;

  let SelectControlField = ({ value, handleSelectChange }) => (
    <SelectControl
      label={`Set ${label}`}
      value={select("core/editor").getEditedPostAttribute("meta")[meta_key]}
      onChange={(value) => handleSelectChange(value)}
      options={options}
      help={help}
    />
  );

  SelectControlField = withSelect((select) => {
    return {
      [meta_key]:
        select("core/editor").getEditedPostAttribute("meta")[meta_key],
    };
  })(SelectControlField);

  SelectControlField = withDispatch((dispatch) => {
    return {
      handleSelectChange: (value) => {
        dispatch("core/editor").editPost({ meta: { [meta_key]: value } });
      },
    };
  })(SelectControlField);
  return (
    <>
      <SelectControlField />
    </>
  );
};

export default SelectControlComponent;
