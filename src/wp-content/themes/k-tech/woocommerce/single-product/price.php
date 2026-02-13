<?php
/**
 * Single Product Price, including microdata for SEO
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see              https://docs.woocommerce.com/document/template-structure/
 * @author           WooThemes
 * @package          WooCommerce/Templates
 * @version          3.0.0
 * @flatsome-version 3.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
// Lấy giá gốc từ post meta, bỏ qua filter 'woocommerce_product_get_price'
$price = (float) get_post_meta( $product->get_id(), '_price', true );
$classes = array();

$current_user = wp_get_current_user();
$price_origin = $price == 0 ? 'No Sale' : wc_price($price);
$roles = $current_user->roles;
$role  = array_shift($roles);
$price_master = 0;
$price_pharmer = 0;

$roles_setting = get_option('ktech_account_roles_setting', []);

if ($role != 'master' && $role != 'pharmer_seller' && $role != 'pharmer') {
	if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['price_type_avaliable']) {
		$price_type_avaliable = $roles_setting[$role]['price_type_avaliable'];
		if ($price_type_avaliable == 'pharmer') {
			$role = 'pharmer';
		}
		if ($price_type_avaliable == 'master') {
			$role = 'master';
		}
	}
}

if ($role === 'master') {
	$price_master = get_post_meta( $product->get_id(), '_master_price', true );
}

if ($role === 'pharmer' || $role === 'pharmer_seller') {
	$price_pharmer = get_post_meta( $product->get_id(), '_pharmer_price', true );
}

if($product->is_on_sale()) $classes[] = 'price-on-sale';
if(!$product->is_in_stock()) $classes[] = 'price-not-in-stock'; 
?>

<?php
	if ( $product->get_sale_price() ) {
		?>
			<div class="price-wrapper">
				<p>Giá niêm yết</p>
				<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
				<?php 
					echo wc_price($product->get_regular_price());
				?>
				</p>
			</div>
			<div class="price-wrapper">
				<p>Giá khuyến mãi</p>
				<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
				<?php 
					echo wc_price($product->get_sale_price());
				?>
				</p>
			</div>
		<?php
	} else {
		?>
			<div class="price-wrapper">
				<p><?php echo ($role === 'master' || $role === 'pharmer' || $role === 'pharmer_seller') ? 'Giá niêm yết' : 'Giá bán lẻ'; ?></p>
				<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
				<?php 
					echo $price_origin;
				?>
				</p>
			</div>
		<?php
	}
?>

<?php
	if ($roles_setting && $roles_setting[$role] && $roles_setting[$role]['discount']) {
		$discount_for_role = floatval($roles_setting[$role]['discount']);
		if ($discount_for_role > 0 && $discount_for_role < 100) {
			if (!empty($price_master)) {
				$price_master = $price_master * (1 - $discount_for_role / 100);
			}
			if (!empty($price_pharmer)) {
				$price_pharmer = $price_pharmer * (1 - $discount_for_role / 100);
			}
		}
	}
?>

<?php if (!empty($price_master)) { ?>
	<div class="price-wrapper">
		<p>Giá dành cho Master</p>
		<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
		<?php echo wc_price($price_master); ?></p>
	</div>
<?php } ?>

<?php if (!empty($price_pharmer)) { ?>
	<div class="price-wrapper">
		<p>Giá dành cho VIP Member</p>
		<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
		<?php echo wc_price($price_pharmer); ?></p>
	</div>
<?php } ?>

<?php
$end_of_sale = get_post_meta( $product->get_id(), '_end_of_sale', true );
$end_of_sale_date = get_post_meta( $product->get_id(), '_end_of_sale_date', true );

$now = current_time('Y-m-d\TH:i');
$is_expired = false;
if ( $end_of_sale_date ) {
	$is_expired = ( strtotime($end_of_sale_date) < strtotime($now) );
}

if ( $end_of_sale === 'yes' && $end_of_sale_date ) {
	if (!$is_expired) {
		?>
			<div class="price-wrapper">
				<p>Kết thúc bán hàng</p>
				<p class="price product-page-price <?php echo implode(' ', $classes); ?>">
					<span id="countdown-timer" data-end-time="<?php echo esc_attr($end_of_sale_date); ?>">
						<?php
							$now_time = current_time('timestamp');
							$end_time = strtotime($end_of_sale_date);
							$diff = $end_time - $now_time;

							if ($diff > 0) {
								$days    = floor($diff / (60 * 60 * 24));
								$hours   = floor(($diff % (60 * 60 * 24)) / (60 * 60));
								$minutes = floor(($diff % (60 * 60)) / 60);
								$seconds = $diff % 60;
								if ($days) {
									echo "<b>{$days}</b> ngày ";
								}
								if ($hours) {
									echo "<b>{$hours}</b> giờ ";
								}
								if ($minutes) {
									echo "<b>{$minutes}</b> phút ";
								}
								if ($seconds) {
									echo "<b>{$seconds}</b> giây";
								}
							} else {
								echo "Đã hết thời gian mở bán";
							}
						?>
					</span>
				</p>
			</div>
		<?php
	} else {
	?>
		<div class="price-wrapper">
			<p>Đã hết thời gian mở bán</p>
		</div>
	<?php
	}
}
