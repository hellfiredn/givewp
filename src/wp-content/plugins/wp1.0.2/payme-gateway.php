<?php

/*
 * Plugin Name: WooCommerce PayME Payment Gateway
 * Plugin URI: https://payme.vn
 * Description: Take PayME payments on your store provided by PayME
 * Author: PayME Corp.
 * Author URI: http://payme.vn
 * Version: 1.0.5
 */

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
include_once 'GApiService.php';

add_filter('woocommerce_payment_gateways', 'payme_add_gateway_class');

function payme_add_gateway_class($gateways) {
    $gateways[] = 'WC_PayME_Gateway'; // your class name is here
    return $gateways;
}

add_action('plugins_loaded', 'payme_init_gateway_class');

function payme_init_gateway_class() {

    class WC_PayME_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            $payment_url = 'https://gapi.payme.vn';
            $test_payment_url = 'https://sbx-gapi.payme.vn';

            $this->transaction_prefix = 'woo_plg_';

            $this->id = 'payme_payments'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'PayME Gateway';
            $this->method_description = 'Make a payment via PayME'; // will be displayed on the options page
            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            //  $this->enabled = $this->get_option('enabled');

            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->payment_url = $this->testmode ? $test_payment_url : $payment_url;

            $this->xAPIClient = $this->testmode ? $this->get_option('test_xAPIClient') : $this->get_option('xAPIClient');
            $this->secret_key = $this->testmode ? $this->get_option('test_secret_key') : $this->get_option('secret_key');

            $this->convert_rate = $this->get_option('convert_rate');

            $this->payment_methods = $this->getPaymentMethods();
            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // You can also register a webhook here
            add_action('woocommerce_api_paymehook', array($this, 'webhook'));

            add_action('woocommerce_thankyou', array($this, 'thankyou_action_callback'), 10, 1);
        }

        function thankyou_action_callback($order_id) {
            // Get an instance of the WC_Order Object
            $order = wc_get_order($order_id);

            if ($order->get_status() === 'on-hold' || $order->get_status() === 'failed') {
                $partnerTransaction = $order->get_meta('partner_transaction');
                $paymentInfo = $this->getPaymentResult($partnerTransaction);

                $order->update_meta_data('payme_callback_payload', json_encode($paymentInfo));
                if ($paymentInfo !== null && $paymentInfo['state'] === "SUCCEEDED") {
                    $order->update_status('processing', 'Order has bees paid at: ', $paymentInfo['updatedAt']); // order note is optional, if you want to  add a note to order
                    $order->reduce_order_stock();
                } else {
                    $order->update_status('failed');
                    if (!empty($_GET['isCancelled']) && $_GET['isCancelled']) {
                        header('Location: ' . $this->get_return_url($order));
                    }
                }
            }
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Payment By PayME',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Pay with your Account via PayME payment gateway.',
                    'desc_tip' => true,
                ),
                'convert_rate' => array(
                    'title' => 'Convert Rate to VND',
                    'type' => 'number',
                    'description' => 'The ratio from your currency to VND (E.g. 1 USD -> 23000 VND).',
                    'default' => 1,
                ),
                'testmode' => array(
                    'title' => 'Test mode',
                    'label' => 'Enable Test Mode',
                    'type' => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default' => 'yes',
                    'desc_tip' => true,
                ),
                'test_xAPIClient' => array(
                    'title' => 'Test x-api-client',
                    'type' => 'number',
                    'description' => 'Test x-api-client number (with test mode enable)'
                ),
                'test_secret_key' => array(
                    'title' => 'Test Secret Key',
                    'type' => 'text',
                    'description' => 'Test Secret key (with test mode enable)'
                ),
                'xAPIClient' => array(
                    'title' => 'x-api-client',
                    'type' => 'number',
                    'description' => 'x-api-client number'
                ),
                'secret_key' => array(
                    'title' => 'Secret Key',
                    'type' => 'text',
                    'description' => 'Secret key'
                )
            );
        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields() {
            // ok, let's display some description before the payment form
            if ($this->description) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ($this->testmode) {
                    $this->description .= ' <b style="color: red">TEST MODE ENABLED.</b>';
                    $this->description = trim($this->description);
                }
                // display the description with <p> tags etc.
                echo wpautop(wp_kses_post($this->description));
            }

            // I will echo() the form, but you can close PHP tags and print it directly in HTML
            echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

            $payment_items = [];
            if ($this->payment_methods) {
                foreach ($this->payment_methods as $info) {
                    if ($info['isActive'] === true && $info['payCode']) {
                        array_push($payment_items,
                                "<div class='form-row form-row-wide' style='padding: 0 1rem'>
                                    <label>
                                      <input type='radio' name='payCode' value='{$info['payMethod']}'>
                                      {$info['title']}
                                    </label>        
                                  </div>");
                    }
                }
            }

            // Add this action hook if you want your custom payment gateway to support it
            do_action('woocommerce_credit_card_form_start', $this->id);

            $option_html = join(' ', $payment_items);
            echo "
                <div class='payme-payment-method'>
                  {$option_html} 
                  <div class='clear'>
                </div>
              </div>";

            do_action('woocommerce_credit_card_form_end', $this->id);

            echo '<div class="clear"></div></fieldset>';
        }

        /**
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
        public function payment_scripts() {
            
        }

        /**
         * We're processing the payments here, everything about it is in Step 5
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            $billing_info = array(
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'address' => $order->get_billing_address_1(),
                'locality' => $order->get_billing_city(),
                'administrativeArea' => $order->get_billing_state(),
                'postalCode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            );

            $partnerTransaction = $this->transaction_prefix . strval($order_id) . '_' . rand(1, 9999);
            $order->update_meta_data("partner_transaction", $partnerTransaction);

            $req_body = array(
                'partnerTransaction' => $partnerTransaction,
                'amount' => floatval($order->get_total()) * $this->convert_rate,
                'desc' => 'Thanh toán mua hàng',
                'payMethod' => $_POST['payCode'],
                'title' => 'Thanh toán từ Woocommerce',
                'ipnUrl' => home_url('/wc-api/paymehook'),
                'billingInfo' => $billing_info,
                'currency' => 'VND',
                'expiryAt' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'redirectUrl' => $this->get_return_url($order),
                'failedUrl' => $this->get_return_url($order) . '&isCancelled=true',
                'redirectTime' => 0,
            );

            $gapiService = new GApiService($this->payment_url, $this->xAPIClient, $this->secret_key);
            $res = $gapiService->Request("/payment/web", "POST", $req_body);

            if ($res) {
                $res_body = json_decode($res, true);
                if ($res_body['code'] == 105000) {
                    $order->update_status('on-hold', 'waitting for payment: ' . json_encode($req_body) . ' -- response: ' . $res);

                    $encode_data = base64_encode(urlencode($res_body['data']['form']));
                    return array(
                        'result' => 'success',
                        'redirect' => $res_body['data']['url']
                    );
                } else {
                    wc_add_notice("Lỗi thanh toán", 'error');
                    return;
                }
            } else {
                wc_add_notice('Lỗi kết nối', 'error');
                return;
            }
        }

        public function validate_fields() {
            return parent::validate_fields();
        }

        public function webhook() {
            $rawData = file_get_contents('php://input');

            $data = json_decode($rawData);
            if ($data) {
                $orderId = explode('_', $data->partnerTransaction)[2];

                $order = wc_get_order($orderId);
                if ($order) {
                    if ($order->get_status() == 'on-hold') {
                        $order->update_meta_data('payme_callback_payload', $rawData);
                        if ($data->state === "SUCCEEDED") {
                            $order->update_status('processing', 'Order has bees paid at: ', $data->updatedAt); // order note is optional, if you want to  add a note to order
                            $order->reduce_order_stock();
                            echo json_encode(array(
                                "message" => "Success"
                            ));
                        } else {
                            $order->update_status('failed');
                            echo json_encode(array(
                                "message" => "Failed"
                            ));
                        }
                    } else {
                        echo json_encode(array(
                            "message" => "Transaction has been processed"
                        ));
                    }
                }
            }
            exit;
        }

        private function getPaymentResult($partnerTransaction) {
            $req_body = array(
                "partnerTransaction" => $partnerTransaction
            );

            $gapiService = new GApiService($this->payment_url, $this->xAPIClient, $this->secret_key);
            $res = $gapiService->Request("/payment/query", "POST", $req_body);

            if ($res) {
                $res_body = json_decode($res, true);
                if ($res_body['code'] == 105002) {
                    return $res_body['data'];
                }
            }
            return null;
        }

        private function getPaymentMethods() {
            $curr_locale = get_locale();
            if ($curr_locale === 'en_US') {
                $req_body = array("language" => "en");
            } else {
                $req_body = array("language" => "vi");
            }


            $gapiService = new GApiService($this->payment_url, $this->xAPIClient, $this->secret_key);
            $res = $gapiService->Request("/payment/method", "POST", $req_body);

            if ($res) {
                $res_body = json_decode($res, true);
                if ($res_body['code'] == 129002) {
                    return $res_body['data'];
                }
            }
            return null;
        }

    }

}
