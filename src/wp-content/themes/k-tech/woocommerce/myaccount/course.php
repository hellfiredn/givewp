<?php
  $user_id = get_current_user_id();
  $registered_training = get_user_meta($user_id, 'registered_training', true) ?: [];
?>

<div class="course">
  <div class="course__header">
    <h1 class="course__header-title">KHÓA HỌC CỦA TÔI</h1>
  </div>
  
  <?php if (!empty($registered_training) && is_array($registered_training)): ?>
    <div class="course__table-wrapper">
      <table class="course__table">
        <thead>
          <tr>
            <th>Tên khóa học</th>
            <th>Ngày đào tạo</th>
            <th>Địa điểm</th>
            <th>Thời gian</th>
            <th>Số lượng thành viên</th>
            <th>Giá</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registered_training as $training_id): ?>
            <?php
              $post = get_post($training_id);
              if (!$post || $post->post_type !== 'training_post') continue;
              
              $date_train = get_post_meta($training_id, 'date_train', true);
              // Format date_train from Ymd to d/m/Y
              $date_train_formatted = '';
              if ($date_train && preg_match('/^\d{8}$/', $date_train)) {
                $date_train_formatted = date('d/m/Y', strtotime($date_train));
              }
              $location_train = get_post_meta($training_id, 'location_train', true);
              $time_train = get_post_meta($training_id, 'time_train', true);
              $number_member = get_post_meta($training_id, 'number_member', true);
              $price_train = get_post_meta($training_id, 'price_train', true);
            ?>
            <tr>
              <td><?php echo esc_html($post->post_title); ?></td>
              <td><?php echo esc_html($date_train_formatted); ?></td>
              <td><?php echo esc_html($location_train); ?></td>
              <td><?php echo esc_html($time_train); ?></td>
              <td><?php echo esc_html($number_member); ?></td>
              <td><?php echo esc_html($price_train); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="course__empty">
      <p>Chưa có khóa học nào được đăng ký</p>
    </div>
  <?php endif; ?>
</div>