import { URLInput } from "@wordpress/block-editor";
import { withSelect, select, withDispatch } from "@wordpress/data";
import { Button, Dropdown } from "@wordpress/components";
import { useState, useMemo } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import PostPanelRow from "../PostPanelRow";
import { keyboardReturn, arrowLeft } from "@wordpress/icons";

const MyTextControl = (props) => {
  const { label, value, onChange } = props;

  const [tempValue, setTempValue] = useState(value);

  const [popoverAnchor, setPopoverAnchor] = useState(null);
  // Memoize popoverProps to avoid returning a new object every time.
  const popoverProps = useMemo(
    () => ({
      // Anchor the popover to the middle of the entire row so that it doesn't
      // move around when the label changes.
      anchor: popoverAnchor,
      "aria-label": __("Change publish date"),
      placement: "bottom-end",
    }),
    [popoverAnchor],
  );

  const buttonLabel = value ? value.replace(/http(s)?:\/\//, "") : "Set page";
  const buttonLabelFull = value;

  return (
    <PostPanelRow label={label} ref={setPopoverAnchor}>
      <Dropdown
        popoverProps={popoverProps}
        focusOnMount
        className="editor-post-schedule__panel-dropdown"
        contentClassName="editor-post-schedule__dialog"
        renderToggle={({ onToggle, isOpen }) => (
          <Button
            __next40pxDefaultSize
            className="editor-post-schedule__dialog-toggle"
            variant="tertiary"
            onClick={() => {
              setTempValue({url: value});
              onToggle();
            }}
            aria-label={sprintf(
              // translators: %s: Current post date.
              __("Change page: %s"),
              buttonLabel,
            )}
            label={buttonLabelFull}
            showTooltip={label !== buttonLabelFull}
            aria-expanded={isOpen}
            style={{
              whiteSpace: "normal",
              textAlign: "left",
              height: "auto",
              wordBreak: "break-word",
            }}
          >
            {buttonLabel}
          </Button>
        )}
        renderContent={({ onClose }) => (
          <form
            className="block-editor-url-input__button-modal"
            onSubmit={() => {
              onChange(tempValue.url);
              onClose();
            }}
          >
            <div className="block-editor-url-input__button-modal-line">
              <Button
                className="block-editor-url-input__back"
                icon={arrowLeft}
                label={__("Close")}
                onClick={onClose}
              />
              <URLInput
                __nextHasNoMarginBottom
                value={tempValue.url || ""}
                onChange={(url, post) => {
                  console.log({url, post});
                  setTempValue ({url, post})
                }}
              />
              <Button
                icon={keyboardReturn}
                label={__("Submit")}
                type="submit"
              />
            </div>
          </form>
        )}
      />
    </PostPanelRow>
  );
};

// @ts-ignore
const ControlField = withSelect((select, props) => {
  const { label, meta_key } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];
  const key = meta_key + row_index + property_key;

  if (typeof row_index === "undefined") {
    return { value, key, label: `Set ${label}` };
  }

  const defaultValue = props.field.default || "";

  return {
    value:
      typeof value[row_index][property_key] !== "undefined"
        ? value[row_index][property_key]
        : defaultValue,
    key,
    label: `Set ${property_key.replace("_", " ")}`,
  };
})(MyTextControl);

// @ts-ignore
export default withDispatch((dispatch, props) => {
  const { meta_key } = props.field;
  const { row_index, property_key } = props;

  return {
    onChange: (value) => {
      let newValue = value;

      console.log("in onchange", newValue);

      if (typeof row_index !== "undefined") {
        let repeaterValues =
          select("core/editor").getEditedPostAttribute("meta")?.[meta_key];
        newValue = repeaterValues.map((row, innerIndex) => {
          return innerIndex === row_index
            ? { ...row, [property_key]: value }
            : row;
        });
      }

      console.log("in onchange", newValue);

      dispatch("core/editor").editPost({ meta: { [meta_key]: newValue } });
    },
  };
})(ControlField);
