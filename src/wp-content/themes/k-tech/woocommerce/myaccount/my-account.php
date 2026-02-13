<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>

<!-- Popup xác nhận hủy đơn -->
<div id="cancel-order-modal">
  <div class="cancel-order-modal-inner">
    <h2>Xác nhận hủy đơn hàng</h2>
    <p>Bạn có chắc chắn muốn hủy đơn hàng này?</p>
    <button type="button" id="confirm-cancel-order" class="button button-primary">Xác nhận</button>
    <button type="button" id="cancel-cancel-order" class="button">Huỷ</button>
  </div>
</div>
