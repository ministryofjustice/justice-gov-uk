<?php
function sidebar_child_menu_List()
{
    global $post;
    $page_args = [
        'child_of' => $post->ID,
        'depth' => 0,
        'exclude' => wp_get_post_parent_id($post->ID),
        'title_li' => 0,
        'post_status' => 'publish',
        'link_after' => '<span class="dropdown"></span>',
        'order' => 'ASC',
        'orderby' => 'menu_order',
    ];

    echo '<nav class="c-left-hand-menu js-left-hand-menu">
            <div class="c-left-hand-menu__step_back">' . get_the_title($post->ID) . '</div>
            <ul>'.
                wp_list_pages($page_args) . '</ul></nav>';
}

sidebar_child_menu_List();
