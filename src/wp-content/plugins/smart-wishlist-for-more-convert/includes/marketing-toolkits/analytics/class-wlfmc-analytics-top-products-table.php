<?php
/**
 * Analytics Products list Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert Premium
 * @since 1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WLFMC_Analytics_Top_Products_Table' ) ) {

	/**
	 * Wishlist analytics users table
	 */
	class WLFMC_Analytics_Top_Products_Table extends WP_List_Table {

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
				case 'prod_id':
					$product = wc_get_product( $item['prod_id'] );
					if ( is_object( $product ) ) {
						$permalink = add_query_arg(
							array(
								'page'             => 'mc-analytics',
								'product-insights' => $item['prod_id'],
							),
							admin_url( 'admin.php' )
						);
						return '<a href="' . $permalink . '">' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</a>';
					} else {
						return '-';
					}
				case 'sales_in_period':
				case 'total_sales':
					return wc_price( $item[ $column_name ] );
				case 'inventory':
					$product = wc_get_product( $item['prod_id'] );
					return is_object( $product ) ? ( $product->managing_stock() ? $product->get_stock_quantity() : $product->get_stock_status() ) : '0';
				case 'user_count':
				case 'guest_count':
					$list_type = isset( $_REQUEST['list_type'] ) && '' !== $_REQUEST['list_type'] ? sanitize_text_field( wp_unslash( $_REQUEST['list_type'] ) ) : 'all-lists';// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return $item[ $column_name ] > 0 ? '<a href="' . esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'page'         => 'mc-analytics',
									'tab'          => 'users',
									'type'         => 'class',
									'segment_type' => 'product',
									'prod_id'      => $item['prod_id'],
									'list_type'    => $list_type,
								),
								admin_url( 'admin.php' )
							),
							'wlfmc_new_segment'
						)
					) . '">' . esc_attr( $item[ $column_name ] ) . '</a>' : $item[ $column_name ];
				case 'items_sold':
				case 'added_to_list':
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
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = self::get_items( 5, 1 );

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
            // phpcs:disable WordPress.DB ,WordPress.Security.NonceVerification

			$sql  = "SELECT DISTINCT( posts.ID ) AS prod_id,
                       IFNULL( items.user_count, 0 ) AS user_count,
                       IFNULL( ol.product_qty, 0 ) AS items_sold, 
                       IFNULL( ol.product_net_revenue, 0 ) AS total_sales,
                       SUM( IF(a.type  IN ('buy-through-list', 'buy-through-coupon'),a.quantity, 0  ) ) as item_purchased,
                       SUM( IF(a.type  IN ('buy-through-list', 'buy-through-coupon'),a.quantity * a.price, 0  ) ) as sales_in_period,
                       COUNT( DISTINCT CASE WHEN ( a.type IN ('add-to-list', 'buy-through-list') ) OR ( a.type = 'buy-through-coupon' AND a.wishlist_id = 0 ) THEN a.ID END ) AS added_to_list,
                       IFNULL( items.quantity, 0 ) AS list_quantity,
                       IFNULL( items.guest_count, 0 ) AS guest_count
                    FROM $wpdb->posts as posts 
                    LEFT JOIN $wpdb->wlfmc_wishlist_analytics AS a ON a.prod_id = posts.ID
                    LEFT JOIN (
                        SELECT items.prod_id, 
                        SUM(IFNULL(items.quantity, 0)) AS quantity ,
                        COUNT( DISTINCT items.user_id ) AS user_count,
                        COUNT( DISTINCT IFNULL(w.session_id, NULL) ) AS guest_count
                        FROM $wpdb->wlfmc_wishlist_items as items
                        LEFT JOIN $wpdb->wlfmc_wishlists as w ON items.wishlist_id = w.ID AND items.user_id IS NULL
                        WHERE 1=1
                        GROUP BY prod_id
                    ) as items ON items.prod_id = posts.ID
                    LEFT JOIN (
                        SELECT IF(o.variation_id = 0,o.product_id ,o.variation_id )as prod_id,
                               sum( o.product_qty ) as product_qty , 
                               sum( o.product_net_revenue ) as product_net_revenue,
                               '' as list_type 
                        FROM  {$wpdb->prefix}wc_order_product_lookup as o
                        LEFT JOIN {$wpdb->prefix}wc_customer_lookup as cl on cl.customer_id = o.customer_id 
                        GROUP BY prod_id
                    ) as ol ON ol.prod_id = posts.ID
                    WHERE posts.post_type IN( 'product', 'product_variation' ) AND ( posts.ID = items.prod_id OR posts.ID = a.prod_id  OR ol.prod_id = posts.ID)
                    GROUP BY posts.ID ";
			$sql .= ' ORDER BY added_to_list DESC';
			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			return $wpdb->get_results( $sql, 'ARRAY_A' );
			// phpcs:enable WordPress.DB ,WordPress.Security.NonceVerification
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

