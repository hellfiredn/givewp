<?php

/*

Template Name: Training

*/
global $post;
get_header();
?>
<section class="training_page">
    <div class="container">
        <h2 class="text-center">Đào tạo</h2>
        <div class="calendar-container" id="my-calendar">
            <div class="calendar-header">
                <button class="calendar-prev">

                </button>
                <h2 class="calendar-title"></h2>
                <button class="calendar-next">

                </button>
            </div>
            <table class="calendar">
                <thead>
                <tr>
                    <th>Chủ Nhật</th>
                    <th>Thứ Hai</th>
                    <th>Thứ Ba</th>
                    <th>Thứ Tư</th>
                    <th>Thứ Năm</th>
                    <th>Thứ Sáu</th>
                    <th>Thứ Bảy</th>
                </tr>
                </thead>
                <tbody class="calendar-body">
                <!-- Ngày sẽ render tại đây -->
                </tbody>
            </table>
        </div>
        <div class="calendar-container" id="my-calendar-mobile">
            <div class="calendar-header">
                <button class="calendar-prev">

                </button>
                <h2 class="calendar-title"></h2>
                <button class="calendar-next">

                </button>
            </div>
            <div class="calendar-body">

            </div>
        </div>
        <?php
            $current_user_id = get_current_user_id();
            $registered_training = get_user_meta($current_user_id, 'registered_training', true);

            if (empty($registered_training)) {
                $registered_training = array();
            }

            $args = array(
                'post_type'      => 'training_post',
                'post_status'    => 'public',
                'posts_per_page' => -1
            );
            $training_posts = get_posts($args);
            $data_training = array();
            foreach($training_posts as $item) {
                $date_train = get_post_meta($item->ID, 'date_train', true);
                $location_train = get_post_meta($item->ID, 'location_train', true);
                $time_train = get_post_meta($item->ID, 'time_train', true);
                $number_member = get_post_meta($item->ID, 'number_member', true);
                $price_train = get_post_meta($item->ID, 'price_train', true);
                $title = get_the_title($item->ID);
                $content = $item->post_content;

                if ($date_train) {
                    $date_train = date('Y-m-d', strtotime($date_train));
                }

                if ($number_member) {
                    $status = 'limited';
                    $status_label = 'Giới hạn';
                }

                if (in_array($item->ID, $registered_training)) {
                    $status = 'registered';
                    $status_label = 'Đã đăng ký';
                }

                $data_training[$date_train] = array(
                    'id' => $item->ID,
                    'date_train' => $date_train,
                    'location_train' => $location_train,
                    'time_train' => $time_train,
                    'number_member' => $number_member,
                    'price_train' => $price_train,
                    'status' => $status,
                    'status_label' => $status_label,
                    'content' => $title,
                    'desc' => $content,
                    'is_logged_in' => is_user_logged_in(),
                );
            }
        ?>
        <script>
            var dataByDate = <?php echo json_encode($data_training, JSON_UNESCAPED_UNICODE); ?>;
        </script>
    </div>
</section>

<?php
get_footer();
