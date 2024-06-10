// @ts-check

import { __ } from "@wordpress/i18n";
import { RichTextToolbarButton } from "@wordpress/block-editor";
import { Popover, TextControl } from "@wordpress/components";
import { select, subscribe } from "@wordpress/data";
import { Fragment, useState } from "@wordpress/element";
import { applyFormat, toggleFormat, useAnchor } from "@wordpress/rich-text";
import { cleanForSlug } from "@wordpress/url";
import { TfiAnchor } from "react-icons/tfi";

/**
 * Adds support for anchor destiantions to rich-text content in the block editor.
 * 
 * Import type definitions for JSDoc.
 * @typedef {import('@wordpress/rich-text').RichTextValue} RichTextValue
 * @typedef {import('@wordpress/rich-text/build-types/register-format-type').WPFormat } WPFormat
 */

const name = "moj/anchor";

/**
 * The textControl props, language for the Edit > AnchorUI > TextControl component.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 */

const textControlProps = {
  label: "HTML Anchor",
  placeholder: "Add an anchor",
  help: (
    <>
      Enter a word or two — without spaces — to make a unique web address just
      for this block, called an “anchor.” Then, you’ll be able to link directly
      to this section of your page.
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
 * A variable to keep track of the current editor mode.
 * @type {'visual' | 'text' }
 */

let editorMode;

/**
 * Subscribe to the editorMode change.
 *
 * When the editorMode changes to "visual", format legacy anchor links. E.g.
 * - from `<a name="foo" id="foo"></a>` to `<a name="foo" id="foo" class="moj-anchor"> </a>`.
 * - from `<a name="foo" id="foo">Text</a>` to `<a name="foo" id="foo" class="moj-anchor">Text</a>`.
 * 
 * @returns {Promise<void>}
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

  // Format legacy anchor links - target only anchors that have a name and id, but no href or class.
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
 * This component is responsible for:
 * - rendering the anchor button in the toolbar
 * - showing the popover when the button is clicked
 * - handling the atribute and popover state
 * - validating and cleaning the anchor name
 *
 * @param {Object} props the props passed registerFormatType.
 * @param {React.RefObject<HTMLElement>} props.contentRef a reference to the content element.
 * @param {boolean} props.isActive whether the anchor format is active.
 * @param {RichTextValue & {activeFormats: Object[]}} props.value the current value of the rich text.
 * @param {Function} props.onChange the change handler for the rich text.
 * @returns {JSX.Element}
 */

const Edit = ({ contentRef, isActive, value, onChange }) => {
  // State to show popover.
  const [showPopover, setShowPopover] = useState(false);
  const [activeAnchorName, setActiveAnchorName] = useState("");

  const getActiveAttrs = () => {
    const formats = value.activeFormats.filter(
      (format) => name === format.type,
    );

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
    const cleanValue = cleanForSlug(newValue);

    if (cleanValue !== newValue) {
      alert(
        `Anchor name "${newValue}" is not a valid anchor name. It will be changed to "${cleanValue}".`,
      );
    }

    setActiveAnchorName(cleanValue);

    onChange(
      applyFormat(value, {
        type: name,
        attributes: { name: cleanValue, id: cleanValue },
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
      />
      {showPopover && (
        <AnchorUI
          contentRef={contentRef}
          initialState={getActiveAttrs().name || ""}
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
        />
      )}
    </Fragment>
  );
};

/**
 * The AnchorUI react functional component.
 *
 * This component is shown as a popover when the anchor button is clicked.
 * It allows the user to enter and clear an anchor name.
 * 
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-rich-text/#useanchor
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/popover/
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/text-control/
 *
 * @param {Object} props the props passed from the parent component.
 * @param {React.RefObject<HTMLElement>} props.contentRef a reference to the content element, so the popover is positioned correctly.
 * @param {string} props.initialState
 * @param {React.MouseEventHandler<HTMLButtonElement>} props.onClear
 * @param {Function} props.onClose
 * @param {Function} props.onSubmit
 * @returns {JSX.Element}
 */

const AnchorUI = ({ contentRef, initialState, onClear, onClose, onSubmit }) => {
  const [anchorName, setAnchorName] = useState(initialState);

  // It's annoying that settings is required here for useAnchor to work.
  // It's almost like a cyclic dependency, but it's just spaghetti.
  const anchor = useAnchor({
    editableContentElement: contentRef.current,
    settings,
  });

  return (
    <Popover
      anchor={anchor}
      className={`${settings.className}__popover`}
      onClose={() => onClose({ newValue: anchorName })}
    >
      <form onSubmit={(e) => onSubmit(e, { newValue: anchorName })}>
        <TextControl
          value={anchorName}
          onChange={setAnchorName}
          {...textControlProps}
        />
      </form>

      <div className={`${settings.className}__popover__row--button`}>
        <button
          className={`${settings.className}__popover__button components-button is-tertiary`}
          onClick={onClear}
          type="button"
        >
          Clear
        </button>
      </div>
    </Popover>
  );
};

/**
 * @type {WPFormat}
 */

const settings = {
  name,
  className: "moj-anchor",
  edit: Edit,
  interactive: false,
  tagName: "a",
  title: __("Anchor", "block-options"),
};

export default settings;
