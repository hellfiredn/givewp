<?php
/**
 * Smart Wishlist Integration with cache plugins
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.7.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Integration_Cache' ) ) {
	/**
	 * Smart Wishlist Integration with cache plugins
	 */
	class WLFMC_Integration_Cache {
		/**
		 * Performs all required hooks to handle forms
		 *
		 * @return void
		 * @since 1.4.0
		 * @version 1.7.6
		 */
		public static function init() {

			if ( defined( 'LSWCP_PLUGIN_URL' ) ) {
				// Force ESI nonce.
				add_action( 'wp_enqueue_scripts', array( 'WLFMC_Integration_Cache', 'litespeed_conf_esi_nonce' ), 9 );
				// Force exclude URL.
				add_action( 'init', array( 'WLFMC_Integration_Cache', 'litespeed_conf_exc_uri' ) );
			}
			// Force exclude URL.
			add_action( 'admin_init', array( 'WLFMC_Integration_Cache', 'wp_fastest_cache_reject' ) );
		}

		/**
		 * Force ESI nonce
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public static function litespeed_conf_esi_nonce() {
			do_action( 'litespeed_nonce', 'wp_rest' );
			do_action( 'litespeed_nonce', 'wlfmc_*' );
		}

		/**
		 * Force Exclude wishlist URl
		 *
		 * @return void
		 */
		public static function litespeed_conf_exc_uri() {
			$val = apply_filters( 'litespeed_conf', 'cache-exc' );
			$ids = array( get_option( 'wlfmc_wishlist_page_id' ), get_option( 'wlfmc_tabbed_page_id' ), get_option( 'wlfmc_waitlist_page_id' ), get_option( 'wlfmc_multi_list_page_id' ) );
			$ids = array_map( 'absint', $ids );
			$ids = array_filter(
				$ids,
				function( $a ) {
					return ( 0 !== $a );
				}
			);
			if ( empty( $ids ) ) {
				return;
			}
			$pages     = $ids;
			$languages = apply_filters(
				'wpml_active_languages',
				array(),
				array(
					'skip_missing' => 0,
					'orderby'      => 'code',
				)
			);
			if ( ! empty( $languages ) ) {
				foreach ( $ids as $id ) {
					foreach ( $languages as $l ) {
						$pages[] = wlfmc_object_id( $id, 'page', true, $l['language_code'] );
					}
				}
				$pages = array_unique( $pages );
			}
			$pages = array_filter( $pages );

			if ( ! empty( $pages ) ) {
				foreach ( $pages as $i => $page ) {
					$pages[$i] = preg_replace("/^\//", '', rtrim(str_replace(get_site_url(), '', get_permalink(absint($page))), '/') ?? '' ); // @codingStandardsIgnoreLine Squiz.Strings.DoubleQuoteUsage.NotRequired
				}
			}
			$pages = array_unique( $pages );
			$pages = array_filter( $pages );

			$val = array_unique( array_merge( $val, $pages ) );
			do_action( 'litespeed_conf_force', 'cache-exc', $val );
		}

		/**
		 * Force Exclude wishlist URl
		 *
		 * @version 1.7.6
		 * @return void
		 */
		public static function wp_fastest_cache_reject() {
			if ( defined( 'WPFC_WP_PLUGIN_DIR' ) ) {
				$rules_json = get_option( 'WpFastestCacheExclude' );
				$ids        = array( get_option( 'wlfmc_wishlist_page_id' ), get_option( 'wlfmc_tabbed_page_id' ), get_option( 'wlfmc_waitlist_page_id' ), get_option( 'wlfmc_multi_list_page_id' ) );
				$ids        = array_map( 'absint', $ids );
				$ids        = array_filter(
					$ids,
					function( $a ) {
						return ( 0 !== $a );
					}
				);
				if ( empty( $ids ) ) {
					return;
				}
				$pages     = $ids;
				$languages = apply_filters(
					'wpml_active_languages',
					array(),
					array(
						'skip_missing' => 0,
						'orderby'      => 'code',
					)
				);
				if ( ! empty( $languages ) ) {
					foreach ( $ids as $id ) {
						foreach ( $languages as $l ) {
							$pages[] = wlfmc_object_id( $id, 'page', true, $l['language_code'] );
						}
					}
					$pages = array_unique( $pages );
				}
				$pages = array_filter( $pages );

				if ( ! empty( $pages ) ) {
					foreach ( $pages as $i => $page ) {
						$pages[$i] = preg_replace("/^\//", '', rtrim(str_replace(get_site_url(), '', get_permalink(absint($page))), '/') ?? '' ); // @codingStandardsIgnoreLine Squiz.Strings.DoubleQuoteUsage.NotRequired
					}
				}
				$pages = array_unique( $pages );
				$pages = array_filter( $pages );

				$rules_std = json_decode( $rules_json, true );
				$ex_pages  = array();
				$rules_std = is_array( $rules_std ) ? $rules_std : array();
				foreach ( $rules_std as $value ) {
					$value['type'] = $value['type'] ?? 'page';
					if ( 'page' === $value['type'] ) {
						$ex_pages[] = $value['content'];
					}
				}
				$ex_pages = array_unique( $ex_pages );
				$ex_pages = array_filter( $ex_pages );
				$changed  = false;

				foreach ( $pages as $page ) {
					$page = preg_replace( '/\/$/', '', $page );

					if ( ! in_array( $page, $ex_pages, true ) ) {
						$changed     = true;
						$rules_std[] = array(
							'prefix'  => 'startwith',
							'content' => $page,
							'type'    => 'page',
						);
					}
				}
				if ( $changed ) {
					$data = wp_json_encode( $rules_std );
					update_option( 'WpFastestCacheExclude', $data );
				}
			}
		}
	}
}

WLFMC_Integration_Cache::init();
