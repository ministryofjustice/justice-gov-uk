// @ts-check

import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";

/**
 * This block will render a search form for the current page.
 */

registerBlockType("moj/search", {
  title: "Search - child pages",
  icon: "search",
  category: "common",
  attributes: {},
  description: "Search child pages of current page.",
  edit: () => {
    const blockProps = useBlockProps({
      className: 'wp-block-moj-search',
    });

    const alertUser = (e) => {
      e.preventDefault();
      alert('Search is disabled for editors in the WordPress dashboard.')
    }

    return (
      <div {...blockProps}>
        <form className="search-bar" onSubmit={alertUser} >
          <input id="query" type="text"  onClick={alertUser} />
          <input className="go-btn" type="submit" value="Search" onClick={alertUser} />
        </form>
      </div>
    );
  }
});
