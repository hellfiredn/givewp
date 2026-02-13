jQuery(document).ready(function($) {
  $('#kam_filter_order').on('change', function() {
    var filterValue = $(this).val();
    $('#kam_refund_order').html('<div class="loader"></div>');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_filter_order',
        filter: filterValue
      },
      success: function(response) {
        $('#kam_refund_order').html(response.data.html);
        $('#kam_refund_order_export').html(response.data.html_export);
      },
      error: function(xhr, status, error) {
        alert('Có lỗi xảy ra order: ' + error);
      }
    });
  });
});