<?php
/**
 * Subscribe Setting template
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.8.8
 */

/**
 * Template variables:
 *
 * @var $atts                      array all template variables
 * @var $gdpr_enable               bool
 * @var $gdpr_content              string
 * @var $gdpr_accept_button_title  string
 * @var $gdpr_denied_button_title  string
 * @var $customer_id               int
 */
$nonce = wp_create_nonce( 'wlfmc_change_gdpr_status' );
if ( $gdpr_enable ) : ?>
	<div class="wlfmc-gdpr-notice-wrapper">
		<?php if ( isset( $gdpr_content ) && '' !== $gdpr_content ) : ?>
			<div class="wlfmc-notice-content">
				<?php echo do_shortcode( $gdpr_content ); ?>
			</div>
		<?php endif; ?>
		<div class="wlfmc-notice-buttons">
			<a href="#" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-action="subscribe" data-cid="<?php echo esc_attr( $customer_id ); ?>" class="wlfmc-gdpr-btn button alt wlfmc-btn wlfmc-accept-btn"><span><?php echo esc_attr( $gdpr_accept_button_title ); ?></span></a>
			<a href="#" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-action="unsubscribe" data-cid="<?php echo esc_attr( $customer_id ); ?>" class="wlfmc-gdpr-btn button wlfmc-btn wlfmc-denied-btn"><span><?php echo esc_attr( $gdpr_denied_button_title ); ?></span></a>
		</div>
	</div>
<?php endif; ?>
