// @ts-check
/// <reference path="./block-editor.d.ts" />

import { addFilter } from "@wordpress/hooks";

import "../components/core-list/block-editor";
import "../components/core-rich-text/block-editor";
import "../components/inline-menu/block-editor";
import "../components/search/block-editor";
import "../components/simple-definition-list-blocks/block-editor";
import "../components/simple-guten-fields/index";
import "../components/to-the-top/block-editor";

/**
 * Removes support for custom class name on core blocks.
 *
 * @param {Object} settings
 * @param {string} name
 * @returns {Object}
 */

const addListBlockClassName = (settings, name) => {
  if (
    !name.startsWith("core/") ||
    settings.supports?.customClassName === false
  ) {
    return settings;
  }

  console.log(settings);

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
  "moj/customise-blocks",
  addListBlockClassName,
);
