<?php
/**
 * Smart Wishlist Analytics Admin Demo
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.7.6
 * @since 1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Analytics_Admin_Demo' ) ) {
	/**
	 * WooCommerce Wishlist Analytics Admin Demo
	 */
	class WLFMC_Analytics_Admin_Demo {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Analytics_Admin_Demo
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			add_filter( 'wlfmc_admin_menus', array( $this, 'add_admin_menu' ), 19, 2 );
		}

		/**
		 * Add admin menu
		 *
		 * @param array  $admin_menus admin menus.
		 * @param string $parent_slug parent slug.
		 *
         * @version 1.7.6
		 * @return array
		 */
		public function add_admin_menu( $admin_menus, $parent_slug ) {

			$analytics = add_submenu_page(
				$parent_slug,
				__( 'Reports', 'wc-wlfmc-wishlist' ),
				__( 'Reports', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-analytics',
				array( $this, 'show_analytics' )
			);
			$features = add_submenu_page(
				$parent_slug,
				__( 'Features', 'wc-wlfmc-wishlist' ),
				__( 'Features', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-features',
				array( $this, 'show_features' )
			);

            $premium = add_submenu_page(
                $parent_slug,
                __( 'Get Pro Now', 'wc-wlfmc-wishlist' ),
                '<span style="color:#FD5D00">' . __( 'Get Pro Now', 'wc-wlfmc-wishlist' ) . '</span>',
                apply_filters( 'wlfmc_capability', 'manage_options' ),
                'https://moreconvert.com/vb79'
            );

			$keys  = array_keys( $admin_menus );
			$index = array_search( 'email-automations', $keys, true );

			$admin_menus = array_merge(
				array_slice( $admin_menus, 0, $index ),
				array(
					'analytics' => $analytics,
                    'features'  => $features,
                    'premium'   => $premium,
                ),
				array_slice( $admin_menus, $index )
			);
			if ( isset( $admin_menus['dashboard'] ) ) {
                unset( $admin_menus['dashboard'] );
			}

			return $admin_menus;
		}

        /**
		 * Show analytics page.
		 *
		 * @return void
		 */
        public function show_features() {

            $fields = new MCT_Fields(
                array(
                    'title'          => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
                    'subtitle'       => __( 'MoreConvert Setting', 'wc-wlfmc-wishlist' ),
                    'desc'           => __( 'Gather leads and boost your sales with attention-grabbing lists for potential customers.', 'wc-wlfmc-wishlist' ),
                    'logo'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
                    'id'             => 'wlfmc_features_page',
                    'header_buttons' => wlfmc_get_admin_header_buttons(),
                    'header_menu'    => wlfmc_get_admin_header_menu(),
                    'type'           => 'class-type',
                    'class_array'    => array( $this, 'display_features' ),
                )
            );
            $fields->output();
        }

		/**
		 * Show analytics page.
		 *
		 * @return void
		 */
		public function show_analytics() {
			?>
            <div id="wlfmc-analytics">
				<?php
				$fields = new MCT_Fields(
					array(
						'title'                => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
						'logo'                 => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
						'removable_query_args' => array( 'dates', 'list_type', 's', 'user_id', 'customer_id' ),
						'type'                 => 'setting-type',
						'add_form_tag'         => false,
						'options'              => array(
							'analytics_tabs' => array(
								'tabs'   => array(
									'overview' => __( 'Overview', 'wc-wlfmc-wishlist' ),
									'users'    => __( 'Users', 'wc-wlfmc-wishlist' ),
									'lists'    => __( 'Lists', 'wc-wlfmc-wishlist' ),
									'products' => __( 'Products', 'wc-wlfmc-wishlist' ),
								),
								'fields' => array(
									'overview' => array(
										'type'  => 'class',
										'class' => array( $this, 'display_overview' ),
									),
									'users'    => array(
										'type'  => 'class',
										'class' => array( $this, 'display_users' ),
									),
									'lists'    => array(
										'type'  => 'class',
										'class' => array( $this, 'display_lists' ),
									),
									'products' => array(
										'type'  => 'class',
										'class' => array( $this, 'display_products' ),
									),
								),
							),
						),
						'header_buttons'       => wlfmc_get_admin_header_buttons(),
						'header_menu'          => wlfmc_get_admin_header_menu(),
						'id'                   => 'wlfmc_analytics_options',
					)
				);
				$fields->output();
				?>
            </div>
            <div id="snackbar"></div>
			<?php
		}

		/* === DISPLAY METHODS === */


		/**
		 * Display the features.
		 *
         * @version 1.7.6
		 * @return void
		 */
		public function display_features() {
			$articles = array(
                array(
                  'title'        => __( 'Customization', 'wc-wlfmc-wishlist' ),
                  'descriptions' => __( 'Customize wishlist buttons, counters, notifications, layout, and styling options according to your preferences.', 'wc-wlfmc-wishlist' ),
                  'columns'      => array(
	                  array(
		                  'image' => 'feature-01.png',
		                  'title' => __( 'Quick Wizard', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
	                  array(
		                  'image' => 'feature-02.png',
		                  'title' => __( 'Customizable Wishlist Buttons', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
	                  array(
		                  'image' => 'feature-03.png',
		                  'title' => __( 'Customizable Counter', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
	                  array(
		                  'image' => 'feature-04.png',
		                  'title' => __( 'Additional CSS', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
	                  array(
		                  'image' => 'feature-05.png',
		                  'title' => __( 'Customize Toast Notifications', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Tailor the appearance, content, and position of your alerts to match your site\'s unique style.', 'wc-wlfmc-wishlist' ),
		                  'video' => 'https://www.youtube.com/embed/1ZxtYQFmoAo',
	                  ),
	                  array(
		                  'image' => 'feature-06.png',
		                  'title' => __( 'Product Exclusions For Precise Alerts', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'You can customize your notifications effortlessly by excluding specific products or categories from each alert state. ', 'wc-wlfmc-wishlist' ),
		                  'video' => 'https://www.youtube.com/embed/V5bHsuGmxRc',
	                  ),
	                  array(
		                  'image' => 'feature-07.png',
		                  'title' => __( 'List & Grid Options', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Choose between list view and grid View modes in our lists tables, giving users the flexibility to switch seamlessly based on their preference.', 'wc-wlfmc-wishlist' ),
		                  'video' => 'https://www.youtube.com/embed/wvGfdai-WMo',
	                  ),
	                  array(
		                  'image' => 'feature-08.png',
		                  'title' => __( 'Elementor Widgets', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
	                  array(
		                  'image' => 'feature-09.png',
		                  'title' => __( 'Shortcodes For Buttons', 'wc-wlfmc-wishlist' ),
		                  'desc'  => __( 'Set up your wishlist functionality quickly and easily with a user-friendly wizard, saving you time and effort.', 'wc-wlfmc-wishlist' ),
		                  'activate' => true,
	                  ),
                  ),
                ),
                array(
                    'title'        => __( 'User Wishlist Management', 'wc-wlfmc-wishlist' ),
                    'descriptions' => __( 'Manage wishlists efficiently with features such as waitlists, unlimited lists, copy/move options, sorting, and price calculation.', 'wc-wlfmc-wishlist' ),
                    'columns'      => array(
	                    /*array(
		                    'image' => 'feature-10.png',
		                    'title' => __( 'Active Notification For Wishlist', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Give your users the chance to activate notifications for items on sale, limited stock, back in stock, and price changes according to your priority. Send them automatic emails based on product changes.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/#TODO',
	                    ),*/
	                    array(
		                    'image' => 'feature-11.png',
		                    'title' => __( 'Unlimited Lists', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'With Unlimited Lists, create, customize, and curate without limits. From birthday gifts to collections, explore the freedom to organize your desires without any limitations.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/kb9pR0P1P8s',
	                    ),
	                    array(
		                    'image' => 'feature-12.png',
		                    'title' => __( 'Copy All To My List', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Enable others to add all items from your wishlist (or any other lists) to their own with a simple button.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/ZtEMzeyCpPA',
	                    ),
	                    array(
		                    'image' => 'feature-13.png',
		                    'title' => __( 'Move Products To Lists', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Organize your products by effortlessly shifting them between lists. With a simple click, choose the destination list for your items, making list management a breeze.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/rUJblhpS8ao',
	                    ),
	                    array(
		                    'image' => 'feature-14.png',
		                    'title' => __( 'Private and public', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Create both public and private lists, giving users the option to share their desired items publicly or keep them private for personal reference.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/XWnHcxxoMRY',
	                    ),
	                    array(
		                    'image' => 'feature-15.png',
		                    'title' => __( 'Drag And Drop Sorting', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Rearrange products in your Wishlist, Multi-lists, and Waitlist effortlessly, putting you in command of your preferred order.', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/py5u4evJeP0',
	                    ),
	                    array(
		                    'image' => 'feature-16.png',
		                    'title' => __( 'Total Price Of All Products', 'wc-wlfmc-wishlist' ),
		                    'desc'  => __( 'Showcases the total cost of listed products below tables for quick decision-making. Available in Marketing, Modern, and Classic modes', 'wc-wlfmc-wishlist' ),
		                    'video' => 'https://www.youtube.com/embed/AHzE3mvySfs',
	                    ),

                    ),
                ),
				array(
					'title'        => __( 'Customer Retention and Conversion', 'wc-wlfmc-wishlist' ),
					'descriptions' => __( 'Enhance user engagement and encourage conversions with features like save for later, engagement tracking, user invitations, and out-of-stock notifications.', 'wc-wlfmc-wishlist' ),
					'columns'      => array(
						array(
							'image' => 'feature-17.png',
							'title' => __( 'Save For Later Button', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Move products to the cart with a click, and delete items individually or in bulk.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/K5zsgwngJFc',
						),
						array(
							'image' => 'feature-18.png',
							'title' => __( 'Wishlist Engagement Counter', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Encourage users to add items with live user count for urgency', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/epeK5fL9KHg',
						),
						array(
							'image' => 'feature-19.png',
							'title' => __( 'Enable/Disable For Unregistered Users', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'This feature enables admins to control access to wishlists, allowing them to enable or disable wishlist functionality for unregistered users as needed.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
						),
						array(
							'image' => 'feature-20.png',
							'title' => __( 'Login/Signup Invitation', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Encourage unregistered users to create an account or log in by inviting them to save their wishlist for future visits.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
						),
						array(
							'image' => 'feature-21.png',
							'title' => __( 'Advanced Share Wishlist', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Enable customers to share their wishlists effortlessly on social media platforms, through direct links, or even export them as PDF files.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/IW-ntbCDEHA',
						),
						array(
							'image' => 'feature-22.png',
							'title' => __( 'Hidden "Save For Later" Button', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Add a hidden "Save for Later" button on the cart page, reaching another chance to persuade users based on their products and interests.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/K3ZyU-_rfVg',
						),
						array(
							'image' => 'feature-23.png',
							'title' => __( 'Notify Box For Out-Of-Stock', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Exclusive Out-of-Stock product box with 2 customizable styles', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/Xs6MOVM19wM',
						),
					),
				),
				array(
					'title'        => __( 'Analytics and Reporting', 'wc-wlfmc-wishlist' ),
					'descriptions' => __( 'Access detailed analytics, user exports, product monitoring, and customizable reports to track wishlist activity and user behavior.', 'wc-wlfmc-wishlist' ),
					'columns'      => array(
						array(
							'image' => 'feature-24.png',
							'title' => __( 'Advanced Analytics And Reports', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Access detailed data on users, lists, and products for invaluable insights. Export or launch campaigns easily.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/LrbUarjgjKo',
						),
						array(
							'image' => 'feature-25.png',
							'title' => __( 'User List Analytics', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'In our Analytics section, gain a granular view of all user lists, analyzing their purchase history and created lists. Uncover invaluable insights to supercharge your marketing game.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/KiruTFHUoks',
						),
                        array(
							'image' => 'feature-26.png',
							'title' => __( 'User Export With Custom Filtering', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'This feature allows you to export and filter users according to their lists and purchase history. Take control of your user data management effortlessly.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/836IebqRiqc',
						),
						array(
							'image' => 'feature-27.png',
							'title' => __( 'User-Centric Product Monitoring', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Explore the potential to check product status based on user behavior, uncovering items tailored to user interests.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/LAsIKs5Tz6s',
						),
						array(
							'image' => 'feature-28.png',
							'title' => __( 'Track User Profile Insights', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Evaluate user profiles through their favorite products, and gain key insights. Gain personalized strategies, ensuring targeted engagement and successful marketing outcomes.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/6e2pJNQcqh0',
						),
						array(
							'image' => 'feature-29.png',
							'title' => __( 'Precision User Filters', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'There are several conditions to filter users and products in a detailed way. You can track users behavior based on list type, favorite items, average order value and…', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/KKk8qXtj004',
						),
					),
				),
				array(
					'title'        => __( 'Email Marketing', 'wc-wlfmc-wishlist' ),
					'descriptions' => __( 'Utilize wishlist data for targeted email campaigns, including waitlist notifications, email automation, one-shot campaigns, SMTP integration, and smart coupons.', 'wc-wlfmc-wishlist' ),
					'columns'      => array(
						array(
							'image' => 'feature-30.png',
							'title' => __( 'Waitlist Notifications:<br>Price, Offers, Stock Updates', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Add products to your Waitlist and receive timely notifications for Price Changes, Exclusive Offers, Back-in-Stock, and Low-Stock situations.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/kn8BhlyUx1E',
						),
						array(
							'image' => 'feature-31.png',
							'title' => __( 'Unlimited Email Marketing', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Use automation or one-shot campaign to Target users by role, wishlists, history, and conditions, sending personalized emails for effective engagement.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/1oO9RAOysa0',
						),
						array(
							'image' => 'feature-32.png',
							'title' => __( 'Unlimited One-Shot Campaigns Based On Users\' Interests', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'From engaging newsletters to personalized promotions and offers, experience the freedom to connect with your audience limitlessly. ', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/qs8GJHfvLHc',
						),
						array(
							'image' => 'feature-33.png',
							'title' => __( 'Automatically Send Emails Using Website\'s SMTP', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Automatically send emails using the website’s SMTP with a system to reduce server pressure.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
						),
						array(
							'image' => 'feature-34.png',
							'title' => __( 'Smart Coupons', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Auto coupon builder for emails; set your preferences, auto-delete after expiry, effortless coupon management.', 'wc-wlfmc-wishlist' ),
							'video' => 'https://www.youtube.com/embed/wCDRyP8orzo',
						),
					),
				),
				array(
					'title'        => __( 'Website Integration and Compatibility', 'wc-wlfmc-wishlist' ),
					'descriptions' => __( 'Simplify website integration with robust compatibility and flexible settings management for seamless operation.', 'wc-wlfmc-wishlist' ),
					'columns'      => array(
						array(
							'image' => 'feature-35.png',
							'title' => __( 'GDPR Full Integration', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Complete integration of GDPR compliance measures within the plugin, ensuring adherence to data privacy standards mandated by GDPR regulations.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
						),
						array(
							'image' => 'feature-36.png',
							'title' => __( 'Integrated Themes & Plugins', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Smooth compatibility with a wide range of popular themes and page builders, including Gutenberg, Elementor, Divi, Astra, Storefront, OceanWP, Flatsome, and many others.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
                            'video' => 'https://www.youtube.com/embed/wGRCbwPdugw',
						),
						array(
							'image' => 'feature-37.png',
							'title' => __( 'Import/Export Settings', 'wc-wlfmc-wishlist' ),
							'desc'  => __( 'Easily import and export plugin settings, making it easy to transfer and replicate your wishlist configurations across different websites.', 'wc-wlfmc-wishlist' ),
							'activate' => true,
						),
					),
				),
			);
			WLFMC_Admin_Notice()->styles();
			?>
            <div id="wlfmc-features">
                <div id="wlfmc-premium-admin-notice" class="wlfmc-notice wlfmc-notice-checked hide-icon  mar-bot-20">
                    <h2><?php echo wp_kses_post( __( 'WooCommerce Wishlist Plugin, That Really Generates Leads and Sales.', 'wc-wlfmc-wishlist' ) ); ?></h2>
                    <p class="mar-bot-20">
						<?php esc_html_e( 'Craft beautiful WooCommerce wishlists effortlessly, with additional essential features like save cart for later, Back in stock box, email marketing, and reports.', 'wc-wlfmc-wishlist' ); ?>
                    </p>
                    <div class="d-flex gap-10 f-wrap mar-bot-20">
                        <div class="mct-article d-flex f-column">
                            <h2 class="orange-text">2372</h2>
                            <strong><?php esc_attr_e( 'HAPPY USERS', 'wc-wlfmc-wishlist' ); ?></strong>
                        </div>
                        <div class="mct-article d-flex f-column">
                            <h2 class="orange-text">60+</h2>
                            <strong><?php esc_attr_e( '5-STAR REVIEWS', 'wc-wlfmc-wishlist' ); ?></strong>
                        </div>
                        <div class="mct-article d-flex f-column">
                            <h2 class="orange-text">24/7</h2>
                            <strong><?php esc_attr_e( 'SUPPORT', 'wc-wlfmc-wishlist' ); ?></strong>
                        </div>
                        <div class="mct-article d-flex f-column">
                            <h2 class="orange-text"><?php esc_attr_e( '14-Day', 'wc-wlfmc-wishlist' ); ?></h2>
                            <strong><?php esc_attr_e( 'MONEY-BACK GUARANTEE', 'wc-wlfmc-wishlist' ); ?></strong>
                        </div>
                    </div>
                    <div class="d-flex gap-10">
                        <a href="https://moreconvert.com/q8zv" target="_blank" class="btn-primary " ><?php esc_attr_e( 'View Pricing', 'wc-wlfmc-wishlist' ); ?> <span class="dashicons dashicons-arrow-<?php echo is_rtl() ? 'left' : 'right'; ?>-alt"></span></a>
                        <a href="https://moreconvert.com/fyv9" target="_blank" class="btn-secondary "><?php esc_attr_e( 'View Demo', 'wc-wlfmc-wishlist' ); ?></a>
                    </div>
                </div>
	            <?php foreach ( $articles as $article ) : ?>
                    <div class="mct-article mar-bot-20">
                        <div class="article-title">
                            <h2><?php echo esc_attr( $article['title'] ); ?></h2>
                            <p class="description"><?php echo esc_attr( $article['descriptions'] ); ?></p>
                        </div>
                        <div class="container">
		                    <?php foreach ( $article['columns'] as $column ) : ?>
                                <div class="column d-flex f-column mct-article">
                                    <div class="d-flex f-column center-align">
                                        <img class="thumbnail mar-bot-20" alt="" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/features/<?php echo esc_attr( $column['image'] ); ?>" />
                                        <strong><?php echo wp_kses_post( $column['title'] ); ?></strong>
                                        <p><?php echo wp_kses_post( $column['desc'] ); ?></p>
                                    </div>
                                    <?php if ( isset( $column['activate'] ) && wlfmc_is_true( $column['activate'] ) ) : ?>
                                        <div class="btn-secondary active-btn d-flex gap-5 f-center">
                                            <img width="" alt="" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/tick.svg" />
                                            <?php esc_attr_e( 'Activated', 'wc-wlfmc-wishlist' ); ?>
                                        </div>
                                    <?php else: ?>
                                        <a class="btn-primary d-flex gap-5 f-center " href="https://moreconvert.com/q8zv" target="_blank">
                                            <img width="" alt="" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/wizard/crown.svg" />
                                            <span><?php esc_attr_e( 'Unlock with Premium', 'wc-wlfmc-wishlist' ) ;?></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ( isset( $column['video'] ) ): ?>
                                        <a href="<?php echo esc_url( $column['video'] ); ?>" data-modal="modal_youtube" class="modal-toggle btn-flat btn-text black-text">
	                                        <?php esc_attr_e( 'Watch Video', 'wc-wlfmc-wishlist' ) ;?>
                                        </a>
                                    <?php endif; ?>
                                </div>
		                    <?php endforeach; ?>
                            <?php
                            if ( count( $article['columns'] ) < 5 ) {
	                            for ( $i = 0; $i < 5 - count( $article['columns'] );$i++  ) {
                                   echo '<div></div>';
	                            }
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>


            </div>
            <div id="modal_youtube" class="mct-modal modal_youtube" style="display:none">
                <div class="modal-overlay modal-toggle" data-modal="modal_youtube"></div>
                <div class="modal-wrapper modal-transition modal-large modal-horizontal">
                    <button class="modal-close modal-toggle" data-modal="modal_youtube"><span class="dashicons dashicons-no-alt"></span></button>
                    <div class="modal-body">
                        <div class="modal-content">
							<iframe id="youtubeIframe" width="100%" height="400" src="" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <script>

	            (function() {
		            document.addEventListener("DOMContentLoaded", function() {
			            // JavaScript
			            document.querySelectorAll('.modal-toggle').forEach(function(element) {
				            element.addEventListener('click', function(event) {
					            event.preventDefault();
					            document.getElementById('youtubeIframe').src = this.getAttribute('href');
				            });
			            });

			            document.querySelectorAll('.modal-close').forEach(function(element) {
				            element.addEventListener('click', function() {
					            document.getElementById('youtubeIframe').src = '';
				            });
			            });
		            });
	            })();
            </script>
			<?php
		}

		/**
		 * Display the overview.
		 *
		 * @return void
		 */
		public function display_overview() {
			global $wpdb;
			// phpcs:disable WordPress.DB.PreparedSQL, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$email_sent      = $wpdb->get_var( "SELECT sum(IF(b.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) sent FROM $wpdb->wlfmc_wishlist_offers as b" );
			$wishlist_count  = $wpdb->get_var( "SELECT COUNT(*) as count FROM $wpdb->wlfmc_wishlists" );
			$list_statistics = $wpdb->get_row(
				"SELECT
                    sum(IF(a.type IN ('buy-through-list', 'buy-through-coupon'), a.quantity, 0)) as product_solds,
                    sum(IF(a.type IN ('buy-through-list', 'buy-through-coupon'), ( a.quantity * a.price ), 0)) as total_sales,
                    (sum(IF(a.type IN ('add-to-list', 'buy-through-list'), 1, 0)) + (sum(IF(a.type = 'buy-through-coupon' AND a.wishlist_id = 0, 1, 0)))) as product_added
                    FROM $wpdb->wlfmc_wishlist_analytics as a "
			);
            $top_products    = $wpdb->get_results( "
                                    SELECT DISTINCT( items.prod_id ) AS prod_id, COUNT( DISTINCT items.customer_id ) as count
                                    FROM $wpdb->wlfmc_wishlist_items as items
                                    LEFT JOIN $wpdb->posts as posts ON items.prod_id = posts.ID 
                                    WHERE posts.post_type IN( 'product', 'product_variation' )
                                    GROUP BY items.prod_id ORDER BY COUNT( DISTINCT items.customer_id ) DESC LIMIT 3", "ARRAY_A" );

			$p1_count  = ! empty( $top_products ) && isset( $top_products[0]['count'] ) ? intval( $top_products[0]['count'] ) : 0;
			$p2_count  = ! empty( $top_products ) && isset( $top_products[1]['count'] ) ? intval( $top_products[1]['count'] ) : 0;
			$p3_count  = ! empty( $top_products ) && isset( $top_products[2]['count'] ) ? intval( $top_products[2]['count'] ) : 0;
            $product_1 = ! empty( $top_products ) && isset( $top_products[0]['prod_id'] ) && $p1_count > 0 ? wc_get_product( $top_products[0]['prod_id'] ) : false;
			$product_2 = ! empty( $top_products ) && isset( $top_products[1]['prod_id'] ) && $p2_count > 0 ? wc_get_product( $top_products[1]['prod_id'] ) : false;
			$product_3 = ! empty( $top_products ) && isset( $top_products[2]['prod_id'] ) && $p3_count > 0 ? wc_get_product( $top_products[2]['prod_id'] ) : false;
            $outofstock_products = wlfmc_get_out_of_stock_product_count();

			$reports = apply_filters(
				'wlfmc_dashboard_reports',
				array(
					'created-lists'  => array(
						'title'      => __( 'Created Lists', 'wc-wlfmc-wishlist' ),
						'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/created-list.svg',
						'count'      => number_format( intval( $wishlist_count ) ),
						'link_class' => 'modal-toggle',
						'url'        => '',
					),
					'added-products' => array(
						'title'      => __( 'Added Products', 'wc-wlfmc-wishlist' ),
						'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/added-products.svg',
						'count'      => number_format( intval( $list_statistics->product_added ) ),
						'link_class' => 'modal-toggle',
						'url'        => add_query_arg(
							array(
								'page' => 'mc-analytics',
								'tab'  => 'products',
								'type' => 'class',
							),
							admin_url( 'admin.php' )
						),
					),
					'send-emails'    => array(
						'title'      => __( 'Send Emails', 'wc-wlfmc-wishlist' ),
						'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/send-emails.svg',
						'count'      => number_format( intval( $email_sent ) ),
						'link_class' => 'modal-toggle',
						'url'        => '',
					),
					'sales'          => array(
						'title'      => __( 'Sales', 'wc-wlfmc-wishlist' ),
						'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/sales.svg',
						'count'      => wc_price( $list_statistics->total_sales ),
						'link_class' => 'modal-toggle',
						'url'        => '',
					),
				)
			);

			?>
            <div class="mct-article wlfmc-style-0 mar-bot-20">
                <div class="article-title">
                    <h2><?php esc_html_e( 'Reports', 'wc-wlfmc-wishlist' ); ?></h2>
                    <div class="description"><?php esc_html_e( 'This is a summary of what is currently happening on your website’s wishlist.', 'wc-wlfmc-wishlist' ); ?></div>
                </div>
                <ul class="mct-tools mc-row">
                    <?php foreach ( $reports as $k => $tool ) : ?>
                        <li>
                            <a href="<?php echo '' !== $tool['url'] ? esc_url( $tool['url'] ) : ''; ?>" class="<?php echo esc_attr( $tool['link_class'] ); ?>" data-modal="modal_analytics">
                                <?php
                                if ( isset( $tool['image'] ) ) {
                                    echo wp_kses_post( $tool['image'] );
                                } elseif ( isset( $tool['image_url'] ) ) {
                                    echo '<img class="icon" src="' . esc_url( $tool['image_url'] ) . '"/>';
                                }
                                ?>
                                <div>
                                    <span class="tool-title"><?php echo esc_attr( $tool['title'] ); ?></span>
                                    <strong class="tool-count">
                                        <?php echo wp_kses_post( $tool['count'] ); ?>
                                    </strong>
                                </div>
                            </a>

                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="mct-options wlfmc-two-column">
                <div class="mct-inside ">
                    <div class="mct-inside-inner">
                        <div class="mct-article wlfmc-style-0 mar-bot-20">
                            <div class="article-title  mar-bot-20">
                                <div class="d-flex space-between f-center">
                                    <h2 class="">
                                        <span><?php esc_html_e( 'Most Active Users', 'wc-wlfmc-wishlist' ); ?></span>
                                    </h2>
                                    <a href="#" class="btn-primary center-align modal-toggle small-btn"  data-modal="modal_analytics"><?php esc_attr_e( 'See All', 'wc-wlfmc-wishlist' ); ?> <span class="dashicons dashicons-arrow-<?php echo is_rtl() ? 'left' : 'right'; ?>-alt"></span></a>
                                </div>
                                <p class="description"><?php esc_html_e( 'Identify users who are highly engaged and ready for your marketing offers.', 'wc-wlfmc-wishlist' ); ?></p>
                            </div>
                            <div id="wlfmc_analytics_top_wishlists" class="modal-toggle" data-modal="modal_analytics">
                                <div style="pointer-events: none;">
                                    <?php
                                    $users = new WLFMC_Analytics_Top_Users_Table();
                                    $users->prepare_items();
                                    $users->display();
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php if ( $product_3 ) : ?>
                            <div class="mct-article mar-bot-20 wlfmc-style-4">
                                <div class="d-flex">
                                    <div class="wlfmc-image-wrapper">
	                                    <?php echo wp_kses_post( $product_3->get_image( 'woocommerce_thumbnail', array( 'style' => 'max-width:260px !important;height: 100%;object-fit: cover;' ) ) ); ?>
                                        <div class="wlfmc-over-image">
                                            <span style="font-size:40px;color:#FD5D00; font-weight:bold;">
		                                        <?php echo esc_attr( $p3_count ); ?>
                                            </span>
                                            <br>
                                            <span style="font-weight:500;">
		                                        <?php esc_attr_e( 'Potential customers', 'wc-wlfmc-wishlist' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="wlfmc-content">
                                        <p class="d-flex gap-10 f-center">
                                            <img width="60" height="60" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/user-minus.svg" alt=""/>
                                            <strong>
                                                <?php echo sprintf( __( '%d Potential customers for %s', 'wc-wlfmc-wishlist' ), $p3_count , '<a href="' . esc_url(  $product_3->get_permalink() ) . '" style="font-style:italic;color:#FD5D00;">'. wp_kses_post( is_callable( array( $product_3, 'get_name' ) ) ? $product_3->get_name() : $product_3->get_title() ) .'</a>' );?>
                                            </strong>
                                        </p>
                                        <p class="description" style="font-weight:500"><?php echo sprintf( esc_html__( 'Create an automation for the potential %d customers who have added the specific product to their wishlist. Invite them to buy it right now with different ideas, such as a contest, on-sale automation, or even a webinar for them, with a special coupon provided at the end. Start increasing your revenue and don\'t lose this opportunity', 'wc-wlfmc-wishlist' ), $p3_count ); ?></p>
                                        <p>
                                            <a href="#" class="btn-primary min-pad center-align modal-toggle d-inline-flex f-center gap-5"  data-modal="modal_automation">
                                                <img width="20" height="20" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/gear.svg" alt=""/>
		                                        <?php esc_attr_e( 'Create Automation', 'wc-wlfmc-wishlist' ); ?>
                                            </a>
                                        </p>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mct-article wlfmc-style-0 mar-bot-20">
                            <div class="article-title  mar-bot-20">
                                <div class="d-flex space-between f-center">
                                    <h2 class="">
                                        <span><?php esc_html_e( 'Most Favorite Product', 'wc-wlfmc-wishlist' ); ?></span>
                                    </h2>
                                    <a href="#" class="btn-primary center-align modal-toggle small-btn"  data-modal="modal_analytics"><?php esc_attr_e( 'See All', 'wc-wlfmc-wishlist' ); ?> <span class="dashicons dashicons-arrow-<?php echo is_rtl() ? 'left' : 'right'; ?>-alt"></span></a>
                                </div>
                                <p class="description"><?php esc_html_e( 'Discover which product is the most favored by users.', 'wc-wlfmc-wishlist' ); ?></p>
                            </div>
                            <div id="wlfmc_analytics_top_products"  class="modal-toggle" data-modal="modal_analytics">
                                 <div style="pointer-events: none;">
                                    <?php
                                    $users = new WLFMC_Analytics_Top_Products_Table();
                                    $users->prepare_items();
                                    $users->display();
                                    ?>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ( $product_1 || $outofstock_products || $product_2 ) : ?>
                    <div class="mct-inside " >
                        <div class="mct-sidebar-inner">
                            <?php if ( $product_1 && $p1_count > 1 ) : ?>
                                <div class="mct-article mar-bot-20 wlfmc-style-1">
                                    <div class="d-flex gap-20 f-center">
                                        <img width="60" height="60" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/user-plus-orange.svg" alt=""/>
                                        <?php echo wp_kses_post( sprintf( __( '%s <strong>Positive Review Lost!</strong>', 'wc-wlfmc-wishlist' ), '<h1 style="color:#FF5F5F;font-size: 44px;padding:0"><strong>' . intval( $p1_count / 3 ) . '</strong></h1>' ) );?>
                                    </div>
                                    <p style="font-weight:400">
                                        <?php echo sprintf( __( 'Lost Potential Review for %s', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url(  $product_1->get_permalink() ) . '" style="font-style:italic;color:#FF5F5F;">'. wp_kses_post( is_callable( array( $product_1, 'get_name' ) ) ? $product_1->get_name() : $product_1->get_title() ) .'</a>' );?>
                                    </p>

                                    <p class="description mar-bot-20" style="font-weight:500">
                                        <?php echo sprintf( esc_html__( 'With %d users who have this product on their wishlist, you can gain at least %d positive reviews by running a email campaign for its users. Set conditions for a big deal for all those who send the best reviews under this product.', 'wc-wlfmc-wishlist' ), $p1_count, intval( $p1_count / 3 ) ); ?>
                                    </p>
                                    <p class="d-flex row-reverse">
                                        <a href="#" class="btn-secondary btn-red  min-pad center-align modal-toggle d-inline-flex f-center gap-5"  data-modal="modal_campaign">
                                            <svg width="20" height="20" viewBox="0 0 20 20">
                                                <g transform="translate(-172 -640)">
                                                    <path d="M19.136,12.23l0,0-1.282-1.177a8.393,8.393,0,0,0,0-2.1L19.136,7.77a1.265,1.265,0,0,0,.233-1.546L17.955,3.779a1.255,1.255,0,0,0-1.463-.568l-1.663.525a7.622,7.622,0,0,0-1.812-1.052L12.632.976A1.254,1.254,0,0,0,11.417,0H8.583A1.254,1.254,0,0,0,7.368.975L6.984,2.683A7.622,7.622,0,0,0,5.172,3.736L3.507,3.211a1.253,1.253,0,0,0-1.458.563L.628,6.229a1.264,1.264,0,0,0,.24,1.544L2.15,8.951A8.518,8.518,0,0,0,2.083,10a8.681,8.681,0,0,0,.066,1.049L.864,12.23a1.264,1.264,0,0,0-.233,1.545L2.045,16.22a1.255,1.255,0,0,0,1.463.569l1.663-.525a7.622,7.622,0,0,0,1.813,1.052l.384,1.707A1.254,1.254,0,0,0,8.583,20h2.833a1.254,1.254,0,0,0,1.215-.975l.384-1.708a7.594,7.594,0,0,0,1.812-1.052l1.665.525a1.252,1.252,0,0,0,1.459-.564l1.42-2.455a1.264,1.264,0,0,0-.237-1.54ZM10,14.167A4.167,4.167,0,1,1,14.167,10,4.171,4.171,0,0,1,10,14.167Z" transform="translate(172 640)" fill="#FF5F5F"/>
                                                </g>
                                            </svg>
		                                    <?php esc_attr_e( 'Run Campaign', 'wc-wlfmc-wishlist' ); ?>
                                        </a>
                                    </p>

                                </div>
                            <?php endif; ?>
                            <?php if ( $outofstock_products ) : ?>
                                <div class="mct-article mar-bot-20 wlfmc-style-2">
                                    <div class="d-flex f-column f-center">
                                        <img width="60" height="60" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/user-plus-green.svg" alt=""/>
                                        <h1 style="color:#02BC77;font-size: 44px;padding:0"><strong><?php echo esc_attr( $outofstock_products * 30 ); ?></strong></h1>
                                        <strong class="mar-bot-20"><?php esc_attr_e( 'Gain potential customers.', 'wc-wlfmc-wishlist' );?></strong>
                                        <p class="description mar-bot-20 center-align" style="max-width:300px"><?php echo sprintf( esc_html__( 'With %d out-of-stock products, you must immediately convert potential customers on your product pages into leads and boost sales by promoting similar or alternative available products to them.', 'wc-wlfmc-wishlist' ), $outofstock_products ); ?></p>
                                        <p>
                                            <a href="#" class="btn-secondary btn-green min-pad center-align modal-toggle d-inline-flex f-center gap-5"  data-modal="modal_waitlist">
                                                <svg width="20" height="20" viewBox="0 0 20 20">
                                                    <g transform="translate(-172 -640)">
                                                        <path d="M19.136,12.23l0,0-1.282-1.177a8.393,8.393,0,0,0,0-2.1L19.136,7.77a1.265,1.265,0,0,0,.233-1.546L17.955,3.779a1.255,1.255,0,0,0-1.463-.568l-1.663.525a7.622,7.622,0,0,0-1.812-1.052L12.632.976A1.254,1.254,0,0,0,11.417,0H8.583A1.254,1.254,0,0,0,7.368.975L6.984,2.683A7.622,7.622,0,0,0,5.172,3.736L3.507,3.211a1.253,1.253,0,0,0-1.458.563L.628,6.229a1.264,1.264,0,0,0,.24,1.544L2.15,8.951A8.518,8.518,0,0,0,2.083,10a8.681,8.681,0,0,0,.066,1.049L.864,12.23a1.264,1.264,0,0,0-.233,1.545L2.045,16.22a1.255,1.255,0,0,0,1.463.569l1.663-.525a7.622,7.622,0,0,0,1.813,1.052l.384,1.707A1.254,1.254,0,0,0,8.583,20h2.833a1.254,1.254,0,0,0,1.215-.975l.384-1.708a7.594,7.594,0,0,0,1.812-1.052l1.665.525a1.252,1.252,0,0,0,1.459-.564l1.42-2.455a1.264,1.264,0,0,0-.237-1.54ZM10,14.167A4.167,4.167,0,1,1,14.167,10,4.171,4.171,0,0,1,10,14.167Z" transform="translate(172 640)" fill="#02BC77"/>
                                                    </g>
                                                </svg>
		                                        <?php esc_attr_e( 'Active Waitlist Button', 'wc-wlfmc-wishlist' ); ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ( $product_2 ) : ?>
                                <div class="mct-article mar-bot-20 wlfmc-style-3">
                                    <div class="d-flex gap-10 f-center">
                                        <img width="60" height="60" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/newsletter.svg" alt=""/>
                                        <h2>
                                            <strong>
		                                        <?php esc_attr_e( 'Newsletter', 'wc-wlfmc-wishlist' );?>
                                            </strong>
                                        </h2>
                                    </div>
                                    <p style="font-weight:500">
                                        <?php echo sprintf( __( 'Build a newsletter for: %s', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url(  $product_2->get_permalink() ) . '" style="font-style:italic;color:#5EC4FF;">'. wp_kses_post( is_callable( array( $product_2, 'get_name' ) ) ? $product_2->get_name() : $product_2->get_title() ) .'</a>' );?>
                                    </p>
                                    <p class="description mar-bot-20"><?php echo sprintf( esc_html__( 'Create an automation for the potential %d customers who have added the specific product to their wishlist. Design an email automation with 5 emails that send them a group of related videos, blog posts, and other reviews about this product to increase user motivation to buy it faster. Then, provide a dedicated coupon, all within MoreConvert automation part.', 'wc-wlfmc-wishlist' ), $p2_count ); ?></p>
                                    <p class="d-flex row-reverse">
                                        <a href="#" class="btn-primary btn-blue min-pad center-align modal-toggle d-inline-flex f-center gap-5"  data-modal="modal_automation">
                                            <img width="20" height="20" src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/analytics/gear.svg" alt=""/>
		                                    <?php esc_attr_e( 'Create Automation', 'wc-wlfmc-wishlist' ); ?>
                                        </a>
                                    </p>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ( $product_2 || $product_3 ) : ?>
                <?php
                $modal = array(
	                'class'      => 'modal-large modal-horizontal',
	                'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/automation/automation.gif',
	                'image_link' => 'https://moreconvert.com/aqxv',
	                'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
	                'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #E43B67">' . __( 'Sequential Email Automation', 'wc-wlfmc-wishlist' ) . '</span>',
	                'desc'       => __( 'Active automated emails for users to receive invitations to buy products they like and add to their list with highly personalized emails and increase sales.', 'wc-wlfmc-wishlist' ),
	                'features'   => array(
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-2.svg',
			                'desc' => __( 'Increase customer retention', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-4.svg',
			                'desc' => __( 'Save 20 hours every week', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-3.svg',
			                'desc' => __( 'Increase customer loyalty', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-1.svg',
			                'desc' => __( 'Real-time tracking', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-5.svg',
			                'desc' => __( 'Better targeting', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-6.svg',
			                'desc' => __( 'Gain valuable insights', 'wc-wlfmc-wishlist' ),
		                ),
	                ),
	                'buttons'    => array(
		                array(
			                'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
			                'btn_url'   => 'https://moreconvert.com/2l9q',
			                'btn_class' => 'btn-flat btn-orange',
			                'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
		                ),
		                array(
			                'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
			                'btn_url'   => 'https://moreconvert.com/aqxv',
			                'btn_class' => 'btn-flat btn-get-start',
			                'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
		                ),
	                ),
                );
                ?>
                <div id="modal_automation" class="mct-modal modal_automation" style="display:none">
                    <div class="modal-overlay modal-toggle" data-modal="modal_automation"></div>
                    <div class="modal-wrapper modal-transition <?php echo isset( $modal['class'] ) ? esc_attr( $modal['class'] ) : ''; ?>">
                        <button class="modal-close modal-toggle"
                                data-modal="modal_automation"><span
                                    class="dashicons dashicons-no-alt"></span></button>
                        <div class="modal-body">
                            <div class="modal-image">
								<?php if ( isset( $modal['image_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $modal['image_link'] ); ?>" target="_blank">
									<?php endif; ?>
                                    <img src="<?php echo esc_url( $modal['image_url'] ); ?>" alt="image"/>
									<?php if ( isset( $modal['image_link'] ) ) : ?>
                                </a>
							<?php endif; ?>
                            </div>
                            <div class="modal-content">
								<?php if ( isset( $modal['title_icon'] ) ) : ?>
                                    <img src="<?php echo esc_url( $modal['title_icon'] ); ?>" width="72" height="72" alt="title"/>
								<?php endif; ?>

                                <h2><?php echo wp_kses_post( $modal['title'] ); ?></h2>
								<?php if ( isset( $modal['desc'] ) ) : ?>
                                    <p class="desc"><?php echo wp_kses_post( $modal['desc'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $modal['buttons'] ) ) : ?>
                                    <div class="modal-buttons">
										<?php foreach ( $modal['buttons'] as $button ) : ?>
                                            <a data-modal="modal_automation" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
                                                <span><?php echo esc_attr( $button['btn_label'] ); ?></span>
												<?php if ( isset( $button['btn_svg'] ) ) : ?>
													<?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<?php endif; ?>
                                            </a>
										<?php endforeach; ?>
                                    </div>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
							<?php if ( ! empty( $modal['features'] ) ) : ?>
								<?php if ( ! empty( $modal['features_title'] ) ) : ?>
                                    <p class="feature-title"><?php echo wp_kses_post( $modal['features_title'] ); ?></p>
								<?php endif; ?>
                                <div class="features-card">
                                    <div class="features">
										<?php foreach ( $modal['features'] as $features ) : ?>
                                            <div class="column d-flex f-center gap-5">
                                                <img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
                                                <span><?php echo esc_attr( $features['desc'] ); ?></span>
                                            </div>
										<?php endforeach; ?>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
			<?php if ( $outofstock_products ) : ?>
                <?php
                $modal = array(
	                'class'      => 'modal-large modal-horizontal',
	                'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/waitlist.gif',
	                'image_link' => 'https://moreconvert.com/g3ki',
	                'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
	                'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #FF2366">' . __( 'Waitlist', 'wc-wlfmc-wishlist' ) . '</span>',
	                'desc'       => __( 'Add a Customizable waitlist button, detailed waitlist page design, and counter option, you’ll never miss a sale again.', 'wc-wlfmc-wishlist' ),
	                'features'   => array(
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-3.svg',
			                'desc' => __( 'Pre-launch Waitlist', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-1.svg',
			                'desc' => __( 'Price Change Alert', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-2.svg',
			                'desc' => __( 'Flash Sale Waitlist', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-6.svg',
			                'desc' => __( 'Low Stock Alert', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-4.svg',
			                'desc' => __( 'Back in Stock Notification for Similar Products', 'wc-wlfmc-wishlist' ),
		                ),
		                array(
			                'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-5.svg',
			                'desc' => __( 'Integrated with other lists', 'wc-wlfmc-wishlist' ),
		                ),
	                ),
	                'buttons'    => array(
		                array(
			                'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
			                'btn_url'   => 'https://moreconvert.com/8ljf',
			                'btn_class' => 'btn-flat btn-orange',
			                'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
		                ),
		                array(
			                'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
			                'btn_url'   => 'https://moreconvert.com/g3ki',
			                'btn_class' => 'btn-flat btn-get-start',
			                'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
		                ),
	                ),
                )
                ?>
                <div id="modal_waitlist" class="mct-modal modal_waitlist" style="display:none">
                    <div class="modal-overlay modal-toggle" data-modal="modal_waitlist"></div>
                    <div class="modal-wrapper modal-transition <?php echo isset( $modal['class'] ) ? esc_attr( $modal['class'] ) : ''; ?>">
                        <button class="modal-close modal-toggle"
                                data-modal="modal_waitlist"><span
                                    class="dashicons dashicons-no-alt"></span></button>
                        <div class="modal-body">
                            <div class="modal-image">
								<?php if ( isset( $modal['image_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $modal['image_link'] ); ?>" target="_blank">
									<?php endif; ?>
                                    <img src="<?php echo esc_url( $modal['image_url'] ); ?>" alt="image"/>
									<?php if ( isset( $modal['image_link'] ) ) : ?>
                                </a>
							<?php endif; ?>
                            </div>
                            <div class="modal-content">
								<?php if ( isset( $modal['title_icon'] ) ) : ?>
                                    <img src="<?php echo esc_url( $modal['title_icon'] ); ?>" width="72" height="72" alt="title"/>
								<?php endif; ?>

                                <h2><?php echo wp_kses_post( $modal['title'] ); ?></h2>
								<?php if ( isset( $modal['desc'] ) ) : ?>
                                    <p class="desc"><?php echo wp_kses_post( $modal['desc'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $modal['buttons'] ) ) : ?>
                                    <div class="modal-buttons">
										<?php foreach ( $modal['buttons'] as $button ) : ?>
                                            <a data-modal="modal_waitlist" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
                                                <span><?php echo esc_attr( $button['btn_label'] ); ?></span>
												<?php if ( isset( $button['btn_svg'] ) ) : ?>
													<?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<?php endif; ?>
                                            </a>
										<?php endforeach; ?>
                                    </div>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
							<?php if ( ! empty( $modal['features'] ) ) : ?>
								<?php if ( ! empty( $modal['features_title'] ) ) : ?>
                                    <p class="feature-title"><?php echo wp_kses_post( $modal['features_title'] ); ?></p>
								<?php endif; ?>
                                <div class="features-card">
                                    <div class="features">
										<?php foreach ( $modal['features'] as $features ) : ?>
                                            <div class="column d-flex f-center gap-5">
                                                <img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
                                                <span><?php echo esc_attr( $features['desc'] ); ?></span>
                                            </div>
										<?php endforeach; ?>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
			<?php if ( $product_1 && $p1_count > 1 ) : ?>
                <?php
                    $modal = array(
	                    'class'      => 'modal-large modal-horizontal',
	                    'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/campaign.gif',
	                    'image_link' => 'https://moreconvert.com/m1c9',
	                    'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
	                    'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #E43B67">' . __( 'One-Shot Email', 'wc-wlfmc-wishlist' ) . '</span>',
	                    'desc'       => __( 'Select a specific group of users that add products to their lists and send them an email with dedicated coupons.', 'wc-wlfmc-wishlist' ),
	                    'features'   => array(
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-3.svg',
			                    'desc' => __( 'New Product Launch Announcement', 'wc-wlfmc-wishlist' ),
		                    ),
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-1.svg',
			                    'desc' => __( 'Cross-Selling and Upselling', 'wc-wlfmc-wishlist' ),
		                    ),
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-2.svg',
			                    'desc' => __( 'Seasonal Sales Promotion', 'wc-wlfmc-wishlist' ),
		                    ),
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-6.svg',
			                    'desc' => __( 'Re-engagement Campaigns', 'wc-wlfmc-wishlist' ),
		                    ),
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-4.svg',
			                    'desc' => __( 'Best-Selling Product Promotion', 'wc-wlfmc-wishlist' ),
		                    ),
		                    array(
			                    'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-5.svg',
			                    'desc' => __( 'One Time Personalized Recommendations', 'wc-wlfmc-wishlist' ),
		                    ),
	                    ),
	                    'buttons'    => array(
		                    array(
			                    'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
			                    'btn_url'   => 'https://moreconvert.com/broj',
			                    'btn_class' => 'btn-flat btn-orange',
			                    'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
		                    ),
		                    array(
			                    'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
			                    'btn_url'   => 'https://moreconvert.com/m1c9',
			                    'btn_class' => 'btn-flat btn-get-start',
			                    'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
		                    ),
	                    ),
                    )
                ?>
                <div id="modal_campaign" class="mct-modal modal_campaign" style="display:none">
                    <div class="modal-overlay modal-toggle" data-modal="modal_campaign"></div>
                    <div class="modal-wrapper modal-transition <?php echo isset( $modal['class'] ) ? esc_attr( $modal['class'] ) : ''; ?>">
                        <button class="modal-close modal-toggle"
                                data-modal="modal_campaign"><span
                                    class="dashicons dashicons-no-alt"></span></button>
                        <div class="modal-body">
                            <div class="modal-image">
								<?php if ( isset( $modal['image_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $modal['image_link'] ); ?>" target="_blank">
									<?php endif; ?>
                                    <img src="<?php echo esc_url( $modal['image_url'] ); ?>" alt="image"/>
									<?php if ( isset( $modal['image_link'] ) ) : ?>
                                </a>
							<?php endif; ?>
                            </div>
                            <div class="modal-content">
								<?php if ( isset( $modal['title_icon'] ) ) : ?>
                                    <img src="<?php echo esc_url( $modal['title_icon'] ); ?>" width="72" height="72" alt="title"/>
								<?php endif; ?>

                                <h2><?php echo wp_kses_post( $modal['title'] ); ?></h2>
								<?php if ( isset( $modal['desc'] ) ) : ?>
                                    <p class="desc"><?php echo wp_kses_post( $modal['desc'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $modal['buttons'] ) ) : ?>
                                    <div class="modal-buttons">
										<?php foreach ( $modal['buttons'] as $button ) : ?>
                                            <a data-modal="modal_campaign" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
                                                <span><?php echo esc_attr( $button['btn_label'] ); ?></span>
												<?php if ( isset( $button['btn_svg'] ) ) : ?>
													<?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<?php endif; ?>
                                            </a>
										<?php endforeach; ?>
                                    </div>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
							<?php if ( ! empty( $modal['features'] ) ) : ?>
								<?php if ( ! empty( $modal['features_title'] ) ) : ?>
                                    <p class="feature-title"><?php echo wp_kses_post( $modal['features_title'] ); ?></p>
								<?php endif; ?>
                                <div class="features-card">
                                    <div class="features">
										<?php foreach ( $modal['features'] as $features ) : ?>
                                            <div class="column d-flex f-center gap-5">
                                                <img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
                                                <span><?php echo esc_attr( $features['desc'] ); ?></span>
                                            </div>
										<?php endforeach; ?>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
            <?php
			$modal =  array(
				'class'      => 'modal-large modal-horizontal',
				'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/analytics.gif',
				'image_link' => 'https://moreconvert.com/cv2i',
				'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
				'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color:#A45EFF">' . __( 'Analytics', 'wc-wlfmc-wishlist' ) . '</span>',
				'desc'       => __( 'By analyzing individual user’s lists, you can tailor your marketing strategies to be more specific and effective, resulting in increased sales.', 'wc-wlfmc-wishlist' ),
				'features'   => array(
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-3.svg',
						'desc' => __( 'Segment your audience', 'wc-wlfmc-wishlist' ),
					),
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-6.svg',
						'desc' => __( 'Personalize email content', 'wc-wlfmc-wishlist' ),
					),
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-4.svg',
						'desc' => __( 'Track customer journey', 'wc-wlfmc-wishlist' ),
					),
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-2.svg',
						'desc' => __( 'Increase customer retention', 'wc-wlfmc-wishlist' ),
					),
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-5.svg',
						'desc' => __( 'Predict future sales', 'wc-wlfmc-wishlist' ),
					),
					array(
						'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-1.svg',
						'desc' => __( 'Identify popular categories', 'wc-wlfmc-wishlist' ),
					),
				),
				'buttons'    => array(
					array(
						'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
						'btn_url'   => 'https://moreconvert.com/wmma',
						'btn_class' => 'btn-flat btn-orange',
						'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
					),
					array(
						'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
						'btn_url'   => 'https://moreconvert.com/cv2i',
						'btn_class' => 'btn-flat btn-get-start',
						'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
					),
				),
			);
            ?>
            <div id="modal_analytics" class="mct-modal modal_analytics" style="display:none">
                    <div class="modal-overlay modal-toggle" data-modal="modal_analytics"></div>
                    <div class="modal-wrapper modal-transition <?php echo isset( $modal['class'] ) ? esc_attr( $modal['class'] ) : ''; ?>">
                        <button class="modal-close modal-toggle"
                                data-modal="modal_analytics"><span
                                    class="dashicons dashicons-no-alt"></span></button>
                        <div class="modal-body">
                            <div class="modal-image">
                                <?php if ( isset( $modal['image_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $modal['image_link'] ); ?>" target="_blank">
                                    <?php endif; ?>
                                    <img src="<?php echo esc_url( $modal['image_url'] ); ?>" alt="image"/>
                                    <?php if ( isset( $modal['image_link'] ) ) : ?>
                                </a>
                            <?php endif; ?>
                            </div>
                            <div class="modal-content">
                                <?php if ( isset( $modal['title_icon'] ) ) : ?>
                                    <img src="<?php echo esc_url( $modal['title_icon'] ); ?>" width="72" height="72" alt="title"/>
                                <?php endif; ?>

                                <h2><?php echo wp_kses_post( $modal['title'] ); ?></h2>
                                <?php if ( isset( $modal['desc'] ) ) : ?>
                                    <p class="desc"><?php echo wp_kses_post( $modal['desc'] ); ?></p>
                                <?php endif; ?>
                                <?php if ( ! empty( $modal['buttons'] ) ) : ?>
                                    <div class="modal-buttons">
                                        <?php foreach ( $modal['buttons'] as $button ) : ?>
                                            <a data-modal="modal_analytics" class="<?php echo esc_attr( $button['btn_class'] ); ?>" href="<?php echo esc_url( $button['btn_url'] ); ?>" target="<?php echo isset( $button['btn_target'] ) ? esc_attr( $button['btn_target'] ) : '_blank'; ?>">
                                                <span><?php echo esc_attr( $button['btn_label'] ); ?></span>
                                                <?php if ( isset( $button['btn_svg'] ) ) : ?>
                                                    <?php echo wlfmc_sanitize_svg( $button['btn_svg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <?php if ( ! empty( $modal['features'] ) ) : ?>
                                <?php if ( ! empty( $modal['features_title'] ) ) : ?>
                                    <p class="feature-title"><?php echo wp_kses_post( $modal['features_title'] ); ?></p>
                                <?php endif; ?>
                                <div class="features-card">
                                    <div class="features">
                                        <?php foreach ( $modal['features'] as $features ) : ?>
                                            <div class="column d-flex f-center gap-5">
                                                <img src="<?php echo esc_url( $features['icon'] ); ?>" width="40" height="40" alt="feature"/>
                                                <span><?php echo esc_attr( $features['desc'] ); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
			<?php
		}

		/**
		 * Display the list of campaigns.
		 *
		 * @return void
		 */
		public function display_users() {
			$posted_data = array(
				'conditions_group' => array(
					array(
						'conditions' => array(
							array(
								'condition_type' => 'list-type',
								'condition_list_operator' => 'have-any',
								'condition_list_type' => '',
								'condition_list_type_multiple' => array(
									'wishlist'
								),
								'condition_list_status' => '',
								'condition_list' => '',
								'condition_operator' => '',
								'condition_date_operator' => '',
								'condition_date_value' => '01/09/2024',
								'condition_daterange_value' => '',
								'condition_date_from' => '',
								'condition_date_to' => '',
								'date_formatted_value' => '2024-01-09',
								'date_from_formatted_value' => '',
								'date_to_formatted_value' => '',
								'condition_product_operator' => '',
								'condition_number_value' => '',
								'condition_product' => array(),
								'condition_product_cat' => array(),
								'condition_order_status' => '',
								'condition_order_purchased_operator' => '',
								'condition_order' => '',
								'condition_order_value_operator' => '',
								'condition_order_product_operator' => '',
								'condition_order_number_value' => '',
								'condition_order_product' => array(),
								'condition_order_product_cat' => array(),
								'condition_user_fields' => '',
								'condition_role_operator' => '',
								'condition_user_roles' => '',
								'condition_user_activity' => '',
								'condition_user_operator' => '',
								'condition_text_value' => '',
								'condition_post' => '',
								'condition_campaign_operator' => '',
								'condition_automation_operator' => '',
								'condition_campaigns' => '',
								'condition_automations' => ''
							),
							array(
								'condition_type' => 'list-status',
								'condition_list_operator' => '',
								'condition_list_type' => '',
								'condition_list_type_multiple' => array(),
								'condition_list_status' => 'waitlist',
								'condition_list' => 'list-total-price',
								'condition_operator' => 'lower',
								'condition_date_operator' => '',
								'condition_date_value' => '01/09/2024',
								'condition_daterange_value' => '',
								'condition_date_from' => '',
								'condition_date_to' => '',
								'date_formatted_value' => '2024-01-09',
								'date_from_formatted_value' => '',
								'date_to_formatted_value' => '',
								'condition_product_operator' => '',
								'condition_number_value' => 1,
								'condition_product' => array(),
								'condition_product_cat' => array(),
								'condition_order_status' => '',
								'condition_order_purchased_operator' => '',
								'condition_order' => '',
								'condition_order_value_operator' => '',
								'condition_order_product_operator' => '',
								'condition_order_number_value' => '',
								'condition_order_product' => array(),
								'condition_order_product_cat' => array(),
								'condition_user_fields' => '',
								'condition_role_operator' => '',
								'condition_user_roles' => '',
								'condition_user_activity' => '',
								'condition_user_operator' => '',
								'condition_text_value' => '',
								'condition_post' => '',
								'condition_campaign_operator' => '',
								'condition_automation_operator' => '',
								'condition_campaigns' => '',
								'condition_automations' => ''
							)
						)
					),
					array(
						'conditions' => array(
							array(
								'condition_type' => 'user-fields',
								'condition_list_operator' => '',
								'condition_list_type' => '',
								'condition_list_type_multiple' => array(),
								'condition_list_status' => '',
								'condition_list' => '',
								'condition_operator' => '',
								'condition_date_operator' => '',
								'condition_date_value' => '01/09/2024',
								'condition_daterange_value' => '',
								'condition_date_from' => '',
								'condition_date_to' => '',
								'date_formatted_value' => '2024-01-09',
								'date_from_formatted_value' => '',
								'date_to_formatted_value' => '',
								'condition_product_operator' => '',
								'condition_number_value' => '',
								'condition_product' => array(),
								'condition_product_cat' => array(),
								'condition_order_status' => '',
								'condition_order_purchased_operator' => '',
								'condition_order' => '',
								'condition_order_value_operator' => '',
								'condition_order_product_operator' => '',
								'condition_order_number_value' => '',
								'condition_order_product' => array(),
								'condition_order_product_cat' => array(),
								'condition_user_fields' => 'user_email',
								'condition_role_operator' => '',
								'condition_user_roles' => '',
								'condition_user_activity' => '',
								'condition_user_operator' => 'not-equal',
								'condition_text_value' => 'test',
								'condition_post' => '',
								'condition_campaign_operator' => '',
								'condition_automation_operator' => '',
								'condition_campaigns' => '',
								'condition_automations' => ''
							)
						)
					)
				)
			);
			$fields       = new MCT_Fields(
				array(
					'type'         => 'fields-type',
					'id'           => 'wlfmc_user_conditions',
					'fields'       => array(
						'columns-start'    => array(
							'type'    => 'columns-start',
							'columns' => 1,
						),
						'column-start'     => array(
							'type'              => 'column-start',
							'class'             => 'flexible-rows',
							'custom_attributes' => array(
								'style' => 'width:100%',
							),
						),
						'conditions_group' => array(
							'label'           => __( 'Filters', 'wc-wlfmc-wishlist' ),
							'type'            => 'nested-repeater',
							'add_new_label'   => __( 'OR', 'wc-wlfmc-wishlist' ),
							'repeater_fields' => array(
								'conditions' => array(
									'label'           => __( 'Conditions', 'wc-wlfmc-wishlist' ),
									'type'            => 'inner-repeater',
									'desc'            => __( 'Or', 'wc-wlfmc-wishlist' ),
									'add_button'      => __( 'And', 'wc-wlfmc-wishlist' ),
									'remove_button'   => '<span class="dashicons dashicons-no-alt"></span>',
									'repeater_fields' => array(
										'condition_type'  => array(
											'label'   => __( 'Condition Type', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'options' => array(
												'' => __( 'Please, Select Filter Type', 'wc-wlfmc-wishlist' ),
												array(
													'label'   => __( 'Lists', 'wc-wlfmc-wishlist' ),
													'options' => array(
														'list-type'            => __( 'List Type', 'wc-wlfmc-wishlist' ),
														'list-status'          => __( 'List Status', 'wc-wlfmc-wishlist' ),
													),
												),
												array(
													'label'   => __( 'Users', 'wc-wlfmc-wishlist' ),
													'options' => array(
														'user-fields'   => __( 'Fields', 'wc-wlfmc-wishlist' ),
														'user-roles'    => __( 'Roles', 'wc-wlfmc-wishlist' ),
														'user-activity' => __( 'Activity', 'wc-wlfmc-wishlist' ),
													),
												),
												array(
													'label'   => __( 'Orders', 'wc-wlfmc-wishlist' ),
													'options' => array(
														'order-status'        => __( 'Order Status', 'wc-wlfmc-wishlist' ),
													),
												),
												array(
													'label'   => __( 'Marketing Toolkits', 'wc-wlfmc-wishlist' ),
													'options' => array(
														'campaign-status'   => __( 'Campaigns State', 'wc-wlfmc-wishlist' ),
														'automation-status' => __( 'Automations State', 'wc-wlfmc-wishlist' ),
													),
												),
											),
											'default' => '',
											'custom_attributes' => array(
												'autocomplete' => 'off',
											),
										),
										'condition_list_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'have'     => __( 'Have', 'wc-wlfmc-wishlist' ),
												'did-not-have' => __( 'Did not have', 'wc-wlfmc-wishlist' ),
												'have-any' => __( 'Have any', 'wc-wlfmc-wishlist' ),
												'did-not-have-any' => __( 'Did not have any', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'list-type',
											),
											'default'      => 'have',
										),
										'condition_list_type' => array(
											'label'        => __( 'List Type', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'class'        => 'select2-trigger',
											'options'      => array(
												'wishlist' => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
												'lists'    => __( 'Multi-list', 'wc-wlfmc-wishlist' ),
												'waitlist' => __( 'All Waitlist', 'wc-wlfmc-wishlist' ),
												'on-sale'  => __( '-- On Sale', 'wc-wlfmc-wishlist' ),
												'back-in-stock' => __( '-- Back in Stock', 'wc-wlfmc-wishlist' ),
												'low-stock' => __( '-- Low Stock', 'wc-wlfmc-wishlist' ),
												'price-change' => __( '-- Price Change', 'wc-wlfmc-wishlist' ),
												'save-for-later' => __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' ),
												'all-lists' => __( 'All Lists', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-type',
												),
												array(
													'id' => 'condition_list_operator',
													'value' => 'have,did-not-have',
												),
											),
											'default'      => 'all-lists',
										),
										'condition_list_type_multiple' => array(
											'label'        => __( 'List Type', 'wc-wlfmc-wishlist' ),
											'type'         => 'multi-select',
											'class'        => 'select2-trigger',
											'options'      => array(
												'wishlist' => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
												'lists'    => __( 'Multi-list', 'wc-wlfmc-wishlist' ),
												'on-sale'  => __( 'Waitlist On Sale', 'wc-wlfmc-wishlist' ),
												'back-in-stock' => __( 'Waitlist Back in Stock', 'wc-wlfmc-wishlist' ),
												'low-stock' => __( 'Waitlist Low Stock', 'wc-wlfmc-wishlist' ),
												'price-change' => __( 'Waitlist Price Change', 'wc-wlfmc-wishlist' ),
												'save-for-later' => __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-type',
												),
												array(
													'id' => 'condition_list_operator',
													'value' => 'have-any,did-not-have-any',
												),

											),
											'default'      => 'wishlist',
										),
										'condition_list_status' => array(
											'label'        => __( 'List Type', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'wishlist' => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
												'lists'    => __( 'Multi-list', 'wc-wlfmc-wishlist' ),
												'waitlist' => __( 'All Waitlist', 'wc-wlfmc-wishlist' ),
												'on-sale'  => __( '-- On Sale', 'wc-wlfmc-wishlist' ),
												'back-in-stock' => __( '-- Back in Stock', 'wc-wlfmc-wishlist' ),
												'low-stock' => __( '-- Low Stock', 'wc-wlfmc-wishlist' ),
												'price-change' => __( '-- Price Change', 'wc-wlfmc-wishlist' ),
												'save-for-later' => __( 'Next Purchase Cart', 'wc-wlfmc-wishlist' ),
												'any-lists' => __( 'Any Lists', 'wc-wlfmc-wishlist' ),
												'all-lists' => __( 'All lists together', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
											),
											'default'      => 'all-lists',
										),
										'condition_list'  => array(
											'label'        => __( 'List Status', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'list-product-count'   => __( 'Product Count', 'wc-wlfmc-wishlist' ),
												'list-total-price'     => __( 'Total price', 'wc-wlfmc-wishlist' ),
												'list-product'         => __( 'Product(s)', 'wc-wlfmc-wishlist' ),
												'list-product-category' => __( 'Product(s) Category', 'wc-wlfmc-wishlist' ),
												'list-added-date'      => __( 'Product Added to List Date', 'wc-wlfmc-wishlist' ),
												'list-purchased-date'  => __( 'Product Purchase Date', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
											),
											'default'      => 'list-product-count',
										),
										'condition_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'equal'  => __( 'Equal to', 'wc-wlfmc-wishlist' ),
												'not-equal' => __( 'Not equal to', 'wc-wlfmc-wishlist' ),
												'lower'  => __( 'Lower than', 'wc-wlfmc-wishlist' ),
												'higher' => __( 'Higher than', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-product-count,list-total-price',
												),

											),
											'default'      => 'equal',
										),
										'condition_date_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'is'       => __( 'Is', 'wc-wlfmc-wishlist' ),
												'is-before' => __( 'Is before', 'wc-wlfmc-wishlist' ),
												'is-on-before' => __( 'Is on or before', 'wc-wlfmc-wishlist' ),
												'is-after' => __( 'Is after', 'wc-wlfmc-wishlist' ),
												'is-on-after' => __( 'Is on or after', 'wc-wlfmc-wishlist' ),
												'is-between' => __( 'Is between', 'wc-wlfmc-wishlist' ),
												'is-not-between' => __( 'Is not between', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-added-date,list-purchased-date',
												),

											),
											'default'      => 'equal',
										),
										'condition_date_value' => array(
											'label'        => __( 'Date', 'wc-wlfmc-wishlist' ),
											'type'         => 'datepicker',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-added-date,list-purchased-date',
												),
												array(
													'id' => 'condition_date_operator',
													'value' => 'is,is-before,is-on-before,is-after,is-on-after',
												),
											),
										),
										'condition_daterange_value' => array(
											'label'        => __( 'Date', 'wc-wlfmc-wishlist' ),
											'type'         => 'daterange',
											'from_field_name' => 'condition_date_from',
											'to_field_name' => 'condition_date_to',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-added-date,list-purchased-date',
												),
												array(
													'id' => 'condition_date_operator',
													'value' => 'is-between,is-not-between',
												),
											),
										),
										'condition_product_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'include' => __( 'Include', 'wc-wlfmc-wishlist' ),
												'not-include' => __( 'Does not include', 'wc-wlfmc-wishlist' ),
												'include-any' => __( 'Include any', 'wc-wlfmc-wishlist' ),
												'not-include-any' => __( 'Does not include any', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-product,list-product-category',
												),
											),
											'default'      => 'exist',
										),
										'condition_number_value' => array(
											'label'        => __( 'Value', 'wc-wlfmc-wishlist' ),
											'type'         => 'number',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-product-count,list-total-price',
												),
											),
										),
										'condition_product' => array(
											'label'        => __( 'Product(s)', 'wc-wlfmc-wishlist' ),
											'type'         => 'search-product',
											'data'         => array(
												'action' => 'woocommerce_json_search_products_and_variations',
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-product',
												),
											),
										),
										'condition_product_cat' => array(
											'label'        => __( 'Product(s) Category', 'wc-wlfmc-wishlist' ),
											'type'         => 'search-product-cat',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'list-status',
												),
												array(
													'id' => 'condition_list',
													'value' => 'list-product-category',
												),
											),
										),
										'condition_order_status' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => $this->get_order_statuses(),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'order-status',
											),
										),
										'condition_order_purchased_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'buy-through-coupon' => __( 'Buy through Mc Coupon', 'wc-wlfmc-wishlist' ),
												'buy-through-list'    => __( 'Buy through Mc List', 'wc-wlfmc-wishlist' ),
												'without-list'       => __( 'Without Mc Lists', 'wc-wlfmc-wishlist' ),
												'all-orders'       => __( 'All orders', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order_status',
													'value' =>  $this->get_paid_statuses(),
												),

											),
											'default'      => 'buy-through-coupon',
										),
										'condition_order' => array(
											'label'        => __( 'Condition', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'order-product-count'   => __( 'Product Count', 'wc-wlfmc-wishlist' ),
												'order-total-price'     => __( 'Total price', 'wc-wlfmc-wishlist' ),
												'order-product'         => __( 'Product(s)', 'wc-wlfmc-wishlist' ),
												'order-product-category' => __( 'Product(s) Category', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
											),
											'default'      => 'order-product-count',
										),
										'condition_order_value_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'equal'  => __( 'Equal to', 'wc-wlfmc-wishlist' ),
												'not-equal' => __( 'Not equal to', 'wc-wlfmc-wishlist' ),
												'lower'  => __( 'Lower than', 'wc-wlfmc-wishlist' ),
												'higher' => __( 'Higher than', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order',
													'value' => 'order-product-count,order-total-price',
												),

											),
											'default'      => 'equal',
										),
										'condition_order_product_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'include' => __( 'Include', 'wc-wlfmc-wishlist' ),
												'not-include' => __( 'Does not include', 'wc-wlfmc-wishlist' ),
												'include-any' => __( 'Include any', 'wc-wlfmc-wishlist' ),
												'not-include-any' => __( 'Does not include any', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order',
													'value' => 'order-product,order-product-category',
												),
											),
											'default'      => 'exist',
										),
										'condition_order_number_value' => array(
											'label'        => __( 'Value', 'wc-wlfmc-wishlist' ),
											'type'         => 'number',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order',
													'value' => 'order-product-count,order-total-price',
												),
											),
										),
										'condition_order_product' => array(
											'label'        => __( 'Product(s)', 'wc-wlfmc-wishlist' ),
											'type'         => 'search-product',
											'data'         => array(
												'action' => 'woocommerce_json_search_products_and_variations',
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order',
													'value' => 'order-product',
												),
											),
										),
										'condition_order_product_cat' => array(
											'label'        => __( 'Product(s) Category', 'wc-wlfmc-wishlist' ),
											'type'         => 'search-product-cat',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'order-status',
												),
												array(
													'id' => 'condition_order',
													'value' => 'order-product-category',
												),
											),
										),
										'condition_user_fields' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => array(
												'user_login'           => __( 'Username', 'woocommerce' ),
												'user_email'           => __( 'User Email', 'woocommerce' ),
												'billing_first_name'  => __( 'Billing First Name', 'woocommerce' ),
												'billing_last_name'   => __( 'Billing Last Name', 'woocommerce' ),
												'billing_company'     => __( 'Billing Company', 'woocommerce' ),
												'billing_city'        => __( 'Billing City', 'woocommerce' ),
												'billing_postcode'    => __( 'Billing Postal/Zip Code', 'woocommerce' ),
												'billing_state'       => __( 'Billing State', 'woocommerce' ),
												'billing_country'     => __( 'Billing Country / Region', 'woocommerce' ),
												'billing_phone'       => __( 'Billing Phone Number', 'woocommerce' ),
												'billing_email'       => __( 'Billing Email Address', 'woocommerce' ),
												'shipping_first_name' => __( 'Shipping First Name', 'woocommerce' ),
												'shipping_last_name'  => __( 'Shipping Last Name', 'woocommerce' ),
												'shipping_company'    => __( 'Shipping Company', 'woocommerce' ),
												'shipping_city'       => __( 'Shipping City', 'woocommerce' ),
												'shipping_postcode'   => __( 'Shipping Postal/Zip Code', 'woocommerce' ),
												'shipping_state'      => __( 'Shipping State', 'woocommerce' ),
												'shipping_country'    => __( 'Shipping Country / Region', 'woocommerce' ),
												'shipping_phone'      => __( 'Shipping Phone Number', 'woocommerce' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'user-fields',
											),
										),
										'condition_role_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'equal' => __( 'Equals', 'wc-wlfmc-wishlist' ),
												'not-equal' => __( 'Does not equal', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'user-roles',
											),
											'default'      => 'equal',
										),
										'condition_user_roles' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => $this->get_user_roles(),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'user-roles',
											),
										),
										'condition_user_activity' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => array(
												'commenting-any'      => __( 'Commenting any content', 'wc-wlfmc-wishlist' ),
												'commenting-specific' => __( 'Commenting specific content', 'wc-wlfmc-wishlist' ),
												'post-modification'   => __( 'Post Creation/Edit', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'user-activity',
											),
										),
										'condition_user_operator' => array(
											'label'        => __( 'Operate', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'options'      => array(
												'equal'   => __( 'Equals', 'wc-wlfmc-wishlist' ),
												'not-equal' => __( 'Does not equal', 'wc-wlfmc-wishlist' ),
												'contain' => __( 'Contains', 'wc-wlfmc-wishlist' ),
												'not-contain' => __( 'Does no contain', 'wc-wlfmc-wishlist' ),
												'set'     => __( 'Is set', 'wc-wlfmc-wishlist' ),
												'not-set' => __( 'Is not set', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'user-fields',
											),
											'default'      => 'equal',
										),
										'condition_text_value' => array(
											'label'        => __( 'Value', 'wc-wlfmc-wishlist' ),
											'type'         => 'text',
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'user-fields',
												),
											),
										),
										'condition_post'  => array(
											'label'        => __( 'product(s)|post(s)|page(s)', 'wc-wlfmc-wishlist' ),
											'type'         => 'search-post',
											'custom_attributes' => array(
												'data-post-types' => 'product,post,page',
											),
											'dependencies' => array(
												array(
													'id' => 'condition_type',
													'value' => 'user-activity',
												),
												array(
													'id' => 'condition_user_activity',
													'value' => 'commenting-specific',
												),

											),
										),
										'condition_campaign_operator' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => array(
												'sent'     => __( 'was sent', 'wc-wlfmc-wishlist' ),
												'not-sent' => __( 'was not sent', 'wc-wlfmc-wishlist' ),
												'opened'   => __( 'was opened', 'wc-wlfmc-wishlist' ),
												'not-opened' => __( 'was not opened', 'wc-wlfmc-wishlist' ),
												'clicked'  => __( 'was clicked', 'wc-wlfmc-wishlist' ),
												'not-clicked' => __( 'was not clicked', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'campaign-status',
											),
										),
										'condition_automation_operator' => array(
											'label'        => '',
											'type'         => 'select',
											'options'      => array(
												'active' => __( 'Active in', 'wc-wlfmc-wishlist' ),
												'not-active' => __( 'Not active in', 'wc-wlfmc-wishlist' ),
												'completed' => __( 'Has completed', 'wc-wlfmc-wishlist' ),
											),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'automation-status',
											),
										),
										'condition_campaigns' => array(
											'label'        => __( 'Campaigns', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'class'        => 'select2-trigger',
											'options'      => array( 1 => 'Test' ),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'campaign-status',
											),
										),
										'condition_automations' => array(
											'label'        => __( 'Automations', 'wc-wlfmc-wishlist' ),
											'type'         => 'select',
											'class'        => 'select2-trigger',
											'options'      => array( 1 => 'Test' ),
											'dependencies' => array(
												'id'    => 'condition_type',
												'value' => 'automation-status',
											),
										),
									),
								),
							),
						),
						'buttons'          => array(
							'header_hide' => true,
							'type'        => 'group-fields',
							'fields'      => array(
								'submit' => array(
									'label'             => '',
									'class'             => 'btn-primary btn-secondary apply-filter',
									'type'              => 'button',
									'custom_attributes' => array(
										'name'  => 'filter',
										'value' => 'do_filter',
									),
									'default'           => __( 'Apply Filter', 'wc-wlfmc-wishlist' ),
								),
							),
						),
						'column-end'       => array(
							'type' => 'column-end',
						),
						'columns-end'      => array(
							'type' => 'columns-end',
						),
					),
					'field_values' => $posted_data,
				)
			);
			?>
            <div class="mct-article mar-bot-20 modal-toggle" data-modal="modal_analytics">
                <div class="article-title  mar-bot-20">
                    <h2 class="">
                        <span><?php esc_html_e( 'Users', 'wc-wlfmc-wishlist' ); ?></span>
                    </h2>
                    <p class="description"><?php esc_html_e( 'Find a specific group of users by setting different conditions', 'wc-wlfmc-wishlist' ); ?></p>
                </div>
                <div id="customize_filters" style="pointer-events: none;display:none">
                    <?php $fields->output(); ?>
                </div>
                <script>
	                (function() {
		                document.addEventListener("DOMContentLoaded", function() {
			                setTimeout(function() {
				                document.getElementById("customize_filters").style.display = 'block';
			                }, 1000);
		                });
	                })();

                </script>
            </div>
            <div id="wlfmc_analytics_users_form" class="modal-toggle" data-modal="modal_analytics">
                <div id="wlfmc_analytics_users_table" class="mct-article" style="pointer-events: none;">
					<?php
                    $users = new WLFMC_Analytics_Users_Table_Demo();
					$users->prepare_items();
					$users->search_box( esc_html__( 'Search', 'wc-wlfmc-wishlist' ), 'users-search' );
					$users->display();
					?>
                </div>
            </div>
			<?php
			$this->display_modal(
				array(
					array(
						'image_id' => 4,
						'title'    => __( 'Set conditions based on users\' Role, Purchase history, Products on lists and so more.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 16,
						'title'    => __( 'Check out your user lists to get what they\'re into and how they act.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 13,
						'title'    => __( 'Track down folks who haven\'t been active, reach out to them with campaigns.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 18,
						'title'    => __( 'Find list-loving customers and give them sweet deals on their favorite stuff.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 19,
						'title'    => __( 'Find users who are into high-value items and create special offers for them.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 13,
						'title'    => __( 'Find users who are influencers and partner up with them for marketing campaigns.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 17,
						'title'    => __( 'Export users to telemarketing and other marketing channels you want.', 'wc-wlfmc-wishlist' ),
					),
				)
			);
		}

		/**
		 * Display the list of campaign items.
		 *
		 * @return void
		 */
		public function display_products() {
            ?>
			<div class="mct-article mar-bot-20 modal-toggle" data-modal="modal_analytics">
				<div class="article-title">
					<div class="d-flex space-between f-center">
						<h2 class="">
							<span><?php esc_html_e( 'Products', 'wc-wlfmc-wishlist' ); ?></span>
						</h2>
					</div>
				</div>
				<div style="pointer-events: none;" id="wlfmc_analytics_products_form" class="wlfmc-hide-delete-on-change">
					<?php
					$products = new WLFMC_Analytics_Products_Table_Demo();
					$products->prepare_items();
					$products->search_box( esc_html__( 'Search', 'wc-wlfmc-wishlist' ), 'products-search' );
					$products->display();
					?>
				</div>
			</div>
			<?php
            $this->display_modal(
                array(
                    array(
                       'image_id' => 12,
                       'title'    => __( 'Ensure that popular items, frequently added to lists, are always in stock.', 'wc-wlfmc-wishlist' ),
                    ),
                    array(
                        'image_id' => 11,
                        'title'    => __( 'Improve suggestions for similar products to boost sales.', 'wc-wlfmc-wishlist' ),
                    ),
                    array(
                        'image_id' => 8,
                        'title'    => __( 'Optimize prices based on what customers are willing to pay for frequently added products.', 'wc-wlfmc-wishlist' ),
                    ),
                    array(
                        'image_id' => 9,
                        'title'    => __( 'Identify popular products for more informed inventory and marketing decisions.', 'wc-wlfmc-wishlist' ),
                    ),
                    array(
                        'image_id' => 10,
                        'title'    => __( 'Identify top product descriptions by list additions and purchases; optimize others accordingly.', 'wc-wlfmc-wishlist' ),
                    ),
                )
            );
		}

		/**
		 * Display the all list of users .
		 *
		 * @return void
		 */
		public function display_lists() {
			?>
			<div class="mct-article mar-bot-20 modal-toggle" data-modal="modal_analytics">
				<div class="article-title">
					<div class="d-flex space-between f-center">
						<h2 class="">
							<span><?php esc_html_e( 'User lists', 'wc-wlfmc-wishlist' ); ?></span>
						</h2>
					</div>
				</div>
                <div style="pointer-events: none;">
	                <?php
	                $statistic_lists = new WLFMC_Analytics_Lists_Statistics_Table_Demo();
	                $statistic_lists->prepare_items();
	                $statistic_lists->display();
	                ?>
                </div>
			</div>
			<div class="mct-article mar-bot-20 modal-toggle" data-modal="modal_analytics">
				<div style="pointer-events: none;" id="wlfmc_analytics_lists_form">
					<?php
					$lists = new WLFMC_Analytics_Lists_Table_Demo();
					$lists->prepare_items();
					$lists->search_box( esc_html__( 'Search', 'wc-wlfmc-wishlist' ), 'lists-search' );
					$lists->display();
					?>
				</div>
			</div>
			<?php
			$this->display_modal(
				array(
					array(
						'image_id' => 15,
						'title'    => __( 'Visit each user lists without any limitation.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 14,
						'title'    => __( 'Discover each lists sales and conversion rate.', 'wc-wlfmc-wishlist' ),
					),
					array(
						'image_id' => 7,
						'title'    => __( 'Get insight about lists engagement and idea to make it better.', 'wc-wlfmc-wishlist' ),
					),
				)
			);
		}

        /**
         * Display modal
         * @param array $features
         * @return void
         */
		public function display_modal( $features ) {
			?>
            <div id="modal_analytics" class="mct-modal modal_analytics">
                <div class="modal-overlay modal-toggle" data-modal="modal_analytics"></div>
                <div class="modal-wrapper modal-transition modal-large modal-horizontal without-image">
                    <button class="modal-close modal-toggle" data-modal="modal_analytics">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                    <div class="modal-body">
                        <div class="modal-content">
                            <div class="center-align" style="max-width: 400px;margin: 0 auto 20px;">
                                <img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/crown.svg" width="72" height="72" alt="crown" />
                                <h2><?php esc_attr_e( 'Unlock Now!', 'wc-wlfmc-wishlist' ); ?><br><span style="color: #A45EFF"><?php esc_attr_e( 'Analytics', 'wc-wlfmc-wishlist' ); ?></span></h2>
                                <p class="desc"><?php esc_attr_e( 'By analyzing individual user’s lists, you can tailor your marketing strategies to be more specific and effective, resulting in increased sales.', 'wc-wlfmc-wishlist' ); ?></p>
                                <a data-modal="modal_analytics" class="orange-btn btn-primary d-inline-flex f-center gap-5" href="https://moreconvert.com/wmma" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <g transform="translate(-375 -149)">
                                            <rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect>
                                            <path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path>
                                        </g>
                                    </svg>
                                    <span><?php esc_attr_e( 'Upgrade Now', 'wc-wlfmc-wishlist' ); ?></span>
                                </a>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="features-card">
                            <div class="features">
								<?php if ( ! empty( $features ) ) : ?>
									<?php foreach ( $features as $feature ) : ?>
                                        <div class="d-flex f-center gap-5">
                                            <img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/analytics/feature-<?php echo esc_attr( $feature['image_id'] ) ;?>.svg" width="40" height="40" alt="features" />
                                            <span><?php echo esc_attr( $feature['title'] ); ?></span>
                                        </div>
									<?php endforeach; ?>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}

		/**
		 * Get order statuses
		 *
		 * @return mixed|void
		 */
		public function get_order_statuses() {
			$status = wc_get_order_statuses();
			if ( isset( $status['wc-checkout-draft'] ) ) {
				unset( $status['wc-checkout-draft'] );
			}
			$status['all-paid']   = __( 'All paid status', 'wc-wlfmc-wishlist' );
			$status['all-unpaid'] = __( 'All unpaid status', 'wc-wlfmc-wishlist' );
			return apply_filters( 'wlfmc_conditions_order_statuses', $status );
		}

		/**
		 * Get page statuses
		 *
		 * @return string
		 */
		public function get_paid_statuses() {
			$purchased = apply_filters( 'wlfmc_conditions_paid_order_statuses', wc_get_is_paid_statuses() );
			$purchased = array_map(
				function( $product ) {
					return 'wc-' . $product;
				},
				$purchased
			);
			return ! empty( $purchased ) ? implode( ',', $purchased ) : '';
		}

		/**
		 * Get user roles
		 *
		 * @return mixed|void
		 */
		public function get_user_roles() {
			return apply_filters( 'wlfmc_conditions_user_roles', wp_roles()->get_names() );
		}

		/**
		 * Returns single instance of the class.
		 *
		 * @access public
		 *
		 * @return WLFMC_Analytics_Admin_Demo
		 */
		public static function get_instance(): WLFMC_Analytics_Admin_Demo {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

}
/**
 * Unique access to instance of WLFMC_Analytics_Admin_Demo class.
 *
 * @return WLFMC_Analytics_Admin_Demo
 */
function WLFMC_Analytics_Admin_Demo(): WLFMC_Analytics_Admin_Demo {  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Analytics_Admin_Demo::get_instance();
}

