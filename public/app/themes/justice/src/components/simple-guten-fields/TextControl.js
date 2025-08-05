// @ts-check
import { TextControl } from "@wordpress/components";
import { withSelect, select, withDispatch } from "@wordpress/data";
import { createElement } from "@wordpress/element";
import DOMPurify from "dompurify";

const { sanitize } = DOMPurify;

/**
 * Sanitize the value of help, it could be of type string or null.
 *
 * @param {string|null} help - The help text to sanitize.
 * @returns {string|null|React.DOMElement} - Returns sanitized help text or null if not applicable.
 */
const getHelpSanitized = (help) => {
  // If help is not a string or is empty, return null.
  if (!help || typeof help !== "string" || help.trim() === "") {
    return null;
  }

  // Allow a subset of HTML tags and attributes in the help text.
  const helpSanitized = sanitize(help, {
    ALLOWED_TAGS: ["a", "br"],
    ALLOWED_ATTR: ["href", "target"],
  });

  // After sanitization, check if we have any HTML left.
  const isHtml =
    sanitize(helpSanitized, { ALLOWED_TAGS: [] }) !== helpSanitized;

  // If we have no html, and just a plain text, return it as is.
  if (!isHtml) {
    return helpSanitized;
  }

  // If we have HTML, return it as a span with dangerouslySetInnerHTML.
  return createElement("span", {
    dangerouslySetInnerHTML: { __html: helpSanitized },
  });
};

// @ts-ignore
const ControlField = withSelect((select, props) => {
  const { help, label, meta_key } = props.field;
  const { row_index, property_key } = props;
  const value = select("core/editor").getEditedPostAttribute("meta")[meta_key];

  if (typeof row_index === "undefined") {
    return {
      value,
      label: `Set ${label}`,
      help: getHelpSanitized(help),
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
    help: getHelpSanitized(help),
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
