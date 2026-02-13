<?php

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KTech_Order_Admin {
  public function __construct () {
    add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_invoice_column'));
    add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'render_invoice_column'), 10, 2);
    
    // Thêm meta box vào trang order detail
    add_action('add_meta_boxes_woocommerce_page_wc-orders', array($this, 'add_invoice_meta_box'));
  }

  // Thêm cột "Gửi hoá đơn" vào danh sách đơn hàng
  public function add_invoice_column($columns) {
    $columns['is_send_invoice'] = __('Yêu cầu hoá đơn', 'ktech_affiliate_menbership');
    return $columns;
  }

  // Hiển thị nội dung cho cột "Gửi hoá đơn"
  public function render_invoice_column($column, $post_id) {
    if ($column === 'is_send_invoice') {
      $post_id = is_object($post_id) ? $post_id->ID : $post_id;
      $is_send_invoice = get_post_meta($post_id, '_require_invoice', true);
      if ($is_send_invoice !== 'yes') {
        echo 'Không';
        return;
      }
      echo 'Có';
    }
  }
  
  // Thêm meta box thông tin hoá đơn
  public function add_invoice_meta_box() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'shop_order' || $screen->id === 'woocommerce_page_wc-orders')) {
      add_meta_box(
        'ktech_invoice_info',
        'Thông tin hoá đơn',
        array($this, 'invoice_meta_box_content'),
        $screen->id,
        'normal',
        'high'
      );
    }
  }
  
  // Hiển thị nội dung meta box
  public function invoice_meta_box_content($post) {
    $order_id = $post->ID;
    $require_invoice = get_post_meta($order_id, '_require_invoice', true);
    $company = get_post_meta($order_id, '_invoice_company', true);
    $tax = get_post_meta($order_id, '_invoice_tax', true);
    $address = get_post_meta($order_id, '_invoice_address', true);
    $email = get_post_meta($order_id, '_invoice_email', true);
    
    if ($require_invoice !== 'yes') {
      echo '<p>Khách hàng không yêu cầu hoá đơn.</p>';
      return;
    }
    
    echo '<table class="form-table">';
    echo '<tr><th>Công ty:</th><td>' . esc_html($company) . '</td></tr>';
    echo '<tr><th>Mã số thuế:</th><td>' . esc_html($tax) . '</td></tr>';
    echo '<tr><th>Địa chỉ:</th><td>' . esc_html($address) . '</td></tr>';
    echo '<tr><th>Email nhận HĐ:</th><td>' . esc_html($email) . '</td></tr>';
    echo '</table>';
  }
}

new KTech_Order_Admin();