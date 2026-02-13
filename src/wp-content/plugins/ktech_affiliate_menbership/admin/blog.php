<?php

class KAM_Blog_Setting {
  public function __construct() {
    add_action('add_meta_boxes', array($this, 'config'));
    add_action('save_post', array($this, 'save'));
  }

  public function config () {
    add_meta_box(
      'allowed_account_roles',
      'Loại tài khoản cho phép',
      array($this, 'render'),
      'post',
      'side',
      'default'
    );
  }

  public function render($post) {
    // Lấy tất cả role hiện tại
    if (function_exists('get_editable_roles')) {
      $wp_roles = get_editable_roles();
    } else {
      global $wp_roles;
      $wp_roles = $wp_roles->roles;
    }
    $selected = (array) get_post_meta($post->ID, 'allowed_account_roles', true);
    echo '<div style="margin-bottom:8px;">Chọn loại tài khoản được phép:</div>';
    foreach ($wp_roles as $key => $role) {
        $checked = in_array($key, $selected) ? 'checked' : '';
        echo '<label style="display:block;margin-bottom:4px;">';
        echo '<input type="checkbox" name="allowed_account_roles[]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($role['name']);
        echo '</label>';
    }
    $checked_guest = in_array('guest', $selected) ? 'checked' : '';
    echo '<label style="display:block;margin-bottom:4px;">';
    echo '<input type="checkbox" name="allowed_account_roles[]" value="guest" ' . $checked_guest . '> Khách lẻ';
    echo '</label>';
  }

  public function save ($post_id) {
    if (isset($_POST['allowed_account_roles'])) {
      update_post_meta($post_id, 'allowed_account_roles', $_POST['allowed_account_roles']);
    } else {
      delete_post_meta($post_id, 'allowed_account_roles');
    }
  }
}

// Khởi tạo class để hook hoạt động
new KAM_Blog_Setting();
