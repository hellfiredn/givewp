<?php
/**
 * Static class that will handle all ajax calls for the list.
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Ajax_Handler' ) ) {
	/**
	 * Woocommerce Smart Wishlist Ajax Handler
	 */
	class WLFMC_Ajax_Handler {
		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 * @version 1.3.3
		 */
		public static function init() {

			add_filter( 'wlfmc_ajax_add_return_params', array( 'WLFMC_Ajax_Handler', 'add_load_automation_nonce' ) );
			// add to wishlist.
			add_action( 'wp_ajax_wlfmc_add_to_wishlist', array( 'WLFMC_Ajax_Handler', 'add_to_wishlist' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_add_to_wishlist', array( 'WLFMC_Ajax_Handler', 'add_to_wishlist' ) );

			// add to cart.
			add_action( 'wp_ajax_wlfmc_add_to_cart', array( 'WLFMC_Ajax_Handler', 'add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_add_to_cart', array( 'WLFMC_Ajax_Handler', 'add_to_cart' ) );

			// remove from wishlist.
			add_action( 'wp_ajax_wlfmc_remove_from_wishlist', array( 'WLFMC_Ajax_Handler', 'remove_from_wishlist' ) );
			add_action(
				'wp_ajax_nopriv_wlfmc_remove_from_wishlist',
				array(
					'WLFMC_Ajax_Handler',
					'remove_from_wishlist',
				)
			);

			// remove from wishlist (button).
			add_action( 'wp_ajax_wlfmc_delete_item', array( 'WLFMC_Ajax_Handler', 'delete_item' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_delete_item', array( 'WLFMC_Ajax_Handler', 'delete_item' ) );

			// update item quantity.
			add_action( 'wp_ajax_wlfmc_update_item_quantity', array( 'WLFMC_Ajax_Handler', 'update_item_quantity' ) );
			add_action(
				'wp_ajax_nopriv_wlfmc_update_item_quantity',
				array(
					'WLFMC_Ajax_Handler',
					'update_item_quantity',
				)
			);

			// Change layouts.
			add_action( 'wp_ajax_wlfmc_change_layout', array( 'WLFMC_Ajax_Handler', 'change_layout' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_change_layout', array( 'WLFMC_Ajax_Handler', 'change_layout' ) );

			// load fragments.
			add_action( 'wp_ajax_wlfmc_load_fragments', array( 'WLFMC_Ajax_Handler', 'load_fragments' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_load_fragments', array( 'WLFMC_Ajax_Handler', 'load_fragments' ) );

			// load automations.
			add_action( 'wp_ajax_wlfmc_load_automations', array( 'WLFMC_Ajax_Handler', 'load_automations' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_load_automations', array( 'WLFMC_Ajax_Handler', 'load_automations' ) );

			// wp_loaded ajax_mode.
			add_action( 'wp_loaded', array( 'WLFMC_Ajax_Handler', 'wp_loaded_action' ), 0 );

			// Create wishlist page.
			add_action( 'wp_ajax_wlfmc_create_wishlist_page', array( 'WLFMC_Ajax_Handler', 'ajax_create_page_callback' ) );

			// update plugin tables.
			add_action( 'wp_ajax_wlfmc_update_table_database', array( 'WLFMC_Ajax_Handler', 'ajax_update_table_database_callback' ) );

			// Change layouts.
			add_action( 'wp_ajax_wlfmc_change_gdpr_status', array( 'WLFMC_Ajax_Handler', 'change_gdpr_status' ) );
			add_action( 'wp_ajax_nopriv_wlfmc_change_gdpr_status', array( 'WLFMC_Ajax_Handler', 'change_gdpr_status' ) );

		}

		/**
		 * Add load automation nonce to ajax response
		 *
		 * @param array $params ajax params.
		 * @version 1.7.6
		 * @return array
		 */
		public static function add_load_automation_nonce( $params ) {
			$params['load_automation_nonce'] = wp_create_nonce( 'wlfmc_load_automations' );
			return $params;
		}

		/**
		 * Manage all wp_loaded ajax mode actions.
		 *
		 * @return false|void
		 * @since 1.3.3
		 */
		public static function wp_loaded_action() {
			if ( is_null( filter_input( INPUT_POST, 'action' ) ) ) {
				return false;
			}

			$action = ! empty( $_POST['action'] ) ? sanitize_key( $_POST['action'] ) : '';// phpcs:ignore WordPress.Security.NonceVerification

			if ( ! in_array(
				$action,
				array(
					'wlfmc_wp_loaded_add_to_wishlist',
					'wlfmc_wp_loaded_load_automations',
					'wlfmc_wp_loaded_remove_from_wishlist',
					'wlfmc_wp_loaded_update_item_quantity',
					'wlfmc_wp_loaded_delete_item',
					'wlfmc_wp_loaded_load_fragments',
					'wlfmc_wp_loaded_change_layout',
					'wlfmc_wp_loaded_change_gdpr_status',
				),
				true
			) ) {
				return false;
			}
			remove_action( 'init', 'woocommerce_add_to_cart_action' );
			remove_action( 'wp_loaded', 'WC_Form_Handler::add_to_cart_action', 20 );

			$action = str_replace( 'wlfmc_wp_loaded_', '', $action );
			self::$action();

		}

		/**
		 * Update plugin tables ajaxify.
		 *
		 * @since 1.6.3
		 * @version 1.7.6
		 */
		public static function ajax_update_table_database_callback() {
			global $wpdb;
			check_ajax_referer( 'ajax-nonce', 'key' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permissions to access this option.', 'wc-wlfmc-wishlist' ) ) );
			}
			$offset  = isset( $_POST['offset'] ) ? absint( wp_unslash( $_POST['offset'] ) ) : 0;
			$version = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : '';
			if ( '1.6.3' === $version ) {
				$limit = 1;
				$total = 7;
				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				switch ( $offset ) {
					case 0:
						// create customers by wishlists.
						$wpdb->query(
							"INSERT INTO $wpdb->wlfmc_wishlist_customers (customer_id, session_id, user_id)
								SELECT DISTINCT 0 as customer_id, session_id, user_id
								FROM $wpdb->wlfmc_wishlists
								WHERE (session_id, user_id) NOT IN (SELECT session_id, user_id FROM $wpdb->wlfmc_wishlist_customers);"
						);
						break;
					case 1:
						// create customers by analytics.
						if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'user_id';" ) ) {
							$wpdb->query(
								"INSERT INTO $wpdb->wlfmc_wishlist_customers (customer_id, session_id, user_id)
								SELECT DISTINCT 0 as customer_id, NULL as session_id, user_id
								FROM $wpdb->wlfmc_wishlist_analytics
								WHERE user_id NOT IN (SELECT DISTINCT user_id FROM $wpdb->wlfmc_wishlist_customers);"
							);
						}
						break;
					case 2:
						// update wishlist and add customer id.
						$wpdb->query(
							"UPDATE $wpdb->wlfmc_wishlists as w
							JOIN $wpdb->wlfmc_wishlist_customers as c ON ( w.session_id = c.session_id AND w.user_id IS NULL) OR ( w.user_id = c.user_id AND w.session_id IS NULL )
							SET w.customer_id = c.customer_id
							WHERE w.customer_id = 0;"
						);
						break;
					case 3:
						// update wishlist items and add customer id base on user_id.
						$wpdb->query(
							"UPDATE $wpdb->wlfmc_wishlist_items as i
		                    JOIN $wpdb->wlfmc_wishlists as w ON i.wishlist_id = w.ID
							SET i.customer_id = w.customer_id
							WHERE i.customer_id = 0;"
						);
						break;
					case 4:
						// update analytics and add customer id.
						if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'user_id';" ) ) {
							$wpdb->query(
								"UPDATE $wpdb->wlfmc_wishlist_analytics as a
							JOIN $wpdb->wlfmc_wishlist_customers as c ON a.user_id = c.user_id
							SET a.customer_id = c.customer_id
							WHERE a.customer_id = 0;"
							);
						}
						break;
					case 5:
						// update offers and add customer id.
						if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'user_id';" ) ) {
							$wpdb->query(
								"UPDATE $wpdb->wlfmc_wishlist_offers as o
								JOIN $wpdb->wlfmc_wishlist_customers as c ON o.user_id = c.user_id
								SET o.customer_id = c.customer_id
								WHERE o.customer_id = 0;"
							);
						}

						break;
					case 6:
						if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'user_id';" ) ) {
							$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers DROP COLUMN `user_id`;" );
						}
						if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'user_id';" ) ) {
							$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_analytics DROP COLUMN `user_id`;" );
						}
						delete_option( 'wlfmc_need_update_tables' );
						$options = new MCT_Options( 'wlfmc_options' );
						$options->update_option( 'wishlist_enable', get_option( 'wlfmc_wishlist_old_status', '1' ) );
						delete_option( 'wlfmc_wishlist_old_status' );
						break;
				}
				// phpcs:enable WordPress.DB.DirectDatabaseQuery
				if ( 7 > $offset ) {
					$total_updated = $offset + 1;
					$percentage    = (int) floor( ( $total_updated / $total ) * 100 );
					$new_offset    = $offset + $limit;
					if ( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_object_id' ) ) {
						if ( version_compare( WLFMC_VERSION, '1.6.9', '>' ) ) {
							update_option( 'wlfmc_need_update_tables', '1.7.0' );
						}
					}
					wp_send_json_success(
						array(
							'offset'     => min( $new_offset, $total ),
							'percentage' => $percentage,
							'message'    => __( 'All tables updated', 'wc-wlfmc-wishlist' ),
						)
					);
				} else {
					wp_send_json_error( array( 'message' => __( 'Tables not found.', 'wc-wlfmc-wishlist' ) ) );
				}
			} elseif ( '1.7.0' === $version ) {
				$results = wp_cache_get( 'wlfmc_update_table_v1_7_0', 'wlfmc-cache' );
				if ( false === $results ) {
					$results = $wpdb->get_col( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						"SELECT DISTINCT prod_id FROM (
						            SELECT prod_id FROM $wpdb->wlfmc_wishlist_items
						            UNION ALL
						            SELECT product_id as prod_id FROM $wpdb->wlfmc_wishlist_offers
						            UNION ALL
						            SELECT prod_id FROM $wpdb->wlfmc_wishlist_analytics
						            UNION ALL
						            SELECT parent_id as prod_id FROM $wpdb->wlfmc_wishlist_items
						        ) AS subquery"
					);
					wp_cache_set( 'wlfmc_update_table_v1_7_0', $results, 'wlfmc-cache' );
				}
				if ( empty( $results ) ) {
					wp_send_json_error( array( 'message' => __( 'Not exists any data.', 'wc-wlfmc-wishlist' ) ) );
				}
				$total   = count( $results );
				$limit   = 10;
				$results = array_slice( $results, $offset, $limit );

				foreach ( $results as $result ) {
					$product_id      = absint( $result );
					$main_product_id = absint( wlfmc_wpml_object_id( $result, 'product', true ) );

					if ( $product_id > 0 && $main_product_id > 0 && $product_id !== $main_product_id ) {
						$wpdb->update( // phpcs:ignore WordPress.DB
							$wpdb->wlfmc_wishlist_items,
							array( 'prod_id' => $main_product_id ),
							array( 'prod_id' => $product_id ),
							array( '%d' ),
							array( '%d' )
						);
						$wpdb->update( // phpcs:ignore WordPress.DB
							$wpdb->wlfmc_wishlist_items,
							array( 'parent_id' => $main_product_id ),
							array( 'parent_id' => $product_id ),
							array( '%d' ),
							array( '%d' )
						);
						$wpdb->update( // phpcs:ignore WordPress.DB
							$wpdb->wlfmc_wishlist_analytics,
							array( 'prod_id' => $main_product_id ),
							array( 'prod_id' => $product_id ),
							array( '%d' ),
							array( '%d' )
						);
						$wpdb->update( // phpcs:ignore WordPress.DB
							$wpdb->wlfmc_wishlist_offers,
							array( 'product_id' => $main_product_id ),
							array( 'product_id' => $product_id ),
							array( '%d' ),
							array( '%d' )
						);
					}
				}
				$remaining = $total - ( $offset + count( $results ) );

				if ( 0 === $remaining ) {
					delete_option( 'wlfmc_need_update_tables' );
					wp_cache_delete( 'wlfmc_update_table_v1_7_0', 'wlfmc-cache' );
					$table_name = $wpdb->prefix . 'icl_string_translations';
					// Check if the table exists.
					if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) { // phpcs:ignore WordPress.DB
						if ( version_compare( WLFMC_VERSION, '1.7.4', '>' ) ) {
							update_option( 'wlfmc_need_update_tables', '1.7.6' );
						}
					}
					wp_send_json_success(
						array(
							'offset'     => 0,
							'percentage' => 100,
							'message'    => __( 'All tables updated', 'wc-wlfmc-wishlist' ),
						)
					);
				}
				if ( $total > $offset || $total < $limit ) {
					$total_updated = $offset + 1;
					$percentage    = (int) floor( ( $total_updated / $total ) * 100 );
					$new_offset    = $offset + $limit;
					wp_send_json_success(
						array(
							'offset'     => min( $new_offset, $total ),
							'percentage' => $percentage,
						)
					);
				} else {
					wp_send_json_error( array( 'message' => __( 'Tables not found.', 'wc-wlfmc-wishlist' ) ) );
				}
			} elseif ( '1.7.6' === $version ) {
				if ( ! function_exists( 'icl_st_is_registered_string' ) ) {
					wp_send_json_error( array( 'message' => __( 'WPML String Translation plugin not activate.', 'wc-wlfmc-wishlist' ) ) );
				}
				if ( defined( 'WLFMC_PREMIUM_VERSION' ) && version_compare( WLFMC_PREMIUM_VERSION, '1.7.6', '<' ) ) {
					wp_send_json_error( array( 'message' => __( 'Please first update the MoreConvert Pro plugin, then update the database tables.', 'wc-wlfmc-wishlist' ) ) );
				}
				$all_texts  = array(
					'global-settings' => array(
						'action_label',
						'action_add_to_cart_label',
						'action_remove_label',
						'apply_label',
						'all_add_to_cart_label',
						'share_tooltip',
						'socials_title',
						'share_popup_title',
						'share_on_label',
						'copy_field_label',
						'copy_button_text',
						'share_on_facebook_tooltip_label',
						'share_on_messenger_tooltip_label',
						'share_on_twitter_tooltip_label',
						'share_on_whatsapp_tooltip_label',
						'share_on_telegram_tooltip_label',
						'share_on_email_tooltip_label',
						'share_on_copy_link_tooltip_label',
						'share_on_download_pdf_tooltip_label',
						// premium version.
						'copy_all_to_list_label',
						'successfully_copy_message',
						'total_price_text',
						'total_added_price_text',
						'total_current_price_text',
						'increase_total_price_marketing_title',
						'increase_total_price_marketing_desc',
						'nochange_total_price_marketing_title',
						'nochange_total_price_marketing_desc',
						'decrease_total_price_marketing_title',
						'decrease_total_price_marketing_desc',
					),
					'button-display'  => array(
						'login_need_text',
						'product_added_text',
						'product_removed_text',
						'already_in_wishlist_text',
						'button_label_view',
						'button_label_add',
						'button_label_remove',
						'button_label_exists',
						'wishlist_page_title',
						'all_add_to_cart_tooltip_label',
						'counter_button_text',
						'counter_total_text',
						'counter_empty_wishlist_content',
					),
					'multi-list'      => array(
						'multi_list_popup_new_list_title',
						'multi_list_popup_edit_list_title',
						'multi_list_popup_delete_list_title',
						'multi_list_popup_delete_list_content',
						'multi_list_popup_list_cancel_label',
						'multi_list_popup_list_save_label',
						'multi_list_popup_list_remove_label',
						'multi_list_popup_new_list_create_label',
						'multi_list_popup_list_input_name_label',
						'multi_list_popup_list_input_name_placeholder',
						'multi_list_popup_list_input_descriptions_label',
						'multi_list_popup_list_input_descriptions_optional',
						'multi_list_popup_list_input_descriptions_placeholder',
						'multi_list_popup_list_input_privacy_label',
						'multi_list_popup_list_input_public_label',
						'multi_list_popup_list_input_private_label',
						'multi_list_move_all_to_list_label',
						'multi_list_popup_addtolist_title',
						'multi_list_popup_movetolist_title',
						'multi_list_popup_copytolist_title',
						'multi_list_popup_addtolist_content',
						'multi_list_popup_manage_label',
						'multi_list_popup_movetolist_content',
						'multi_list_popup_copytolist_content',
						'multi_list_popup_addtolist_empty_list_text',
						'multi_list_popup_addtolist_notfound_list_text',
						'multi_list_popup_addtolist_search_placeholder',
						'multi_list_popup_addtolist_save_label',
						'multi_list_popup_movetolist_save_label',
						'multi_list_popup_copytolist_save_label',
						'multi_list_popup_addtolist_create_label',
						'multi_list_successfully_move_message',
						'multi_list_saved_text',
						'multi_list_login_need_text',
						'multi_list_search_field_title',
						'multi_list_all_lists_label',
						'multi_list_previous_list_label',
						'multi_list_next_list_label',
						'multi_list_all_lists_tab_label',
						'multi_list_empty_list_title',
						'multi_list_empty_list_content',
						'multi_list_all_lists_create_new_list_description',
						'multi_list_all_lists_create_new_list_button_label',
						'multi_list_change_privacy_tooltip',
						'multi_list_edit_list_tooltip',
						'multi_list_open_list_tooltip',
						'multi_list_delete_list_tooltip',
						'multi_list_counter_button_text',
						'multi_list_counter_mini_list_title_text',
						'multi_list_counter_empty_list_content',
						'multi_list_button_label',
						'multi_list_all_add_to_cart_tooltip_label',
					),
					'waitlist'        => array(
						'waitlist_popup_title',
						'waitlist_email_label',
						'waitlist_email_placeholder',
						'waitlist_submit_label',
						'waitlist_back_in_stock_label',
						'waitlist_low_stock_label',
						'waitlist_price_change_label',
						'waitlist_on_sale_label',
						'waitlist_select_list_label',
						'waitlist_activate_email_subject',
						'waitlist_activate_email_content',
						'waitlist_product_added_text',
						'waitlist_product_changed_text',
						'waitlist_product_removed_text',
						'waitlist_email_invalid_text',
						'waitlist_email_sent_text',
						'waitlist_make_a_selection_text',
						'waitlist_login_need_text',
						'waitlist_counter_button_text',
						'waitlist_counter_total_text',
						'counter_empty_waitlist_content',
						'waitlist_button_label',
						'waitlist_page_title',
						'waitlist_all_add_to_cart_tooltip_label',
					),
					'save-for-later'  => array(
						'sfl_popup_remove_title',
						'sfl_popup_remove_content',
						'sfl_popup_save_all_title',
						'sfl_popup_save_all_content',
						'sfl_popup_move_text',
						'sfl_popup_remove_text',
						'sfl_popup_cancel_text',
						'sfl_login_need_text',
						'sfl_product_added_text',
						'sfl_product_removed_text',
						'sfl_already_in_text',
						'sfl_title',
						'sfl_similar_button',
						'sfl_remove_all_label',
						'sfl_button_label_add',
					),
				);
				$options    = new MCT_Options( 'wlfmc_options' );
				$lang       = apply_filters( 'wpml_default_language', null );
				$string_ids = array();
				foreach ( $all_texts as $text_key => $texts ) {
					foreach ( $texts as $name ) {
						$new_name = "[wlfmc_options][texts]$name";
						$old_name = "[wlfmc_options][$text_key]$name";

						$old_string_id = icl_st_is_registered_string( 'admin_texts_wlfmc_options', $old_name );
						if ( $old_string_id ) {
							$new_string_id = icl_st_is_registered_string( 'admin_texts_wlfmc_options', $new_name );
							if ( ! $new_string_id ) {
								try {
									$new_string_id = icl_register_string( 'admin_texts_wlfmc_options', $new_name, $options->get_option( $name ), true, $lang );
									$new_string_id = icl_st_is_registered_string( 'admin_texts_wlfmc_options', $new_name );
								} catch ( Exception $e ) {
									/* translators:  %s: error message */
									wp_send_json_error( array( 'message' => sprintf( __( 'WPML String Translation plugin error %s. navigation to WPML->string translation page and try again.', 'wc-wlfmc-wishlist' ), $e->getMessage() ) ) );
								}
							}
							if ( $new_string_id ) {
								// phpcs:disable WordPress.DB.DirectDatabaseQuery
								$wpdb->update(
									$wpdb->prefix . 'icl_string_translations',
									array( 'string_id' => $new_string_id ),
									array( 'string_id' => $old_string_id ),
									array( '%d' ),
									array( '%d' )
								);

								$st_ids = $wpdb->get_col(
									$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}icl_string_translations WHERE string_id=%d", $new_string_id )
								);
								// phpcs:enable WordPress.DB.DirectDatabaseQuery
								$string_ids[] = $new_string_id;
								if ( ! empty( $st_ids ) ) {
									foreach ( $st_ids as $st_id ) {
										do_action( 'wpml_st_add_string_translation', (int) $st_id );
									}
								}

								icl_update_string_status( $new_string_id );
								icl_unregister_string( 'admin_texts_wlfmc_options', $old_name );
							}
						}
					}
				}

				do_action( 'wpml_st_language_of_strings_changed', $string_ids );
				delete_option( 'wlfmc_need_update_tables' );

				wp_send_json_success(
					array(
						'offset'     => 0,
						'percentage' => 100,
						'message'    => __( 'All tables updated', 'wc-wlfmc-wishlist' ),
						'redirect'   => add_query_arg(
							array(
								'page'    => 'wpml-string-translation/menu/string-translation.php',
								'context' => esc_attr( 'admin_texts_wlfmc_options' ),
							),
							admin_url( 'admin.php' )
						),
					)
				);
			}
			exit;
		}

		/**
		 * Create wishlist page ajaxify.
		 *
		 * @version 1.5.9
		 * @since 1.0.1
		 */
		public static function ajax_create_page_callback() {

			check_ajax_referer( 'ajax-nonce', 'key' );

			delete_option( 'wlfmc_wishlist_page_id' );

			$id = wc_create_page(
				sanitize_title_with_dashes( _x( 'wishlist', 'page_slug', 'wc-wlfmc-wishlist' ) ),
				'wlfmc_wishlist_page_id',
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				'<!-- wp:shortcode -->[wlfmc_wishlist]<!-- /wp:shortcode -->'
			);

			$options = new MCT_Options( 'wlfmc_options' );
			$options->update_option( 'wishlist_page', $id );

			echo wp_json_encode(
				array(
					'success' => true,
				)
			);
			exit;
		}

		/**
		 * Add to cart item
		 *
		 * @return void
		 * @throws Exception If add to cart fails.
		 * @since 1.4.2
		 * @version 1.7.6
		 */
		public static function add_to_cart() {

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wlfmc_add_to_cart_from_wishlist' ) ) {
				wp_send_json( array( 'result' => false ) );
			}
			$item_id     = isset( $_POST['lid'] ) ? absint( wp_unslash( $_POST['lid'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification
			$wishlist_id = isset( $_POST['wid'] ) ? absint( wp_unslash( $_POST['wid'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification

			if ( ! $item_id || ! $wishlist_id ) {
				return;
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$item = $wishlist->get_item( $item_id );

			if ( ! $item ) {
				return;
			}
			$options                  = new MCT_Options( 'wlfmc_options' );
			$remove_after_add_to_cart = 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' );
			$product_id               = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_id', $item->get_product_id(), $item_id );
			$product                  = wc_get_product( $product_id ); // TODO: check worked Properly. old_value =  $item->get_product().
			$meta                     = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_meta', $item->get_product_meta( 'view' ), $item_id );
			$cart_item                = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_cart_item', $item->get_cart_item(), $item_id );
			if ( $product && $product->is_type( 'variable' ) ) {
				wc_add_notice( apply_filters( 'wlfmc_add_to_cart_error_message_for_variable', __( 'you didn\'t select a variation for it', 'wc-wlfmc-wishlist' ), $product ), 'error' );

				$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, '' );
				$redirect_url = apply_filters( 'woocommerce_cart_redirect_after_error', $permalink, $product_id );
				$data         = array(
					'error'       => true,
					'product_url' => $redirect_url,
				);
				wp_send_json( $data );
				return;
			}
			$attributes = array();
			if ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) {
				foreach ( $meta['attributes'] as $key => $value ) {
					if ( '' !== $value ) {
						$attributes[ $key ] = $value;
					}
				}
			}
			$variation_id = 0;
			if ( $product && 'variation' === $product->get_type() ) {
				$variation_id = $product_id;
				$product_id   = $product->get_parent_id();
			}
			$attributes        = apply_filters( 'wlfmc_woocommerce_add_to_cart_attributes', $attributes, $item_id );
			$variation_id      = apply_filters( 'wlfmc_woocommerce_add_to_cart_variation_id', $variation_id, $item_id );
			$quantity          = apply_filters( 'wlfmc_woocommerce_add_to_cart_quantity', $item->get_quantity(), $product, $product_id, $variation_id );
			$passed_validation = apply_filters( 'wlfmc_woocommerce_add_to_cart_validation', true, $product, $meta, $item, $cart_item );
			if ( $passed_validation ) {
				$data          = array();
				$cart_item_key = WLFMC_Frontend()->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item, $item );
				if ( false !== $cart_item_key ) {
					wc_add_to_cart_message( array( $product_id => $quantity ), true );
					if ( $wishlist->is_current_user_owner() ) {
						$variation_id = 0 === $variation_id ? $product_id : $variation_id;
						do_action( 'wlfmc_product_added_to_cart', $wishlist->get_customer_id(), $wishlist->get_id(), $cart_item_key, $variation_id, $quantity, $wishlist->get_type() );
					}
					if ( $remove_after_add_to_cart ) {
						$item->delete();
					}
					ob_start();

					woocommerce_mini_cart();

					$mini_cart = ob_get_clean();

					$data = array(
						'fragments' => apply_filters(
							'woocommerce_add_to_cart_fragments',
							array(
								'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
							)
						),
						'cart_hash' => WC()->cart->get_cart_hash(),
					);
				}
				$data = array_merge(
					$data,
					array(
						'error'   => ! $cart_item_key,
						'message' => sprintf( '<div class="woocommerce-notices-wrapper">%s</div>', wc_print_notices( true ) ),
					)
				);
				wp_send_json( $data );
			} else {

				$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, '' );
				$redirect_url = apply_filters( 'woocommerce_cart_redirect_after_error', $permalink, $product_id );
				// If there was an error adding to the cart, redirect to the product page to show any errors.
				$data = array(
					'error'       => true,
					'product_url' => $redirect_url,
				);
				wp_send_json( $data );
			}
		}

		/**
		 * Add to wishlist from ajax call
		 *
		 * @return void
		 * @version 1.3.3
		 */
		public static function add_to_wishlist() {
			$result = false;
			try {
				$result  = WLFMC()->add();
				$return  = 'true';
				$message = '';

			} catch ( WLFMC_Exception $e ) {
				$return  = $e->getTextualCode();
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			} catch ( Exception $e ) {
				$return  = 'error';
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			}
			$product_id    = isset( $_REQUEST['add_to_wishlist'] ) ? intval( $_REQUEST['add_to_wishlist'] ) : false; // phpcs:ignore WordPress.Security
			$fragments     = isset( $_REQUEST['fragments'] ) ? json_decode( wp_unslash( $_REQUEST['fragments'] ), true ) : false;// phpcs:ignore WordPress.Security
			$found_item    = false;
			$found_in_list = false;
			if ( 'exists' === $return ) {
				$found_in_list = wlfmc_get_wishlist( false );
				$found_item    = $found_in_list ? $found_in_list->get_product( $product_id ) : false;
			}

			$wishlist_url = WLFMC()->get_wc_wishlist_url( 'wishlist', 'last_operation' );

			wp_send_json(
				apply_filters(
					'wlfmc_ajax_add_return_params',
					array(
						'prod_id'      => $product_id,
						'result'       => $return,
						'message'      => $message,
						'fragments'    => self::refresh_fragments( $fragments ),
						'wishlist_url' => $wishlist_url,
						'wishlist_id'  => $result ? $result['wishlist_id'] : ( $found_in_list ? $found_in_list->get_id() : false ),
						'item_id'      => $result ? $result['item_id'] : ( $found_item ? $found_item->get_id() : false ),
						'customer_id'  => $result ? $result['customer_id'] : ( $found_item ? $found_item->get_customer_id() : false ),
					)
				)
			);
		}

		/**
		 *  Add new hook for work automations after product added to list
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public static function load_automations() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'wlfmc_load_automations' ) ) {
				wp_send_json_error();
			}
			$product_id  = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$wishlist_id = isset( $_POST['wishlist_id'] ) ? intval( $_POST['wishlist_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$list_type   = isset( $_POST['list_type'] ) ? sanitize_text_field( wp_unslash( $_POST['list_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$customer_id = isset( $_POST['customer_id'] ) ? intval( $_POST['customer_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! $wishlist_id || ! $product_id || ! $customer_id ) {
				wp_send_json_error();
			}

			do_action( 'wlfmc_load_automations', $product_id, $wishlist_id, $customer_id, $list_type );

			// stops ajax call from further execution (no return value expected on answer body).
			wp_send_json_success();
		}

		/**
		 *  Change wishlist and other lists view layout.
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public static function change_layout() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'wlfmc_change_layout' ) ) {
				wp_send_json_error();
			}
			$new_layout = isset( $_POST['new_layout'] ) ? sanitize_key( $_POST['new_layout'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! in_array( $new_layout, array( 'list', 'grid' ), true ) ) {
				die();
			}
			if ( is_user_logged_in() ) {
				update_user_meta( get_current_user_id(), 'wlfmc_list_layout', $new_layout );
			} else {
				wlfmc_setcookie( 'wlfmc_list_layout', $new_layout );
			}

			// stops ajax call from further execution (no return value expected on answer body).
			wp_send_json_success();
		}

		/**
		 *  Change GDPR Status.
		 *
		 * @since 1.8.8
		 * @return void
		 */
		public static function change_gdpr_status() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'wlfmc_change_gdpr_status' ) ) {
				wp_send_json_error();
			}
			$action      = isset( $_POST['action_type'] ) ? sanitize_key( $_POST['action_type'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$customer_id = absint( wp_unslash( $_POST['cid'] ) );
			if ( ! $customer_id > 0  || ! in_array( $action, array( 'subscribe', 'unsubscribe' ), true ) ) {
				die();
			}

			$customer = wlfmc_get_customer( $customer_id );
			if ( 'unsubscribe' === $action ) {;
				WLFMC_Wishlist_Factory::unsubscribe_customer( $customer );
			} else {
				WLFMC_Wishlist_Factory::subscribe_customer( $customer );
			}

			// stops ajax call from further execution (no return value expected on answer body).
			wp_send_json_success();
		}

		/**
		 * Remove from wishlist from ajax call
		 *
		 * @return void
		 * @version 1.7.6
		 */
		public static function remove_from_wishlist() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'wlfmc_remove_from_wishlist' ) ) {
				wp_send_json_error();
			}
			$fragments = isset( $_REQUEST['fragments'] ) ? wp_unslash( $_REQUEST['fragments'] ) : false;// phpcs:ignore WordPress.Security
			$return    = false;
			$result    = false;
			try {
				$result  = WLFMC()->remove();
				$return  = 'true';
				$message = '';
			} catch ( Exception $e ) {
				$message = $e->getMessage();
			}

			wp_send_json(
				array(
					'result'    => $return,
					'message'   => $message,
					'count'     => $result ? $result['count'] : false,
					'fragments' => self::refresh_fragments( $fragments ),
				)
			);
		}

		/**
		 * Remove item from a wishlist
		 * Differs from remove from wishlist, since this accepts item id instead of product id
		 *
		 * @return void
		 * @version 1.7.9
		 */
		public static function delete_item() {
			$item_id   = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$fragments = isset( $_REQUEST['fragments'] ) ? wp_unslash( $_REQUEST['fragments'] ) : false;// phpcs:ignore WordPress.Security
			$return    = array(
				'result' => false,
			);

			if ( $item_id ) {
				$item = WLFMC_Wishlist_Factory::get_wishlist_item( $item_id );

				if ( $item ) {

					$wishlist = $item->get_wishlist();

					if ( $wishlist && $wishlist->current_user_can( 'remove_from_wishlist' ) ) {
						$item->delete();
						$return = array(
							'result'    => 'true',
							'message'   => '',
							'fragments' => self::refresh_fragments( $fragments ),
						);
					}
				}
			}

			wp_send_json( $return );
		}

		/**
		 * Update quantity of an item in wishlist
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public static function update_item_quantity() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'wlfmc_wishlist_actions_nonce' ) ) {
				wp_send_json_error();
			}
			$wishlist_token = isset( $_POST['wishlist_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wishlist_token'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$product_id     = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$new_quantity   = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
			$cart_item_key  = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$fragments      = isset( $_REQUEST['fragments'] ) ? wp_unslash( $_REQUEST['fragments'] ) : false;// phpcs:ignore WordPress.Security

			if ( ! $wishlist_token || ! $product_id ) {
				die();
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_token );

			if ( ! $wishlist || ! $wishlist->current_user_can( 'update_quantity' ) ) {
				die();
			}

			$item = $wishlist->get_product( $cart_item_key ? $cart_item_key : $product_id );

			if ( ! $item ) {
				die();
			}

			do_action( 'wlfmc_before_item_quantity_update', $cart_item_key, $new_quantity, $item->get_quantity(), $item );

			$item->set_quantity( $new_quantity );
			$item->save();

			wp_send_json( array( 'fragments' => self::refresh_fragments( $fragments ) ) );
			// stops ajax call from further execution (no return value expected on answer body).
			die();
		}

		/**
		 * Generated fragments to replace in the page
		 *
		 * @return void
		 */
		public static function load_fragments() {
			$fragment_json = isset( $_FILES['fragments_file']['tmp_name'] ) && is_uploaded_file( $_FILES['fragments_file']['tmp_name'] ) ? file_get_contents( $_FILES['fragments_file']['tmp_name'] ) : false;// phpcs:ignore
			$fragments     = $fragment_json ? json_decode( $fragment_json, true ) : false;
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // wpml current  language.
				global $sitepress;
				$lang = $sitepress->get_current_language();
			} elseif ( function_exists( 'pll_current_language' ) ) { // polylang current language.
				$lang = pll_current_language();
			} else { // cannot determine current language.
				$lang = null;
			}

			wp_send_json(
				apply_filters(
					'wlfmc_load_fragments',
					array(
						'fragments' => self::refresh_fragments( $fragments ),
						'products'  => self::get_current_items(),
						'lang'      => $lang,
					)
				)
			);
		}

		/**
		 * Generate fragments for the templates that needs to be refreshed after ajax
		 *
		 * @param array|null $fragments Array of fragments to refresh.
		 *
		 * @return array Array of templates to be replaced on the page
		 * @version 1.7.6
		 */
		public static function refresh_fragments( $fragments ): array {
			$result = array();
			if ( ! empty( $fragments ) ) {
				foreach ( $fragments as $id => $options ) {

					$id      = apply_filters(
						'wlfmc_fragment_id',
						str_replace(
							array(
								'.form-group',
								'.on-first-load',
								'.show-list-on-hover',
								'.show-list-on-click',
								'.darkmysite_ignore',
								'.darkmysite_bg_ignore',
								'.darkmysite_processed',
								'.darkmysite_style_all',
								'.darkmysite_style_link',
								'.darkmysite_style_bg',
								'.darkmysite_style_txt_border',
								'.darkmysite_style_border',
								'.darkmysite_style_txt',
								'.darkmysite_style_bg_txt',
								'.darkmysite_style_secondary_bg',
								'.darkmysite_style_bg_border',
								'.darkmysite_style_button',
								'.darkmysite_changed_brightness_and_grayscale',
								'.darkmysite_last_state',
								'.darkmysite_secondary_bg_finder',
								'.darkmysite_dark_mode_enabled',
								'.darkmysite_inverted_inline_svg',
								'.darkmysite_preserved_filter',
								'.darkmysite_preserved_classes',
								'.darkmysite_preserved_color',
							),
							'',
							sanitize_text_field( $id )
						)
					);
					$options = WLFMC_Frontend()->decode_fragment_options( $options );
					$item    = $options['item'] ?? false;
					if ( ! $item ) {
						continue;
					}
					if ( isset( $options['is_cache_enabled'] ) ) {
						// Set is cache disabled for showing wc notices after ajax requests in the wp_loaded mode.
						$options['is_cache_enabled'] = '0';
					}
					switch ( $item ) {
						case 'wishlist_counter':
						case 'wishlist':
							$result[ $id ] = WLFMC_Shortcode::$item( $options );
							break;
						default:
							$result[ $id ] = apply_filters( 'wlfmc_fragment_output', '', $id, $options );
							break;
					}
				}
			}

			return $result;
		}

		/**
		 * Get current items in current user wishlist
		 *
		 * @return array
		 */
		public static function get_current_items(): array {
			$wishlist_data = array();
			$wishlist      = WLFMC_Wishlist_Factory::get_current_wishlist();
			if ( ! empty( $wishlist ) ) {
				$items = $wishlist->get_items();
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$wishlist_data[] = array(
							'product_id'  => $item->get_product_id(),
							'wishlist_id' => $wishlist->get_id(),
							'item_id'     => $item->get_id(),
						);
					}
				}
			}
			return $wishlist_data;
		}
	}
}
WLFMC_Ajax_Handler::init();
