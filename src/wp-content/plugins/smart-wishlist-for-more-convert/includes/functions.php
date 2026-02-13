<?php
/**
 * Smart Wishlist Functions
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/* === TESTER FUNCTIONS === */

if ( ! function_exists( 'wlfmc_is_wishlist' ) ) {
	/**
	 * Check if we're printing wishlist shortcode
	 *
	 * @return bool
	 */
	function wlfmc_is_wishlist() {
		global $wlfmc_is_wishlist;

		return $wlfmc_is_wishlist;
	}
}

if ( ! function_exists( 'wlfmc_is_wishlist_page' ) ) {
	/**
	 * Check if current page is wishlist
	 *
	 * @return bool
	 */
	function wlfmc_is_wishlist_page() {
		$wishlist_page_id = WLFMC()->get_wishlist_page_id();

		if ( ! $wishlist_page_id ) {
			return false;
		}

		return apply_filters( 'wlfmc_is_wishlist_page', is_page( $wishlist_page_id ) );
	}
}

if ( ! function_exists( 'wlfmc_is_multi_list_page' ) ) {
	/**
	 * Check if current page is wishlist
	 *
	 * @return bool
	 */
	function wlfmc_is_multi_list_page() {
		$wishlist_page_id = WLFMC()->get_multi_list_page_id();

		if ( ! $wishlist_page_id ) {
			return false;
		}

		return apply_filters( 'wlfmc_is_multi_list_page', is_page( $wishlist_page_id ) );
	}
}

if ( ! function_exists( 'wlfmc_is_waitlist_page' ) ) {
	/**
	 * Check if current page is waitlist
	 *
	 * @return bool
	 */
	function wlfmc_is_waitlist_page() {
		$wishlist_page_id = WLFMC()->get_waitlist_page_id();

		if ( ! $wishlist_page_id ) {
			return false;
		}

		return apply_filters( 'wlfmc_is_waitlist_page', is_page( $wishlist_page_id ) );
	}
}

if ( ! function_exists( 'wlfmc_is_tabbed_page' ) ) {
	/**
	 * Check if current page is tabbed page
	 *
	 * @return bool
	 */
	function wlfmc_is_tabbed_page() {
		$tabbed_page_id = WLFMC()->get_tabbed_page_id();

		if ( ! $tabbed_page_id ) {
			return false;
		}

		return apply_filters( 'wlfmc_is_tabbed_page', is_page( $tabbed_page_id ) );
	}
}

if ( ! function_exists( 'wlfmc_is_single' ) ) {
	/**
	 * Returns true if it finds that you're printing a single product
	 * Should return false in any loop (including the ones inside single product page)
	 *
	 * @return bool Whether you're currently on single product template
	 */
	function wlfmc_is_single() {
		return apply_filters(
			'wlfmc_is_single',
			is_product() && ! in_array(
				wc_get_loop_prop( 'name' ),
				array(
					'related',
					'up-sells',
				),
				true
			) && ! wc_get_loop_prop( 'is_shortcode' )
		);
	}
}

if ( ! function_exists( 'wlfmc_is_mobile' ) ) {
	/**
	 * Returns true if we're currently on mobile view
	 *
	 * @return bool Whether you're currently on mobile view
	 */
	function wlfmc_is_mobile() {
		global $wlfmc_is_mobile;

		return apply_filters( 'wlfmc_is_wishlist_responsive', true ) && ( wp_is_mobile() || $wlfmc_is_mobile );
	}
}

if ( ! function_exists( 'wlfmc_is_true' ) ) {
	/**
	 * Is something true?
	 *
	 * @param string|bool|int $value The value to check for.
	 *
	 * @return bool
	 */
	function wlfmc_is_true( $value ): bool {
		return 'yes' === strtolower( $value ) || 'true' === strtolower( $value ) || 1 === $value || '1' === $value || true === $value || 'on' === strtolower( $value );
	}
}

if ( ! function_exists( 'wlfmc_is_rtl' ) ) {
	/**
	 * Is rtl ?
	 *
	 * @param string|bool $lang language.
	 *
	 * @return bool
	 */
	function wlfmc_is_rtl( $lang = false ): bool {
		$current_lang    = $lang ?? apply_filters( 'wpml_current_language', null );
		return apply_filters( 'wpml_is_rtl' , $current_lang ?? is_rtl() );
	}
}

/* === TEMPLATE FUNCTIONS === */

if ( ! function_exists( 'wlfmc_locate_template' ) ) {
	/**
	 * Locate the templates and return the path of the file found
	 *
	 * @param string $path Path to locate.
	 * @param array  $var Unused.
	 *
	 * @version 1.5.0
	 * @return string
	 */
	function wlfmc_locate_template( $path, $var = null ) {
		$woocommerce_base = WC()->template_path();

		$template_woocommerce_path = $woocommerce_base . $path;
		$template_path             = '/' . $path;
		$plugin_path               = MC_WLFMC_DIR . 'templates/' . $path;
		$premium_plugin_path       = defined( 'MC_WLFMC_PRO_DIR' ) ? MC_WLFMC_PRO_DIR . 'templates/' . $path : false;

		$located = locate_template(
			array(
				$template_woocommerce_path, // Search in <theme>/woocommerce/.
				$template_path,             // Search in <theme>/.
			)
		);

		if ( ! $located && file_exists( $premium_plugin_path ) ) {
			return apply_filters( 'wlfmc_locate_template', $premium_plugin_path, $path );
		}

		if ( ! $located && file_exists( $plugin_path ) ) {
			return apply_filters( 'wlfmc_locate_template', $plugin_path, $path );
		}

		return apply_filters( 'wlfmc_locate_template', $located, $path );
	}
}

if ( ! function_exists( 'wlfmc_get_template' ) ) {
	/**
	 * Retrieve a template file.
	 *
	 * @param string $path Path to get.
	 * @param mixed  $var Variables to send to template.
	 * @param bool   $return Whether to return or print the template.
	 *
	 * @return string|void
	 */
	function wlfmc_get_template( $path, $var = null, $return = false ) {
		$located = wlfmc_locate_template( $path, $var );

		if ( $var && is_array( $var ) ) {
			$atts = $var;
			extract( $var ); // @codingStandardsIgnoreLine.
		}

		if ( $return ) {
			ob_start();
		}

		// include file located.
		include $located;

		if ( $return ) {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'wlfmc_get_template_part' ) ) {
	/**
	 * Search and include a template part
	 *
	 * @param string $template Template to include.
	 * @param string $template_part Template part.
	 * @param string $template_layout Template variation.
	 * @param array  $var Array of variables to be passed to template.
	 * @param bool   $return Whether to return template or print it.
	 *
	 * @return string|void
	 */
	function wlfmc_get_template_part( $template = '', $template_part = '', $template_layout = '', $var = array(), $return = false ) {
		if ( ! empty( $template_part ) ) {
			$template_part = '-' . $template_part;
		}

		if ( ! empty( $template_layout ) ) {
			$template_layout = '-' . $template_layout;
		}

		$template_hierarchy = apply_filters(
			'wlfmc_template_part_hierarchy',
			array_merge(
				! wlfmc_is_mobile() ? array() : array(
					"wishlist-{$template}{$template_layout}{$template_part}-mobile.php",
					"wishlist-{$template}{$template_part}-mobile.php",
				),
				array(
					"wishlist-{$template}{$template_layout}{$template_part}.php",
					"wishlist-{$template}{$template_part}.php",
				)
			),
			$template,
			$template_part,
			$template_layout,
			$var
		);

		foreach ( $template_hierarchy as $filename ) {
			$located = wlfmc_locate_template( $filename );

			if ( $located ) {
				return wlfmc_get_template( $filename, $var, $return );
			}
		}
	}
}


/* === GET FUNCTIONS === */
if ( ! function_exists( 'wlfmc_get_out_of_stock_product_count' ) ) {
	/**
	 * Get the count of out-of-stock products in WooCommerce.
	 *
	 * @author Wendy from staffup.ai
	 */
	function wlfmc_get_out_of_stock_product_count() {

		add_filter( 'woocommerce_product_query', 'wlfmc_filter_out_of_stock_products' );
		$product_count = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );
		remove_filter( 'woocommerce_product_query', 'wlfmc_filter_out_of_stock_products' );

		return $product_count->found_posts;
	}

	/**
	 * Filter the product query to retrieve only out-of-stock products.
	 *
	 * @param WP_Query $q The product query.
	 */
	function wlfmc_filter_out_of_stock_products( $q ) {
		$q->set( 'meta_query', array(
			array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => '=',
			),
		) );
	}
}


if ( ! function_exists( 'wlfmc_get_hidden_products' ) ) {
	/**
	 * Retrieves a list of hidden products, whatever WC version is running
	 *
	 * WC switched from meta _visibility to product_visibility taxonomy since version 3.0.0,
	 * forcing a split handling (Thank you, WC!)
	 *
	 * @return array List of hidden product ids
	 */
	function wlfmc_get_hidden_products() {
		$hidden_products = get_transient( 'wlfmc_hidden_products' );

		if ( false === $hidden_products ) {
			if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
				$hidden_products = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => - 1,
						'fields'         => 'ids',
						'meta_query'     => array(  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							array(
								'key'   => '_visibility',
								'value' => 'visible',
							),
						),
					)
				);
			} else {
				$hidden_products = wc_get_products(
					array(
						'limit'      => - 1,
						'status'     => 'publish',
						'return'     => 'ids',
						'visibility' => 'hidden',
					)
				);
			}

			/**
			 * Array_filter was added to prevent errors when previous query returns for some reason just 0 index.
			 */
			$hidden_products = array_filter( $hidden_products );

			set_transient( 'wlfmc_hidden_products', $hidden_products, 30 * DAY_IN_SECONDS );
		}

		return apply_filters( 'wlfmc_hidden_products', $hidden_products );
	}
}

if ( ! function_exists( 'wlfmc_get_customer' ) ) {
	/**
	 * Retrieves customer by ID
	 *
	 * @param int|string $customer_id Customer ID or Customer Token.
	 *
	 * @return WLFMC_Customer|bool Customer object; false on error
	 */
	function wlfmc_get_customer( $customer_id ) {
		return WLFMC_Wishlist_Factory::get_customer( $customer_id );
	}
}

if ( ! function_exists( 'wlfmc_get_customer_id_by_user' ) ) {
	/**
	 * Get customer_id by user_id or session_id
	 *
	 * @param int|string $id user_id or session_id.
	 *
	 * @return integer
	 */
	function wlfmc_get_customer_id_by_user( $id ): int {
		global $wpdb;
		if ( ! empty( $id ) ) {
			if ( is_int( $id ) ) {
				return absint( $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM  $wpdb->wlfmc_wishlist_customers WHERE user_id=%d", $id ) ) );//phpcs:ignore WordPress.DB
			} elseif ( is_string( $id ) ) {
				return absint( $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM  $wpdb->wlfmc_wishlist_customers WHERE session_id=%s", $id ) ) );//phpcs:ignore WordPress.DB
			}
		}

		return false;
	}
}

if ( ! function_exists( 'wlfmc_get_wishlist' ) ) {
	/**
	 * Retrieves wishlist by ID
	 *
	 * @param int|string $wishlist_id Wishlist ID or Wishlist Token.
	 *
	 * @return WLFMC_Wishlist|bool Wishlist object; false on error
	 */
	function wlfmc_get_wishlist( $wishlist_id ) {
		return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
	}
}

if ( ! function_exists( 'wlfmc_get_privacy_label' ) ) {
	/**
	 * Returns privacy label
	 *
	 * @param int  $privacy Privacy value.
	 * @param bool $extended Whether to show extended or simplified label.
	 *
	 * @return string Privacy label
	 */
	function wlfmc_get_privacy_label( $privacy, $extended = false ) {

		switch ( $privacy ) {
			case 1:
				$privacy_label = 'private';
				$privacy_text  = esc_html__( 'Private', 'wc-wlfmc-wishlist' );

				if ( $extended ) {
					$privacy_text  = '<b>' . $privacy_text . '</b> - ';
					$privacy_text .= esc_html__( 'Only you can see this list', 'wc-wlfmc-wishlist' );
				}

				break;
			default:
				$privacy_label = 'public';
				$privacy_text  = esc_html__( 'Public', 'wc-wlfmc-wishlist' );

				if ( $extended ) {
					$privacy_text  = '<b>' . $privacy_text . '</b> - ';
					$privacy_text .= esc_html__( 'Anyone can search for and see this list', 'wc-wlfmc-wishlist' );
				}

				break;
		}

		return apply_filters( "wlfmc_{$privacy_label}_wishlist_visibility", $privacy_text, $extended, $privacy );
	}
}

if ( ! function_exists( 'wlfmc_get_privacy_value' ) ) {
	/**
	 * Returns privacy numeric value
	 *
	 * @param string $privacy_label Privacy label.
	 *
	 * @return int Privacy value
	 */
	function wlfmc_get_privacy_value( $privacy_label ) {

		switch ( $privacy_label ) {
			case 'private':
				$privacy_value = 1;
				break;
			default:
				$privacy_value = 0;
				break;
		}

		return apply_filters( 'wlfmc_privacy_value', $privacy_value, $privacy_label );
	}
}

if ( ! function_exists( 'wlfmc_get_option' ) ) {
	/**
	 * Return option value
	 *
	 * @param string $field Field key.
	 * @param string $default Default value.
	 *
	 * @return mixed
	 */
	function wlfmc_get_option( $field, $default = '' ) {
		$all_options = get_option( 'wlfmc_options', array() );
		$value       = $default;

		if ( is_array( $all_options ) && ! empty( $all_options ) ) {
			foreach ( $all_options as $section ) {
				if ( isset( $section[ $field ] ) ) {
					$value = $section[ $field ];
					break;
				}
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'wlfmc_get_current_url' ) ) {
	/**
	 * Retrieves current url
	 *
	 * @return string Current url
	 */
	function wlfmc_get_current_url() {
		global $wp;

		/**
		 * Returns empty string by default, to avoid problems with unexpected redirects
		 * Added filter to change default behaviour, passing what we think is current page url
		 */
		return apply_filters( 'wlfmc_current_url', '', add_query_arg( $wp->query_vars, home_url( $wp->request ) ) );
	}
}

if ( ! function_exists( 'wlfmc_get_admin_header_buttons' ) ) {
	/**
	 * Retrieves admin header buttons
	 *
	 * @return array header buttons
	 */
	function wlfmc_get_admin_header_buttons(): array {
		$buttons = array();
		$test_mode = defined( 'WLFMC_TEST_MODE' ) && wlfmc_is_true( WLFMC_TEST_MODE );
		$test_help = $test_mode ? __( 'Turn off Test Mode to display the settings you have applied.', 'wc-wlfmc-wishlist' ) : __( 'Turn on Test Mode to practice and try things out without displaying for users.', 'wc-wlfmc-wishlist' );
		$help      = '<div class="mct-help-tip-wrap">
						<span class="mct-help-tip-dec">
							<p> ' . esc_attr( $test_help ) .' </p>
						</span>
					</div>';

		if ( ! wlfmc_is_true( get_option( 'wlfmc_wizard' ) ) ) {
			$buttons[] = array(
				'btn_label' => __( 'Fast Setup', 'wc-wlfmc-wishlist' ) . '<img src="' . MC_WLFMC_URL . 'assets/backend/images/arrow-right.svg" alt="" width="24" height="24"/>',
				'btn_class' => 'fast-setup-btn orangelight-btn btn-primary d-flex f-center gap-5 min-pad',
				'btn_url'   => esc_url( add_query_arg( array( 'page' => 'mc-wishlist-setup' ), admin_url( 'admin.php' ) ) ),
			);
		}
		$buttons[] = array(
			'btn_label' => __( 'Get Premium', 'wc-wlfmc-wishlist' ) . '<img src="' . MC_WLFMC_URL . 'assets/backend/images/white-crown.svg" alt="crown" width="24" height="24"/>',
			'btn_class' => 'get-premium-btn orange-btn btn-primary d-flex f-center gap-5 min-pad',
			'btn_url'   => 'https://moreconvert.com/a2a8',
		);
		$buttons[] = array(
			'btn_label' => sprintf( __( 'Test Mode %s %s', 'wc-wlfmc-wishlist' ), $help, $test_mode ? '<span class="badge enabled">'. __( 'Enabled', 'wc-wlfmc-wishlist' ) . '</span>': '<span class="badge disabled">'.__( 'Disabled', 'wc-wlfmc-wishlist' )  . '</span>' ),
			'btn_class' => 'd-flex f-center gap-5 wlfmc-test-mode-status',
			'btn_url'   => 'https://moreconvert.com/h739',
		);
		return apply_filters( 'wlfmc_admin_header_buttons', $buttons );
	}
}

if ( ! function_exists( 'wlfmc_get_admin_header_menu' ) ) {
	/**
	 * Retrieves admin header menu
	 *
	 * @version 1.7.6
	 * @return array header menu
	 */
	function wlfmc_get_admin_header_menu(): array {
		//$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		//$current_tab  = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		return apply_filters(
			'wlfmc_admin_header_menu',
			array(
				array(
					'id'   => 'style',
					'text' => __( 'Style', 'wc-wlfmc-wishlist' ),
					'submenu' => array(
						array(
							'id'   => 'button-style',
							'text' => __( 'Wishlist Button Style', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'button',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'page-style',
							'text'          => __( 'Wishlist Page Style', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'page-settings',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'counter-style',
							'text'          => __( 'Wishlist Counter Style', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'counter',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'popup-style',
							'text'          => __( 'Popup Style', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-global-settings',
									'tab'  => 'appearance',
								),
								admin_url( 'admin.php' )
							),
						),
					),
				),
				array(
					'id'   => 'text',
					'text' => __( 'Text', 'wc-wlfmc-wishlist' ),
					'submenu' => array(
						array(
							'id'   => 'button-text',
							'text' => __( 'Wishlist Button Texts', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-text-customization',
									'tab'  => 'wishlist#button_label_add',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'page-text',
							'text'          => __( 'Wishlist Page Texts', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-text-customization',
									'tab'  => 'wishlist#wishlist_page_title',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'counter-text',
							'text'          => __( 'Counter Texts', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-text-customization',
									'tab'  => 'wishlist#counter_button_text',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'notification-text',
							'text'          => __( 'Notification Texts', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-text-customization',
									'tab'  => 'wishlist#login_need_text',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'global-text',
							'text'          => __( 'Global Texts', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-text-customization',
									'tab'  => 'global#action_label',
								),
								admin_url( 'admin.php' )
							),
						),
					),
				),
				array(
					'id'      => 'functions',
					'text'    => __( 'Functions', 'wc-wlfmc-wishlist' ),
					'submenu' => array(
						array(
							'id'   => 'wishlist',
							'text' => __( 'In-List Actions', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'page-settings#remove_from_wishlist',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'wishlist-button-actions',
							'text' => __( 'Wishlist Button Actions', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'button#click_wishlist_button_behavior',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'wishlist-page-select',
							'text' => __( 'Page Selection', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'page-settings#wishlist_tabbed_lists',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'popup-options',
							'text' => __( 'Wishlist Popup Options', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'general#popup_size',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'counter-options',
							'text' => __( 'Wishlist Counter Options', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-wishlist-settings',
									'tab'  => 'counter',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'sharing',
							'text' => __( 'Sharing Options', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-global-settings',
									'tab'  => 'share',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'caching',
							'text' => __( 'Caching and Speed', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-global-settings',
									'tab'  => 'general#ajax_mode',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'   => 'import-export',
							'text' => __( 'Import & Export', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-global-settings',
									'tab'  => 'general#import_commerce_kit_wishlist',
								),
								admin_url( 'admin.php' )
							),
						),
					),
				),
				array(
					'id'      => 'email',
					'text'    => __( 'Email Options', 'wc-wlfmc-wishlist' ),
					'submenu' => array(
						array(
							'id'   => 'email-automation',
							'text' => __( 'Email Automations', 'wc-wlfmc-wishlist' ),
							'url'  => add_query_arg(
								array(
									'page' => 'mc-email-automations',
								),
								admin_url( 'admin.php' )
							),
						),
						array(
							'id'            => 'email-marketing',
							'text'          => __( 'Global Settings', 'wc-wlfmc-wishlist' ),
							'url'           => add_query_arg(
								array(
									'page' => 'mc-global-settings',
									'tab'  => 'marketing',
								),
								admin_url( 'admin.php' )
							),
						),
					),
				),
				array(
					'id'      => 'help-center',
					'text'    => __( 'Help Center', 'wc-wlfmc-wishlist' ),
					'url'     => '#',
					'submenu' => array(
						array(
							'id'     => 'docs',
							'text'   => __( 'Documentation', 'wc-wlfmc-wishlist' ),
							'url'    => 'https://moreconvert.com/6xe3',
							'target' => '_blank',
						),
						array(
							'id'     => 'support',
							'text'   => __( 'Support Ticket', 'wc-wlfmc-wishlist' ),
							'url'    => 'https://moreconvert.com/tt9i',
							'target' => '_blank',
						),
					),
				),
			)
		);
	}
}

if ( ! function_exists( 'wlfmc_get_admin_sidebar' ) ) {
	/**
	 * Retrieves admin sidebar items
	 *
	 * @version 1.7.6
	 * @return array sidebar items
	 */
	function wlfmc_get_admin_sidebar( $type = 'default' ): array {
		$icon  = '<span class="dashicons dashicons-external"></span>';
		$premium_widget = array(
			'id'      => 'premium_wishlist',
			'class'   => 'wlfmc-premium-sidebar d-flex f-column',
			'image'   => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/white-crown.svg" width="44" height="44" />',
			'title'   => __( 'Get MoreConvert Pro', 'wc-wlfmc-wishlist' ),
			'content' => __( 'Obtain the most essential "lists" features for a WooCommerce store—lightweight and compatible with over 100 themes and plugins. Track user behavior and boost retention and sales with a built-in email automation and marketing system.', 'wc-wlfmc-wishlist' ),
			'button'  => array(
				'btn_label' => __( 'Get MoreConvert Pro', 'wc-wlfmc-wishlist' ) . '<span class="dashicons dashicons-arrow-' . ( is_rtl() ? 'left' : 'right' ) . '-alt"></span>',
				'btn_url'   => 'https://moreconvert.com/h558',
				'btn_class' => 'btn-primary black-btn',
			),
			'footer_content' => '
				<p>
					<small>' . __( 'Read reviews from real users:', 'wc-wlfmc-wishlist' ) . '</small>
				</p>
				<a href="https://wordpress.org/support/plugin/smart-wishlist-for-more-convert/reviews/" class="d-flex gap-10 f-center rate-btn ">
					<img src="' . MC_WLFMC_URL . 'assets/backend/images/wordpress.svg" alt="rate" width="20" height="20" />
					<div class="d-flex f-column gap-5">
						<div class="d-flex gap-5">
							<img src="' . MC_WLFMC_URL . 'assets/backend/images/icon-star.svg" alt="star" width="12" height="12" />
							<img src="' . MC_WLFMC_URL . 'assets/backend/images/icon-star.svg" alt="star" width="12" height="12" />
							<img src="' . MC_WLFMC_URL . 'assets/backend/images/icon-star.svg" alt="star" width="12" height="12" />
							<img src="' . MC_WLFMC_URL . 'assets/backend/images/icon-star.svg" alt="star" width="12" height="12" />
							<img src="' . MC_WLFMC_URL . 'assets/backend/images/icon-star.svg" alt="star" width="12" height="12" />
						</div>
						<div>' . __( 'Rated 5.0 • by 65+ Users', 'wc-wlfmc-wishlist' ) . '</div>
					</div>
				</a>',
		);

		switch ( $type ) {
			case 'wishlist' :
				$items = array(
					$premium_widget,
					array(
						'id'      => 'wishlist-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
								<a href="https://moreconvert.com/r2yb">'. esc_attr__( 'How to configure wishlist?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/rr3l">'. esc_attr__( 'What is mini-wishlist?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/69n9">'. esc_attr__( 'How to use wishlist counter shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/1ijn">'. esc_attr__( 'How to prevent button overlapping in product listings and single product pages?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/v55t">'. esc_attr__( 'How to customize "Add To Wishlist" button on product lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<!--a href="https://moreconvert.com/evkm">'. esc_attr__( 'How to hide counter icon when the wishlist is empty?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/4k67">'. esc_attr__( 'How to create multiple/unlimited lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/y572">'. esc_attr__( 'How to use "Add to wishlist" button shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/3gsh">'. esc_attr__( 'How to use wishlist table shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/ycrt">'. esc_attr__( 'How to add icons to tabbed lists in woocommerce my account tabs?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/wa1b">'. esc_attr__( 'How to configure wishlist pop-up settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/meli">'. esc_attr__( 'How to configure wishlist display settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/2go6">'. esc_attr__( 'How to configure wishlist counter settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/98jb">'. esc_attr__( 'How to configure wishlist table settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/ox6a">'. esc_attr__( 'How to configure wishlist page settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/njbz">'. esc_attr__( 'How to add wishlist functionality to your custom dashboard?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/a0l4">'. esc_attr__( 'How to customize the empty list image and button text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/f3ct">'. esc_attr__( 'How to display SKU in wishlist table?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/3945">'. esc_attr__( 'How to display price changes in the wishlist table?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/zy38',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/84c3',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			case 'multi-list':
				$items = array(
					array(
						'id'      => 'multi-list-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
							<a href="https://moreconvert.com/mbxg">'. esc_attr__( 'What is multi-lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/vxnd">'. esc_attr__( 'How to prevent button overlapping in product listings and single product pages?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/x42i">'. esc_attr__( 'How to enable drag and drop products functionality in lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/h14n">'. esc_attr__( 'How to customize multi-lists counter?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/lryq">'. esc_attr__( 'How to configure multi-lists page settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<!--a href="https://moreconvert.com/o0u6">'. esc_attr__( 'How to customize multi-lists display settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/7g98">'. esc_attr__( 'How to customize multi-lists button on single product page?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/szm6">'. esc_attr__( 'How to customize texts for multi-lists label and tooltip?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/qc3q">'. esc_attr__( 'How to configure multi-lists pop-up settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/noab">'. esc_attr__( 'How to customize pop-up appearance settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/88jg">'. esc_attr__( 'How to customize label and tooltip texts on single lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/j773">'. esc_attr__( 'How to configure multi-lists table settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/fkcn">'. esc_attr__( 'How to configure settings for single lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/msiq">'. esc_attr__( 'How to customize texts for multi-lists notifications?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/i1sl">'. esc_attr__( 'How to customize settings for "Add To List" pop up?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/uj4x">'. esc_attr__( 'How to customize texts for multi-lists button and tooltip?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->					           
						',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/gno0',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/d33k',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			case 'waitlist':
				$items = array(
					array(
						'id'      => 'waitlist-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
							<a href="https://moreconvert.com/wjwx">'. esc_attr__( 'How to use waitlist shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>						           
							<a href="https://moreconvert.com/lwdt">'. esc_attr__( 'How to enable drag and drop products functionality in lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>							
							<a href="https://moreconvert.com/84ew">'. esc_attr__( 'What is Mini-Waitlist and how to enable it?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/q9nn">'. esc_attr__( 'How to customize waitlist table?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/nc4u">'. esc_attr__( 'How to add lists functionality to your custom dashboard?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/d9fo">'. esc_attr__( 'How to display price changes in the wishlist table?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<!--a href="https://moreconvert.com/4z81">'. esc_attr__( 'How to customize display options for waitlist?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/8uu8">'. esc_attr__( 'How to customize "Add to waitlist" button on single product page?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/53di">'. esc_attr__( 'How to customize waitlist counter?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/bhs4">'. esc_attr__( 'How to customize "Add to waitlist" button on product listing?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/vkuu">'. esc_attr__( 'How to customize texts for waitlist buttons and tooltips?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/wrkb">'. esc_attr__( 'How to activate email settings for waitlist?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/bkwf">'. esc_attr__( 'How to customize waitlist pop-up?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/qwlb">'. esc_attr__( 'How to customize waitlist notification texts?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/1pqx">'. esc_attr__( 'How to customize waitlist page?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/a859">'. esc_attr__( 'How to customize waitlist label and tooltip texts?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/d0a3">'. esc_attr__( 'How to customize the empty list image and button text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/96kt">'. esc_attr__( 'How to display SKU in lists table?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/1xe1">'. esc_attr__( 'How to prevent button overlapping in product listings and single product pages?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->	
							<!--a href="https://moreconvert.com/iczb">'. esc_attr__( 'How to add icons to tabbed lists in woocommerce my account tabs?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
						',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/1zwy',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/lm3f',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			case 'save-for-later':
				$items = array(
					array(
						'id'      => 'save-for-later-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
							<a href="https://moreconvert.com/510i">'. esc_attr__( 'How to enable "Remove Reduction Pop-up"?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/80f2">'. esc_attr__( 'How to add next purchase cart button to cart page?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/laok">'. esc_attr__( 'How to customize next purchase cart pop-up??', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/2xs6">'. esc_attr__( 'What is Next Purchase Cart?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<a href="https://moreconvert.com/saov">'. esc_attr__( 'How to customize label and tooltip customization text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							<!--a href="https://moreconvert.com/46ay">'. esc_attr__( 'How to customize next purchase cart display settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/26n8">'. esc_attr__( 'How to customize table settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/t42l">'. esc_attr__( 'How to change all labels and texts settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/vfjj">'. esc_attr__( 'How to customize notification custom text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/9x3h">'. esc_attr__( 'How to use next purchase cart table shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/g5vt">'. esc_attr__( 'How to use next purchase cart button shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
							<!--a href="https://moreconvert.com/apou">'. esc_attr__( 'How to use next purchase cart counter shortcodes?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->				
						',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/eno0',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/dau9',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			case 'global' :
				$items = array(
					$premium_widget,
					array(
						'id'      => 'global-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
								<a href="https://moreconvert.com/d9ah">'. esc_attr__( 'How to enable drag and drop products functionality in lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/cbcs">'. esc_attr__( 'How to customize pop-ups and tooltips appearance?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/cd4l">'. esc_attr__( 'How to configure tabbed lists settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/wbgu">'. esc_attr__( 'How to move products between lists?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/1sku">'. esc_attr__( 'How to add additional CSS?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<!--a href="https://moreconvert.com/50of">'. esc_attr__( 'How to add sign-up and login links to wishlist?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/00bd">'. esc_attr__( 'How to configure advanced list actions settings?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/cntb">'. esc_attr__( 'How to enable and customize social share?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/1ilf">'. esc_attr__( 'How to customize share label & tooltip custom text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/eqfh">'. esc_attr__( 'How to customize toast notification for add to list?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/zshc">'. esc_attr__( 'How to optimize cache settings and improve performance?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/okgi">'. esc_attr__( 'How to customize tabbed lists appearance?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/8sk0">'. esc_attr__( 'How to customize the global text management?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								<!--a href="https://moreconvert.com/3eiv">'. esc_attr__( 'How to add a lists users counter?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a-->
								
							',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/n7mu',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/zrpk',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			case 'text' :
				$items = array(
					$premium_widget,
					array(
						'id'      => 'text-sidebar',
						'class'   => 'faq-content',
						'title'   => __( 'FAQs', 'wc-wlfmc-wishlist' ),
						'desc'    => __( 'Most frequently asked questions.', 'wc-wlfmc-wishlist' ),
						'content' => '
								<a href="https://moreconvert.com/gri0">'. esc_attr__( 'How to Translate Automations and Campaigns with WPML?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/02ua">'. esc_attr__( 'How to customize label and tooltip customization text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/1ilf">'. esc_attr__( 'How to customize share label & tooltip custom text?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/8sk0">'. esc_attr__( 'How to customize the global text management?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
								<a href="https://moreconvert.com/szm6">'. esc_attr__( 'How to customize texts for multi-lists label and tooltip?', 'wc-wlfmc-wishlist' ) . wp_kses_post( $icon ) .'</a>
							',
						'buttons'  => array(
							array(
								'btn_label' => __( 'See All Documents', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/1hhw',
								'btn_class' => 'btn-secondary full-w red-btn',
							),
							array(
								'btn_label' => __( 'Ask your question', 'wc-wlfmc-wishlist' ),
								'btn_url'   => 'https://moreconvert.com/ztpq',
								'btn_class' => 'btn-primary full-w red-btn',
							)
						),
					),
				);
				break;
			default:
				$items = array(
					array(
						'image'   => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/sell.svg" width="97" height="42" />',
						'title'   => __( 'More Convert Tips', 'wc-wlfmc-wishlist' ),
						'content' => __( 'Let MC Wishlist be active on your site for at least one month and then boost your sales through its analytics.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'id'      => 'premium_wishlist',
						'image'   => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/diamond.svg" width="63" height="55" />',
						'title'   => __( 'Premium Wishlist', 'wc-wlfmc-wishlist' ),
						'content' => __( 'If you want to have a more advanced Wishlist and at the same time increase sales without more traffic, be sure to check out the Pro version.', 'wc-wlfmc-wishlist' ),
						'button'  => array(
							'btn_label' => __( 'Check now', 'wc-wlfmc-wishlist' ),
							'btn_url'   => 'https://moreconvert.com/qt6g',
							'btn_class' => 'btn-primary min-width-btn ',
						),
					),
					array(
						'id'      => 'start_growing',
						'image'   => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/youtube.svg" width="80" height="55" />',
						'title'   => __( 'WordPress & Sales', 'wc-wlfmc-wishlist' ),
						'content' => __( 'Learn 3 new videos every week about troubleshooting WordPress and increasing revenue from your WooCommerce website.', 'wc-wlfmc-wishlist' ),
						'button'  => array(
							'btn_label' => __( 'Start Growing', 'wc-wlfmc-wishlist' ),
							'btn_url'   => 'https://moreconvert.com/7jx7',
							'btn_class' => 'btn-primary min-width-btn red-btn',
						),
					),
				);
				break;

		}
		return apply_filters( 'wlfmc_sidebar_items', $items, $type );
	}
}

if ( ! function_exists( 'wlfmc_get_action_output' ) ) {
	/**
	 * Get the output of a specified action.
	 *
	 * This function executes the given action and captures its output using
	 * output buffering. The captured output is then returned as a string.
	 *
	 * @param string $action_name The name of the action to execute.
	 *
	 * @return string The captured output of the action.
	 */
	function wlfmc_get_action_output( $action_name ) {
		// Start output buffering.
		ob_start();

		// Execute the specified action.
		do_action( $action_name );

		// Get the captured output and clean the output buffer.
		return ob_get_clean();
	}
}

if ( ! function_exists( 'wlfmc_get_icon_names' ) ) {
	/**
	 * Get icon name for admin settings and elementor.
	 *
	 * @param string $list_type list type.
	 * @param bool   $has_image has preview image or not.
	 * @param bool   $multiple_image has multiple preview image or not.
	 * @param bool   $return_keys return string of icon keys
	 * @return array|string
	 */
	function wlfmc_get_icon_names( $list_type, $has_image = false, $multiple_image = false, $return_keys = false ) {

		$icons     = array();
		$tmp_icons = array();

		if ( 'wishlist' === $list_type ) {
			$icons = array(
				'heart'           => esc_html__( 'Heart', 'wc-wlfmc-wishlist' ),
				'heart-light-1'   => esc_html__( 'Heart light 1', 'wc-wlfmc-wishlist' ),
				'heart-light-2'   => esc_html__( 'Heart light 2', 'wc-wlfmc-wishlist' ),
				'heart-regular-1' => esc_html__( 'Heart regular 1', 'wc-wlfmc-wishlist' ),
				'heart-regular-2' => esc_html__( 'Heart regular 2', 'wc-wlfmc-wishlist' ),
				'plus'            => esc_html__( 'Plus', 'wc-wlfmc-wishlist' ),
				'pin'             => esc_html__( 'Pin', 'wc-wlfmc-wishlist' ),
				'gift'            => esc_html__( 'Gift', 'wc-wlfmc-wishlist' ),
				'gift-light'      => esc_html__( 'Gift light', 'wc-wlfmc-wishlist' ),
				'gift-regular'    => esc_html__( 'Gift regular', 'wc-wlfmc-wishlist' ),
				'star'            => esc_html__( 'Star', 'wc-wlfmc-wishlist' ),
				'star-light'      => esc_html__( 'Star light', 'wc-wlfmc-wishlist' ),
				'star-regular'    => esc_html__( 'Star regular', 'wc-wlfmc-wishlist' ),
				'tag'             => esc_html__( 'Save', 'wc-wlfmc-wishlist' ),
				'tag-light'       => esc_html__( 'Save light', 'wc-wlfmc-wishlist' ),
				'tag-regular'     => esc_html__( 'Save regular', 'wc-wlfmc-wishlist' ),
				'custom'          => esc_html__( 'Custom icon', 'wc-wlfmc-wishlist' ),
			);
		}
		if ( 'waitlist' === $list_type ) {
			$icons = array(
				'notification-1'         => esc_html__( 'Notification 1', 'wc-wlfmc-wishlist' ),
				'notification-2'         => esc_html__( 'Notification 2', 'wc-wlfmc-wishlist' ),
				'notification-3'         => esc_html__( 'Notification 3', 'wc-wlfmc-wishlist' ),
				'notification-4-light'   => esc_html__( 'Notification 4 light', 'wc-wlfmc-wishlist' ),
				'notification-4-regular' => esc_html__( 'Notification 4 regular', 'wc-wlfmc-wishlist' ),
				'notification-5-light'   => esc_html__( 'Notification 5 light', 'wc-wlfmc-wishlist' ),
				'notification-5-regular' => esc_html__( 'Notification 5 regular', 'wc-wlfmc-wishlist' ),
				'notification-6-light'   => esc_html__( 'Notification 6 light', 'wc-wlfmc-wishlist' ),
				'notification-6-regular' => esc_html__( 'Notification 6 regular', 'wc-wlfmc-wishlist' ),
				'notification-7-light'   => esc_html__( 'Notification 7 light', 'wc-wlfmc-wishlist' ),
				'notification-7-regular' => esc_html__( 'Notification 7 regular', 'wc-wlfmc-wishlist' ),
				'custom'                 => esc_html__( 'Custom icon', 'wc-wlfmc-wishlist' ),
			);
		}

		if ( 'save-for-later' === $list_type ) {
			$icons = array(
				'sfl-1'          => esc_html__( 'Icon 1', 'wc-wlfmc-wishlist' ),
				'sfl-2'          => esc_html__( 'Icon 2', 'wc-wlfmc-wishlist' ),
				'sfl-3'          => esc_html__( 'Icon 3', 'wc-wlfmc-wishlist' ),
				'sfl-4'          => esc_html__( 'Icon 4', 'wc-wlfmc-wishlist' ),
				'sfl-5'          => esc_html__( 'Icon 5', 'wc-wlfmc-wishlist' ),
				'sfl-6'          => esc_html__( 'Icon 6', 'wc-wlfmc-wishlist' ),
				'sfl-7'          => esc_html__( 'Icon 7', 'wc-wlfmc-wishlist' ),
				'sfl-8'          => esc_html__( 'Icon 8', 'wc-wlfmc-wishlist' ),
				'sfl-9-light'    => esc_html__( 'Icon 9 light', 'wc-wlfmc-wishlist' ),
				'sfl-9-regular'  => esc_html__( 'Icon 9 regular', 'wc-wlfmc-wishlist' ),
				'sfl-10-light'   => esc_html__( 'Icon 10 light', 'wc-wlfmc-wishlist' ),
				'sfl-10-regular' => esc_html__( 'Icon 10 regular', 'wc-wlfmc-wishlist' ),
				'sfl-10-solid'   => esc_html__( 'Icon 10 solid', 'wc-wlfmc-wishlist' ),
				'sfl-11-light'   => esc_html__( 'Icon 11 light', 'wc-wlfmc-wishlist' ),
				'sfl-11-regular' => esc_html__( 'Icon 11 regular', 'wc-wlfmc-wishlist' ),
				'sfl-12-light'   => esc_html__( 'Icon 12 light', 'wc-wlfmc-wishlist' ),
				'sfl-12-regular' => esc_html__( 'Icon 12 regular', 'wc-wlfmc-wishlist' ),
				'sfl-13-light'   => esc_html__( 'Icon 13 light', 'wc-wlfmc-wishlist' ),
				'sfl-13-regular' => esc_html__( 'Icon 13 regular', 'wc-wlfmc-wishlist' ),
				'sfl-14-light'   => esc_html__( 'Icon 14 light', 'wc-wlfmc-wishlist' ),
				'sfl-14-regular' => esc_html__( 'Icon 14 regular', 'wc-wlfmc-wishlist' ),
				'custom'         => esc_html__( 'Custom icon', 'wc-wlfmc-wishlist' ),
			);
		}

		if ( 'multi-list' === $list_type ) {
			$icons = array(
				'multi-list-1'          => esc_html__( 'Multi-list 1', 'wc-wlfmc-wishlist' ),
				'multi-list-1-o'        => esc_html__( 'Multi-list 2', 'wc-wlfmc-wishlist' ),
				'multi-list-2'          => esc_html__( 'Multi-list 3', 'wc-wlfmc-wishlist' ),
				'multi-list-2-o'        => esc_html__( 'Multi-list 4', 'wc-wlfmc-wishlist' ),
				'multi-list-3'          => esc_html__( 'Multi-list 5', 'wc-wlfmc-wishlist' ),
				'multi-list-3-o'        => esc_html__( 'Multi-list 6', 'wc-wlfmc-wishlist' ),
				'multi-list-4'          => esc_html__( 'Multi-list 7', 'wc-wlfmc-wishlist' ),
				'multi-list-4-o'        => esc_html__( 'Multi-list 8', 'wc-wlfmc-wishlist' ),
				'multi-list-5'          => esc_html__( 'Multi-list 9', 'wc-wlfmc-wishlist' ),
				'multi-list-5-o'        => esc_html__( 'Multi-list 10', 'wc-wlfmc-wishlist' ),
				'multi-list-6'          => esc_html__( 'Multi-list 11', 'wc-wlfmc-wishlist' ),
				'multi-list-6-o'        => esc_html__( 'Multi-list 12', 'wc-wlfmc-wishlist' ),
				'multi-list-7'          => esc_html__( 'Multi-list 13', 'wc-wlfmc-wishlist' ),
				'multi-list-7-o'        => esc_html__( 'Multi-list 14', 'wc-wlfmc-wishlist' ),
				'multi-list-8'          => esc_html__( 'Multi-list 15', 'wc-wlfmc-wishlist' ),
				'multi-list-8-o'        => esc_html__( 'Multi-list 16', 'wc-wlfmc-wishlist' ),
				'multi-list-9'          => esc_html__( 'Multi-list 17', 'wc-wlfmc-wishlist' ),
				'multi-list-9-o'        => esc_html__( 'Multi-list 18', 'wc-wlfmc-wishlist' ),
				'multi-list-10'         => esc_html__( 'Multi-list 19', 'wc-wlfmc-wishlist' ),
				'multi-list-10-o'       => esc_html__( 'Multi-list 20', 'wc-wlfmc-wishlist' ),
				'multi-list-11-light'   => esc_html__( 'Multi-list 21 light', 'wc-wlfmc-wishlist' ),
				'multi-list-11-regular' => esc_html__( 'Multi-list 21 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-11-solid-o' => esc_html__( 'Multi-list 21 solid', 'wc-wlfmc-wishlist' ),
				'multi-list-12-light'   => esc_html__( 'Multi-list 22 light', 'wc-wlfmc-wishlist' ),
				'multi-list-12-regular' => esc_html__( 'Multi-list 22 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-12-solid-o' => esc_html__( 'Multi-list 22 solid', 'wc-wlfmc-wishlist' ),
				'multi-list-13-light'   => esc_html__( 'Multi-list 23 light', 'wc-wlfmc-wishlist' ),
				'multi-list-13-regular' => esc_html__( 'Multi-list 23 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-13-solid-o' => esc_html__( 'Multi-list 23 solid', 'wc-wlfmc-wishlist' ),
				'multi-list-14-light'   => esc_html__( 'Multi-list 24 light', 'wc-wlfmc-wishlist' ),
				'multi-list-14-regular' => esc_html__( 'Multi-list 24 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-14-solid-o' => esc_html__( 'Multi-list 24 solid', 'wc-wlfmc-wishlist' ),
				'heart'                 => esc_html__( 'Heart', 'wc-wlfmc-wishlist' ),
				'tag'                   => esc_html__( 'Save', 'wc-wlfmc-wishlist' ),
				'plus'                  => esc_html__( 'Plus', 'wc-wlfmc-wishlist' ),
				'gift'                  => esc_html__( 'Gift', 'wc-wlfmc-wishlist' ),
				'star'                  => esc_html__( 'Star', 'wc-wlfmc-wishlist' ),
				'pin'                   => esc_html__( 'Pin', 'wc-wlfmc-wishlist' ),
				'heart-o'               => esc_html__( 'Heart Filled', 'wc-wlfmc-wishlist' ),
				'tag-o'                 => esc_html__( 'Save Filled', 'wc-wlfmc-wishlist' ),
				'plus-o'                => esc_html__( 'Plus Filled', 'wc-wlfmc-wishlist' ),
				'gift-o'                => esc_html__( 'Gift Filled', 'wc-wlfmc-wishlist' ),
				'star-o'                => esc_html__( 'Star Filled', 'wc-wlfmc-wishlist' ),
				'pin-o'                 => esc_html__( 'Pin Filled', 'wc-wlfmc-wishlist' ),
				'heart-light-1'         => esc_html__( 'Heart light 1', 'wc-wlfmc-wishlist' ),
				'heart-light-2'         => esc_html__( 'Heart light 2', 'wc-wlfmc-wishlist' ),
				'heart-regular-1'       => esc_html__( 'Heart regular 1', 'wc-wlfmc-wishlist' ),
				'heart-regular-2'       => esc_html__( 'Heart regular 2', 'wc-wlfmc-wishlist' ),
				'heart-solid-1-o'       => esc_html__( 'Heart solid 1', 'wc-wlfmc-wishlist' ),
				'heart-solid-2-o'       => esc_html__( 'Heart solid 2', 'wc-wlfmc-wishlist' ),
				'star-light'            => esc_html__( 'Star light', 'wc-wlfmc-wishlist' ),
				'star-regular'          => esc_html__( 'Star regular', 'wc-wlfmc-wishlist' ),
				'star-solid-o'          => esc_html__( 'Star solid', 'wc-wlfmc-wishlist' ),
				'gift-light'            => esc_html__( 'Gift light', 'wc-wlfmc-wishlist' ),
				'gift-regular'          => esc_html__( 'Gift regular', 'wc-wlfmc-wishlist' ),
				'gift-solid-o'          => esc_html__( 'Gift solid', 'wc-wlfmc-wishlist' ),
				'tag-light'             => esc_html__( 'Save light', 'wc-wlfmc-wishlist' ),
				'tag-regular'           => esc_html__( 'Save regular', 'wc-wlfmc-wishlist' ),
				'tag-solid-o'           => esc_html__( 'Save solid', 'wc-wlfmc-wishlist' ),
				'custom'                => esc_html__( 'Custom icon', 'wc-wlfmc-wishlist' ),
			);
		}
		if ( 'multi-list-counter' === $list_type ) {
			$icons = array(
				'multi-list-1'          => esc_html__( 'Multi-list 1', 'wc-wlfmc-wishlist' ),
				'multi-list-2'          => esc_html__( 'Multi-list 2', 'wc-wlfmc-wishlist' ),
				'multi-list-3'          => esc_html__( 'Multi-list 3', 'wc-wlfmc-wishlist' ),
				'multi-list-4'          => esc_html__( 'Multi-list 4', 'wc-wlfmc-wishlist' ),
				'multi-list-5'          => esc_html__( 'Multi-list 5', 'wc-wlfmc-wishlist' ),
				'multi-list-6'          => esc_html__( 'Multi-list 6', 'wc-wlfmc-wishlist' ),
				'multi-list-7'          => esc_html__( 'Multi-list 7', 'wc-wlfmc-wishlist' ),
				'multi-list-8'          => esc_html__( 'Multi-list 8', 'wc-wlfmc-wishlist' ),
				'multi-list-9'          => esc_html__( 'Multi-list 9', 'wc-wlfmc-wishlist' ),
				'multi-list-10'         => esc_html__( 'Multi-list 10', 'wc-wlfmc-wishlist' ),
				'multi-list-11-light'   => esc_html__( 'Multi-list 11 light', 'wc-wlfmc-wishlist' ),
				'multi-list-11-regular' => esc_html__( 'Multi-list 11 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-12-light'   => esc_html__( 'Multi-list 12 light', 'wc-wlfmc-wishlist' ),
				'multi-list-12-regular' => esc_html__( 'Multi-list 12 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-13-light'   => esc_html__( 'Multi-list 13 light', 'wc-wlfmc-wishlist' ),
				'multi-list-13-regular' => esc_html__( 'Multi-list 13 regular', 'wc-wlfmc-wishlist' ),
				'multi-list-14-light'   => esc_html__( 'Multi-list 14 light', 'wc-wlfmc-wishlist' ),
				'multi-list-14-regular' => esc_html__( 'Multi-list 14 regular', 'wc-wlfmc-wishlist' ),
				'heart'                 => esc_html__( 'Heart', 'wc-wlfmc-wishlist' ),
				'heart-light-1'         => esc_html__( 'Heart light 1', 'wc-wlfmc-wishlist' ),
				'heart-light-2'         => esc_html__( 'Heart light 2', 'wc-wlfmc-wishlist' ),
				'heart-regular-1'       => esc_html__( 'Heart regular 1', 'wc-wlfmc-wishlist' ),
				'heart-regular-2'       => esc_html__( 'Heart regular 2', 'wc-wlfmc-wishlist' ),
				'tag'                   => esc_html__( 'Save', 'wc-wlfmc-wishlist' ),
				'plus'                  => esc_html__( 'Plus', 'wc-wlfmc-wishlist' ),
				'pin'                   => esc_html__( 'Pin', 'wc-wlfmc-wishlist' ),
				'gift'                  => esc_html__( 'Gift', 'wc-wlfmc-wishlist' ),
				'gift-light'            => esc_html__( 'Gift light', 'wc-wlfmc-wishlist' ),
				'gift-regular'          => esc_html__( 'Gift regular', 'wc-wlfmc-wishlist' ),
				'star'                  => esc_html__( 'Star', 'wc-wlfmc-wishlist' ),
				'star-light'            => esc_html__( 'Star light', 'wc-wlfmc-wishlist' ),
				'star-regular'          => esc_html__( 'Star regular', 'wc-wlfmc-wishlist' ),
				'tag-light'             => esc_html__( 'Save light', 'wc-wlfmc-wishlist' ),
				'tag-regular'           => esc_html__( 'Save regular', 'wc-wlfmc-wishlist' ),
				'custom'                => esc_html__( 'Custom icon', 'wc-wlfmc-wishlist' ),
			);
		}
		if ( $has_image && ! empty( $icons ) ) {
			foreach ( $icons as $key => $title ) {
				if ( 'custom' === $key ) {
					$tmp_icons[ $key ] = $title;
					continue;
				}
				$second_key = $key;
				if ( $multiple_image ) {
					$second_key = str_replace( array( '-regular-', '-light-', '-regular', '-light' ), array( '-solid-', '-solid-', '-solid', '-solid' ), $key );
				}
				$tmp_icons[ $key ] = array(
					'title' => $title,
					'image' => $multiple_image ? '<span class="d-flex space-between f-center"><img src="' . MC_WLFMC_URL . 'assets/backend/images/font-icons/' . esc_attr( $key ) . '.svg" alt="' . esc_attr( $title ) . '" width="15" height="15" style="margin:0  5px"/><img src="' . MC_WLFMC_URL . 'assets/backend/images/font-icons/' . esc_attr( $second_key ) . '-o.svg" alt="' . esc_attr( $title ) . '" width="15" height="15" /></span>' : '<img src="' . MC_WLFMC_URL . 'assets/backend/images/font-icons/' . esc_attr( $key ) . '.svg" alt="' . esc_attr( $title ) . '" width="15" height="15" />',
				);
			}

			$icons = $tmp_icons;
		}

		if ( $return_keys ) {
			if ( isset($icons['custom']) ) {
				unset( $icons['custom'] );
			}
			$icons = implode( ',', array_keys( $icons ) );
		}

		return $icons;
	}
}

/* === UTILITY FUNCTIONS === */

if ( ! function_exists( 'wlfmc_remove_args_from_posted_data' ) ) {
	/**
	 * Remove args from posted data
	 *
	 * @return array
	 * @version 1.7.7
	 */
	function wlfmc_remove_args_from_posted_data() {
		return apply_filters(
			'wlfmc_remove_args_from_posted_data',
			array(
				'action',
				'nonce',
				'context',
				'fragments',
				'add_to_wishlist',
				'add_to_list',
				'wishlist_ids',
				'current_lists',
				'product_id',
				'variation_id',
				'product_type',
				'quantity',
				'wlid',
				'_wpnonce',
				'_wp_http_referer',
				'add-to-wishlist-type',
				'wl_from_single_product',
				'wlfmc_email',
				'wlfmc_mobile',
				'add_to_waitlist',
				'on_sale',
				'back_in_stock',
				'price_change',
				'low_stock',
				'remove_from_cart_item',
				'remove_from_cart_all',
				'save_cart',
				'cart_item_key',
				'merge_lists',
				'merge_save_for_later',
			)
		);
	}
}

if ( ! function_exists( 'wlfmc_remove_empty_html_tags' ) ) {
	/**
	 * Remove empty tags from html
	 *
	 * @param string $html html code.
	 *
	 * @return array|string|string[]|null
	 */
	function wlfmc_remove_empty_html_tags( $html ) {
		do {
			$old_html = $html;
			$html     = preg_replace( '#<([^<]+?)\s[^>]*>(\s|&nbsp;)*</\1>#', '', $html ?? '' );
		} while ( $html !== $old_html );
		return $html;
	}
}

if ( ! function_exists( 'wlfmc_reserved_slugs' ) ) {
	/**
	 * Reserved wishlist slugs
	 *
	 * @return mixed|void
	 */
	function wlfmc_reserved_slugs() {
		return apply_filters(
			'wlfmc_reserved_slugs',
			array(
				'save-for-later',
				'notifications',
				'waitlist',
				'abandoned-cart',
				'ask-for-estimate',
				'quote-request',
				'back-in-stock',
				'price-change',
				'on-sale',
				'low-stock',
				'hunter-list',
				'upsell-customers',
				'potential-complementary',
				'comment-list',
			)
		);
	}
}

if ( ! function_exists( 'wlfmc_allow_duplicate_products_slugs' ) ) {
	/**
	 * This function allows the user to add duplicate products to specific lists by slugs.
	 *
	 * @return mixed|void
	 */
	function wlfmc_allow_duplicate_products_slugs() {
		return apply_filters(
			'wlfmc_allow_duplicate_products_slugs',
			array(
				'save-for-later',
			)
		);
	}
}

if ( ! function_exists( 'wlfmc_get_slugs_using_cart_item_key' ) ) {
	/**
	 * Return an array of slugs that use the cart_item_key instead of the product_id as the key for the items array
	 *
	 * @return mixed|void
	 */
	function wlfmc_get_slugs_using_cart_item_key() {
		return apply_filters(
			'wlfmc_get_slugs_using_cart_item_key',
			array(
				'save-for-later',
			)
		);
	}
}

if ( ! function_exists( 'wlfmc_process_product_data' ) ) {
	/**
	 * Processes product data and returns posted data and product meta.
	 *
	 * @param string $product_type The type of the product (e.g., 'variable', 'simple', etc.).
	 * @param array  $atts         An array containing product_id and variation_id (if applicable).
	 * @param int    $prod_id      The product ID.
	 * @param int    $quantity     The quantity of the product.
	 *
	 * @return array An associative array containing 'posted_data' and 'product_meta'.
	 */
	function wlfmc_process_product_data( $product_type, $atts, $prod_id, $quantity ) {
		$variations = array();
		if ( in_array( $product_type, array( 'variable',  'variation', 'variable-subscription'  ), true ) ) {
			foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'attribute_' !== substr( $key, 0, 10 ) || '' === $value ) {
					continue;
				}
				$variations['attributes'][ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
			}
		}

		try {
			// phpcs:disable WordPress.Security.NonceVerification
			$post = $_POST;
			unset( $_POST['fragments'] );
			unset( $_POST['action'] );
			unset( $_POST['context'] );
			unset( $_POST['add_to_wishlist'] );
			// phpcs:enable WordPress.Security.NonceVerification
			$product_id   = $atts['product_id'] ?? $prod_id;
			$variation_id = $atts['variation_id'] ?? 0;
			$product_meta = (array) apply_filters( 'woocommerce_add_cart_item_data', array(), $product_id, $variation_id, $quantity );
			$_POST        = $post;
		} catch ( Exception $e ) {
			$product_meta = array();
		}
		$product_meta = (array) apply_filters( 'wlfmc_add_cart_item_data', $product_meta, $product_id, $variation_id, $quantity );
		$product_meta = ! empty( $variations ) ? array_merge( $product_meta, $variations ) : $product_meta;
		$post         = wp_unslash( $_POST );// phpcs:ignore WordPress.Security.NonceVerification
		$post         = array_diff_key(
			$post,
			array_flip( wlfmc_remove_args_from_posted_data() )
		);
		$files        = array();
		if ( is_array( $_FILES ) ) {
			foreach ( $_FILES as $k => $file ) {
				if ( '' !== $file['name'] ) {
					$files[ $k ] = array(
						'name'     => $file['name'],
						'size'     => $file['size'],
						'error'    => is_array( $file['name'] ) ? array( 0 ) : 0,
						'tmp_name' => $file['name'],
						'type'     => $file['type'],
					);
				}
			}
		}
		if ( in_array( $product_type, array( 'variable',  'variation', 'variable-subscription'  ), true ) ) {
			foreach ( $post as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'attribute_' === substr( $key, 0, 10 ) && '' === $value ) {
					unset( $post[ $key ] );
				}
			}
		}
		$files       = apply_filters( 'wlfmc_files_posted_data', $files );
		$post        = apply_filters( 'wlfmc_posted_data', $post );
		$posted_data = array();
		if ( ! empty( $post ) || ! empty( $files ) ) {
			$posted_data = array(
				'post'  => $post,
				'files' => $files,
			);
		}

		return array(
			'posted_data'  => $posted_data,
			'product_meta' => $product_meta,
		);
	}
}

if ( ! function_exists( 'wlfmc_enabled_lists' ) ) {
	/**
	 * Reserved wishlist slugs
	 *
	 * @return mixed|void
	 */
	function wlfmc_enabled_lists() {
		return apply_filters( 'wlfmc_enabled_lists', array() );
	}
}

if ( ! function_exists( 'wlfmc_object_id' ) ) {
	/**
	 * Retrieve translated object id, if a translation plugin is active
	 *
	 * @param int    $id Original object id.
	 * @param string $type Object type.
	 * @param bool   $return_original Whether to return original object if no translation is found.
	 * @param string $lang Language to use for translation ().
	 *
	 * @return int Translation id
	 */
	function wlfmc_object_id( $id, $type = 'page', $return_original = true, $lang = null ) {

		// process special value for $lang.
		if ( 'default' === $lang ) {
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // wpml default language.
				global $sitepress;
				$lang = $sitepress->get_default_language();
			} elseif ( function_exists( 'pll_default_language' ) ) { // polylang default language.
				$lang = pll_default_language( 'locale' );
			} else { // cannot determine default language.
				$lang = null;
			}
		}

		// Should work with WPML and PolyLang.
		$id = apply_filters( 'wpml_object_id', $id, $type, $return_original, $lang );

		// Space for additional translations.
		return apply_filters( 'wlfmc_object_id', $id, $type, $return_original, $lang );
	}
}

if ( ! function_exists( 'wlfmc_wpml_object_id' ) ) {
	/**
	 * Get id of post translation in current language
	 *
	 * @param int         $element_id The element ID.
	 * @param string      $element_type The element type.
	 * @param bool        $return_original_if_missing Return original if missing or not.
	 * @param null|string $language_code The language code.
	 *
	 * @return int the translation id
	 */
	function wlfmc_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $language_code );
		} elseif ( function_exists( 'icl_object_id' ) ) {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $language_code );
		} else {
			return $element_id;
		}
	}
}

if ( ! function_exists( 'wlfmc_str_contains' ) ) {
	/**
	 * Determine if a string contains a given substring
	 *
	 * @param string $haystack String.
	 * @param string $needle substring.
	 *
	 * @return bool
	 */
	function wlfmc_str_contains( $haystack, $needle ) {
		if ( function_exists( 'str_contains' ) ) {
			return '' !== $needle && str_contains( $haystack, $needle ) !== false;
		}
		return '' !== $needle && mb_strpos( $haystack, $needle ) !== false;
	}
}

if ( ! function_exists( 'wlfmc_sanitize_svg' ) ) {
	/**
	 * Sanitize svg
	 *
	 * @param string $svg Svg code.
	 * @return false|string
	 * @since 1.3.2
	 */
	function wlfmc_sanitize_svg( string $svg ) {

		$sanitizer = new enshrined\svgSanitize\Sanitizer();

		return $sanitizer->sanitize( $svg );

	}
}

if ( ! function_exists( 'wlfmc_remove_filters' ) ) {
	/**
	 * Allow removing method for a hook when, it's a class method used and class don't have variable, but you know the class name :)
	 *
	 * @link https://github.com/herewithme/wp-filters-extras
	 *
	 * @param string $hook_name Hook name.
	 * @param string $class_name Class name.
	 * @param string $method_name Method name.
	 * @param int    $priority Priority.
	 *
	 * @return false
	 */
	function wlfmc_remove_filters( string $hook_name = '', string $class_name = '', string $method_name = '', int $priority = 0 ): bool {
		global $wp_filter;

		// Take only filters on right hook name and priority.
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}

		// Loop on filters registered.
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method).
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param.
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) === $class_name && $filter_array['function'][1] === $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/).
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'wlfmc_merge_notices' ) ) {
	/**
	 * Merge all validation error notice
	 *
	 * @param string $product_name Formatted product name.
	 *
	 * @return string|void
	 */
	function wlfmc_merge_notices( $product_name ) {
		if ( 0 === wc_notice_count( 'error' ) ) {
			wc_clear_notices();
			return;
		}

		$error_notices = wc_get_notices( 'error' );

		$error_notices = array_map(
			function( $val ) {
				return $val['notice'];
			},
			$error_notices
		);

		// Prevent notices from being output later on.
		wc_clear_notices();
		/* translators: %1$s: product name , %2$: error notice . */
		return wp_kses_post( sprintf( __( 'We couldn\'t add %1$s to the cart because: %2$s', 'wc-wlfmc-wishlist' ), $product_name, implode( ',', $error_notices ) ) );

	}
}

if ( ! function_exists( 'wlfmc_create_default_automations' ) ) {

	/**
	 * Create default automations.
	 */
	function wlfmc_create_default_automations() {
		global $wpdb;
		$default_options = array(
			'email-from-name'    => wp_specialchars_decode( get_option( 'woocommerce_email_from_name' ), ENT_QUOTES ),
			'email-from-address' => sanitize_email( get_option( 'woocommerce_email_from_address' ) ),
			'mail-type'          => 'simple-template',
			'is_special'         => 0,
			'trigger_name'       => 'on-sale',
			'offer_emails'       => array(
				array(
					'enable_email'    => '1',
					'send_after_days' => 0,
					'mail_heading'    => '',
					'mail_subject'    => '',
					'html_content'    => '',
					'html_footer'     => nl2br( __( '{site_name}
{site_description}
If you have any questions, feel free to contact us at <a href="{shop_url}">Shop</a>
<a href="{unsubscribe_url}">Unsubscribe here</a>', 'wc-wlfmc-wishlist' ) ),
				),
				array(
					'enable_email' => '0',
					'send_after_days' => 1,
					'mail_heading'    => '',
					'mail_subject'    => '',
					'html_content'    => '',
					'html_footer'     => nl2br( __( '{site_name}
{site_description}
If you have any questions, feel free to contact us at <a href="{shop_url}">Shop</a>
<a href="{unsubscribe_url}">Unsubscribe here</a>', 'wc-wlfmc-wishlist' ) ),
				),
				array(
					'enable_email' => '0',
					'send_after_days' => 3,
					'mail_heading'    => '',
					'mail_subject'    => '',
					'html_content'    => '',
					'html_footer'     => nl2br( __( '{site_name}
{site_description}
If you have any questions, feel free to contact us at <a href="{shop_url}">Shop</a>
<a href="{unsubscribe_url}">Unsubscribe here</a>', 'wc-wlfmc-wishlist' ) ),
				),
				array(
					'enable_email' => '0',
					'send_after_days' => 5,
					'mail_heading'    => '',
					'mail_subject'    => '',
					'html_content'    => '',
					'html_footer'     => nl2br( __( '{site_name}
{site_description}
If you have any questions, feel free to contact us at <a href="{shop_url}">Shop</a>
<a href="{unsubscribe_url}">Unsubscribe here</a>', 'wc-wlfmc-wishlist' ) ),
				),
				array(
					'enable_email' => '0',
					'send_after_days' => 7,
					'mail_heading'    => '',
					'mail_subject'    => '',
					'html_content'    => '',
					'html_footer'     => nl2br( __( '{site_name}
{site_description}
If you have any questions, feel free to contact us at <a href="{shop_url}">Shop</a>
<a href="{unsubscribe_url}">Unsubscribe here</a>', 'wc-wlfmc-wishlist' ) ),
				),
			),
		);
		$triggers        = array( 'on-sale', 'back-in-stock', 'low-stock', 'price-change' );
		foreach ( $triggers as $trigger ) {
			$count = (int) $wpdb->get_var( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "SELECT count(ID) as count FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro=1 AND is_special=0 AND trigger_name=%s", $trigger )
			);
			if ( 0 < $count ) {
				continue;
			}
			$default_options['trigger_name'] = $trigger;
			$automation_name                 = '';
			switch ( $trigger ) {
				case 'on-sale':
					$automation_name                                    = 'Default On Sale Automation';
					$default_options['offer_emails'][0]['mail_subject'] = __( '{product_name} - A Deal You Can\'t Miss!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][0]['html_content'] = nl2br( __(
						'Hey {user_first_name},

We\'ve got something exciting just for you. Our {product_name} is now on sale, and the price won\'t stay this low for long! Don\'t miss out on this fantastic opportunity to own the {product_name}.

**Regular Price:** {regular_price}
**Sale Price:** {sale_price}

<a href="{product_url}">{product_image}</a>

Ready to seize the deal? <a href="{add_to_cart_url}">Click here to Shop Now</a>

Don\'t hesitate! Act fast and be the first to grab this incredible offer.

Happy shopping!
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][1]['mail_subject'] = __( 'Exclusive Offer: {product_name} is Waiting for You!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][1]['html_content'] = nl2br( __(
						'Hello {user_first_name},

We noticed you\'re eyeing our amazing {product_name}. Good news - it\'s on sale now!

**Regular Price:** {regular_price}
**Sale Price:** {sale_price}

[View Product]({product_link})

Don\'t miss your chance to save big on this fantastic deal. It\'s too good to last forever. Shop now and treat yourself to something special.

Ready to make your purchase? <a href="{add_to_cart_url}">Shop Now</a>

We can\'t wait to see you enjoy your new {product_name}!

Warm regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );


					$default_options['offer_emails'][2]['mail_subject'] = __( 'Hurry! Last Chance to Grab {product_name} on Sale', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][2]['html_content'] = nl2br( __(
						'Hey {user_first_name},

Time is running out! Our {product_name} is still on sale, but the clock is ticking.

**Regular Price:** {regular_price}
**Sale Price:** {sale_price}

[View Product](<a href="{product_url}">{product_name}</a>)

This is your final reminder. Don\'t let this opportunity slip away. Make your purchase now before the sale ends!

Ready to take action? <a href="{add_to_cart_url}">Shop Now</a>

Hurry, we don\'t want you to miss out!

Best regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][3]['mail_subject'] = __( 'Don\'t Regret It! {product_name} Sale Ending Soon', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][3]['html_content'] = nl2br( __(
						'Hello {user_first_name},

We hate to break it to you, but our {product_name} sale is about to end.

**Regular Price:** {regular_price}
**Sale Price:** {sale_price}

[View Product]({product_link})

You don\'t want to look back and wish you had acted sooner. Seize this opportunity and make your purchase now!

Ready to shop? <a href="{add_to_cart_url}">Shop Now</a>

Time is of the essence. Don\'t miss out on owning the {product_name}!

Sincerely,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][4]['mail_subject'] = __( '{product_name} Sale Ends Today', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][4]['html_content'] = nl2br( __(
						'Hey {user_first_name},

Today is your last chance to get the {product_name} at an incredible price.

**Regular Price:** {regular_price}
**Sale Price:** {sale_price}

[View Product](<a href="{product_url}">{product_name}</a>)

Don\'t let this fantastic deal slip through your fingers. Act now before the sale is over!

Ready to take action? <a href="{add_to_cart_url}">Shop Now</a>

We don\'t want you to have regrets, so hurry and make your purchase!

Warmest wishes,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );
					break;
				case 'back-in-stock':
					$automation_name                                    = 'Default Back in Stock Automation';
					$default_options['offer_emails'][0]['mail_subject'] = __( 'Exciting News! {product_name} is back in stock!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][0]['html_content'] = nl2br( __(
						'Hi {user_first_name},

We\'ve got some fantastic news for you! Your favorite product, {product_name}, is back in stock. Our inventory was running low, but we\'ve managed to restock it just for you.

Here\'s a sneak peek of {product_name}:
{product_image}

Click here to check it out <a href="{product_url}">{product_name}</a>
Hurry, this product tends to sell out quickly! Don\'t miss out on the chance to get your hands on it.

Best regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][1]['mail_subject'] = __( '{user_first_name}, don\'t miss out on {product_name}!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][1]['html_content'] = nl2br( __(
						'Hi {user_first_name},

Just a quick reminder that {product_name} is now back in stock. It\'s a customer favorite, and we don\'t want you to miss out on it.

Here\'s a glimpse of {product_name}:
{product_image}

Check it out here <a href="{product_url}">{product_name}</a>This is your chance to grab it before it\'s gone again.

Warm regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][2]['mail_subject'] = __( 'Exclusive Offer: {product_name} + {coupon_amount} Off Just for You!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][2]['html_content'] = nl2br( __(
						'Hi {user_first_name},

We know you\'ve been eyeing {product_name}, and we\'ve got something special for you. As a token of our appreciation, here\'s an exclusive {coupon_amount} discount on {product_name} just for you.

Your exclusive coupon code: {coupon_code}
Expires on: {expiry_date}

Ready to grab {product_name} at a discounted price? Click here <a href="{product_url}">{product_name}</a>

Don\'t miss out on this limited-time offer! We hope to see you soon.

Warmly,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][3]['mail_subject'] = __( 'Grab {product_name} Before It\'s Gone Again!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][3]['html_content'] = nl2br( __(
						'Hi {user_first_name},

Time is running out! {product_name} is flying off the shelves, and we\'d hate for you to miss out.

Here\'s a final look at {product_name}:
{product_image}

Secure your {coupon_amount} discount with code {coupon_code} before it\'s too late. Click here <a href="{product_url}">{product_name}</a>

Don\'t let this opportunity slip through your fingers. Act fast, and make {product_name} yours today.

Best regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][4]['mail_subject'] = __( 'Exciting News! {product_name} is back in stock!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][4]['html_content'] = nl2br( __(
						'Hi {user_first_name},

This is your last reminder that {product_name} is on the verge of selling out once again. We hate to see you miss out on it.

Take one last look at {product_name}:
{product_image}

Shop now before it\'s too late! Click here <a href="{product_url}">{product_name}</a>

Thank you for considering this opportunity. We look forward to serving you soon.

Warmly,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					break;
				case 'low-stock':
					$automation_name                                    = 'Default Low Stock Automation';
					$default_options['offer_emails'][0]['mail_subject'] = __( 'Act Fast to Get Your {product_name}!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][0]['html_content'] = nl2br( __(
						'Dear {user_first_name},

We noticed that one of your favorite items, {product_name}, is running low in stock on {site_name}.

With only a limited quantity left, you don\'t want to miss out on the chance to own this fantastic product. It\'s a must-have for {product_link}.

Go to checkout: {checkout_url}

Remember, it\'s your last chance to purchase at the regular price of {regular_price}. Don\'t let it slip away! Stay tuned for more updates about {product_name}.

Stay trendy,
{site_name}
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][1]['mail_subject'] = __( 'Last Few {product_name} Items Left!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][1]['html_content'] = nl2br( __(
						'Hey {user_first_name},

We hope you\'re having an amazing day! We wanted to remind you that {product_name} is flying off the shelves on {site_name}. There are only a few left, and they\'re going fast!

Don\'t miss out on this opportunity. These items are still available at their regular price of {regular_price}. You can get yours now at {checkout_url}.

Your support means the world to us, and we can\'t wait for you to enjoy your new {product_name}.

Warm regards,
{site_name}
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][2]['mail_subject'] = __( 'Your {product_name} Awaits You!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][2]['html_content'] = nl2br( __(
						'Hello {user_first_name},

We\'re excited to update you about {product_name}. They\'re almost gone, and we don\'t want you to miss out!

The {product_name} is now available at {sale_price}, but you have to act quickly. Grab yours before they\'re all gone!

Hurry to checkout now: {checkout_url}

Thanks for being a loyal {site_name} customer. We appreciate your support.

Best wishes,
{site_name}
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][3]['mail_subject'] = __( 'Exclusive {product_name} Discount Inside!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][3]['html_content'] = nl2br( __(
						'Hello {user_first_name},

Time is running out, and we want to make sure you don\'t miss out on {product_name}! We\'re offering you an exclusive {coupon_amount} discount for your loyalty.

Use code {coupon_code} at checkout to get your {product_name} at an incredible price of {new_price}. Don\'t let this deal slip away!

Get your {product_name} now: {checkout_url}

Thanks for choosing {site_name} for your shopping needs!

Happy shopping,
{site_name}
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][4]['mail_subject'] = __( 'Last Chance to Save on {product_name}!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][4]['html_content'] = nl2br( __(
						'Hi {user_first_name},

This is your last chance to snag a great deal on {product_name}! With just a few left in stock, you don\'t want to miss out.

Use code {coupon_code} to enjoy a {coupon_amount} discount on {product_name} before it\'s too late. Your friends will envy your style!

Grab your {product_name} at the discounted price now: {checkout_url}

Thank you for being a part of {site_name}. We look forward to serving you again soon.

Warm regards,
{site_name}
',
						'wc-wlfmc-wishlist'
					) );
					break;
				case 'price-change':
					$automation_name                                    = 'Default Price Change Automation';
					$default_options['offer_emails'][0]['mail_subject'] = __( 'Exciting News About {product_name}', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][0]['html_content'] = nl2br( __(
						'Hey there, {user_first_name}!

We\'ve got some fantastic news for you. We\'ve recently adjusted the price of {product_name}, and we couldn\'t wait to share it with you.

<a href="{product_url}">{product_image}</a>

**Old Price:** {old_price}
**New Price:** {new_price}

Ready to make it yours? <a href="{product_url}">Check it out now</a>

Stay tuned for more exciting updates!

Warm regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][1]['mail_subject'] = __( 'Last Chance: Grab {product_name} Before It\'s Gone!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][1]['html_content'] = nl2br( __(
						'Hi {user_first_name},

We noticed you\'ve been eyeing {product_name}, and we don\'t want you to miss out on this fantastic deal. The clock is ticking, and stock is limited!

<a href="{product_url}">{product_image}</a>

**New Price:** {new_price}

Don\'t wait, act now! <a href="{product_url}">{product_name}</a>

Best wishes,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][2]['mail_subject'] = __( '{user_first_name}, See Why Others Love {product_name}!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][2]['html_content'] = nl2br( __(
						'Hey {user_first_name},

The excitement around {product_name} is growing, and we wanted to share what our happy customers are saying:

"I can\'t believe I got {product_name} at this price! It\'s a game-changer."
- Jerry Montum

<a href="{product_url}">{product_image}</a>

**New Price:** {new_price}

Ready to join the delighted customers? <a href="{add_to_cart_url}">Shop Now</a>

Sincerely,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][3]['mail_subject'] = __( 'Exclusive Offer: Save Extra on {product_name} Today!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][3]['html_content'] = nl2br( __(
						'Hello {user_first_name},

We know you\'ve been considering {product_name}, and we appreciate your interest. As a token of our appreciation, here\'s an exclusive discount just for you:

**Use Code:** {coupon_code}
**Discount:** {coupon_amount}
**Expires:** {expiry_date}

Hurry and grab this special offer now: <a href="{product_url}">{product_name}</a>

Warm regards,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );

					$default_options['offer_emails'][4]['mail_subject'] = __( 'Last Chance! Don\'t Miss Out on {product_name} Savings!', 'wc-wlfmc-wishlist' );
					$default_options['offer_emails'][4]['html_content'] = nl2br( __(
						'Hi {user_first_name},

This is it – your last opportunity to save big on {product_name}. Time is running out, and stock is limited. Don\'t let this deal slip away!

{product_image}

**New Price:** {sale_price}

Claim your savings now: <a href="{product_url}">{product_name}</a>

Your satisfaction is our priority!

Best wishes,
The {site_name} Team
',
						'wc-wlfmc-wishlist'
					) );
					break;
			}
			$default_options['offer_emails'][0]['mail_heading'] = $default_options['offer_emails'][0]['mail_subject'];
			$default_options['offer_emails'][1]['mail_heading'] = $default_options['offer_emails'][1]['mail_subject'];
			$default_options['offer_emails'][2]['mail_heading'] = $default_options['offer_emails'][2]['mail_subject'];
			$default_options['offer_emails'][3]['mail_heading'] = $default_options['offer_emails'][3]['mail_subject'];
			$default_options['offer_emails'][4]['mail_heading'] = $default_options['offer_emails'][4]['mail_subject'];

			$wpdb->insert( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->wlfmc_wishlist_automations,
				array(
					'automation_name' => $automation_name,
					'is_special'      => 0,
					'trigger_name'    => $trigger,
					'is_active'       => 1,
					'is_pro'          => 1,
					'options'         => serialize( $default_options ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				),
				array( '%s', '%d', '%s', '%d', '%d', '%s' )
			);
		}
	}
}

/**
 * ========================  Cookie Functions =================================
 */

if ( ! function_exists( 'wlfmc_get_cookie_expiration' ) ) {
	/**
	 * Returns default expiration for wishlist cookie
	 *
	 * @return int Number of seconds the cookie should last.
	 */
	function wlfmc_get_cookie_expiration() {
		return intval( apply_filters( 'wlfmc_cookie_expiration', 60 * 60 * 24 * 30 ) );
	}
}

if ( ! function_exists( 'wlfmc_setcookie' ) ) {
	/**
	 * Create a cookie.
	 *
	 * @param string $name Cookie name.
	 * @param mixed  $value Cookie value.
	 * @param int    $time Cookie expiration time.
	 * @param bool   $secure Whether cookie should be available to secured connection only.
	 * @param bool   $httponly Whether cookie should be available to HTTP request only (no js handling).
	 *
	 * @return bool
	 */
	function wlfmc_setcookie( $name, $value = array(), $time = null, $secure = false, $httponly = false ) {
		if ( ! apply_filters( 'wlfmc_set_cookie', true ) || empty( $name ) ) {
			return false;
		}

		$time = null !== $time ? $time : time() + wlfmc_get_cookie_expiration();

		$value      = wp_json_encode( stripslashes_deep( $value ) );
		$expiration = apply_filters( 'wlfmc_cookie_expiration_time', $time ); // Default 30 days.

		$_COOKIE[ $name ] = $value;
		wc_setcookie( $name, $value, $expiration, $secure, $httponly );

		return true;
	}
}

if ( ! function_exists( 'wlfmc_getcookie' ) ) {
	/**
	 * Retrieve the value of a cookie.
	 *
	 * @param string $name Cookie name.
	 *
	 * @return mixed
	 */
	function wlfmc_getcookie( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			return json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ), true );
		}

		return array();
	}
}

if ( ! function_exists( 'wlfmc_destroycookie' ) ) {
	/**
	 * Destroy a cookie.
	 *
	 * @param string $name Cookie name.
	 *
	 * @return void
	 */
	function wlfmc_destroycookie( $name ) {
		wlfmc_setcookie( $name, array(), time() - 3600 );
	}
}

