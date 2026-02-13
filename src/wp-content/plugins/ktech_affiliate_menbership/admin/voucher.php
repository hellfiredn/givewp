<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KTech_Voucher_Admin {
  
  public function __construct() {
    add_action('admin_menu', array($this, 'add_admin_menu'));
    add_action('wp_ajax_add_voucher', array($this, 'handle_add_voucher'));
    add_action('wp_ajax_edit_voucher', array($this, 'handle_edit_voucher'));
    add_action('wp_ajax_delete_voucher', array($this, 'handle_delete_voucher'));
    add_action('wp_ajax_update_voucher_status', array($this, 'handle_update_status'));
    
    // Hook vào khi order được chuyển sang complete
    add_action('woocommerce_order_status_completed', array($this, 'auto_assign_voucher_to_user'));

  }
  
  // --- Tặng voucher tự động cho user nếu đủ điều kiện ---
  public function auto_assign_voucher_to_user($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order ? $order->get_user_id() : 0;
    if ($user_id) {
        // Lấy danh sách voucher active
        $voucher_db = new KTech_Voucher_DB();
        $vouchers = $voucher_db->get_active();
        $order_total = floatval($order->get_total());
        foreach ($vouchers as $voucher) {
            // Kiểm tra điều kiện: đơn tối thiểu, chưa quá hạn, chưa quá số lượng
            $is_valid = true;
            if ($voucher->minimum_order > 0 && $order_total < $voucher->minimum_order) $is_valid = false;
            if ($voucher->expiry_date && strtotime($voucher->expiry_date) < time()) $is_valid = false;
            if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) $is_valid = false;
            if ($is_valid) {
              // Thêm id voucher vào meta user nếu chưa có
              $user_vouchers = get_user_meta($user_id, 'kam_user_vouchers', true);
              if (!is_array($user_vouchers)) $user_vouchers = array();

              $has_voucher = false;
              foreach ($user_vouchers as $uv) {
                if (isset($uv['voucher_id']) && $uv['voucher_id'] == $voucher->id) {
                  $has_voucher = true;
                  break;
                }
              }

              if (!$has_voucher) {
                $user_vouchers[] = array(
                  'voucher_id' => $voucher->id,
                  'received_at' => current_time('mysql'),
                  'order_id' => $order_id,
                  'status' => 'received'
                );
                update_user_meta($user_id, 'kam_user_vouchers', $user_vouchers);
              }
            }
        }
    }
  }
  


  public function add_admin_menu() {
    add_submenu_page(
      'ktech-affiliate-membership',
      'Quản lý Voucher',
      'Quản lý Voucher',
      'manage_options',
      'voucher-management',
      array($this, 'voucher_management_page')
    );
  }
  
  public function voucher_management_page() {
    // Check if voucher DB class exists
    if (!class_exists('KTech_Voucher_DB')) {
      echo '<div class="wrap"><h1>Lỗi: Class KTech_Voucher_DB không tồn tại. Vui lòng kiểm tra file voucher-db.php</h1></div>';
      return;
    }
    
    $voucher_db = new KTech_Voucher_DB();
    
    // Get current tab from URL parameter
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'all';
    
    echo '<div class="wrap">';
    echo '<h1>Quản lý Voucher <button id="add-voucher-btn" class="button button-primary" style="margin-left: 20px;">Thêm Voucher</button></h1>';
    
    // Display tabs
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=voucher-management&tab=all" class="nav-tab ' . ($current_tab === 'all' ? 'nav-tab-active' : '') . '">Tất cả</a>';
    echo '<a href="?page=voucher-management&tab=active" class="nav-tab ' . ($current_tab === 'active' ? 'nav-tab-active' : '') . '">Đang hoạt động</a>';
    echo '<a href="?page=voucher-management&tab=inactive" class="nav-tab ' . ($current_tab === 'inactive' ? 'nav-tab-active' : '') . '">Tạm dừng</a>';
    echo '<a href="?page=voucher-management&tab=expired" class="nav-tab ' . ($current_tab === 'expired' ? 'nav-tab-active' : '') . '">Hết hạn</a>';
    echo '</h2>';
    
    // Get vouchers based on selected tab
    $args = array('limit' => 100);
    if ($current_tab !== 'all') {
      $args['status'] = $current_tab;
    }
    $vouchers = $voucher_db->get_all($args);
    
    // Display vouchers table
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th style="width: 50px;">ID</th>';
    echo '<th style="width: 100px;">Mã Voucher</th>';
    echo '<th>Tiêu đề</th>';
    echo '<th>Đơn tối thiểu</th>';
    echo '<th>Hạn sử dụng</th>';
    echo '<th>Sử dụng</th>';
    echo '<th>Trạng thái</th>';
    echo '<th>Hành động</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if ($vouchers) {
      foreach ($vouchers as $voucher) {
        $value_display = $voucher->value_type === 'percent' 
          ? $voucher->value . '%' 
          : number_format($voucher->value) . ' VNĐ';
          
        $status_class = '';
        $status_label = '';
        switch ($voucher->status) {
          case 'active':
            $status_class = 'status-active';
            $status_label = 'Hoạt động';
            break;
          case 'inactive':
            $status_class = 'status-inactive';
            $status_label = 'Tạm dừng';
            break;
          case 'expired':
            $status_class = 'status-expired';
            $status_label = 'Hết hạn';
            break;
        }
        
        $usage_display = $voucher->usage_limit 
          ? $voucher->used_count . '/' . $voucher->usage_limit
          : $voucher->used_count . '/∞';
        
        echo '<tr>';
        echo '<td>' . $voucher->id . '</td>';
        echo '<td><code>' . esc_html($voucher->voucher_code) . '</code></td>';
        echo '<td>' . esc_html($voucher->title) . '</td>';
        echo '<td>' . number_format($voucher->minimum_order) . ' VNĐ</td>';
        echo '<td>' . ($voucher->expiry_date ? date('d/m/Y', strtotime($voucher->expiry_date)) : 'Không giới hạn') . '</td>';
        echo '<td>' . $usage_display . '</td>';
        echo '<td><span class="' . $status_class . '">' . $status_label . '</span></td>';
        echo '<td>';
        
        // Status toggle buttons
        if ($voucher->status === 'active') {
          echo '<button class="button button-small status-btn" data-id="' . $voucher->id . '" data-status="inactive">Tạm dừng</button> ';
        } else if ($voucher->status === 'inactive') {
          echo '<button class="button button-small button-primary status-btn" data-id="' . $voucher->id . '" data-status="active">Kích hoạt</button> ';
        }
        
        // Prepare voucher data for edit button
        $voucher_data = array(
          'id' => $voucher->id,
          'voucher_code' => $voucher->voucher_code,
          'title' => $voucher->title,
          'description' => $voucher->description,
          'minimum_order' => $voucher->minimum_order,
          'usage_limit' => $voucher->usage_limit,
          'image_url' => $voucher->image_url,
          'expiry_date' => $voucher->expiry_date
        );
        
        echo '<button class="button button-small edit-btn" 
                data-id="' . $voucher->id . '" 
                data-voucher="' . esc_attr(json_encode($voucher_data)) . '">Sửa</button> ';
        echo '<button class="button button-small button-link-delete delete-btn" data-id="' . $voucher->id . '">Xóa</button>';
        echo '</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="8">Không có voucher nào.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    // Add Voucher Popup
    ?>
    <div id="voucher-popup" style="display: none;">
      <div class="voucher-popup-overlay">
        <div class="voucher-popup-content">
          <div class="voucher-popup-header">
            <h2>Thêm Voucher Mới</h2>
            <button class="voucher-popup-close">&times;</button>
          </div>
          <form id="add-voucher-form">
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Mã Voucher *</label>
                <input type="text" name="voucher_code" required maxlength="50">
              </div>
              <div class="voucher-form-group">
                <label>Tiêu đề *</label>
                <input type="text" name="title" required maxlength="255">
              </div>
            </div>
            
            <div class="voucher-form-group">
              <label>Mô tả</label>
              <textarea name="description" rows="3"></textarea>
            </div>
            
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Đơn hàng tối thiểu</label>
                <input type="number" name="minimum_order">
              </div>
              <div class="voucher-form-group">
                <label>Giới hạn sử dụng</label>
                <input type="number" name="usage_limit" min="1" placeholder="Để trống = không giới hạn">
              </div>
            </div>
            
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Ngày hết hạn</label>
                <input type="datetime-local" name="expiry_date">
              </div>
              <div class="voucher-form-group">
                <label>Hình ảnh</label>
                <input type="url" name="image_url" placeholder="URL hình ảnh voucher" maxlength="500">
              </div>
            </div>
            
            <div class="voucher-form-actions">
              <button type="submit" class="button button-primary">Thêm Voucher</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Edit Voucher Popup -->
    <div id="edit-voucher-popup" style="display: none;">
      <div class="voucher-popup-overlay">
        <div class="voucher-popup-content">
          <div class="voucher-popup-header">
            <h2>Chỉnh sửa Voucher</h2>
            <button class="edit-voucher-popup-close">&times;</button>
          </div>
          <form id="edit-voucher-form">
            <input type="hidden" name="voucher_id" value="">
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Mã Voucher *</label>
                <input type="text" name="voucher_code" required maxlength="50">
              </div>
              <div class="voucher-form-group">
                <label>Tiêu đề *</label>
                <input type="text" name="title" required maxlength="255">
              </div>
            </div>
            
            <div class="voucher-form-group">
              <label>Mô tả</label>
              <textarea name="description" rows="3"></textarea>
            </div>
            
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Đơn hàng tối thiểu</label>
                <input type="number" name="minimum_order">
              </div>
              <div class="voucher-form-group">
                <label>Giới hạn sử dụng</label>
                <input type="number" name="usage_limit" min="1" placeholder="Để trống = không giới hạn">
              </div>
            </div>
            
            <div class="voucher-form-row">
              <div class="voucher-form-group">
                <label>Ngày hết hạn</label>
                <input type="datetime-local" name="expiry_date">
              </div>
              <div class="voucher-form-group">
                <label>Hình ảnh</label>
                <input type="url" name="image_url" placeholder="URL hình ảnh voucher" maxlength="500">
              </div>
            </div>
            
            <div class="voucher-form-actions">
              <button type="submit" class="button button-primary">Cập nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php
  }
  
  public function handle_add_voucher() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'voucher_nonce')) {
      wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Permission denied');
    }
    
    // Check if class exists
    if (!class_exists('KTech_Voucher_DB')) {
      wp_send_json_error('Voucher DB class not found');
    }
    
    try {

      $voucher_db = new KTech_Voucher_DB();
    
      $data = array(
        'voucher_code' => sanitize_text_field($_POST['voucher_code']),
        'title' => sanitize_text_field($_POST['title']),
        'description' => sanitize_textarea_field($_POST['description']),
        'minimum_order' => floatval($_POST['minimum_order']),
        'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
        'status' => 'active'
      );
      // Nếu có image_id thì thêm vào
      if (!empty($_POST['image_id'])) {
        $data['image_id'] = intval($_POST['image_id']);
      }
      
      if (!empty($_POST['expiry_date'])) {
        $data['expiry_date'] = date('Y-m-d H:i:s', strtotime($_POST['expiry_date']));
      }
    
      $result = $voucher_db->insert($data);
      
      if ($result) {
        wp_send_json_success('Voucher đã được thêm thành công');
      } else {
        wp_send_json_error('Không thể thêm voucher');
      }
    } catch (Exception $e) {
      wp_send_json_error('Lỗi: ' . $e->getMessage());
    }
  }
  

  public function handle_edit_voucher() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'voucher_nonce')) {
      wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Permission denied');
    }
    
    if (!class_exists('KTech_Voucher_DB')) {
      wp_send_json_error('Voucher DB class not found');
    }
    
    try {
      $voucher_db = new KTech_Voucher_DB();
      $voucher_id = intval($_POST['voucher_id']);
    
      $data = array(
        'voucher_code' => sanitize_text_field($_POST['voucher_code']),
        'title' => sanitize_text_field($_POST['title']),
        'description' => sanitize_textarea_field($_POST['description']),
        'minimum_order' => floatval($_POST['minimum_order']),
        'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
        'image_url' => esc_url_raw($_POST['image_url'])
      );
          
      if (!empty($_POST['expiry_date'])) {
        $data['expiry_date'] = date('Y-m-d H:i:s', strtotime($_POST['expiry_date']));
      } else {
        $data['expiry_date'] = null;
      }
    
      $result = $voucher_db->update($voucher_id, $data);
      
      if ($result !== false) {
        wp_send_json_success('Voucher đã được cập nhật thành công');
      } else {
        wp_send_json_error('Không thể cập nhật voucher');
      }
    } catch (Exception $e) {
      wp_send_json_error('Lỗi: ' . $e->getMessage());
    }
  }
  
  public function handle_update_status() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'voucher_nonce')) {
      wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Permission denied');
    }
    
    if (!class_exists('KTech_Voucher_DB')) {
      wp_send_json_error('Voucher DB class not found');
    }
    
    try {
      $voucher_db = new KTech_Voucher_DB();
      $voucher_id = intval($_POST['voucher_id']);
      $status = sanitize_text_field($_POST['status']);
      
      $result = $voucher_db->update($voucher_id, array('status' => $status));
      
      if ($result !== false) {
        wp_send_json_success('Đã cập nhật trạng thái');
      } else {
        wp_send_json_error('Không thể cập nhật trạng thái');
      }
    } catch (Exception $e) {
      wp_send_json_error('Lỗi: ' . $e->getMessage());
    }
  }
  
  public function handle_delete_voucher() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'voucher_nonce')) {
      wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Permission denied');
    }
    
    if (!class_exists('KTech_Voucher_DB')) {
      wp_send_json_error('Voucher DB class not found');
    }
    
    try {
      $voucher_db = new KTech_Voucher_DB();
      $voucher_id = intval($_POST['voucher_id']);
      
      $result = $voucher_db->delete($voucher_id);
      
      if ($result !== false) {
        wp_send_json_success('Đã xóa voucher');
      } else {
        wp_send_json_error('Không thể xóa voucher');
      }
    } catch (Exception $e) {
      wp_send_json_error('Lỗi: ' . $e->getMessage());
    }
  }
}

// Initialize the class
new KTech_Voucher_Admin();