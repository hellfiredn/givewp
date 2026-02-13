<?php

class KTech_Address_DB {
    private $table;

    public function __construct() {
      global $wpdb;
      $this->table = $wpdb->prefix . 'kam_user_addresses';
    }

    // Tạo bảng (gọi khi kích hoạt plugin)
    public static function install_table() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'kam_user_addresses';
      $charset_collate = $wpdb->get_charset_collate();
      $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        city VARCHAR(100),
        district VARCHAR(100),
        commune VARCHAR(100),
        address TEXT,
        type VARCHAR(50),
        is_default TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY is_default (is_default)
      ) $charset_collate;";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
    }

    // Thêm địa chỉ mới
    public function insert($data) {
      global $wpdb;
      
      // Nếu đặt làm mặc định, bỏ mặc định của các địa chỉ khác
      if (!empty($data['is_default'])) {
        $this->unset_default_addresses($data['user_id']);
      }
      
      $result = $wpdb->insert($this->table, [
        'user_id'    => $data['user_id'],
        'name'       => $data['name'],
        'phone'      => $data['phone'],
        'city'       => $data['city'],
        'district'   => $data['district'],
        'commune'    => $data['commune'],
        'address'    => $data['address'],
        'type'       => $data['type'],
        'is_default' => $data['is_default'] ?? 0,
        'created_at' => current_time('mysql')
      ]);
      
      return $result ? $wpdb->insert_id : false;
    }

    // Cập nhật địa chỉ
    public function update($id, $data) {
      global $wpdb;
      
      // Nếu đặt làm mặc định, bỏ mặc định của các địa chỉ khác
      if (!empty($data['is_default'])) {
        $address = $this->get_by_id($id);
        if ($address) {
          $this->unset_default_addresses($address->user_id);
        }
      }
      
      $update_data = [
        'name'       => $data['name'],
        'phone'      => $data['phone'],
        'city'       => $data['city'],
        'district'   => $data['district'],
        'commune'    => $data['commune'],
        'address'    => $data['address'],
        'type'       => $data['type'],
        'is_default' => $data['is_default'] ?? 0,
        'updated_at' => current_time('mysql')
      ];
      
      return $wpdb->update($this->table, $update_data, ['id' => $id]);
    }

    // Xóa địa chỉ
    public function delete($id) {
      global $wpdb;
      return $wpdb->delete($this->table, ['id' => $id]);
    }

    // Lấy địa chỉ theo ID
    public function get_by_id($id) {
      global $wpdb;
      return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$this->table} WHERE id = %d",
        $id
      ));
    }

    // Lấy tất cả địa chỉ của user
    public function get_by_user($user_id) {
      global $wpdb;
      return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$this->table} WHERE user_id = %d ORDER BY is_default DESC, created_at DESC",
        $user_id
      ));
    }

    // Lấy địa chỉ mặc định của user
    public function get_default_address($user_id) {
      global $wpdb;
      return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$this->table} WHERE user_id = %d AND is_default = 1 LIMIT 1",
        $user_id
      ));
    }

    // Đặt địa chỉ làm mặc định
    public function set_default($id) {
      global $wpdb;
      
      // Lấy thông tin địa chỉ
      $address = $this->get_by_id($id);
      if (!$address) return false;
      
      // Bỏ mặc định của các địa chỉ khác
      $this->unset_default_addresses($address->user_id);
      
      // Đặt địa chỉ này làm mặc định
      return $wpdb->update(
        $this->table,
        ['is_default' => 1, 'updated_at' => current_time('mysql')],
        ['id' => $id]
      );
    }

    // Bỏ mặc định tất cả địa chỉ của user
    private function unset_default_addresses($user_id) {
      global $wpdb;
      return $wpdb->update(
        $this->table,
        ['is_default' => 0],
        ['user_id' => $user_id]
      );
    }

    // Kiểm tra user có địa chỉ mặc định hay không
    public function has_default_address($user_id) {
      global $wpdb;
      $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d AND is_default = 1",
        $user_id
      ));
      return (int)$count > 0;
    }

    // Đếm số lượng địa chỉ của user
    public function count_by_user($user_id) {
      global $wpdb;
      return (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d",
        $user_id
      ));
    }

    // Xóa tất cả địa chỉ theo user_id
    public function delete_by_user($user_id) {
      global $wpdb;
      return $wpdb->delete($this->table, ['user_id' => $user_id]);
    }
}
