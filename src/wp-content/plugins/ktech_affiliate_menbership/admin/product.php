<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class KAM_Product_Setting {
  public function __construct() {
    add_action('woocommerce_product_data_panels', array($this, 'add_end_of_sale_checkbox'));
    add_action('woocommerce_process_product_meta', array($this, 'save_end_of_sale_checkbox'));
    add_filter('woocommerce_product_data_tabs', array($this, 'add_new_data_tabs'));
  }

  public function add_new_data_tabs ($tabs) {
      $tabs['affiliate_membership'] = array(
          'label'    => __('Affiliate Membership', 'woocommerce'),
          'target'   => 'affiliate_membership_data',
          'class'    => array(),
          'priority' => 60,
      );
      return $tabs;
  }

  // Thêm checkbox End of Sale, ngày kết thúc, giá Master, giá Pharmer, và danh sách role user
  public function add_end_of_sale_checkbox() {
    global $post;
    $end_of_sale = get_post_meta($post->ID, '_end_of_sale', true);
    $end_of_sale_date = get_post_meta($post->ID, '_end_of_sale_date', true);
    $master_price = get_post_meta($post->ID, '_master_price', true);
    $pharmer_price = get_post_meta($post->ID, '_pharmer_price', true);
    $allowed_roles = get_post_meta($post->ID, '_allowed_roles', true);
    if (!is_array($allowed_roles)) $allowed_roles = array();
    echo '<div id="affiliate_membership_data" class="panel woocommerce_options_panel">';
    woocommerce_wp_checkbox( array(
      'id'          => '_end_of_sale',
      'label'       => __('End of Sale', 'ktech'),
      'description' => __('Đánh dấu sản phẩm này là kết thúc sale.', 'ktech'),
      'value'       => $end_of_sale === 'yes' ? 'yes' : 'no',
    ));
    echo '<p class="form-field end_of_sale_date_field" style="display:' . ($end_of_sale === 'yes' ? 'block' : 'none') . ';">';
    echo '<label for="_end_of_sale_date">' . __('Ngày & giờ kết thúc sale', 'ktech') . '</label>';
    echo '<input type="datetime-local" class="short" name="_end_of_sale_date" id="_end_of_sale_date" value="' . esc_attr($end_of_sale_date) . '" />';
    echo '</p>';
    // Giá cho Master
    echo '<p class="form-field master_price_field">';
    echo '<label for="_master_price">' . __('Giá cho Master', 'ktech') . '</label>';
    echo '<input type="number" step="0.01" min="0" class="short" name="_master_price" id="_master_price" value="' . esc_attr($master_price) . '" />';
    echo '</p>';
    // Giá cho Pharmer
    echo '<p class="form-field pharmer_price_field">';
    echo '<label for="_pharmer_price">' . __('Giá cho VIP Member', 'ktech') . '</label>';
    echo '<input type="number" step="0.01" min="0" class="short" name="_pharmer_price" id="_pharmer_price" value="' . esc_attr($pharmer_price) . '" />';
    echo '</p>';
    // Danh sách role user
    global $wp_roles;
    $roles = $wp_roles->roles;
    echo '<style>
      .allowed_roles_field .role-checkbox-list label {
        margin: 0;
      }
    </style>';
    echo '<p class="form-field allowed_roles_field">';
    echo '<label>' . __('Cho phép với roles:', 'ktech') . '</label>';
    echo '<span class="role-checkbox-list">';
    $checked_all = in_array('all', $allowed_roles) ? 'checked' : '';
    echo '<label><input type="checkbox" value="all" name="_allowed_roles[]" ' . $checked_all . ' > All</label>';
    $checked_guest = in_array('guest', $allowed_roles) ? 'checked' : '';
    echo '<label><input type="checkbox" value="guest" name="_allowed_roles[]" ' . $checked_guest . ' > Khách lẻ</label>';
    foreach ($roles as $role_key => $role) {
      $checked = in_array($role_key, $allowed_roles) ? 'checked' : '';
      echo '<label><input type="checkbox" name="_allowed_roles[]" value="' . esc_attr($role_key) . '" ' . $checked . '> ' . esc_html($role['name']) . '</label>';
    }
    echo '</span>';
    echo '</p>';
    // Checkbox tên sản phẩm affiliate
    $is_affiliate_product = get_post_meta($post->ID, '_is_affiliate_product', true);
    woocommerce_wp_checkbox( array(
      'id'          => '_is_affiliate_product',
      'label'       => __('Sản phẩm Affiliate', 'ktech'),
      'description' => __('Đánh dấu sản phẩm này là sản phẩm affiliate.', 'ktech'),
      'value'       => $is_affiliate_product === 'yes' ? 'yes' : 'no',
    ));
    ?>
    <script>
    jQuery(document).ready(function($){
      $('#_end_of_sale').on('change', function(){
        if($(this).is(':checked')){
          $('.end_of_sale_date_field').show();
        } else {
          $('.end_of_sale_date_field').hide();
        }
      });
    });
    </script>
    <?php
    echo '</div>';
  }

  // Lưu giá trị checkbox End of Sale, ngày kết thúc, giá Master, Pharmer, allowed roles
  public function save_end_of_sale_checkbox($post_id) {
    $end_of_sale = isset($_POST['_end_of_sale']) ? 'yes' : 'no';
    update_post_meta($post_id, '_end_of_sale', $end_of_sale);
    $end_of_sale_date = isset($_POST['_end_of_sale_date']) ? sanitize_text_field($_POST['_end_of_sale_date']) : '';
    update_post_meta($post_id, '_end_of_sale_date', $end_of_sale_date);
    $master_price = isset($_POST['_master_price']) ? floatval($_POST['_master_price']) : '';
    update_post_meta($post_id, '_master_price', $master_price);
    $pharmer_price = isset($_POST['_pharmer_price']) ? floatval($_POST['_pharmer_price']) : '';
    update_post_meta($post_id, '_pharmer_price', $pharmer_price);
    // Lưu allowed_roles, luôn là mảng
    $allowed_roles = array();
    if (isset($_POST['_allowed_roles'])) {
      if (is_array($_POST['_allowed_roles'])) {
        foreach ($_POST['_allowed_roles'] as $role) {
          $allowed_roles[] = sanitize_text_field($role);
        }
      } else {
        $allowed_roles[] = sanitize_text_field($_POST['_allowed_roles']);
      }
    }
    update_post_meta($post_id, '_allowed_roles', $allowed_roles);
    // Lưu trạng thái affiliate product
    $is_affiliate_product = isset($_POST['_is_affiliate_product']) ? 'yes' : 'no';
    update_post_meta($post_id, '_is_affiliate_product', $is_affiliate_product);
  }
}

// Initialize the class
new KAM_Product_Setting();
