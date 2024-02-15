<?php
// function sidebar_child_menu_List()
// {
//     global $post;

//     if (!$post) {
//         return;
//     }

//     $page_args = [
//         'child_of' => $post->ID,
//         'depth' => 1,
//         'exclude' => wp_get_post_parent_id($post->ID),
//         'title_li' => 0,
//         'post_status' => 'publish',
//         'link_after' => '<span class="dropdown"></span>',
//         'order' => 'ASC',
//         'orderby' => 'menu_order',
//     ];

//     echo '<nav class="c-left-hand-menu js-left-hand-menu">
//             <div class="c-left-hand-menu__step_back">' . get_the_title($post->ID) . '</div>
//             <ul>'.
//                 wp_list_pages($page_args) . '</ul></nav>';
// }

// sidebar_child_menu_List();

?>

<ul class="menu-left">
    <nav>
        <li class="level1"><a href="/courts"
            >Courts</a>
        </li>
        <li class="level1"><a href="/courts/procedure-rules"
            >Procedure
                rules</a></li>
        <li class="level1"><a class="selected"
                                href="/courts/procedure-rules/family"
            >Family</a>
        </li>
        <li class="level2"><a
                href="/courts/procedure-rules/family/fpr_foreword"
            >Foreword
                and summary of the rules</a></li>
        <li class="level2"><a
                href="/courts/procedure-rules/family/rules_pd_menu"
            >Rules
                &amp; Practice Directions</a></li>
        <li class="level2"><a
                href="/courts/procedure-rules/family/magistrates"
            >Magistrates
                Courts Rules</a></li>
        <li class="level2"><a href="/courts/procedure-rules/family/glossary"
            >Glossary</a>
        </li>
        <li class="level2"><a href="/courts/procedure-rules/family/formspage"
            >Forms</a>
        </li>
        <li class="level2"><a href="/courts/procedure-rules/family/update"
            >Updates
                &amp; Zips</a></li>
        <li class="level2"><a href="/courts/procedure-rules/family/stat_instr"
            >Statutory
                Instruments</a></li>
        <li class="level2"><a href="/courts/procedure-rules/family/contact"
            >Contact</a>
        </li>
    </nav>
</ul>
