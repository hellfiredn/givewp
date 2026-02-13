<?php

function ktech_affiliate_get_template($template_name) {
    $plugin_path = WP_PLUGIN_DIR . '/ktech_affiliate_menbership/templates/' . $template_name;
    if (file_exists($plugin_path)) {
        include $plugin_path;
    }
}

add_action( 'woocommerce_shipping_init', function () {
    
    if ( ! class_exists( 'WC_Shipping_Method' ) ) {
        return;
    }

    if ( ! class_exists( 'WC_Shipping_GHN' ) ) {
        class WC_Shipping_GHN extends WC_Shipping_Method {
            public function process_admin_options() {
                parent::process_admin_options();
                $this->init_settings();
                $this->title      = $this->get_option('title');
                $this->tax_status = $this->get_option('tax_status');
                $this->cost       = $this->get_option('cost');
            }
            public function __construct( $instance_id = 0 ) {
                $this->id                 = 'ghn';
                $this->instance_id        = absint( $instance_id );
                $this->method_title       = __( 'Giao Hàng Nhanh', 'woocommerce' );
                $this->method_description = __( 'Phương thức giao hàng nhanh với giá cả phải chăng', 'woocommerce' );
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                );
                $this->init_form_fields();
                $this->init_settings();
                // Define user set variables
                $this->title           = $this->get_option( 'title' );
                $this->tax_status      = $this->get_option( 'tax_status' );
                $this->cost            = $this->get_option( 'cost' );
            }

            public function init_form_fields() {
                $this->form_fields = array(
                    'title' => array(
                        'title'       => __( 'Tiêu đề phương thức', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'Tên hiển thị của phương thức giao hàng', 'woocommerce' ),
                        'default'     => __( 'Giao Hàng Nhanh', 'woocommerce' ),
                        'desc_tip'    => true,
                    ),
                    'tax_status' => array(
                        'title'   => __( 'Tình trạng thuế', 'woocommerce' ),
                        'type'    => 'select',
                        'class'   => 'wc-enhanced-select',
                        'default' => 'taxable',
                        'options' => array(
                            'taxable' => __( 'Tính thuế', 'woocommerce' ),
                            'none'    => __( 'Không tính thuế', 'woocommerce' ),
                        ),
                    ),
                    'cost' => array(
                        'title'       => __( 'Phí giao hàng', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'Nhập phí giao hàng cố định (VNĐ)', 'woocommerce' ),
                        'default'     => 0,
                        'desc_tip'    => true,
                    ),
                );
            }

            public function calculate_shipping( $package = array() ) {
                $cost = $this->cost ? floatval( $this->cost ) : 0;
                $rate = array(
                    'id'    => $this->get_rate_id(),
                    'label' => $this->title,
                    'cost'  => $cost,
                );
                $this->add_rate( $rate );
            }

            public function is_available( $package ) {
                return parent::is_available( $package );
            }
        }
    }

    if ( ! class_exists( 'WC_Shipping_Grab' ) ) {
        class WC_Shipping_Grab extends WC_Shipping_Method {
            public function process_admin_options() {
                parent::process_admin_options();
                $this->init_settings();
                $this->title      = $this->get_option('title');
                $this->tax_status = $this->get_option('tax_status');
                $this->cost       = $this->get_option('cost');
            }
            public function __construct( $instance_id = 0 ) {
                $this->id                 = 'grab';
                $this->instance_id        = absint( $instance_id );
                $this->method_title       = __( 'Grab', 'woocommerce' );
                $this->method_description = __( 'Phương thức Grab với giá cả phải chăng', 'woocommerce' );
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                );
                $this->init_form_fields();
                $this->init_settings();
                // Define user set variables
                $this->title           = $this->get_option( 'title' );
                $this->tax_status      = $this->get_option( 'tax_status' );
                $this->cost            = $this->get_option( 'cost' );
            }

            public function init_form_fields() {
                $this->form_fields = array(
                    'title' => array(
                        'title'       => __( 'Tiêu đề phương thức', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'Tên hiển thị của phương thức giao hàng', 'woocommerce' ),
                        'default'     => __( 'Grab', 'woocommerce' ),
                        'desc_tip'    => true,
                    ),
                    'tax_status' => array(
                        'title'   => __( 'Tình trạng thuế', 'woocommerce' ),
                        'type'    => 'select',
                        'class'   => 'wc-enhanced-select',
                        'default' => 'taxable',
                        'options' => array(
                            'taxable' => __( 'Tính thuế', 'woocommerce' ),
                            'none'    => __( 'Không tính thuế', 'woocommerce' ),
                        ),
                    ),
                    'cost' => array(
                        'title'       => __( 'Phí giao hàng', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'Nhập phí giao hàng cố định (VNĐ)', 'woocommerce' ),
                        'default'     => 35000,
                        'desc_tip'    => true,
                    ),
                );
            }

            public function calculate_shipping( $package = array() ) {
                $cost = $this->cost ? floatval( $this->cost ) : 35000;
                $rate = array(
                    'id'    => $this->get_rate_id(),
                    'label' => $this->title,
                    'cost'  => $cost,
                );
                $this->add_rate( $rate );
            }

            public function is_available( $package ) {
                return parent::is_available( $package );
            }
        }
    }

    add_filter('woocommerce_shipping_methods', 'add_custom_shipping_method');
    function add_custom_shipping_method($methods) {
        $partners = get_option('pancake_shipping_partners', []);
        foreach ($partners as $partner) {
            $name = strtolower($partner['name']);
            switch ($name) {
                case 'giao hàng nhanh':
                    $methods['ghn'] = 'WC_Shipping_GHN';
                    break;
                case 'grab express':
                    $methods['grab'] = 'WC_Shipping_Grab';
                    break;
                // Có thể thêm các case khác ở đây
            }
        }
        return $methods;
    }
} );

// add_filter('show_admin_bar', function($show) {
//     return current_user_can('administrator');
// });