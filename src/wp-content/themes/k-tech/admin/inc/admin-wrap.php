<?php





function admin_menu_themes_setting() {
    add_menu_page(
        'Cài đặt giao diện',
        'Cài đặt giao diện',
        'edit_posts',
        'theme-options',
        'theme_options_page',
        get_stylesheet_directory_uri() . '/admin/assets/images/theme.svg',
        1
    );

    add_submenu_page(
        'theme-options',
        'Cài đặtMenu',
        'Cài đặt Menu',
        'edit_posts',
        'nav-menus.php'
    );
    add_submenu_page(
        'theme-options',
        'Tuỳ biến giao diện',
        'Tuỳ biến giao diện',
        'edit_posts',
        'customize.php'
    );
    add_submenu_page(
        'theme-options',
        'Cài đặt nâng cao',
        'Cài đặt nâng cao',
        'edit_posts',
        'admin.php?page=optionsframework&tab'
    );
    add_submenu_page(
        'theme-options',
        'Chỉnh sửa Code',
        'Chỉnh sửa Code',
        'edit_posts',
        'theme-editor.php'
    );
    add_submenu_page(
        'theme-options',
        'Cài đặt Widgets',
        'Cài đặt Widgets',
        'edit_posts',
        'widgets.php'
    );
    add_submenu_page(
        'theme-options',
        'Quản lý người dùng',
        'Quản lý người dùng',
        'edit_posts',
        'users.php'
    );
}
add_action('admin_menu', 'admin_menu_themes_setting');


function theme_options_page() {}

function theme_options_redirect()
{
    if (isset($_GET['page']) && $_GET['page'] === 'theme-options') {
        wp_redirect(admin_url('themes.php'));
        exit;
    }
}

add_action('admin_menu', 'admin_menu_themes_setting');
add_action('admin_init', 'theme_options_redirect');



function nocodevn_menu_admin() { ?>
    <li class="my-profile wp-has-submenu menu-top" id="my-profile">
        <div class="nocodevn-profile">
            <a href="<?php echo home_url(); ?>">
                <div class="nocodevn-profile-logo">
                    <?php
                    $custom_logo_id = get_theme_mod('site_logo');
                    $header_logo_url = $custom_logo_id ? wp_get_attachment_image_src($custom_logo_id, 'full')[0] : '';

                    echo $header_logo_url ? '<img src="' . esc_url($header_logo_url) . '" alt="Site Logo">' : '<img src="' . esc_url(get_template_directory_uri() . '/admin/assets/images/nocode-logo.png') . '" alt="Default Logo">';
                    ?>
                </div>
            </a>
            <div class="nocodevn-profile-title"><?php echo esc_html(get_bloginfo('name')); ?> <?php get_bloginfo('description') && esc_html_e('- ' . get_bloginfo('description')); ?>
            </div>
            <div class="nocodevn-profile-action">
                <div class="profile profile-user">
                    <span class="nocodevnRedirectAdmin" data-redirect="<?php echo esc_url(admin_url('users.php')); ?>">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/admin/assets/images/account.svg">
                    </span>
                </div>
                <div class="profile profile-logout">
                    <span class="nocodevnRedirectAdmin" data-redirect="<?php echo esc_url(wp_logout_url()); ?>">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/admin/assets/images/logout.svg">
                    </span>
                </div>
            </div>
        </div>
    </li>
<?php }
add_action('adminmenu', 'nocodevn_menu_admin');


