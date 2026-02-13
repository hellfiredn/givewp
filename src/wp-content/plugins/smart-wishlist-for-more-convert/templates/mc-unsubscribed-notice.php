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
 * @var $atts                       array all template variables
 * @var $unsubscribed               bool
 * @var $unsubscribed_content       string
 * @var $unsubscribed_button_title  string
 * @var $customer_id                int
 */
$nonce = wp_create_nonce( 'wlfmc_change_gdpr_status' );
if ( $unsubscribed ) : ?>
	<tr>
		<td colspan="3">
			<div class="wlfmc-unsubscribe-notice-wrapper">
				<?php if ( isset( $unsubscribed_content ) && '' !== $unsubscribed_content ) : ?>
					<div class="wlfmc-notice-content">
						<?php echo do_shortcode( $unsubscribed_content ); ?>
					</div>
				<?php endif; ?>
				<div class="wlfmc-notice-buttons">
					<a href="#" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-cid="<?php echo esc_attr( $customer_id ); ?>" data-action="subscribe" class="wlfmc-gdpr-btn button alt wlfmc-btn wlfmc-subscribe-btn" >
						<?php echo esc_attr( $unsubscribed_button_title ); ?>
					</a>
				</div>
			</div>
		</td>
	</tr>
<?php endif; ?>
