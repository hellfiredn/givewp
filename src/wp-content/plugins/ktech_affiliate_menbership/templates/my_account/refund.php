<?php
  $user_id = get_current_user_id();
  $db = new KTech_Affiliate_DB();
  $membership = new KAM_Affiliate_Membership();
  $current_user = wp_get_current_user();
  $user_role = !empty($current_user->roles) ? $current_user->roles[0] : '';
  $column_refund = get_option( 'kam_refund_fields_by_role' );
  $column_refund = is_array($column_refund) && array_key_exists($user_role, $column_refund) ? $column_refund[$user_role] : [];
  $personal_total = $db->get_total($user_id, 'personal');
  $direct_total = $db->get_total($user_id, 'direct');
  $indirect_total = $db->get_total($user_id, 'indirect');
  $total = $db->get_total($user_id);
  $total_refund = $total ? $total->total_refund : 0;
  $lp_point = $total ? $total->total_lp : 0;
  $total_sales = $total ? $total->total_amount : 0;
  $direct_sales = $direct_total ? $direct_total->total_amount : 0;
  $indirect_sales = $indirect_total ? $indirect_total->total_amount : 0;
  $personal_sales = $personal_total ? $personal_total->total_amount : 0;
  $number_order = $db->get_order_count($user_id);
  $refund_columns = [
    'personal_sales' => ['label' => 'DS cá nhân', 'value' => wc_price($personal_sales)],
    'order_count' => ['label' => 'SL đơn hàng', 'value' => $number_order],
    'direct_sales' => ['label' => 'DS Trực tiếp', 'value' => wc_price($direct_sales)],
    'indirect_sales' => ['label' => 'DS gián tiếp', 'value' => wc_price($indirect_sales)],
    'lp_points' => ['label' => 'Điểm thưởng LP', 'value' => ($lp_point ? $lp_point : 0) . ' LP'],
    'total_sales' => ['label' => 'Tổng doanh số', 'value' => wc_price($total_sales - $personal_sales)],
    'refund' => ['label' => 'Hoàn tiền', 'value' => wc_price($total_refund)],
  ];
?>

<div class="payback">
    <div class="payback__header">
        <h1 class="payback__header-title">THÔNG TIN HOÀN TIỀN VÀ ĐIỂM THƯỞNG LP</h1>
        <p class="payback__header-desc">
            (Chính sách hoàn tiền và tích điểm áp dụng theo nguyên tắc tiêu dùng hàng hóa, không chi trả dựa trên việc phát triển mạng lưới thành viên)
        </p>
    </div>

    <div class="payback__section">
      <h3 class="payback__section-title">Tổng hoàn tiền</h3>
      <button id="export-total-refund" data-table_id="table-total-refund" class="payback__export-btn">Xuất Excel</button>
      <div class="payback__table-wrapper">
          <table class="payback__table" id="table-total-refund">
            <thead>
              <tr>
                <?php foreach ($refund_columns as $key => $col) {
                  if (in_array($key, $column_refund)) {
                    echo '<th>' . esc_html($col['label']) . '</th>';
                  }
                } ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <?php foreach ($refund_columns as $key => $col) {
                  if (in_array($key, $column_refund)) {
                    echo '<td>' . $col['value'] . '</td>';
                  }
                } ?>
              </tr>
            </tbody>
        </table>
      </div>
    </div>

    <div class="payback__section">
        <div class="payback__filter">
            <h3 class="payback__section-title">Lọc theo tháng</h3>
            <input type="daterange" id="kam_filter_refund" class="payback__filter-date" placeholder="Chọn ngày" />
            <button id="export-month-refund" data-table_id="kam_refund_results" class="payback__export-btn">Xuất Excel</button>
        </div>
        <div class="payback__table-wrapper" id="kam_refund_results">
          <div class="loader"></div>
        </div>
    </div>

    <div class="payback__section">
      <?php
        $all_orders = $db->get_orders_direct_indirect($user_id);
      ?>
      <div class="payback__filter">
          <h3 class="payback__section-title">Đơn hàng trực tiếp và gián tiếp</h3>
          <input type="daterange" id="kam_filter_order" class="payback__filter-date" placeholder="Chọn ngày" />
          <button id="export-direct-indirect" data-table_id="kam_refund_order_export" class="payback__export-btn">Xuất Excel</button>
      </div>
      <div class="payback__table-wrapper" id="kam_refund_order">
        <div class="loader"></div>
      </div>
      <div class="payback__table-wrapper" id="kam_refund_order_export" style="display: none !important;">
        <div class="loader"></div>
      </div>
    </div>
</div>