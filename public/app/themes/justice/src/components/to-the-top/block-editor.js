// @ts-check

/**
 * This block will render a link to the top of the page.
 * @see https://www.wordpressintegration.com/blog/creating-custom-wordpress-gutenberg-block/
 */

wp?.blocks.registerBlockType("moj/to-the-top", {
  title: "To the top", // Block name visible to the user within the editor
  icon: "arrow-up-alt", // Toolbar icon displayed beneath the name of the block
  category: "common", // The category under which the block will appear in the Add block menu
  attributes: {}, // The data this block will be storing
  //   edit: function () {
  //     // Defines how the block will render in the editor
  //   },
  supports: {
    className: false, // Removes the default class name: wp-block-{name}
  },
  save: function () {
    // Defines how the block will render on the frontend
    return wp.element.createElement(
      "a",
      { href: "#top", className: "to-the-top" },
      "To the top",
    );
  },
});