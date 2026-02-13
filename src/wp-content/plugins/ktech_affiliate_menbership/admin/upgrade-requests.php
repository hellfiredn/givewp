<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KTech_Upgrade_Requests_Admin {
  
  public function __construct() {
    add_action('admin_menu', array($this, 'add_admin_menu'));
    add_action('wp_ajax_handle_upgrade_request', array($this, 'handle_upgrade_request'));
  }

  public function add_admin_menu() {
    // Tạo parent menu trước (nếu chưa có)
    add_menu_page(
      'KTech Affiliate Membership',
      'Affiliate Membership',
      'manage_options',
      'ktech-affiliate-membership',
      array($this, 'main_admin_page'),
      'dashicons-groups',
      30
    );

    // Thêm submenu
    add_submenu_page(
      'ktech-affiliate-membership',
      'Yêu cầu nâng cấp',
      'Yêu cầu nâng cấp',
      'manage_options',
      'upgrade-requests',
      array($this, 'upgrade_requests_page')
    );
  }

  public function main_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Thiết lập chung</h1>';
    // Nút và popup thêm role
    echo '<button type="button" id="add-role-btn" class="button">Thêm role</button>';
    echo '<div id="add-role-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;align-items:center;justify-content:center;">';
    echo '<div style="background:#fff;padding:30px;border-radius:8px;min-width:300px;max-width:90vw;position:relative;">';
    echo '<h2>Thêm role mới</h2>';
    echo '<input type="text" id="new-role-name" placeholder="Tên role mới" style="width:100%;margin-bottom:15px;">';
    echo '<button type="button" id="save-new-role" class="button button-primary">Thêm</button> ';
    echo '<button type="button" id="close-role-modal" class="button">Đóng</button>';
    echo '<div id="add-role-error" style="color:red;margin-top:10px;"></div>';
    echo '</div></div>';
    echo '<form id="add-role-form" method="post" style="display:none;">';
    echo '<input type="hidden" name="add_new_role" value="1">';
    echo '<input type="hidden" name="new_role_name" id="new_role_name_hidden">';
    echo '</form>';
    // Xử lý thêm role vào $wp_roles
    if (isset($_POST['add_new_role']) && !empty($_POST['new_role_name'])) {
      $new_role_name = sanitize_text_field($_POST['new_role_name']);
      $new_key = sanitize_title($new_role_name);
      if (!get_role($new_key)) {
        add_role($new_key, $new_role_name);
        echo '<div class="updated notice"><p>Đã thêm role mới vào hệ thống!</p></div>';
      } else {
        echo '<div class="error notice"><p>Tên role đã tồn tại!</p></div>';
      }
    }
    ?>
    <script>
    jQuery(document).ready(function($){
      $('#add-role-btn').on('click', function(){
        $('#add-role-modal').css('display','flex');
        $('#add-role-error').text('');
        $('#new-role-name').val('');
      });
      $('#close-role-modal').on('click', function(){
        $('#add-role-modal').hide();
      });
      $('#save-new-role').on('click', function(){
        var roleName = $('#new-role-name').val().trim();
        if(!roleName){
          $('#add-role-error').text('Vui lòng nhập tên role!');
          return;
        }
        $('#new_role_name_hidden').val(roleName);
        $('#add-role-form').submit();
      });
    });
    </script>
    <?php
    echo '<div class="wrap">';
    echo '<h1>Thiết lập chung</h1>';

    // Các loại tài khoản mặc định
    // Lấy tất cả role hiện tại trong hệ thống
    global $wp_roles;
    $default_roles = [];
    foreach ($wp_roles->roles as $key => $role) {
      $default_roles[$key] = $role['name'];
    }
    $roles_setting = get_option('ktech_account_roles_setting', []);
    if (!$roles_setting) {
      foreach ($default_roles as $key => $label) {
        $roles_setting[$key] = [
          'name' => $label,
          'commission_directly' => 0,
          'commission_indirect' => 0,
          'discount' => 0,
          'lp' => 0,
          'allow_register' => 'no',
        ];
      }
    }

    // Lưu dữ liệu khi submit
    if (isset($_POST['save_roles_setting'])) {
      // Lưu 3 URL chính sách
      update_option('ktech_url_terms', sanitize_text_field($_POST['url_terms'] ?? ''));
      update_option('ktech_url_personal', sanitize_text_field($_POST['url_personal'] ?? ''));
      update_option('ktech_url_thirdparty', sanitize_text_field($_POST['url_thirdparty'] ?? ''));
      if (!empty($_POST['role_commission_directly_guest'])) {
        update_option('commission_directly_guest', $_POST['role_commission_directly_guest']);
      }
      if (!empty($_POST['role_commission_indirect_guest'])) {
        update_option('commission_indirect_guest', $_POST['role_commission_indirect_guest']);
      }
      foreach ($default_roles as $key => $label) {
        $roles_setting[$key]['name'] = sanitize_text_field($_POST['role_name_' . $key] ?? $label);
        $roles_setting[$key]['commission_directly'] = floatval($_POST['role_commission_directly_' . $key] ?? 0);
        $roles_setting[$key]['commission_indirect'] = floatval($_POST['role_commission_indirect_' . $key] ?? 0);
        $roles_setting[$key]['lp'] = floatval($_POST['role_lp_' . $key] ?? 0);
        $roles_setting[$key]['discount'] = floatval($_POST['role_discount_' . $key] ?? 0);
        $roles_setting[$key]['allow_register'] = sanitize_text_field($_POST['role_allow_register_' . $key] ?? '');
        $roles_setting[$key]['price_type_avaliable'] = sanitize_text_field($_POST['role_price_type_avaliable_' . $key] ?? '');
      }
      // Xử lý upload file hợp đồng/thỏa thuận section riêng
      if (!empty($_FILES['contract_file']['name'])) {
        $file = $_FILES['contract_file'];
        $upload = wp_handle_upload($file, array('test_form' => false));
        if (!isset($upload['error']) && isset($upload['url'])) {
          update_option('ktech_contract_file_url', $upload['url']);
        }
      }
      // Lưu cấu hình API Pancake
      $api_key = sanitize_text_field($_POST['api_key'] ?? '');
      $shop_id = sanitize_text_field($_POST['shop_id'] ?? '');
      update_option('api_key_pancake', $api_key);
      update_option('shop_id_pancake', $shop_id);
      update_option('ktech_account_roles_setting', $roles_setting);
      echo '<div class="updated notice"><p>Đã lưu thiết lập!</p></div>';
    }

    echo '<form method="post" enctype="multipart/form-data">';
    // Thêm cấu hình URL chính sách
    echo '<hr><h3>Cấu hình các URL chính sách</h3>';
    $url_terms = get_option('ktech_url_terms', '/dieu-khoan-su-dung-chinh-sach-bao-mat/');
    $url_personal = get_option('ktech_url_personal', '#');
    $url_thirdparty = get_option('ktech_url_thirdparty', '#');
    echo '<table class="form-table">';
    echo '<tr><th scope="row"><label for="url_terms">URL Điều khoản & Chính sách bảo mật</label></th>';
    echo '<td><input type="text" name="url_terms" id="url_terms" value="' . esc_attr($url_terms) . '" class="regular-text"></td></tr>';
    echo '<tr><th scope="row"><label for="url_personal">URL Chính sách xử lý thông tin cá nhân</label></th>';
    echo '<td><input type="text" name="url_personal" id="url_personal" value="' . esc_attr($url_personal) . '" class="regular-text"></td></tr>';
    echo '<tr><th scope="row"><label for="url_thirdparty">URL Chính sách cung cấp thông tin cho bên thứ ba</label></th>';
    echo '<td><input type="text" name="url_thirdparty" id="url_thirdparty" value="' . esc_attr($url_thirdparty) . '" class="regular-text"></td></tr>';
    echo '</table>';
    // Tiếp tục bảng thiết lập role
    echo '<table class="form-table">';
    echo '<tr><th>Tên loại tài khoản</th>
    <th>% hoa hồng trực tiếp</th>
    <th>% hoa hồng gián tiếp</th>
    <th>Điểm thưởng LP (VNĐ/LP)</th>
    <th>% giảm giá khi mua hàng</th>
    <th>Cho phép đăng ký</th>
    <th>Giá áp dụng</th>';
    echo '<th></th>';
    echo '</tr>';
    echo '<tr>';
      echo '<td><input style="color:#000;" disabled type="text" name="role_name_guest" value="Khách ngoài" required></td>';
      echo '<td><input type="number" name="role_commission_directly_guest" value="' . esc_attr(get_option('commission_directly_guest', 0)) . '" min="0" max="100" /> %</td>';
      echo '<td><input type="number" name="role_commission_indirect_guest" value="' . esc_attr(get_option('commission_indirect_guest', 0)) . '" min="0" max="100" /> %</td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '<td></td>';
    echo '</tr>';
    foreach ($default_roles as $key => $label) {
      $setting = $roles_setting[$key];
      echo '<tr>';
      echo '<td><input style="color:#000;" disabled type="text" name="role_name_' . esc_attr($key) . '" value="' . esc_attr($label) . '" required></td>';
      echo '<td><input type="number" name="role_commission_directly_' . esc_attr($key) . '" value="' . esc_attr($setting['commission_directly']) . '" min="0" max="100" /> %</td>';
      echo '<td><input type="number" name="role_commission_indirect_' . esc_attr($key) . '" value="' . esc_attr($setting['commission_indirect']) . '" min="0" max="100" /> %</td>';
      echo '<td><input type="number" name="role_lp_' . esc_attr($key) . '" value="' . esc_attr($setting['lp']) . '" min="0" /> VNĐ</td>';
      echo '<td><input type="number" name="role_discount_' . esc_attr($key) . '" value="' . esc_attr($setting['discount']) . '" min="0" max="100" /> %</td>';
      $allow_register = isset($setting['allow_register']) ? $setting['allow_register'] : 'no';
      echo '<td>
        <select name="role_allow_register_' . esc_attr($key) . '">
          <option value="no"' . ($allow_register === 'no' ? ' selected' : '') . '>Không</option>
          <option value="yes"' . ($allow_register === 'yes' ? ' selected' : '') . '>Có</option>
        </select>
      </td>';
      $price_type_avaliable = isset($setting['price_type_avaliable']) ? $setting['price_type_avaliable'] : 'customer';
      echo '<td><select name="role_price_type_avaliable_' . esc_attr($key) . '">';
      if ($key == 'pharmer') {
        echo '<option value="pharmer" selected>Pharmer</option>';
      } else if ($key == 'master') {
        echo '<option value="master" selected>Master</option>';
      } else if ($key == 'pharmer_seller') {
        echo '<option value="pharmer" selected>Pharmer</option>';
      } else {
        echo '<option value="customer"' . ($price_type_avaliable === 'customer' ? ' selected' : '') . '>Customer</option>
        <option value="pharmer"' . ($price_type_avaliable === 'pharmer' ? ' selected' : '') . '>Pharmer</option>
        <option value="master"' . ($price_type_avaliable === 'master' ? ' selected' : '') . '>Master</option>';
      }
      echo '</select></td>';
      echo '<td><button type="button" class="button delete-role-btn" data-role="' . esc_attr($key) . '">Xoá</button></td>';
      echo '</tr>';
    }
    echo '</table>';
    // Popup xác nhận xoá role
    echo '<div id="delete-role-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:30px;border-radius:8px;min-width:300px;max-width:90vw;position:relative;">
        <h2>Xác nhận xoá role</h2>
        <p id="delete-role-name"></p>
        <button type="button" id="confirm-delete-role" class="button button-primary">Xác nhận</button> 
        <button type="button" id="cancel-delete-role" class="button">Huỷ</button>
        <div id="delete-role-error" style="color:red;margin-top:10px;"></div>
      </div>
    </div>';
    echo '<hr><h3>Hợp đồng/Thỏa thuận</h3>';
    echo '<div style="margin-bottom:20px;">';
    $contract_file_url = get_option('ktech_contract_file_url', '');
    if ($contract_file_url) {
      $file_name = basename($contract_file_url);
      echo '<a href="' . esc_url($contract_file_url) . '" target="_blank">' . esc_html($file_name) . '</a><br>';
    }
    echo '<input type="file" name="contract_file" accept=".pdf,.doc,.docx,.jpg,.png">';
    echo '</div>';
    // Thêm cấu hình API Pancake vào form này
    echo '<hr><h3>Cấu hình API Pancake</h3>';
    $api_key = get_option('api_key_pancake', '');
    $shop_id = get_option('shop_id_pancake', '');
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="api_key">API Key</label></th>';
    echo '<td><input type="text" name="api_key" id="api_key" value="' . esc_attr($api_key) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="shop_id">Shop ID</label></th>';
    echo '<td><input type="text" name="shop_id" id="shop_id" value="' . esc_attr($shop_id) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '</table>';
    // Gọi API lấy danh sách đối tác vận chuyển
    $api_key = get_option('api_key_pancake', '');
    $shop_id = get_option('shop_id_pancake', '');
    // Lưu lựa chọn đối tác vận chuyển khi submit
    $api_partners_data = [];
    if ($api_key && $shop_id) {
      $api_url = "https://pos.pages.fm/api/v1/shops/{$shop_id}/partners?api_key={$api_key}";
      $response = wp_remote_get($api_url);
      if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!empty($data['success']) && !empty($data['data'])) {
          $api_partners_data = $data['data'];
        }
      }
    }
    if (isset($_POST['save_roles_setting'])) {
      $selected_partners = [];
      if (isset($_POST['shipping_partners'])) {
        foreach ($_POST['shipping_partners'] as $partner_id) {
          foreach ($api_partners_data as $partner) {
            if ($partner['id'] == $partner_id) {
              $selected_partners[] = [
                'id' => sanitize_text_field($partner['id']),
                'name' => sanitize_text_field($partner['name'])
              ];
              break;
            }
          }
        }
      }
      update_option('pancake_shipping_partners', $selected_partners);
    }
    $saved_partners = get_option('pancake_shipping_partners', []);
    if ($api_key && $shop_id) {
      $api_url = "https://pos.pages.fm/api/v1/shops/{$shop_id}/partners?api_key={$api_key}";
      $response = wp_remote_get($api_url);
      if (is_wp_error($response)) {
      echo '<pre>Lỗi khi gọi API: ' . esc_html($response->get_error_message()) . '</pre>';
      } else {
      $body = wp_remote_retrieve_body($response);
      $data = json_decode($body, true);
      if (!empty($data['success']) && !empty($data['data'])) {
        echo '<hr><h3>Đối tác vận chuyển</h3>';
        echo '<div style="margin-bottom:20px;">';
        foreach ($data['data'] as $partner) {
          $checked = '';
          foreach ($saved_partners as $saved) {
            if (isset($saved['id']) && $saved['id'] == $partner['id']) {
              $checked = 'checked';
              break;
            }
          }
          echo '<label style="display: block;margin-right:15px;">';
          echo '<input type="checkbox" name="shipping_partners[]" value="' . esc_attr($partner['id']) . '" ' . $checked . '> ';
          echo esc_html($partner['name']);
          echo '</label>';
        }
        echo '</div>';
      } else {
        echo '<pre>Lỗi dữ liệu API hoặc không có đối tác vận chuyển.</pre>';
      }
      }
    } else {
      echo '<pre>Vui lòng nhập API Key và Shop ID để lấy danh sách đối tác vận chuyển.</pre>';
    }
    echo '<p><button type="submit" name="save_roles_setting" class="button button-primary">Lưu thiết lập</button></p>';
    echo '</form>';
    echo '</div>';
  }

  public function upgrade_requests_page() {
    echo '<div class="wrap">';
    echo '<h1>Yêu cầu nâng cấp</h1>';
    
    // Get current tab from URL parameter
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'pending';
    
    // Display tabs
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=upgrade-requests&tab=all" class="nav-tab ' . ($current_tab === 'all' ? 'nav-tab-active' : '') . '">Tất cả</a>';
    echo '<a href="?page=upgrade-requests&tab=pending" class="nav-tab ' . ($current_tab === 'pending' ? 'nav-tab-active' : '') . '">Chờ duyệt</a>';
    echo '<a href="?page=upgrade-requests&tab=approved" class="nav-tab ' . ($current_tab === 'approved' ? 'nav-tab-active' : '') . '">Đã duyệt</a>';
    echo '<a href="?page=upgrade-requests&tab=rejected" class="nav-tab ' . ($current_tab === 'rejected' ? 'nav-tab-active' : '') . '">Đã từ chối</a>';
    echo '</h2>';
    
    // Sử dụng class database để lấy dữ liệu
    $db = new KTech_Upgrade_Requests_DB();
    
    // Get requests based on selected tab
    switch ($current_tab) {
      case 'approved':
        $requests = $db->get_approved();
        break;
      case 'rejected':
        $requests = $db->get_rejected();
        break;
      case 'all':
        $requests = $db->get_all();
        break;
      case 'pending':
      default:
        $requests = $db->get_pending();
        break;
    }
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>User</th>';
    echo '<th>Tên doanh nghiệp</th>';
    echo '<th>Người đại diện</th>';
    echo '<th>Địa chỉ kinh doanh</th>';
    echo '<th>Mã số DN</th>';
    echo '<th>File GPKD</th>';
    echo '<th>SĐT liên hệ</th>';
    echo '<th>Gói hiện tại</th>';
    echo '<th>Gói muốn nâng cấp</th>';
    echo '<th>Ngày tạo</th>';
    echo '<th>Trạng thái</th>';
    if ($current_tab === 'pending') {
      echo '<th>Hành động</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if ($requests) {
      foreach ($requests as $request) {
      echo '<tr>';
      echo '<td>' . (isset($request->id) ? $request->id : '') . '</td>';
      echo '<td><strong>' . esc_html(isset($request->user_login) ? $request->user_login : '') . '</strong><br>' . esc_html(isset($request->user_email) ? $request->user_email : '') . '</td>';
      echo '<td>' . esc_html(isset($request->company_name) ? $request->company_name : '') . '</td>';
      echo '<td>' . esc_html(isset($request->representative_name) ? $request->representative_name : '') . '</td>';
      echo '<td>' . esc_html(isset($request->business_address) ? $request->business_address : '') . '</td>';
      echo '<td>' . esc_html(isset($request->business_code) ? $request->business_code : '') . '</td>';
      echo '<td>';
      if (isset($request->business_license_file) && $request->business_license_file) {
        echo '<a href="' . esc_url($request->business_license_file) . '" target="_blank">Xem file</a>';
      } else {
        echo 'Chưa có file';
      }
      echo '</td>';
      echo '<td>' . esc_html(isset($request->contact_phone) ? $request->contact_phone : '') . '</td>';
      echo '<td>' . esc_html(isset($request->current_package) ? $request->current_package : '') . '</td>';
      echo '<td>' . esc_html(isset($request->requested_package) ? $request->requested_package : '') . '</td>';
      echo '<td>' . (isset($request->created_at) ? date('d/m/Y H:i', strtotime($request->created_at)) : '') . '</td>';
      echo '<td>';
      $status_label = '';
      $status_class = '';
      $status = isset($request->status) ? $request->status : 'pending';
      switch ($status) {
        case 'pending':
          $status_label = 'Chờ duyệt';
          $status_class = 'status-pending';
          break;
        case 'approved':
          $status_label = 'Đã duyệt';
          $status_class = 'status-approved';
          break;
        case 'rejected':
          $status_label = 'Đã từ chối';
          $status_class = 'status-rejected';
          break;
      }
      echo '<span class="' . $status_class . '">' . $status_label . '</span>';
      echo '</td>';
      
      if ($current_tab === 'pending') {
        echo '<td>';
        echo '<button class="button button-primary approve-btn" data-id="' . (isset($request->id) ? $request->id : '') . '">Duyệt</button> ';
        echo '<button class="button button-secondary reject-btn" data-id="' . (isset($request->id) ? $request->id : '') . '">Từ chối</button>';
        echo '</td>';
      }
      echo '</tr>';
      }
    } else {
      $colspan = ($current_tab === 'pending') ? '13' : '12';
      echo '<tr><td colspan="' . $colspan . '">Không có yêu cầu nâng cấp nào.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    // Add CSS for status styling
    ?>
    <style>
    .status-pending { color: #f56e28; font-weight: bold; }
    .status-approved { color: #46b450; font-weight: bold; }
    .status-rejected { color: #dc3232; font-weight: bold; }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
      $('.approve-btn').on('click', function() {
      var requestId = $(this).data('id');
      if (confirm('Bạn có chắc chắn muốn duyệt yêu cầu này?')) {
        handleAction('approve', requestId, $(this));
      }
      });
      
      $('.reject-btn').on('click', function() {
      var requestId = $(this).data('id');
      var reason = prompt('Lý do từ chối (tùy chọn):');
      if (reason !== null) {
        handleAction('reject', requestId, $(this), reason);
      }
      });
      
      function handleAction(action, requestId, button, reason) {
      button.prop('disabled', true).text('Đang xử lý...');
      
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
        action: 'handle_upgrade_request',
        request_action: action,
        request_id: requestId,
        reason: reason || '',
        nonce: '<?php echo wp_create_nonce('upgrade_request_nonce'); ?>'
        },
        success: function(response) {
        if (response.success) {
          alert(response.data);
          location.reload();
        } else {
          alert('Lỗi: ' + response.data);
          button.prop('disabled', false).text(action === 'approve' ? 'Duyệt' : 'Từ chối');
        }
        },
        error: function() {
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
        button.prop('disabled', false).text(action === 'approve' ? 'Duyệt' : 'Từ chối');
        }
      });
      }
    });
    </script>
    <?php
  }
  
  public function handle_upgrade_request() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'upgrade_request_nonce')) {
      wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Permission denied');
    }
    
    $request_id = intval($_POST['request_id']);
    $action = sanitize_text_field($_POST['request_action']);
    $reason = sanitize_textarea_field($_POST['reason'] ?? '');
    
    $db = new KTech_Upgrade_Requests_DB();
    
    if ($action === 'approve') {
      // Lấy thông tin request trước khi approve
      $request = $db->get_by_id($request_id);
      
      if (!$request) {
        wp_send_json_error('Không tìm thấy yêu cầu');
      }
      
      // Approve request trong database
      $result = $db->approve($request_id);
      
      if ($result) {
        // Cập nhật role của user
        $package_to_role = array(
          'Master' => 'master', 
          // 'Pharmer' => 'pharmer',
          // 'VIP Master' => 'vip_master'
        );
        
        $new_role = $package_to_role[$request->requested_package] ?? null;
        
        if ($new_role) {
          $user = new WP_User($request->user_id);
          $user->set_role($new_role);
          
          // Gửi email thông báo cho user
          $this->send_approval_notification($request->user_id, $request->requested_package);
          
          wp_send_json_success('Yêu cầu đã được duyệt thành công và tài khoản đã được nâng cấp lên ' . $request->requested_package);
        } else {
          wp_send_json_error('Không thể xác định role mới từ package: ' . $request->requested_package);
        }
      } else {
        wp_send_json_error('Có lỗi xảy ra khi duyệt yêu cầu');
      }
    } elseif ($action === 'reject') {
      $result = $db->reject($request_id, $reason);
      if ($result) {
        // Gửi email thông báo từ chối cho user
        $request = $db->get_by_id($request_id);
        if ($request) {
          $this->send_rejection_notification($request->user_id, $reason);
        }
        
        wp_send_json_success('Yêu cầu đã bị từ chối');
      } else {
        wp_send_json_error('Có lỗi xảy ra khi từ chối yêu cầu');
      }
    } else {
      wp_send_json_error('Hành động không hợp lệ');
    }
  }
  
  private function send_approval_notification($user_id, $new_package) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $subject = 'Yêu cầu nâng cấp tài khoản đã được duyệt';
    $message = "Chào {$user->display_name},\n\n";
    $message .= "Yêu cầu nâng cấp tài khoản của bạn đã được duyệt thành công.\n";
    $message .= "Loại tài khoản mới: {$new_package}\n\n";
    $message .= "Bạn có thể đăng nhập và sử dụng các tính năng mới ngay bây giờ.\n\n";
    $message .= "Trân trọng,\nĐội ngũ quản trị";
    
    wp_mail($user->user_email, $subject, $message);
  }
  
  private function send_rejection_notification($user_id, $reason) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $subject = 'Yêu cầu nâng cấp tài khoản đã bị từ chối';
    $message = "Chào {$user->display_name},\n\n";
    $message .= "Rất tiếc, yêu cầu nâng cấp tài khoản của bạn đã bị từ chối.\n";
    if ($reason) {
      $message .= "Lý do: {$reason}\n";
    }
    $message .= "\nBạn có thể gửi lại yêu cầu nâng cấp sau khi khắc phục các vấn đề.\n\n";
    $message .= "Trân trọng,\nĐội ngũ quản trị";
    
    wp_mail($user->user_email, $subject, $message);
  }
}

// Initialize the class
new KTech_Upgrade_Requests_Admin();