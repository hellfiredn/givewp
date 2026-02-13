<?php
/**
 * Category layout with no sidebar.
 *
 * @package          Flatsome/WooCommerce/Templates
 * @flatsome-version 3.18.7
 */

?>

<section class="header-category-txt">
    <?php
		$image_url = '';
    if (is_tax('product_cat')) {
        $term = get_queried_object();
        $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        // $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : get_template_directory_uri() . '/img/hah.png';
        $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
        $category_name = $term->name;
    } else {
        // $image_url = get_template_directory_uri() . '/img/hah.png';
        $category_name = '';
    }
    ?>

    <?php if (is_tax('product_cat') && !empty($image_url)) : ?>
        <img src="<?php echo esc_url($image_url); ?>" alt="">
    <?php endif; ?>

    <div class="product-txt">
        <div class="container">
            <?php if (!is_shop()) : ?>
                <div class="head-txt">Sản phẩm</div>
            <?php endif; ?>

            <div class="sub-txt">
                <?php
                if (is_shop()) {
                    echo 'Sản phẩm';
                } elseif (is_tax('product_cat')) {
                    echo esc_html($category_name);
                }
                ?>
            </div>
        </div>
    </div>
</section>



<div class="row category-page-row">

		<div class="col large-12">
		<?php
		do_action( 'flatsome_products_before' );

		/**
		* Hook: woocommerce_before_main_content.
		*
		* @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		* @hooked woocommerce_breadcrumb - 20 (FL removed)
		* @hooked WC_Structured_Data::generate_website_data() - 30
		*/
		do_action( 'woocommerce_before_main_content' );

		if ( fl_woocommerce_version_check( '8.8.0' ) ) {
			/**
			 * Hook: woocommerce_shop_loop_header.
			 *
			 * @since 8.8.0
			 *
			 * @hooked woocommerce_product_taxonomy_archive_header - 10
			 */
			do_action( 'woocommerce_shop_loop_header' );
		} else {
			do_action( 'woocommerce_archive_description' );
		}

		if ( woocommerce_product_loop() ) {

			/**
			 * Hook: woocommerce_before_shop_loop.
			 *
			 * @hooked wc_print_notices - 10
			 * @hooked woocommerce_result_count - 20 (FL removed)
			 * @hooked woocommerce_catalog_ordering - 30 (FL removed)
			 */
			do_action( 'woocommerce_before_shop_loop' );

			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();

					/**
					 * Hook: woocommerce_shop_loop.
					 *
					 * @hooked WC_Structured_Data::generate_product_data() - 10
					 */
					do_action( 'woocommerce_shop_loop' );

					wc_get_template_part( 'content', 'product' );
				}
			}

			woocommerce_product_loop_end();

			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			 */
			do_action( 'woocommerce_after_shop_loop' );
		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}
		?>

		<?php
		/**
		 * Hook: flatsome_products_after.
		 *
		 * @hooked flatsome_products_footer_content - 10
		 */
		do_action( 'flatsome_products_after' );
		/**
		 * Hook: woocommerce_after_main_content.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
		?>

		</div>
</div>
