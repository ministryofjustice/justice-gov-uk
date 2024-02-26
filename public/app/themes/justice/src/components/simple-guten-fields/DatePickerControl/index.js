/**
 * External dependencies
 */
import { format } from "@wordpress/date";

/**
 * WordPress dependencies
 */
import { getSettings } from "@wordpress/date";
import { dispatch, useDispatch, useSelect } from "@wordpress/data";
import { DateTimePicker } from "@wordpress/components";
import { useState, useCallback, useMemo } from "@wordpress/element";
import { store as coreStore } from "@wordpress/core-data";

/**
 * Internal dependencies
 */

export default function PostSchedule({
  onClose,
  field: { meta_key },
  row_index,
  property_key,
  values,
  isChild,
  onChange,
}) {
  const value = isChild
    ? values
    : useSelect(
        (select) =>
          select("core/editor").getEditedPostAttribute("meta")[meta_key],
      );

  const onChangeHandler = useCallback(
    (value) => {
      const formattedValue = format("Y-m-d H:i:s", value);

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
    <DateTimePicker
      currentDate={value}
      onChange={onChangeHandler}
      is12Hour={is12HourTime}
      onClose={onClose}
    />
  );
}
