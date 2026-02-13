<?php
defined( 'ABSPATH' ) || exit;

// In thông báo WooCommerce
wc_print_notices();

$current_user_id = get_current_user_id();
?>

<div class="upgrade-account">
    <div class="upgrade-account__header text-center">
        <h1 class="upgrade-account__title">THÔNG TIN NÂNG CẤP TÀI KHOẢN</h1>
        <!-- <p class="upgrade-account__note">Tài khoản đang trong quá trình chờ duyệt nâng cấp</p> -->
        <p class="upgrade-account__note"></p>
    </div>

    <form id="upgradeAccountForm">
        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Loại tài khoản</label>
            <select name="account_type" class="upgrade-account__input" required>
                <option value="">Chọn loại tài khoản</option>
                <?php
                $current_user = wp_get_current_user();
                $current_role = $current_user->roles[0] ?? 'customer';
                
                // Show upgrade options based on current role
                ?>
                <option value="Master">Master</option>
            </select>
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Tên doanh nghiệp</label>
            <input type="text" name="company_name" class="upgrade-account__input" placeholder="Nhập tên doanh nghiệp" required>
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Tên người đại diện</label>
            <input type="text" name="representative_name" class="upgrade-account__input" placeholder="Nhập tên người đại diện" required>
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Địa chỉ kinh doanh</label>
            <input type="text" name="business_address" class="upgrade-account__input" placeholder="Nhập địa chỉ kinh doanh" required>
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Mã số doanh nghiệp</label>
            <input type="text" name="business_code" class="upgrade-account__input" placeholder="Nhập mã số doanh nghiệp" required>
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Upload file GPKD</label>
            <input type="file" name="business_license" class="upgrade-account__input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
        </div>

        <div class="upgrade-account__form-group">
            <label class="upgrade-account__label">Số điện thoại liên hệ</label>
            <input type="tel" name="phone" class="upgrade-account__input" placeholder="Nhập số điện thoại liên hệ" required>
        </div>

        <button type="submit" class="upgrade-account__submit">Nâng cấp</button>
    </form>

    <div id="upgradeAccountMessage" style="margin-top:10px;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    // Check upgrade status on page load
    checkUpgradeStatus();
    
    function checkUpgradeStatus() {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_upgrade_request_status'
            },
            success: function(response) {
                if (response.success && response.data.status) {
                    var status = response.data.status;
                    var $form = $('#upgradeAccountForm');
                    var $message = $('#upgradeAccountMessage');
                    var $note = $('.upgrade-account__note');
                    
                    if (status === 'pending') {
                        $note.text('Yêu cầu nâng cấp đang chờ duyệt');
                        $note.css('color', '#f39c12');
                        $form.hide();
                        $message.html('<div class="upgrade-notice warning">Bạn đã gửi yêu cầu nâng cấp lên <strong>' + response.data.requested_package + '</strong> và đang chờ admin duyệt.</div>');
                        
                        // Fill form with existing data if needed
                        if (response.data.data) {
                            fillFormWithData(response.data.data);
                        }
                    } else if (status === 'approved') {
                        $note.text('Yêu cầu nâng cấp đã được duyệt');
                        $note.css('color', '#27ae60');
                        $form.hide();
                        $message.html('<div class="upgrade-notice success">Yêu cầu nâng cấp của bạn đã được duyệt! Tài khoản đã được nâng cấp thành công.</div>');
                    } else if (status === 'rejected') {
                        $note.text('Yêu cầu nâng cấp đã bị từ chối');
                        $note.css('color', '#e74c3c');
                        var reason = response.data.rejection_reason || 'Không có lý do cụ thể';
                        $message.html('<div class="upgrade-notice error">Yêu cầu nâng cấp của bạn đã bị từ chối. Lý do: ' + reason + '</div>');
                        $form.show();
                    }
                }
            }
        });
    }
    
    function fillFormWithData(data) {
        $('input[name="company_name"]').val(data.company_name);
        $('input[name="representative_name"]').val(data.representative_name);
        $('input[name="business_address"]').val(data.business_address);
        $('input[name="business_code"]').val(data.business_code);
        $('input[name="phone"]').val(data.contact_phone);
        $('input[name="account_type"]').val(data.requested_package);
    }
    
    // Handle form submission
    $('#upgradeAccountForm').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var $message = $('#upgradeAccountMessage');
        
        // Disable submit button
        $submitBtn.prop('disabled', true).text('Đang gửi...');
        
        // Prepare form data with file
        var formData = new FormData(this);
        formData.append('action', 'submit_upgrade_account');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="upgrade-notice success">' + response.data + '</div>');
                    $form.hide();
                    $('.upgrade-account__note').text('Yêu cầu nâng cấp đang chờ duyệt').css('color', '#f39c12');
                } else {
                    $message.html('<div class="upgrade-notice error">' + response.data + '</div>');
                    $submitBtn.prop('disabled', false).text('Nâng cấp');
                }
            },
            error: function() {
                $message.html('<div class="upgrade-notice error">Có lỗi xảy ra. Vui lòng thử lại.</div>');
                $submitBtn.prop('disabled', false).text('Nâng cấp');
            }
        });
    });
});
</script>

<style>
.upgrade-notice {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}
.upgrade-notice.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}
.upgrade-notice.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
.upgrade-notice.warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}
</style>

