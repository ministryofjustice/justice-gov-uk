<div class="search-result-list">
    <?php if (!empty($args['cards'])): ?>
        <ol class="search-result-list__list">
            <?php foreach ($args['cards'] as $card): ?>
                <li class="search-result-list__element">
                    <?php
                    get_template_part('template-parts/search/search-result-card', null, [
                        'title' => $card['title'],
                        'url' => $card['url'],
                        'date' => $card['date'],
                        'description' => $card['description'] ?? '',
                        'language' => $card['language'] ?? null,
                        'is_document' => $card['is_document'] ?? false,
                        'format' => ($card['is_document'] ?? false) ? $card['format'] : null,
                        'filesize' => ($card['is_document'] ?? false) ? ($card['filesize'] ?? null) : null,
                    ]);
                    ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <div class="search-result-list__no-results">
            <?php

            $content = "<h2>No results</h2>";

            if (!empty($args['query'])) {
                $content .= "<p>Your search for <span class='search-result-list__no-results-query'>" . esc_html($args['query']) . "</span> did not return any results.</p>";
                $content .= "<p>Please try again with different keywords or filters.</p>";
            } else {
                $content .= "<p>Please enter a search query to begin.</p>";
            }

            get_template_part('template-parts/common/rich-text', null, [
                'content' => $content,
            ]);

            ?>
        </div>
    <?php endif; ?>
</div>