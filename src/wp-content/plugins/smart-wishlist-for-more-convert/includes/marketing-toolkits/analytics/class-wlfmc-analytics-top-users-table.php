<?php
/**
 * Users list Class
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
if ( ! class_exists( 'WLFMC_Analytics_Top_Users_Table' ) ) {
	/**
	 * WLFMC_Analytics_Top_Users_Table Class.
	 */
	class WLFMC_Analytics_Top_Users_Table extends WP_List_Table {

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
				case 'total_lists_payment':
				case 'total_payment':
					return wc_price( $item[ $column_name ] );
				case 'email':
					$email_verified = ! $is_registered && wlfmc_is_true( $item['email_verified'] ) ? ' <svg class="email-verified" width="16" height="16" style="vertical-align:middle" ><use xlink:href="' . esc_url( MC_WLFMC_URL ) . 'assets/backend/images/sprite.svg#email-verified"></use></svg>' : '';
					return esc_attr( $item[ $column_name ] ) . $email_verified;
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
				'list_count'          => __( 'Number of lists', 'wc-wlfmc-wishlist' ),
				'total_product_lists' => __( 'Total Products in all lists', 'wc-wlfmc-wishlist' ),
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
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = self::get_items( 5 );

		}

		/**
		 * Get items
		 *
		 * @param int $per_page Per page.
		 * @param int $page_number Page number.
		 *
		 * @return array|object|null
		 */
		public static function get_items( $per_page, int $page_number = 1 ) {

			global $wpdb;
			// phpcs:disable WordPress.Security.NonceVerification
			$sql  = "SELECT 
                                    customers.customer_id, 
                                    customers.user_id, 
                                    customers.session_id,
                                    customers.email_verified,
                                    customers.phone_verified,
                                    IFNULL( users.user_email, customers.email) AS email ,
                                    CONCAT_WS( ' ', IFNULL( m1.meta_value, customers.first_name),  IFNULL( m2.meta_value, customers.last_name) ) as display_name,
                                    IFNULL( m1.meta_value, customers.first_name) AS first_name ,
                                    IFNULL( m2.meta_value, customers.last_name) AS last_name, 
                                    IFNULL( users.user_login, customers.email) AS username ,
                                    COUNT(DISTINCT items.wishlist_id) AS list_count,             
                                    COUNT(DISTINCT items.prod_id) AS total_product_lists,
                                    a.total_product_analytics, 
                                    a.total_lists_payment
                                    FROM $wpdb->wlfmc_wishlist_customers as customers
                                    LEFT JOIN $wpdb->users AS users ON customers.user_id = users.ID
                                    LEFT JOIN $wpdb->usermeta AS m1 ON users.ID = m1.user_id AND m1.meta_key = 'first_name'
                                    LEFT JOIN $wpdb->usermeta AS m2 ON users.ID = m2.user_id AND m2.meta_key = 'last_name'
                                    LEFT JOIN $wpdb->wlfmc_wishlist_items as items ON items.customer_id  = customers.customer_id
                                    LEFT JOIN (
                                         SELECT customer_id, 
                                                COUNT(DISTINCT prod_id) AS total_product_analytics ,
                                                SUM(IF(order_id IS NOT NULL AND datepurchased IS NOT NULL AND type IN ('buy-through-list', 'buy-through-coupon'), price * quantity, 0)) AS total_lists_payment
                                         FROM $wpdb->wlfmc_wishlist_analytics
                                         GROUP BY customer_id
                                    ) as a on a.customer_id = customers.customer_id";
			$sql .= " WHERE ( ( users.user_email IS NOT NULL AND users.user_email  != '' ) OR  customers.email != '' ) ";

			$sql .= ' GROUP BY customers.customer_id,email,display_name,first_name,last_name,username';
			$sql .= ' HAVING ( total_product_lists > 0 OR total_product_analytics> 0 )';

			$sql .= ' ORDER BY total_product_lists DESC ';

			// phpcs:enable
			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
			return $wpdb->get_results( $sql, 'ARRAY_A' );  // phpcs:ignore WordPress.DB
		}

		/**
		 * Displays the table.
		 */
		public function display() {
			$singular = $this->_args['singular'];
			?>
			<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
				<?php $this->print_table_description(); ?>
				<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
				</thead>

				<tbody id="the-list"
					<?php
					if ( $singular ) {
						echo esc_attr( " data-wp-lists='list:$singular'" );
					}
					?>
				>
				<?php $this->display_rows_or_placeholder(); ?>
				</tbody>

			</table>
			<?php
		}
	}
}
