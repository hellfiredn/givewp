jQuery(document).ready(function($) {
  $('#kam_filter_refund').on('change', function() {
    var filterValue = $(this).val();
    $('#kam_refund_results').html('<div class="loader"></div>');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_filter_refund',
        filter: filterValue
      },
      success: function(response) {
        $('#kam_refund_results').html(response);
      },
      error: function(xhr, status, error) {
        // alert('Có lỗi xảy ra: ' + error);
      }
    });
  });
});