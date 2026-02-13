<?php

add_action('wp_ajax_kam_register_event', 'kam_register_event_callback');
add_action('wp_ajax_nopriv_kam_register_event', 'kam_register_event_callback');

function kam_register_event_callback() {
  // Get filter value from POST
  $event_id = isset($_POST['event_id']) ? sanitize_text_field($_POST['event_id']) : '';

  if ( $event_id ) {
    $current_user_id = get_current_user_id();

    // Đăng ký thành viên cho event
    $registered_members = get_post_meta($event_id, 'registered_members', true) ?: [];
    if (!in_array($current_user_id, $registered_members)) {
      $registered_members[] = $current_user_id;
      update_post_meta($event_id, 'registered_members', $registered_members);
    }

    // Đăng ký event cho user
    $registered_training = get_user_meta($current_user_id, 'registered_training', true) ?: [];
    if (!in_array($event_id, $registered_training)) {
      $registered_training[] = $event_id;
      update_user_meta($current_user_id, 'registered_training', $registered_training);
      wp_send_json_success(['message' => 'Đăng ký thành công']);
    }
  }

  wp_die();
}
