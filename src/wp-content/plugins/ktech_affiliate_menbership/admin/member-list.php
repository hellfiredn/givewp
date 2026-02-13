<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KTech_Member_List_Admin {
  
  public function __construct() {
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  public function add_admin_menu() {
    // Thêm submenu danh sách thành viên
    add_submenu_page(
      'ktech-affiliate-membership',
      'Danh sách thành viên',
      'Danh sách thành viên',
      'manage_options',
      'member-list',
      array($this, 'member_list_page')
    );
  }
  
  public function member_list_page() {
    echo '<div class="wrap">';
    echo '<h1>Danh sách thành viên</h1>';
    
    // Get current role filter from URL parameter
    $current_role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : 'all';
    
    // Display role filter tabs
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=member-list&role=all" class="nav-tab ' . ($current_role === 'all' ? 'nav-tab-active' : '') . '">Tất cả</a>';
    echo '<a href="?page=member-list&role=pharmer" class="nav-tab ' . ($current_role === 'pharmer' ? 'nav-tab-active' : '') . '">VIP Member</a>';
    echo '<a href="?page=member-list&role=pharmer_seller" class="nav-tab ' . ($current_role === 'pharmer_seller' ? 'nav-tab-active' : '') . '">Pharmer Seller</a>';
    echo '<a href="?page=member-list&role=master" class="nav-tab ' . ($current_role === 'master' ? 'nav-tab-active' : '') . '">Master</a>';
    echo '<a href="?page=member-list&role=vip_master" class="nav-tab ' . ($current_role === 'vip_master' ? 'nav-tab-active' : '') . '">VIP Master</a>';
    echo '<a href="?page=member-list&role=super_vip_master" class="nav-tab ' . ($current_role === 'super_vip_master' ? 'nav-tab-active' : '') . '">Super VIP Master</a>';
    // echo '<a href="?page=member-list&role=pending" class="nav-tab ' . ($current_role === 'pending' ? 'nav-tab-active' : '') . '">Chờ duyệt</a>';
    echo '</h2>';
    
    // Search form
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    echo '<div style="margin: 20px 0;">';
    echo '<form method="get" action="" style="display: flex; gap: 10px; max-width: 500px;">';
    echo '<input type="hidden" name="page" value="member-list">';
    echo '<input type="hidden" name="role" value="' . esc_attr($current_role) . '">';
    echo '<input type="text" name="s" value="' . esc_attr($search_term) . '" placeholder="Tìm kiếm theo tên, email, hoặc mã giới thiệu..." style="flex: 1; padding: 8px;">';
    echo '<button type="submit" class="button button-second">Tìm kiếm</button>';
    echo '</form>';
    echo '</div>';
    
    // Get users based on selected role
    $target_roles = ['pharmer', 'pharmer_seller', 'master', 'vip_master', 'super_vip_master'];
    
    $args = array(
      'meta_query' => array(),
      'number' => 1000,
      'orderby' => 'registered',
      'order' => 'DESC'
    );
    
    // if ($current_role === 'pending') {
    //   $args = array(
    //     'meta_key' => 'kam_approved',
    //     'meta_value' => 'no',
    //     'number' => 100,
    //     'orderby' => 'registered',
    //     'order' => 'DESC'
    //   );
    //   $users = get_users($args);
    //   echo '<h2>Thành viên chờ duyệt</h2>';
    // } elseif ($current_role !== 'all' && in_array($current_role, $target_roles)) {
    //   $args = array(
    //     'meta_key' => 'kam_approved',
    //     'meta_value' => 'yes',
    //     'number' => 100,
    //     'orderby' => 'registered',
    //     'order' => 'DESC'
    //   );
    //   $args['role'] = $current_role;
    //   $users = get_users($args);
    // } else {
    //   $args = array(
    //     'meta_key' => 'kam_approved',
    //     'meta_value' => 'yes',
    //     'number' => 100,
    //     'orderby' => 'registered',
    //     'order' => 'DESC'
    //   );
    //   $args['role__in'] = $target_roles;
    //   $users = get_users($args);
    // }

    if ($current_role !== 'all' && in_array($current_role, $target_roles)) {
      $args = array(
        'number' => 1000,
        'orderby' => 'registered',
        'order' => 'DESC'
      );
      $args['role'] = $current_role;
      
      // Add search query
      if (!empty($search_term)) {
        $args['search'] = '*' . $search_term . '*';
        $args['search_columns'] = array('user_login', 'user_email', 'display_name');
      }
      
      $users = get_users($args);
      
      // If search term exists, also search in user meta (ref code)
      if (!empty($search_term) && empty($users)) {
        $args['search'] = '';
        $args['meta_query'] = array(
          'relation' => 'OR',
          array(
            'key' => 'my_ref_code',
            'value' => $search_term,
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'ref_by',
            'value' => $search_term,
            'compare' => 'LIKE'
          )
        );
        $users = get_users($args);
      }
    } else {
      $args = array(
        'number' => 1000,
        'orderby' => 'registered',
        'order' => 'DESC'
      );
      $args['role__in'] = $target_roles;
      
      // Add search query
      if (!empty($search_term)) {
        $args['search'] = '*' . $search_term . '*';
        $args['search_columns'] = array('user_login', 'user_email', 'display_name');
      }
      
      $users = get_users($args);
      
      // If search term exists, also search in user meta (ref code)
      if (!empty($search_term) && empty($users)) {
        $args['search'] = '';
        $args['meta_query'] = array(
          'relation' => 'OR',
          array(
            'key' => 'my_ref_code',
            'value' => $search_term,
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'ref_by',
            'value' => $search_term,
            'compare' => 'LIKE'
          )
        );
        $users = get_users($args);
      }
    }
    
    // Display user count
    $total_count = count($users);
    if (!empty($search_term)) {
      echo '<p><strong>Kết quả tìm kiếm cho "' . esc_html($search_term) . '": ' . $total_count . ' thành viên</strong></p>';
    } else {
      echo '<p><strong>Tổng số thành viên: ' . $total_count . '</strong></p>';
    }
    
    // Display users table
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th style="width: 50px;">ID</th>';
    echo '<th>Tên hiển thị</th>';
    echo '<th>Email</th>';
    echo '<th>Vai trò</th>';
    echo '<th>Ref Code</th>';
    echo '<th>Người giới thiệu</th>';
    // echo '<th>Điểm LP</th>';
    // echo '<th>Doanh số tích lũy</th>';
    echo '<th>Ngày đăng ký</th>';
    echo '<th>Trạng thái</th>';
    echo '<th>Hành động</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if ($users) {
      foreach ($users as $user) {
        $user_roles = $user->roles;
        $primary_role = !empty($user_roles) ? $user_roles[0] : 'Không có';
        $ref_code = get_user_meta($user->ID, 'my_ref_code', true);
        
        $db = new KTech_Affiliate_DB();
        $total = $db->get_total($user_id);
        $total_sales = $total ? $total->total_refund : 0;
        $lp = $total ? $total->total_lp : 0;
        
        // Get referrer information
        $referrer_user_id = get_user_meta($user->ID, 'ref_by', true);
        $referrer_display = 'Không có';
        
        if ($referrer_user_id) {
          $users_referrer = get_users([
              'meta_key'   => 'my_ref_code',
              'meta_value' => $referrer_user_id,
              'fields'     => array('ID', 'display_name'),
              'number'     => 1
          ]);

          if ($users_referrer) {
            $referrer_display = '<a href="' . admin_url('user-edit.php?user_id=' . $users_referrer[0]->ID) . '">' . esc_html($users_referrer[0]->display_name) . '</a><br><small>(' . esc_html($referrer_user_id) . ')</small>';
          } else {
            $referrer_display = '<span style="color: #dc3545;">Không có</span>';
          }
        }
        
        // Get role display name
        $role_names = [
          'pharmer' => 'VIP Member',
          'pharmer_seller' => 'Pharmer Seller',
          'master' => 'Master',
          'vip_master' => 'VIP Master',
          'super_vip_master' => 'Super VIP Master'
        ];
        $role_display = $role_names[$primary_role] ?? $primary_role;
        
        echo '<tr>';
        echo '<td>' . $user->ID . '</td>';
        echo '<td><strong>' . esc_html($user->display_name) . '</strong><br><small>' . esc_html($user->user_login) . '</small></td>';
        echo '<td>' . esc_html($user->user_email) . '</td>';
        echo '<td><span class="role-badge role-' . esc_attr($primary_role) . '">' . esc_html($role_display) . '</span></td>';
        echo '<td>' . ($ref_code ? '<code>' . esc_html($ref_code) . '</code>' : 'Chưa có') . '</td>';
        echo '<td>' . $referrer_display . '</td>';
        // echo '<td>' . number_format($lp) . '</td>';
        // echo '<td>' . number_format($total_sales) . ' VNĐ</td>';
        echo '<td>' . date('d/m/Y', strtotime($user->user_registered)) . '</td>';
        // Trạng thái
        if ($current_role === 'pending') {
          echo '<td><span style="color:#ffc107;">Chưa duyệt</span></td>';
        } else {
          $locked = get_user_meta($user->ID, 'kam_locked', true);
          if ($locked === 'yes') {
            echo '<td><span style="color:#dc3545;font-weight:bold;">Đang khoá</span></td>';
          } else {
            echo '<td><span style="color:#28a745;">Đang hoạt động</span></td>';
          }
        }
        echo '<td>';
        if ($current_role === 'pending') {
          echo '<a href="?page=member-list&role=pending&approve_user=' . $user->ID . '" class="button button-small button-primary" onclick="return confirm(\'Duyệt thành viên này?\')">Duyệt</a> ';
          echo '<a href="?page=member-list&role=pending&delete_user=' . $user->ID . '" class="button button-small button-danger" onclick="return confirm(\'Bạn có chắc chắn muốn xoá thành viên này?\')">Xoá</a>';
        } else {
          echo '<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '" class="button button-small">Chỉnh sửa</a> ';
          $is_locked = get_user_meta($user->ID, 'kam_locked', true) === 'yes';
          if ($is_locked) {
            echo '<a href="?page=member-list&role=' . esc_attr($current_role) . '&unlock_user=' . $user->ID . '" class="button button-small button-success" onclick="return confirm(\'Mở khoá tài khoản này?\')">Mở tài khoản</a> ';
          } else {
            echo '<a href="?page=member-list&role=' . esc_attr($current_role) . '&lock_user=' . $user->ID . '" class="button button-small button-danger" onclick="return confirm(\'Khoá tài khoản này?\')">Khoá tài khoản</a> ';
          }
          echo '<a href="?page=member-list&role=' . esc_attr($current_role) . '&delete_user=' . $user->ID . '" class="button button-small button-danger" onclick="return confirm(\'Bạn có chắc chắn muốn xoá thành viên này?\')">Xoá</a>';
        }
        echo '</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="11">Không có thành viên nào.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    // Add CSS for role badges
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
    
    .wp-list-table th,
    .wp-list-table td {
      padding: 8px 10px;
    }
    
    .wp-list-table .column-primary {
      width: 200px;
    }
    </style>
    <?php
    
    // Xử lý duyệt thành viên
    if ($current_role === 'pending' && isset($_GET['approve_user'])) {
      $approve_id = intval($_GET['approve_user']);
      if ($approve_id) {
        update_user_meta($approve_id, 'kam_approved', 'yes');
        echo '<div class="updated notice"><p>Thành viên ID ' . $approve_id . ' đã được duyệt.</p></div>';
        $user_info = get_userdata($approve_id);
        if ($user_info && !empty($user_info->user_email)) {
          $subject = 'Hợp đồng/Thỏa thuận thành viên';
          $message = 'Chào ' . esc_html($user_info->display_name) . ",\n\n"
            . "Cảm ơn bạn đã đăng ký thành viên. Vui lòng kiểm tra file hợp đồng/thỏa thuận đính kèm.\n\n"
            . "Trân trọng,\nKTech Team";
          $headers = array('Content-Type: text/plain; charset=UTF-8');
          // Lấy đường dẫn file hợp đồng từ option
          $contract_file_url = get_option('ktech_contract_file_url', '');
          $attachment = '';
          if ($contract_file_url) {
            // Chuyển URL sang đường dẫn file vật lý
            $upload_dir = wp_upload_dir();
            if (strpos($contract_file_url, $upload_dir['baseurl']) === 0) {
              $attachment = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $contract_file_url);
            }
          }
          if ($attachment && file_exists($attachment)) {
            wp_mail($user_info->user_email, $subject, $message, $headers, array($attachment));
            echo '<div class="updated notice"><p>Đã gửi hợp đồng/thỏa thuận đến email: ' . esc_html($user_info->user_email) . '.</p></div>';
          } else {
            echo '<div class="error notice"><p>Không tìm thấy file hợp đồng/thỏa thuận để gửi.</p></div>';
          }
        }
        // Redirect using PHP after approval
        wp_redirect(admin_url('admin.php?page=member-list&role=pending'));
        exit;
      }
    }
    
    // Xử lý khoá tài khoản
    if ($current_role !== 'pending' && isset($_GET['lock_user'])) {
      $lock_id = intval($_GET['lock_user']);
      if ($lock_id) {
        update_user_meta($lock_id, 'kam_locked', 'yes');
        echo '<div class="error notice"><p>Đã khoá tài khoản ID ' . $lock_id . '.</p></div>';
      }
    }
    
    // Xử lý mở khoá tài khoản
    if ($current_role !== 'pending' && isset($_GET['unlock_user'])) {
      $unlock_id = intval($_GET['unlock_user']);
      if ($unlock_id) {
        update_user_meta($unlock_id, 'kam_locked', 'no');
        echo '<div class="updated notice"><p>Đã mở khoá tài khoản ID ' . $unlock_id . '.</p></div>';
      }
    }
    
    // Xử lý xoá thành viên
    if (isset($_GET['delete_user'])) {
      $delete_id = intval($_GET['delete_user']);
      if ($delete_id) {
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        wp_delete_user($delete_id);
        $address_db = new KTech_Address_DB();
        $upgrade_db = new KTech_Upgrade_Requests_DB();
        $affiliate_db = new KTech_Affiliate_DB();

        $affiliate_db->delete_by_user($delete_id);
        $address_db->delete_by_user($delete_id);
        $upgrade_db->delete_by_user($delete_id);
        echo '<div class="error notice"><p>Đã xoá thành viên ID ' . $delete_id . '.</p></div>';
      }
    }
  }
}

// Initialize the class
new KTech_Member_List_Admin();