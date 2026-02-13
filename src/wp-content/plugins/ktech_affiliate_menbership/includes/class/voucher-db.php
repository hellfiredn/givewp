<?php

class KTech_Voucher_DB {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'kam_vouchers';
    }

    // Tạo bảng (gọi khi kích hoạt plugin)
    public static function install_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kam_vouchers';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            voucher_code VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            minimum_order DECIMAL(10,2) DEFAULT 0,
            expiry_date DATETIME,
            usage_limit INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
            image_url VARCHAR(500) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY voucher_code (voucher_code),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Thêm voucher mới
    public function insert($data) {
        global $wpdb;
        $defaults = array(
            'voucher_code' => '',
            'title' => '',
            'description' => '',
            'minimum_order' => 0,
            'expiry_date' => null,
            'usage_limit' => null,
            'image_url' => '',
            'status' => 'active'
        );
        
        $data = wp_parse_args($data, $defaults);
        $data['created_at'] = current_time('mysql');
        
        $result = $wpdb->insert($this->table, $data);
        return $result ? $wpdb->insert_id : false;
    }

    // Cập nhật voucher
    public function update($id, $data) {
        error_log(print_r($data, true));
        global $wpdb;
        $data['updated_at'] = current_time('mysql');
        return $wpdb->update($this->table, $data, array('id' => $id));
    }

    // Xóa voucher
    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table, array('id' => $id));
    }

    // Lấy voucher theo ID
    public function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE id = %d",
            $id
        ));
    }

    // Lấy voucher theo code
    public function get_by_code($code) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE voucher_code = %s",
            $code
        ));
    }

    // Lấy tất cả voucher với filter
    public function get_all($args = array()) {
        global $wpdb;
        $defaults = array(
            'status' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        

        
        $where_clause = implode(' AND ', $where_conditions);
        $order_clause = sprintf('ORDER BY %s %s', 
            sanitize_sql_orderby($args['orderby']), 
            strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC'
        );
        
        $limit_clause = sprintf('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where_clause} {$order_clause} {$limit_clause}";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $where_values));
        }
        
        return $wpdb->get_results($sql);
    }

    // Lấy voucher active
    public function get_active() {
        return $this->get_all(array('status' => 'active'));
    }



    // Tăng số lần sử dụng voucher
    public function increment_used_count($id) {
        global $wpdb;
        return $wpdb->query($wpdb->prepare(
            "UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = %d",
            $id
        ));
    }

    // Kiểm tra voucher có khả dụng không
    public function is_voucher_available($id) {
        $voucher = $this->get_by_id($id);
        if (!$voucher) return false;
        
        // Kiểm tra trạng thái
        if ($voucher->status !== 'active') return false;
        
        // Kiểm tra hạn sử dụng
        if ($voucher->expiry_date && strtotime($voucher->expiry_date) < time()) {
            // Tự động cập nhật status thành expired
            $this->update($id, array('status' => 'expired'));
            return false;
        }
        
        // Kiểm tra giới hạn sử dụng
        if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
            return false;
        }
        
        return true;
    }

    // Lấy voucher theo user (từ user meta)
    public function get_user_vouchers($user_id) {
        $voucher_ids = get_user_meta($user_id, 'kam_user_vouchers', true);
        if (!$voucher_ids || !is_array($voucher_ids)) {
            return array();
        }
        
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($voucher_ids), '%d'));
        $sql = "SELECT * FROM {$this->table} WHERE id IN ($placeholders) ORDER BY created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $voucher_ids));
    }

    // Tặng voucher cho user (lưu vào user meta)
    public function give_voucher_to_user($user_id, $voucher_id, $order_id = null) {
        // Kiểm tra voucher có tồn tại không
        if (!$this->get_by_id($voucher_id)) {
            return false;
        }
        
        // Lấy danh sách voucher hiện tại của user
        $user_vouchers = get_user_meta($user_id, 'kam_user_vouchers', true);
        if (!is_array($user_vouchers)) {
            $user_vouchers = array();
        }
        
        // Thêm voucher mới (với thông tin thêm)
        $voucher_data = array(
            'voucher_id' => $voucher_id,
            'received_at' => current_time('mysql'),
            'order_id' => $order_id,
            'status' => 'received'
        );
        
        $user_vouchers[] = $voucher_data;
        
        // Cập nhật user meta
        return update_user_meta($user_id, 'kam_user_vouchers', $user_vouchers);
    }

    // Đánh dấu voucher đã sử dụng (trong user meta)
    public function mark_user_voucher_used($user_id, $voucher_id) {
        $user_vouchers = get_user_meta($user_id, 'kam_user_vouchers', true);
        if (!is_array($user_vouchers)) {
            return false;
        }
        
        foreach ($user_vouchers as $key => $voucher_data) {
            if ($voucher_data['voucher_id'] == $voucher_id && $voucher_data['status'] == 'received') {
                $user_vouchers[$key]['status'] = 'used';
                $user_vouchers[$key]['used_at'] = current_time('mysql');
                
                // Tăng số lần sử dụng trong bảng chính
                $this->increment_used_count($voucher_id);
                
                return update_user_meta($user_id, 'kam_user_vouchers', $user_vouchers);
            }
        }
        
        return false;
    }

    // Đếm số voucher của user
    public function count_user_vouchers($user_id, $status = '') {
        $user_vouchers = get_user_meta($user_id, 'kam_user_vouchers', true);
        if (!is_array($user_vouchers)) {
            return 0;
        }
        
        if (empty($status)) {
            return count($user_vouchers);
        }
        
        $count = 0;
        foreach ($user_vouchers as $voucher_data) {
            if ($voucher_data['status'] == $status) {
                $count++;
            }
        }
        
        return $count;
    }
}
