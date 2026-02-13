<?php
    $order_id = $_GET['order_code'];
?>

<?php if ($order_id) { ?>

    <?php
        $current_user_id = get_current_user_id();
        $orders = wc_get_orders( array(
            'customer_id' => $current_user_id,
            'include'     => array( $order_id ),
            'limit'       => -1,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ) );
    ?>

    <div class="orders_account">
        <div class="orders__header">
            <div class="orders__cell orders__cell--code">Mã đơn hàng</div>
            <div class="orders__cell orders__cell--total">Tổng tiền</div>
            <div class="orders__cell orders__cell--date">Ngày đặt</div>
            <div class="orders__cell orders__cell--status">Trạng thái</div>
            <div class="orders__cell orders__cell--actions">Xem chi tiết</div>
        </div>

        <?php
            if ( $orders ) {
                foreach ( $orders as $order ) {
                    if ($order->get_id() != $order_id) continue;
                    $cancel_url = '';
                    if ( $order->has_status( array( 'pending', 'on-hold', 'processing' ) ) ) {
                        $cancel_url = wp_nonce_url(
                            add_query_arg(
                                array(
                                    'cancel_order' => 'true',
                                    'order_id'     => $order->get_id(),
                                ),
                                wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) )
                            ),
                            'woocommerce-cancel_order'
                        );
                    }
                    ?>
                        <div class="orders__row">
                            <div class="orders__cell orders__cell--code"><?php echo $order->get_id(); ?></div>
                            <div class="orders__cell orders__cell--date"><?php echo wc_price( $order->get_total() ); ?></div>
                            <div class="orders__cell orders__cell--total"><?php echo $order->get_date_created()->date('d-m-Y'); ?></div>
                            <div class="orders__cell orders__cell--status orders__status--pending"><?php echo wc_get_order_status_name( $order->get_status() ); ?></div>
                            <div class="orders__cell orders__cell--status">
                                <a href="#" class="orders__action orders__action--view order-view-popup-btn" data-order-id="<?php echo $order->get_id(); ?>">Xem</a>
                            </div>
                            <div class="orders__cell orders__cell--actions">
                                <?php if (!empty($cancel_url)) { ?>
                                    <a href="<?php echo $cancel_url; ?>" class="orders__action orders__action--cancel">Hủy đơn</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php
                }
            } else {
                echo __('Bạn chưa có đơn hàng nào.', 'woocommerce');
            }
        ?>
    </div>

<?php } else { ?>
    <form method="get" id="order-lookup-form">
        <label for="order_code">Mã đơn hàng:</label>
        <input type="text" id="order_code" name="order_code" placeholder="Nhập mã đơn hàng" required>
        <button type="submit">Xem đơn hàng</button>
    </form>
<?php } ?>