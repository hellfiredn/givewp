<?php
add_action('wp_enqueue_scripts', 'k_tech_style');
function k_tech_style() {
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}


include_once 'inc/template.php';
include_once 'admin/admin-template.php';




add_filter( 'woocommerce_product_tabs', 'custom_product_tabs' );
add_shortcode('custom_product_tabs','custom_product_tabs');
function custom_product_tabs( $tabs ) {

    $tabs['dung_tich'] = array(
        'title'    => 'Dung tích',
        'priority' => 40,
        'callback' => function() {
            echo '<p>200ml</p>';
        }
    );

    $tabs['loai_da'] = array(
        'title'    => 'Loại da',
        'priority' => 41,
        'callback' => function() {
            echo '<p>Dành cho mọi loại da</p>';
        }
    );

    $tabs['cach_su_dung'] = array(
        'title'    => 'Cách sử dụng',
        'priority' => 42,
        'callback' => function() {
            echo '<p>Dùng tay thoa một lượng sản phẩm lên da, vỗ nhẹ để sản phẩm thấm thấu</p>';
        }
    );

    $tabs['thanh_phan_chinh'] = array(
        'title'    => 'Thành phần chính',
        'priority' => 43,
        'callback' => function() {
            echo '<p>Terpineol, chiết xuất rau má, rau sam, rễ cam thảo…</p>';
        }
    );

    $tabs['thanh_phan_chi_tiet'] = array(
        'title'    => 'Thành phần chi tiết',
        'priority' => 44,
        'callback' => function() {
            echo '<p>Betaine, Sodium Hyaluronate, Hyaluronic Acid, Hydrolyzed Hyaluronic Acid, Hydrolyzed Sodium Hyaluronate, Hydroxypropyltrimonium Hyaluronate, Potassium Hyaluronate, Hyaluronate Crosspolymer, Sodium Acetylated Hyaluronate, Histidine, Proline, Phenylalanine, Tryptophan, Threonine, Tyrosine, Alanine, Isoleucine, Aspartic Acid, Cysteine, Serine, Valine, Methionine, Leucine, Lysine, Glutamic Acid, Glycine, Panthenol (Vitamin B5), Allantoin, Portulaca Oleracea Extract, Centella Asiatica Extract (Chiết xuất rau má), Dipotassium Glycyrrhizate (Chiết xuất cam thảo), Glycyrrhiza Uralensis (Licorice) Root Extract (Chiết xuất rễ cam thảo)</p>';
        }
    );

    return $tabs;
}




function share_social() {

    ob_start();
    $social = get_field('social', 'option');
    ?>

    <ul class="share_social default_ul">
        <?php foreach ($social as $key => $item) {
            ?>
            <li><a href="<?php echo $item['link'] ?>">
                    <?php echo wp_get_attachment_image($item['icon']['ID'], 'full', "", array("class" => "")); ?>


                </a></li>
            <?php
        } ?>
    </ul>
    <?php

    return ob_get_clean();

}


add_shortcode('share_social', 'share_social');