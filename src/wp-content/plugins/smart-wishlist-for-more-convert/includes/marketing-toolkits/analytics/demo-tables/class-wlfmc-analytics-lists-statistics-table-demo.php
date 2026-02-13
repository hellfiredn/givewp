<?php
/**
 * User lists Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert Premium
 * @version 1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WLFMC_Analytics_Lists_Statistics_Table_Demo' ) ) {
	/**
	 * Analytics prospects table
	 */
	class WLFMC_Analytics_Lists_Statistics_Table_Demo extends WP_List_Table {


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'list_statistics',     // Singular name of the listed records.
					'plural'   => 'lists_statistics',    // Plural name of the listed records.
					'ajax'     => false, // should this table support ajax?
				)
			);

		}

		/**
		 * Get columns
		 *
		 * @return array
		 */
		public function get_columns(): array {
			return array(
				'name'          => __( 'Lists', 'wc-wlfmc-wishlist' ),
				'user_count'    => __( 'Users', 'wc-wlfmc-wishlist' ),
				'product_count' => __( 'Products', 'wc-wlfmc-wishlist' ),
				'total_sales'   => __( 'Total Sales', 'wc-wlfmc-wishlist' ),
				'sale_rate'     => __( 'Sale Rate', 'wc-wlfmc-wishlist' ),
				'filter'        => __( 'Filter', 'wc-wlfmc-wishlist' ),

			);
		}

		/**
		 * Handles the default column output.
		 *
		 * @param object $item        The current item.
		 * @param string $column_name The current column name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'name':
					switch ( $item['list_slug'] ) {
						case 'wishlist':
							return __( 'Wishlist', 'wc-wlfmc-wishlist' );
						case 'lists':
							return __( 'Multi-list', 'wc-wlfmc-wishlist' );
						case 'waitlist':
							return __( 'All Waitlist', 'wc-wlfmc-wishlist' );
						case 'on-sale':
							return __( '-- On Sale', 'wc-wlfmc-wishlist' );
						case 'back-in-stock':
							return __( '-- Back in Stock', 'wc-wlfmc-wishlist' );
						case 'low-stock':
							return __( '-- Low Stock', 'wc-wlfmc-wishlist' );
						case 'price-change':
							return __( '-- Price Change', 'wc-wlfmc-wishlist' );
						case 'save-for-later':
							return __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' );
						case 'abandoned-cart':
							return __( 'Abandonment Cart', 'wc-wlfmc-wishlist' );
						case 'all-lists':
							return __( 'All Lists', 'wc-wlfmc-wishlist' );
						default:
							return $item['list_slug'];
					}
				case 'total_sales':
					return wc_price( $item[ $column_name ] );
				case 'filter':
					return '<a class="btn-secondary center-align small-btn" href="' . add_query_arg(
						array(
							'page'      => 'mc-analytics',
							'tab'       => 'lists',
							'type'      => 'class',
							'list_type' => $item['list_slug'],
						),
						admin_url( 'admin.php' )
					) . '"><span class="dashicons dashicons-filter"></span> ' . __( 'Filter', 'wc-wlfmc-wishlist' ) . '</a>';
				case 'sale_rate':
					if ( isset( $item['total'] ) && floatval( $item['total_sales'] > 0 ) ) {
						return round( $item['total_sales'] / $item['total'], 4 ) * 100 . '%';
					} else {
						return 0;
					}
				case 'user_count':
					return intval( $item[ $column_name ] ) > 0 ? '<a href="' . esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'page'         => 'mc-analytics',
									'tab'          => 'users',
									'type'         => 'class',
									'segment_type' => 'list_type',
									'list_type'    => $item['list_slug'],
								),
								admin_url( 'admin.php' )
							),
							'wlfmc_new_segment'
						)
					) . '">' . esc_attr( $item[ $column_name ] ) . '</a>' : $item[ $column_name ];
				case 'product_count':
					return intval( $item[ $column_name ] ) > 0 ? '<a href="' . esc_url(
						add_query_arg(
							array(
								'page'      => 'mc-analytics',
								'tab'       => 'products',
								'type'      => 'class',
								'list_type' => $item['list_slug'],
							),
							admin_url( 'admin.php' )
						)
					) . '">' . esc_attr( $item[ $column_name ] ) . '</a>' : $item[ $column_name ];
				default:
					return $item[ $column_name ];
			}
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {
			// sets pagination arguments.

			// sets columns headers.
			$columns               = $this->get_columns();
			$this->_column_headers = array( $columns, array(), array() );
			// retrieve data for table.
			$this->items = array(
				array(
					'list_slug'     => 'wishlist',
					'user_count'    => 50,
					'product_count' => 100,
					'total_sales'   => 325.000,
					'total'         => 9122.000,
				),
				array(
					'list_slug'     => 'lists',
					'user_count'    => 40,
					'product_count' => 115,
					'total_sales'   => 215.000,
					'total'         => 2195.000,
				),
				array(
					'list_slug'     => 'save-for-later',
					'user_count'    => 65,
					'product_count' => 210,
					'total_sales'   => 405.000,
					'total'         => 1300.000,
				),
				array(
					'list_slug'     => 'waitlist',
					'user_count'    => 95,
					'product_count' => 150,
					'total_sales'   => 100.000,
					'total'         => 1518.000,
				),
			);

		}


	}

}
