jQuery(document).ready(function($) {
  // Hiển thị popup khi người dùng chọn checkbox "Yêu cầu xuất hóa đơn"
  $('#require_invoice').on('change', function(){
    if($(this).is(':checked')){
      $('#invoice_info_popup').fadeIn();
      $('#invoice_info_popup').css('display','flex');
    } else {
      $('#invoice_info_popup').css('display','');
      $('#invoice_info_popup').fadeOut();
    }
  });

  // Đóng popup khi nhấn nút đóng, nếu chưa nhập gì thì bỏ chọn checkbox
  $('#close_invoice_popup').on('click', function(){
    var allEmpty = !$('#invoice_company').val().trim() && !$('#invoice_tax').val().trim() && !$('#invoice_address').val().trim() && !$('#invoice_email').val().trim();
    $('#invoice_info_popup').fadeOut();
    if(allEmpty){
      $('#require_invoice').prop('checked', false);
    }
  });

  // Đóng popup khi click ra ngoài vùng nội dung, nếu chưa nhập gì thì bỏ chọn checkbox
  $('#invoice_info_popup').on('click', function(e){
    if(e.target === this){
      var allEmpty = !$('#invoice_company').val().trim() && !$('#invoice_tax').val().trim() && !$('#invoice_address').val().trim() && !$('#invoice_email').val().trim();
      $('#invoice_info_popup').fadeOut();
      if(allEmpty){
        $('#require_invoice').prop('checked', false);
      }
    }
  });

  // Xử lý nút "Xong" trong popup: chỉ đóng popup nếu đã nhập đủ thông tin, nếu thiếu thì báo lỗi
  $('#done_invoice_popup').on('click', function(){
    var filled = $('#invoice_company').val().trim() && $('#invoice_tax').val().trim() && $('#invoice_address').val().trim() && $('#invoice_email').val().trim();
    if(filled){
      $('#invoice_info_popup').fadeOut();
    }else{
      alert('Vui lòng nhập đầy đủ thông tin hóa đơn!');
    }
  });

  // Kiểm tra trạng thái các trường thông tin hóa đơn, nếu đủ thì hiện nút "Xong", nếu thiếu thì ẩn
  function checkInvoiceFields() {
    var filled = $('#invoice_company').val().trim() && $('#invoice_tax').val().trim() && $('#invoice_address').val().trim() && $('#invoice_email').val().trim();
    if(filled){
      $('#done_invoice_popup').fadeIn();
    }else{
      $('#done_invoice_popup').fadeOut();
    }
  }

  // Lắng nghe sự thay đổi trên các trường input để kiểm tra trạng thái
  $('#invoice_company, #invoice_tax, #invoice_address, #invoice_email').on('input', checkInvoiceFields);
  // Kiểm tra trạng thái ban đầu khi load trang
  checkInvoiceFields();
});
