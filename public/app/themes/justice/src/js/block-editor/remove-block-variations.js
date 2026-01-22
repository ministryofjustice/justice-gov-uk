// @ts-check

import {
    getBlockVariations,
    unregisterBlockVariation,
} from "@wordpress/blocks";
import domReady from "@wordpress/dom-ready";

/**
 * Removes support for non-default block variations.
 *
 * @returns {void}
 */

domReady(async () => {
  let i = 0;

  // Wait for block variations to be defined, with a timeout of 30 seconds
  while (getBlockVariations?.("core/heading") === undefined && i++ < 3_000) {
    // Wait 100ms between checks
    await new Promise((resolve) => setTimeout(resolve, 100));
  }

  getBlockVariations("core/heading")?.forEach((variation) => {
    if (!variation.isDefault) {
      unregisterBlockVariation("core/heading", variation.name);
    }
  });

  getBlockVariations("core/paragraph")?.forEach((variation) => {
    if (!variation.isDefault) {
      unregisterBlockVariation("core/paragraph", variation.name);
    }
  });
});
