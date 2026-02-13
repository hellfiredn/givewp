<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form');

$user_id = get_current_user_id();
$user    = get_userdata($user_id);
?>
<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?> enctype="multipart/form-data">

    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="user_login"><?php esc_html_e('ID (Cố định)', 'woocommerce'); ?></label>
        <input type="text" name="user_login" disabled id="user_login" value="<?php echo esc_attr($user->user_login); ?>" readonly />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="user_fullname"><?php esc_html_e('Họ và tên', 'woocommerce'); ?></label>
        <input type="text" name="user_fullname" id="user_fullname" value="<?php echo esc_attr(get_user_meta($user_id, 'user_fullname', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="user_email"><?php esc_html_e('Email', 'woocommerce'); ?></label>
        <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($user->user_email); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="user_phone"><?php esc_html_e('Số điện thoại', 'woocommerce'); ?></label>
        <input type="text" name="user_phone" id="user_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'user_phone', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row-first">
        <label for="user_gender"><?php esc_html_e('Giới tính', 'woocommerce'); ?></label>
        <select name="user_gender" id="user_gender">
            <option value="">-- Chọn giới tính --</option>
            <option value="Nam" <?php selected(get_user_meta($user_id, 'user_gender', true), 'Nam'); ?>>Nam</option>
            <option value="Nữ" <?php selected(get_user_meta($user_id, 'user_gender', true), 'Nữ'); ?>>Nữ</option>
            <option value="Khác" <?php selected(get_user_meta($user_id, 'user_gender', true), 'Khác'); ?>>Khác</option>
        </select>
    </p>

    <p class="woocommerce-form-row form-row form-row-last">
        <label for="user_birthday"><?php esc_html_e('Sinh nhật', 'woocommerce'); ?></label>
        <input type="date" name="user_birthday" id="user_birthday" value="<?php echo esc_attr(get_user_meta($user_id, 'user_birthday', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="spa_name"><?php esc_html_e('Tên Spa/Clinic', 'woocommerce'); ?></label>
        <input type="text" name="spa_name" id="spa_name" value="<?php echo esc_attr(get_user_meta($user_id, 'spa_name', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="spa_address"><?php esc_html_e('Địa chỉ Spa/Clinic', 'woocommerce'); ?></label>
        <input type="text" name="spa_address" id="spa_address" value="<?php echo esc_attr(get_user_meta($user_id, 'spa_address', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="passport_cccd"><?php esc_html_e('Số CCCD/Passport', 'woocommerce'); ?></label>
        <input type="text" name="passport_cccd" id="passport_cccd" value="<?php echo esc_attr(get_user_meta($user_id, 'passport_cccd', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="date_issue"><?php esc_html_e('Ngày cấp', 'woocommerce'); ?></label>
        <input type="date" name="date_issue" id="date_issue" value="<?php echo esc_attr(get_user_meta($user_id, 'date_issue', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="card_local"><?php esc_html_e('Nơi cấp', 'woocommerce'); ?></label>
        <input type="text" name="card_local" id="card_local" value="<?php echo esc_attr(get_user_meta($user_id, 'card_local', true)); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="cccd_front"><?php esc_html_e('Mặt trước CCCD', 'woocommerce'); ?></label>
        <input type="file" name="cccd_front" id="cccd_front" value="<?php echo esc_attr(get_user_meta($user_id, 'cccd_front', true)); ?>" style="width: 100%;" accept="image/*,application/pdf" />
        <?php
        $cccd_front_id = (int) get_user_meta($user_id, 'cccd_front_id', true);
        if ($cccd_front_id) {
            echo '<span><a href="' . esc_url(wp_get_attachment_url($cccd_front_id)) . '" target="_blank">Xem CCCD mặt trước</a></span>';
        }
        ?>
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="cccd_back"><?php esc_html_e('Mặt sau CCCD', 'woocommerce'); ?></label>
        <input type="file" name="cccd_back" id="cccd_back" value="<?php echo esc_attr(get_user_meta($user_id, 'cccd_back', true)); ?>" style="width: 100%;" accept="image/*,application/pdf" />
        <?php
        $cccd_back_id = (int) get_user_meta($user_id, 'cccd_back_id', true);
        if ($cccd_back_id) {
            echo '<span><a href="' . esc_url(wp_get_attachment_url($cccd_back_id)) . '" target="_blank">Xem CCCD mặt sau</a></span>';
        }
        ?>
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="gpkd"><?php esc_html_e('Giấy phép kinh doanh', 'woocommerce'); ?></label>
        <input type="file" name="gpkd" id="gpkd" value="<?php echo esc_attr(get_user_meta($user_id, 'gpkd', true)); ?>" style="width: 100%;" accept="image/*,application/pdf" />
        <?php
        $gpkd_id = (int) get_user_meta($user_id, 'gpkd_id', true);
        if ($gpkd_id) {
            echo '<span><a href="' . esc_url(wp_get_attachment_url($gpkd_id)) . '" target="_blank">Xem GPKD</a></span>';
        }
        ?>
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-first">
        <label for="user_ref_link"><?php esc_html_e('Link giới thiệu', 'woocommerce'); ?></label>
        <input type="text" name="user_ref_link" id="user_ref_link" value="<?php echo esc_attr(home_url() . '/dang-ky/?ref_code=' . get_user_meta($user_id, 'my_ref_code', true)); ?>" readonly onclick="this.select(); document.execCommand('copy');" />
        <script>
            document.getElementById('user_ref_link').addEventListener('click', function() {
                this.select();
                document.execCommand('copy');
                alert('Đã copy');
            });
        </script>
    </p>

    <p class="woocommerce-form-row form-row form-row form-row-last">
        <label for="user_product_link"><?php esc_html_e('Link sản phẩm', 'woocommerce'); ?></label>
        <input type="text" name="user_product_link" id="user_product_link" value="<?php echo esc_attr(home_url() . '/?ref_code=' . get_user_meta($user_id, 'my_ref_code', true)); ?>" readonly onclick="this.select(); document.execCommand('copy');" />
        <script>
            document.getElementById('user_product_link').addEventListener('click', function() {
                this.select();
                document.execCommand('copy');
                alert('Đã copy');
            });
        </script>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var phoneInput = document.getElementById('user_phone');
            var submitBtn = document.querySelector('button[name="save_account_details"]');

            function validatePhoneVN(phone) {
                // Số điện thoại Việt Nam: 10 số, bắt đầu bằng 0
                return /^0[0-9]{9}$/.test(phone.trim());
            }

            phoneInput.addEventListener('input', function() {
                var errorMsg = document.getElementById('phone-error-msg');
                if (!validatePhoneVN(phoneInput.value)) {
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.id = 'phone-error-msg';
                        errorMsg.style.color = 'red';
                        errorMsg.style.display = 'block';
                        errorMsg.style.marginTop = '5px';
                        errorMsg.textContent = 'Số điện thoại không hợp lệ!';
                        phoneInput.parentNode.appendChild(errorMsg);
                    }
                    submitBtn.disabled = true;
                } else {
                    if (errorMsg) errorMsg.remove();
                    submitBtn.disabled = false;
                }
            });
        });
    </script>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <p>
        <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
        <button type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e('Cập nhật', 'woocommerce'); ?>"><?php esc_html_e('Cập nhật', 'woocommerce'); ?></button>
        <input type="hidden" name="action" value="save_account_details" />
    </p>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<?php do_action('woocommerce_after_edit_account_form'); ?>