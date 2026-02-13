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

if ( ! class_exists( 'WLFMC_Analytics_Lists_Table_Demo' ) ) {
	/**
	 * Analytics prospects table
	 */
	class WLFMC_Analytics_Lists_Table_Demo extends WP_List_Table {


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'user_list',     // Singular name of the listed records.
					'plural'   => 'user_lists',    // Plural name of the listed records.
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
				'cb'            => '<input type="checkbox" />',
				'username'      => __( 'User', 'wc-wlfmc-wishlist' ),
				'wishlist_name' => __( 'Name', 'wc-wlfmc-wishlist' ),
				'privacy'       => __( 'Privacy', 'wc-wlfmc-wishlist' ),
				'items'         => __( 'Items', 'wc-wlfmc-wishlist' ),
				'type'          => __( 'Type', 'wc-wlfmc-wishlist' ),
				'dateadded'     => __( 'Date', 'wc-wlfmc-wishlist' ),
				'actions'       => __( 'Actions', 'wc-wlfmc-wishlist' ),
			);
		}

		/**
		 * Handles the checkbox column output.
		 *
		 * @param object $item The current item.
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="items[]" value="%s" />',
				$item['ID']
			);
		}

		/**
		 * Handles items.
		 *
		 * @param object $item The current item.
		 */
		public function column_items( $item ) {
			return $item['items'];
		}

		/**
		 * Handles items.
		 *
		 * @param object $item The current item.
		 */
		public function column_username( $item ) {
			return $item['username'];
		}

		/**
		 * Prints column for wishlist creation date
		 *
		 * @param array $item Item to use to print record.
		 * @return string
		 */
		public function column_dateadded( $item ) {
			$row = '';

			if ( isset( $item['dateadded'] ) ) {
				$time_diff = time() - $item['dateadded'];

				if ( $time_diff < DAY_IN_SECONDS ) {
					// translators: 1. Date diff since wishlist creation (EG: 1 hour, 2 seconds, etc...).
					$row = sprintf( __( '%s ago', 'wc-wlfmc-wishlist' ), human_time_diff( $item['dateadded'] ) );
				} else {
					$row = date_i18n( wc_date_format(), $item['dateadded'] );
				}
			}

			return $row;
		}

		/**
		 * Prints column for wishlist privacy
		 *
		 * @param array $item Item to use to print record.
		 * @return string
		 */
		public function column_privacy( $item ) {
			$row = '';

			if ( isset( $item['wishlist_privacy'] ) ) {
				switch ( $item['wishlist_privacy'] ) {
					case 0:
						$row = __( 'Public', 'wc-wlfmc-wishlist' );
						break;
					case 2:
					default:
						$row = __( 'Private', 'wc-wlfmc-wishlist' );
						break;
				}
			}

			return $row;
		}

		/**
		 * Prints column for wishlist name
		 *
		 * @param array $item Item to use to print record.
		 * @return string
		 */
		public function column_wishlist_name( $item ) {
			return ( ! empty( $item['wishlist_name'] ) ) ? $item['wishlist_name'] : '-';
		}

		/**
		 * Prints column for wishlist type
		 *
		 * @param array $item Item to use to print record.
		 * @return string
		 */
		public function column_type( $item ) {

			if ( isset( $item['wishlist_slug'] ) ) {
				switch ( $item['wishlist_slug'] ) {
					case 'wishlist':
						return __( 'Wishlist', 'wc-wlfmc-wishlist' );
					case 'lists':
						return __( 'Multi-list', 'wc-wlfmc-wishlist' );
					case 'waitlist':
						return __( 'Waitlist', 'wc-wlfmc-wishlist' );
					case 'save-for-later':
						return __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' );
					default:
						return isset( $item['is_default'] ) && wlfmc_is_true( $item['is_default'] ) ? __( 'Wishlist', 'wc-wlfmc-wishlist' ) : __( 'Multi-List', 'wc-wlfmc-wishlist' );
				}
			}
			return '';
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns(): array {
			return array(
				'wishlist_name' => array( 'wishlist_name', false ), // true means it's already sorted.
				'username'      => array( 'user_login', false ),
				'privacy'       => array( 'wishlist_privacy', false ),
				'dateadded'     => array( 'dateadded', false ),
			);
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @return array
		 */
		public function get_bulk_actions(): array {
			return array(
				'delete' => __( 'Delete', 'wc-wlfmc-wishlist' ),
			);
		}

		/**
		 * Prints column for wishlist action.
		 *
		 * @param object $item The current item.
		 */
		public function column_actions( $item ) {
			if ( 'save-for-later' === $item['type'] ) {
				return sprintf(
					'<div class="d-flex f-center gap-5"><a href="%s" class="btn-secondary center-align min-pad" ><span class="dashicons dashicons-visibility"></span></a></div>',
					esc_url(
						add_query_arg(
							array(
								'page'      => 'mc-analytics',
								'show-list' => $item['ID'],
							),
							admin_url( 'admin.php' )
						)
					)
				); // phpcs:ignore WordPress.Security.NonceVerification;
			} else {
				return sprintf(
					'<div class="d-flex f-center gap-5"><a href="%s" class="btn-secondary center-align min-pad" ><span class="dashicons dashicons-visibility"></span></a><a href="%s" class="btn-secondary center-align min-pad" target="_blank"><span class="dashicons dashicons-external"></span></a></div>',
					esc_url(
						add_query_arg(
							array(
								'page'      => 'mc-analytics',
								'show-list' => $item['ID'],
							),
							admin_url( 'admin.php' )
						)
					),
					WLFMC()->get_wishlist_url( $item['type'], 'view/' . $item['wishlist_token'] )
				); // phpcs:ignore WordPress.Security.NonceVerification;
			}

		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {
			// sets columns headers.
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			// process bulk actions.
			$this->process_bulk_action();

			// retrieve data for table.
			$dump_array = array();

			$types    = array( 'wishlist', 'waitlist', 'lists', 'save-for-later' );
			$users    = array( 'Alex Turner', 'Emily Rodriguez', 'Jordan Patel', 'Mia Johnson', 'Ryan Thompson', 'Ava Smith', 'Ethan Davis', 'Olivia Carter', 'Mason Lee', 'Sophia Nguyen' );
			$wishlist = array( 'Tech Gadgets Wishlist', 'Cozy Home Essentials', 'Fitness Gear Wishlist', 'Culinary Delights List', 'Wanderlust Adventure Picks', 'Beauty and Skincare Must-Haves', 'Fashion Forward Favorites', 'Home Decor Dreams', 'Bookworm\'s Reading List', 'Gourmet Cooking Wishlist' );
			for ( $i = 0; $i < 10; $i++ ) {
				$type          = $types[ wp_rand( 0, 3 ) ];
				$wishlist_name = ( 'lists' === $type ) ? $wishlist[ $i ] : '';
				$days_ago      = wp_rand( 1, 30 );
				$date_added    = strtotime( "-$days_ago days" );

				$dump_array[] = array(
					'ID'               => $i,
					'username'         => $users[ $i ],
					'wishlist_token'   => 'DEMO',
					'wishlist_slug'    => $type,
					'wishlist_name'    => $wishlist_name,
					'wishlist_privacy' => wp_rand( 0, 2 ),
					'items'            => wp_rand( 1, 20 ),
					'type'             => $type,
					'dateadded'        => $date_added,
				);
			}
			$this->items = $dump_array;

		}

		/**
		 * Displays the search box.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			?>
			<div class="submit" style="float: right; padding: 0;">
				<?php parent::search_box( $text, $input_id ); ?>
			</div>
			<?php
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @param string $which Where to show nav.
		 */
		protected function extra_tablenav( $which ) {

			if ( 'top' === $which ) :
				?>
				<div class="alignleft ">
					<div class="d-flex f-center f-wrap gap-5">
						<div>
							<label class="screen-reader-text" for="list_type"><?php esc_html_e( 'List Type', 'wc-wlfmc-wishlist' ); ?></label>
							<select name="list_type" id="list_type" autocomplete="off">
								<?php
								$lists     = array(
									'all-lists'      => __( 'All Lists', 'wc-wlfmc-wishlist' ),
									'wishlist'       => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
									'lists'          => __( 'Multi-list', 'wc-wlfmc-wishlist' ),
									'waitlist'       => __( 'All Waitlist', 'wc-wlfmc-wishlist' ),
									'on-sale'        => __( '-- On Sale', 'wc-wlfmc-wishlist' ),
									'back-in-stock'  => __( '-- Back in Stock', 'wc-wlfmc-wishlist' ),
									'low-stock'      => __( '-- Low Stock', 'wc-wlfmc-wishlist' ),
									'price-change'   => __( '-- Price Change', 'wc-wlfmc-wishlist' ),
									'save-for-later' => __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' ),
								);
								$list_type = isset( $_REQUEST['list_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['list_type'] ) ) : 'all-lists';// phpcs:ignore WordPress.Security.NonceVerification.Recommended
								foreach ( $lists as $key => $value ) {
									echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $list_type, false ) . '>' . esc_attr( $value ) . "</option>\n";
								}

								?>
							</select>
						</div>
						<div>
							<button type="submit" id="filter-action" class="btn-secondary">
								<?php esc_html_e( 'Filter', 'wc-wlfmc-wishlist' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php
			endif;
		}


	}

}
