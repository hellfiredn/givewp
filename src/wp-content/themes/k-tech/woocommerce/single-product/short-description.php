<?php
/**
 * Single product short description
 *
 * @author           Automattic
 * @package          WooCommerce/Templates
 * @version          3.3.0
 * @flatsome-version 3.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}

?>
<div class="product-short-description<?php echo is_user_logged_in() ? '' : ' not-logged-in'; ?>">
	<?php echo $short_description; // WPCS: XSS ok. ?>
	<?php if ( is_user_logged_in() ) : ?>
		<?php
			$product_id = get_the_ID();
		?>
		<div class="wlfmc-add-to-wishlist wlfmc-add-to-wishlist-<?php echo esc_attr($product_id); ?> wlfmc-single-btn wlfmc_position_image_top_left wlfmc-btn-type-icon wlfmc-top-of-image image_top_left show-remove-after-add" data-remove-url="?remove_from_wishlist=#product_id" data-add-url="?add_to_wishlist=#product_id" data-enable-outofstock="1" data-popup-id="">
			<!-- ADD TO WISHLIST -->
			<div class="wlfmc-add-button  wlfmc-addtowishlist  wlfmc-tooltip wlfmc-tooltip-top" data-tooltip-text="Add To Wishlist" data-tooltip-type="default">
				<a href="#" rel="nofollow" aria-label="Add to wishlist" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-type="simple" data-parent-product-id="<?php echo esc_attr($product_id); ?>" data-e-disable-page-transition="" class="wlfmc_add_to_wishlist wlfmc-custom-btn alt ">
				<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
					<path d="M17.75,1c-2.504,0-4.777,1.851-5.75,4.354-.973-2.504-3.246-4.354-5.75-4.354C2.804,1,0,3.804,0,7.25c0,6.76,9.754,14.07,11.709,15.466l.291,.208,.291-.208c1.956-1.396,11.709-8.707,11.709-15.466,0-3.446-2.804-6.25-6.25-6.25Zm-5.75,20.693C6.859,17.958,1,12.022,1,7.25,1,4.355,3.355,2,6.25,2c2.748,0,5.25,2.86,5.25,6h1c0-3.14,2.502-6,5.25-6,2.895,0,5.25,2.355,5.25,5.25,0,4.772-5.859,10.708-11,14.443Z"/>
				</svg>
				</a>
			</div>
			<!-- REMOVE FROM WISHLIST -->
			<div class="wlfmc-add-button  wlfmc-removefromwishlist  wlfmc-tooltip wlfmc-tooltip-top" data-tooltip-type="default" data-tooltip-text="Remove From Wishlist">
				<a href="#" rel="nofollow" data-wishlist-id="" data-item-id="" aria-label="Remove from wishlist" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-type="simple" data-parent-product-id="<?php echo esc_attr($product_id); ?>" data-e-disable-page-transition="" class="wlfmc_delete_item  wlfmc-custom-btn alt ">
					<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 21.92">
						<path d="M17.75,0c-2.5,0-4.78,1.85-5.75,4.35C11.03,1.85,8.75,0,6.25,0,2.8,0,0,2.8,0,6.25c0,6.76,9.75,14.07,11.71,15.47l.29.21.29-.21c1.96-1.4,11.71-8.71,11.71-15.47,0-3.45-2.8-6.25-6.25-6.25Z"/>
					</svg>
				</a>
			</div>
		</div>
	<?php endif; ?>
</div>
