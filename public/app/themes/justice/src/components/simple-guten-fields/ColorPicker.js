import { withSelect, select, withDispatch } from "@wordpress/data";
import { BaseControl, ColorPicker } from "@wordpress/components";

import { withState } from "@wordpress/compose";

const ColorPickerComponent = ({ field }) => {
  const { meta_key, label } = field;

  let FieldControl = withState({
    showPicker: false,
  })(({ showPicker, setState, handleValueChange }) => {
    let color = select("core/editor").getEditedPostAttribute("meta")[meta_key];
    return (
      <div style={{ margin: "20px" }}>
        <div
          onClick={() => {
            setState({ showPicker: !showPicker });
          }}
          style={{ display: "flex" }}
        >
          <button>Pick Color for {label}</button>
          <div
            style={{ height: "22px", width: "200px", backgroundColor: color }}
          ></div>
        </div>
        {showPicker && (
          <ColorPicker
            color={color}
            onChangeComplete={(value) => {
              handleValueChange(value);
            }}
          />
        )}
        <button onClick={() => handleValueChange({ hex: "" })}>
          Remove Color
        </button>
      </div>
    );
  });

  FieldControl = withSelect((select) => {
    return {
      [meta_key]:
        select("core/editor").getEditedPostAttribute("meta")[meta_key],
    };
  })(FieldControl);

  FieldControl = withDispatch((dispatch) => {
    return {
      handleValueChange: (value) => {
        dispatch("core/editor").editPost({ meta: { [meta_key]: value.hex } });
      },
    };
  })(FieldControl);

  return (
    <>
      <FieldControl />
    </>
  );
};
export default ColorPickerComponent;
