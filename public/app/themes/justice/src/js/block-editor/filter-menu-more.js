// @ts-check

import domReady from "@wordpress/dom-ready";

/**
 * Remove unwanted buttons and groups from the more menu.
 */

const buttonsToRemove = [
  "Manage patterns",
  "Welcome Guide",
  "Help(opens in a new tab)",
];

/**
 * Check if a node is an element node.
 *
 * @param {Node} node
 * @returns {node is Element}
 */

const isElementNode = (node) => node?.nodeType === Node.ELEMENT_NODE;

/**
 * Remove items from the more-menu.
 *
 * This is the menu with the 3 vertical dots in the top right corner of the editor.
 * WordPress doesn't give us a filter for this so we have to do it manually.
 *
 * @param {MutationRecord[]} mutationsList
 * @returns {void}
 */

export const removeElementsFromMoreMenu = (mutationsList) => {
  // Check if the menu-more node has been added.
  const menuMoreNodeAdded = mutationsList.find(
    (mutation) =>
      isElementNode(mutation.target.firstChild) &&
      mutation.target.firstChild?.classList?.contains(
        "interface-more-menu-dropdown__content",
      ),
  );

  // Return if the menu-more node has not been added.
  if (!menuMoreNodeAdded) {
    return;
  }

  // Get the groups.
  const groups = document.querySelectorAll(
    ".interface-more-menu-dropdown__content .components-menu-group",
  );

  // Return if there are no button groups.
  if (!groups) return;

  // Remove the Plugin group.
  const pluginGroup = Array.from(groups).find(
    (g) =>
      g.querySelector(".components-menu-group__label")?.textContent ===
      "Plugins",
  );
  pluginGroup?.classList.add("hidden");

  // Get all of the menu-more buttons.
  const buttons = document.querySelectorAll(
    ".interface-more-menu-dropdown__content .components-button",
  );
  // Loop over the buttons and hide the ones we don't want.
  buttons?.forEach((button) => {
    if (buttonsToRemove.includes(button.textContent)) {
      button.classList.add("hidden");
    }
  });
};

domReady(() => {
  // Setup and start observing the DOM.
  const observer = new MutationObserver(removeElementsFromMoreMenu);
  observer.observe(document.body, { childList: true, subtree: true });
});
