// @ts-check

import { addFilter } from "@wordpress/hooks";

/**
 * Removes support for custom class name on core blocks.
 *
 * @param {Object} settings
 * @param {string} name
 * @returns {Object}
 */

const removeCustomClassNameSupport = (settings, name) => {
  if (
    !name.startsWith("core/") ||
    settings.supports?.customClassName === false
  ) {
    return settings;
  }

  return {
    ...settings,
    supports: {
      ...settings.supports,
      customClassName: false,
    },
  };
};

addFilter(
  "blocks.registerBlockType",
  "moj/remove-custom-class",
  removeCustomClassNameSupport,
);
