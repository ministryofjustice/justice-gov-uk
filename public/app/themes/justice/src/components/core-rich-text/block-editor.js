// // @ts-check

import { registerFormatType } from "@wordpress/rich-text";
import { underline } from "./underline";

/**
 * Add support to the rich text block.
 *
 * @see https://github.com/CakeWP/block-options/blob/master/src/extensions/formats/index.js
 */

function registerEditorsKitFormats() {
  const { name, ...settings } = underline;
  registerFormatType(name, settings);
}

wp.domReady(registerEditorsKitFormats);
