<?php

class KTech_Affiliate_DB {
  private $table;

  public function __construct() {
    global $wpdb;
    $this->table = $wpdb->prefix . 'kam_affiliate_stats';
  }

  // Tạo bảng (gọi khi kích hoạt plugin)
  public static function install_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'kam_affiliate_stats';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      order_id BIGINT(20) UNSIGNED,
      order_username VARCHAR(255),
      order_userrole VARCHAR(100),
      type VARCHAR(50) NOT NULL,
      amount DOUBLE DEFAULT 0,
      lp DOUBLE DEFAULT 0,
      refund DOUBLE DEFAULT 0,
      note TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY  (id),
      KEY user_id (user_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  // Thêm bản ghi mới
  public function insert($data) {
    global $wpdb;
    $wpdb->insert($this->table, [
      'user_id'         => $data['user_id'],
      'order_id'        => $data['order_id'],
      'order_username'  => $data['order_username'],
      'order_userrole'  => $data['order_userrole'],
      'type'            => $data['type'],
      'amount'          => $data['amount'],
      'lp'              => $data['lp'],
      'refund'          => $data['refund'],
      'note'            => $data['note'],
      'created_at'      => current_time('mysql')
    ]);
    return $wpdb->insert_id;
  }

  // Lấy lịch sử theo user
  public function get_by_user($user_id, $limit = 20) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM {$this->table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
      $user_id, $limit
    ));
  }

  // Lấy tổng doanh số/LP theo user và loại
  public function get_total($user_id, $type = '', $date_range = '') {
    global $wpdb;
    $sql = "SELECT SUM(amount) as total_amount, SUM(lp) as total_lp, SUM(refund) as total_refund FROM {$this->table} WHERE user_id = %d";
    $params = [$user_id];

    if ($type) {
      $sql .= " AND type = %s";
      $params[] = $type;
    }

    if ($date_range) {
      // Expecting format: "dd-mm-yyyy - dd-mm-yyyy"
      $dates = explode(' - ', $date_range);
      if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d-m-Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d-m-Y', trim($dates[1]));
        if ($start && $end) {
          $sql .= " AND created_at BETWEEN %s AND %s";
          $params[] = $start->format('Y-m-d 00:00:00');
          $params[] = $end->format('Y-m-d 23:59:59');
        }
      }
    }

    return $wpdb->get_row($wpdb->prepare($sql, ...$params));
  }

  // Lấy top 10 user có amount cao nhất theo tháng/năm với order_userrole = master
  public function get_top_users_by_month($month_year = '') {
    global $wpdb;

    $user_master_ids = $wpdb->get_col(
      $wpdb->prepare(
      "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
      $wpdb->get_blog_prefix() . 'capabilities',
      '%master%'
      )
    );

    // Nếu không có user master thì trả về mảng rỗng
    if (empty($user_master_ids)) {
      return [];
    }

    $sql = "SELECT s.user_id, u.display_name, SUM(s.amount) as total_amount 
      FROM {$this->table} s
      JOIN {$wpdb->users} u ON s.user_id = u.ID
      WHERE s.user_id IN (" . implode(',', array_map('intval', $user_master_ids)) . ")";
    
    $params = [];
    if ($month_year) {
      // Parse month-year format (6-2025)
      $parts = explode('-', $month_year);
      if (count($parts) == 2) {
        $month = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $year = $parts[1];
        
        $sql .= " AND YEAR(created_at) = %d AND MONTH(created_at) = %d";
        $params[] = $year;
        $params[] = $month;
      }
    }
    
    $sql .= " GROUP BY user_id ORDER BY total_amount DESC LIMIT 10";

    if (!empty($params)) {
      return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    } else {
      return $wpdb->get_results($sql);
    }
  }

  // Lấy số lượng đơn hàng theo user và loại
  public function get_order_count($user_id, $type = '', $date_range = '') {
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d";
    $params = [$user_id];

    if ($type) {
      $sql .= " AND type = %s";
      $params[] = $type;
    }

    if ($date_range) {
      // Expecting format: "dd-mm-yyyy - dd-mm-yyyy"
      $dates = explode(' - ', $date_range);
      if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d-m-Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d-m-Y', trim($dates[1]));
        if ($start && $end) {
          $sql .= " AND created_at BETWEEN %s AND %s";
          $params[] = $start->format('Y-m-d 00:00:00');
          $params[] = $end->format('Y-m-d 23:59:59');
        }
      }
    }

    return (int)$wpdb->get_var($wpdb->prepare($sql, ...$params));
  }

  // Xoá tất cả bản ghi theo user_id
  public function delete_by_user($user_id) {
    global $wpdb;
    return $wpdb->query($wpdb->prepare("DELETE FROM {$this->table} WHERE user_id = %d", $user_id));
  }

  public function get_orders_direct_indirect($user_id, $date_range = '') {
    global $wpdb;
    $types = ['direct', 'indirect'];
    $placeholders = implode(',', array_fill(0, count($types), '%s'));
    $sql = "SELECT user_id, order_id, order_username, order_userrole, amount, created_at FROM {$this->table} WHERE user_id = %d AND type IN ($placeholders)";
    $params = array_merge([$user_id], $types);

    if ($date_range) {
      // Expecting format: "dd-mm-yyyy - dd-mm-yyyy"
      $dates = explode(' - ', $date_range);
      if (count($dates) == 2) {
        $start = DateTime::createFromFormat('d-m-Y', trim($dates[0]));
        $end = DateTime::createFromFormat('d-m-Y', trim($dates[1]));
        if ($start && $end) {
          $sql .= " AND created_at BETWEEN %s AND %s";
          $params[] = $start->format('Y-m-d 00:00:00');
          $params[] = $end->format('Y-m-d 23:59:59');
        }
      }
    }

    $sql .= " ORDER BY created_at DESC";
    return $wpdb->get_results($wpdb->prepare($sql, ...$params));
  }
}