<?php

use Roots\WPConfig\Config;
use MOJ\Justice\Breadcrumbs;
use MOJ\Justice\Documents;
use MOJ\Justice\Search;
use MOJ\Justice\Utils;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/search.v1.php';
    return;
}

get_header();

?>

<div class="one-sidebar one-sidebar--left">
    <div class="one-sidebar__grid">

        <div class="one-sidebar__sidebar">
            <?php Utils::getSidebarMulti('left', ['panels_in' => ['search-filters']]); ?>
        </div>

        <article id="main-page-content" class="one-sidebar__article">
            <div class="one-sidebar__article-header">

                <?php get_template_part('template-parts/common/hero', null, [
                    'title' => Search::getSearchPageTitle(),
                    'breadcrumbs' => Breadcrumbs::getTheBreadcrumbs(),
                ]); ?>

                <div class="one-sidebar__sidebar one-sidebar__sidebar--mobile">
                    <?php Utils::getSidebarMulti('left', ['is_mobile' => true, 'panels_in' => ['search-filters']]); ?>
                </div>

            </div>

            <div class="one-sidebar__article-content">

                <?php

                get_template_part('template-parts/search/search-bar-block', null, [
                    'search_form' => [
                        'id' => 'search-bar-main',
                        'action' => '/search',
                        'input' => [
                            'id' => 'searchbox-top',
                            'name' => 's',
                            'label' => Search::getSearchFormLabel(),
                            'value' => get_search_query(),
                            'label_hidden' => true,
                        ],
                        'hidden_inputs' => Search::getFormValues(['s', 'orderby']),
                        'button' => ['text' => 'Search']
                    ],
                    'result_count' => Search::getResultCount(),
                    'filters' => Search::getSortOptions(),
                    'did_you_mean' => Search::getDidYouMean(),
                ]);

                $results = [];

                if (!empty(get_search_query()) && have_posts()) {
                    while (have_posts()) {
                        the_post();
                        $url = get_the_permalink();
                        $results[] = [
                            'title' => get_the_title(),
                            'url' => $url,
                            'date' => get_the_date('j F Y'),
                            'description' => apply_filters('the_excerpt', get_the_excerpt()),
                            'is_document' => Documents::isDocument(get_the_ID()),
                            'filesize' => Documents::getFormattedFilesize(get_the_ID()),
                            'format' => pathinfo($url, PATHINFO_EXTENSION),
                        ];
                    }
                }

                get_template_part('template-parts/search/search-result-list', null, [
                    'cards' => $results,
                    'query' => get_search_query(),
                ]);

                get_template_part('template-parts/search/pagination', null, Search::getPaginationArgs()); ?>

            </div>
        </article>

    </div>
</div>

<?php

get_footer();
