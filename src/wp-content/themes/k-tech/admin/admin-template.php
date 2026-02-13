<?php


require __DIR__ . '/inc/admin-remove.php';
require __DIR__ . '/inc/admin-header.php';
require __DIR__ . '/inc/admin-wrap.php';

function ktech_backend() {
    wp_enqueue_style('backend', get_stylesheet_directory_uri() . '/admin/assets/admin-fe.css');
    wp_enqueue_script('backend', get_stylesheet_directory_uri() . '/admin/assets/admin-script.js', array('jquery'), false, true);
}
add_action('admin_enqueue_scripts', 'ktech_backend');

function ktech_login() {
    wp_enqueue_style('frontend', get_stylesheet_directory_uri() . '/admin/assets/admin-login.css');
}
add_action('login_enqueue_scripts', 'ktech_login');


add_filter('login_headerurl', fn() => '');


function ktech_copyright($text) {
    return '<p>Thiết kế Website bởi: <a href="https://k-tech.net.vn/">K-Tech</a></p>';
}
add_filter('admin_footer_text', 'ktech_copyright');







add_action('login_message', function () { ?>
    <a class="text-none" href="<?php echo home_url(); ?>">
        <div class="back-to-site">
            <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/admin/assets/images/back.png" alt="">
            <span>Quay lại Website <?php echo esc_html(get_bloginfo('name')); ?></span>
        </div>
    </a>
<?php
});





function custom_login_form_h2_script() { ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('loginform');
            form?.insertAdjacentHTML('afterbegin', '<h1 class="title-login">Đăng nhập</h1>');
        });
    </script>
<?php }
add_action('login_enqueue_scripts', 'custom_login_form_h2_script');




