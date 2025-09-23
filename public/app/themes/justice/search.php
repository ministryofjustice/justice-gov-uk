<?php

use Roots\WPConfig\Config;
use MOJ\Justice\Search;
use MOJ\Justice\Breadcrumbs;
use MOJ\Justice\Content;
use MOJ\Justice\Utils;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/search.v1.php';
    return;
}

get_header();

// TODO - make dynamic title
$title = 'Search';

$search = new Search();

?>

<div class="one-sidebar one-sidebar--left">
    <div class="one-sidebar__grid">

        <div class="one-sidebar__sidebar">
            <?php Utils::getSidebarMulti('left', ['panels_in' => ['search-filters']]); ?>
        </div>

        <article id="main-page-content" class="one-sidebar__article">
            <div class="one-sidebar__article-header">

                <?php get_template_part('template-parts/common/hero', null, [
                    'title' => $title,
                    'breadcrumbs' => (new Breadcrumbs)->getTheBreadcrumbs(),
                ]); ?>

            </div>
            <!-- TODO -->
        </article>

    </div>
</div>

<?php

get_footer();
