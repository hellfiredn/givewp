<?php
/**
 * AJAX handlers for upgrade account form
 */

add_action('wp_ajax_submit_upgrade_account', 'submit_upgrade_account_callback');
add_action('wp_ajax_nopriv_submit_upgrade_account', 'submit_upgrade_account_callback');

function submit_upgrade_account_callback() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Bạn cần đăng nhập để gửi yêu cầu nâng cấp');
    }
    
    $user_id = get_current_user_id();
    
    // Get form data
    $account_type = sanitize_text_field($_POST['account_type'] ?? '');
    $company_name = sanitize_text_field($_POST['company_name'] ?? '');
    $representative_name = sanitize_text_field($_POST['representative_name'] ?? '');
    $business_address = sanitize_textarea_field($_POST['business_address'] ?? '');
    $business_code = sanitize_text_field($_POST['business_code'] ?? '');
    $contact_phone = sanitize_text_field($_POST['phone'] ?? '');
    
    // Validate required fields
    if (empty($company_name) || empty($representative_name) || empty($business_address) || empty($business_code) || empty($contact_phone)) {
        wp_send_json_error('Vui lòng điền đầy đủ thông tin bắt buộc');
    }
    
    // Handle file upload
    $business_license_file = '';
    if (isset($_FILES['business_license']) && $_FILES['business_license']['error'] === UPLOAD_ERR_OK) {
        $uploaded_file = handle_business_license_upload($_FILES['business_license']);
        if (is_wp_error($uploaded_file)) {
            wp_send_json_error('Lỗi upload file: ' . $uploaded_file->get_error_message());
        }
        $business_license_file = $uploaded_file;
    }
    
    // Get current user package/role
    $user = wp_get_current_user();
    $current_role = $user->roles[0] ?? 'customer';
    $role_to_package = array(
        'customer' => 'Basic',
        'pharmer' => 'VIP Member', 
        'master' => 'Master',
        'vip_master' => 'VIP Master'
    );
    $current_package = $role_to_package[$current_role] ?? 'Basic';
    
    // Check if user already has pending request
    $db = new KTech_Upgrade_Requests_DB();
    if ($db->has_pending_request($user_id)) {
        wp_send_json_error('Bạn đã có yêu cầu nâng cấp đang chờ duyệt');
    }
    
    // Prepare data for insertion
    $data = array(
        'user_id' => $user_id,
        'company_name' => $company_name,
        'representative_name' => $representative_name,
        'business_address' => $business_address,
        'business_code' => $business_code,
        'business_license_file' => $business_license_file,
        'contact_phone' => $contact_phone,
        'current_package' => $current_package,
        'requested_package' => $account_type,
        'status' => 'pending'
    );
    
    // Insert into database
    $result = $db->insert($data);
    
    if ($result) {
        // Send notification email to admin
        send_upgrade_notification_to_admin($user_id, $data);
        
        wp_send_json_success('Yêu cầu nâng cấp đã được gửi thành công. Chúng tôi sẽ xem xét và phản hồi trong thời gian sớm nhất.');
    } else {
        wp_send_json_error('Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại.');
    }
}

function handle_business_license_upload($file) {
    // Check file type
    $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return new WP_Error('invalid_file_type', 'Chỉ cho phép upload file PDF, DOC, DOCX, JPG, JPEG, PNG');
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return new WP_Error('file_too_large', 'File không được vượt quá 5MB');
    }
    
    // Set upload directory
    $upload_dir = wp_upload_dir();
    $upgrade_uploads_dir = $upload_dir['basedir'] . '/upgrade-requests';
    $upgrade_uploads_url = $upload_dir['baseurl'] . '/upgrade-requests';
    
    // Create directory if not exists
    if (!file_exists($upgrade_uploads_dir)) {
        wp_mkdir_p($upgrade_uploads_dir);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . sanitize_file_name($file['name']);
    $filepath = $upgrade_uploads_dir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $upgrade_uploads_url . '/' . $filename;
    } else {
        return new WP_Error('upload_failed', 'Không thể upload file');
    }
}

function send_upgrade_notification_to_admin($user_id, $data) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $admin_email = get_option('admin_email');
    
    $subject = 'Yêu cầu nâng cấp tài khoản mới từ ' . $user->display_name;
    $message = "Có yêu cầu nâng cấp tài khoản mới:\n\n";
    $message .= "Người dùng: {$user->display_name} ({$user->user_login})\n";
    $message .= "Email: {$user->user_email}\n";
    $message .= "Tên doanh nghiệp: {$data['company_name']}\n";
    $message .= "Người đại diện: {$data['representative_name']}\n";
    $message .= "Địa chỉ kinh doanh: {$data['business_address']}\n";
    $message .= "Mã số doanh nghiệp: {$data['business_code']}\n";
    $message .= "Số điện thoại: {$data['contact_phone']}\n";
    $message .= "Gói hiện tại: {$data['current_package']}\n";
    $message .= "Gói yêu cầu: {$data['requested_package']}\n\n";
    $message .= "Vui lòng vào admin để duyệt: " . admin_url('admin.php?page=upgrade-requests');
    
    wp_mail($admin_email, $subject, $message);
}

// Get user's upgrade request status
add_action('wp_ajax_get_upgrade_request_status', 'get_upgrade_request_status_callback');
add_action('wp_ajax_nopriv_get_upgrade_request_status', 'get_upgrade_request_status_callback');

function get_upgrade_request_status_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    
    $user_id = get_current_user_id();
    $db = new KTech_Upgrade_Requests_DB();
    $latest_request = $db->get_latest_by_user($user_id);
    
    if ($latest_request) {
        wp_send_json_success(array(
            'status' => $latest_request->status,
            'requested_package' => $latest_request->requested_package,
            'created_at' => $latest_request->created_at,
            'rejection_reason' => $latest_request->rejection_reason ?? null,
            'data' => $latest_request
        ));
    } else {
        wp_send_json_success(array('status' => null));
    }
}

// Xử lý AJAX xoá role
add_action('wp_ajax_delete_role', 'delete_role_callback');
add_action('wp_ajax_nopriv_delete_role', 'delete_role_callback');

function delete_role_callback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Bạn không có quyền thực hiện thao tác này.');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_role_nonce')) {
        wp_send_json_error('Mã bảo mật không hợp lệ.');
    }
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    if (!$role) {
        wp_send_json_error('Thiếu thông tin role.');
    }
    global $wp_roles;
    if (!isset($wp_roles->roles[$role])) {
        wp_send_json_error('Role không tồn tại.');
    }
    // Không cho xoá các role mặc định của WP
    $default_roles = [
        'administrator',
        'editor',
        'author',
        'contributor',
        'subscriber',
        'pharmer',
        'master'
    ];
    if (in_array($role, $default_roles)) {
        wp_send_json_error('Không cho phép xóa role này.');
    }
    // Xoá role
    remove_role($role);
    // Xoá khỏi option ktech_account_roles_setting nếu có
    $roles_setting = get_option('ktech_account_roles_setting', []);
    if (isset($roles_setting[$role])) {
        unset($roles_setting[$role]);
        update_option('ktech_account_roles_setting', $roles_setting);
    }
    wp_send_json_success('Đã xoá role thành công!');
}