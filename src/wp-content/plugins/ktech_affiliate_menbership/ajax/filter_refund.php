<?php
add_action('wp_ajax_kam_filter_refund', 'kam_filter_refund_callback');
add_action('wp_ajax_nopriv_kam_filter_refund', 'kam_filter_refund_callback');

function kam_filter_refund_callback() {
  // Get filter value from POST
  $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';

  $results = '';
  if ( $filter ) {
    $user_id = get_current_user_id();
    $db = new KTech_Affiliate_DB();
    $membership = new KAM_Affiliate_Membership();
    $current_user = wp_get_current_user();
    $user_role = !empty($current_user->roles) ? $current_user->roles[0] : '';
    $column_refund = get_option( 'kam_refund_fields_by_role' );
    $column_refund = is_array($column_refund) && array_key_exists($user_role, $column_refund) ? $column_refund[$user_role] : [];
    $personal_total = $db->get_total($user_id, 'personal', $filter);
    $direct_total = $db->get_total($user_id, 'direct', $filter);
    $indirect_total = $db->get_total($user_id, 'indirect', $filter);
    $total = $db->get_total($user_id, '', $filter);
    $total_refund = $total ? $total->total_refund : 0;
    $lp_point = $total ? $total->total_lp : 0;
    $total_sales = $total ? $total->total_amount : 0;
    $direct_sales = $direct_total ? $direct_total->total_amount : 0;
    $indirect_sales = $indirect_total ? $indirect_total->total_amount : 0;
    $personal_sales = $personal_total ? $personal_total->total_amount : 0;
    $number_order = $db->get_order_count($user_id, '', $filter);
    $refund_columns = [
      'personal_sales' => ['label' => 'DS cá nhân', 'value' => wc_price($personal_sales)],
      'order_count' => ['label' => 'SL đơn hàng', 'value' => $number_order],
      'direct_sales' => ['label' => 'DS Trực tiếp', 'value' => wc_price($direct_sales)],
      'indirect_sales' => ['label' => 'DS gián tiếp', 'value' => wc_price($indirect_sales)],
      'lp_points' => ['label' => 'Điểm thưởng LP', 'value' => ($lp_point ? $lp_point : 0) . ' LP'],
      'total_sales' => ['label' => 'Tổng doanh số', 'value' => wc_price($total_sales - $personal_sales)],
      'refund' => ['label' => 'Hoàn tiền', 'value' => wc_price($total_refund)],
    ];

    $results = '<table class="payback__table"><thead><tr>';
        foreach ($refund_columns as $key => $col) {
          if (in_array($key, $column_refund)) {
            $results .= '<th>' . esc_html($col['label']) . '</th>';
          }
        }
    $results .= '</tr></thead>';
    $results .= '<tbody><tr>';
    foreach ($refund_columns as $key => $col) {
      if (in_array($key, $column_refund)) {
        $results .= '<td>' . $col['value'] . '</td>';
      }
    }
    $results .= '</tr></tbody></table>';
  } else {
    $results .= '<p>Không có kết quả phù hợp.</p>';
  }

  echo $results;

  wp_die();
}
