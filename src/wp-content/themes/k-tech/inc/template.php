<?php


function frontend() {
	wp_enqueue_style('root', get_stylesheet_directory_uri() . '/public/css/root.css');
	wp_enqueue_style('frontend', get_stylesheet_directory_uri() . '/public/css/frontend.css');
	wp_enqueue_style('swipper', get_stylesheet_directory_uri() . '/public/css/swiper.min.css');

	wp_enqueue_script('script', get_stylesheet_directory_uri() . '/public/scripts/frontend.js', array('jquery'), false, true);
	wp_enqueue_script('sweetalert2', get_stylesheet_directory_uri() . '/public/scripts/sweetalert2.all.min.js', array('jquery'), false, true);
	wp_enqueue_script('swipper', get_stylesheet_directory_uri() . '/public/scripts/swiper.min.js', array('jquery'), false, true);

}
add_action('wp_enqueue_scripts', 'frontend');




include_once 'shortcode/master.php';
include_once 'shortcode/noti_header.php';
include_once 'shortcode/list_faq.php';
include_once 'shortcode/register.php';




add_filter('use_block_editor_for_post', '__return_false');
add_filter('use_block_editor_for_page', '__return_false');



