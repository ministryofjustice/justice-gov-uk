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
        <div className="search-bar-block">
          <div className="search-bar-block__wrapper">

            <div className="search-bar-block__search">
                  
              <form action="/search" onSubmit={alertUser}>
                <div className="text-input-form__wrapper">

                  <div className="text-input-form__input">
                    <div className="text-input">
                      <label className="text-input__label visually-hidden" htmlFor="searchbox-top">
                        Enter your Civil Procedure Rules search
                      </label>
                      <input id="searchbox-top" className="text-input__input" name="s" type="text" onClick={alertUser} />
                    </div>
                  </div>
                  
                  <div className="text-input-form__button">          
                    <input className="button button--input button--primary" type="submit" value="Search"  onClick={alertUser} />
                  </div>

                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    );
  }
});
