// @ts-check

/**
 * Adds support for anchor destiantions to rich-text content in the block editor.
 */

import { __ } from "@wordpress/i18n";
import { RichTextToolbarButton } from "@wordpress/block-editor";
import { Popover, TextControl } from "@wordpress/components";
import { Fragment, useState } from "@wordpress/element";
import { applyFormat, toggleFormat, useAnchor } from "@wordpress/rich-text";
import { TfiAnchor } from "react-icons/tfi";

const name = "moj/anchor";

export const anchor = {
  name,
  title: __("Anchor", "block-options"),
  tagName: "a",
  className: "moj-anchor",
  attributes: {
    name: "name",
  },
  edit({ contentRef, isActive, value, onChange }) {
    const { activeFormats } = value;

    const getActiveAttrs = () => {
      const [format] = activeFormats.filter(
        (format) => name === format["type"],
      );

      if (!format) {
        return {};
      }

      const { attributes, unregisteredAttributes } = format;

      return Object.keys(attributes).length
        ? attributes
        : unregisteredAttributes || {};
    };

    // State to show popover.
    const [showPopover, setShowPopover] = useState(false);

    const [anchorName, setAnchorName] = useState(getActiveAttrs().name || "");

    const popoverAnchor = useAnchor({
      editableContentElement: contentRef.current,
    });

    const textControl = {
      label: "HTML Anchor",
      placeholder: "Add an anchor",
      help: (
        <>
          Enter a word or two — without spaces — to make a unique web address
          just for this block, called an “anchor.” Then, you’ll be able to link
          directly to this section of your page.
          <br />
          <a
            href="https://wordpress.org/documentation/article/page-jumps/"
            target="_blank"
          >
            Learn more about anchors
          </a>
        </>
      ),
    };

    const commitAnchorState = () => {
      onChange(
        applyFormat(value, {
          type: name,
          attributes: { name: anchorName },
        }),
      );
    };

    return (
      <Fragment>
        <RichTextToolbarButton
          // Use padding to correct the icon size.
          icon={(props) => (
            <TfiAnchor {...props} style={{ padding: "0.2em" }} />
          )}
          title={__("Anchor", "block-options")}
          onClick={() => {
            setShowPopover(true);
          }}
          isActive={isActive}
          onPointerEnterCapture={null}
          onPointerLeaveCapture={null}
          placeholder={null}
        />
        {showPopover && (
          <Popover
            anchor={popoverAnchor}
            className="moj-anchor__popover"
            onClose={() => {
              commitAnchorState();
              setShowPopover(false);
            }}
          >
            <form
              onSubmit={() => {
                commitAnchorState();
                setShowPopover(false);
              }}
            >
              <TextControl
                value={anchorName}
                onChange={setAnchorName}
                {...textControl}
              />
            </form>

            <div className="moj-anchor__popover__row--button">
              <button
                type="button"
                className="moj-anchor__popover__button components-button is-tertiary"
                onClick={() => {
                  // Remove Format.
                  onChange(toggleFormat(value, { type: name }));
                  // Hide the popover.
                  setShowPopover(false);
                }}
              >
                Clear
              </button>
            </div>
          </Popover>
        )}
      </Fragment>
    );
  },
};
