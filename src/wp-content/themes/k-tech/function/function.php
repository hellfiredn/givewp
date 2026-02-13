<?php

// Khai báo endpoint mới khi khởi tạo
add_action( 'init', function() {
    $custom_endpoints = [
        'bank-info',
        'refund',
        'upgrade-account',
        'purchase-history',
        'wishlist',
        'change-password',
        'voucher'
    ];

    foreach ( $custom_endpoints as $ep ) {
        add_rewrite_endpoint( $ep, EP_ROOT | EP_PAGES );
    }
});

// Thêm menu mới vào My Account
add_filter( 'woocommerce_account_menu_items', function( $items ) {
    unset( $items['dashboard'] );

    $items = [
        'edit-account'      => 'Thông tin tài khoản',
        'voucher'           => 'Phiếu quà tặng',
        'bank-info'         => 'Thông tin chuyển khoản',
        'refund'            => 'Hoàn tiền',
        'upgrade-account'   => 'Nâng cấp tài khoản',
        'orders'            => 'Theo dõi đơn hàng',
        'purchase-history'  => 'Lịch sử mua hàng',
        'edit-address'      => 'Địa chỉ', 
        'wishlist'          => 'Sản phẩm yêu thích',
        'course'            => 'Khóa học của tôi',
        'change-password'   => 'Đổi mật khẩu',
        'customer-logout'   => 'Thoát'
    ];

    return $items;
});

// Nội dung hiển thị tạm thời cho các endpoint mới
add_action( 'woocommerce_account_bank-info_endpoint', function() {
    echo '<h3>Thông tin chuyển khoản</h3><p>Nội dung trang chuyển khoản...</p>';
});

add_action( 'woocommerce_account_refund_endpoint', function() {
    echo '<h3>Hoàn tiền</h3><p>Nội dung trang hoàn tiền...</p>';
});

add_action( 'woocommerce_account_upgrade-account_endpoint', function() {
    echo '<h3>Nâng cấp tài khoản</h3><p>Nội dung trang nâng cấp...</p>';
});

add_action( 'woocommerce_account_purchase-history_endpoint', function() {
    echo '<h3>Lịch sử mua hàng</h3><p>Nội dung lịch sử mua hàng...</p>';
});

add_action( 'woocommerce_account_wishlist_endpoint', function() {
    echo '<h3>Sản phẩm yêu thích</h3><p>Nội dung sản phẩm yêu thích...</p>';
});

add_action( 'woocommerce_account_change-password_endpoint', function() {
    echo '<h3>Đổi mật khẩu</h3><p>Form đổi mật khẩu...</p>';
});
add_action('woocommerce_rest_insert_shop_order', function($order, $request, $creating) {
    if (!$creating) return;

    $custom_date = $request->get_param('date_created');

    if ($custom_date) {
        $date = new WC_DateTime($custom_date);
        $order->set_date_created($date);
        $order->save();
    }
}, 10, 3);