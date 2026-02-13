<?php
add_action('wp_ajax_kam_filter_master_control', 'kam_filter_master_control_callback');
add_action('wp_ajax_nopriv_kam_filter_master_control', 'kam_filter_master_control_callback');

function kam_filter_master_control_callback() {
  // Get filter value from POST
  $month_year = isset($_POST['month_year']) ? sanitize_text_field($_POST['month_year']) : '';

  $results = '';
  if ( $month_year ) {
    $db = new KTech_Affiliate_DB();
    $masters = $db->get_top_users_by_month($month_year);
    if (!empty($masters)) {
      // Chia thành 2 cột: cột trái 5 item đầu, cột phải 5 item tiếp
      $left_column = array_slice($masters, 0, 5);
      $right_column = array_slice($masters, 5, 5);
      
      $results .= '<div class="bxh-wrapper" style="display: flex; gap: 20px;">';
      
      // Cột trái
      $results .= '<div class="bxh-column" style="flex: 1;">';
      foreach ($left_column as $i => $master) {
        $class = ($i < 3) ? 'top3' : '';
        $results .= '<div class="bxh-item ' . $class . '"><span>' . ($i + 1) . '</span> ' . $master->display_name . '</div>';
      }
      $results .= '</div>';
      
      // Cột phải
      $results .= '<div class="bxh-column" style="flex: 1;">';
      foreach ($right_column as $i => $master) {
        $rank = $i + 6; // Bắt đầu từ số 6
        $results .= '<div class="bxh-item"><span>' . $rank . '</span> ' . $master->display_name . '</div>';
      }
      $results .= '</div>';
      
      $results .= '</div>';
    } else {
      $results .= '<p>Chưa có dữ liệu thống kê</p>';
    }
  }

  echo $results;

  wp_die();
}
