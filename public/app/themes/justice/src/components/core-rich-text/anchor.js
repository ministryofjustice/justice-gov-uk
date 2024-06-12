// @ts-check

import { __ } from "@wordpress/i18n";
import { RichTextToolbarButton } from "@wordpress/block-editor";
import { Popover, TextControl, ToggleControl } from "@wordpress/components";
import { dispatch, select, subscribe, useSelect } from "@wordpress/data";
import { Fragment, useState } from "@wordpress/element";
import { store as preferencesStore } from "@wordpress/preferences";
import { applyFormat, toggleFormat, useAnchor } from "@wordpress/rich-text";
import { cleanForSlug } from "@wordpress/url";
import { TfiAnchor } from "react-icons/tfi";

/**
 * This file adds support for anchor destinations to rich-text content in the block editor.
 *
 * In summary, this file does the following:
 * - adds an anchor button to the toolbar.
 * - shows a popover when the button is clicked.
 * - allows the user to enter and clear an anchor id.
 * - validates and cleans the anchor id.
 * - saves the anchor id and mirrors the value to the name attribute.
 * - formats legacy anchor links.
 * - subscribes to the user's preference for showing anchor icons.
 * - adds or removes a class from the post content element based on the user's preference.
 *
 * Import type definitions for JSDoc.
 * @typedef {import('@wordpress/rich-text').RichTextValue} RichTextValue
 * @typedef {import('@wordpress/rich-text/build-types/register-format-type').WPFormat } WPFormat
 */

/**
 * The settings, without the edit function, that's added later.
 * @type {Omit<WPFormat, 'edit'>}
 */

const settings = {
  name: "moj/anchor",
  className: "moj-anchor",
  interactive: false,
  tagName: "a",
  title: __("Anchor", "block-options"),
  // @ts-ignore - this is valid according rich-text.js.
  attributes: {
    id:  "id",
    name:  "name",
  },
};

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
 * The toggleControl props, language for the Edit > AnchorUI > ToggleControl component.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/toggle-control/
 */

const toggleControlProps = {
  label: "Show anchor icons",
  help: "Update my preferences to show anchor icons.",
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
 * @type {'visual' | 'text'}
 */

let editorMode;

/**
 * A variable to keep track of the user's preference.
 * @type {boolean}
 */

let showIcons;

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
 * Subscribe to the user's preference for showing anchor icons.
 *
 * When the user's preference changes, add or remove a class from the post content element.
 *
 * @returns {Promise<void>}
 */

subscribe(async () => {
  await visualEditorIsReady();

  // @ts-ignore - due to WP's lack of types/docs.
  const newShowIcons = select(preferencesStore).get(settings.name, "showIcons");

  if (newShowIcons === showIcons) {
    return;
  }

  showIcons = newShowIcons;

  const postContentClassname = "wp-block-post-content";
  const variationClassname = `${postContentClassname}--show-moj-anchor-icons`;
  const postContentElement = document.querySelector(`.${postContentClassname}`);

  if (showIcons) {
    postContentElement.classList.add(variationClassname);
  } else {
    postContentElement.classList.remove(variationClassname);
  }
});

/**
 * Set the user's preference for showing anchor icons to true by default.
 */

// @ts-ignore - due to WP's lack of types/docs.
dispatch(preferencesStore).setDefaults(settings.name, {
  showIcons: true,
});

/**
 * The Edit react functional component.
 *
 * This component is responsible for:
 * - rendering the anchor button in the toolbar.
 * - showing the popover when the button is clicked.
 * - handling the atribute and popover state.
 * - validating and cleaning the anchor name.
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

  // Get the user's preference for showing anchor icons from local storage.
  const showIcons = useSelect(
    // @ts-ignore - due to WP's lack of types/docs.
    (select) => select(preferencesStore).get(settings.name, "showIcons"),
    [],
  );

  // Saves the user's preference for showing anchor icons to local storage.
  const toggleShowIcons = () => {
    // @ts-ignore - due to WP's lack of types/docs.
    dispatch(preferencesStore).toggle(settings.name, "showIcons");
  };

  /**
   * A helper function to get the active attributes.
   *
   * It will check the value from props and fallback to an empty object.
   *
   * @returns {{id: string, name: string}?}
   */

  const getActiveAttrs = () => {
    const formats = value.activeFormats.filter(
      (format) => settings.name === format.type,
    );

    if (!formats.length) {
      return { id: "", name: "" };
    }

    // Check two properties, and prefer attributes over unregisteredAttributes.
    const { attributes, unregisteredAttributes } = formats[0];

    const appliedAttributes =
      attributes && Object.keys(attributes).length
        ? attributes
        : unregisteredAttributes;

    return appliedAttributes || { id: "", name: "" };
  };

  /**
   * A helper function to validate and apply a new id value.
   *
   * @param {string} newId the new value to validate and apply.
   * @returns {void}
   */

  const applyAttributes = (newId) => {
    // Utilise JS's decodeURI & encodeURI functions to ensure the newId is a valid url fragment.
    const dummyUrl = "http://a.com/#";
    const validUrl = encodeURI(decodeURI(`${dummyUrl}${newId}`));
    const validId = validUrl.replace(dummyUrl, "");

    if (validId !== newId) {
      alert(
        `Anchor name "${newId}" is not a valid anchor name. It will be changed to "${validId}".`,
      );
    }

    onChange(
      applyFormat(value, {
        type: settings.name,
        // @ts-ignore - due to WP's lack of types/docs.
        attributes: { id: validId, name: validId },
      }),
    );
  };

  return (
    <Fragment>
      <RichTextToolbarButton
        // Use padding to correct the icon size.
        icon={(props) => <TfiAnchor {...props} style={{ padding: "0.2em" }} />}
        isActive={isActive}
        title={__("Anchor", "block-options")}
        onClick={() => {
          setShowPopover(true);
        }}
      />
      {showPopover && (
        <AnchorUI
          contentRef={contentRef}
          initialState={getActiveAttrs()?.id || ""}
          onClose={({ newId }) => {
            applyAttributes(newId);
            setShowPopover(false);
          }}
          onSubmit={(e, { newId }) => {
            e.preventDefault();
            applyAttributes(newId);
            setShowPopover(false);
          }}
          onClear={() => {
            // Remove Format.
            onChange(toggleFormat(value, { type: settings.name }));
            setShowPopover(false);
          }}
          showIcons={showIcons}
          toggleShowIcons={toggleShowIcons}
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
 * @param {boolean} props.showIcons
 * @param {Function} props.toggleShowIcons
 * @returns {JSX.Element}
 */

const AnchorUI = ({
  contentRef,
  initialState,
  onClear,
  onClose,
  onSubmit,
  showIcons,
  toggleShowIcons,
}) => {
  const [anchorId, setAnchorId] = useState(initialState);

  // It's annoying that settings is required here for useAnchor to work.
  // It's almost like a cyclic dependency, but it's just spaghetti.
  const anchor = useAnchor({
    editableContentElement: contentRef.current,
    settings: settingsWithEdit,
  });

  return (
    <Popover
      anchor={anchor}
      className={`${settings.className}__popover`}
      onClose={() => onClose({ newId: anchorId })}
    >
      <form onSubmit={(e) => onSubmit(e, { newId: anchorId })}>
        <TextControl
          value={anchorId}
          onChange={setAnchorId}
          {...textControlProps}
        />
      </form>

      {/* A toggle that updates the user's preference for showing anchor icons */}
      <ToggleControl
        {...toggleControlProps}
        checked={showIcons}
        onChange={() => {
          toggleShowIcons();
        }}
      />

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
 * Add the Edit function to the settings object.
 * @type {WPFormat}
 */

const settingsWithEdit = { ...settings, edit: Edit };

export default settingsWithEdit;
