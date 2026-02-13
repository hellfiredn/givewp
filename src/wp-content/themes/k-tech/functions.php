<?php
add_action('wp_enqueue_scripts', 'k_tech_style');
function k_tech_style() {
        // CSS
    wp_enqueue_style(
        'daterangepicker-css',
        'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
        array(),
        null
    );

    // Moment.js
    wp_enqueue_script(
        'moment-js',
        'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
        array('jquery'),
        null,
        true
    );

    // Date Range Picker
    wp_enqueue_script(
        'daterangepicker-js',
        'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
        array('jquery', 'moment-js'),
        null,
        true
    );
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));

    wp_enqueue_style('long-style', get_stylesheet_directory_uri() . '/assets/css/style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/style.css'), 'all');
    wp_enqueue_style('header-style', get_stylesheet_directory_uri() . '/assets/css/header.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/header.css'), 'all');
    wp_enqueue_style('my-account-style', get_stylesheet_directory_uri() . '/assets/css/my-account.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/my-account.css'), 'all');

    //js
    wp_enqueue_script(
        'main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        array('jquery', 'daterangepicker-js', 'moment-js'),
        filemtime(get_stylesheet_directory() . '/assets/js/main.js'),
        true
    );
    wp_localize_script(
        'main-js',
        'ajax_object',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );

}


include_once 'inc/template.php';
include_once 'admin/admin-template.php';
include_once 'functions/user-function.php';

add_filter( 'woocommerce_product_tabs', 'custom_product_tabs' );
function custom_product_tabs( $tabs ) {
    $product_id = get_the_ID();
    $tabs['dung_tich'] = array(
        'title'    => 'Dung tích',
        'priority' => 40,
        'callback' => function() {
            echo get_field('dung_tich', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['loai_da'] = array(
        'title'    => 'Loại da',
        'priority' => 41,
        'callback' => function() {
           echo get_field('loai_da', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['cach_su_dung'] = array(
        'title'    => 'Cách sử dụng',
        'priority' => 42,
        'callback' => function() {
            echo get_field('cach_su_dung', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['thanh_phan_chinh'] = array(
        'title'    => 'Thành phần chính',
        'priority' => 43,
        'callback' => function() {
            echo get_field('thanh_phan_chinh', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['thanh_phan_chi_tiet'] = array(
        'title'    => 'Thành phần chi tiết',
        'priority' => 44,
        'callback' => function() {
            echo get_field('thanh_phan_chi_tiet', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['thong_tin_nha_san_xuat'] = array(
        'title'    => 'Thông tin nhà sản xuất',
        'priority' => 45,
        'callback' => function() {
            echo get_field('thong_tin_nha_san_xuat', $product_id) ?: 'Chưa có thông tin';
        }
    );

    $tabs['luu_y_khi_mua_hang_va_su_dung'] = array(
        'title'    => 'Lưu ý khi mua hàng và sử dụng',
        'priority' => 46,
        'callback' => function() {
            echo get_field('luu_y_khi_mua_hang_va_su_dung', $product_id) ?: 'Chưa có thông tin';
        }
    );

    return $tabs;
}

function share_social() {

    ob_start();
    $social = get_field('social', 'option');
    ?>

    <ul class="share_social default_ul">
        <?php foreach ($social as $key => $item) {
            ?>
            <li><a target="_blank" href="<?php echo $item['link'] ?>">
                    <?php echo wp_get_attachment_image($item['icon']['ID'], 'full', "", array("class" => "")); ?>


                </a></li>
            <?php
        } ?>
    </ul>
    <?php

    return ob_get_clean();

}


add_shortcode('share_social', 'share_social');

function shortcode_woocommerce_product_tabs() {
    if ( ! is_product() ) return '';

    ob_start();
    do_action( 'woocommerce_after_single_product_summary' );
    return ob_get_clean();
}
add_shortcode('woo_product_tabs', 'shortcode_woocommerce_product_tabs');

if (!function_exists('k_tech_custom_vnd_currency_symbol')) {
    add_filter( 'woocommerce_currency_symbol', 'k_tech_custom_vnd_currency_symbol', 10, 2 );
    function k_tech_custom_vnd_currency_symbol( $currency_symbol, $currency ) {
        if ( $currency === 'VND' ) {
            $currency_symbol = 'VNĐ';
        }
        return $currency_symbol;
    }
}

if (!function_exists('k_tech_zalo_chat_button')) {
    add_action( 'wp_footer', 'k_tech_zalo_chat_button' );
    function k_tech_zalo_chat_button() {
        ?>
        <div id="zalo-chat-button">
            <a href="https://zalo.me/2619810007423081795" target="_blank" rel="noopener">
                <img src="/wp-content/uploads/2025/08/zalo-icon.png" alt="Chat Zalo" />
            </a>
        </div>
        <?php
    }
}


add_shortcode( 'product_category_name', 'get_product_category_name_shortcode' );
function get_product_category_name_shortcode( $atts ) {
    global $post;

    $atts = shortcode_atts( array(
        'id' => $post ? $post->ID : 0,
    ), $atts, 'product_category_name' );

    $product_id = intval( $atts['id'] );
    if ( ! $product_id ) return '';

    $terms = wp_get_post_terms( $product_id, 'product_cat' );

    if ( !empty($terms) && !is_wp_error($terms) ) {
        return esc_html( $terms[0]->name );
    }

    return '';
}

add_filter( 'gettext', 'custom_cart_button_text', 20, 3 );
function custom_cart_button_text( $translated, $original, $domain ) {
    if ( $original === 'Proceed to checkout' && $domain === 'woocommerce' ) {
        $translated = 'Đặt hàng';
    }
    return $translated;
}

add_action('wlfmc_table_end_center_column', 'ktech_wishlist_my_account_action_item', 3, 10);
if (!function_exists('ktech_wishlist_my_account_action_item')) {
    function ktech_wishlist_my_account_action_item ( $item, $wishlist, $atts) {
        ?>
        <div class="wishlist-items-actions">
            <a href="/?add-to-cart=<?php echo esc_attr($item['product_id']); ?>" 
            class="button add_to_cart_button ajax_add_to_cart" 
            data-product_id="<?php echo esc_attr($item['product_id']); ?>" 
            rel="nofollow">
            </a>
            <a href="<?php echo esc_url(add_query_arg('remove_from_wishlist', $item['product_id'], wlfmc_get_current_url())); ?>" 
                class="button remove_from_wishlist" 
                data-nonce="<?php echo esc_attr(wp_create_nonce('wlfmc_remove_from_wishlist')); ?>"
                title="<?php esc_attr_e('Remove this product', 'wc-wlfmc-wishlist'); ?>"
                aria-label="<?php esc_attr_e('Remove Product', 'wc-wlfmc-wishlist'); ?>">
                Bỏ thích
            </a>
        </div>
        <?php
    }
}

add_filter( 'wlfmc_no_access_image', function ($atts) { return ''; }, 1 , 20);
add_filter( 'wlfmc_no_product_in_wishlist_image', function ($atts) { return ''; }, 1 , 20);
add_filter( 'wlfmc_no_access_title', function ($atts) { 
    return '<div class="wc-empty-cart-message">
	<div class="woocommerce-info message-wrapper">
		<div class="message-container container medium-text-center">
            Chưa có sản phẩm trong danh sách yêu thích
        </div>
	</div>
	</div>';
});
add_filter( 'wlfmc_no_product_in_wishlist_title', function ($atts) { 
    return '<div class="wc-empty-cart-message">
	<div class="woocommerce-info message-wrapper">
		<div class="message-container container medium-text-center">
            Chưa có sản phẩm trong danh sách yêu thích
        </div>
	</div>
	</div>';
});
add_filter( 'wlfmc_no_access_message', function ($atts) { return ''; });
add_filter( 'wlfmc_no_product_in_wishlist_message', function ($atts) { return ''; });
add_filter( 'wlfmc_no_access_button', function ($atts) { 
    // $related_ids = get_posts( array(
    // 'post_type'      => 'product',
    // 'posts_per_page' => 4,
    // 'orderby'        => 'date',
    // 'order'          => 'DESC',
    // 'fields'         => 'ids',
	// ) );

    // $html = '';
	// if ( ! empty( $related_ids ) ) {
    //     $related_products = wc_get_products( array(
    //             'include' => $related_ids,
    //     ) );

    //     $html.='<div class="related related-products-wrapper related-product-shortcode">';
    //     $html.='<h3 class="product-section-title container-width product-section-title-related pt-half pb-half">';
    //     $html.='</h3>';

    //     $html.='<ul class="products related-products">';
    //         foreach ( $related_products as $related_product ) {
    //             $post_object = get_post( $related_product->get_id() );
    //             setup_postdata( $GLOBALS['post'] = $post_object );
    //             ob_start();
    //             wc_get_template_part( 'content', 'product' );
    //             $html .= ob_get_clean();
    //         }
    //     $html.='</ul>';
    //     $html.='</div>';
    //     wp_reset_postdata();
	// }
    // return $html; 
    return '';
});
add_filter( 'wlfmc_no_product_in_wishlist_button', function ($atts) {
    // $related_ids = get_posts( array(
    // 'post_type'      => 'product',
    // 'posts_per_page' => 4,
    // 'orderby'        => 'date',
    // 'order'          => 'DESC',
    // 'fields'         => 'ids',
	// ) );

    // $html = '';
	// if ( ! empty( $related_ids ) ) {
    //     $related_products = wc_get_products( array(
    //             'include' => $related_ids,
    //     ) );

    //     $html.='<div class="related related-products-wrapper related-product-shortcode">';
    //     $html.='<h3 class="product-section-title container-width product-section-title-related pt-half pb-half">';
    //     $html.=esc_html__( 'Sản phẩm liên quan', 'woocommerce' );
    //     $html.='</h3>';

    //     $html.='<ul class="products related-products">';
    //     foreach ( $related_products as $related_product ) {
    //             $post_object = get_post( $related_product->get_id() );
    //             setup_postdata( $GLOBALS['post'] = $post_object );
    //             ob_start();
    //             wc_get_template_part( 'content', 'product' );
    //             $html .= ob_get_clean();
    //     }
    //     $html.='</ul>';
    //     $html.='</div>';
    //     wp_reset_postdata();
	// }
    // return $html; 
    return '';
});

// === REF CODE TRACKING SYSTEM ===
// 1. Lưu ref_code từ URL vào session khi user truy cập
add_action('wp', 'ktech_capture_ref_code');
function ktech_capture_ref_code() {
    if (isset($_GET['ref_code']) && !empty($_GET['ref_code'])) {
        if (!session_id()) session_start();
        setcookie('ref_code', sanitize_text_field($_GET['ref_code']), time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
    }
}

// 2. Lưu ref_code vào meta đơn hàng khi user đặt hàng
add_action('woocommerce_checkout_update_order_meta', 'ktech_save_ref_code_to_order');
function ktech_save_ref_code_to_order($order_id) {
    if (isset($_COOKIE['ref_code'])) {
        update_post_meta($order_id, '_ref_code_link', sanitize_text_field($_COOKIE['ref_code']));
    }
    setcookie('ref_code', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
}

add_action('woocommerce_checkout_create_order', function($order, $data) {
    $total_reduced = 0;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (isset($cart_item['amount_reduced'])) {
            $total_reduced += $cart_item['amount_reduced'] * $cart_item['quantity'];
        }
    }
    $order->update_meta_data('_total_reduced', $total_reduced);
}, 10, 2);

add_action('wp_ajax_send_voucher_email', 'ktech_send_voucher_email');
add_action('wp_ajax_nopriv_send_voucher_email', 'ktech_send_voucher_email');
function ktech_send_voucher_email() {
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    if (!$user_info) {
        wp_send_json_error('Không tìm thấy user');
    }
    $voucher_db = new KTech_Voucher_DB();
    $voucher_obj = $voucher_db->get_by_id($_POST['voucher_id']);

    if (!$voucher_obj) {
        wp_send_json_error('Không tìm thấy voucher');
    }

    $to = $user_info->user_email;
    $subject = 'Bạn vừa nhận được voucher!';
    $message = 'Xin chào ' . $user_info->display_name . ",\n\n";
    $message .= 'Bạn vừa nhận được voucher: ' . $voucher_obj->title . "\n";
    $message .= 'Mã voucher: ' . $voucher_obj->voucher_code . "\n";
    $message .= 'Hạn sử dụng: ' . $voucher_obj->expiry_date . "\n";
    $message .= 'Mô tả: ' . $voucher_obj->description . "\n";
    $message .= "<br>Chúc bạn mua sắm vui vẻ!";

    $sent = wp_mail($to, $subject, $message);

    if ($sent) {
        wp_send_json_success('Voucher đã được gửi về email của bạn!');
    } else {
        wp_send_json_error('Gửi thất bại, thử lại sau.');
    }
}

add_action('wp_ajax_ktech_get_order_detail', function() {
    if (!isset($_POST['order_id'])) {
        wp_send_json_error('Thiếu order_id');
    }
    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);
    if (!$order) {
        echo 'Không tìm thấy đơn hàng.';
        wp_die();
    }
    $current_user_id = get_current_user_id();
    if ($order->get_customer_id() != $current_user_id) {
        echo 'Bạn không có quyền xem đơn hàng này.';
        wp_die();
    }
    echo '<div class="checkout-cart-summary">';
    
    $current_user = wp_get_current_user();
    $roles = $current_user->roles;
    $role = array_shift($roles);
    $is_reduce_price = false;
    $roles_setting = get_option('ktech_account_roles_setting', []);
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
    if ($role === 'master' || $role === 'pharmer' || $role === 'pharmer_seller') {
        $is_reduce_price = true;
    }
    
    foreach ( $order->get_items() as $item_id => $item ) {
        $product   = $item->get_product();
        $quantity  = $item->get_quantity();
        $product_id = $product->get_id();
        $product_price = get_post_meta( $product_id, '_price', true );
        $price_origin_numeric = $product_price * $quantity;
        $price     = wc_price( $item->get_subtotal() );
        $price_number     = floatval(str_replace('.', '', preg_replace('/[^\d.]/', '', $price)));
        $price_origin = wc_price($price_origin_numeric);

        $thumbnail = $product->get_image( 'woocommerce_thumbnail' );
        $name      = $product->get_name();
        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        $category_name = '';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $category_name = $terms[0]->name;
        }

        echo '<div class="cart-item">';
        echo '<div class="cart-item-thumbnail">' . $thumbnail . '</div>';
        echo '<div class="cart-item-info">';
        echo '<div class="cart-item-name">
            <span>' . esc_html( $category_name ) . '</span>
            <h3>' . esc_html( $name ) . '</h3>
            <p>' . apply_filters('woocommerce_short_description', $product->get_short_description()) . '</p>
        </div>';
        echo '<div class="cart-item-quantity"><span>Số lượng</span><span>' . $quantity . '</span></div>';
        echo '<div class="cart-item-price">';
        echo '<span>Giá hiện tại</span>';
        echo '<span>';
        if (!empty($price_origin) && $price_origin_numeric > $price_number) {
            echo '<del class="price-reduced">' . $price_origin . '</del><br />';
        }
        echo $price;
        echo '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';

    $total_reduced = $order->get_meta('_total_reduced');

    echo '<div class="checkout-order-review thankyou-order-review">';
    echo '<div class="checkout-order-review-row">';
    echo '<span>Số lượng đặt hàng</span>';
    echo '<span>' . $order->get_item_count() . '</span>';
    echo '</div>';
    echo '<div class="checkout-order-review-row">';
    echo '<span>Giảm giá</span>';
    echo '<span>' . ($total_reduced > 0 ? wc_price($total_reduced) : '0 VND') . '</span>';
    echo '</div>';
    echo '<div class="checkout-order-review-row">';
    echo '<span>Phí vận chuyển</span>';
    echo '<span>Phí vận chuyển sẽ được thông báo sau thông qua zalo của quý khách</span>';
    echo '</div>';
    echo '<div class="checkout-order-review-row-total">';
    echo '<span>Tổng tiền</span>';
    echo '<span>' . wc_price( $order->get_total() ) . '</span>';
    echo '</div>';
    echo '</div>';
    
    wp_die();
});
add_action('wp_ajax_nopriv_ktech_get_order_detail', function() {
    echo 'Vui lòng đăng nhập để xem chi tiết đơn hàng.';
    wp_die();
});

// Popup hiển thị chi tiết đơn hàng
add_action('wp_footer', function () {
    echo '<div id="order-view-popup-my-account">
        <div>
            <button type="button" id="close-order-view-popup">&times;</button>
            <div id="order-view-popup-content">Đang tải...</div>
        </div>
    </div>';

    echo "<script>
        jQuery(document).ready(function($){
            $('.order-view-popup-btn').on('click', function(e){
                e.preventDefault();
                var orderId = $(this).data('order-id');
                $('#order-view-popup-my-account').css('display','flex');
                $('#order-view-popup-content').html('Đang tải...');
                $.ajax({
                    url: '". admin_url('admin-ajax.php') ."',
                    type: 'POST',
                    data: {
                        action: 'ktech_get_order_detail',
                        order_id: orderId
                    },
                    success: function(res){
                        $('#order-view-popup-content').html(res);
                    },
                    error: function(){
                        $('#order-view-popup-content').html('Lỗi tải dữ liệu!');
                    }
                });
            });
            $('#close-order-view-popup').on('click', function(){
                $('#order-view-popup-my-account').hide();
            });
        });
    </script>";
});

add_action('template_redirect', 'ktech_redirect_logged_in_account_page');
function ktech_redirect_logged_in_account_page() {
    if (is_user_logged_in() && is_account_page() && !is_wc_endpoint_url()) {
        global $wp;
        if ($wp->request == 'tai-khoan') {
            wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
            exit;
        }
    }
}

// Popup đăng ký Pharmer cho khách chưa đăng nhập ở trang checkout
add_action('wp_footer', function() {
    if (is_checkout() && !is_user_logged_in()) {
        ?>
        <div id="ktech-checkout-register-popup" style="display:none;">
            <div class="ktech-popup-overlay"></div>
            <div class="ktech-popup-content">
                <button type="button" class="ktech-popup-close">&times;</button>
                <h2>Đăng ký VIP Member để nhận giá ưu đãi</h2>
                <p>Đăng ký tài khoản VIP Member ngay để nhận được mức giá ưu đãi tốt nhất cho đơn hàng của bạn!</p>
                <a href="/dang-ky" class="ktech-popup-btn">Đăng ký VIP Member</a>
            </div>
        </div>
        <style>
            #ktech-checkout-register-popup {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 999999;
            }
            .ktech-popup-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
            }
            .ktech-popup-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #fff;
                padding: 40px;
                border-radius: 8px;
                max-width: 500px;
                width: 90%;
                text-align: center;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            }
            .ktech-popup-close {
                position: absolute;
                top: 10px;
                right: 10px;
                margin: 0;
                background: transparent;
                border: none;
                font-size: 30px;
                cursor: pointer;
                color: #999;
                line-height: 1;
                padding: 0;
                width: 30px;
                height: 30px;
                min-height: unset;
            }
            .ktech-popup-close:hover {
                color: #333;
            }
            .ktech-popup-content h2 {
                margin: 0 0 15px;
                font-size: 24px;
                color: #333;
            }
            .ktech-popup-content p {
                margin: 0 0 25px;
                color: #666;
                line-height: 1.6;
            }
            .ktech-popup-btn {
                display: inline-block;
                padding: 12px 30px;
                background: var(--fs-color-primary);
                border: 1px solid var(--fs-color-primary);
                color: #fff;
                text-decoration: none;
                font-weight: 600;
                transition: background 0.3s;
            }
            .ktech-popup-btn:hover {
                background: #fff;
                color: var(--fs-color-primary);
            }
        </style>
        <script>
            jQuery(document).ready(function($) {
                // Check if user has closed popup in this session
                if (!sessionStorage.getItem('ktech_checkout_popup_closed')) {
                    setTimeout(function() {
                        $('#ktech-checkout-register-popup').fadeIn();
                    }, 1000);
                }
                
                // Close popup
                $('.ktech-popup-close, .ktech-popup-overlay').on('click', function() {
                    $('#ktech-checkout-register-popup').fadeOut();
                    sessionStorage.setItem('ktech_checkout_popup_closed', 'true');
                });
                
                // Prevent closing when clicking inside popup content
                $('.ktech-popup-content').on('click', function(e) {
                    e.stopPropagation();
                });
            });
        </script>
        <?php
    }
});

// add_filter('woocommerce_cart_needs_shipping', '__return_false');

// Ajax handler validate register form
// add_action('wp_ajax_validate_register_form', 'validate_register_form_ajax');
// add_action('wp_ajax_nopriv_validate_register_form', 'validate_register_form_ajax');

// function validate_register_form_ajax() {
//     $username = sanitize_user($_POST['username']);
//     $email = sanitize_email($_POST['email']);
//     $ref_code_submitted = sanitize_text_field($_POST['ref_code']);
    
//     $errors = array();
    
//     // Validate ref code exists
//     if (empty($ref_code_submitted)) {
//         $errors[] = 'Mã giới thiệu là bắt buộc.';
//     } else {
//         // Kiểm tra ref code có tồn tại không
//         $ref_user_query = new WP_User_Query(array(
//             'meta_key'   => 'my_ref_code',
//             'meta_value' => $ref_code_submitted,
//             'number'     => 1,
//             'fields'     => 'all',
//             'role__in'   => array('master', 'pharmer_seller'),
//         ));
//         $ref_user = !empty($ref_user_query->get_results()) ? $ref_user_query->get_results()[0] : false;
//         if (!$ref_user) {
//             $errors[] = 'Mã giới thiệu không tồn tại.';
//         }
//     }
    
//     if (username_exists($username) || email_exists($email)) {
//         $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
//     }
    
//     if (empty($errors)) {
//         wp_send_json_success();
//     } else {
//         wp_send_json_error(array('errors' => $errors));
//     }
// }

// Chỉ cho phép người dùng đã mua sản phẩm mới được đánh giá
// Thêm class vào body nếu user đã mua sản phẩm
add_filter('body_class', function($classes) {
    if (is_product()) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            // Try to get product ID from global $product or from the query
            global $product;
            if (is_object($product) && method_exists($product, 'get_id')) {
                $product_id = $product->get_id();
            } else {
                $product_id = get_the_ID();
            }
            $has_bought = $product_id ? wc_customer_bought_product('', $user_id, $product_id) : false;
            $classes[] = $has_bought ? 'user-buyed' : 'user-not-buy';
        } else {
            $classes[] = 'user-not-buy';
        }
    }
    return $classes;
});


// Hook vào action 'wp' để kiểm tra quyền truy cập bài viết khi trang được tải
add_action('wp', function() {
    // Kiểm tra nếu đây là trang chi tiết của một bài viết (post)
    if (is_single()) {
        global $post;
        // Đảm bảo biến $post tồn tại, có ID và là loại 'post'
        if ($post && isset($post->ID) && $post->post_type === 'post') {
            // Lấy meta 'allowed_account_roles' chứa danh sách role được phép xem bài viết
            $allowed_roles = get_post_meta($post->ID, 'allowed_account_roles', true);
            // Nếu meta không tồn tại, rỗng hoặc không phải mảng, chặn truy cập
            if (empty($allowed_roles) || !is_array($allowed_roles)) {
                wp_die('Bạn không có quyền xem bài viết này.', '', array('back_link' => true));
            }
            // Nếu người dùng chưa đăng nhập, chặn truy cập
            if (!is_user_logged_in()) {
                if (in_array('guest', $allowed_roles)) {
                    return;
                } else {
                    wp_die('Bạn không có quyền xem bài viết này.', '', array('back_link' => true));
                }
            }
            // Lấy danh sách role của người dùng hiện tại
            $current_user = wp_get_current_user();
            $user_roles = (array) $current_user->roles;
            // Nếu là admin thì luôn được xem
            if (in_array('administrator', $user_roles)) {
                return;
            }
            $has_role = false;
            // Kiểm tra xem người dùng có role nào nằm trong danh sách được phép không
            foreach ($allowed_roles as $role) {
                if (in_array($role, $user_roles)) {
                    $has_role = true;
                    break;
                }
            }
            // Nếu không có role phù hợp, hiển thị cảnh báo và quay lại trang trước
            if (!$has_role) {
                echo '<script>alert("Bạn không có quyền xem bài viết này.");window.history.back();</script>';
                exit;
            }
        }
    }
});

// Lọc bài viết trong query dựa trên quyền của user
add_filter('pre_get_posts', function($query) {
    if (!is_admin() && $query->get('post_type') === 'post') {
        // Lấy tất cả posts để kiểm tra quyền
        add_filter('posts_where', function ($where, $query) {
            global $wpdb;

            remove_filter('posts_where', 'filter_posts_by_role', 10);
                
            // Lấy role của user hiện tại
            $current_user_roles = array();
            $is_admin = false;
            
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                $current_user_roles = (array) $current_user->roles;
                // Admin được xem tất cả
                if (in_array('administrator', $current_user_roles)) {
                    $is_admin = true;
                }
            }
            
            // Nếu là admin thì không cần filter
            if (!$is_admin) {
                // Nếu không có user hoặc không có role, loại bỏ tất cả posts có meta 'allowed_account_roles'
                if (empty($current_user_roles)) {
                    $where .= " AND ( ";
                    $where .= "{$wpdb->posts}.ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'allowed_account_roles') ";
                    $where .= "OR {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'allowed_account_roles' AND (meta_value LIKE '%guest%' OR meta_value LIKE 'a:%%guest%%;%%'))";
                    $where .= ")";
                } else {
                    // Lấy danh sách post IDs mà user có quyền xem
                    $allowed_post_ids = array(0); // Thêm 0 để tránh SQL error
                    
                    // Query tất cả posts có meta 'allowed_account_roles'
                    $posts_with_roles = $wpdb->get_results(
                        "SELECT post_id, meta_value FROM {$wpdb->postmeta} 
                        WHERE meta_key = 'allowed_account_roles'"
                    );
                    
                    foreach ($posts_with_roles as $post_meta) {
                        $allowed_roles = maybe_unserialize($post_meta->meta_value);
                        if (is_array($allowed_roles)) {
                            // Kiểm tra nếu user có role phù hợp
                            foreach ($allowed_roles as $role) {
                                if (in_array($role, $current_user_roles)) {
                                    $allowed_post_ids[] = $post_meta->post_id;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Chỉ hiển thị posts mà user có quyền hoặc posts không có meta 'allowed_account_roles'
                    $allowed_ids = implode(',', $allowed_post_ids);
                    $where .= " AND (
                        {$wpdb->posts}.ID IN ($allowed_ids)
                        OR {$wpdb->posts}.ID NOT IN (
                            SELECT post_id FROM {$wpdb->postmeta} 
                            WHERE meta_key = 'allowed_account_roles'
                        )
                    )";
                }
            }
            
            return $where;
        }, 10, 2);
    }
    return $query;
}, 10, 1);



/**
 * Hook vào sự kiện đăng nhập (wp_login) để xử lý giỏ hàng WooCommerce.
 *
 * - Khi người dùng đăng nhập, hàm này sẽ xóa session giỏ hàng hiện tại.
 * - Sau đó, nếu user meta có lưu trữ giỏ hàng trước đó (_woocommerce_persistent_cart_), 
 *   sẽ nạp lại các sản phẩm vào giỏ hàng hiện tại.
 */
add_action('wp_login', function($user_login, $user) {
    // Xóa cart session hiện tại
    WC()->cart->empty_cart();
    // Nạp lại cart từ user meta (nếu có)
    $saved_cart = get_user_meta($user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true);
    if (!empty($saved_cart['cart'])) {
        foreach ($saved_cart['cart'] as $cart_item_key => $values) {
            WC()->cart->add_to_cart(
                $values['product_id'],
                $values['quantity'],
                $values['variation_id'],
                $values['variation'],
                $values['cart_item_data']
            );
        }
    }
}, 10, 2);

// add_shortcode('read_customers_csv', function() {
//     $file = WP_CONTENT_DIR . '/uploads/customer.csv';
//     if (!file_exists($file)) {
//         return '<p>Không tìm thấy file customer.csv.</p>';
//     }

//     $output = '';
//     $csv_data = array();
//     $success_count = 0;
//     $error_count = 0;
//     $errors = array();
    
//     if (($handle = fopen($file, 'r')) !== false) {
//         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
//             $csv_data[] = $data;
//         }
//         fclose($handle);
        
//         // Import users from CSV data
//         foreach ($csv_data as $index => $row) {
//             // Helper: convert 'NULL' or empty to ''
//             $get_val = function($val) {
//                 return (strtoupper(trim($val)) === 'NULL' || trim($val) === '') ? '' : $val;
//             };

//             $full_name = sanitize_text_field($get_val($row[0]));
//             $phone = sanitize_text_field($get_val($row[1]));
//             $email = sanitize_email($get_val($row[2]));
//             $cccd = sanitize_text_field($get_val($row[3]));
//             $cccd_date = sanitize_text_field($get_val($row[4]));
//             $cccd_location = sanitize_text_field($get_val($row[5]));
//             $sex = sanitize_text_field($get_val($row[6]));
//             $my_ref = sanitize_text_field($get_val($row[7]));
//             $ref_by = sanitize_text_field($get_val($row[8]));
//             $status = sanitize_text_field($get_val($row[9]));
//             $password = '123123';

//             // Validate required fields
//             if (empty($email)) {
//                 $errors[] = "Row " . ($index + 1) . ": Email là bắt buộc";
//                 $error_count++;
//                 continue;
//             }

//             if (empty($my_ref)) {
//                 $errors[] = "Row " . ($index + 1) . ": Mã giới thiệu là bắt buộc";
//                 $error_count++;
//                 continue;
//             }
            
//             // Check if user already exists
//             if (email_exists($email)) {
//                 $errors[] = "Row " . ($index + 1) . ": Email {$email} đã tồn tại";
//                 $error_count++;
//                 continue;
//             }
            
//             // Generate username from email if not provided
//             $username = sanitize_user($my_ref);
//             if (username_exists($username)) {
//                 continue;
//             }
            
//             // Create user
//             $user_id = wp_create_user($username, $password, $email);
            
//             if (is_wp_error($user_id)) {
//                 $errors[] = "Row " . ($index + 1) . ": " . $user_id->get_error_message();
//                 $error_count++;
//                 continue;
//             }
            
//             // Update user data
//             wp_update_user(array(
//                 'ID' => $user_id,
//                 'first_name' => $full_name,
//                 'last_name' => '',
//                 'display_name' => $full_name,
//             ));
            
//             // Set user role based on ref_by_code
//             $role = 'pharmer'; // Default role
//             if (!empty($ref_by_code)) {
//                 if (strtolower($ref_by_code) === 'masterlv0') {
//                     $role = 'master';
//                 } elseif (strtolower($ref_by_code) === 'masterlv1') {
//                     $role = 'pharmer';
//                 }
//             }
            
//             $user = new WP_User($user_id);
//             $user->set_role($role);
            
//             // Update user meta
//             if ($phone !== 'NULL' && !empty($phone)) {
//                 update_user_meta($user_id, 'billing_phone', $phone);
//                 update_user_meta($user_id, 'custom_phone', $phone);
//             }

//             if ($full_name !== 'NULL' && !empty($full_name)) {
//                 update_user_meta($user_id, 'custom_full_name', $full_name);
//             }
            
//             if ($email !== 'NULL' && !empty($email)) {
//                 update_user_meta($user_id, 'custom_email', $email);
//             }

//             if ($cccd !== 'NULL' && !empty($cccd)) {
//                 update_user_meta($user_id, 'tax_id_number', $cccd);
//             }
            
//             if ($cccd_date !== 'NULL' && !empty($cccd_date)) {
//                 update_user_meta($user_id, 'id_issue_date', $cccd_date);
//             }
            
//             if ($cccd_location !== 'NULL' && !empty($cccd_location)) {
//                 update_user_meta($user_id, 'id_issue_place', $cccd_location);
//             }
            
//             if ($sex !== 'NULL' && !empty($sex)) {
//                 if ($sex == 'Male') {
//                     $sex = 'Nam';
//                 }
//                 if ($sex == 'Female') {
//                     $sex = 'Nữ';
//                 }
//                 if ($sex == 'Other') {
//                     $sex = 'Khác';
//                 }
//                 update_user_meta($user_id, 'custom_gender', $sex);
//             }
            
//             if ($my_ref !== 'NULL' && !empty($my_ref)) {
//                 update_user_meta($user_id, 'my_ref_code', $my_ref);
//             }
            
//             if ($ref_by !== 'NULL' && !empty($ref_by)) {
//                 update_user_meta($user_id, 'ref_by', $ref_by);
//             }

//             if ($status !== 'NULL' && !empty($status)) {
//                 if ($status == '0') {
//                     $status = 'yes';
//                 }
//                 if ($status == '1') {
//                     $status = 'no';
//                 }
//                 update_user_meta($user_id, 'kam_locked', $status);
//             }
            
//             $success_count++;
//         }
        
//         // Display results
//         $output .= '<div style="padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">';
//         $output .= '<h2>Kết quả Import</h2>';
//         $output .= '<p><strong>Tổng số dòng:</strong> ' . count($csv_data) . '</p>';
//         $output .= '<p style="color: green;"><strong>Thành công:</strong> ' . $success_count . '</p>';
//         $output .= '<p style="color: red;"><strong>Lỗi:</strong> ' . $error_count . '</p>';
        
//         if (!empty($errors)) {
//             $output .= '<h3>Chi tiết lỗi:</h3>';
//             $output .= '<ul style="color: red;">';
//             foreach ($errors as $error) {
//                 $output .= '<li>' . esc_html($error) . '</li>';
//             }
//             $output .= '</ul>';
//         }
        
//         $output .= '</div>';
        
//     } else {
//         return '<p>Không thể đọc file customers.csv.</p>';
//     }
    
//     return $output;
// });
// --------------------------------------------------------------
// add_shortcode('read_customers_csv2', function() {
//     $file = WP_CONTENT_DIR . '/uploads/customer2.csv';
//     if (!file_exists($file)) {
//         return '<p>Không tìm thấy file customer.csv.</p>';
//     }

//     $output = '';
//     $csv_data = array();
//     $success_count = 0;
//     $update_count = 0;
//     $error_count = 0;
//     $errors = array();
    
//     if (($handle = fopen($file, 'r')) !== false) {
//         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
//             $csv_data[] = $data;
//         }
//         fclose($handle);
        
//         // Import users from CSV data
//         foreach ($csv_data as $index => $row) {
//             // Helper: convert 'NULL' or empty to ''
//             $get_val = function($val) {
//                 return (strtoupper(trim($val)) === 'NULL' || trim($val) === '') ? '' : $val;
//             };

//             $full_name = sanitize_text_field($get_val($row[0]));
//             $phone = sanitize_text_field($get_val($row[1]));
//             $email = sanitize_email($get_val($row[2]));
//             $cccd = sanitize_text_field($get_val($row[3]));
//             $cccd_date = sanitize_text_field($get_val($row[4]));
//             $cccd_location = sanitize_text_field($get_val($row[5]));
//             $sex = sanitize_text_field($get_val($row[6]));
//             $role_val = sanitize_text_field($get_val($row[7]));
//             $status = sanitize_text_field($get_val($row[8]));
//             $my_ref = sanitize_text_field($get_val($row[9]));
//             $ref_by = sanitize_text_field($get_val($row[10]));
//             $bod = sanitize_text_field($get_val($row[11]));
//             $password = '123123';

//             // Validate required fields
//             if (empty($email)) {
//                 $errors[] = "Row " . ($index + 1) . ": Email là bắt buộc";
//                 $error_count++;
//                 continue;
//             }

//             if (empty($my_ref)) {
//                 $errors[] = "Row " . ($index + 1) . ": Mã giới thiệu là bắt buộc";
//                 $error_count++;
//                 continue;
//             }
            
//             // Check if user already exists
//             $existing_user_id = email_exists($email);
            
//             if ($existing_user_id) {
//                 // Update existing user
//                 $user_id = $existing_user_id;
                
//                 // Update user data
//                 wp_update_user(array(
//                     'ID' => $user_id,
//                     'first_name' => $full_name,
//                     'last_name' => '',
//                     'display_name' => $full_name,
//                 ));
                
//                 $update_count++;
//             } else {
//                 // Generate username from email if not provided
//                 $username = sanitize_user($my_ref);
//                 if (username_exists($username)) {
//                     continue;
//                 }
                
//                 // Create user
//                 $user_id = wp_create_user($username, $password, $email);
                
//                 if (is_wp_error($user_id)) {
//                     $errors[] = "Row " . ($index + 1) . ": " . $user_id->get_error_message();
//                     $error_count++;
//                     continue;
//                 }
                
//                 // Update user data
//                 wp_update_user(array(
//                     'ID' => $user_id,
//                     'first_name' => $full_name,
//                     'last_name' => '',
//                     'display_name' => $full_name,
//                 ));
                
//                 $success_count++;
//             }
            
//             // Set user role based on ref_by
//             $role = 'pharmer'; // Default role
//             if (!empty($role_val)) {
//                 if (strtolower(trim($role_val)) == 'master') {
//                     $role = 'master';
//                 } elseif (strtolower(trim($role_val)) == 'pharmer') {
//                     $role = 'pharmer';
//                 } elseif (strtolower(trim($role_val)) == 'pharmer seller') {
//                     $role = 'pharmer_seller';
//                 }
//             }
            
//             $user = new WP_User($user_id);
//             $user->set_role($role);
            
//             // Update user meta
//             if (!empty($phone)) {
//                 update_user_meta($user_id, 'billing_phone', $phone);
//                 update_user_meta($user_id, 'custom_phone', $phone);
//             }

//             if (!empty($full_name)) {
//                 update_user_meta($user_id, 'custom_full_name', $full_name);
//             }
            
//             if (!empty($email)) {
//                 update_user_meta($user_id, 'custom_email', $email);
//             }

//             if (!empty($cccd)) {
//                 update_user_meta($user_id, 'tax_id_number', $cccd);
//             }
            
//             if (!empty($cccd_date)) {
//                 update_user_meta($user_id, 'id_issue_date', $cccd_date);
//             }
            
//             if (!empty($cccd_location)) {
//                 update_user_meta($user_id, 'id_issue_place', $cccd_location);
//             }
            
//             if (!empty($sex)) {
//                 if ($sex == 'Male') {
//                     $sex = 'Nam';
//                 }
//                 if ($sex == 'Female') {
//                     $sex = 'Nữ';
//                 }
//                 if ($sex == 'Other') {
//                     $sex = 'Khác';
//                 }
//                 update_user_meta($user_id, 'custom_gender', $sex);
//             }
            
//             if (!empty($my_ref)) {
//                 update_user_meta($user_id, 'my_ref_code', $my_ref);
//             }
            
//             if (!empty($ref_by)) {
//                 update_user_meta($user_id, 'ref_by', $ref_by);
//             }

//             if (!empty($status)) {
//                 if ($status == 'Inactive') {
//                     $status = 'yes';
//                 }
//                 if ($status == 'Active') {
//                     $status = 'no';
//                 }
//                 update_user_meta($user_id, 'kam_locked', $status);
//             }

//             if (!empty($bod)) {
//                 update_user_meta($user_id, 'custom_birthday', $bod);
//             }
//         }
        
//         // Display results
//         $output .= '<div style="padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">';
//         $output .= '<h2>Kết quả Import</h2>';
//         $output .= '<p><strong>Tổng số dòng:</strong> ' . count($csv_data) . '</p>';
//         $output .= '<p style="color: green;"><strong>Tạo mới thành công:</strong> ' . $success_count . '</p>';
//         $output .= '<p style="color: blue;"><strong>Cập nhật thành công:</strong> ' . $update_count . '</p>';
//         $output .= '<p style="color: red;"><strong>Lỗi:</strong> ' . $error_count . '</p>';
        
//         if (!empty($errors)) {
//             $output .= '<h3>Chi tiết lỗi:</h3>';
//             $output .= '<ul style="color: red;">';
//             foreach ($errors as $error) {
//                 $output .= '<li>' . esc_html($error) . '</li>';
//             }
//             $output .= '</ul>';
//         }
        
//         $output .= '</div>';
        
//     } else {
//         return '<p>Không thể đọc file customers.csv.</p>';
//     }
    
//     return $output;
// });

add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) return false;
    return $show;
});