<?php

function register_form_src() {
    // Lấy ref code từ URL
    $ref_code = isset($_GET['ref_code']) ? sanitize_text_field($_GET['ref_code']) : '';
    // Xác định step
    $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '1';
    $errors = new WP_Error();
    $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
    $email    = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $user_role = isset($_POST['user_role']) ? $_POST['user_role'] : '';
    $ref_code_submitted = isset($_POST['ref_code']) ? sanitize_text_field($_POST['ref_code']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $fullname = isset($_POST['fullname']) ? sanitize_text_field($_POST['fullname']) : '';
    $cccd = isset($_POST['cccd']) ? sanitize_text_field($_POST['cccd']) : '';
    
    // Biến để lưu thông tin user sau khi đăng ký
    $user_id = null;
    $user = null;
    $registration_success = false;

    // Validate
    if ($step == 'register') {
        // Kiểm tra số điện thoại đã tồn tại trong custom_phone chưa
        $phone_query = new WP_User_Query(array(
            'meta_key' => 'custom_phone',
            'meta_value' => $phone,
            'number' => 1,
            'fields' => 'ID',
        ));

        if (!empty($phone) && !empty($phone_query->get_results())) {
            $errors->add('register_phone_exists', 'Số điện thoại đã tồn tại.');
        }

        // Validate phone (10 số, bắt đầu bằng 0)
        if ( !preg_match('/^0\d{9}$/', $phone) ) {
            $errors->add('register_phone_invalid', 'Số điện thoại không hợp lệ. Vui lòng nhập đúng 10 số và bắt đầu bằng số 0.');
        }

        // Validate ref code exists
        if (empty($ref_code_submitted)) {
            $errors->add('register_ref_required', 'Mã giới thiệu là bắt buộc.');
        } else {
            // Kiểm tra ref code có tồn tại không
            $ref_user_query = new WP_User_Query(array(
                'meta_key'   => 'my_ref_code',
                'meta_value' => $ref_code_submitted,
                'number'     => 1,
                'fields'     => 'all',
                'role__in'   => array('master', 'pharmer_seller'),
            ));
            $ref_user = !empty($ref_user_query->get_results()) ? $ref_user_query->get_results()[0] : false;
            if (!$ref_user) {
                $errors->add('register_ref_invalid', 'Mã giới thiệu không tồn tại.');
            }
        }

        if ( empty($cccd) || !preg_match('/^\d{12}$/', $cccd) ) {
            $errors->add('register_cccd_invalid', 'Số CCCD phải gồm đúng 12 số.');
        }

        if ( username_exists( $username ) ) {
            $errors->add( 'register_username_exists', 'Tên đăng nhập đã tồn tại.' );
        }

        if (strlen($username) < 3) {
            $errors->add('register_username_length', 'Tên đăng nhập ít nhất 3 ký tự.');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $errors->add('register_username_invalid', 'Tên đăng nhập chỉ được chứa chữ cái tiếng Anh và số.');
        }

        if ( email_exists( $email ) ) {
            $errors->add( 'exists', 'Email đã tồn tại.' );
        }

        
        // validate password
        if (strlen($password) < 8 || 
            !preg_match('/[A-Za-z]/', $password) || 
            !preg_match('/\d/', $password) || 
            !preg_match('/[^A-Za-z\d]/', $password)) {
            $errors->add('register_password_invalid', 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái tiếng Anh, số và ký tự đặc biệt.');
        }

        if ($confirm_password != $password) {
            $errors->add('register_password_mismatch', 'Mật khẩu xác nhận không khớp. Vui lòng nhập lại mật khẩu.');
        }
    }

    if ( $step == 'register' && empty( $errors->get_error_codes() ) ) {
        $user_id = wp_create_user( $username, $password, $email );
        if ( ! is_wp_error( $user_id ) ) {
            $user = new WP_User( $user_id );
            $user->set_role( $user_role );
            
            // Lưu thông tin bổ sung
            update_user_meta($user_id, 'billing_phone', $phone);
            update_user_meta($user_id, 'custom_email', $email);
            update_user_meta($user_id, 'custom_phone', $phone);
            update_user_meta($user_id, 'first_name', $fullname);
            update_user_meta($user_id, 'custom_full_name', $fullname);
            update_user_meta($user_id, 'last_name', '');
            update_user_meta($user_id, 'display_name', $fullname);
            update_user_meta($user_id, 'cccd_number', $cccd);
            update_user_meta($user_id, 'referrer_code', $ref_code_submitted);
            update_user_meta($user_id, 'show_admin_bar_front', 'false');
            // Nếu là master thì lưu master_agreement
            if ($user_role === 'master' && isset($_POST['master_agreement'])) {
                update_user_meta($user_id, 'master_agreement', sanitize_text_field($_POST['master_agreement']));
            }
            update_user_meta($user_id, 'kam_approved', 'no');
            // Cập nhật display name
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $fullname
            ));

            // Tự động đăng nhập sau khi đăng ký thành công bằng wp_signon
            error_log('Đăng ký xong, chuẩn bị đăng nhập:');
            error_log('user_login: ' . $user->user_login);
            error_log('password: ' . $password);
            $creds = array(
                'user_login'    => $user->user_login,
                'user_password' => $password,
                'remember'      => true
            );
            $user_signon = wp_signon($creds, false);
            if (is_wp_error($user_signon)) {
                error_log('Đăng nhập tự động thất bại: ' . $user_signon->get_error_message());
            } else {
                error_log('Đăng nhập tự động thành công!');
            }
            
            // Đánh dấu đăng ký thành công
            $registration_success = true;
            // Không redirect, chỉ đăng nhập rồi show HTML
        }
    }

    ob_start();

    echo '<div class="register-wrapper" data-step="'.$step.'">';
    // if ( is_user_logged_in() ) {
    //     return '<p>Bạn đã đăng nhập.</p>';
    // }

    // Step 1: Checkbox xác nhận
    if ( $step == '1' ) {
        $url_terms = get_option('ktech_url_terms', '/dieu-khoan-su-dung-chinh-sach-bao-mat/');
        $url_personal = get_option('ktech_url_personal', '#');
        $url_thirdparty = get_option('ktech_url_thirdparty', '#');
        ?>
        <h2>Đồng ý với các điều khoản và điều kiện</h2>
        <div class="register-process"></div>
        <p><strong>Bạn phải đồng ý với các mục yêu cầu để đăng ký</strong></p>
        <p>Tôi đã đủ 14 tuổi trở lên và đồng ý với tất cả các điều khoản và chính sách.</p>
        <p>Điều khoản sử dụng & chính sách bảo mật <a href="<?= $url_terms ?>" target="_blank">Xem chi tiết</a></p>
        <p>Đồng ý với chính sách xử lý thông tin cá nhân và thông báo đẩy <a href="<?= $url_personal ?>">Xem chi tiết</a></p>
        <p>Đồng ý cung cấp thông tin cá nhân cho bên thứ ba <a href="<?= $url_thirdparty ?>">Xem chi tiết</a></p>
        <form method="post" class="register-step-1">
            <label>
                <input type="checkbox" name="agree" required> Tôi đồng ý với tất cả điều khoản trên
            </label>
            <br><br>
            <input type="hidden" name="ref_code" value="<?php echo $ref_code; ?>" />
            <button type="submit" class="button primary ml-half" name="step" value="2">Đăng ký</button>
        </form>
        <?php
    }

    // Step 2: Form đăng ký WooCommerce
    if ( $step == '2' || ($step == 'register' && !empty( $errors->get_error_codes())) ) {
        if ( isset($_POST['agree']) ) {
            global $wp_roles;
            $default_roles = [];
            foreach ($wp_roles->roles as $key => $role) {
                $default_roles[$key] = $role['name'];
            }
            $roles_setting = get_option('ktech_account_roles_setting', []);
            $registerable_roles = [];
            foreach ($roles_setting as $key => $role) {
                if (isset($role['allow_register']) && $role['allow_register'] === 'yes') {
                    $registerable_roles[$key] = (object)[
                        'key' => $key,
                        'name' => $role['name']
                    ];
                }
            }
            
            ?>
            <h2>Tham gia Thành viên</h2>
            <div class="register-process"></div>
            <p><strong>Bạn phải nhập thông tin chính xác và đăng ký để nhận dịch vụ tốt nhất</strong></p>
            <form id="custom-register-form" class="form-register register-step-2" method="post" action="">
                <div class="form-group">
                    <select name="user_role" id="user_role_select" required>
                        <option value="">Loại tài khoản</option>
                        <?php foreach ($registerable_roles as $role) { ?>
                            <option value="<?php echo $role->key; ?>" <?php selected($user_role ?? ''); ?>><?php echo $role->name; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="ref_code" placeholder="Nhập mã giới thiệu" value="<?php echo esc_attr($ref_code_submitted); ?>" required>
                    <small class="form-text text-muted">Bạn không thể đăng ký nếu không có mã của người giới thiệu</small>
                </div>

                <div class="form-group">
                    <input type="text" name="username" value="<?php echo $username; ?>" placeholder="Tên đăng nhật (ID) hoặc số điện thoại" required>
                    <small class="form-text text-muted">Chỉ có thể nhập các chữ cái tiếng Anh, số. Ít nhất 3 ký tự</small>
                </div>

                <div class="form-group">
                    <input type="password" name="password" value="<?php echo $password; ?>" placeholder="Mật khẩu" required>
                    <small class="form-text text-muted">Nhập ít nhất 8 ký tự, kết hợp giữa chữ cái tiếng Anh, số và ký tự đặc biệt.</small>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" value="<?php echo $password; ?>" placeholder="Xác nhận mật khẩu" required>
                    <small class="form-text text-muted">Nhập lại mật khẩu.</small>
                </div>

                <div class="form-group">
                    <input type="email" name="email" value="<?php echo $email; ?>" placeholder="Địa chỉ email" required>
                    <small class="form-text text-muted">Nhập địa chỉ email của chính bạn</small>
                </div>

                <div class="form-group">
                    <input type="text" name="fullname" value="<?php echo $fullname; ?>" placeholder="Họ và tên" required>
                    <small class="form-text text-muted">Nhập chính xác Họ và Tên của bạn</small>
                </div>

                <div class="form-group">
                    <input type="tel" name="phone" value="<?php echo $phone; ?>" placeholder="Số điện thoại" required>
                    <small class="form-text text-muted">Nhập chính xác số điện thoại của bạn</small>
                </div>

                <div class="form-group">
                    <input type="text" name="cccd" value="<?php echo $cccd; ?>" placeholder="Số CCCD" required>
                    <small class="form-text text-muted">Nhập chính xác số CCCD của bạn</small>
                </div>

                <div id="master_agreement_wrapper" style="display:none;margin: 15px 0;">
                    <label><input type="checkbox" name="master_agreement" value="1" required> Xác nhận ký thỏa thuận hợp tác và thỏa thuận sử dụng thông tin <a href="#" target="_blank">Xem ngay</a></label>
                </div>

                <input type="hidden" name="step" value="register">
                <input type="hidden" name="agree" value="true">

                <div class="form-group">
                    <button type="submit" class="submit-btn">Đăng ký</button>
                </div>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var select = document.getElementById('user_role_select');
                var agreement = document.getElementById('master_agreement_wrapper');
                var checkbox = agreement.querySelector('input[type="checkbox"]');
                select.addEventListener('change', function() {
                    if (this.value === 'master') {
                        agreement.style.display = 'block';
                        checkbox.required = true;
                    } else {
                        agreement.style.display = 'none';
                        checkbox.checked = false;
                        checkbox.required = false;
                    }
                });
            });
            </script>
            <?php
        } else {
            echo '<p>Bạn phải đồng ý với điều khoản trước khi tiếp tục.</p>';
        }
    }

    // Step Register (xử lý) - Hiển thị kết quả
    if ( $registration_success && $user ) {
        echo '<div class="register-success">';
        echo '<h2>Đăng kí thành công</h2>';
        echo '<div class="register-process"></div>';
        echo '<img src="/wp-content/uploads/2025/08/check-mark-icon.png" />';
        echo '<h3>Chúc mừng bạn</h3>';
        echo '<h3>đã đăng ký tài khoản thành công</h3>';
        echo '</div>';
        echo '<div class="box-info-user-registed">';
        echo '<div class="box-info-user-registed-row">';
        echo '<span>Tên</span><span><strong>'.$user->display_name.'</strong></span>';
        echo '</div>';
        echo '<div class="box-info-user-registed-row">';
        echo '<span>ID</span><span><strong>'.$username.'</strong></span>';
        echo '</div>';
        echo '<div class="box-info-user-registed-row">';
        echo '<span>Ngày đăng ký</span><span><strong>'.date_i18n( 'd.m.Y', strtotime( $user->user_registered ) ).'</strong></span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<a class="button registed-to-buy-now" href="/">Mua hàng ngay</a>';
    }
    if (!empty($errors->get_error_messages())) {
        foreach ( $errors->get_error_messages() as $error ) {
            echo '<p style="color:red;">' . esc_html( $error ) . '</p>';
        }
    }
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('register_form', 'register_form_src');

