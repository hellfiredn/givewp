<?php
/**
 * Checkout shipping information form
 * Override WooCommerce checkout form to show saved addresses
 */

if (!defined('ABSPATH')) {
    exit;
}

$db = new KTech_Address_DB();
$user_id = get_current_user_id();
$saved_addresses = $db->get_by_user($user_id);
?>

<div class="woocommerce-shipping-fields">
    <?php if ($user_id > 0 && !empty($saved_addresses)) : ?>
        <!-- Saved Addresses Section for Shipping -->
        <div class="saved-addresses-section">
            <h3><?php esc_html_e('Chọn địa chỉ giao hàng', 'woocommerce'); ?></h3>
            
            <div class="saved-addresses-list">
                <div class="address-option">
                    <input type="radio" id="new_shipping_address" name="shipping_address_type" value="new" checked>
                    <label for="new_shipping_address"><?php esc_html_e('Nhập địa chỉ mới', 'woocommerce'); ?></label>
                </div>
                
                <div class="address-option">
                    <input type="radio" id="same_as_billing" name="shipping_address_type" value="billing">
                    <label for="same_as_billing"><?php esc_html_e('Giống địa chỉ thanh toán', 'woocommerce'); ?></label>
                </div>
                
                <?php foreach ($saved_addresses as $address) : ?>
                    <div class="address-option">
                        <input type="radio" 
                               id="saved_shipping_address_<?php echo $address->id; ?>" 
                               name="shipping_address_type" 
                               value="saved" 
                               data-address-id="<?php echo $address->id; ?>"
                               data-name="<?php echo esc_attr($address->name); ?>"
                               data-phone="<?php echo esc_attr($address->phone); ?>"
                               data-city="<?php echo esc_attr($address->city); ?>"
                               data-district="<?php echo esc_attr($address->district); ?>"
                               data-commune="<?php echo esc_attr($address->commune); ?>"
                               data-address="<?php echo esc_attr($address->address); ?>">
                        <label for="saved_shipping_address_<?php echo $address->id; ?>">
                            <strong><?php echo esc_html($address->name); ?></strong> - <?php echo esc_html($address->phone); ?><br>
                            <small><?php echo esc_html($address->address . ', ' . $address->district . ', ' . $address->city); ?></small>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="shipping-fields-toggle">
    <?php endif; ?>
    
    <!-- Default WooCommerce shipping fields -->
    <div class="woocommerce-shipping-fields__field-wrapper">
        <?php
        $fields = $checkout->get_checkout_fields('shipping');
        
        foreach ($fields as $key => $field) {
            woocommerce_form_field($key, $field, $checkout->get_value($key));
        }
        ?>
    </div>
    
    <?php if ($user_id > 0 && !empty($saved_addresses)) : ?>
        </div> <!-- .shipping-fields-toggle -->
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle between saved address and new address form for shipping
    $('input[name="shipping_address_type"]').on('change', function() {
        if ($(this).val() === 'saved') {
            // Hide shipping form fields
            $('.woocommerce-shipping-fields__field-wrapper').hide();
            
            // Get selected address data
            var selectedAddress = $('input[name="shipping_address_type"]:checked');
            
            // Fill hidden fields with saved address data
            $('#shipping_first_name').val(selectedAddress.data('name'));
            $('#shipping_phone').val(selectedAddress.data('phone'));
            $('#shipping_city').val(selectedAddress.data('city'));
            $('#shipping_state').val(selectedAddress.data('district'));
            $('#shipping_address_1').val(selectedAddress.data('address'));
            
            // Add hidden field for address ID
            if ($('input[name="shipping_address_id"]').length === 0) {
                $('form.checkout').append('<input type="hidden" name="shipping_address_id" value="' + selectedAddress.data('address-id') + '">');
            } else {
                $('input[name="shipping_address_id"]').val(selectedAddress.data('address-id'));
            }
            
        } else if ($(this).val() === 'billing') {
            // Hide shipping form fields and copy from billing
            $('.woocommerce-shipping-fields__field-wrapper').hide();
            
            // Copy billing data to shipping
            $('#shipping_first_name').val($('#billing_first_name').val());
            $('#shipping_phone').val($('#billing_phone').val());
            $('#shipping_city').val($('#billing_city').val());
            $('#shipping_state').val($('#billing_state').val());
            $('#shipping_address_1').val($('#billing_address_1').val());
            
            // Remove shipping address ID field
            $('input[name="shipping_address_id"]').remove();
            
        } else {
            // Show shipping form fields
            $('.woocommerce-shipping-fields__field-wrapper').show();
            
            // Clear fields
            $('#shipping_first_name, #shipping_phone, #shipping_city, #shipping_state, #shipping_address_1').val('');
            
            // Remove address ID field
            $('input[name="shipping_address_id"]').remove();
        }
    });
    
    // Update fields when different saved address is selected
    $('input[name="shipping_address_type"][value="saved"]').on('change', function() {
        var selectedAddress = $(this);
        $('#shipping_first_name').val(selectedAddress.data('name'));
        $('#shipping_phone').val(selectedAddress.data('phone'));
        $('#shipping_city').val(selectedAddress.data('city'));
        $('#shipping_state').val(selectedAddress.data('district'));
        $('#shipping_address_1').val(selectedAddress.data('address'));
        
        // Update address ID
        $('input[name="shipping_address_id"]').val(selectedAddress.data('address-id'));
    });
});
</script>
