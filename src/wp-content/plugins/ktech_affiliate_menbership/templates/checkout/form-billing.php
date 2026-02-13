<?php
/**
 * Checkout billing information form
 * Custom template from KTech Affiliate Membership plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

$db = new KTech_Address_DB();
$user_id = get_current_user_id();
$default_address = $db->get_default_address($user_id);
?>

<div class="woocommerce-billing-fields">
  <!-- Order Notes Section -->
  <div class="order-notes-section">
    <div class="order-notes-wrapper">
      <label for="order_comments" class="order-notes-label">
        <?php esc_html_e('Ghi chú', 'woocommerce'); ?>
      </label>
      <textarea 
        name="order_comments" 
        id="order_comments" 
        class="order-notes-textarea" 
        placeholder="<?php esc_attr_e('Ghi chú về đơn hàng, ví dụ: hướng dẫn đặc biệt cho việc giao hàng...', 'woocommerce'); ?>"
        rows="4"
      ><?php echo esc_textarea(WC()->checkout()->get_value('order_comments')); ?></textarea>
    </div>
  </div>
  <?php  if (!empty($user_id)) {  ?>
    <div class="kam-address-selected">
      <img class="kam-address-selected-location" src="/wp-content/uploads/2025/09/location.png" />
      <div class="kam-address-selected-content">
        <?php if (!empty($default_address)) { ?>
          <p><strong><?php echo esc_attr($default_address->name); ?></strong><span> (<?php echo esc_attr($default_address->phone); ?>)</span></p>
          <p><span><?php echo esc_attr($default_address->address . ', ' . $default_address->commune . ', ' . $default_address->district . ', ' . $default_address->city); ?></span></p>
        <?php } else { ?>
          <p><strong></strong><span></span></p>
          <p><span><?php echo esc_attr('Chưa thêm địa chỉ'); ?></span></p>
        <?php } ?>
      </div>
      <div class="kam-address-selected-action">
        <div class="kam-address-selected-edit">
          <img src="/wp-content/uploads/2025/08/pencil.png" />
        </div>
      </div>
    </div>
  <?php } else { ?>
    <div id="kam_save_address" class="form_address_check_for_guest">
      <div class="address-form__group">
        <label class="address-form__label">Tên người nhận</label>
        <input type="text" name="recipient_name" autocomplete="off" class="address-form__input" placeholder="Nhập tên người nhận" required>
      </div>
      <div class="address-form__group">
        <label class="address-form__label">Email</label>
        <input type="text" name="recipient_email" autocomplete="off" class="address-form__input" placeholder="Địa chỉ email" required>
      </div>
      <div class="address-form__group">
        <label class="address-form__label">Số điện thoại</label>
        <input type="text" name="phone" class="address-form__input" autocomplete="off" placeholder="Nhập số điện thoại" required>
      </div>
      <div class="address-form__group">
        <label class="address-form__label">Chọn tỉnh/thành phố</label>
        <select name="city" class="address-form__input" required>
            <option value="">Chọn tỉnh/thành phố</option>
            <?php global $vietnam_cities; ?>
            <?php foreach ($vietnam_cities as $city) { ?>
                <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
            <?php } ?>
        </select>
      </div>
      <div class="address-form__group">
          <label class="address-form__label">Chọn quận/huyện</label>
          <select name="district" class="address-form__input" required>
          <option value="">Chọn quận/huyện</option>
          </select>
      </div>
      <div class="address-form__group">
          <label class="address-form__label">Phường/Xã</label>
          <select name="commune" class="address-form__input" required>
          <option value="">Phường/Xã</option>
          </select>
      </div>
      <div class="address-form__group address-form__group--full">
          <label class="address-form__label">Địa chỉ</label>
          <input type="text" name="address" autocomplete="off" class="address-form__input" placeholder="Nhập địa chỉ cụ thể" required>
      </div>
    </div>
  <?php } ?>

  <!-- Default WooCommerce billing fields -->
  <div class="woocommerce-billing-fields__field-wrapper" style="display:none;" >
    <?php
      $fields = $checkout->get_checkout_fields('billing');
      
      foreach ($fields as $key => $field) {
        woocommerce_form_field($key, $field, $checkout->get_value($key));
      }
    ?>
  </div>
</div>
