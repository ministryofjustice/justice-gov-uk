// @ts-check

/**
 * Adds support for anchor destiantions to rich-text content in the block editor.
 */

import { __ } from "@wordpress/i18n";
import { RichTextToolbarButton } from "@wordpress/block-editor";
import { Popover, TextControl } from "@wordpress/components";
import { Fragment, useEffect, useState } from "@wordpress/element";
import { applyFormat, toggleFormat, useAnchor } from "@wordpress/rich-text";
import { TfiAnchor } from "react-icons/tfi";


// TODO - on domReady detect any anchors and adjust the formatting to moj-anchor.

const name = "moj/anchor";

const Edit = ({ contentRef, isActive, value, onChange }) => {
  const { activeFormats } = value;

  // State to show popover.
  const [showPopover, setShowPopover] = useState(false);
  const [activeAnchorName, setActiveAnchorName] = useState('');

  const getActiveAttrs = () => {
    const formats = activeFormats.filter((format) => name === format.type);

    if (!formats.length) {
      return {};
    }

    const { attributes, unregisteredAttributes } = formats[0];

    const appliedAttributes =
      attributes && Object.keys(attributes).length
        ? attributes
        : unregisteredAttributes;

    if (appliedAttributes) {
      return appliedAttributes;
    }

    // If we have no attributes, use the active anchor name from state.
    return { name: activeAnchorName };
  };

  const commitAnchorState = ({ newValue }) => {
    console.log({ newValue });
    setActiveAnchorName(newValue);
    onChange(
      applyFormat(value, {
        type: name,
        attributes: { name: newValue },
      }),
    );
  };

  return (
    <Fragment>
      <RichTextToolbarButton
        // Use padding to correct the icon size.
        icon={(props) => <TfiAnchor {...props} style={{ padding: "0.2em" }} />}
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
        <InlineUI
          onClose={({ newValue }) => {
            commitAnchorState({ newValue });
            setShowPopover(false);
          }}
          onSubmit={(e, { newValue }) => {
            e.preventDefault();
            commitAnchorState({ newValue });
            setShowPopover(false);
          }}
          onClear={() => {
            // Remove Format.
            onChange(toggleFormat(value, { type: name }));
            // Hide the popover.
            setShowPopover(false);
          }}
          contentRef={contentRef}
          initialState={getActiveAttrs().name || ""}
        />
      )}
    </Fragment>
  );
};

const InlineUI = ({ onSubmit, onClear, onClose, contentRef, initialState }) => {

  const [anchorName, setAnchorName] = useState(initialState);

  // It's annoying that settings is required here for useAnchor to work.
  // It's almost like a cyclic dependency, but it's just spaghetti.
  const popoverAnchor = useAnchor({
    editableContentElement: contentRef.current,
    settings,
  });

  const textControl = {
    label: "HTML Anchor",
    placeholder: "Add an anchor",
    help: (
      <>
        Enter a word or two — without spaces — to make a unique web address just
        for this block, called an “anchor.” Then, you’ll be able to link
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

  return (
    <Popover
      anchor={popoverAnchor}
      className="moj-anchor__popover"
      onClose={() => onClose({ newValue: anchorName })}
    >
      <form onSubmit={(e) => onSubmit(e, { newValue: anchorName })}>
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
          onClick={onClear}
        >
          Clear
        </button>
      </div>
    </Popover>
  );
};



/**
 * @typedef {import('@wordpress/rich-text/src/register-format-type').WPFormat } WPFormat
 * @type {WPFormat}
 */

const settings = {
  name,
  title: __("Anchor", "block-options"),
  tagName: "a",
  className: "moj-anchor",
  edit: Edit,
  interactive: false,
};

export default settings
