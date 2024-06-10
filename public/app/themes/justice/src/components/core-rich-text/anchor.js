// @ts-check

/**
 * Adds support for anchor destiantions to rich-text content in the block editor.
 */

import { __ } from "@wordpress/i18n";
import { RichTextToolbarButton } from "@wordpress/block-editor";
import { Popover, TextControl } from "@wordpress/components";
import { select, subscribe } from "@wordpress/data";
import { Fragment, useState } from "@wordpress/element";
import { applyFormat, toggleFormat, useAnchor } from "@wordpress/rich-text";
import { TfiAnchor } from "react-icons/tfi";

const name = "moj/anchor";

/**
 * A variable to keep track of the current editor mode.
 * @type {'' | 'visual' | 'text' }
 */

let editorMode = "";

/**
 * A helper function that resolves when the visual editor is ready.
 *
 * @see https://stackoverflow.com/a/60907141/6671505
 * @returns {Promise<void>}
 */

const visualEditorIsReady = () =>
  new Promise((resolve) => {
    const unsubscribe = subscribe(() => {
      if (
        select("core/editor").isCleanNewPost() ||
        select("core/block-editor").getBlockCount() > 0
      ) {
        unsubscribe();
        resolve();
      }
    });
  });

/**
 * Subscribe to the editorMode change.
 *
 * When the editorMode changes to "visual", format legacy anchor links. E.g.
 * - from `<a name="foo" id="foo"></a>` to `<a name="foo" id="foo" class="moj-anchor"> </a>`.
 * - from `<a name="foo" id="foo">Text</a>` to `<a name="foo" id="foo" class="moj-anchor">Text</a>`.
 */

subscribe(async () => {
  // Get the current editorMode
  const newEditorMode = select("core/edit-post").getEditorMode();

  // Only do something if editorMode has changed.
  if (newEditorMode === editorMode) {
    return;
  }

  // Update the editorMode variable.
  editorMode = newEditorMode;

  // Only do something if the editorMode is "visual".
  if (newEditorMode !== "visual") {
    return;
  }

  // Wait for the visual editor to be ready.
  await visualEditorIsReady();

  // Format legacy anchor links.
  document
    .querySelectorAll("a[name][id]:not([href]):not([class])")
    .forEach((a) => {
      // Add a class to the anchor.
      a.classList.add(settings.className);
      // If anchor text is empty, add a space - for compatibility and so the editor can see it.
      if (a.textContent === "") {
        a.textContent = " ";
      }
    });
});

/**
 * The Edit react functional component.
 *
 * TODO - sligify inpur
 */

const Edit = ({ contentRef, isActive, value, onChange }) => {
  const { activeFormats } = value;

  // State to show popover.
  const [showPopover, setShowPopover] = useState(false);
  const [activeAnchorName, setActiveAnchorName] = useState("");

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
        attributes: { name: newValue, id: newValue },
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
        <AnchorUI
          onClose={({ newValue }) => {
            commitAnchorState({ newValue });
            setShowPopover(false);
          }}
          onSubmit={(e, { newValue }) => {
            e.preventDefault();
            commitAnchorState({ newValue });
            setShowPopover(false);
          }}
          onClear={(e) => {
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

/**
 * The AnchorUI react functional component.
 *
 * This component is what is shown as a popover when the anchor button is clicked.
 * It allows the user to enter and clear an anchor name.
 *
 * TODO - sligify input
 *
 * @param {Object} props description
 * @param {any} props.contentRef description
 * @param {string} props.initialState description
 * @param {React.MouseEventHandler<HTMLButtonElement>} props.onClear description
 * @param {Function} props.onClose description
 * @param {Function} props.onSubmit description
 */

const AnchorUI = ({ contentRef, initialState, onClear, onClose, onSubmit }) => {
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

export default settings;
