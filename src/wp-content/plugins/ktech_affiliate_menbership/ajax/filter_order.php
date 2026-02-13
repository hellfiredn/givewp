<?php
add_action('wp_ajax_kam_filter_order', 'kam_filter_order_callback');
add_action('wp_ajax_nopriv_kam_filter_order', 'kam_filter_order_callback');

function kam_filter_order_callback() {
  // Get filter value from POST
  $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';

  $results = '';
  $results_export = '';

  if ( $filter ) {
    $user_id = get_current_user_id();
    $db = new KTech_Affiliate_DB();

    // Lấy danh sách đơn hàng theo filter
    $all_orders = $db->get_orders_direct_indirect($user_id, $filter);

    if ( !empty($all_orders) ) {
      $results .= '<table class="payback__table">
      <thead>
      <tr>
        <th>Họ và Tên</th>
        <th>Loại tài khoản</th>
        <th>Giá trị đơn hàng</th>
        <th>Ngày đặt mua</th>
      </tr>
      </thead>
      <tbody>';
      foreach ($all_orders as $order) {
        $results .= '<tr>
          <td>' . esc_html($order->order_username) . '</td>
          <td>' . esc_html($order->order_userrole) . '</td>
          <td>' . wc_price($order->amount) . '</td>
          <td>' . date('d-m-Y', strtotime($order->created_at)) . '</td>
        </tr>';
      }
      $results .= '</tbody></table>';

      // HTML Export
      $results_export .= '<table class="payback__table">
        <thead>
          <tr>
            <th>Ngày đặt mua</th>
            <th>Mã đơn hàng</th>
            <th>Họ và Tên</th>
            <th>Loại tài khoản</th>
            <th>ID</th>
            <th>Người giới thiệu</th>
            <th>Sản phẩm</th>
            <th>SKU</th>
            <th>SL</th>
            <th>Giá trị đơn hàng</th>
          </tr>
        </thead>
      <tbody>';
      foreach ($all_orders as $order) {
        $order_obj = wc_get_order($order->order_id);
        $customer_id = $order_obj ? $order_obj->get_customer_id() : '';
        $ref_by_id = get_user_meta($customer_id, 'ref_by', true);
        $my_user = get_userdata($customer_id);
        $my_id = $my_user ? $my_user->user_login : '';
        $ref_by_display_name = '';
        if ($ref_by_id) {
          $ref_by_user = get_userdata($ref_by_id);
          $ref_by_display_name = $ref_by_user ? $ref_by_user->display_name : $ref_by_id;
        }
        $order_items = wc_get_order($order->order_id)->get_items();
        $product_names = [];
        $product_sku = [];
        $product_qty = [];
        foreach ($order_items as $item) {
          $product_names[] = $item->get_name();
          $product_sku[] = $item->get_product() ? $item->get_product()->get_sku() : '';
          $product_qty[] = $item->get_quantity();
        }
        $results_export .= '<tr>
          <td>' . date('d-m-Y', strtotime($order->created_at)) . '</td>
          <td>' . esc_html($order->order_id) . '</td>
          <td>' . esc_html($order->order_username) . '</td>
          <td>' . esc_html($order->order_userrole) . '</td>
          <td>' . esc_html($my_id) . '</td>
          <td>' . esc_html($ref_by_display_name). '</td>
          <td>' . implode(', ', $product_names) . '</td>
          <td>' . implode(', ', $product_sku) . '</td>
          <td>' . implode(', ', $product_qty) . '</td>
          <td>' . wc_price($order->amount) . '</td>
        </tr>';
      }
      $results_export .= '</tbody></table>';
    } else {
      $results .= '<p>Không có đơn hàng nào phù hợp.</p>';
      $results_export .= '<p>Không có đơn hàng nào phù hợp.</p>';
    }
  } else {
    $results .= '<p>Không có kết quả phù hợp.</p>';
    $results_export .= '<p>Không có kết quả phù hợp.</p>';
  }

  wp_send_json_success([
    'html' => $results,
    'html_export' => $results_export,
  ]);

  wp_die();
}
