<?php
/**
 * Smart Wishlist Privacy
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wlfmc_Privacy' ) ) {
	/**
	 * Plugin Privacy
	 */
	class Wlfmc_Privacy {

		/**
		 * Single instance of the class
		 *
		 * @var Wlfmc_Privacy
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'add_privacy_message' ) );

			// Add message in a specific section.
			add_filter( 'wlfmc_privacy_guide_content', array( $this, 'add_content_in_section' ), 10, 2 );
			// set up wishlist data exporter.
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );

			// set up wishlist data eraser.
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
		}

		/**
		 * Adds the privacy message on Wlfmc privacy page.
		 */
		public function add_privacy_message() {
			if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
				$content = $this->get_privacy_html();

				if ( $content ) {
					$title = apply_filters( 'wlfmc_privacy_policy_guide_title', _x( 'MC Woocommerce Wishlist', 'Privacy Policy Guide Title', 'wc-wlfmc-wishlist' ) );
					wp_add_privacy_policy_content( $title, $content );
				}
			}
		}

		/**
		 * Get the privacy message.
		 *
		 * @return string
		 */
		public function get_privacy_html() {

			$content = wlfmc_get_template(
				'admin/mc-policy-content.php',
				array(
					'sections' => $this->get_sections(),
				),
				true
			);

			return apply_filters( 'wlfmc_privacy_policy_content_html', $content );
		}

		/**
		 * Get the sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			return apply_filters(
				'wlfmc_privacy_policy_content_sections',
				array(
					'general'           => array(
						'tutorial'    => _x( 'This sample language includes the basics around what personal data your store may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store will vary. We recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'Privacy Policy Content', 'wc-wlfmc-wishlist' ),
						'description' => '',
					),
					'collect_and_store' => array(
						'title' => _x( 'What we collect and store', 'Privacy Policy Content', 'wc-wlfmc-wishlist' ),
					),
					'has_access'        => array(
						'title' => _x( 'Who on our team has access', 'Privacy Policy Content', 'wc-wlfmc-wishlist' ),
					),
					'share'             => array(
						'title' => _x( 'What we share with others', 'Privacy Policy Content', 'wc-wlfmc-wishlist' ),
					),
					'payments'          => array(
						'title' => _x( 'Payments', 'Privacy Policy Content', 'wc-wlfmc-wishlist' ),
					),
				)
			);
		}

		/**
		 * Retrieves privacy example text for wishlist plugin
		 *
		 * @param string $content Content of the Section.
		 * @param string $section Section of the message to retrieve.
		 *
		 * @return string Privacy message
		 */
		public function add_content_in_section( $content, $section ): string {
			$content = '';

			switch ( $section ) {
				case 'collect_and_store':
					$content = '<p>' . __( 'While you visit our site, we’ll track:', 'wc-wlfmc-wishlist' ) . '</p>' .
								'<ul>' .
									'<li>' . __( 'Products you’ve added to different lists: we’ll use this to show you and other users your favourite products, and to create targeted email campaigns.', 'wc-wlfmc-wishlist' ) . '</li>' .
									'<li>' . __( 'lists you’ve created: we’ll keep track of the lists you create, and make them visible to the store staff', 'wc-wlfmc-wishlist' ) . '</li>' .
								'</ul>' .
								'<p>' . __( 'We’ll also use cookies to keep track of list contents while you’re browsing our site.', 'wc-wlfmc-wishlist' ) . '</p>';
					break;
				case 'has_access':
					$content = '<p>' . __( 'Members of our team have access to the information you provide us with. For example, both Administrators and Shop Managers can access:', 'wc-wlfmc-wishlist' ) . '</p>' .
								'<ul>' .
									'<li>' . __( '    lists details, such as products added, date of addition, name and privacy settings of your lists', 'wc-wlfmc-wishlist' ) . '</li>' .
								'</ul>' .
								'<p>' . __( 'Our team members have access to this information to offer you better deals for the products you love.', 'wc-wlfmc-wishlist' ) . '</p>';
					break;
				case 'share':
				case 'payments':
				default:
					break;
			}

			return apply_filters( 'wlfmc_privacy_policy_content', $content, $section );
		}


		/**
		 * Register exporters for wishlist plugin
		 *
		 * @param array $exporters Array of currently registered exporters.
		 * @return array Array of filtered exporters
		 */
		public function register_exporter( $exporters ) {
			$exporters['wlfmc_exporter'] = array(
				'exporter_friendly_name' => __( 'Customer wishlists', 'wc-wlfmc-wishlist' ),
				'callback'               => array( $this, 'wishlist_data_exporter' ),
			);

			return $exporters;
		}

		/**
		 * Register eraser for wishlist plugin
		 *
		 * @param array $erasers Array of currently registered erasers.
		 * @return array Array of filtered erasers
		 */
		public function register_eraser( $erasers ) {
			$erasers['wlfmc_eraser'] = array(
				'eraser_friendly_name' => __( 'Customer wishlists', 'wc-wlfmc-wishlist' ),
				'callback'             => array( $this, 'wishlist_data_eraser' ),
			);

			return $erasers;
		}

		/**
		 * Export user wishlists (only available for authenticated users' wishlist)
		 *
		 * @param string $email_address Email of the users that requested export.
		 * @param int    $page Current page processed.
		 * @return array Array of data to export
		 */
		public function wishlist_data_exporter( $email_address, $page ) {
			$done           = true;
			$page           = (int) $page;
			$offset         = 10 * ( $page - 1 );
			$user           = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			$data_to_export = array();
			if ( $user instanceof WP_User ) {
				$customer_id = wlfmc_get_customer_id_by_user( $user->ID );
				$customer    = $customer_id ? wlfmc_get_customer( $customer_id ) : false;
			} else {
				$customers = WLFMC_Wishlist_Factory::search_customer(
					array(
						'user_id'        => false,
						'session_id'     => false,
						'email'          => $email_address,
						'email_verified' => 1,
					)
				);
				$customer  = ! empty( $customers ) ? $customers[0] : false;
			}
			if ( $customer ) {
				$wishlists = WLFMC_Wishlist_Factory::get_wishlists(
					array(
						'limit'            => 10,
						'offset'           => $offset,
						'show_empty'       => false,
						'customer_id'      => $customer->get_id(),
						'orderby'          => 'ID',
						'order'            => 'ASC',
						'return_wishlists' => true,
					)
				);

				if ( 0 < count( $wishlists ) ) {
					foreach ( $wishlists as $wishlist ) {
						$data_to_export[] = array(
							'group_id'    => 'wlfmc_' . $wishlist->get_type(),
							'group_label' => $wishlist->get_formatted_name(),
							'item_id'     => 'wishlist-' . $wishlist->get_id(),
							'data'        => $this->get_wishlist_personal_data( $wishlist ),
						);
					}
					$done = 10 > count( $wishlists );
				} else {
					$done = true;
				}
			}

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Deletes user wishlists (only available for authenticated users' wishlist)
		 *
		 * @param string $email_address Email of the users that requested export.
		 * @param int    $page Current page processed.
		 * @return array Result of the operation
		 */
		public function wishlist_data_eraser( $email_address, $page ) {
			global $wpdb;

			$page   = (int) $page;
			$offset = 10 * ( $page - 1 );
			$user   = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			if ( $user instanceof WP_User ) {
				$customer_id = wlfmc_get_customer_id_by_user( $user->ID );
				$customer    = $customer_id ? wlfmc_get_customer( $customer_id ) : false;
			} else {
				$customers = WLFMC_Wishlist_Factory::search_customer(
					array(
						'user_id'        => false,
						'session_id'     => false,
						'email'          => $email_address,
						'email_verified' => 1,
					)
				);
				$customer  = ! empty( $customers ) ? $customers[0] : false;
			}
			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			if ( ! $customer ) {
				return $response;
			}

			$customer_id = $customer->get_id();

			$wishlists = WLFMC_Wishlist_Factory::get_wishlists(
				array(
					'limit'            => 10,
					'offset'           => $offset,
					'show_empty'       => false,
					'customer_id'      => $customer_id,
					'orderby'          => 'ID',
					'order'            => 'ASC',
					'return_wishlists' => true,
				)
			);

			if ( 0 < count( $wishlists ) ) {
				foreach ( $wishlists as $wishlist ) {
					if ( apply_filters( 'wlfmc_privacy_erase_wishlist_personal_data', true, $wishlist ) ) {

						do_action( 'wlfmc_privacy_before_remove_wishlist_personal_data', $wishlist );

						$wishlist->delete();

						do_action( 'wlfmc_privacy_remove_wishlist_personal_data', $wishlist );

						/* Translators: %s Order number. */
						$response['messages'][]    = sprintf( __( 'Removed wishlist %s.', 'wc-wlfmc-wishlist' ), $wishlist->get_token() );
						$response['items_removed'] = true;
					} else {
						/* Translators: %s Order number. */
						$response['messages'][]     = sprintf( __( 'Wishlist %s has been retained.', 'wc-wlfmc-wishlist' ), $wishlist->get_token() );
						$response['items_retained'] = true;
					}
				}
				$response['done'] = 10 > count( $wishlists );
			} else {
				$response['done'] = true;
			}
			if ( $response['done'] ) {
				if ( apply_filters( 'wlfmc_privacy_erase_analytics_personal_data', true, $customer ) ) {
					$wpdb->delete( $wpdb->wlfmc_wishlist_analytics, array( 'customer_id' => $customer_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$response['messages'][] = __( 'Removed Mc Wishlist Analytics data.', 'wc-wlfmc-wishlist' );
				} else {
					$response['messages'][]     = __( 'Mc Wishlist Analytics data has been retained.', 'wc-wlfmc-wishlist' );
					$response['items_retained'] = true;
				}
				if ( apply_filters( 'wlfmc_privacy_erase_offers_personal_data', true, $customer ) ) {
					$wpdb->delete( $wpdb->wlfmc_wishlist_offers, array( 'customer_id' => $customer_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$response['messages'][] = __( 'Removed Mc Wishlist Offer & Automation data.', 'wc-wlfmc-wishlist' );
				} else {
					$response['messages'][]     = __( 'Mc Wishlist Offer & Automation data has been retained.', 'wc-wlfmc-wishlist' );
					$response['items_retained'] = true;
				}

				if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
					if ( apply_filters( 'wlfmc_privacy_erase_campaign_personal_data', true, $customer ) ) {
						$wpdb->delete( $wpdb->wlfmcpro_campaign_items, array( 'customer_id' => $customer_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
					$response['messages'][] = __( 'Removed Mc Wishlist Campaign data.', 'wc-wlfmc-wishlist' );

				} else {
					$response['messages'][]     = __( 'Mc Wishlist Campaign data has been retained.', 'wc-wlfmc-wishlist' );
					$response['items_retained'] = true;
				}

				if ( apply_filters( 'wlfmc_privacy_erase_customers_personal_data', true, $customer ) ) {
					$wpdb->delete( $wpdb->wlfmc_wishlist_customers, array( 'customer_id' => $customer_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
						$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmcpro_filters SET customer_ids = null , updated_at = null WHERE FIND_IN_SET( %d, customer_ids) > 0", $customer_id ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					}
					$response['messages'][] = __( 'Removed Mc Wishlist Customer.', 'wc-wlfmc-wishlist' );
				} else {
					$response['messages'][]     = __( 'Mc Wishlist Customer data has been retained.', 'wc-wlfmc-wishlist' );
					$response['items_retained'] = true;
				}
			}

			return $response;
		}

		/**
		 * Retrieves data to export for each user's wishlist
		 *
		 * @param WLFMC_Wishlist $wishlist Wishlist.
		 * @return array Data to export
		 */
		protected function get_wishlist_personal_data( $wishlist ) {
			$personal_data = array();

			$props_to_export = apply_filters(
				'wlfmc_privacy_export_wishlist_personal_data_props',
				array(
					'wishlist_token'   => __( 'Token', 'wc-wlfmc-wishlist' ),
					'wishlist_url'     => __( 'Wishlist URL', 'wc-wlfmc-wishlist' ),
					'wishlist_name'    => __( 'Title', 'wc-wlfmc-wishlist' ),
					'dateadded'        => _x( 'Created on', 'date when wishlist was created', 'wc-wlfmc-wishlist' ),
					'wishlist_privacy' => __( 'Visibility', 'wc-wlfmc-wishlist' ),
					'items'            => __( 'Items added', 'wc-wlfmc-wishlist' ),
				),
				$wishlist
			);

			foreach ( $props_to_export as $prop => $name ) {
				$value = '';

				switch ( $prop ) {
					case 'items':
						$item_names = array();
						$items      = $wishlist->get_items();

						foreach ( $items as $item ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							$item_name = $product->get_name() . ' x ' . $item['quantity'];

							if ( $item->get_date_added() ) {
								$item_name .= ' (on: ' . $item->get_date_added() . ')';
							}

							$item_names[] = $item_name;
						}

						$value = implode( ', ', $item_names );
						break;
					case 'wishlist_url':
						$wishlist_url = $wishlist->get_url();

						$value = sprintf( '<a href="%1$s">%1$s</a>', $wishlist_url );
						break;
					case 'wishlist_name':
						$value = $wishlist->get_formatted_name();
						break;
					case 'dateadded':
						$value = $wishlist->get_date_added();
						break;
					case 'wishlist_privacy':
						$value = $wishlist->get_formatted_privacy();
						break;
					default:
						if ( isset( $wishlist[ $prop ] ) ) {
							$value = $wishlist[ $prop ];
						}
						break;
				}

				$value = apply_filters( 'wlfmc_privacy_export_wishlist_personal_data_prop', $value, $prop, $wishlist );

				if ( $value ) {
					$personal_data[] = array(
						'name'  => $name,
						'value' => $value,
					);
				}
			}

			$personal_data = apply_filters( 'wlfmc_privacy_export_wishlist_personal_data', $personal_data, $wishlist );

			return $personal_data;
		}


		/**
		 * Returns single instance of the class
		 *
		 * @return Wlfmc_Privacy
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}

/**
 * Unique access to instance of Wlfmc_Privacy class
 *
 * @return Wlfmc_Privacy
 */
function Wlfmc_Privacy() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Wlfmc_Privacy::get_instance();
}
