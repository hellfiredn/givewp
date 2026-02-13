<?php

class KAM_Affiliate_Membership {
    public function __construct() {
        add_action('init', [$this, 'register_roles']);
        add_action('woocommerce_order_status_processing', [$this, 'handle_order_completed']);
        add_action('user_register', [$this, 'handle_user_register']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'set_master_product_price'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_master_custom_price']);
        add_filter('woocommerce_is_purchasable', array($this, 'restrict_spa_products'), 10, 2);
        add_filter('manage_users_columns', [$this, 'add_ref_code_column']);
        add_filter('manage_users_custom_column', [$this, 'show_ref_code_column'], 10, 3);
        add_action('after_setup_theme', [$this, 'overwrite_ux_product_title_shortcode']);
        add_action('woocommerce_checkout_process', [$this, 'validate_info_invoice_checkout']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_info_invoice_order']);
        add_filter('authenticate', [$this, 'auth_account_member'], 30, 3);
        add_filter('woocommerce_checkout_fields', [$this, 'handle_field_in_checkout']);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_discount_meta_to_order_item'], 10, 4);
        add_action('woocommerce_checkout_create_order', [$this, 'save_total_discount_to_order'], 10, 2);
        add_action('template_redirect', [$this, 'check_product_avaliable_with_role_page']);
        add_filter('woocommerce_product_is_visible', [$this, 'check_product_avaliable_with_role_query'], 10, 2);
        // add_action('pre_get_posts', [$this, 'filter_products_by_role_in_query']);
        add_filter('flatsome_ajax_search_products_args', [$this, 'filter_products_by_role_in_args'], 10, 2);
        // path /wp-content/themes/flatsome/inc/extensions/flatsome-live-search
        
        add_filter('woocommerce_product_get_price', function($price, $product) {
            // Nếu không phải trang single product và không phải trang checkout thì không override giá
            $current_user = wp_get_current_user();
            $roles = (array) $current_user->roles;
            $role  = array_shift($roles);
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

            $price_master = get_post_meta( $product->get_id(), '_master_price', true );
            $price_pharmer = get_post_meta( $product->get_id(), '_pharmer_price', true );

            if ($role === 'master' && $price_master) {
                $price = $price_master;
            }

            if ($role === 'pharmer' && $price_pharmer) {
                $price = $price_pharmer;
            }

            if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['discount']) {
                $discount_for_role = floatval($roles_setting[$role]['discount']);
                if ($discount_for_role > 0 && $discount_for_role < 100) {
                    $price = $price * (1 - $discount_for_role / 100);
                }
            }
            return $price;
        }, 10, 2);

        add_action('admin_init', function() {
            if (is_admin() && !defined('DOING_AJAX')) {
                if (!current_user_can('administrator')) {
                    wp_redirect(home_url());
                    exit;
                }
            }
        });
    }

    public function filter_products_by_role_in_query($query) {
        // Chỉ áp dụng cho WooCommerce product queries ở frontend
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if (!is_shop() && !is_product_category() && !is_product_tag() && !is_tax('product_cat') && !is_post_type_archive('product')) {
            return;
        }

        // Lấy tất cả products và filter theo role
        global $wpdb;
        
        // Xác định role của user hiện tại
        $current_roles = [];
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $current_roles = (array) $user->roles;
        } else {
            $current_roles = ['guest'];
        }

        // Lấy tất cả product IDs có meta _allowed_roles
        $all_products_with_roles = $wpdb->get_results(
            "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_allowed_roles'"
        );

        $excluded_product_ids = [];
        
        foreach ($all_products_with_roles as $product_meta) {
            $allowed_roles = maybe_unserialize($product_meta->meta_value);
            if (!is_array($allowed_roles)) $allowed_roles = [];
            
            // Nếu có 'all' thì cho phép tất cả
            if (in_array('all', $allowed_roles)) {
                continue;
            }

            // Kiểm tra xem user có role phù hợp không
            $has_access = false;
            foreach ($current_roles as $role) {
                if (in_array($role, $allowed_roles)) {
                    $has_access = true;
                    break;
                }
            }

            // Nếu không có quyền, thêm vào danh sách loại trừ
            if (!$has_access) {
                $excluded_product_ids[] = $product_meta->post_id;
            }
        }

        // Loại trừ các sản phẩm không có quyền xem
        if (!empty($excluded_product_ids)) {
            $query->set('post__not_in', array_merge(
                (array) $query->get('post__not_in'),
                $excluded_product_ids
            ));
        }
    }

    public function check_product_avaliable_with_role_query ($visible, $product_id) {
        $allowed_roles = get_post_meta($product_id, '_allowed_roles', true);
        if (!is_array($allowed_roles)) $allowed_roles = array();
        if (in_array('all', $allowed_roles)) {
            return true;
        }
        if (!is_user_logged_in() && in_array('guest', $allowed_roles)) {
            return true;
        }
        $user = wp_get_current_user();
        if ($user && !empty($user->roles)) {
            foreach ($user->roles as $role) {
                if (in_array($role, $allowed_roles)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function check_product_avaliable_with_role_page () {
        if (is_singular('product')) {
            global $post;
            $allowed_roles = get_post_meta($post->ID, '_allowed_roles', true);
            if (!is_array($allowed_roles)) $allowed_roles = array();
            $show_product = false;
            if (in_array('all', $allowed_roles)) {
                $show_product = true;
            } else {
                if (!is_user_logged_in() && in_array('guest', $allowed_roles)) {
                    $show_product = true;
                } else {
                    $user = wp_get_current_user();
                    if ($user && !empty($user->roles)) {
                        foreach ($user->roles as $role) {
                            if (in_array($role, $allowed_roles)) {
                                $show_product = true;
                                break;
                            }
                        }
                    }
                }
            }
            if (!$show_product) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                nocache_headers();
                include get_404_template();
                exit;
            }
        }
    }

    public function handle_field_in_checkout ($fields) {
        if (isset($fields['billing']['billing_company'])) {
            unset($fields['billing']['billing_company']);
        }

        if (isset($fields['shipping']['shipping_company'])) {
            unset($fields['shipping']['shipping_company']);
        }

        if (isset($fields['billing']['billing_postcode'])) {
            unset($fields['billing']['billing_postcode']);
        }

        if (isset($fields['billing']['billing_last_name'])) {
            unset($fields['billing']['billing_last_name']);
        }

        if (isset($fields['shipping']['shipping_postcode'])) {
            unset($fields['shipping']['shipping_postcode']);
        }

        if (isset($fields['shipping']['shipping_last_name'])) {
            unset($fields['shipping']['shipping_last_name']);
        }
        return $fields;
    }

    public function auth_account_member ($user, $username, $password) {
        if (is_wp_error($user) || !is_a($user, 'WP_User')) return $user;
        $approved = get_user_meta($user->ID, 'kam_approved', true);
        $locked = get_user_meta($user->ID, 'kam_locked', true);
        if ($locked === 'yes') {
            return new WP_Error('kam_locked', __('Tài khoản của bạn đã bị khoá. Vui lòng liên hệ quản trị viên.'));
        }
        // if ($approved === 'no') {
        //     return new WP_Error('kam_not_approved', __('Tài khoản của bạn chưa được duyệt.'));
        // }
        return $user;
    }

    public function validate_info_invoice_checkout() {
        // Validation cho thông tin hóa đơn trước khi tạo đơn hàng
        if (isset($_POST['require_invoice']) && $_POST['require_invoice']) {
            $company = sanitize_text_field($_POST['invoice_company'] ?? '');
            $tax = sanitize_text_field($_POST['invoice_tax'] ?? '');
            $address = sanitize_text_field($_POST['invoice_address'] ?? '');
            $email = sanitize_email($_POST['invoice_email'] ?? '');

            if (empty($company)) {
                wc_add_notice('Vui lòng nhập tên công ty/cá nhân để xuất hóa đơn.', 'error');
            }
            if (empty($tax)) {
                wc_add_notice('Vui lòng nhập mã số thuế để xuất hóa đơn.', 'error');
            }
            if (empty($address)) {
                wc_add_notice('Vui lòng nhập địa chỉ để xuất hóa đơn.', 'error');
            }
            if (empty($email)) {
                wc_add_notice('Vui lòng nhập email nhận hóa đơn.', 'error');
            }
        }
    }

    public function save_info_invoice_order ($order_id) {
        if (isset($_POST['require_invoice']) && $_POST['require_invoice']) {
            $company = sanitize_text_field($_POST['invoice_company'] ?? '');
            $tax = sanitize_text_field($_POST['invoice_tax'] ?? '');
            $address = sanitize_text_field($_POST['invoice_address'] ?? '');
            $email = sanitize_email($_POST['invoice_email'] ?? '');

            update_post_meta($order_id, '_require_invoice', 'yes');
            update_post_meta($order_id, '_invoice_company', $company);
            update_post_meta($order_id, '_invoice_tax', $tax);
            update_post_meta($order_id, '_invoice_address', $address);
            update_post_meta($order_id, '_invoice_email', $email);
        } else {
            update_post_meta($order_id, '_require_invoice', 'no');
        }
    }

    // Thêm cột ref code vào danh sách user admin
    public function add_ref_code_column($columns) {
        $columns['my_ref_code'] = 'Ref Code';
        return $columns;
    }

    // Hiển thị giá trị ref code trong cột
    public function show_ref_code_column($value, $column_name, $user_id) {
        if ($column_name == 'my_ref_code') {
            return get_user_meta($user_id, 'my_ref_code', true);
        }
        return $value;
    }
    // Xử lý khi user đăng ký: tạo mã referral, lưu referrer, full name, khởi tạo meta
    public function handle_user_register($user_id) {
        // Chỉ tạo mã referral nếu role là Pharmer Seller, Master, VIP Master, Super VIP Master
        $user = new WP_User($user_id);
        // $ref_code = strtoupper(wp_generate_password(7, false, false)) . $user_id;
        $ref_code = $_POST['phone'];
        update_user_meta($user_id, 'my_ref_code', $ref_code);

        // Lưu referrer nếu có mã referral trên form
        if (!empty($_POST['ref_code'])) {
            $input_code = sanitize_text_field($_POST['ref_code']);
            $ref_by = $this->get_user_id_by_ref_code($input_code);
            if ($ref_by) {
                update_user_meta($user_id, 'ref_code', $input_code);
                update_user_meta($user_id, 'ref_by', $ref_by);
            }
        }

        // Lưu full name nếu có
        if (!empty($_POST['fullname'])) {
            $fullname = sanitize_text_field($_POST['fullname']);
            wp_update_user(['ID' => $user_id, 'display_name' => $fullname]);
            update_user_meta($user_id, 'user_fullname', $fullname);
        }

        if (!empty($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_user_meta($user_id, 'user_phone', $phone);
        }

        if (!empty($_POST['cccd'])) {
            $cccd = sanitize_text_field($_POST['cccd']);
            update_user_meta($user_id, 'user_cccd', $cccd);
        }

        // Khởi tạo các meta cần thiết
        update_user_meta($user_id, 'activation_status', 'pending');
    }

    // Hàm lấy user_id từ referral code
    public function get_user_id_by_ref_code($ref_code) {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'my_ref_code' AND meta_value = %s LIMIT 1",
            $ref_code
        ));
        return $user_id ? intval($user_id) : false;
    }

    // Đăng ký các nhóm vai trò
    public function register_roles() {
        add_role('pharmer', 'VIP Member');
        add_role('pharmer_seller', 'Pharmer Seller');
        add_role('master', 'Master');
        add_role('vip_master', 'VIP Master');
        add_role('super_vip_master', 'Super VIP Master');
    }

    // Xử lý khi đơn hàng hoàn thành
    public function handle_order_completed($order_id) {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if ($order && ($order->get_status() === 'cancelled' || $order->get_status() === 'failed')) {
            return;
        }

        $db = new KTech_Affiliate_DB();

        $user = get_userdata($order->get_user_id());
        $order_username = $user ? $user->display_name : $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $order_user_role = $this->get_user_role($order->get_user_id());
        $order_user_role_name = $this->get_role_name_by_slug($order_user_role) ? $this->get_role_name_by_slug($order_user_role) : 'Guest';
        $user_id = $order->get_user_id();
        $ref_code_link = get_post_meta($order_id, '_ref_code_link', true);
        $roles_setting = get_option('ktech_account_roles_setting', []);
        $amount_order = $order->get_total();

        // Tính tổng giá các sản phẩm affiliate trong đơn hàng
        $amount_aff = 0;
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $is_affiliate_product = get_post_meta($product_id, '_is_affiliate_product', true);
            if ($is_affiliate_product === 'yes') {
                $amount_aff += $item->get_total();
            }
        }

        // Lưu doanh số cá nhân
        if ($user_id) {
            $db->insert([
                'user_id' => $user_id,
                'order_id' => $order_id,
                'order_username'   => $order_username,
                'order_userrole'   => $order_user_role_name,
                'type'    => 'personal',
                'amount'  => $amount_aff,
                'lp'      => 0,
                'refund'      => 0,
                'note'    => 'Doanh số cá nhân',
            ]);
        }

        if ($ref_code_link) {
            $guest_directly_user_id = $this->get_user_id_by_ref_code($ref_code_link);
            $guest_directly_user_role = $this->get_user_role($guest_directly_user_id);
            $guest_percentage_directly = $roles_setting[$guest_directly_user_role]['commission_directly'] / 100;
            $guest_refund_directly = round($amount_aff * $guest_percentage_directly);
            
            if ($guest_directly_user_id && !empty($guest_refund_directly)) {
                if ($guest_directly_user_id != $order->get_user_id()) {
                    // Lưu doanh số từ liên kết (trực tiếp)
                    $db->insert([
                        'user_id' => $guest_directly_user_id,
                        'order_id' => $order_id,
                        'order_username'   => $order_username,
                        'order_userrole'   => $order_user_role_name,
                        'type'    => 'direct',
                        'amount'  => $amount_aff,
                        'lp'      => 0,
                        'refund'      => $guest_refund_directly,
                        'note'    => 'Doanh số từ liên kết trực tiếp',
                    ]);

                    // Lưu doanh số từ liên kết (gián tiếp)
                    $guest_indirect_user_id = get_user_meta($guest_directly_user_id, 'ref_by', true);
                    if ($guest_indirect_user_id) {
                        $guest_indirect_user_role = $this->get_user_role($guest_indirect_user_id);
                        $guest_percentage_indirect = $roles_setting[$guest_indirect_user_role]['commission_indirect'] / 100;
                        $guest_refund_indirect = round($amount_aff * $guest_percentage_indirect);
                        $guest_indirect_user_lp_config = $roles_setting[$guest_indirect_user_role]['lp'];
                        $guest_indirect_lp_amount = round($amount_aff / $guest_indirect_user_lp_config);

                        // Lưu doanh số gián tiếp
                        $db->insert([
                            'user_id' => $guest_indirect_user_id,
                            'order_id' => $order_id,
                            'order_username'   => $order_username,
                            'order_userrole'   => $order_user_role_name,
                            'type'    => 'indirect',
                            'amount'  => $amount_aff,
                            'lp'      => $guest_indirect_lp_amount,
                            'refund'      => $guest_refund_indirect,
                            'note'    => 'Doanh số từ liên kết gián tiếp từ F2',
                        ]);
                    }
                }
            }
        }

        // Tuyến trực tiếp (referrer)
        $direct_user_id = get_user_meta($user_id, 'ref_by', true);

        if ($direct_user_id) {
            $direct_user_role = $this->get_user_role($direct_user_id);
            // Kiểm tra role có tồn tại trong settings
            if (!empty($direct_user_role)) {
                $percentage_direct = $roles_setting[$direct_user_role]['commission_directly'] / 100;
                $refund_direct = round($amount_aff * $percentage_direct);
                
                // Lưu doanh số trực tiếp
                $db->insert([
                    'user_id' => $direct_user_id,
                    'order_id' => $order_id,
                    'order_username'   => $order_username,
                    'order_userrole'   => $order_user_role_name,
                    'type'    => 'direct',
                    'amount'  => $amount_aff,
                    'lp'      => 0,
                    'refund'      => $refund_direct,
                    'note'    => 'Doanh số trực tiếp từ F1',
                ]);

                // Tuyến gián tiếp (referrer)
                $indirect_user_id = get_user_meta($direct_user_id, 'ref_by', true);
                if ($indirect_user_id) {
                    $indirect_user_role = $this->get_user_role($indirect_user_id);
                    $percentage_indirect = $roles_setting[$indirect_user_role]['commission_indirect'] / 100;
                    $refund_indirect = round($amount_aff * $percentage_indirect);
                    $lp_config = $roles_setting[$indirect_user_role]['lp'];
                    $lp_amount = round($amount_aff / $lp_config);
                    
                    // Lưu doanh số gián tiếp
                    $db->insert([
                        'user_id' => $indirect_user_id,
                        'order_id' => $order_id,
                        'order_username'   => $order_username,
                        'order_userrole'   => $order_user_role_name,
                        'type'    => 'indirect',
                        'amount'  => $amount_aff,
                        'lp'      => $lp_amount,
                        'refund'      => $refund_indirect,
                        'note'    => 'Doanh số gián tiếp từ F2',
                    ]);
                }
            }
        }
        
        $this->check_pharmer_seller_activation($user_id);
        $this->check_master_activation($user_id);
        $this->check_vip_master_activation($user_id);
    }

    // Lấy vai trò chính của user
    public function get_user_role($user_id) {
        $user = get_userdata($user_id);
        return $user->roles[0] ?? '';
    }

    // Lấy tên hiển thị của role
    public function get_role_name_by_slug($role_slug) {
      $roles = [
        'pharmer'           => 'VIP Member',
        'pharmer_seller'    => 'Pharmer Seller',
        'master'            => 'Master',
        'vip_master'        => 'VIP Master',
        'super_vip_master'  => 'Super VIP Master',
      ];
      return $roles[$role_slug] ?? $role_slug;
    }

    // Kiểm tra kích hoạt membership và duy trì cho Pharmer Seller
    // Pharmer: Mua đơn >= 10 triệu hoặc tích lũy đủ 10 triệu trong 2 tháng để hưởng quyền lợi PS
    public function check_pharmer_seller_activation($user_id) {
        $role = $this->get_user_role($user_id);
        if ($role !== 'pharmer') return;
        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit' => -1,
            'orderby' => 'date_created',
            'order' => 'ASC'
        ]);
        $activated = false;
        foreach ($orders as $order) {
            if ($order->get_total() >= 10000000) {
                $activated = true;
                break;
            }
        }
        // Kiểm tra tích lũy 10 triệu trong 2 tháng
        $from = date('Y-m-d H:i:s', strtotime('-2 months'));
        $orders_2m = wc_get_orders([
            'customer_id' => $user_id,
            'date_created' => '>=' . $from,
            'limit' => -1
        ]);
        $total_2m = 0;
        foreach ($orders_2m as $order) {
            $total_2m += $order->get_total();
        }
        if ($activated || $total_2m >= 10000000) {
            update_user_meta($user_id, 'ps_benefit', 'activated');
            // Nâng role lên Pharmer Seller nếu chưa phải
            $user = new WP_User($user_id);
            if (!$user->has_role('pharmer_seller')) {
                $user->set_role('pharmer_seller');
                // Tạo ref code nếu chưa có
                $ref_code = get_user_meta($user_id, 'my_ref_code', true);
                if (empty($ref_code)) {
                    $new_ref_code = strtoupper(wp_generate_password(7, false, false)) . $user_id;
                    update_user_meta($user_id, 'my_ref_code', $new_ref_code);
                }
            }
        } else {
            update_user_meta($user_id, 'ps_benefit', 'pending');
        }
    }

    // Kiểm tra kích hoạt membership cho Master
    public function check_master_activation($user_id) {
        $role = $this->get_user_role($user_id);
        if ($role !== 'pharmer_seller') return;
        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit' => -1,
            'orderby' => 'date_created',
            'order' => 'ASC'
        ]);
        $activated = false;
        foreach ($orders as $order) {
            if ($order->get_total() >= 30000000) {
                $activated = true;
                break;
            }
        }
        // Kiểm tra tích lũy 50 triệu trong 2 tháng
        $from = date('Y-m-d H:i:s', strtotime('-2 months'));
        $orders_2m = wc_get_orders([
            'customer_id' => $user_id,
            'date_created' => '>=' . $from,
            'limit' => -1
        ]);
        $total_2m = 0;
        foreach ($orders_2m as $order) {
            $total_2m += $order->get_total();
        }
        if ($activated || $total_2m >= 50000000) {
            // Nâng role lên Master nếu chưa phải
            $user = new WP_User($user_id);
            if (!$user->has_role('master')) {
                $user->set_role('master');
                // Tạo ref code nếu chưa có
                $ref_code = get_user_meta($user_id, 'my_ref_code', true);
                if (empty($ref_code)) {
                    $new_ref_code = strtoupper(wp_generate_password(7, false, false)) . $user_id;
                    update_user_meta($user_id, 'my_ref_code', $new_ref_code);
                }
            }
        }
    }

    // Kiểm tra kích hoạt membership cho VIP Master
    public function check_vip_master_activation($user_id) {
        $role = $this->get_user_role($user_id);
        if ($role !== 'master') return;
        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit' => -1,
            'orderby' => 'date_created',
            'order' => 'ASC'
        ]);
        $activated = false;
        foreach ($orders as $order) {
            if ($order->get_total() >= 500000000) {
                $activated = true;
                break;
            }
        }
        // Kiểm tra tích lũy 600 triệu trong 2 tháng
        $from = date('Y-m-d H:i:s', strtotime('-2 months'));
        $orders_2m = wc_get_orders([
            'customer_id' => $user_id,
            'date_created' => '>=' . $from,
            'limit' => -1
        ]);
        $total_2m = 0;
        foreach ($orders_2m as $order) {
            $total_2m += $order->get_total();
        }
        if ($activated || $total_2m >= 600000000) {
            // Nâng role lên VIP Master nếu chưa phải
            $user = new WP_User($user_id);
            if (!$user->has_role('vip_master')) {
                $user->set_role('vip_master');
            }
            // Tạo ref code nếu chưa có
            $ref_code = get_user_meta($user_id, 'my_ref_code', true);
            if (empty($ref_code)) {
                $new_ref_code = strtoupper(wp_generate_password(7, false, false)) . $user_id;
                update_user_meta($user_id, 'my_ref_code', $new_ref_code);
            }
        }
    }

    public function set_master_product_price($cart_item_data, $product_id) {
        if (!is_user_logged_in()) return $cart_item_data;
        $user_id = get_current_user_id();
        $role = $this->get_user_role($user_id);
        $price_master = 0;
        $price_pharmer = 0;
        $original_price = get_post_meta($product_id, '_regular_price', true);
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

        if ($role === 'master') {
            $price_master = get_post_meta($product_id, '_master_price', true);
            if ($price_master && $price_master < $original_price) {
                $cart_item_data['custom_price'] = $price_master;
                $cart_item_data['amount_reduced'] = $original_price - $price_master;
            }
        }

        if ($role === 'pharmer') {
            $price_pharmer = get_post_meta($product_id, '_pharmer_price', true);
            if ($price_pharmer && $price_pharmer < $original_price) {
                $cart_item_data['custom_price'] = $price_pharmer;
                $cart_item_data['amount_reduced'] = $original_price - $price_pharmer;
            }
        }

        return $cart_item_data;
    }

    // Áp dụng giá custom cho Master trong cart
    public function apply_master_custom_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['custom_price'])) {
                $product = $cart_item['data'];
                $new_price = $cart_item['custom_price'];
                $product->set_price($new_price);
            }
        }
    }

    // Lưu số tiền đã giảm vào meta sản phẩm của đơn hàng
    public function save_discount_meta_to_order_item($item, $cart_item_key, $values, $order) {
        if (isset($values['amount_reduced'])) {
            $item->add_meta_data('amount_reduced', $values['amount_reduced']);
        }
    }

    // Lưu tổng số tiền đã giảm vào meta đơn hàng
    public function save_total_discount_to_order($order, $data) {
        $total_reduced = 0;
        foreach ($order->get_items() as $item) {
            $reduced = $item->get_meta('amount_reduced');
            if ($reduced) {
                $total_reduced += $reduced * $item->get_quantity();
            }
        }
        if ($total_reduced > 0) {
            $order->update_meta_data('total_amount_reduced', $total_reduced);
        }
    }

    // Kiểm tra sản phẩm chuyên sâu cho Spa, chỉ cho phép role master và vip_master mua
    public function restrict_spa_products($purchasable, $product) {
        $spa_category = 'spa'; // slug của category Spa
        if (has_term($spa_category, 'product_cat', $product->get_id())) {
            $user_id = get_current_user_id();
            $role = $this->get_user_role($user_id);
            if (!in_array($role, ['master', 'vip_master'])) {
                return false;
            }
        }
        return $purchasable;
    }

    public function overwrite_ux_product_title_shortcode () {
        remove_shortcode('ux_product_title');
        add_shortcode('ux_product_title', function ( $atts ) {
            extract( shortcode_atts( array(
                'size'      => false,
                'divider'   => true,
                'case'      => 'normal',
                'uppercase' => false,
            ), $atts ) );

            if ( ! is_product() ) {
                return null;
            }

            add_filter( 'theme_mod_product_title_divider', function ( $input ) use ( $divider ) {
                if ( $divider ) {
                    return true;
                }
            } );

            $classes = array( 'product-title-container' );
            if ( $size ) {
                $classes[] = 'is-' . $size;
            }
            if ( $uppercase ) {
                $classes[] = 'is-uppercase';
            }

            ob_start();
            echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
            woocommerce_template_single_title();
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                if (in_array('master', (array) $user->roles)) {
                    $product_url = get_permalink();
                    $user_ref_code = get_user_meta($user->ID, 'my_ref_code', true);
                    $ref_link = add_query_arg('ref_code', $user_ref_code, $product_url);
                    echo '<div class="icon-ref-link-share">';
                    echo '<img src="/wp-content/uploads/2025/09/link.png" />';
                    echo '<div class="ref-link-container">';
                    echo '<p>master ID: ' . esc_html($user->user_login) . '</p>';
                    echo '<div class="ref-link-inner">';
                    echo '<button class="copy-ref-link-btn" data-link="' . esc_url($ref_link) . '"><img src="/wp-content/uploads/2025/09/copy.png" /></button>';
                    echo '<input type="text" disabled value="' . esc_url($ref_link) . '" />';
                    echo '</div>';
                    echo '<p class="ref-link-notification"></p>';
                    echo '</div>';
                    echo '</div>';
                    ?>
                    <?php
                }
            }
            echo '</div>';

            return ob_get_clean();
        });
    }

    /**
     * Lọc sản phẩm theo role cho live search Flatsome
     */
    public function filter_products_by_role_in_args($args, $search_type) {
        // Chỉ áp dụng cho product/product_variation
        if (!isset($args['post_type'])) return $args;
        $post_types = (array)$args['post_type'];
        if (!in_array('product', $post_types) && !in_array('product_variation', $post_types)) return $args;
        global $wpdb;
        $current_roles = is_user_logged_in() ? (array) wp_get_current_user()->roles : ['guest'];
        $all_products_with_roles = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_allowed_roles'");
        $excluded_product_ids = [];
        foreach ($all_products_with_roles as $product_meta) {
            $allowed_roles = maybe_unserialize($product_meta->meta_value);
            if (!is_array($allowed_roles) || empty($allowed_roles)) $allowed_roles = [];
            if (in_array('all', $allowed_roles)) continue;
            $has_access = false;
            foreach ($current_roles as $role) {
                if (in_array($role, $allowed_roles)) {
                    $has_access = true;
                    break;
                }
            }
            if (!$has_access) $excluded_product_ids[] = intval($product_meta->post_id);
        }
        if (!empty($excluded_product_ids)) {
            $args['post__not_in'] = array_unique(array_merge(
                isset($args['post__not_in']) ? (array) $args['post__not_in'] : [],
                $excluded_product_ids
            ));
        }
        return $args;
    }
}
new KAM_Affiliate_Membership();
