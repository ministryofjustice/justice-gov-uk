// // @ts-check

import { registerFormatType } from "@wordpress/rich-text";
import anchor from "./anchor";
import underline from "./underline";
import highlightExample from "./highlight-example";

/**
 * Add support to the rich text block.
 *
 * @see https://github.com/CakeWP/block-options/blob/master/src/extensions/formats/index.js
 */

function registerEditorsKitFormats() {

  [anchor, highlightExample, underline].forEach(({ name, ...settings }) => {
    registerFormatType(name, settings);
  });

}

wp.domReady(registerEditorsKitFormats);
