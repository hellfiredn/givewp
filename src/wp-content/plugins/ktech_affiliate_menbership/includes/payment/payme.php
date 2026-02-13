<?php
if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'init_payme_gateway');
function init_payme_gateway() {
  if (!class_exists('WC_Payment_Gateway')) return;
  
  if (!class_exists('WC_Gateway_Payme')) {
    class WC_Gateway_Payme extends WC_Payment_Gateway {
      public function __construct() {
        $this->id = 'payme';
        $this->method_title = 'Payme';
        $this->method_description = 'Thanh toán qua Payme';
        $this->has_fields = false;
        $this->title = 'Payme';
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
      }
      public function init_form_fields() {
        $this->form_fields = array(
          'enabled' => array(
            'title' => 'Kích hoạt',
            'type' => 'checkbox',
            'label' => 'Kích hoạt phương thức Payme',
            'default' => 'yes'
          ),
          'title' => array(
            'title' => 'Tên hiển thị',
            'type' => 'text',
            'default' => 'Payme'
          )
        );
      }
      public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        // Xử lý logic thanh toán ở đây
        $order->update_status('on-hold', 'Chờ thanh toán qua Payme');
        // Trả về kết quả cho WooCommerce
        return array(
          'result' => 'success',
          'redirect' => $this->get_return_url($order)
        );
      }
    }
  }
  
  class Payme_Payment_Method {
    public function __construct() {
      add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
    }

    public function add_gateway($gateways) {
      if (class_exists('WC_Gateway_Payme')) {
        $gateways[] = 'WC_Gateway_Payme';
      }
      return $gateways;
    }
  }
  new Payme_Payment_Method();
}