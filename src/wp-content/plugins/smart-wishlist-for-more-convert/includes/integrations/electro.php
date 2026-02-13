<?php
/**
 * WLFMC wishlist integration with Electro theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_electro_integrate' );
add_filter( 'wlfmc_single_position_options', 'wlfmc_electro_position_options', 0 );
add_filter( 'wlfmc_loop_position_options', 'wlfmc_electro_position_options', 0 );
add_filter( 'wlfmc_admin_options', 'wlfmc_electro_settings', 10 );
add_filter( 'wlfmc_outofstock_loop_positions', 'wlfmc_electro_outofstock_loop_positions', 999 );

/**
 * Integration with electro theme
 *
 * @return void
 */
function wlfmc_electro_integrate() {
	add_action( 'electro_loop_action_buttons', 'wlfmc_electro_action_buttons', 10 );
	add_filter( 'wlfmc_loop_positions', 'wlfmc_electro_fix_loop_position' );
	add_filter( 'wlfmc_button_positions', 'wlfmc_electro_fix_single_position' );
	add_filter( 'wlfmc_admin_steps', 'wlfmc_electro_wizard_steps' );
	add_filter( 'electro_handheld_footer_bar_links', 'wlfmc_electro_handheld_footer_bar_links' );
	add_action( 'electro_header_icons', 'wlfmc_electro_header_icons', 80 );
}


/**
 * Add electro default position
 *
 * @return void
 */
function wlfmc_electro_action_buttons() {
	$position = wlfmc_is_single() ? 'single' : 'loop';
	do_action( 'wlfmc_electro_' . $position . '_action_buttons' );
}

/**
 * Fix outofstock loop position
 *
 * @param array $positions positions.
 * @return array
 */
function wlfmc_electro_outofstock_loop_positions( $positions ) {
	unset( $positions['instead_read_more'] );
	$positions['after_read_more'] = array(
		'hook'     => 'woocommerce_after_shop_loop_item',
		'priority' => 15,
	);
	return $positions;
}
/**
 * Add flex positions
 *
 * @param array $options position options.
 * @return array
 */
function wlfmc_electro_position_options( $options ) {
	return array_merge(
		array(
			'electro' => __( 'Default Elector Theme', 'wc-wlfmc-wishlist' ),
		),
		$options
	);
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_electro_fix_loop_position( array $positions ): array {

	$positions['electro'] = array(
		'hook'     => 'wlfmc_electro_loop_action_buttons',
		'priority' => 10,
	);

	return $positions;
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_electro_fix_single_position( array $positions ): array {

	$positions['electro'] = array(
		array(
			'hook'     => 'electro_single_product_action_buttons',
			'priority' => '10',
		),
		array(
			'hook'     => 'wlfmc_electro_single_action_buttons',
			'priority' => '10',
		),
		array(
			'hook'     => 'woocommerce_single_product_summary',
			'priority' => '30',
		),
	);
	return $positions;
}

/**
 * Fix position with electro
 *
 * @param array $settings wishlist single position settings.
 *
 * @return array
 */
function wlfmc_electro_settings( array $settings ): array {

	if ( isset( $settings['button-display'] ) ) {
		$settings['button-display']['fields']['button']['wishlist_button_position']['default'] = 'electro';
		$settings['button-display']['fields']['button']['loop_position']['default']            = 'electro';
		$settings['button-display']['fields']['button']['button_type_single']['default']       = 'both';
		$settings['button-display']['fields']['button']['button_type_loop']['default']         = 'both';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_font_size_single']['default']       = '.929em';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_color_single']['default']           = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_hover_color_single']['default']     = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_font_size_single']['default']       = '.929em';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_color_single']['default']           = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_hover_color_single']['default']     = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_icon_loop']['fields']['icon_font_size_loop']['default']           = '.929em';
		$settings['button-display']['fields']['button']['button_icon_loop']['fields']['icon_color_loop']['default']               = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_icon_loop']['fields']['icon_hover_color_loop']['default']         = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_text_loop']['fields']['text_font_size_loop']['default']           = '.929em';
		$settings['button-display']['fields']['button']['button_text_loop']['fields']['text_color_loop']['default']               = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_text_loop']['fields']['text_hover_color_loop']['default']         = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_sizes_single']['fields']['button_height_single']['default']       = 'auto';
		$settings['button-display']['fields']['button']['button_sizes_loop']['fields']['button_height_loop']['default']           = 'auto';
		$settings['button-display']['fields']['counter']['counter_products_number_position']['default']                           = 'top-right';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_color']['default']                   = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_icon_font_size']['default']          = '20px';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_number_background_color']['default'] = 'rgb(131,183,53)';
	}

	return $settings;
}

/**
 * Fix position with electro in the wizard
 *
 * @param array $settings wizard settings.
 *
 * @return array
 */
function wlfmc_electro_wizard_steps( array $settings ): array {
	if ( isset( $settings['step-2']['fields'] ) ) {
		$settings['step-2']['fields']['wishlist_button_position']['options'] = array_merge(
			array(
				'electro' => __( 'Default Elector Theme', 'wc-wlfmc-wishlist' ),
			),
			$settings['step-2']['fields']['wishlist_button_position']['options']
		);
		$settings['step-2']['fields']['loop_position']['options']            = array_merge(
			array(
				'electro' => __( 'Default Elector Theme', 'wc-wlfmc-wishlist' ),
			),
			$settings['step-2']['fields']['loop_position']['options']
		);
		$settings['step-2']['fields']['wishlist_button_position']['default'] = 'electro';
		$settings['step-2']['fields']['loop_position']['default']            = 'electro';
	}
	return $settings;
}

/**
 * Add wishlist counter to footer bar v1.
 *
 * @param array $links footer links.
 * @return array
 */
function wlfmc_electro_handheld_footer_bar_links( $links ) {
	$links['mc-wishlist'] = array(
		'priority' => 50,
		'callback' => 'wlfmc_electro_handheld_footer_bar_wishlist_link',
	);
	return $links;
}
if ( ! function_exists( 'wlfmc_electro_handheld_footer_bar_wishlist_link' ) ) {
	/**
	 * Show wishlist counter in the footer
	 *
	 * @return void
	 */
	function wlfmc_electro_handheld_footer_bar_wishlist_link() {
		echo do_shortcode( '[wlfmc_wishlist_counter dropdown_products="false" ]' );
	}
}

/**
 * Add wishlist counter to header
 *
 * @return void
 */
function wlfmc_electro_header_icons() {
	?>
	<div class="header-icon">
		<?php echo do_shortcode( '[wlfmc_wishlist_counter]' ); ?>
	</div>
	<?php
}

