<?php
/**
 * Checkout shipping information form
 * Custom template from KTech Affiliate Membership plugin
 * Hidden when unified address is selected
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="woocommerce-shipping-fields" style="display: none;">
    <!-- Shipping fields are hidden when unified address system is used -->
    <div class="woocommerce-shipping-fields__field-wrapper">
        <?php
        $fields = $checkout->get_checkout_fields('shipping');
        
        foreach ($fields as $key => $field) {
            woocommerce_form_field($key, $field, $checkout->get_value($key));
        }
        ?>
    </div>
</div>
