<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KTech_My_Account_Admin {
  public function __construct() {
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  public function add_admin_menu() {
    // Thêm submenu Trang cá nhân thành viên
    add_submenu_page(
      'ktech-affiliate-membership',
      'Trang cá nhân thành viên',
      'Trang cá nhân thành viên',
      'manage_options',
      'my-account-setting',
      array($this, 'my_account_page')
    );
  }

  public function my_account_page() {
    echo '<div class="wrap">';
    echo '<h1>Trang cá nhân thành viên</h1>';

    // Lấy danh sách role
    global $wp_roles;
    $roles = $wp_roles->roles;

    $refund_fields = [
      'personal_sales' => 'DS cá nhân',
      'order_count' => 'SL đơn hàng',
      'direct_sales' => 'DS Trực tiếp',
      'indirect_sales' => 'DS gián tiếp',
      'lp_points' => 'Điểm thưởng LP',
      'total_sales' => 'Tổng doanh số',
      'refund' => 'Hoàn tiền'
    ];

    // Lưu dữ liệu khi submit vào option page
    if (isset($_POST['refund_fields']) && is_array($_POST['refund_fields']) && isset($_POST['role'])) {
      $role = sanitize_text_field($_POST['role']);
      $all_settings = (array) get_option('kam_refund_fields_by_role', []);
      $all_settings[$role] = $_POST['refund_fields'];
      update_option('kam_refund_fields_by_role', $all_settings);
      echo '<div class="updated notice"><p>Đã lưu cài đặt hoàn tiền cho role!</p></div>';
    }

    $all_settings = (array) get_option('kam_refund_fields_by_role', []);

    echo '<form method="post">';
    echo '<table class="form-table">';
    foreach ($roles as $role_key => $role_data) {
      $saved_refund_fields = isset($all_settings[$role_key]) ? (array) $all_settings[$role_key] : [];
      echo '<tr><th colspan="2"><span>' . esc_html($role_data['name']) . '</span></th></tr>';
      foreach ($refund_fields as $key => $label) {
      $checked = in_array($key, $saved_refund_fields) ? 'checked' : '';
      echo '<tr>';
      echo '<td style="width:40px;"><input type="checkbox" name="refund_fields[' . esc_attr($role_key) . '][]" value="' . esc_attr($key) . '" ' . $checked . '></td>';
      echo '<td>' . esc_html($label) . '</td>';
      echo '</tr>';
      }
    }
    echo '</table>';
    echo '<p><button type="submit" class="button button-primary">Lưu cài đặt hoàn tiền</button></p>';
    echo '</form>';
    echo '</div>';

    // Xử lý lưu dữ liệu cho từng role
    if (isset($_POST['refund_fields']) && is_array($_POST['refund_fields'])) {
      $all_settings = [];
      foreach ($_POST['refund_fields'] as $role_key => $fields) {
      $all_settings[$role_key] = array_map('sanitize_text_field', $fields);
      }
      update_option('kam_refund_fields_by_role', $all_settings);
    }
  ?>
    <style>
    .role-badge {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      color: white;
      text-transform: uppercase;
    }
    .role-pharmer { background-color: #28a745; }
    .role-pharmer_seller { background-color: #17a2b8; }
    .role-master { background-color: #ffc107; color: #000; }
    .role-vip_master { background-color: #dc3545; }
    .role-super_vip_master { background-color: #6f42c1; }
    .form-table th, .form-table td { padding: 8px 10px; }
    </style>
  <?php
  }
}

// Initialize the class
new KTech_My_Account_Admin();
