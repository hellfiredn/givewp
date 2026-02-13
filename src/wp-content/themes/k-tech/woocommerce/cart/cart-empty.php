<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @package          WooCommerce\Templates
 * @version          7.0.1
 * @flatsome-version 3.16.2
 */

defined( 'ABSPATH' ) || exit; ?>
<div class="text-center pt pb">
	<?php
	/**
	 * @hooked wc_empty_cart_message - 10
	 */
	do_action( 'woocommerce_cart_is_empty' );

	$related_ids = get_posts( array(
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'fields'         => 'ids',
	) );

	if ( ! empty( $related_ids ) ) {
			$related_products = wc_get_products( array(
					'include' => $related_ids,
			) );

			echo '<div class="related related-products-wrapper related-product-shortcode">';
			echo '<h3 class="product-section-title container-width product-section-title-related pt-half pb-half">';
			echo esc_html__( 'Sản phẩm liên quan', 'woocommerce' );
			echo '</h3>';

			echo '<ul class="products related-products">';
			foreach ( $related_products as $related_product ) {
					$post_object = get_post( $related_product->get_id() );
					setup_postdata( $GLOBALS['post'] = $post_object );

					wc_get_template_part( 'content', 'product' );
			}
			echo '</ul>';
			echo '</div>';
			wp_reset_postdata();
	}
?>
</div>
