jQuery(document).ready(function($) {
  // Bảng xếp hạng dropdowns
  // Year dropdown
  $('#year-btn').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $('#year-dropdown').toggle();
      $('#month-dropdown').hide(); // Ẩn dropdown khác
  });

  // Month dropdown
  $('#month-btn').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $('#month-dropdown').toggle();
      $('#year-dropdown').hide(); // Ẩn dropdown khác
  });

  const get_master_control_data = () => {
      const year = $('#year-btn').data('value');
      const month = $('#month-btn').data('value');
      return `${month}-${year}`;
  }

  const handle_filter_master_control = () => {
    const month_year = get_master_control_data();
    $('.bxh-content#tab-thang').html('<div class="loader"></div>');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_filter_master_control',
        month_year: month_year
      },
      success: function(response) {
        $('.bxh-content#tab-thang').html(response);
      },
      error: function(xhr, status, error) {
        // alert('Có lỗi xảy ra: ' + error);
      }
    });
  }

  handle_filter_master_control();

  // Select year
  $('#year-dropdown div').on('click', function() {
      const yearText = $(this).text();
      const yearValue = $(this).data('year');
      $('#year-btn').data('value', yearValue);
      $('#year-btn').text(yearText);
      $('#year-dropdown').hide();
      handle_filter_master_control();
  });

  // Select month
  $('#month-dropdown div').on('click', function() {
      const monthText = $(this).text();
      const monthValue = $(this).data('month');
      $('#month-btn').data('value', monthValue);
      $('#month-btn').text(monthText);
      $('#month-dropdown').hide();
      handle_filter_master_control();
  });

  // Close dropdowns when click outside
  $(document).on('click', function(e) {
      if (!$(e.target).closest('.bxh-year-dropdown, .bxh-month-dropdown').length) {
          $('#year-dropdown, #month-dropdown').hide();
      }
  });

  // Close dropdowns with Escape key
  $(document).on('keydown', function(e) {
      if (e.key === 'Escape') {
          $('#year-dropdown, #month-dropdown').hide();
      }
  });

  $('.copy-ref-link-btn').on('click', function() {
    const link = $(this).data('link');
    navigator.clipboard.writeText(link).then(function() {
      $('.ref-link-notification').text('Đã copy!');
      setTimeout(function() {
        $('.ref-link-notification').text('');
      }, 2000);
    });
  });
});