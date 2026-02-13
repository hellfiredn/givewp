<?php
/**
 * The Template for displaying privacy content policy.
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.5
 */

/**
 * Template Variables:
 *
 * @var $sections array Array of sections
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wp-suggested-text">
	<?php do_action( 'wlfmc_privacy_guide_content_before' ); ?>

	<?php
	foreach ( $sections as $key => $section ) {
		$privacy_action = "wlfmc_privacy_guide_content_$key";
		$content        = apply_filters( 'wlfmc_privacy_guide_content', '', $key );

		if ( has_action( $privacy_action ) || ! empty( $section['tutorial'] ) || ! empty( $section['description'] ) || $content ) {
			if ( ! empty( $section['title'] ) ) {
				echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
			}

			if ( ! empty( $section['tutorial'] ) ) {
				echo '<p class="privacy-policy-tutorial">' . wp_kses_post( $section['tutorial'] ) . '</p>';
			}

			if ( ! empty( $section['description'] ) ) {
				echo '<p >' . wp_kses_post( $section['description'] ) . '</p>';
			}

			if ( ! empty( $content ) ) {
				echo wp_kses_post( $content );
			}
		}

		do_action( $privacy_action );
	}
	?>

	<?php do_action( 'wlfmc_privacy_guide_content_after' ); ?>
</div>
