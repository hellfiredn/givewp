<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );

$user_id = get_current_user_id();
$user    = get_userdata( $user_id );
?>
<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

    <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

    <p class="woocommerce-form-row form-row form-row-wide">
        <label for="user_login"><?php esc_html_e( 'ID (Cố định)', 'woocommerce' ); ?></label>
        <input type="text" name="user_login" disabled id="user_login" value="<?php echo esc_attr( $user->user_login ); ?>" readonly />
    </p>

    <p class="woocommerce-form-row form-row form-row-wide">
        <label for="user_fullname"><?php esc_html_e( 'Họ và tên', 'woocommerce' ); ?></label>
        <input type="text" name="user_fullname" id="user_fullname" value="<?php echo esc_attr( get_user_meta( $user_id, 'user_fullname', true ) ); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row-wide">
        <label for="user_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?></label>
        <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr( $user->user_email ); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row-first">
        <label for="user_gender"><?php esc_html_e( 'Giới tính', 'woocommerce' ); ?></label>
        <select name="user_gender" id="user_gender">
            <option value="">-- Chọn giới tính --</option>
            <option value="Nam" <?php selected( get_user_meta( $user_id, 'user_gender', true ), 'Nam' ); ?>>Nam</option>
            <option value="Nữ" <?php selected( get_user_meta( $user_id, 'user_gender', true ), 'Nữ' ); ?>>Nữ</option>
            <option value="Khác" <?php selected( get_user_meta( $user_id, 'user_gender', true ), 'Khác' ); ?>>Khác</option>
        </select>
    </p>

    <p class="woocommerce-form-row form-row form-row-last">
        <label for="user_birthday"><?php esc_html_e( 'Sinh nhật', 'woocommerce' ); ?></label>
        <input type="date" name="user_birthday" id="user_birthday" value="<?php echo esc_attr( get_user_meta( $user_id, 'user_birthday', true ) ); ?>" />
    </p>

    <p class="woocommerce-form-row form-row form-row-wide">
        <label for="user_phone"><?php esc_html_e( 'Số điện thoại', 'woocommerce' ); ?></label>
        <input type="text" name="user_phone" id="user_phone" value="<?php echo esc_attr( get_user_meta( $user_id, 'user_phone', true ) ); ?>" />
    </p>

    <?php 
        $roles = (array) $user->roles;
        if (in_array('master', $roles) || in_array('pharmer_seller', $roles)) :
    ?>
        <p class="woocommerce-form-row form-row form-row-wide">
            <label for="user_ref_link"><?php esc_html_e( 'Link giới thiệu', 'woocommerce' ); ?></label>
            <input type="text" name="user_ref_link" id="user_ref_link" value="<?php echo esc_attr( home_url() . '/dang-ky/?ref_code=' . get_user_meta( $user_id, 'my_ref_code', true ) ); ?>" readonly onclick="this.select(); document.execCommand('copy');" />
            <script>
                document.getElementById('user_ref_link').addEventListener('click', function() {
                    this.select();
                    document.execCommand('copy');
                    alert('Đã copy');
                });
            </script>
        </p>

        <p class="woocommerce-form-row form-row form-row-wide">
            <label for="user_product_link"><?php esc_html_e( 'Link sản phẩm', 'woocommerce' ); ?></label>
            <input type="text" name="user_product_link" id="user_product_link" value="<?php echo esc_attr( home_url() . '/?ref_code=' . get_user_meta( $user_id, 'my_ref_code', true ) ); ?>" readonly onclick="this.select(); document.execCommand('copy');" />
            <script>
                document.getElementById('user_product_link').addEventListener('click', function() {
                    this.select();
                    document.execCommand('copy');
                    alert('Đã copy');
                });
            </script>
        </p>
    <?php endif; ?>

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

    <?php do_action( 'woocommerce_edit_account_form' ); ?>

    <p>
        <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
        <button type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e( 'Cập nhật', 'woocommerce' ); ?>"><?php esc_html_e( 'Cập nhật', 'woocommerce' ); ?></button>
        <input type="hidden" name="action" value="save_account_details" />
    </p>

    <?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
