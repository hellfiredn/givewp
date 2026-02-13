<?php
add_action('wp_ajax_kam_save_address', 'kam_save_address_callback');
add_action('wp_ajax_nopriv_kam_save_address', 'kam_save_address_callback');

function kam_save_address_callback() {
    // Lấy dữ liệu từ POST
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    
    if (!$form_data) {
        wp_die('Không có dữ liệu');
    }
    
    // Parse dữ liệu form serialize
    parse_str($form_data, $parsed_data);
    
    // Lấy các trường cần thiết
    $user_id = get_current_user_id();
    $name = sanitize_text_field($parsed_data['recipient_name'] ?? '');
    $type = sanitize_text_field($parsed_data['address_type'] ?? '');
    $phone = sanitize_text_field($parsed_data['phone'] ?? '');
    $city = sanitize_text_field($parsed_data['city'] ?? '');
    $district = sanitize_text_field($parsed_data['district'] ?? '');
    $commune = sanitize_text_field($parsed_data['commune'] ?? '');
    $address = sanitize_textarea_field($parsed_data['address'] ?? '');
    $is_default = isset($parsed_data['is_default']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($phone) || empty($address)) {
        wp_die('Vui lòng điền đầy đủ thông tin');
    }
    
    // Lưu vào database
    $db = new KTech_Address_DB();
    $result = $db->insert([
        'user_id' => $user_id,
        'name' => $name,
        'type' => $type,
        'phone' => $phone,
        'city' => $city,
        'district' => $district,
        'commune' => $commune,
        'address' => $address,
        'is_default' => $is_default
    ]);
    
    // if ($result) {
    //     echo 'Lưu địa chỉ thành công!';
    // } else {
    //     echo 'Có lỗi xảy ra khi lưu địa chỉ';
    // }

    $list_address = $db->get_by_user($user_id);

    $html = '';

    if (!empty($list_address)) {    
        if ($location === 'my-account') {
            foreach ($list_address as $address) {
                ?>
                    <div class="address-list__row">
                        <div class="address-list__cell"><input type="radio" <?php echo($address->is_default == 1 ? 'checked' : ''); ?> name="kam-address-is-default" /></div>
                        <div class="address-list__cell"><?php echo $address->type; ?></div>
                        <div class="address-list__cell"><?php echo $address->name; ?></div>
                        <div class="address-list__cell"><?php echo $address->phone; ?></div>
                        <div class="address-list__cell address-list__cell--content"><?php echo $address->address; ?>
                            <div class="address-list__cell--btn">
                                <button class="address-list__action address-list__action--edit"><img src="/wp-content/uploads/2025/08/pencil.png" /></button>
                                <button class="address-list__action address-list__action--delete"><img src="/wp-content/uploads/2025/08/delete-icon.png" /></button>
                            </div>
                        </div>
                    </div>
                <?php
            }
        }

        if ($location === 'checkout') {
            foreach ($list_address as $address) {
                ?>
                <div class="address-option">
                    <img class="kam-address-selected-location" src="/wp-content/uploads/2025/09/location.png" />
                    <label for="saved_address_<?php echo $address->id; ?>">
                        <strong><?php echo esc_html($address->name); ?></strong> - <?php echo esc_html($address->phone); ?><br>
                        <small><?php echo esc_html($address->address . ', ' . $address->district . ', ' . $address->city); ?></small>
                    </label>
                    <div>
                        <input type="radio" 
                        <?php checked($address->is_default, 1); ?>
                        id="saved_address_<?php echo $address->id; ?>" 
                        name="kam-address-is-default"
                        value="saved" 
                        data-id="<?php echo $address->id; ?>"
                        data-name="<?php echo esc_attr($address->name); ?>"
                        data-phone="<?php echo esc_attr($address->phone); ?>"
                        data-city="<?php echo esc_attr($address->city); ?>"
                        data-district="<?php echo esc_attr($address->district); ?>"
                        data-commune="<?php echo esc_attr($address->commune); ?>"
                        data-address="<?php echo esc_attr($address->address); ?>">
                    </div>
                </div>
                <?php
            }
        }
    } else {
        $html .= '<p>Chưa có địa chỉ</p>';
    }
    
    echo $html;

    wp_die(); // Bắt buộc có để kết thúc AJAX request
}

add_action('wp_ajax_kam_delete_address', 'kam_delete_address_callback');
add_action('wp_ajax_nopriv_kam_delete_address', 'kam_delete_address_callback');

function kam_delete_address_callback() {
    // Lấy dữ liệu từ POST
    $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
    
    if (!$address_id) {
        wp_die('Không có dữ liệu');
    }
    
    $db = new KTech_Address_DB();
    $result = $db->delete($address_id);

    $user_id = get_current_user_id();
    $list_address = $db->get_by_user($user_id);

    $html = '';

    if (!empty($list_address)) {
        foreach ($list_address as $address) {
            ?>
                <div class="address-list__row" data-id="<?php echo $address->id; ?>">
                    <div class="address-list__cell"><input type="radio" <?php echo($address->is_default == 1 ? 'checked' : ''); ?> name="kam-address-is-default" /></div>
                    <div class="address-list__cell"><?php echo $address->type; ?></div>
                    <div class="address-list__cell"><?php echo $address->name; ?></div>
                    <div class="address-list__cell"><?php echo $address->phone; ?></div>
                    <div class="address-list__cell address-list__cell--content"><?php echo $address->address; ?> 
                        <div class="address-list__cell--btn">
                            <button class="address-list__action address-list__action--edit"><img src="/wp-content/uploads/2025/08/pencil.png" /></button>
                            <button class="address-list__action address-list__action--delete"><img src="/wp-content/uploads/2025/08/delete-icon.png" /></button>
                        </div>
                    </div>
                </div>
            <?php
        }
    }
    
    echo $html;

    wp_die(); // Bắt buộc có để kết thúc AJAX request
}

add_action('wp_ajax_kam_set_default_address', 'kam_set_default_address_callback');
add_action('wp_ajax_nopriv_kam_set_default_address', 'kam_set_default_address_callback');

function kam_set_default_address_callback() {
    // Lấy dữ liệu từ POST
    $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
    
    if (!$address_id) {
        wp_die('Không có dữ liệu');
    }

    $db = new KTech_Address_DB();
    $result = $db->set_default($address_id);
    
    wp_die(); // Bắt buộc có để kết thúc AJAX request
}


add_action('wp_ajax_kam_edit_address', 'kam_edit_address_callback');
add_action('wp_ajax_nopriv_kam_edit_address', 'kam_edit_address_callback');

function kam_edit_address_callback() {
    // Lấy dữ liệu từ POST
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : '';
    
    if (!$form_data) {
        wp_die('Không có dữ liệu');
    }
    
    // Parse dữ liệu form serialize
    parse_str($form_data, $parsed_data);
    
    // Lấy các trường cần thiết
    $user_id = get_current_user_id();
    $name = sanitize_text_field($parsed_data['recipient_name'] ?? '');
    $phone = sanitize_text_field($parsed_data['phone'] ?? '');
    $city = sanitize_text_field($parsed_data['city'] ?? '');
    $district = sanitize_text_field($parsed_data['district'] ?? '');
    $commune = sanitize_text_field($parsed_data['commune'] ?? '');
    $address = sanitize_textarea_field($parsed_data['address'] ?? '');
    $address_id = sanitize_textarea_field($parsed_data['address_id'] ?? '');
    $type = sanitize_textarea_field($parsed_data['address_type'] ?? '');
    $is_default = isset($parsed_data['is_default']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($phone) || empty($address)) {
        wp_die('Vui lòng điền đầy đủ thông tin');
    }
    
    // Lưu vào database
    $db = new KTech_Address_DB();
    $result = $db->update($address_id, [
        'user_id' => $user_id,
        'name' => $name,
        'type' => $type,
        'phone' => $phone,
        'city' => $city,
        'district' => $district,
        'commune' => $commune,
        'address' => $address,
        'is_default' => $is_default
    ]);

    $list_address = $db->get_by_user($user_id);

    $html = '';

    if (!empty($list_address)) {    
        foreach ($list_address as $address) {
            $html .= '<div class="address-list__row" 
                    data-id="' . $address->id . '"
                    data-name="' . esc_attr($address->name) . '"
                    data-phone="' . esc_attr($address->phone) . '"
                    data-city="' . esc_attr($address->city) . '"
                    data-district="' . esc_attr($address->district) . '"
                    data-commune="' . esc_attr($address->commune) . '"
                    data-type="' . esc_attr($address->type) . '"
                    data-address="' . esc_attr($address->address) . '"
                    data-is_default="' . $address->is_default . '"
                >
                <div class="address-list__cell"><input type="radio" name="kam-address-is-default" /></div>
                <div class="address-list__cell">' . $address->name . '</div>
                <div class="address-list__cell">' . $address->type . '</div>
                <div class="address-list__cell">' . $address->type . '</div>
                <div class="address-list__cell">' . $address->phone . '</div>
                <div class="address-list__cell address-list__cell--content">' . $address->address . '
                <div class="address-list__cell--btn">
                    <button class="address-list__action address-list__action--edit"><img src="/wp-content/uploads/2025/08/pencil.png" /></button>
                    <button class="address-list__action address-list__action--delete"><img src="/wp-content/uploads/2025/08/delete-icon.png" /></button>
                </div>
                </div>
            </div>';
        }
    } else {
        $html .= '<p>Chưa có địa chỉ</p>';
    }
    
    echo $html;

    wp_die(); // Bắt buộc có để kết thúc AJAX request
}