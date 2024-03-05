// @ts-check

import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import { select, useSelect } from "@wordpress/data";

/**
 * This block will render a list of child pages for the current page.
 */

registerBlockType("moj/inline-menu", {
  title: "Inline menu - child pages",
  icon: "editor-insertmore",
  category: "common",
  attributes: {},
  edit: () => {
    const blockProps = useBlockProps(),
      postId = select("core/editor").getCurrentPostId(),
      postType = select("core/editor").getCurrentPostType(),
      childPages = useSelect((select) => {
        // @ts-ignore https://github.com/WordPress/gutenberg/pull/46881
        return select("core").getEntityRecords("postType", postType, {
          parent: postId,
        });
      }, []);

    return (
      <div {...blockProps}>
        {!childPages ? (
          <p>Loading...</p>
        ) : (
          <ul>
            {childPages.map((page) => (
              <li key={page.id}>
                <a href={`#${page.slug}`}>{page.title.rendered}</a>
              </li>
            ))}
          </ul>
        )}
      </div>
    );
  },
  supports: {
    className: false,
  },
});
