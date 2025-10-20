import { __experimentalInspectorPopoverHeader as InspectorPopoverHeader } from "@wordpress/block-editor";
import { DateTimePicker } from "@wordpress/components";
import { dispatch } from "@wordpress/data";
import { format, getSettings } from "@wordpress/date";
import { forwardRef, useCallback } from "@wordpress/element";

/**
 * Internal dependencies
 */

const PostSchedule = (
  {
    onClose,
    field: { meta_key },
    row_index,
    property_key,
    value,
    onChange,
    label,
  },
  ref,
) => {
  const onChangeHandler = useCallback(
    (value) => {
      const formattedValue = value ? format("Y-m-d H:i:s", value) : null;

      if (onChange) {
        onChange(formattedValue, property_key, row_index);

        return;
      }

      dispatch("core/editor").editPost({
        meta: { [meta_key]: formattedValue },
      });
    },
    [property_key, row_index, meta_key, onChange, dispatch],
  );

  const settings = getSettings();

  // To know if the current timezone is a 12 hour time with look for "a" in the time format
  // We also make sure this a is not escaped by a "/"
  const is12HourTime = /a(?!\\)/i.test(
    settings.formats.time
      .toLowerCase() // Test only the lower case a.
      .replace(/\\\\/g, "") // Replace "//" with empty strings.
      .split("")
      .reverse()
      .join(""), // Reverse the string and test for "a" not followed by a slash.
  );

  return (
    <div ref={ref}>
      <InspectorPopoverHeader
        title={label}
        actions={[
          {
            label: "Unset",
            onClick: () => onChangeHandler?.(null),
          },
        ]}
        onClose={onClose}
      />
      <DateTimePicker
        currentDate={value}
        onChange={onChangeHandler}
        is12Hour={is12HourTime}
        onClose={onClose}
      />
    </div>
  );
};

export default forwardRef(PostSchedule);
