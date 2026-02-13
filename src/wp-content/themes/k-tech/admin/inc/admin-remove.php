<?php


add_action('admin_menu', function () {
    remove_menu_page('wp-logo');
    remove_menu_page('index.php');
    remove_menu_page('themes.php');
    remove_menu_page('tools.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('plugin-editor.php');
    remove_menu_page('users.php');

    remove_menu_page('flatsome-panel');
    remove_menu_page('postType=wp_block');
    remove_menu_page('edit.php?post_type=featured_item');
}, 99);