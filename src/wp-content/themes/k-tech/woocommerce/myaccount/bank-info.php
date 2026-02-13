<?php
defined( 'ABSPATH' ) || exit;

// In thông báo WooCommerce
wc_print_notices();

$current_user_id = get_current_user_id();
?>

<form method="post" class="woocommerce-EditAccountForm edit-account">

    <h3>Thông tin tài khoản ngân hàng</h3>

    <p class="form-row form-row-wide">
        <label for="bank_account_name">Tên người thụ hưởng</label>
        <input type="text" name="bank_account_name" placeholder="Nhập tên người thụ hưởng" id="bank_account_name"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'bank_account_name', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="bank_account_number">Số tài khoản thụ hưởng</label>
        <input type="text" placeholder="Nhập số tài khoản thụ hưởng" name="bank_account_number" id="bank_account_number"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'bank_account_number', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="bank_name">Ngân hàng thụ hưởng</label>
        <input type="text" placeholder="Nhập tên ngân hàng thụ hưởng" name="bank_name" id="bank_name"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'bank_name', true ) ); ?>">
    </p>

    <h3>Thông tin kê khai thuế</h3>

    <p class="form-row form-row-wide">
        <label for="tax_company_name">Tên đơn vị</label>
        <input type="text" placeholder="Nhập tên đơn vị" name="tax_company_name" id="tax_company_name"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_company_name', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="tax_address">Địa chỉ</label>
        <input type="text" placeholder="Nhập địa chỉ" name="tax_address" id="tax_address"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_address', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="tax_code">Mã số thuế</label>
        <input type="text" placeholder="Nhập mã số thuế" name="tax_code" id="tax_code"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_code', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="tax_email">Email (Nhận chứng từ khấu trừ thuế điện tử/hóa đơn điện tử)</label>
        <input type="email" placeholder="Nhập email" name="tax_email" id="tax_email"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_email', true ) ); ?>">
    </p>

    <p class="form-row form-row-wide">
        <label for="tax_id_number">Số CCCD/CMND</label>
        <input type="text" placeholder="Nhập số CCCD/CMND" name="tax_id_number" id="tax_id_number"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_id_number', true ) ); ?>">
    </p>

    <p class="form-row form-row-first field-date">
        <label for="tax_issue_date">Ngày cấp</label>
        <input type="date" name="tax_issue_date" placeholder="dd/mm/yyyy" id="tax_issue_date"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_issue_date', true ) ); ?>">
    </p>

    <p class="form-row form-row-last field-place">
        <label for="tax_issue_place">Nơi cấp</label>
        <input type="text" name="tax_issue_place" placeholder="Nhập nơi cấp" id="tax_issue_place"
               value="<?php echo esc_attr( get_user_meta( $current_user_id, 'tax_issue_place', true ) ); ?>">
    </p>

    <div class="clear"></div>

    <?php wp_nonce_field( 'save_bank_info', 'save_bank_info_nonce' ); ?>
    <button type="submit" name="save_bank_info" class="woocommerce-Button button">
        Cập nhập
    </button>
</form>
