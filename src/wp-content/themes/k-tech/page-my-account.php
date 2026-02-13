<?php
/**
 * Template name: WooCommerce - My Account
 *
 * This template adds My account to the sidebar.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

get_header(); ?>

<?php do_action( 'flatsome_before_page' ); ?>

<?php //wc_get_template( 'myaccount/header.php' ); ?>

<div class="page-wrapper my-account mb">
    <div class="container" role="main">
        <?php if ( is_user_logged_in() ) { ?>
            <?php 
                $current_user = wp_get_current_user();
                $current_user_id = get_current_user_id();
                $ref_code = get_user_meta( $current_user_id, 'my_ref_code', true ); 
            ?>

            <h1 class="text-center title_account_page">Trang của tôi</h1>
            <div class="block_info_account_page">
                <div class="block_info_list">
                    <div class="block_info_item">
                        <div class="bloc_info_item__label">
                            <strong>Tên</strong>
                        </div>
                        <div class="bloc_info_item__value">
                            <?php 
                                $user_fullname = get_user_meta( $current_user_id, 'user_fullname', true );
                                echo $user_fullname ? $user_fullname : $current_user->display_name;
                            ?>
                        </div>
                    </div>
                    <div class="block_info_item">
                        <div class="bloc_info_item__label">
                            <strong>Loại tài khoản</strong>
                        </div>
                        <div class="bloc_info_item__value">
                            <?php echo $current_user->roles[0]; ?>
                        </div>
                    </div>
                    <div class="block_info_item">
                        <div class="bloc_info_item__label">
                            <strong>ID</strong>
                        </div>
                        <div class="bloc_info_item__value">
                            <?php echo $current_user->user_login; ?>
                        </div>
                    </div>
                    <div class="block_info_item">
                        <div class="bloc_info_item__label">
                            <strong>Mã giới thiệu</strong>
                        </div>
                        <div class="bloc_info_item__value">
                            <?php echo $ref_code ? $ref_code : 'Không có'; ?>
                        </div>
                    </div>
                </div>


            </div>

            <div class="vertical-tabs row">
                <div class="col large-3 medium-3 small-12 ">
                    <div class="my_account_sidebar my_account_sidebar--pc">

                        <?php wc_get_template( 'myaccount/account-user.php' ); ?>

                        <?php do_action( 'woocommerce_before_account_navigation' ); ?>

                        <ul id="my-account-nav" class="account-nav nav nav-line nav-uppercase nav-vertical mt-half">
                            <?php wc_get_template( 'myaccount/account-links.php' ); ?>
                        </ul>

                        <?php do_action( 'woocommerce_after_account_navigation' ); ?>
                    </div>

                </div>

                <div class="col large-9 medium-9 small-12 col_content_account">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; // end of the loop. ?>
                </div>
            </div>

        <?php } else { ?>

            <?php while ( have_posts() ) : the_post(); ?>

                <?php the_content(); ?>

            <?php endwhile; // end of the loop. ?>

        <?php } ?>

    </div>
</div>

<?php do_action( 'flatsome_after_page' ); ?>


<?php get_footer(); ?>
