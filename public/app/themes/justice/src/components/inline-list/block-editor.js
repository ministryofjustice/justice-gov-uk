// @ts-check

import { useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import { Placeholder, Spinner } from "@wordpress/components";
import { select, useSelect } from "@wordpress/data";
import { category } from "@wordpress/icons";

/**
 * This block will render a list of child pages for the current page.
 */

registerBlockType("moj/inline-menu", {
  apiVersion: 2,
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

    if (!childPages)
      return (
        <div {...blockProps}>
          <Spinner />
        </div>
      );

    if (!childPages.length)
      return (
        <div {...blockProps}>
          <Placeholder
            icon={category}
            label="Inline menu - child pages"
            instructions={`No child pages were found for this ${postType}.`}
          />
        </div>
      );

    return (
      <div {...blockProps}>
        <ul className="inline-list">
          {childPages.map((page) => (
            <li key={page.id}>
              <a href={`#${page.slug}`}>{page.title.rendered}</a>
            </li>
          ))}
        </ul>
      </div>
    );
  },
  supports: {
    className: false,
  },
});
