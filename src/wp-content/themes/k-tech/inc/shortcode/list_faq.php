<?php


function list_faq_src( $atts ) {
    ob_start();

    $atts = shortcode_atts( array(
        'parent_id' => 64, // ID của trang cha
    ), $atts );

    $args = array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'post_parent'    => $atts['parent_id'],
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    $child_pages = new WP_Query( $args );

    if ( $child_pages->have_posts() ) {
        echo '<div class="custom-child-pages-list">';

        while ( $child_pages->have_posts() ) {
            $child_pages->the_post();
            echo '<div class="child-page-item">';
            echo '<a class="child-page-title" href="' . get_permalink() . '">' . get_the_title() . '</a>';
            echo '<span class="child-page-date">' . get_the_date('d/m/Y') . '</span>';
            echo '</div>';
        }

        echo '</div>';
        wp_reset_postdata();
    }

    return ob_get_clean();
}
add_shortcode('list_faq', 'list_faq_src');
