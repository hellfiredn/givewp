<?php
// Danh sách endpoint
$custom_endpoints = [
    'voucher'  => 'Phiếu quà tặng',
    'bank-info'        => 'Thông tin chuyển khoản',
    'refund'           => 'Hoàn tiền',
    'upgrade-account'  => 'Nâng cấp tài khoản',
    'followup'          => 'Theo dõi đơn hàng',
    'purchase-history' => 'Lịch sử mua hàng',
    'edit-address'  => 'Địa chỉ',
    'wishlist'         => 'Sản phẩm yêu thích',
    'course'         => 'Khóa học của tôi',
    'change-password'  => 'Đổi mật khẩu',
];

add_action( 'init', function() use ($custom_endpoints) {
    foreach ( array_keys($custom_endpoints) as $ep ) {
        add_rewrite_endpoint( $ep, EP_ROOT | EP_PAGES );
    }
});

// Thêm menu
add_filter( 'woocommerce_account_menu_items', function( $items ) use ($custom_endpoints) {
    unset($items['dashboard']); // Bỏ trang Dashboard mặc định

    $new_items = ['edit-account' => 'Thông tin tài khoản'];
    foreach ( $custom_endpoints as $slug => $label ) {
        $new_items[$slug] = $label;
    }
    $new_items['customer-logout'] = 'Thoát';
    return $new_items;
}, 99);

// Tạo hook hiển thị nội dung cho từng endpoint
foreach ( $custom_endpoints as $slug => $label ) {
    add_action( "woocommerce_account_{$slug}_endpoint", function() use ($slug) {
        wc_get_template( "myaccount/{$slug}.php" );
    });
}
function my_get_account_menu_icon( $endpoint ) {
    // Khai báo icon cho từng endpoint
    $icons = array(
        'dashboard'         => 'user.png',
        'edit-account'      => 'user.png',
        'orders'            => 'orders.svg',
        'downloads'         => 'downloads.svg',
        'edit-address'      => 'location.png',
        'payment-methods'   => 'payment.svg',
        'customer-logout'   => 'logout.png',

        // Endpoint custom của bạn
        'bank-info'         => 'bank.png',
        'refund'            => 'payment.png',
        'upgrade-account'   => 'upgrade.svg',
        'order-tracking'    => 'tracking.svg',
        'purchase-history'  => 'clock.png',
        'wishlist'          => 'heart.png',
        'change-password'   => 'lock.png',
        'followup'          => 'refresh.png',
        'voucher'           => 'voucher_dark.png',
        'course'           => 'course.svg',

    );

    // Lấy đường dẫn file icon
    $theme_dir = get_stylesheet_directory_uri() . '/assets/image/';
    $filename  = isset( $icons[$endpoint] ) ? $icons[$endpoint] : 'default.svg';

    // Trả về HTML
    return '<img src="' . esc_url( $theme_dir . $filename ) . '" alt="" class="menu-icon" />';
}
add_action( 'woocommerce_save_account_details', 'save_extra_account_fields' );
function save_extra_account_fields( $user_id ) {
    if ( isset( $_POST['gender'] ) ) {
        update_user_meta( $user_id, 'gender', sanitize_text_field( $_POST['gender'] ) );
    }
    if ( isset( $_POST['birthday'] ) ) {
        update_user_meta( $user_id, 'birthday', sanitize_text_field( $_POST['birthday'] ) );
    }
    if ( isset( $_POST['phone'] ) ) {
        update_user_meta( $user_id, 'phone', sanitize_text_field( $_POST['phone'] ) );
    }
    // Link giới thiệu & sản phẩm: chỉ set nếu chưa có
    if ( ! get_user_meta( $user_id, 'ref_link', true ) ) {
        $user = get_userdata( $user_id );
        update_user_meta( $user_id, 'ref_link', home_url( '/register/' . $user->user_login ) );
    }
    if ( ! get_user_meta( $user_id, 'product_link', true ) ) {
        update_user_meta( $user_id, 'product_link', home_url( '/shop/customer/' . $user_id ) );
    }

    if ( isset( $_POST['spa_name'] ) ) {
        update_user_meta( $user_id, 'spa_name', sanitize_text_field( $_POST['spa_name'] ) );
    }

    if ( isset( $_POST['spa_address'] ) ) {
        update_user_meta( $user_id, 'spa_address', sanitize_text_field( $_POST['spa_address'] ) );
    }
    
    if ( isset( $_POST['passport_cccd'] ) ) {
        update_user_meta( $user_id, 'passport_cccd', sanitize_text_field( $_POST['passport_cccd'] ) );
    }

    if ( isset( $_POST['date_issue'] ) ) {
        update_user_meta( $user_id, 'date_issue', sanitize_text_field( $_POST['date_issue'] ) );
    }

    if ( isset( $_POST['date_issue'] ) ) {
        update_user_meta( $user_id, 'date_issue', sanitize_text_field( $_POST['date_issue'] ) );
    }

    if ( isset( $_POST['card_local'] ) ) {
        update_user_meta( $user_id, 'card_local', sanitize_text_field( $_POST['card_local'] ) );
    }

    // ===== Upload files -> save attachment ID =====
    $file_fields = [
        'cccd_front' => 'cccd_front_id',
        'cccd_back'  => 'cccd_back_id',
        'gpkd'       => 'gpkd_id',
    ];

    foreach ( $file_fields as $file_input_name => $meta_key ) {
        $attach_id = givehada_handle_user_file_upload( $file_input_name, $user_id );

        // Nếu có upload thành công thì lưu ID
        if ( $attach_id && ! is_wp_error( $attach_id ) ) {
            update_user_meta( $user_id, $meta_key, (int) $attach_id );
        }
    }
}
add_filter( 'woocommerce_save_account_details_required_fields', function( $required_fields ) {
    unset( $required_fields['account_first_name'] );
    unset( $required_fields['account_last_name'] );
    unset( $required_fields['account_email'] );
    unset( $required_fields['account_display_name'] );
    return $required_fields;
});

/**
 * Upload file từ input name -> tạo attachment -> trả về attachment ID
 */
function givehada_handle_user_file_upload( $file_input_name, $user_id ) {

    if ( empty( $_FILES[ $file_input_name ] ) || empty( $_FILES[ $file_input_name ]['name'] ) ) {
        return 0; // không upload
    }

    // Chỉ cho user đã đăng nhập upload cho chính họ (chặn gọi bậy)
    if ( (int) $user_id !== get_current_user_id() ) {
        return 0;
    }

    // Validate lỗi upload
    if ( ! empty( $_FILES[ $file_input_name ]['error'] ) ) {
        return 0;
    }

    // Giới hạn loại file (ảnh + pdf)
    $allowed_mimes = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'webp'         => 'image/webp',
        'pdf'          => 'application/pdf',
    ];

    $file = $_FILES[ $file_input_name ];

    // Kiểm tra mime/extension theo WP
    $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed_mimes );
    if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
        return 0;
    }

    // Chuẩn bị libs upload
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Upload vào uploads
    $overrides = [ 'test_form' => false ];
    $uploaded  = wp_handle_upload( $file, $overrides );

    if ( isset( $uploaded['error'] ) ) {
        return 0;
    }

    $file_path = $uploaded['file'];
    $file_url  = $uploaded['url'];
    $mime_type = $uploaded['type'];

    // Tạo attachment
    $attachment = [
        'post_mime_type' => $mime_type,
        'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'post_author'    => $user_id,
        'guid'           => $file_url,
    ];

    $attach_id = wp_insert_attachment( $attachment, $file_path );

    if ( is_wp_error( $attach_id ) ) {
        return 0;
    }

    // Generate metadata (ảnh sẽ có thumb)
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return (int) $attach_id;
}
//cms

// Hiển thị field custom trong admin
add_action( 'show_user_profile', 'my_custom_user_fields' );
add_action( 'edit_user_profile', 'my_custom_user_fields' );

function my_custom_user_fields( $user ) {
    $master_users = get_users([
        // 'role__in' => ['master', 'pharmer_seller'],
        'fields'    => 'all'
    ]);
    $current_referrer_id = get_user_meta($user->ID, 'ref_by', true);
    $my_ref_code = get_user_meta($user->ID, 'my_ref_code', true);
    ?>
    <h3>Thông tin bổ sung</h3>
    <table class="form-table">
        <tr>
            <th><label for="custom_full_name">Họ và tên</label></th>
            <td>
                <input type="text" name="custom_full_name" id="custom_full_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_full_name', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="custom_gender">Giới tính</label></th>
            <td>
                <select name="custom_gender" id="custom_gender">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam" <?php selected( get_user_meta( $user->ID, 'custom_gender', true ), 'Nam' ); ?>>Nam</option>
                    <option value="Nữ" <?php selected( get_user_meta( $user->ID, 'custom_gender', true ), 'Nữ' ); ?>>Nữ</option>
                    <option value="Khác" <?php selected( get_user_meta( $user->ID, 'custom_gender', true ), 'Khác' ); ?>>Khác</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="custom_birthday">Ngày sinh</label></th>
            <td>
                <input type="date" name="custom_birthday" id="custom_birthday" value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_birthday', true ) ); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="custom_phone">Số điện thoại</label></th>
            <td>
                <input type="text" name="custom_phone" id="custom_phone" value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_phone', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="custom_email">Email</label></th>
            <td>
                <input type="text" name="custom_email" id="custom_email" value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_email', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label>Link giới thiệu</label></th>
            <td>
                <input type="text" readonly value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_ref_link', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label>Link sản phẩm</label></th>
            <td>
                <input type="text" readonly value="<?php echo esc_attr( get_user_meta( $user->ID, 'custom_product_link', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Thông tin tài khoản ngân hàng</h3>
    <table class="form-table">
        <tr>
            <th><label for="bank_account_name">Tên người thụ hưởng</label></th>
            <td>
                <input type="text" name="bank_account_name" id="bank_account_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'bank_account_name', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="bank_account_number">Số tài khoản</label></th>
            <td>
                <input type="text" name="bank_account_number" id="bank_account_number" value="<?php echo esc_attr( get_user_meta( $user->ID, 'bank_account_number', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="bank_name">Tên ngân hàng</label></th>
            <td>
                <input type="text" name="bank_name" id="bank_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'bank_name', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Thông tin kê khai thuế</h3>
    <table class="form-table">
        <tr>
            <th><label for="tax_company_name">Tên đơn vị</label></th>
            <td>
                <input type="text" name="tax_company_name" id="tax_company_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tax_company_name', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="tax_company_address">Địa chỉ</label></th>
            <td>
                <input type="text" name="tax_company_address" id="tax_company_address" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tax_company_address', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="tax_code">Mã số thuế</label></th>
            <td>
                <input type="text" name="tax_code" id="tax_code" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tax_code', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="tax_email">Email (kê khai thuế)</label></th>
            <td>
                <input type="email" name="tax_email" id="tax_email" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tax_email', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="tax_id_number">Số CCCD</label></th>
            <td>
                <input type="text" name="tax_id_number" id="tax_id_number" value="<?php echo esc_attr( get_user_meta( $user->ID, 'tax_id_number', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="id_issue_date">Ngày cấp</label></th>
            <td>
                <input type="date" name="id_issue_date" id="id_issue_date" value="<?php echo esc_attr( get_user_meta( $user->ID, 'id_issue_date', true ) ); ?>" class="regular-text"  />
            </td>
        </tr>
        <tr>
            <th><label for="id_issue_place">Nơi cấp</label></th>
            <td>
                <input type="text" name="id_issue_place" id="id_issue_place" value="<?php echo esc_attr( get_user_meta( $user->ID, 'id_issue_place', true ) ); ?>" class="regular-text" />
            </td>
        </tr>
    </table>

    <h3>Affikiate Membership</h3>
    <table class="form-table">
        <tr>
            <th><label for="my_ref_code">Ref Code</label></th>
            <td>
                <input type="text" name="my_ref_code" id="my_ref_code" value="<?php echo esc_attr( $my_ref_code ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="ref_by">Người giới thiệu</label></th>
            <td>
                <select name="ref_by" id="ref_by">
                    <option value="">Chọn người giới thiệu</option>
                    <?php foreach ($master_users as $user_item) : ?>
                        <?php
                            $user_item_id = $user_item->ID;
                            $user_display_name = $user_item->display_name;
                            $referrer_code = get_user_meta($user_item_id, 'my_ref_code', true);
                        ?>
                        <option value="<?php echo $referrer_code ?>" <?php selected($referrer_code, $current_referrer_id); ?>><?php echo $user_display_name.' ('.$referrer_code.')'; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

//lưu thông tin vào user meta
add_action( 'personal_options_update', 'my_save_custom_user_fields' );
add_action( 'edit_user_profile_update', 'my_save_custom_user_fields' );

function my_save_custom_user_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    if (!empty( $_POST['user_email'] )) {
        wp_update_user([
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['user_email'])
        ]);
    }

    $fields = [
        // Thông tin cá nhân
        'user_fullname'  => 'text',
        'user_gender'     => 'text',
        'user_birthday'   => 'text',
        'user_phone'      => 'text',
        'user_ref_link'   => 'url',
        'user_product_link' => 'url',

        // Thông tin ngân hàng
        'bank_account_name'   => 'text',
        'bank_account_number' => 'text',
        'bank_name'           => 'text',

        // Thông tin kê khai thuế
        'tax_company_name'  => 'text',
        'tax_address'       => 'text',
        'tax_code'          => 'text',
        'tax_email'         => 'email',
        'id_number'         => 'text',
        'id_issue_date'     => 'text',
        'id_issue_place'    => 'text',

        // Affikiate Membership
        'my_ref_code'    => 'text',
        'ref_by'     => 'text',
    ];

    foreach ( $fields as $key => $type ) {
        if ( isset( $_POST[$key] ) ) {
            $value = sanitize_by_type( $_POST[$key], $type );
            update_user_meta( $user_id, $key, $value );
        }
    }
}

// Lưu ở WooCommerce
add_action( 'woocommerce_save_account_details', 'my_save_custom_account_fields' );

function my_save_custom_account_fields( $user_id ) {
    // Dùng lại cùng mảng field như trên
    my_save_custom_user_fields( $user_id );
}

// Hàm sanitize theo loại dữ liệu
function sanitize_by_type( $value, $type ) {
    switch ( $type ) {
        case 'email':
            return sanitize_email( $value );
        case 'url':
            return esc_url_raw( $value );
        default:
            return sanitize_text_field( $value );
    }
}

//xử lý redirect sau khi lưu
add_action( 'template_redirect', function() {
    if ( isset($_POST['save_bank_info']) && is_account_page() && get_query_var('bank-info') ) {
        if ( ! wp_verify_nonce( $_POST['save_bank_info_nonce'], 'save_bank_info' ) ) {
            wc_add_notice( 'Xác thực không hợp lệ, vui lòng thử lại.', 'error' );
            return;
        }

        $user_id = get_current_user_id();
        $fields = [
            'bank_account_name', 'bank_account_number', 'bank_name',
            'company_name', 'company_address', 'tax_code', 'tax_email',
            'personal_id', 'personal_id_date', 'personal_id_place'
        ];

        foreach ( $fields as $field ) {
            if ( isset($_POST[$field]) ) {
                update_user_meta( $user_id, $field, sanitize_text_field($_POST[$field]) );
            }
        }

        wc_add_notice( 'Cập nhật thông tin thành công!', 'success' );
        wp_safe_redirect( wc_get_endpoint_url( 'bank-info', '', wc_get_page_permalink( 'myaccount' ) ) );
        exit;
    }
});
//save bank info
// Xử lý lưu form bank-info
add_action( 'template_redirect', 'my_save_bank_info' );

function my_save_bank_info() {
    if ( isset( $_POST['save_bank_info'] ) && wp_verify_nonce( $_POST['save_bank_info_nonce'], 'save_bank_info' ) ) {

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }

        // Danh sách field cần lưu
        $fields = [
            // Thông tin tài khoản ngân hàng
            'bank_account_name',
            'bank_account_number',
            'bank_name',

            // Thông tin kê khai thuế
            'tax_company_name',
            'tax_address',
            'tax_code',
            'tax_email',
            'tax_id_number',
            'tax_issue_date',
            'tax_issue_place'
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[$field] ) ) {
                update_user_meta( $user_id, $field, sanitize_text_field( $_POST[$field] ) );
            }
        }

        // Thông báo giống Woo
        wc_add_notice( __( 'Thông tin tài khoản đã được cập nhật.', 'woocommerce' ), 'success' );

        // Redirect lại mà không cần query string
        wp_safe_redirect( wc_get_account_endpoint_url( 'bank-info' ) );
        exit;
    }
}

// Thêm SheetJS vào frontend cho export Excel
add_action('wp_enqueue_scripts', function() {
    if (is_account_page() || is_page('tai-khoan')) { // hoặc điều kiện phù hợp trang cần export
        wp_enqueue_script(
            'sheetjs',
            'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
            [],
            null,
            true
        );
    }
});

