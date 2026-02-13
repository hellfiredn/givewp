<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          9.4.0
 * @flatsome-version 3.19.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_classes = array();
$row_classes     = array();
$main_classes    = array();
$sidebar_classes = array();

$layout = get_theme_mod( 'checkout_layout', '' );

if ( ! $layout ) {
	$sidebar_classes[] = 'has-border';
}

if ( $layout == 'simple' ) {
	$sidebar_classes[] = 'is-well';
}

$wrapper_classes = implode( ' ', $wrapper_classes );
$row_classes     = implode( ' ', $row_classes );
$main_classes    = implode( ' ', $main_classes );
$sidebar_classes = implode( ' ', $sidebar_classes );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

// Social login.
if ( get_theme_mod( 'facebook_login_checkout', 0 ) && get_option( 'woocommerce_enable_myaccount_registration' ) == 'yes' && ! is_user_logged_in() ) {
	wc_get_template( 'checkout/social-login.php' );
}
?>
<div class="checkout-cart-summary">
	<?php
		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);
		$is_reduce_price = false;
		$roles_setting = get_option('ktech_account_roles_setting', []);
    if ($role != 'master' && $role != 'pharmer_seller' && $role != 'pharmer') {
        if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['price_type_avaliable']) {
            $price_type_avaliable = $roles_setting[$role]['price_type_avaliable'];
            if ($price_type_avaliable == 'pharmer') {
                $role = 'pharmer';
            }
            if ($price_type_avaliable == 'master') {
                $role = 'master';
            }
        }
    }
		if ($role === 'master' || $role === 'pharmer' || $role === 'pharmer_seller') {
			$is_reduce_price = true;
		}
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product   = $cart_item['data'];
			$quantity  = $cart_item['quantity'];
			$product_id = $product->get_id();
			$product_price = get_post_meta( $product_id, '_price', true );
			$price_origin_numeric = floatval($product_price) * intval($quantity);
			$price     = WC()->cart->get_product_subtotal( $product, $quantity, false );
			$price_number     = floatval(str_replace('.', '', preg_replace('/[^\d.]/', '', $price)));

			$price_origin = wc_price($price_origin_numeric);

			$thumbnail = $product->get_image( 'woocommerce_thumbnail' );
			$name      = $product->get_name();
			$terms = get_the_terms( $product->get_id(), 'product_cat' );
			$category_name = '';
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$category_name = $terms[0]->name;
			}

			echo '<div class="cart-item">';
			echo '<div class="cart-item-thumbnail">' . $thumbnail . '</div>';
			echo '<div class="cart-item-info">';
			echo '<div class="cart-item-name">
				<span>' . esc_html( $category_name ) . '</span>
				<h3>' . esc_html( $name ) . '</h3>
				<p>' . apply_filters('woocommerce_short_description', $product->get_short_description()) . '</p>
			</div>';
			echo '<div class="cart-item-quantity"><span>Số lượng</span><span>' . $quantity . '</span></div>';
			echo '<div class="cart-item-price">';
			echo '<span>Giá hiện tại</span>';
			echo '<span>';
			if (!empty($price) && $price_origin_numeric > $price_number) {
				echo '<del class="price-reduced">' . $price_origin . '</del><br />';
			}
			echo $price;
			echo '</span>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		do_action( 'woocommerce_before_checkout_form', $checkout );
	?>
</div>
<form name="checkout" method="post" class="checkout woocommerce-checkout <?php echo esc_attr( $wrapper_classes ); ?>" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
	<div class="row pt-0 <?php echo esc_attr( $row_classes ); ?>">
		<div class="large-7 col  <?php echo esc_attr( $main_classes ); ?>">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div id="customer_details">
					<div class="clear">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

					<div class="clear">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php endif; ?>

		</div>

		<div class="large-5 col">
			<?php flatsome_sticky_column_open( 'checkout_sticky_sidebar' ); ?>

					<div class="col-inner <?php echo esc_attr( $sidebar_classes ); ?>">
						<div class="checkout-sidebar sm-touch-scroll">

							<div class="checkout-order-review">
								<p>Phương thức thanh toán</p>
								<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
									<ul class="wc_payment_methods payment_methods methods">
										<?php
										if ( WC()->cart->needs_payment() ) {
											$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
											WC()->payment_gateways()->set_current_gateway( $available_gateways );
										} else {
											$available_gateways = array();
										}
										if ( ! empty( $available_gateways ) ) {
											foreach ( $available_gateways as $gateway ) {
												wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
											}
										} else {
											echo '<li>';
											wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
											echo '</li>';
										}
										?>
									</ul>
								<?php endif; ?>
								<p>Phương thức vận chuyển</p>
								<div class="checkout-shipping-methods">
									<?php
										$shipping_methods = WC()->shipping->get_packages();
										if ( ! empty( $shipping_methods ) ) {
											foreach ( $shipping_methods as $package_index => $package ) {
												$available_methods = $package['rates'];
												$chosen_method = isset( WC()->session->get( 'chosen_shipping_methods' )[ $package_index ] ) ? WC()->session->get( 'chosen_shipping_methods' )[ $package_index ] : '';
												if ( ! empty( $available_methods ) ) {
													echo '<ul class="wc_payment_methods payment_methods methods">';
													foreach ( $available_methods as $method ) {
														$checked = checked( $chosen_method, $method->id, false );
														echo '<li>';
														printf(
															'<input type="radio" name="shipping_method[%d]" value="%s" id="%s" %s /><label for="%s">%s</label>',
															$package_index,
															esc_attr( $method->id ),
															$method->id.''.$package_index,
															$checked,
															$method->id.''.$package_index,
															esc_html( $method->get_label() )
														);
														echo '</li>';
													}
													echo '</ul>';
												} else {
													echo '<p>' . esc_html__( 'No shipping methods available. Please check your address or contact us for help.', 'woocommerce' ) . '</p>';
												}
											}
										} else {
											echo '<p>' . esc_html__( 'No shipping packages found.', 'woocommerce' ) . '</p>';
										}
									?>
								</div>
								<div class="checkout-order-review-row">
									<span>Số lượng đặt hàng</span>
									<span><?php echo WC()->cart->get_cart_contents_count(); ?></span>
								</div>
								<?php
										$total_reduced = 0;
										foreach ( WC()->cart->get_cart() as $cart_item ) {
											if ( isset( $cart_item['amount_reduced'] ) ) {
												$total_reduced += $cart_item['amount_reduced'] * $cart_item['quantity'];
											}
										}

										// Tính tổng giảm giá từ coupon
										$coupon_discount = 0;
										if ( WC()->cart ) {
											$coupon_discount = WC()->cart->get_discount_total();
										}

										$total_discount = $total_reduced + $coupon_discount;

										if ( $total_discount > 0 ) {
											?>
												<div class="checkout-order-review-row">
													<span>Giảm giá</span>
													<span>
														<?php echo wc_price( $total_discount ); ?>
													</span>
												</div>
											<?php
										}
								?>
								<div class="checkout-order-review-row">
									<span>Phí vận chuyển</span>
									<span>
										Phí vận chuyển sẽ được thông báo sau thông qua zalo của quý khách
									</span>
								</div>
								<div class="checkout-order-review-row">
									<span>Tạm tính</span>
									<span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
								</div>
								<div class="checkout-order-review-row-total">
									<span>Tổng tiền</span>
									<span><?php echo WC()->cart->get_cart_total(); ?></span>
								</div>
							</div>
							<?php
								$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
							?>

							<!-- Di chuyển popup và checkbox xuất hóa đơn xuống đây -->
							<div class="checkout-invoice-box mb-24">
								<label class="invoice-label flex items-center gap-8 fw-600">
									<input type="checkbox" id="require_invoice" name="require_invoice" value="1" class="invoice-checkbox" />
									Yêu cầu xuất hóa đơn
								</label>
							</div>
							<div id="invoice_info_popup" class="invoice-popup flex-center">
								<div class="invoice-popup-content">
									<button type="button" id="close_invoice_popup" class="invoice-popup-close">&times;</button>
									<h3 class="invoice-popup-title">Thông tin xuất hóa đơn</h3>
									<div class="invoice-field mb-10">
										<label for="invoice_company" class="invoice-label">Tên công ty / cá nhân *</label>
										<input type="text" name="invoice_company" id="invoice_company" class="input-text invoice-input" />
									</div>
									<div class="invoice-field mb-10">
										<label for="invoice_tax" class="invoice-label">Mã số thuế *</label>
										<input type="text" name="invoice_tax" id="invoice_tax" class="input-text invoice-input" />
									</div>
									<div class="invoice-field mb-10">
										<label for="invoice_address" class="invoice-label">Địa chỉ *</label>
										<input type="text" name="invoice_address" id="invoice_address" class="input-text invoice-input" />
									</div>
									<div class="invoice-field mb-10">
										<label for="invoice_email" class="invoice-label">Email nhận hóa đơn *</label>
										<input type="email" name="invoice_email" id="invoice_email" class="input-text invoice-input" />
									</div>
									<button type="button" id="done_invoice_popup" class="invoice-popup-done">Xong</button>
								</div>
							</div>
							<div class="form-row place-order">
								<noscript>
									<?php
									/* translators: $1 and $2 opening and closing emphasis tags respectively */
									printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
									?>
									<br/><button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
								</noscript>

								<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

								<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

								<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

								<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
							</div>

							<?php //do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

							<?php //do_action( 'woocommerce_checkout_before_order_review' ); ?>

							<!-- <div id="order_review" class="woocommerce-checkout-review-order"> -->
								<?php //do_action( 'woocommerce_checkout_order_review' ); ?>
							<!-- </div> -->

							<?php //do_action( 'woocommerce_checkout_after_order_review' ); ?>
						</div>
					</div>

			<?php flatsome_sticky_column_close( 'checkout_sticky_sidebar' ); ?>
		</div>

	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
