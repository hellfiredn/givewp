<?php
/**
 * Users list Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert Premium
 * @version 1.7.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WLFMC_Analytics_Users_Table_Demo' ) ) {
	/**
	 * WLFMC_Analytics_Users_Table_Demo Class.
	 */
	class WLFMC_Analytics_Users_Table_Demo extends WP_List_Table {

		/**
		 * Constructor
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'user',     // Singular name of the listed records.
					'plural'   => 'users',    // Plural name of the listed records.
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
			$is_registered = absint( $item['user_id'] ) > 0;
			switch ( $column_name ) {
				case 'order_id':
					return '<a href="' . get_admin_url( null, 'post.php?post=' . $item[ $column_name ] . '&action=edit' ) . '">#' . $item[ $column_name ] . '</a>';
				case 'user_id':
					$formatted_name = $item['display_name'];
					/* translators: %d : guest customer_id */
					$badge = ! $is_registered ? '<span class="wlfmc-badge guest">' . sprintf( __( 'Guest (#%d)', 'wc-wlfmc-wishlist' ), $item['customer_id'] ) . '</span>' : '';
					return '<a href="' . esc_url(
						add_query_arg(
							array(
								'page'         => 'mc-analytics',
								'show-profile' => $item['customer_id'],
							),
							admin_url( 'admin.php' )
						)
					) . '">' . esc_attr( $item['username'] ) . $badge . '</a><br>' . $formatted_name;
				case 'datepurchased':
					return '' !== $item[ $column_name ] ? date_i18n( 'Y-m-d H:i:s', strtotime( $item[ $column_name ] ) ) : '-';
				case 'type':
					return 'buy-through-coupon' === $item[ $column_name ] ? __( 'Coupons', 'wc-wlfmc-wishlist' ) : __( 'Directly via Lists', 'wc-wlfmc-wishlist' );
				case 'prod_id':
					$product = wc_get_product( $item[ $column_name ] );
					if ( is_object( $product ) ) {
						return '<a href="' . get_the_permalink( $item[ $column_name ] ) . '">' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</a>';
					} else {
						return '-';
					}
				case 'price':
				case 'total_lists_payment':
				case 'total_payment':
					return wc_price( $item[ $column_name ] );
				case 'total_price':
					return wc_price( $item['price'] * $item['quantity'] );
				case 'phone':
					$phone_verified = ! $is_registered && wlfmc_is_true( $item['phone_verified'] ) ? ' <svg class="mobile-verified" ><use xlink:href="' . esc_url( MC_WLFMC_URL ) . 'assets/backend/images/sprite.svg#mobile-verified"></use></svg>' : '';
					return esc_attr( $item[ $column_name ] ) . $phone_verified;
				case 'email':
					$email_verified = ! $is_registered && wlfmc_is_true( $item['email_verified'] ) ? ' <svg class="email-verified" ><use xlink:href="' . esc_url( MC_WLFMC_URL ) . 'assets/backend/images/sprite.svg#email-verified"></use></svg>' : '';
					return esc_attr( $item[ $column_name ] ) . $email_verified;
				case 'company':
				case 'city':
				case 'state':
				case 'postcode':
				case 'country':
				case 'quantity':
				case 'list_count':
				default:
					return esc_attr( $item[ $column_name ] );
			}
		}

		/**
		 * Get columns
		 *
		 * @return array
		 */
		public function get_columns(): array {
			return array(
				'cb'                  => '<input type="checkbox" />',
				'user_id'             => __( 'User', 'wc-wlfmc-wishlist' ),
				'email'               => __( 'Email', 'wc-wlfmc-wishlist' ),
				'phone'               => __( 'Phone', 'wc-wlfmc-wishlist' ),
				'list_count'          => __( 'Number of lists', 'wc-wlfmc-wishlist' ),
				'total_product_lists' => __( 'Total Products in all lists', 'wc-wlfmc-wishlist' ),
				'total_payment'       => __( 'Total Payment Overall', 'wc-wlfmc-wishlist' ),
				'total_lists_payment' => __( 'Total Payment by lists', 'wc-wlfmc-wishlist' ),
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
				$item['customer_id']
			);
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns(): array {
			return array(
				'total_product_lists' => array( 'total_product_lists', true ),
				'total_payment'       => array( 'total_payment', false ),
				'total_lists_payment' => array( 'total_lists_payment', false ),
				'list_count'          => array( 'list_count', false ),
			);
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @return array
		 */
		public function get_bulk_actions(): array {
			return array(
				'bulk-export'        => __( 'Export', 'wc-wlfmc-wishlist' ),
				'bulk-send-campaign' => __( 'Send Campaign', 'wc-wlfmc-wishlist' ),
				'bulk-delete'        => __( 'Delete', 'wc-wlfmc-wishlist' ),
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();
			/** Process bulk action */
			$this->process_bulk_action();

			// sets columns headers.
			$columns               = $this->get_columns();
			$this->_column_headers = array( $columns, array(), array() );
			// retrieve data for table.
			$this->items = self::get_items( 5, 1 );

		}

		/**
		 * Get items
		 *
		 * @param int $per_page Per page.
		 * @param int $page_number Page number.
		 *
		 * @return array
		 */
		public static function get_items( $per_page, int $page_number = 1 ) {
			return array(
				array(
					'customer_id'             => 1,
					'user_id'                 => 7,
					'session_id'              => '',
					'email_verified'          => 0,
					'phone_verified'          => 0,
					'email'                   => 'john.doe@example.com',
					'display_name'            => 'John Doe',
					'first_name'              => 'John',
					'last_name'               => 'Doe',
					'phone'                   => '555-123-4567',
					'username'                => 'johndoe',
					'list_count'              => 4,
					'total_product_lists'     => 11,
					'total_payment'           => '',
					'total_product_analytics' => 11,
					'total_lists_payment'     => 0.000,
				),
				array(
					'customer_id'             => 2,
					'user_id'                 => 8,
					'session_id'              => '',
					'email_verified'          => 0,
					'phone_verified'          => 0,
					'email'                   => 'jane.smith@example.com',
					'display_name'            => 'Jane Smith',
					'first_name'              => 'Jane',
					'last_name'               => 'Smith',
					'phone'                   => '555-987-6543',
					'username'                => 'janesmith',
					'list_count'              => 2,
					'total_product_lists'     => 8,
					'total_payment'           => '',
					'total_product_analytics' => 8,
					'total_lists_payment'     => 0.000,
				),
				array(
					'customer_id'             => 3,
					'user_id'                 => 9,
					'session_id'              => '',
					'email_verified'          => 0,
					'phone_verified'          => 0,
					'email'                   => 'michael.johnson@example.com',
					'display_name'            => 'Michael Johnson',
					'first_name'              => 'Michael',
					'last_name'               => 'Johnson',
					'phone'                   => '555-555-5555',
					'username'                => 'michaeljohnson',
					'list_count'              => 6,
					'total_product_lists'     => 15,
					'total_payment'           => '',
					'total_product_analytics' => 15,
					'total_lists_payment'     => 0.000,
				),
				array(
					'customer_id'             => 4,
					'user_id'                 => 10,
					'session_id'              => '',
					'email_verified'          => 0,
					'phone_verified'          => 0,
					'email'                   => 'sarah.wilson@example.com',
					'display_name'            => 'Sarah Wilson',
					'first_name'              => 'Sarah',
					'last_name'               => 'Wilson',
					'phone'                   => '555-111-2222',
					'username'                => 'sarahwilson',
					'list_count'              => 3,
					'total_product_lists'     => 9,
					'total_payment'           => '',
					'total_product_analytics' => 9,
					'total_lists_payment'     => 0.000,
				),
				array(
					'customer_id'             => 5,
					'user_id'                 => 11,
					'session_id'              => '',
					'email_verified'          => 0,
					'phone_verified'          => 0,
					'email'                   => 'robert.jones@example.com',
					'display_name'            => 'Robert Jones',
					'first_name'              => 'Robert',
					'last_name'               => 'Jones',
					'phone'                   => '555-444-3333',
					'username'                => 'robertjones',
					'list_count'              => 5,
					'total_product_lists'     => 12,
					'total_payment'           => '',
					'total_product_analytics' => 12,
					'total_lists_payment'     => 0.000,
				),
			);
		}

		/**
		 * Displays the bulk action dropdown.
		 *
		 * @since 3.1.0
		 *
		 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
		 *                      This is designated as optional for backward compatibility.
		 */
		protected function bulk_actions( $which = '' ) {
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();

				/**
				 * Filters the items in the bulk actions menu of the list table.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen.
				 *
				 * @since 3.1.0
				 * @since 5.6.0 A bulk action can now contain an array of options in order to create an optgroup.
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

				$two = '';
			} else {
				return;
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_html( 'Select bulk action' ) . '</label>';
			echo '<select name="action' . esc_attr( $two ) . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
			echo '<option value="-1">' . esc_attr__( 'Bulk actions', 'wc-wlfmc-wishlist' ) . "</option>\n";

			foreach ( $this->_actions as $key => $value ) {
				if ( is_array( $value ) ) {
					echo "\t" . '<optgroup label="' . esc_attr( $key ) . '">' . "\n";

					foreach ( $value as $name => $title ) {
						$class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';

						echo '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
					}
					echo "</optgroup>\n";
				} else {
					$class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';

					echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $class ) . '>' . esc_attr( $value ) . "</option>\n";
				}
			}

			echo "</select>\n";
			echo "<button class='btn-primary btn-bulk-action' type='submit' id='doaction" . esc_attr( $two ) . "' data-label='" . esc_html( 'Apply' ) . "'>" . esc_html( 'Apply' ) . '</button>';
			echo "\n";
		}
	}
}
