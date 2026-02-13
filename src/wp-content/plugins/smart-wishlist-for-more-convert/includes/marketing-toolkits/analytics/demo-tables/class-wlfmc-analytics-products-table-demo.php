<?php
/**
 * Analytics Products list Class
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
if ( ! class_exists( 'WLFMC_Analytics_Products_Table_Demo' ) ) {

	/**
	 * Wishlist analytics users table
	 */
	class WLFMC_Analytics_Products_Table_Demo extends WP_List_Table {

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'product',     // Singular name of the listed records.
					'plural'   => 'products',    // Plural name of the listed records.
					'ajax'     => false, // should this table support ajax?
				)
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
				case 'sales_in_period':
				case 'total_sales':
					return wc_price( $item[ $column_name ] );
				case 'prod_id':
				case 'user_count':
				case 'guest_count':
				case 'added_to_list':
					return '<a href="#">' . esc_attr( $item[ $column_name ] ) . '</a>';
				case 'inventory':
				case 'items_sold':
				case 'list_quantity':
				case 'item_purchased':
				default:
					return ! empty( $item[ $column_name ] ) ? $item[ $column_name ] : 0;
			}
		}

		/**
		 * Get columns
		 *
		 * @return array
		 */
		public function get_columns(): array {
			return array(
				'prod_id'         => __( 'Product', 'wc-wlfmc-wishlist' ),
				'added_to_list'   => __( 'Total lists added', 'wc-wlfmc-wishlist' ),
				'list_quantity'   => __( 'List quantity', 'wc-wlfmc-wishlist' ),
				'user_count'      => __( 'User count', 'wc-wlfmc-wishlist' ),
				'guest_count'     => __( 'Guest count', 'wc-wlfmc-wishlist' ),
				'inventory'       => __( 'Inventory', 'wc-wlfmc-wishlist' ),
				'total_sales'     => __( 'Total sales', 'wc-wlfmc-wishlist' ),
				'items_sold'      => __( 'Total item sold', 'wc-wlfmc-wishlist' ),
				'sales_in_period' => __( 'List sales', 'wc-wlfmc-wishlist' ),
				'item_purchased'  => __( 'List item sold', 'wc-wlfmc-wishlist' ),

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
		 * Get a list of sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns(): array {
			return array(
				'added_to_list'   => array( 'added_to_list', true ),
				'list_quantity'   => array( 'list_quantity', false ),
				'sales_in_period' => array( 'sales_in_period', false ),
				'item_purchased'  => array( 'item_purchased', false ),
				'items_sold'      => array( 'items_sold', false ),
				'user_count'      => array( 'user_count', false ),
				'guest_count'     => array( 'guest_count', false ),
				'total_sales'     => array( 'total_sales', false ),
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$columns               = $this->get_columns();
			$this->_column_headers = array( $columns, array(), array() );
			$this->items           = array(
				array(
					'prod_id'         => 'ZenBlend Tea',
					'added_to_list'   => '10',
					'list_quantity'   => '20',
					'user_count'      => '5',
					'guest_count'     => '3',
					'inventory'       => '50',
					'total_sales'     => '100',
					'items_sold'      => '80',
					'sales_in_period' => '30',
					'item_purchased'  => '25',
				),
				array(
					'prod_id'         => 'SwiftStride Shoes',
					'added_to_list'   => '15',
					'list_quantity'   => '30',
					'user_count'      => '8',
					'guest_count'     => '2',
					'inventory'       => '40',
					'total_sales'     => '150',
					'items_sold'      => '120',
					'sales_in_period' => '50',
					'item_purchased'  => '40',
				),
				array(
					'prod_id'         => 'CraftVase Ceramics',
					'added_to_list'   => '5',
					'list_quantity'   => '10',
					'user_count'      => '3',
					'guest_count'     => '1',
					'inventory'       => '30',
					'total_sales'     => '50',
					'items_sold'      => '40',
					'sales_in_period' => '20',
					'item_purchased'  => '15',
				),
				array(
					'prod_id'         => 'TechFlow Charger',
					'added_to_list'   => '20',
					'list_quantity'   => '40',
					'user_count'      => '10',
					'guest_count'     => '5',
					'inventory'       => '60',
					'total_sales'     => '200',
					'items_sold'      => '160',
					'sales_in_period' => '60',
					'item_purchased'  => '50',
				),
				array(
					'prod_id'         => 'Gourmet Olive Oil',
					'added_to_list'   => '8',
					'list_quantity'   => '16',
					'user_count'      => '4',
					'guest_count'     => '2',
					'inventory'       => '20',
					'total_sales'     => '80',
					'items_sold'      => '64',
					'sales_in_period' => '25',
					'item_purchased'  => '20',
				),
				array(
					'prod_id'         => 'VelvetAura Pillow',
					'added_to_list'   => '12',
					'list_quantity'   => '24',
					'user_count'      => '6',
					'guest_count'     => '4',
					'inventory'       => '35',
					'total_sales'     => '120',
					'items_sold'      => '96',
					'sales_in_period' => '40',
					'item_purchased'  => '30',
				),
				array(
					'prod_id'         => 'ExplorePack Backpack',
					'added_to_list'   => '18',
					'list_quantity'   => '36',
					'user_count'      => '9',
					'guest_count'     => '3',
					'inventory'       => '45',
					'total_sales'     => '180',
					'items_sold'      => '144',
					'sales_in_period' => '55',
					'item_purchased'  => '45',
				),
				array(
					'prod_id'         => 'CashmereChic Scarf',
					'added_to_list'   => '6',
					'list_quantity'   => '12',
					'user_count'      => '2',
					'guest_count'     => '1',
					'inventory'       => '15',
					'total_sales'     => '60',
					'items_sold'      => '48',
					'sales_in_period' => '15',
					'item_purchased'  => '10',
				),
				array(
					'prod_id'         => 'AquaGlow Skincare',
					'added_to_list'   => '14',
					'list_quantity'   => '28',
					'user_count'      => '7',
					'guest_count'     => '2',
					'inventory'       => '25',
					'total_sales'     => '140',
					'items_sold'      => '112',
					'sales_in_period' => '45',
					'item_purchased'  => '35',
				),
				array(
					'prod_id'         => 'PixelPerfect Monitor',
					'added_to_list'   => '16',
					'list_quantity'   => '32',
					'user_count'      => '8',
					'guest_count'     => '4',
					'inventory'       => '30',
					'total_sales'     => '160',
					'items_sold'      => '128',
					'sales_in_period' => '50',
					'item_purchased'  => '40',
				),
			);

		}

		/**
		 * Displays the search box.
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
				return;
			}

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
			}
			if ( ! empty( $_REQUEST['order'] ) ) {
				echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
			}
            // phpcs:enable
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
				<input type="search" placeholder="<?php esc_attr_e( 'Search by name', 'wc-wlfmc-wishlist' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @param string $which Where to show nav.
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' === $which ) :
				$dates     = isset( $_REQUEST['dates'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dates'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$list_type = isset( $_REQUEST['list_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['list_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<div class="alignleft actions ">
					<div class="d-flex f-center f-wrap gap-5">
						<!--a class="btn-primary wlfmc-export-users" >
							<?php esc_html_e( 'Export', 'wc-wlfmc-wishlist' ); ?>
							<span class="mct-btn-percent">
							<span class="mct-btn-progress"></span>
							%
						</span>
						</a-->
						<div>
							<label class="screen-reader-text" for="list_type"><?php esc_html_e( 'List Type', 'wc-wlfmc-wishlist' ); ?></label>
							<select name="list_type" id="list_type" autocomplete="off" data-value="<?php echo esc_attr( $list_type ); ?>">
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
						<div class="mct-daterangepicker-wrapper">
							<label class="screen-reader-text" for="date"><?php esc_html_e( 'Date', 'wc-wlfmc-wishlist' ); ?></label>
							<input autocomplete="off" type="text" id="date" placeholder="<?php esc_html_e( 'Date', 'wc-wlfmc-wishlist' ); ?>" name="dates" class="regular-text mct-daterangepicker" value="<?php echo esc_attr( $dates ); ?>"  data-value="<?php echo esc_attr( $dates ); ?>">
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

