<?php

function custom_admin_bar_menu($admin_bar) {
    $admin_bar->remove_node('wp-logo');
}
add_action('admin_bar_menu', 'custom_admin_bar_menu', 100);


