<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          8.1.0
 * @flatsome-version 3.17.7
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order row">

	<?php
	if ( $order ) :
		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>
			<div class="large-12 col order-failed">
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</p>
			</div>

		<?php else : ?>

		<div class="thankyou-order-header">
			<img src="/wp-content/uploads/2025/10/check.png" width="150" height="150" />
			<?php
			if ( $order ) {
				if ( $order->has_status( 'cancelled' ) || $order->has_status( 'failed' ) || $order->has_status( 'on-hold' ) ) {
					echo '<h1>' . esc_html__( 'Bạn đã đặt hàng thất bại!', 'woocommerce' ) . '</h1>';
				} else {
					echo '<h1>' . esc_html__( 'Bạn đã đặt hàng thành công!', 'woocommerce' ) . '</h1>';
				}
			}
			?>
		</div>

		<div class="thankyou-order-info">
			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
					<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e( 'Ngày đặt:', 'woocommerce' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>
			</ul>

			<div class="clear"></div>
		</div>

		<?php
			$billing_address = $order->get_billing_address_1();
			if ($order->get_billing_address_2()) {
					$billing_address .= ', ' . $order->get_billing_address_2();
			}
			if ($order->get_billing_city()) {
					$billing_address .= ', ' . $order->get_billing_city();
			}
			if ($order->get_billing_state()) {
					$billing_address .= ', ' . $order->get_billing_state();
			}
			if ($order->get_billing_postcode()) {
					$billing_address .= ', ' . $order->get_billing_postcode();
			}
			$billing_phone = $order->get_billing_phone();
		?>

		<div class="kam-address-selected thankyou-order-address">
			<img class="kam-address-selected-location" src="/wp-content/uploads/2025/09/location.png" />
			<div class="kam-address-selected-content">
				<p><strong><?php echo $order->get_billing_first_name(); ?></strong><span> (<?php echo $billing_phone; ?>)</span></p>
				<p><span><?php echo $billing_address; ?></span></p>
			</div>
			<div class="kam-address-selected-action">
				<div class="kam-address-selected-edit"></div>
			</div>
		</div>

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
				foreach ( $order->get_items() as $item_id => $item ) {
					$product   = $item->get_product();
					$quantity  = $item->get_quantity();
					$product_id = $product->get_id();
					$product_price = get_post_meta( $product_id, '_price', true );
					$price_origin_numeric = $product_price * $quantity;
					$price     = wc_price( $item->get_subtotal() );
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
					if (!empty($price_origin) && $price_origin_numeric > $price_number) {
						echo '<del class="price-reduced">' . $price_origin . '</del><br />';
					}
					echo $price;
					echo '</span>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
				}
			?>
		</div>
		
		<?php
			$total_reduced = $order->get_meta('_total_reduced');
		?>
		<div class="checkout-order-review thankyou-order-review">
			<div class="checkout-order-review-row">
				<span>Số lượng đặt hàng</span>
				<span><?php echo $order->get_item_count(); ?></span>
			</div>
			<?php
				$total_reduced = floatval( $order->get_meta('total_amount_reduced') );
				// foreach ( WC()->cart->get_cart() as $cart_item ) {
				// 	if ( isset( $cart_item['amount_reduced'] ) ) {
				// 		$total_reduced += $cart_item['amount_reduced'] * $cart_item['quantity'];
				// 	}
				// }

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
				<span>Phí vận chuyển sẽ được thông báo sau thông qua zalo của quý khách</span>
			</div>
			<div class="checkout-order-review-row-total">
				<span>Tổng tiền</span>
				<span><?php echo wc_price( $order->get_total() ); ?></span>
			</div>
		</div>

		<?php
			// Get products from current order
			$order_product_ids = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$order_product_ids[] = $item->get_product_id();
			}

			// Get related products based on cart items, excluding current order products
			$related_products = array();
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => 8,
				'post__not_in'   => $order_product_ids,
				'orderby'        => 'rand',
			);

			// Get categories from order products
			$category_ids = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				$terms = get_the_terms( $product->get_id(), 'product_cat' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$category_ids[] = $term->term_id;
					}
				}
			}

			// If we have categories, get products from those categories
			if ( ! empty( $category_ids ) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array_unique( $category_ids ),
					),
				);
			}

			$related_query = new WP_Query( $args );
			if ( $related_query->have_posts() ) {
				while ( $related_query->have_posts() ) {
					$related_query->the_post();
					$related_products[] = wc_get_product( get_the_ID() );
				}
				wp_reset_postdata();
			}

			$type             = get_theme_mod( 'related_products', 'slider' );
			$repeater_classes = array();

			if ( $type == 'hidden' ) return;
			if ( $type == 'grid' ) $type = 'row';

			if ( get_theme_mod('category_force_image_height' ) ) $repeater_classes[] = 'has-equal-box-heights';
			if ( get_theme_mod('equalize_product_box' ) ) $repeater_classes[]        = 'equalize-box';

			$repeater['type']         = $type;
			$repeater['columns']      = get_theme_mod( 'related_products_pr_row', 4 );
			$repeater['columns__md']  = get_theme_mod( 'related_products_pr_row_tablet', 3 );
			$repeater['columns__sm']  = get_theme_mod( 'related_products_pr_row_mobile', 2 );
			$repeater['class']        = implode( ' ', $repeater_classes );
			$repeater['slider_style'] = 'reveal';
			$repeater['row_spacing']  = 'small';

			if ( $related_products ) : ?>
				<div class="single-product">
					<div id="main">
					<div class="related related-products-wrapper product-section">
						
						<?php
						$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Sản phẩm liên quan', 'woocommerce' ) );

						if ( $heading ) :
							?>
							<h3 class="product-section-title container-width product-section-title-related pt-half pb-half uppercase">
								<?php echo esc_html( $heading ); ?>
							</h3>
						<?php endif; ?>

						<?php get_flatsome_repeater_start( $repeater ); ?>

						<?php foreach ( $related_products as $related_product ) :
							$post_object = get_post( $related_product->get_id() );

							setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

							wc_get_template_part( 'content', 'product' );
						endforeach;
						?>

						<?php get_flatsome_repeater_end( $repeater ); ?>

					</div>
				</div>
				</div>
			<?php
			endif;

			wp_reset_postdata();

		?>

		<!-- <div class="large-7 col"> -->
			<?php //do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php //do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		<!-- </div> -->

		<?php endif; ?>

	<?php endif; ?>

</div>
