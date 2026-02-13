// VOUCHER
jQuery(document).ready(function($) {
  // Show popup
  $('#add-voucher-btn').click(function() {
    $('#voucher-popup').show();
  });
  
  // Hide popup
  $('.voucher-popup-close, .voucher-popup-overlay').click(function(e) {
    if (e.target === this) {
      $('#voucher-popup').hide();
    }
  });
  
  // Hide edit popup
  $('.edit-voucher-popup-close').click(function() {
    $('#edit-voucher-popup').hide();
  });
  
  $('#edit-voucher-popup .voucher-popup-overlay').click(function(e) {
    if (e.target === this) {
      $('#edit-voucher-popup').hide();
    }
  });
  
  // Add voucher
  $('#add-voucher-form').on('submit', function(e) {
    e.preventDefault();
    
    var $submitBtn = $(this).find('button[type="submit"]');
    var $form = $(this);
    var originalBtnText = $submitBtn.text();
    
    var formData = $(this).serialize();

    // Show loading state
    $submitBtn.prop('disabled', true).text('Đang thêm...');
    $form.find('input, select, textarea, button').prop('disabled', true);

    formData += `&action=add_voucher&nonce=${kam_ajax_obj.voucher_nonce}`;

    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.success) {
          // Success state
          $submitBtn.text('✓ Thành công!');
          setTimeout(function() {
            location.reload();
          }, 1000);
        } else {
          // Error state
          $submitBtn.text('✗ Lỗi').removeClass('button-primary').addClass('button-secondary');
          alert('Lỗi: ' + response.data);
          
          // Restore form after error
          setTimeout(function() {
            $form.find('input, select, textarea, button').prop('disabled', false);
            $submitBtn.prop('disabled', false).text(originalBtnText).removeClass('button-secondary').addClass('button-primary');
          }, 2000);
        }
      },
      error: function() {
        // Network error state
        $submitBtn.text('✗ Lỗi kết nối').removeClass('button-primary').addClass('button-secondary');
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
        
        // Restore form after error
        setTimeout(function() {
          $form.find('input, select, textarea, button').prop('disabled', false);
          $submitBtn.prop('disabled', false).text(originalBtnText).removeClass('button-secondary').addClass('button-primary');
        }, 2000);
      }
    });
  });
  
  // Edit voucher
  $('#edit-voucher-form').on('submit', function(e) {
    e.preventDefault();
    
    var $submitBtn = $(this).find('button[type="submit"]');
    var $form = $(this);
    var originalBtnText = $submitBtn.text();
    
    var formData = $(this).serialize();
    formData += `&action=edit_voucher&nonce=${kam_ajax_obj.voucher_nonce}`;

    // Show loading state
    $submitBtn.prop('disabled', true).text('Đang cập nhật...');
    $form.find('input, select, textarea, button').prop('disabled', true);
    
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.success) {
          // Success state
          $submitBtn.text('✓ Thành công!');
          setTimeout(function() {
            location.reload();
          }, 1000);
        } else {
          // Error state
          $submitBtn.text('✗ Lỗi').removeClass('button-primary').addClass('button-secondary');
          alert('Lỗi: ' + response.data);
          
          // Restore form after error
          setTimeout(function() {
            $form.find('input, select, textarea, button').prop('disabled', false);
            $submitBtn.prop('disabled', false).text(originalBtnText).removeClass('button-secondary').addClass('button-primary');
          }, 2000);
        }
      },
      error: function() {
        // Network error state
        $submitBtn.text('✗ Lỗi kết nối').removeClass('button-primary').addClass('button-secondary');
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
        
        // Restore form after error
        setTimeout(function() {
          $form.find('input, select, textarea, button').prop('disabled', false);
          $submitBtn.prop('disabled', false).text(originalBtnText).removeClass('button-secondary').addClass('button-primary');
        }, 2000);
      }
    });
  });
  
  // Update status
  $('.status-btn').click(function() {
    var voucherId = $(this).data('id');
    var newStatus = $(this).data('status');
    
    if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái?')) {
      $.ajax({
        url: kam_ajax_obj.ajaxurl,
        type: 'POST',
        data: {
          action: 'update_voucher_status',
          voucher_id: voucherId,
          status: newStatus,
          nonce: kam_ajax_obj.voucher_nonce
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          } else {
            alert('Lỗi: ' + response.data);
          }
        }
      });
    }
  });
  
  // Edit voucher
  $('.edit-btn').click(function() {
    var voucherData = $(this).data('voucher');
    console.log('Edit button clicked for voucher:', voucherData);
    
    // Show popup immediately
    $('#edit-voucher-popup').show();
    $('#edit-voucher-form')[0].reset(); // Clear form
    
    // Fill form with voucher data immediately (no AJAX needed)
    $('#edit-voucher-form input[name="voucher_id"]').val(voucherData.id);
    $('#edit-voucher-form input[name="voucher_code"]').val(voucherData.voucher_code);
    $('#edit-voucher-form input[name="title"]').val(voucherData.title);
    $('#edit-voucher-form textarea[name="description"]').val(voucherData.description || '');
    $('#edit-voucher-form input[name="minimum_order"]').val(voucherData.minimum_order);
    $('#edit-voucher-form input[name="usage_limit"]').val(voucherData.usage_limit || '');
    $('#edit-voucher-form input[name="image_url"]').val(voucherData.image_url || '');
    
    $('#edit-voucher-form input[name="image_url"]').val(voucherData.image_url || '');
    
    // Format expiry date for datetime-local input
    if (voucherData.expiry_date && voucherData.expiry_date !== '0000-00-00 00:00:00' && voucherData.expiry_date !== null) {
      var date = new Date(voucherData.expiry_date);
      if (!isNaN(date.getTime())) {
        var localDateTime = date.getFullYear() + '-' + 
          String(date.getMonth() + 1).padStart(2, '0') + '-' + 
          String(date.getDate()).padStart(2, '0') + 'T' + 
          String(date.getHours()).padStart(2, '0') + ':' + 
          String(date.getMinutes()).padStart(2, '0');
        $('#edit-voucher-form input[name="expiry_date"]').val(localDateTime);
      }
    } else {
      $('#edit-voucher-form input[name="expiry_date"]').val('');
    }
  });
  
  // Delete voucher
  $('.delete-btn').click(function() {
    var voucherId = $(this).data('id');
    
    if (confirm('Bạn có chắc chắn muốn xóa voucher này?')) {
      $.ajax({
        url: kam_ajax_obj.ajaxurl,
        type: 'POST',
        data: {
          action: 'delete_voucher',
          voucher_id: voucherId,
          nonce: kam_ajax_obj.voucher_nonce
        },
        success: function(response) {
          if (response.success) {
            alert('Đã xóa voucher!');
            location.reload();
          } else {
            alert('Lỗi: ' + response.data);
          }
        }
      });
    }
  });

  // Delete role confirmation
  let roleToDelete = '';
  $(document).on('click', '.delete-role-btn', function(){
    roleToDelete = $(this).data('role');
    let roleName = $(this).closest('tr').find('input[name^="role_name_"]').val();
    $('#delete-role-name').text('Bạn có chắc chắn muốn xoá role "' + roleName + '"?');
    $('#delete-role-modal').css('display','flex');
    $('#delete-role-error').text('');
    $('#confirm-delete-role').prop('disabled', false).text('Xác nhận');
  });
  $(document).on('click', '#cancel-delete-role', function(){
    $('#delete-role-modal').hide();
  });
  $(document).on('click', '#confirm-delete-role', function(){
    if(!roleToDelete) return;
    let $btn = $(this);
    $btn.prop('disabled', true).text('Đang xoá...');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'delete_role',
        role: roleToDelete,
        nonce: kam_ajax_obj.delete_role_nonce
      },
      success: function(response){
        if(response.success){
          location.reload();
        }else{
          $('#delete-role-error').text(response.data);
          $btn.prop('disabled', false).text('Xác nhận');
        }
      },
      error: function(){
        $('#delete-role-error').text('Có lỗi xảy ra.');
        $btn.prop('disabled', false).text('Xác nhận');
      }
    });
  });
});