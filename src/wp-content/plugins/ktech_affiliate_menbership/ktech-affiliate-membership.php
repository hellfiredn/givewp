<?php
/*
Plugin Name: KTech Affiliate Membership
Description: Manage member groups, benefits, motivational bonus framework, LP scoreboard for WooCommerce system.
Version: 1.0
Author: Ngọc Hải
*/

if (!defined('ABSPATH')) {
    exit;
}

global $vietnam_cities, $vietnam_addresses;
$vietnam_cities = array();
$vietnam_addresses = array();
$vietnam_addresses_file = plugin_dir_path(__FILE__) . 'data/vietnam-addresses.json';
if (file_exists($vietnam_addresses_file)) {
    $json_data = file_get_contents($vietnam_addresses_file);
    $addresses = json_decode($json_data, true);
    if (is_array($addresses)) {
        $vietnam_addresses = $addresses;
        foreach ($addresses as $city => $val) {
            if (isset($city)) {
                $vietnam_cities[] = $city;
            }
        }
    }
}

function kam_enqueue_scripts() {
    global $vietnam_addresses;
    
    $scripts = [
        'kam-filter-refund-js' => 'js/filter_refund.js',
        'kam-filter-order-js' => 'js/filter_order.js',
        'kam-save-address-js' => 'js/address.js',
        'kam-master-js' => 'js/master.js',
        'kam-my-account-js' => 'js/my-account.js',
        'kam-single-product-js' => 'js/single-product.js'
    ];
    
    // Thêm checkout script cho trang checkout
    if (is_checkout()) {
        $scripts['kam-checkout-js'] = 'js/checkout.js';
        wp_enqueue_style('kam-checkout-css', plugin_dir_url(__FILE__) . 'css/checkout.css', array(), '1.0.1');
    }

    wp_enqueue_style('kam-my-account-css', plugin_dir_url(__FILE__) . 'css/my-account.css', array(), '1.0.1');
    wp_enqueue_style('kam-single-product-css', plugin_dir_url(__FILE__) . 'css/single-product.css', array(), '1.0');
    
    foreach ($scripts as $handle => $script_path) {
        wp_enqueue_script($handle, plugin_dir_url(__FILE__) . $script_path, array('jquery'), '1.0.1', true);
        wp_localize_script($handle, 'kam_ajax_obj', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'vietnam_addresses' => $vietnam_addresses
        ));
    }
}
add_action('wp_enqueue_scripts', 'kam_enqueue_scripts');

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script('kam_js_admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), '1.0.1', true);
    wp_localize_script('kam_js_admin', 'kam_ajax_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'voucher_nonce' => wp_create_nonce('voucher_nonce'),
        'delete_role_nonce' => wp_create_nonce('delete_role_nonce'),
    ));
    wp_enqueue_style('kam-admin-css', plugin_dir_url(__FILE__) . 'css/admin.css', array(), '1.0');
});

require_once plugin_dir_path(__FILE__) . 'includes/class/affiliate-db.php';
register_activation_hook(__FILE__, ['KTech_Affiliate_DB', 'install_table']);

require_once plugin_dir_path(__FILE__) . 'includes/class/address-db.php';
register_activation_hook(__FILE__, ['KTech_Address_DB', 'install_table']);

require_once plugin_dir_path(__FILE__) . 'includes/class/upgrade-requests-db.php';
register_activation_hook(__FILE__, ['KTech_Upgrade_Requests_DB', 'install_table']);

require_once plugin_dir_path(__FILE__) . 'includes/class/voucher-db.php';
register_activation_hook(__FILE__, ['KTech_Voucher_DB', 'install_table']);

require_once plugin_dir_path(__FILE__) . 'includes/class/affiliate-membership.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions/helper.php';
// require_once plugin_dir_path(__FILE__) . 'includes/payment/payme.php';
require_once plugin_dir_path(__FILE__) . 'ajax/filter_refund.php';
require_once plugin_dir_path(__FILE__) . 'ajax/filter_order.php';
require_once plugin_dir_path(__FILE__) . 'ajax/address.php';
require_once plugin_dir_path(__FILE__) . 'ajax/master.php';
require_once plugin_dir_path(__FILE__) . 'ajax/upgrade-account.php';
require_once plugin_dir_path(__FILE__) . 'ajax/training.php';
require_once plugin_dir_path(__FILE__) . 'admin/upgrade-requests.php';
require_once plugin_dir_path(__FILE__) . 'admin/member-list.php';
require_once plugin_dir_path(__FILE__) . 'admin/product.php';
require_once plugin_dir_path(__FILE__) . 'admin/blog.php';
require_once plugin_dir_path(__FILE__) . 'admin/my-account.php';
require_once plugin_dir_path(__FILE__) . 'admin/voucher.php';
require_once plugin_dir_path(__FILE__) . 'admin/order.php';

// Hook để override WooCommerce checkout templates
add_filter('woocommerce_locate_template', 'kam_locate_template', 10, 3);

function kam_locate_template($template, $template_name, $template_path) {
    global $woocommerce;
    
    $plugin_path = plugin_dir_path(__FILE__) . 'templates/';
    
    // Danh sách template cần override
    $custom_templates = [
        'checkout/form-billing.php',
        'checkout/form-shipping.php'
    ];
    
    if (in_array($template_name, $custom_templates)) {
        if (file_exists($plugin_path . $template_name)) {
            $template = $plugin_path . $template_name;
        }
    }
    
    return $template;
}

// Hook để xử lý checkout với địa chỉ đã lưu
add_action('woocommerce_checkout_process', 'kam_checkout_field_process');
add_action('woocommerce_checkout_update_order_meta', 'kam_checkout_field_update_order_meta');

function kam_checkout_field_process() {
    // Validation cho unified address system
    if (isset($_POST['address_type']) && $_POST['address_type'] === 'saved') {
        // Có thể thêm validation cho địa chỉ đã chọn
        if (empty($_POST['billing_address_id'])) {
            wc_add_notice(__('Vui lòng chọn địa chỉ hợp lệ.'), 'error');
        }
    }
}

function kam_checkout_field_update_order_meta($order_id) {
    // Xử lý unified address system
    if (isset($_POST['address_type']) && $_POST['address_type'] === 'saved') {
        // Lưu thông tin rằng đơn hàng này sử dụng địa chỉ đã lưu
        update_post_meta($order_id, '_used_saved_address', 'yes');
        update_post_meta($order_id, '_unified_address_system', 'yes');
        
        // Lưu ID của địa chỉ đã sử dụng cho cả billing và shipping
        if (isset($_POST['billing_address_id'])) {
            update_post_meta($order_id, '_saved_address_id', sanitize_text_field($_POST['billing_address_id']));
            update_post_meta($order_id, '_billing_address_id', sanitize_text_field($_POST['billing_address_id']));
            update_post_meta($order_id, '_shipping_address_id', sanitize_text_field($_POST['billing_address_id']));
        }
    }
    
    // Xử lý order comments (ghi chú đơn hàng)
    if (isset($_POST['order_comments']) && !empty($_POST['order_comments'])) {
        $order_comments = sanitize_textarea_field($_POST['order_comments']);
        
        // Lưu ghi chú vào order
        $order = wc_get_order($order_id);
        if ($order) {
            $order->set_customer_note($order_comments);
            $order->save();
        }
        
        // Lưu thêm vào post meta để dễ truy xuất
        update_post_meta($order_id, '_order_comments', $order_comments);
    }
}


add_action('wp_footer', 'kam_add_popup_address_checkout');
function kam_add_popup_address_checkout () {
    if (!is_checkout()) {
        return;
    }
    $db = new KTech_Address_DB();
    $user_id = get_current_user_id();
    $saved_addresses = $db->get_by_user($user_id);

    global $vietnam_cities;
    ?>
        <div id="kam-address-checkout-popup" style="display: none;">
            <div class="saved-addresses-section">
                <button type="button" class="kam-address-popup-close" style="position:absolute;top:10px;right:10px;font-size:24px;background:none;border:none;cursor:pointer;z-index:10">&times;</button>
                <?php if ($user_id) : ?>
                    <div id="saved-addresses-list">
                        <?php if (!empty($saved_addresses)) { ?>
                            <?php foreach ($saved_addresses as $address) { ?>
                                <div class="address-option">
                                    <img class="kam-address-selected-location" src="/wp-content/uploads/2025/09/location.png" />
                                    <label for="saved_address_<?php echo $address->id; ?>">
                                        <strong><?php echo esc_html($address->name); ?></strong> - <?php echo esc_html($address->phone); ?><br>
                                        <small><?php echo esc_html($address->address . ', ' . $address->district . ', ' . $address->city); ?></small>
                                    </label>
                                    <div>
                                        <input 
                                            type="radio" 
                                            <?php echo $address->is_default ? 'checked' : ''; ?>
                                            name="kam-address-is-default"
                                            data-id="<?php echo $address->id; ?>"
                                            data-name="<?php echo esc_attr($address->name); ?>"
                                            data-phone="<?php echo esc_attr($address->phone); ?>"
                                            data-city="<?php echo esc_attr($address->city); ?>"
                                            data-district="<?php echo esc_attr($address->district); ?>"
                                            data-commune="<?php echo esc_attr($address->commune); ?>"
                                            data-address="<?php echo esc_attr($address->address); ?>"
                                        >
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <!-- Form thêm/sửa địa chỉ -->
                    <div class="address-form">
                        <h2 class="address-form__title">Thay đổi địa chỉ</h2>
                        <form id="kam_save_address" data-location="checkout">
                            <div class="address-form__group address-form__group--full address-type-wrapper">
                                <input type="radio" id="popup_address-type-home" name="address_type" value="Nhà riêng" required>
                                <label for="popup_address-type-home"  class="btn-add-address-type">Nhà riêng</label>
                                <input type="radio" id="popup_address-type-office" name="address_type" value="Văn phòng" required>
                                <label for="popup_address-type-office"  class="btn-add-address-type">Văn phòng</label>
                            </div>
                            <?php wp_nonce_field('kam_save_address_action', 'kam_save_address_nonce'); ?>
                            <div class="address-form__group">
                            <label class="address-form__label">Tên người nhận</label>
                            <input type="text" name="recipient_name" autocomplete="off" class="address-form__input" placeholder="Nhập tên người nhận" required>
                            </div>
                            <div class="address-form__group">
                            <label class="address-form__label">Số điện thoại</label>
                            <input type="text" name="phone" class="address-form__input" autocomplete="off" placeholder="Nhập số điện thoại" required>
                            </div>
                            <div class="address-form__group">
                            <label class="address-form__label">Chọn tỉnh/thành phố</label>
                            <select name="city" class="address-form__input" required>
                                <option value="">Chọn tỉnh/thành phố</option>
                                <?php foreach ($vietnam_cities as $city) { ?>
                                    <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                <?php } ?>
                            </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Chọn quận/huyện</label>
                                <select name="district" class="address-form__input" required>
                                <option value="">Chọn quận/huyện</option>
                                </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Phường/Xã</label>
                                <select name="commune" class="address-form__input" required>
                                <option value="">Phường/Xã</option>
                                </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Địa chỉ</label>
                                <input type="text" name="address" autocomplete="off" class="address-form__input" placeholder="Nhập địa chỉ cụ thể" required>
                            </div>
                            <button type="submit" name="kam_save_address" class="address-form__submit">Thêm địa chỉ</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>    
        </div>
    <?php
}

add_action('wp_footer', 'kam_add_popup_update_address_myaccount');
function kam_add_popup_update_address_myaccount () {
    if (!is_account_page()) {
        return;
    }
    $db = new KTech_Address_DB();
    $user_id = get_current_user_id();
    $saved_addresses = $db->get_by_user($user_id);

    global $vietnam_cities;
    ?>
        <div id="kam-update-address-myaccount-popup" style="display: none;">
            <div class="edit-addresses-section">
                <?php if ($user_id) : ?>        
                    <!-- Form thêm/sửa địa chỉ -->
                    <div class="address-form">
                        <h2 class="address-form__title">Thay đổi địa chỉ</h2>
                        <form id="kam_edit_address" data-location="checkout">
                            <div class="address-form__group address-form__group--full address-type-wrapper">
                                <input type="radio" id="popup_address-type-home" name="address_type" value="Nhà riêng" required>
                                <label for="popup_address-type-home"  class="btn-add-address-type">Nhà riêng</label>
                                <input type="radio" id="popup_address-type-office" name="address_type" value="Văn phòng" required>
                                <label for="popup_address-type-office"  class="btn-add-address-type">Văn phòng</label>
                            </div>
                            <?php wp_nonce_field('kam_edit_address_action', 'kam_edit_address_nonce'); ?>
                            <input type="hidden" name="address_id" >
                            <div class="address-form__group">
                            <label class="address-form__label">Tên người nhận</label>
                            <input type="text" name="recipient_name" autocomplete="off" class="address-form__input" placeholder="Nhập tên người nhận" required>
                            </div>
                            <div class="address-form__group">
                            <label class="address-form__label">Số điện thoại</label>
                            <input type="text" name="phone" class="address-form__input" autocomplete="off" placeholder="Nhập số điện thoại" required>
                            </div>
                            <div class="address-form__group">
                            <label class="address-form__label">Chọn tỉnh/thành phố</label>
                            <select name="city" class="address-form__input" required>
                                <option value="">Chọn tỉnh/thành phố</option>
                                <?php foreach ($vietnam_cities as $city) { ?>
                                    <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                <?php } ?>
                            </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Chọn quận/huyện</label>
                                <select name="district" class="address-form__input" required>
                                <option value="">Chọn quận/huyện</option>
                                </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Phường/Xã</label>
                                <select name="commune" class="address-form__input" required>
                                <option value="">Phường/Xã</option>
                                </select>
                            </div>
                            <div class="address-form__group">
                                <label class="address-form__label">Địa chỉ</label>
                                <input type="text" name="address" autocomplete="off" class="address-form__input" placeholder="Nhập địa chỉ cụ thể" required>
                            </div>
                            <button type="submit" name="kam_save_address" class="address-form__submit">Sửa địa chỉ</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>    
        </div>
    <?php
}

add_filter('woocommerce_is_purchasable', function($purchasable, $product) {
    $product_id = $product->get_id();
    $end_of_sale = get_post_meta( $product_id, '_end_of_sale', true );
    $end_of_sale_date = get_post_meta( $product_id, '_end_of_sale_date', true );
    $now = current_time('Y-m-d\TH:i');
    $is_expired = false;
    if ( $end_of_sale_date ) {
        $is_expired = ( strtotime($end_of_sale_date) < strtotime($now) );
    }
    if ( $end_of_sale === 'yes' && $is_expired ) {
        return false;
    }
    return $purchasable;
}, 10, 2);

add_filter('body_class', 'ktech_add_user_role_to_body_class');
if (!function_exists('ktech_add_user_role_to_body_class')) {
    function ktech_add_user_role_to_body_class($classes) {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;
            
            foreach ($user_roles as $role) {
                $classes[] = 'user-role-' . $role;
            }
        } else {
            $classes[] = 'user-role-ghost';
        }
        
        return $classes;
    }
}

add_action( 'woocommerce_cart_totals_before_order_total', 'ktech_add_row_decreased' );
function ktech_add_row_decreased () {
    $total_reduced = 0;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['amount_reduced'] ) ) {
            $total_reduced += $cart_item['amount_reduced'] * $cart_item['quantity'];
        }
    }
    if ( $total_reduced > 0 ) {
        ?>
        <tr class="cart-subtotal">
            <th><?php esc_html_e( 'Số tiền đã giảm', 'woocommerce' ); ?></th>
            <td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                <span class="woocommerce-Price-amount amount"><bdi><?php echo wc_price( $total_reduced ); ?></bdi></span>
            </td>
        </tr>
        <?php
    }
}

add_action( 'woocommerce_after_add_to_cart_quantity', 'ktech_total_price_product_single_page_form' );
if (!function_exists('ktech_total_price_product_single_page_form')) {
    function ktech_total_price_product_single_page_form() {
        global $product;

        if ( ! $product ) return;
        
        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role  = array_shift($roles);
        $price = (float) $product->get_price();
        $roles_setting = get_option('ktech_account_roles_setting', []);

        $price_master = get_post_meta( $product->get_id(), '_master_price', true );
        $price_pharmer = get_post_meta( $product->get_id(), '_pharmer_price', true );

        if ($role != 'master' && $role != 'pharmer_seller' && $role != 'pharmer') {
            if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['price_type_avaliable']) {
                $price_type_avaliable = $roles_setting[$role]['price_type_avaliable'];
                if ($price_type_avaliable == 'pharmer') {
                    $role = 'pharmer';
                }
                if ($price_type_avaliable == 'master') {
                    $role = 'master';
                }
            }
        }

        if ($role === 'master' && !empty($price_master)) {
            $price = $price_master;
        }

        if (($role === 'pharmer' || $role === 'pharmer_seller') && !empty($price_pharmer)) {
            $price = $price_pharmer;
        }

        if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['discount']) {
            $discount_for_role = floatval($roles_setting[$role]['discount']);
            if ($discount_for_role > 0 && $discount_for_role < 100) {
                $price = $price * (1 - $discount_for_role / 100);
            }
        }

        ?>
        <div class="total-price-wrapper" data-product_price_total="<?php echo $price; ?>">
            <span>TỔNG TIỀN</span>
            <span class="total-price-wrapper-number"><?php echo wc_price($price); ?></span>
        </div>
        <?php
    }
}

add_action('add_meta_boxes', function() {
    add_meta_box(
        'ktech_registered_students',
        'Danh sách học viên đã đăng ký',
        'ktech_show_registered_students_meta_box',
        'training_post',
        'normal',
        'default'
    );
});

function ktech_show_registered_students_meta_box($post) {
    // Giả sử post type 'product' là khoá học
    global $wpdb;
    $registered_member_id = get_post_meta($post->ID, 'registered_members', true) ?: [];

    $ids = implode(',', $registered_member_id);

    $query = "SELECT display_name, user_email FROM {$wpdb->prefix}users WHERE ID IN ($ids)";
    $members = $wpdb->get_results($query);

    if (empty($members)) {
        echo '<p>Chưa có học viên đăng ký khoá học này.</p>';
        return;
    }

    echo '<table class="widefat"><thead><tr><th>Họ tên</th><th>Email</th></tr></thead><tbody>';
    foreach ($members as $member) {
        echo '<tr>';
        echo '<td>' . esc_html($member->display_name) . '</td>';
        echo '<td>' . esc_html($member->user_email) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}