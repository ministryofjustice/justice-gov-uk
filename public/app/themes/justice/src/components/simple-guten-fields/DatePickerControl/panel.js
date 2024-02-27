/**
 * WordPress dependencies
 */
import { Button, Dropdown } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { useState, useMemo } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";

/**
 * Internal dependencies
 */

import PostPanelRow from "../PostPanelRow";
import DatePickerForm from "./index";
import { getPostScheduleLabel, getFullPostScheduleLabel } from "./label";

export default function PostSchedulePanel({
  field: { label, meta_key, placeholder },
  row_index,
  property_key,
  isChild,
  onChange,
}) {
  if (isChild) {
    console.warn("PostSchedulePanel is not supported inside a repeater.");
    return null;
  }

  const value = useSelect(
    (select) => select("core/editor").getEditedPostAttribute("meta")[meta_key],
  );

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

  const buttonLabel = getPostScheduleLabel(value);
  const buttonLabelFull = getFullPostScheduleLabel(value);

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
            onClick={onToggle}
            aria-label={sprintf(
              // translators: %s: Current post date.
              __("Change date: %s"),
              buttonLabel,
            )}
            label={buttonLabelFull}
            showTooltip={label !== buttonLabelFull}
            aria-expanded={isOpen}
            style={{
              whiteSpace: "normal",
              textAlign: "left",
              height: "auto",
            }}
          >
            {buttonLabel}
          </Button>
        )}
        renderContent={({ onClose }) => (
          <DatePickerForm
            field={{ label, meta_key, placeholder }}
            row_index={row_index}
            property_key={property_key}
            value={value}
            label={label}
            isChild={isChild}
            onChange={onChange}
            onClose={onClose}
          />
        )}
      />
    </PostPanelRow>
  );
}
