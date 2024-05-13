// @ts-check

/**
 * Adds support for underline of rich-text content in the block editor.
 *
 * @see https://github.com/CakeWP/block-options/blob/master/src/extensions/formats/underline/index.js
 */

import { __ } from "@wordpress/i18n";
import { Fragment } from "@wordpress/element";
import { toggleFormat } from "@wordpress/rich-text";
import {
  RichTextToolbarButton,
  RichTextShortcut,
} from "@wordpress/block-editor";

const name = "moj/underline";

export const underline = {
  name,
  title: __("Underline", "block-options"),
  tagName: "span",
  className: "underline",
  attributes: null,
  edit({ isActive, value, onChange }) {
    const onToggle = () => {
      onChange(
        toggleFormat(value, {
          type: name,
        }),
      );
    };

    return (
      <Fragment>
        <RichTextShortcut type="primary" character="u" onUse={onToggle} />
        <RichTextToolbarButton
          icon="editor-underline"
          title={__("Underline", "block-options")}
          onClick={onToggle}
          isActive={isActive}
          shortcutType="primary"
          shortcutCharacter="u"
          onPointerEnterCapture={null}
          onPointerLeaveCapture={null}
          placeholder={null}
        />
      </Fragment>
    );
  },
};
