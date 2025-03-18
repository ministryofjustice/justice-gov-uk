// @ts-check

import { registerFormatType } from "@wordpress/rich-text";
import domReady from "@wordpress/dom-ready";

import anchor from "./anchor";
import underline from "./underline";

/**
 * Add support to the rich text block.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-rich-text/#registerformattype
 * @see https://github.com/CakeWP/block-options/blob/master/src/extensions/formats/index.js
 * @return {void}
 */

const registerEditorsKitFormats = () => {
  [anchor, underline].forEach((settings) => {
    registerFormatType(settings.name, settings);
  });
};

domReady(registerEditorsKitFormats);
