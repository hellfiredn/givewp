<?php
    $db = new KTech_Address_DB();
    $user_id = get_current_user_id();
    $address_list = $db->get_by_user($user_id);
?>

<!-- Danh sách địa chỉ -->
<div class="address-list">
    <div class="address-list__header">
        <div class="address-list__cell address-list__cell--default">Mặc định</div>
        <div class="address-list__cell address-list__cell--default">Loại địa chỉ</div>
        <div class="address-list__cell address-list__cell--name">Tên</div>
        <div class="address-list__cell address-list__cell--phone">SDT</div>
        <div class="address-list__cell address-list__cell--address">Địa chỉ</div>
        <div class="address-list__cell address-list__cell--actions"></div>
    </div>
    <div id="address-list-data">
        <?php if (!empty($address_list)) { ?>
            <?php foreach ($address_list as $address) { ?>
                <div class="address-list__row" 
                    data-id="<?php echo $address->id; ?>"
                    data-id="<?php echo $address->type; ?>"
                    data-name="<?php echo $address->name; ?>"
                    data-phone="<?php echo $address->phone; ?>"
                    data-city="<?php echo $address->city; ?>"
                    data-district="<?php echo $address->district; ?>"
                    data-commune="<?php echo $address->commune; ?>"
                    data-address="<?php echo $address->address; ?>"
                    data-type="<?php echo $address->type; ?>"
                    data-is_default="<?php echo $is_default->is_default; ?>"
                >
                    <div class="address-list__cell">
                        <input type="radio" name="kam-address-is-default" data-id="<?php echo $address->id; ?>" <?php echo $address->is_default ? 'checked' : ''; ?> />
                    </div>
                    <div class="address-list__cell"><?php echo $address->type; ?></div>
                    <div class="address-list__cell"><?php echo $address->name; ?></div>
                    <div class="address-list__cell"><?php echo $address->phone; ?></div>
                    <div class="address-list__cell address-list__cell--content"><?php echo $address->address; ?>
                        <div class="address-list__cell--btn">
                            <button class="address-list__action address-list__action--edit">
                                <img src="/wp-content/uploads/2025/08/pencil.png" />
                            </button>
                            <button class="address-list__action address-list__action--delete">
                                <img src="/wp-content/uploads/2025/08/delete-icon.png" />
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>Chưa có địa chỉ</p>
        <?php } ?>
    </div>
</div>
<?php

global $vietnam_cities;

?>
<!-- Form thêm/sửa địa chỉ -->
<div class="address-form">
    <h2 class="address-form__title">Thêm/Sửa địa chỉ nhận hàng</h2>
    <form id="kam_save_address" data-location="my-account">
        <?php wp_nonce_field('kam_save_address_action', 'kam_save_address_nonce'); ?>
        <div class="address-form__group address-form__group--full address-type-wrapper">
            <input type="radio" id="address-type-home" name="address_type" value="Nhà riêng" required checked>
            <label for="address-type-home"  class="btn-add-address-type">Nhà riêng</label>
            <input type="radio" id="address-type-office" name="address_type" value="Văn phòng" required>
            <label for="address-type-office"  class="btn-add-address-type">Văn phòng</label>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Tên người nhận</label>
            <input type="text" name="recipient_name" autocomplete="off" class="address-form__input" placeholder="Nhập tên người nhận" required>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Số điện thoại</label>
            <input type="text" name="phone" class="address-form__input" autocomplete="off" placeholder="Nhập số điện thoại" required>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Chọn tỉnh/thành phố</label>
            <select name="city" class="address-form__input" required>
                <option value="">Chọn tỉnh/thành phố</option>
                <?php foreach ($vietnam_cities as $city) { ?>
                    <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Chọn quận/huyện</label>
            <select name="district" class="address-form__input" required>
                <option value="">Chọn quận/huyện</option>
            </select>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Phường/Xã</label>
            <select name="commune" class="address-form__input" required>
            <option value="">Phường/Xã</option>
            </select>
        </div>
        <div class="address-form__group">
            <label class="address-form__label">Địa chỉ</label>
            <input type="text" name="address" autocomplete="off" class="address-form__input" placeholder="Nhập địa chỉ cụ thể" required>
        </div>
        <button type="submit" name="kam_save_address" class="address-form__submit">Thêm địa chỉ</button>
    </form>
</div>