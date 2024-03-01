import { URLInput } from "@wordpress/block-editor";
import { Button, Dropdown } from "@wordpress/components";
import { withSelect, select, withDispatch } from "@wordpress/data";
import { useState, useMemo } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { keyboardReturn, arrowLeft, trash } from "@wordpress/icons";

import PostPanelRow from "../PostPanelRow";

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

  const buttonLabels = {
    short: "Set Page",
    full: "Set Page",
  };

  if (value?.url) {
    buttonLabels.short = value.url.replace(/http(s)?:\/\//, "");
    buttonLabels.full = value.url;
  }
  if (value?.post?.title) {
    buttonLabels.short = value.post.title;
    buttonLabels.full = value.post.title;
  }

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
              setTempValue(value);
              onToggle();
            }}
            aria-label={sprintf(
              // translators: %s: Current post date.
              __("Change page: %s"),
              buttonLabels.short,
            )}
            label={buttonLabels.full}
            showTooltip={buttonLabels.short !== buttonLabels.full}
            aria-expanded={isOpen}
            style={{
              whiteSpace: "normal",
              textAlign: "left",
              height: "auto",
              wordBreak: "break-word",
            }}
          >
            {buttonLabels.short}
          </Button>
        )}
        renderContent={({ onClose }) => (
          <form
            className="block-editor-url-input__button-modal"
            onSubmit={() => {
              onChange(tempValue);
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
              <Button
                className="block-editor-url-input__trash"
                icon={trash}
                label={__("Remove link")}
                onClick={() => {
                  onChange(null);
                  onClose();
                }}
              />
              <URLInput
                __nextHasNoMarginBottom
                value={tempValue?.url || tempValue?.post?.title || ""}
                onChange={(url, post) => {
                  setTempValue({ url, post });
                }}
                required={false}
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
      let newValue = value ? {
        ...(value?.post && { post: value.post }),
        ...(value?.url && {url: value.url})
      } : null;

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
