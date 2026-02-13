<?php
/**
 * Smart Wishlist Admin Notice
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.2.0
 * @version 1.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Admin_Notice' ) ) {
	/**
	 * This class handles admin_notice for wishlist plugin
	 */
	class WLFMC_Admin_Notice {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Admin_Notice
		 */
		protected static $instance;

		/**
		 * Styles added status
		 *
		 * @var bool
		 * @since 1.7.6
		 */
		public $style_loaded = false;
		/**
		 * Constructor
		 *
		 * @version 1.8.5
		 * @return void
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'dismiss_notice' ) );
			// add_action( 'admin_notices', array( $this, 'after_skip_wizard' ) );
			// add_action( 'admin_notices', array( $this, 'after_finish_wizard' ) );
			add_action( 'admin_notices', array( $this, 'update_tables' ) );
			add_action( 'admin_notices', array( $this, 'wpml_notice' ) );
			add_action( 'admin_notices', array( $this, 'black_friday_notice' ) );

		}

		/**
		 * Get Next black friday
		 *
         * @since 1.8.7
		 * @return false|int
		 */
		private function get_next_black_friday() {
			$year         = gmdate( 'Y' );
			$november     = strtotime( "November 1 $year" );
			$black_friday = strtotime( 'fourth Friday', $november );
			// If Black Friday has already passed this year, get it for next year.
			/*if ( $black_friday < time() ) {
				$year++;
				$november     = strtotime( "November 1 $year" );
				$black_friday = strtotime( 'fourth Friday', $november );
			}*/
			return $black_friday;
		}

		/**
		 * Print Black Friday Notice
		 *
         * @since 1.8.5
		 * @return void
		 */
		public function black_friday_notice() {
			if ( defined( 'MC_WLFMC_PREMIUM' ) || get_option( 'wlfmc_premium_version' ) ) {
				return;
			}

			$year = gmdate( 'Y' );

			if ( ! is_super_admin() || wlfmc_is_true( get_option( 'wlfmc-black-friday-' . $year . '-notice' ) ) ) {
				return;
			}

			$black_friday = $this->get_next_black_friday();
			$start_date   = strtotime( '-4 days', $black_friday );
			$end_date     = strtotime( '+7 days', $black_friday );
			$current_time = time();
			$dismiss_url  = wp_nonce_url(
				add_query_arg(
					array(
						'wlfmc-black-friday-' . $year . '-dismiss' => 1,
					),
					$this->clean_url()
				),
				'wlfmc-black-friday-' . $year . '-dismiss-nonce'
			);
			if ( $current_time >= $start_date && $current_time <= $end_date ) {
				$this->styles();
				?>
				<div id="wlfmc-black-friday" class="notice wlfmc-notice wlfmc-black-friday">
					<span class="wlfmc-line-up"></span>
					<div class="wlfmc-notice-with-image">
						<div class="wlfmc-image-wrapper">
							<img src="<?php echo esc_url( MC_WLFMC_URL . 'assets/backend/images/black-friday-banner.png' ); ?>" alt="black-friday" width="234px" height="210px" />
						</div>
						<div class="wlfmc-content-wrapper">
							<h2><?php echo wp_kses_post( __( "Don't Miss MoreConvert Exclusive <span class='yellow-text'>Black Friday Deals!</span>", 'wc-wlfmc-wishlist' ) ); ?></h2>
							<a href="<?php echo esc_url( $dismiss_url ); ?>" class="dismiss-btn">
								<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
									<path id="close" d="M13.076,12.244,8.832,8l4.244-4.244a.588.588,0,1,0-.832-.832h0L8,7.168,3.756,2.924a.588.588,0,0,0-.832.832h0L7.168,8,2.924,12.244a.588.588,0,1,0,.832.832h0L8,8.832l4.244,4.244a.588.588,0,0,0,.832-.832Z" transform="translate(-2.752 -2.752)" fill="#fff"/>
								</svg>
							</a>
							<p>
								<?php esc_attr_e( 'Upgrade to Pro and unlock advanced features at unbeatable prices! This is a once-a-year opportunity.', 'wc-wlfmc-wishlist' ); ?>
							</p>
							<p class="wlfmc-notice-action-wrapper">
								<span class="wlfmc-btn-wrapper">
									<a href="https://moreconvert.com/xb4y" class="btn-notice-2" target="_blank">
										<?php esc_attr_e( 'Get the Discount Now!', 'wc-wlfmc-wishlist' ); ?>
									</a>
									<small><?php esc_attr_e( 'Take advantage of this incredible deal right now!', 'wc-wlfmc-wishlist' ); ?></small>
								</span>
								<span id="wlfmc_blackfriday_countdown" class="wlfmc-timer"></span>
							</p>
						</div>
					</div>
					<span class="wlfmc-line-down"></span>
				</div>
				<script>
					// Set the date we're counting down to
					var countDownDate = new Date("<?php echo esc_attr( gmdate( 'Y-m-d H:i:s', $end_date ) ); ?>").getTime();

					// Update the count-down every 1 second
					var x = setInterval(function() {
						var now = new Date().getTime();
						var distance = countDownDate - now;

						// Time calculations for days, hours, minutes and seconds.
						var days = Math.floor(distance / (1000 * 60 * 60 * 24));
						var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
						var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
						var seconds = Math.floor((distance % (1000 * 60)) / 1000);

						// Display the result in the element with id="wlfmc_blackfriday_countdown".
						document.getElementById("wlfmc_blackfriday_countdown").innerHTML = "<span id='wlfmc-notice-timer-days'>"
							+ days + "d </span> : <span id='wlfmc-notice-timer-hours'>"
							+ hours + "h </span> : <span id='wlfmc-notice-timer-minutes'>"
							+ minutes + "m </span> : <span id='wlfmc-notice-timer-seconds'>"
							+ seconds + "s </span>";

						// If the count-down is over, write some text.
						if (distance < 0) {
							clearInterval(x);
							document.getElementById("wlfmc-black-friday").remove();
						}
					}, 1000);
				</script>
				<?php
			}
		}

		/**
		 * Add admin delayed notice.
		 *
		 * @since 1.2.1
		 * @version 1.2.1
		 */
		public function notice() {

			if ( ! is_super_admin() || true === wlfmc_is_true( get_option( 'wlfmc_notice_disabled' ) ) ) {
				return;
			}
			$activate_date = get_option( 'wlfmc_wishlist_activation_date' );
			$today         = strtotime( gmdate( 'Y-m-d' ) );
			if ( ! $activate_date ) {
				update_option( 'wlfmc_wishlist_activation_date', $today );
				$activate_date = $today;
			} else {
				$activate_date = strtotime( $activate_date );
			}

			$args = array(
				array(
					'title'       => '<span class="green-text">' . esc_html__( 'Need More Power for MC Wishlist?', 'wc-wlfmc-wishlist' ) . '</span>',
					'content'     => esc_html__( 'If you need a stronger lists, you can sync it with more plugins and get more powerful sales tools; I suggest you check out the premium version page', 'wc-wlfmc-wishlist' ),
					'btn_title'   => esc_html__( 'Check it Now', 'wc-wlfmc-wishlist' ),
					'btn_class'   => 'btn-notice green-btn',
					'btn_url'     => 'https://moreconvert.com/ag7w',
					'btn_target'  => '_blank',
					'dismiss_url' => '',
				),
				array(
					'title'       => '<span class="orange-text">' . esc_html__( 'Upgrade your website\'s potential with a FREE premium tour', 'wc-wlfmc-wishlist' ) . ' </span>' . esc_html__( 'Your gift for installing MC Wishlist plugin', 'wc-wlfmc-wishlist' ),
					'content'     => esc_html__( 'Click below to demo the exclusive features, and witness the profitable and stunning transformation. Ready to elevate your website game?', 'wc-wlfmc-wishlist' ),
					'btn_title'   => esc_html__( 'Try It Now', 'wc-wlfmc-wishlist' ),
					'btn_class'   => 'btn-notice orange-btn inverse-btn',
					'btn_url'     => 'https://moreconvert.com/11s9',
					'btn_target'  => '_blank',
					'dismiss_url' => '',
				),

				array(
					'image'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/growth-profit.gif" class="" width="175px" height="156px" />',
					'title'           => '<span class="orange-text">' . esc_html__( 'Great News!', 'wc-wlfmc-wishlist' ) . '&nbsp;</span>' . esc_html__( 'You\'ve earned a bonus of', 'wc-wlfmc-wishlist' ) . '&nbsp;<span class="orange-text">' . esc_html__( '$74.50', 'wc-wlfmc-wishlist' ) . ' </span>',
					'content'         => esc_html__( 'join our Rewards Club and receive up to $74.50 off the MoreConvert Premium Plugin. With this offer, you\'ll be able to boost your sales and make your store more beautiful in no time.', 'wc-wlfmc-wishlist' ),
					'btn_title'       => '<span class="dashicons dashicons-saved"></span>&nbsp;' . esc_html__( 'Lets Go', 'wc-wlfmc-wishlist' ),
					'btn_class'       => 'btn-notice-2',
					'btn_desc'        => esc_html__( 'Take advantage of this incredible deal right now!', 'wc-wlfmc-wishlist' ),
					'btn_url'         => 'https://moreconvert.com/irxj',
					'btn_target'      => '_blank',
					'timer'           => 48,
					'disable_for_pro' => true,
					'dismiss_url'     => '',
				),
			);
			$notice_after_days = array(
				'3'  => $args[0],
				'7'  => $args[2],
				'10' => $args[1],
				'15' => $args[0],
				'16' => $args[1],
				'30' => $args[0],
				'35' => $args[1],
			);
			foreach ( $notice_after_days as $day => $notice_args ) {
				$day = intval( $day );
				if ( ! wlfmc_is_true( get_option( 'wlfmc-disable-notice-after-' . $day . '-days' ) ) ) {

					if ( strtotime( '+' . $day . ' days', $activate_date ) <= $today ) {
						$dismiss_nonce = wp_create_nonce( 'wlfmc-after-days-notice' );
						update_option( 'wlfmc_wishlist_activation_date', gmdate( 'y-m-d H:i:s', strtotime( '-' . $day . ' days', $today ) ) );
						$notice_args['dismiss_url'] = add_query_arg(
							array(
								'wlfmc-notice-after-' . $day . '-days-dismiss' => '1',
								'_wpnonce' => $dismiss_nonce,
							),
							$this->clean_url()
						);
						// Disable notice for premium users.
						if ( isset( $notice_args['disable_for_pro'] ) && true === $notice_args['disable_for_pro'] && ( defined( 'MC_WLFMC_PREMIUM' ) || get_option( 'wlfmc_premium_version' ) ) ) {
							break;
						}
						// Disable notice if it has a timer and expired.
						if ( isset( $notice_args['timer'] ) ) {
							$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
							if ( ! $expired ) {
								update_option( 'wlfmc-expired-notice-after-' . $day . '-days', time() + ( intval( $notice_args['timer'] ) * 60 * 60 ) );
							} elseif ( $expired < time() ) {
								update_option( 'wlfmc-disable-notice-after-' . $day . '-days', true );
								break;
							}
						}

						$this->output( $notice_args, $day );
					}
					break;
				}
			}
		}

		/**
		 * Display a message of access denial when attempting to make changes if the default language is different from the current language
		 *
		 * @return void
		 */
		public function wpml_notice() {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->parent_base, array( 'mc-wishlist-setup', 'mc-wishlist-dashboard', 'mc-features', 'mc-global-settings', 'mc-multi-list', 'mc-wishlist-settings', 'mc-save-for-later', 'mc-analytics', 'mc-email-automations', 'mc-campaigns' ), true ) ) {
				if ( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_get_languages' ) ) {
					if ( apply_filters( 'wpml_current_language', null ) !== apply_filters( 'wpml_default_language', null ) ) {
						$this->styles();
						?>
						<div id="wlfmc-not-access-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
							<h2 class="error-text"><?php echo wp_kses_post( __( 'Changes are not saved in the MoreConvert plugin!', 'wc-wlfmc-wishlist' ) ); ?></h2>
							<p><?php echo wp_kses_post( __( 'To modify settings, campaigns, and automations, we must revert to the website\'s default language to save the settings without any issues.', 'wc-wlfmc-wishlist' ) ); ?></p>
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Dismiss Notice.
         *
         * @version 1.8.5
		 */
		public function dismiss_notice() {
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			$year  = gmdate( 'Y' );

			if ( isset( $_GET['mct-wlfmc_options-wizard-skip'] ) && '1' === $_GET['mct-wlfmc_options-wizard-skip'] && wp_verify_nonce( $nonce, 'mct-wlfmc_options-wizard-skip-nonce' ) ) {

				update_option( 'wlfmc-skip-wizard-notice', false );

			}

			if ( isset( $_GET['mct-wlfmc_options-wizard-finish'] ) && '1' === $_GET['mct-wlfmc_options-wizard-finish'] && wp_verify_nonce( $nonce, 'mct-wlfmc_options-wizard-finish-nonce' ) ) {

				update_option( 'wlfmc-finish-wizard-notice', false );

			}

			if ( isset( $_GET['wlfmc-skip-wizard-dismiss'] ) && '1' === $_GET['wlfmc-skip-wizard-dismiss'] && wp_verify_nonce( $nonce, 'wlfmc-skip-wizard-dismiss-nonce' ) ) {

				update_option( 'wlfmc-skip-wizard-notice', true );
				delete_option( 'smart-wishlist-for-more-convert_tracking_notice' );

			}

			if ( isset( $_GET['wlfmc-finish-wizard-dismiss'] ) && '1' === $_GET['wlfmc-finish-wizard-dismiss'] && wp_verify_nonce( $nonce, 'wlfmc-finish-wizard-dismiss-nonce' ) ) {

				update_option( 'wlfmc-finish-wizard-notice', true );

			}

			if ( isset( $_GET[ 'wlfmc-black-friday-' . $year . '-dismiss' ] ) && '1' === $_GET[ 'wlfmc-black-friday-' . $year . '-dismiss' ] && wp_verify_nonce( $nonce, 'wlfmc-black-friday-' . $year . '-dismiss-nonce' ) ) {

				update_option( 'wlfmc-black-friday-' . $year . '-notice', true );

			}

			$notice_after_days = array( '3', '7', '10', '15', '16', '30', '35' );

			foreach ( $notice_after_days as $day ) {

				if ( isset( $_GET[ 'wlfmc-notice-after-' . $day . '-days-dismiss' ] ) && '1' === $_GET[ 'wlfmc-notice-after-' . $day . '-days-dismiss' ] && wp_verify_nonce( $nonce, 'wlfmc-after-days-notice' ) ) {

					update_option( 'wlfmc-disable-notice-after-' . $day . '-days', true );

				}
			}
		}

		/**
		 * Update tables
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public function update_tables() {
			if ( ! is_super_admin() ) {
				return;
			}

			$version = get_option( 'wlfmc_need_update_tables' );
			if ( '1.6.3' === $version || '1.7.0' === $version || '1.7.6' === $version ) {
				$message = '';
				if ( '1.6.3' === $version ) {
					$options = new MCT_Options( 'wlfmc_options' );
					$options->update_option( 'wishlist_enable', '0' );
					/* translators: %s version */
					$message = sprintf( esc_html__( 'To ensure proper functionality of the MoreConvert Wishlist plugin version %s and above, it is necessary to update the database tables. Please note that both the premium and free versions of MoreConvert are temporarily unavailable at this time.', 'wc-wlfmc-wishlist' ), esc_attr( $version ) );
				}
				if ( '1.7.0' === $version ) {
					/* translators: %s version */
					$message = sprintf( esc_html__( 'In our previous version, certain bilingual websites experienced interference issues. To ensure smooth operation of the MoreConvert (former MC Wishlist) plugin, version %s and above, it\'s essential to update the database tables.', 'wc-wlfmc-wishlist' ), esc_attr( $version ) );
				}

				if ( '1.7.6' === $version ) {
					if ( ! function_exists( 'icl_st_is_registered_string' ) ) {
						return;
					}
					$message = esc_html__( 'We need to update the WPML String Translation plugin tables. This ensures that the WPML plugin works well with the text in MC WooCommerce Wishlist. Thank you for your cooperation.', 'wc-wlfmc-wishlist' );
				}
				$this->styles();
				?>
				<div id="wlfmc-update-tables-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
					<h2 class="error-text"><?php echo wp_kses_post( __( 'Attention: Important Plugin Update Required!', 'wc-wlfmc-wishlist' ) ); ?></h2>
					<p><?php echo esc_attr( $message ); ?></p>
					<p class="wlfmc-notice-action-wrapper">
						<a href="#" class="wlfmc-update-tables btn-notice-2" data-updated="<?php esc_html_e( 'Updated!', 'wc-wlfmc-wishlist' ); ?>" data-updating="<?php esc_html_e( 'Updating ...', 'wc-wlfmc-wishlist' ); ?>" data-label="<?php esc_html_e( 'Update tables', 'wc-wlfmc-wishlist' ); ?>" data-version="<?php echo esc_attr( $version ); ?>">
							<?php esc_html_e( 'Update tables', 'wc-wlfmc-wishlist' ); ?>
						</a>
					</p>
				</div>
				<script>
					jQuery(
						function ($) {
							var UpdateTables = function ( offset , element ) {
								$.ajax(
									{
										url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
										data: {
											action : 'wlfmc_update_table_database',
											key: '<?php echo esc_attr( wp_create_nonce( 'ajax-nonce' ) ); ?>',
											offset: offset,
											version: element.data('version')
										},
										method: 'post',
										success: function (response) {
											if (response.success) {

												element.text( response.data.percentage + '%' + ' ' + element.data( 'updating' ) );

												if ( 100 <= parseInt( response.data.percentage ) ) {
													element.text( element.data( 'updated' ) );
													if ( response.data.redirect ){
														window.location.href = response.data.redirect;
													} else {
														location.reload();
													}

												} else {
													UpdateTables( parseInt( response.data.offset ), element );
												}
											} else {
												element.text( element.data( 'label' ) );
												element.prop( 'disabled', false );
												window.alert( response.data.message );
											}

										}
									}
								).fail(
									function (response) {
										element.text( element.data( 'label' ) );
										window.console.log( response );
									}
								);
							};
							$( document.body ).on(
								'click',
								'.wlfmc-update-tables',
								function (e) {
									var element = $( this );
									e.preventDefault();
									element.text( element.data( 'updating' ) );
									UpdateTables( 0 , element )
									return false;
								}
							);
						}
					);

				</script>
				<?php
			}
		}

		/**
		 * Add admin notice after skip wizard.
		 */
		public function after_skip_wizard() {

			if ( ! is_super_admin() || wlfmc_is_true( get_option( 'wlfmc-skip-wizard-notice' ) ) ) {
				return;
			}
			$args = array(
				'wrapper_class' => 'notice-skip-wizard',
				'title'         => esc_html__( 'Thanks for installing MC Wishlist!', 'wc-wlfmc-wishlist' ),
				'content'       => esc_html__( 'It is easy to use the MC Wishlist. Please use the setup wizard to quick start setup.', 'wc-wlfmc-wishlist' ),
				'btn_title'     => esc_html__( 'Start Wizard', 'wc-wlfmc-wishlist' ),
				'btn_class'     => 'btn-notice blue-btn',
				'btn_url'       => wp_nonce_url(
					add_query_arg(
						array(
							'wlfmc-skip-wizard-dismiss' => 1,
						),
						get_admin_url() . 'admin.php?page=mc-wishlist-setup'
					),
					'wlfmc-skip-wizard-dismiss-nonce'
				),
				'btn_target'    => '_self',
				'dismiss_url'   => wp_nonce_url(
					add_query_arg(
						array(
							'wlfmc-skip-wizard-dismiss' => 1,
						),
						$this->clean_url()
					),
					'wlfmc-skip-wizard-dismiss-nonce'
				),
			);

			$this->output( $args );

		}

		/**
		 * Add admin notice after finish wizard.
		 */
		public function after_finish_wizard() {
			if ( ! is_super_admin() || wlfmc_is_true( get_option( 'wlfmc-finish-wizard-notice' ) ) ) {
				return;
			}
			$args = array(
				'wrapper_class' => 'notice-finish-wizard',
				'title'         => esc_html__( 'Together we will increase your sales.', 'wc-wlfmc-wishlist' ),
				'content'       => esc_html__( 'Thank you for installing MC Wishlist plugin. Our main goal and challenge is to increase your site sales effortlessly, without the need for more traffic. If you would like to join us in this challenge, check the marketing settings and documentation right now.', 'wc-wlfmc-wishlist' ),
				'btn_title'     => esc_html__( "Let's Go Marketing", 'wc-wlfmc-wishlist' ),
				'btn_class'     => 'btn-notice',
				'btn_url'       => wp_nonce_url(
					add_query_arg(
						array(
							'wlfmc-finish-wizard-dismiss' => 1,
							'page'                        => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					),
					'wlfmc-finish-wizard-dismiss-nonce'
				),
				'btn_target'    => '_self',
				'dismiss_url'   => wp_nonce_url( add_query_arg( 'wlfmc-finish-wizard-dismiss', 1, $this->clean_url() ), 'wlfmc-finish-wizard-dismiss-nonce' ),
			);

			$this->output( $args );

		}

		/**
		 * Print admin notice
		 *
		 * @param array       $args Array of arguments.
		 * @param string|null $day the day it will be implemented.
		 */
		public function output( $args, $day = null ) {
			$this->styles();
			if ( isset( $args['timer'] ) && '' !== $args['timer'] ) {
				$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
				if ( ! $expired ) {
					update_option( 'wlfmc-expired-notice-after-' . $day . '-days', time() + ( intval( $args['timer'] ) * 60 * 60 ) );
				}
				$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
				$timer   = $args['timer'];
				?>
				<script>
					(function() {
						document.addEventListener("DOMContentLoaded", function() {
							var timerDuration = <?php echo esc_attr( $timer ); ?>;
							var timerExpiry = <?php echo esc_attr( $expired ); ?>;

							// Check if the timer expiry is valid and calculate the remaining time
							if (timerExpiry && (timerExpiry + timerDuration > new Date().getTime() / 1000)) {
								var remainingTime = timerExpiry + timerDuration - new Date().getTime() / 1000;

								// Display the remaining time in separate hour, minute, and second tags
								var hours = Math.floor(remainingTime / 3600);
								var minutes = Math.floor((remainingTime % 3600) / 60);
								var seconds = Math.floor(remainingTime % 60);
								document.getElementById("wlfmc-notice-timer-hours").textContent = hours+'h';
								document.getElementById("wlfmc-notice-timer-minutes").textContent = minutes+'m';
								document.getElementById("wlfmc-notice-timer-seconds").textContent = seconds+'s';

								// Update the timer every second
								setInterval(function() {
									remainingTime--;
									if (remainingTime <= 0) {
										// Hide the notice when the timer expires
										document.querySelector("#wlfmc-notice-<?php echo esc_attr( $day ); ?>").style.display = "none";
									} else {
										// Update the hour, minute, and second tags
										var hours = Math.floor(remainingTime / 3600);
										var minutes = Math.floor((remainingTime % 3600) / 60);
										var seconds = Math.floor(remainingTime % 60);
										document.getElementById("wlfmc-notice-timer-hours").textContent = hours+'h';
										document.getElementById("wlfmc-notice-timer-minutes").textContent = minutes+'m';
										document.getElementById("wlfmc-notice-timer-seconds").textContent = seconds+'s';
									}
								}, 1000);
							} else {
								// Hide the notice if the timer expiry is invalid
								document.querySelector("#wlfmc-notice-<?php echo esc_attr( $day ); ?>").style.display = "none";
							}
						});
					})();
				</script>
				<?php
			}
			?>
			<div id="wlfmc-notice-<?php echo $day ? esc_attr( $day ) : esc_attr( wp_unique_id() ); ?>"
				class="notice wlfmc-notice <?php echo isset( $args['wrapper_class'] ) ? esc_attr( $args['wrapper_class'] ) : ''; ?>">
				<?php if ( isset( $args['image'] ) && '' !== $args['image'] ) : ?>
					<div class="wlfmc-notice-with-image">
						<div class="wlfmc-image-wrapper">
							<?php echo wp_kses_post( $args['image'] ); ?>
						</div>
						<div class="wlfmc-content-wrapper">
				<?php endif; ?>
							<h2><?php echo wp_kses_post( $args['title'] ); ?></h2>
							<a href="<?php echo esc_url( $args['dismiss_url'] ); ?>" class="dismiss-btn">
								<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
									<path id="close" d="M13.076,12.244,8.832,8l4.244-4.244a.588.588,0,1,0-.832-.832h0L8,7.168,3.756,2.924a.588.588,0,0,0-.832.832h0L7.168,8,2.924,12.244a.588.588,0,1,0,.832.832h0L8,8.832l4.244,4.244a.588.588,0,0,0,.832-.832Z" transform="translate(-2.752 -2.752)" fill="#616161"/>
								</svg>
							</a>
							<p>
								<?php echo wp_kses_post( $args['content'] ); ?>
							</p>
							<p class="wlfmc-notice-action-wrapper">
								<?php if ( isset( $args['btn_desc'] ) && '' !== $args['btn_desc'] ) : ?>
									<span class="wlfmc-btn-wrapper">
										<a href="<?php echo esc_url( $args['btn_url'] ); ?>" class="<?php echo esc_attr( $args['btn_class'] ); ?>" target="<?php echo esc_attr( $args['btn_target'] ); ?>">
											<?php echo wp_kses_post( $args['btn_title'] ); ?>
										</a>
										<small><?php echo wp_kses_post( $args['btn_desc'] ); ?></small>
									</span>
								<?php else : ?>
									<a href="<?php echo esc_url( $args['btn_url'] ); ?>" class="<?php echo esc_attr( $args['btn_class'] ); ?>" target="<?php echo esc_attr( $args['btn_target'] ); ?>">
										<?php echo wp_kses_post( $args['btn_title'] ); ?>
									</a>
								<?php endif; ?>
								<?php if ( isset( $args['timer'] ) && '' !== $args['timer'] ) : ?>
									<span class="wlfmc-timer">
										<span id="wlfmc-notice-timer-hours"></span>&nbsp;:&nbsp;<span id="wlfmc-notice-timer-minutes"></span>&nbsp;:&nbsp;<span id="wlfmc-notice-timer-seconds"></span>
									</span>
								<?php endif; ?>
							</p>
				<?php if ( isset( $args['image'] ) && '' !== $args['image'] ) : ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Print styles
		 *
		 * @return void
		 *
		 * @version 1.8.5
		 */
		public function styles() {
			if ( ! $this->style_loaded ) :
				?>
				<style>
					.mct-wizard-page .wlfmc-notice.notice-finish-wizard, .mct-wizard-page .wlfmc-notice.notice-skip-wizard {
						display: none !important;
					}

					.wlfmc-notice {
						padding: 20px !important;
						background: #fff;
						border: 1px solid #c3c4c7 !important;
						border-left: 4px solid #e4dbd0 !important;
						-webkit-box-shadow: 0 6px 6px rgba(0, 0, 0, 0.08);
						box-shadow: 0 6px 6px rgba(0, 0, 0, 0.08);
						position: relative;
						font-weight: normal;
					}

					.wlfmc-notice h2 {
						font-weight: 600;
						font-size: 1.3em;
						margin: 0 0 1em 0;
						padding: 0;
						border:none;
						background:transparent;
						background-color:transparent;
					}

					.wlfmc-notice .dismiss-btn {
						font-size: 14px;
						line-height: 21px;
						color: #302a23;
						position: absolute;
						right: 20px;
						top: 20px;
						text-decoration: none;
					}

					.wlfmc-notice p {
					   /* font-size: 14px;
						line-height: 21px;
						color: #302a23;*/
					}
					.wlfmc-notice .btn-rate {
						border-color : #e4dbd0 !important;
						background-color: #fff !important;
						color:#616161 !important;

					}
					.wlfmc-notice .btn-rate:hover {
						background-color: #FBD137 !important;
						border-color: #FBD137 !important;
						color:#fff !important;
					}
					.wlfmc-notice .btn-notice , .wlfmc-notice .btn-notice-2{
						border: none !important;
						color: #fff;
						height: 40px;
						line-height: 40px;
						min-width: 40px;
						-webkit-border-radius: 8px;
						border-radius: 8px;
						position: relative;
						font-size: 14px;
						font-weight: normal;
						-webkit-transition: all .3s ease-in-out;
						-o-transition: all .3s ease-in-out;
						transition: all .3s ease-in-out;
						display: inline-block;
						text-decoration: none;
						padding: 0 10px 0 40px;
						cursor: pointer;
						-webkit-appearance: none;
						white-space: nowrap;
						-webkit-box-sizing: border-box;
						box-sizing: border-box;
						vertical-align: top;
						overflow: hidden;
						background-color: white;
						-webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.16);
						box-shadow: 0 2px 4px rgba(0, 0, 0, 0.16);
						z-index: 1;
					}
					.wlfmc-notice .btn-notice .dashicons {
						line-height: 40px;
					}

					.wlfmc-notice .btn-notice:hover {
						color: #fff;
					}

					.wlfmc-notice .btn-notice:focus {
						-webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.26);
						box-shadow: 0 2px 4px rgba(0, 0, 0, 0.26);
					}

					.wlfmc-notice .btn-notice:before {
						font-family: Dashicons;
						font-weight: 400;
						font-variant: normal;
						text-transform: none;
						-webkit-font-smoothing: antialiased;
						margin: 0;
						text-indent: 0;
						position: absolute;
						top: 0;
						left: 10px;
						text-align: center;
						font-size: 19px;
						line-height: inherit;
						content: "\f147";
						color: #ea7a0b;
					}

					.wlfmc-notice .btn-notice:after {
						position: absolute;
						-webkit-transition: .3s;
						-o-transition: .3s;
						transition: .3s;
						content: '';
						width: 100%;
						bottom: 0;
						height: 100%;
						right: -35px;
						-webkit-transform: skewX(15deg);
						-ms-transform: skewX(15deg);
						transform: skewX(15deg);
						z-index: -1;
						background-color: #ea7a0b;
						-webkit-border-top-left-radius: 2px;
						border-top-left-radius: 2px;
					}
					.wlfmc-notice .btn-notice:hover {
						color: #ea7a0b;
					}

					.wlfmc-notice .btn-notice-2 {
						background-color: #FD5D00;
						min-width:140px;
						display: flex;
						justify-content: center;
						align-items: center;
						padding:0 10px;
					}
					.wlfmc-notice .btn-notice-2:hover{
						color: #fff;
					}

					.wlfmc-notice .btn-notice:hover:after {
						right: -120%;
					}

					.wlfmc-notice .btn-notice.green-btn:before,
					.wlfmc-notice .btn-notice.green-btn:hover {
						color: #91ca45 !important;
					}

					.wlfmc-notice .btn-notice.green-btn:after {
						background-color: #91ca45;
					}

					.wlfmc-notice .btn-notice.purple-btn:before,
					.wlfmc-notice .btn-notice.purple-btn:hover {
						color: #a45eff !important;
					}

					.wlfmc-notice .btn-notice.purple-btn:after {
						background-color: #a45eff;
					}

					.wlfmc-notice .btn-notice.blue-btn:before,
					.wlfmc-notice .btn-notice.blue-btn:hover {
						color: #5e94ff !important;
					}

					.wlfmc-notice .btn-notice.blue-btn:after {
						background-color: #5e94ff;
					}

					.wlfmc-notice .btn-notice.inverse-btn {
						border: 1px solid #ea7a0b !important;
						color: #ea7a0b;
						-webkit-box-shadow: none !important;
						box-shadow: none !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn:before {
						color: #fff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn:after {
						left: -webkit-calc(-100% + 35px);
						left: calc(-100% + 35px);
						right: auto;
						-webkit-transform: skewX(15deg);
						-ms-transform: skewX(15deg);
						transform: skewX(15deg);
					}

					.wlfmc-notice .btn-notice.inverse-btn:hover {
						color: #fff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn:hover:after {
						right: auto;
						left: 0;
						-webkit-transform: skewX(0deg);
						-ms-transform: skewX(0deg);
						transform: skewX(0deg);
					}

					.wlfmc-notice .btn-notice.inverse-btn.green-btn {
						border-color: #91ca45 !important;
						color: #91ca45 !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.green-btn:after {
						background-color: #91ca45 !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.green-btn:hover {
						color: #fff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.purple-btn {
						border-color: #a45eff !important;
						color: #a45eff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.purple-btn:after {
						background-color: #a45eff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.purple-btn:hover {
						color: #fff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.blue-btn {
						border-color: #5e94ff !important;
						color: #5e94ff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.blue-btn:after {
						background-color: #5e94ff !important;
					}

					.wlfmc-notice .btn-notice.inverse-btn.blue-btn:hover {
						color: #fff !important;
					}

					.wlfmc-notice .green-text {
						color: #91ca45
					}

					.wlfmc-notice .orange-text {
						color: #ea7a0b;
					}

					.wlfmc-notice .info-text {
						color: #ea7a0b;
					}
					.wlfmc-notice .success-text {
						color: #34c240;
					}
					.wlfmc-notice .alert-text {
						color: #fa9f47;
					}
					.wlfmc-notice .error-text {
						color: #d64242;
					}
					.wlfmc-notice-with-image {
						display:flex;
						align-items: center;
						gap:20px;
					}
					.wlfmc-notice-action-wrapper {
						display:flex;
						justify-content: space-between;
						flex-direction: row-reverse;
						gap:10px;
						margin-bottom:0;
					}
					.wlfmc-notice-action-wrapper .wlfmc-btn-wrapper {
						display: flex;
						flex-direction: column;
						justify-content: center;
						align-items: center;
					}
					.wlfmc-timer {
						display: flex;
						align-items: center;
						font-weight: 500;
						font-size: 14px;
						line-height: 21px;
						color: #fd5d00;
					}
					.wlfmc-timer span {
						width: 48px;
						height: 48px;
						border-radius: 8px;
						background: rgba(253, 93, 0, 0.15);
						display: inline-flex;
						align-items: center;
						justify-content: center;
					}
					.wlfmc-content-wrapper {
						width:100%;
					}

					.wlfmc-notice.wlfmc-notice-success:not(.hide-icon) ,
					.wlfmc-notice.wlfmc-notice-error:not(.hide-icon) ,
					.wlfmc-notice.wlfmc-notice-info:not(.hide-icon),
					.wlfmc-notice.wlfmc-notice-alert:not(.hide-icon),
					.wlfmc-notice.wlfmc-notice-checked:not(.hide-icon),
					.wlfmc-notice.wlfmc-notice-star:not(.hide-icon)
					{
						padding-left:70px !important;

					}
					.wlfmc-notice.wlfmc-notice-success ,
					.wlfmc-notice.wlfmc-notice-error ,
					.wlfmc-notice.wlfmc-notice-info,
					.wlfmc-notice.wlfmc-notice-alert,
					.wlfmc-notice.wlfmc-notice-checked,
					.wlfmc-notice.wlfmc-notice-star
					{
						box-shadow: 0 5px 5px rgba(110, 55, 0, 0.1) !important;
					}
					.wlfmc-notice.wlfmc-notice-error {
						border-left-color: #d64242 !important;
					}
					.wlfmc-notice.wlfmc-notice-success {
						border-left-color: #34c240 !important;
					}
					.wlfmc-notice.wlfmc-notice-info {
						border-left-color: #0090e0 !important;
					}
					.wlfmc-notice.wlfmc-notice-alert {
						border-left-color: #fa9f47  !important;
					}
					.wlfmc-notice.wlfmc-notice-checked {
						border-left-color: #FD5D00  !important;
					}
					.wlfmc-notice.wlfmc-notice-star {
						border-left-color: #FBD137  !important;
					}
					.wlfmc-notice-success::before,
					.wlfmc-notice-error::before,
					.wlfmc-notice-info::before,
					.wlfmc-notice-alert::before,
					.wlfmc-notice-star::before,
					.wlfmc-notice-checked::before
					{
						content: '';
						position: absolute;
						top: 0;
						left: 0;
						width: 132px;
						height: 100%;
						opacity: 0.15;
					}
					.wlfmc-notice-success::after,
					.wlfmc-notice-error::after,
					.wlfmc-notice-info::after,
					.wlfmc-notice-alert::after,
					.wlfmc-notice-star::after,
					.wlfmc-notice-checked::after
					{
						content: '';
						position: absolute;
						top: 0;
						left: 0;
						width: 70px;
						height: 100%;
						background-position: center;
						background-repeat: no-repeat;
					}

					.wlfmc-notice-error::before {
						background: linear-gradient(90deg,#d64242 0%, #fff 100%);
					}
					.wlfmc-notice-success::before {
						background: linear-gradient(90deg,#34c240 0%, #fff 100%);
					}
					.wlfmc-notice-info::before {
						background: linear-gradient(90deg,#0090e0 0%, #fff 100%);
					}
					.wlfmc-notice-alert::before {
						background: linear-gradient(90deg,#fa9f47 0%, #fff 100%);
					}
					.wlfmc-notice-star::before {
						background: linear-gradient(90deg,#fbd137 0%, #fff 100%);
					}
					.wlfmc-notice-checked::before {
						background:  linear-gradient(90deg,#fd5d00 0%, #fff 100%);
					}
					.wlfmc-notice-error:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='close-circle-svgrepo-com' d='M16,2A14,14,0,1,0,30,16,14.023,14.023,0,0,0,16,2Zm4.7,17.22a1.056,1.056,0,0,1,0,1.484,1.048,1.048,0,0,1-1.484,0L16,17.484,12.78,20.7a1.048,1.048,0,0,1-1.484,0,1.056,1.056,0,0,1,0-1.484L14.516,16,11.3,12.78A1.049,1.049,0,0,1,12.78,11.3L16,14.516l3.22-3.22A1.049,1.049,0,1,1,20.7,12.78L17.484,16Z' fill='%23d64242'/%3E%3C/svg%3E%0A");
					}
					.wlfmc-notice-success:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='tick-circle-svgrepo-com_1_' data-name='tick-circle-svgrepo-com (1)' d='M16,2A14,14,0,1,0,30,16,14.023,14.023,0,0,0,16,2Zm6.692,10.78-7.938,7.938a1.048,1.048,0,0,1-1.484,0L9.308,16.756a1.049,1.049,0,1,1,1.484-1.484l3.22,3.22,7.2-7.2a1.049,1.049,0,0,1,1.484,1.484Z' fill='%2334c240'/%3E%3C/svg%3E%0A");
					}
					.wlfmc-notice-info:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='Path_1407' data-name='Path 1407' d='M14.6,17.4a1.4,1.4,0,1,0,2.8,0v-7a1.4,1.4,0,0,0-2.8,0Zm2.8,4.184a1.4,1.4,0,0,0-2.8,0V21.6a1.4,1.4,0,0,0,2.8,0ZM9.35,2.543A31.526,31.526,0,0,1,16,2a31.525,31.525,0,0,1,6.65.543,8.913,8.913,0,0,1,4.531,2.276A8.914,8.914,0,0,1,29.457,9.35,31.528,31.528,0,0,1,30,16a31.527,31.527,0,0,1-.543,6.65,8.914,8.914,0,0,1-2.276,4.531,8.914,8.914,0,0,1-4.531,2.276A31.527,31.527,0,0,1,16,30a31.528,31.528,0,0,1-6.65-.543,8.914,8.914,0,0,1-4.531-2.276A8.913,8.913,0,0,1,2.543,22.65,31.525,31.525,0,0,1,2,16a31.526,31.526,0,0,1,.543-6.65A8.913,8.913,0,0,1,4.819,4.819,8.913,8.913,0,0,1,9.35,2.543Z' fill='%230090e0' fill-rule='evenodd'/%3E%3C/svg%3E%0A");
					}
					.wlfmc-notice-alert:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='Path_1406' data-name='Path 1406' d='M14.584,17.421a1.417,1.417,0,1,0,2.833,0V13.079a1.417,1.417,0,1,0-2.833,0Zm2.833,4.326a1.417,1.417,0,1,0-2.833,0v.016a1.417,1.417,0,1,0,2.833,0Zm-5.131-16.4a4.206,4.206,0,0,1,7.429,0l9.35,17.2A4.342,4.342,0,0,1,25.35,29H6.65a4.342,4.342,0,0,1-3.715-6.451Z' transform='translate(0 0)' fill='%23fa9f47' fill-rule='evenodd'/%3E%3C/svg%3E%0A");
					}
					.wlfmc-notice-star:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='Subtraction_3' data-name='Subtraction 3' d='M-7758-2769a30.66,30.66,0,0,1-6.649-.544,8.924,8.924,0,0,1-4.531-2.274,8.935,8.935,0,0,1-2.276-4.533A31.36,31.36,0,0,1-7772-2783a31.387,31.387,0,0,1,.542-6.651,8.931,8.931,0,0,1,2.276-4.531,8.913,8.913,0,0,1,4.531-2.274A31.336,31.336,0,0,1-7758-2797a31.37,31.37,0,0,1,6.651.542,8.925,8.925,0,0,1,4.531,2.274,8.931,8.931,0,0,1,2.276,4.531A31.362,31.362,0,0,1-7744-2783a31.337,31.337,0,0,1-.542,6.647,8.935,8.935,0,0,1-2.276,4.533,8.934,8.934,0,0,1-4.531,2.274A30.693,30.693,0,0,1-7758-2769Zm0-8.255a2.411,2.411,0,0,1,.942.32l.009,0,.425.2a7.585,7.585,0,0,0,2.479.9.862.862,0,0,0,.542-.172c.55-.417.459-1.352.278-3.215v-.011l-.047-.484,0-.028a2.636,2.636,0,0,1,0-1.017,2.579,2.579,0,0,1,.587-.845l.31-.365c1.2-1.4,1.8-2.1,1.592-2.778s-1.09-.874-2.845-1.271l-.461-.1a2.461,2.461,0,0,1-.952-.322,2.57,2.57,0,0,1-.581-.834l-.007-.012-.234-.421-.006-.011c-.9-1.617-1.354-2.429-2.032-2.429s-1.133.815-2.039,2.44l-.234.421-.007.012a2.575,2.575,0,0,1-.581.835,2.431,2.431,0,0,1-.949.321l-.459.1c-1.758.4-2.641.6-2.85,1.272s.392,1.377,1.59,2.776l.312.366a2.625,2.625,0,0,1,.589.845,2.752,2.752,0,0,1,0,1.041v0l-.047.484c-.181,1.87-.272,2.808.277,3.226a.862.862,0,0,0,.542.171,7.566,7.566,0,0,0,2.478-.894l.426-.2A2.446,2.446,0,0,1-7758-2777.256Z' transform='translate(7774 2799.001)' fill='%23fbd137'/%3E%3C/svg%3E");
					}
					.wlfmc-notice-checked:not(.hide-icon)::after{
						background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='tick-circle-svgrepo-com_1_' data-name='tick-circle-svgrepo-com (1)' d='M16,2A14,14,0,1,0,30,16,14.023,14.023,0,0,0,16,2Zm6.692,10.78-7.938,7.938a1.048,1.048,0,0,1-1.484,0L9.308,16.756a1.049,1.049,0,1,1,1.484-1.484l3.22,3.22,7.2-7.2a1.049,1.049,0,0,1,1.484,1.484Z' fill='%23fd5d00'/%3E%3C/svg%3E");
					}

					.wlfmc-black-friday .wlfmc-notice-with-image{
						position:relative;
						z-index:1
					}
					.wlfmc-black-friday {
						border:0 !important;
						border-radius:6px !important;
						color:#fff;
						padding:10px 20px !important;
						position:relative;
						background: linear-gradient(90deg, #171717 0.99%, #272727 1.76%, #3a3a3a 2.98%, #424242 3.81%, #3c3c3c 4.37%, #2b2b2b 6.52%, #1f1f1f 9.2%, #181818 12.96%, #171717 23.39%, #171717 47.75%, #171717 51.51%, #171717 69.44%, #171717 72.21%, #191919 82.01%, #222 88.14%, #303030 93.25%, #3d3d3d 96.19%, #303030 97.13%, #212121 98.72%, #1c1c1c 100%);
					}
					.wlfmc-black-friday h2 {
						color:#fff;
						font-size: 24px;
						margin-bottom: 5px;
					}
					.wlfmc-black-friday .yellow-text {
						color:#FECE26
					}
					.wlfmc-line-down, .wlfmc-line-up {
						position: absolute;
						left: 15px;
						bottom: 30px;
						width: calc(100% - 30px);
						height: 13px;
						background: #000;
						z-index: 0;
						border-radius: 8px;
					}
					.wlfmc-line-up {
						top:30px;
					}
					.wlfmc-line-up:after,.wlfmc-line-down:after {
						content: '';
						border-bottom: 3px dashed #3A3A3A;
						width: calc( 100% - 18px);
						height: 1px;
						position: absolute;
						top: 4px;
						left: 8px;
					}
					.wlfmc-black-friday .wlfmc-timer {
						gap:10px;
					}
					.wlfmc-black-friday .wlfmc-timer span {
						background: #fff;
						color:#171717;
					}

					.rtl .wlfmc-notice.wlfmc-notice-success:not(.hide-icon) ,
					.rtl .wlfmc-notice.wlfmc-notice-error:not(.hide-icon) ,
					.rtl .wlfmc-notice.wlfmc-notice-info:not(.hide-icon),
					.rtl .wlfmc-notice.wlfmc-notice-alert:not(.hide-icon),
					.rtl .wlfmc-notice.wlfmc-notice-checked:not(.hide-icon),
					.rtl .wlfmc-notice.wlfmc-notice-star:not(.hide-icon){
						padding-left:20px !important;
						padding-right:70px !important;
					}
					.rtl .wlfmc-notice-success::before,.rtl  .wlfmc-notice-error::before,.rtl .wlfmc-notice-info::before,.rtl .wlfmc-notice-alert::before,.rtl .wlfmc-notice-star::before,.rtl .wlfmc-notice-checked::before,
					.rtl .wlfmc-notice-success::after,.rtl  .wlfmc-notice-error::after,.rtl .wlfmc-notice-info::after,.rtl .wlfmc-notice-alert::after,.rtl .wlfmc-notice-star::after,.rtl .wlfmc-notice-checked::after {
						right:0;
						left:auto;
					}
					.rtl .wlfmc-notice.wlfmc-notice-error {
						border-right-color: #d64242 !important;
					}
					.rtl .wlfmc-notice.wlfmc-notice-success {
						border-right-color: #34c240 !important;
					}
					.rtl .wlfmc-notice.wlfmc-notice-info {
						border-right-color: #0090e0 !important;
					}
					.rtl .wlfmc-notice.wlfmc-notice-alert {
						border-right-color: #fa9f47  !important;
					}
					.rtl .wlfmc-notice.wlfmc-notice-checked {
						border-right-color: #FD5D00  !important;
					}
					.rtl .wlfmc-notice.wlfmc-notice-star {
						border-right-color: #FBD137  !important;
					}
					.rtl .wlfmc-notice{
						border-left: 1px solid #c3c4c7!important;
						border-right-width: 4px!important;
					}
					.rtl .wlfmc-notice-error::before {
						background: linear-gradient(-90deg,#d64242 0%, #fff 100%);
					}
					.rtl .wlfmc-notice-success::before {
						background: linear-gradient(-90deg,#34c240 0%, #fff 100%);
					}
					.rtl .wlfmc-notice-info::before {
						background: linear-gradient(-90deg,#0090e0 0%, #fff 100%);
					}
					.rtl .wlfmc-notice-alert::before {
						background: linear-gradient(-90deg,#fa9f47 0%, #fff 100%);
					}
					.rtl .wlfmc-notice-star::before {
						background: linear-gradient(-90deg,#fbd137 0%, #fff 100%);
					}
					.rtl .wlfmc-notice-checked::before {
						background: linear-gradient(-90deg,#fd5d00 0%, #fff 100%);
					}

					.rtl .wlfmc-notice .dismiss-btn {
						left: 20px;
						right: auto !important;
					}


					.rtl .wlfmc-notice .btn-notice {
						padding-left: 20px !important;
						padding-right: 55px;
					}


					.rtl .wlfmc-notice .btn-notice:before {
						left: auto;
						right: 10px;
					}

					.rtl .wlfmc-notice .btn-notice:after {
						right: auto;
						left: -40px;
						-webkit-transform: skewX(15deg);
						-ms-transform: skewX(15deg);
						transform: skewX(15deg);
						-webkit-border-top-right-radius: 2px;
						border-top-right-radius: 2px;
						-webkit-border-top-left-radius: 0;
						border-top-left-radius: 0;
					}

					.rtl .wlfmc-notice .btn-notice:hover:after {
						right: auto;
						left: -120%;
					}

					.rtl .wlfmc-notice .btn-notice.inverse-btn:after {
						right: -webkit-calc(-100% + 40px);
						right: calc(-100% + 40px);
						left: auto;
						-webkit-transform: skewX(15deg);
						-ms-transform: skewX(15deg);
						transform: skewX(15deg);
					}

					.rtl .wlfmc-notice .btn-notice.inverse-btn:hover:after {
						left: auto;
						right: 0;
						-webkit-transform: skewX(0deg);
						-ms-transform: skewX(0deg);
						transform: skewX(0deg);
					}
					@media (max-width: 768px) {
						.wlfmc-notice-with-image {
							display: flex;
							align-items: center;
							gap: 10px;
							flex-wrap: wrap;
							justify-content: center;
						}
						.wlfmc-timer {
							display: flex;
							align-items: center;
							justify-content: center;
						}
						.wlfmc-notice-action-wrapper {
							flex-wrap: wrap;
							justify-content: center;
							flex-direction: column-reverse;
						}
					}
				</style>
				<?php
				$this->style_loaded = true;
			endif;

		}

		/**
		 * Get clean url from admin notices
		 *
		 * @return false|string
		 * @since 1.2.4
         * @version 1.8.5
		 */
		private function clean_url() {
			$params = array(
				'wlfmc-skip-wizard-dismiss',
				'mct-wlfmc_options-wizard-skip',
				'mct-wlfmc_options-wizard-finish',
				'wlfmc-show-tracking-notice',
				'wlfmc-finish-wizard-dismiss',
				'wlfmc-notice-after-3-days-dismiss',
				'wlfmc-notice-after-7-days-dismiss',
				'wlfmc-notice-after-10-days-dismiss',
				'wlfmc-notice-after-15-days-dismiss',
				'wlfmc-notice-after-16-days-dismiss',
				'wlfmc-notice-after-30-days-dismiss',
				'wlfmc-notice-after-35-days-dismiss',
                'wlfmc-black-friday-2024-dismiss',
                'wlfmc-black-friday-2025-dismiss',
                'wlfmc-black-friday-2026-dismiss'
			);

			return remove_query_arg( $params );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Admin_Notice
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
 * Unique access to instance of WLFMC_Admin_Notice class
 *
 * @return WLFMC_Admin_Notice
 */
function WLFMC_Admin_Notice() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Admin_Notice::get_instance();
}
