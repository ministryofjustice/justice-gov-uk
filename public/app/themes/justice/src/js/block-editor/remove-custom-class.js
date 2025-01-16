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
  // Blocks that are allowed to have custom class name support, e.g. the list block and the horizontal styling variant.
  const allowed = ['core/list'];

  if (!name.startsWith("core/") || settings.supports?.customClassName === false || allowed.includes(name)) {
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
