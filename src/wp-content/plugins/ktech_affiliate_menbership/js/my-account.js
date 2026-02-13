jQuery(document).ready(function($) {

  $('#address-list-data').on('click', '.address-list__action--edit', function(e) {
    const parentRow = $(this).closest('.address-list__row');
    const addressId = parentRow.data('id');
    const addressName = parentRow.data('name');
    const addressPhone = parentRow.data('phone');
    const addressCity = parentRow.data('city');
    const addressDistrict = parentRow.data('district');
    const addressCommune = parentRow.data('commune');
    console.log(addressCommune);
    const addressAddress = parentRow.data('address');
    const addressType = parentRow.data('type');
    const districts = Object.keys(kam_ajax_obj.vietnam_addresses[addressCity] || []);
    const $districtSelect = $('#kam_edit_address').find('select[name="district"]');
    $districtSelect.empty();
    $districtSelect.append('<option value="">Chọn quận/huyện</option>');
    districts.forEach(function(district) {
      $districtSelect.append('<option value="' + district + '">' + district + '</option>');
    });
    $('#kam_edit_address').find('input[name="address_id"]').val(addressId);
    $('#kam_edit_address').find('input[name="recipient_name"]').val(addressName);
    $('#kam_edit_address').find('input[name="phone"]').val(addressPhone);
    $('#kam_edit_address').find('select[name="city"]').val(addressCity);
    $('#kam_edit_address').find('select[name="district"]').val(addressDistrict);
    $('#kam_edit_address').find('select[name="commune"]').val(addressCommune);
    $('#kam_edit_address').find('input[name="address"]').val(addressAddress);
    $('#kam_edit_address').find(`input[name="address_type"][value="${addressType}"]`).prop('checked', true).trigger('change');
    $('#kam-update-address-myaccount-popup').show();
  })

  // Show/Hide address popup
  $('#kam_edit_address').on('submit', function(e) {
    e.preventDefault(); // Ngăn chặn hành vi mặc định của form
    const $form = $(this); // Lưu reference đến form
    const formData = $form.serialize(); // Lấy dữ liệu từ form
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_edit_address',
        form_data: formData
      },
      success: function(response) {
        $form[0].reset(); // Sử dụng $form thay vì $(this)
        $('#kam-update-address-myaccount-popup').hide();
        $('#address-list-data').html(response);
        $('#address-list-data')[0].scrollIntoView({ behavior: 'smooth' });
      },
      error: function(xhr, status, error) {
        // alert('Có lỗi xảy ra: ' + error);
      }
    });
  });

  // Hide popup when click on overlay (backdrop)
  $('#kam-update-address-myaccount-popup').on('click', function(e) {
    if (e.target === this) {
      e.stopPropagation();
      $('#kam-update-address-myaccount-popup').hide();
    }
  });

  // Hide popup with Escape key
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      $('#kam-update-address-myaccount-popup').hide();
    }
  });

  // --- Xuất Excel dạng .xlsx ---
  const tableToExcel = (table, filename = '') => {
    filename = filename ? filename + '.xlsx' : 'export.xlsx';
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    XLSX.writeFile(wb, filename);
  }
  // --- END ---

  const btnTotalRefund = $('#export-total-refund');
  btnTotalRefund.on('click', function() {
    const tableId = $(this).data('table_id');
    const table = document.getElementById(tableId);
    if (table) {
      tableToExcel(table, 'givehada-tong-hoan-tien');
    }
  });

  const btnMonthRefund = $('#export-month-refund');
  btnMonthRefund.on('click', function() {
    const tableId = $(this).data('table_id');
    const table = document.getElementById(tableId);
    if (table) {
      tableToExcel(table, 'givehada-thang-hoan-tien');
    }
  });

  const btnDirectIndirect = $('#export-direct-indirect');
  btnDirectIndirect.on('click', function() {
    const tableId = $(this).data('table_id');
    const table = document.getElementById(tableId);
    if (table) {
      tableToExcel(table, 'givehada-truc-tiep-gian-tiep');
    }
  });

  let cancelOrderUrl = '';
  $(document).on('click', 'a.orders__action--cancel', function(e){
    e.preventDefault();
    cancelOrderUrl = $(this).attr('href');
    $('#cancel-order-modal').css('display','flex');
    $('#cancel-order-loading').hide();
    $('#confirm-cancel-order').prop('disabled', false).text('Xác nhận');
  });
  $('#cancel-cancel-order').on('click', function(){
    $('#cancel-order-modal').hide();
    cancelOrderUrl = '';
  });
  $('#confirm-cancel-order').on('click', function(){
    if(cancelOrderUrl){
      $('#confirm-cancel-order').prop('disabled', true).text('Đang huỷ...');
      window.location.href = cancelOrderUrl;
    }
  });
});
