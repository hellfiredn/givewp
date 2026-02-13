jQuery(document).ready(function($) {
  $('#kam_save_address').on('submit', function(e) {
    e.preventDefault(); // Ngăn chặn hành vi mặc định của form
    const $form = $(this); // Lưu reference đến form
    const formData = $form.serialize(); // Lấy dữ liệu từ form
    const location = $form.data('location');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_save_address',
        form_data: formData,
        location: location
      },
      success: function(response) {
        $form[0].reset(); // Sử dụng $form thay vì $(this)
        if (location == 'my-account') {
          $('#address-list-data').html(response);
          $('#address-list-data')[0].scrollIntoView({ behavior: 'smooth' });
        }
        if (location == 'checkout') {
          $('#saved-addresses-list').html(response);
          $('#saved-addresses-list')[0].scrollIntoView({ behavior: 'smooth' });
          $('input[name="kam-address-is-default"]').on('change', function() {
            const selectedAddress = $(this);
            updateSelectedAddressDisplay(selectedAddress);
            applyAddressToBoth(selectedAddress);
            $('#kam-address-checkout-popup').hide();
            $('body').removeClass('popup-open');

            const addressId = $(this).data('id');
            console.log(addressId);
            $.ajax({
              url: kam_ajax_obj.ajaxurl,
              type: 'POST',
              data: {
                action: 'kam_set_default_address',
                address_id: addressId
              },
              success: function(response) {
                // $('#address-list-data').html(response);
                // $('#address-list-data')[0]?.scrollIntoView({ behavior: 'smooth' });
              },
              error: function(xhr, status, error) {
                alert('Có lỗi xảy ra khi thiết lập địa chỉ mặc định: ' + error);
              }
            });
          });
        }
      },
      error: function(xhr, status, error) {
        // alert('Có lỗi xảy ra: ' + error);
      }
    });
  });

  $('#address-list-data').on('click', '.address-list__action--delete', function(e) {
    e.preventDefault();
    if (!confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) {
      return;
    }
    const addressId = $(this).closest('.address-list__row').data('id');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_delete_address',
        address_id: addressId
      },
      success: function(response) {
        $('#address-list-data').html(response);
        $('#address-list-data')[0]?.scrollIntoView({ behavior: 'smooth' });
      },
      error: function(xhr, status, error) {
        alert('Có lỗi xảy ra khi xóa địa chỉ: ' + error);
      }
    });
  });

  $('[name="kam-address-is-default"]').on('change', function(e) {
    const addressId = $(this).data('id');
    $.ajax({
      url: kam_ajax_obj.ajaxurl,
      type: 'POST',
      data: {
        action: 'kam_set_default_address',
        address_id: addressId
      },
      success: function(response) {
        // $('#address-list-data').html(response);
        // $('#address-list-data')[0]?.scrollIntoView({ behavior: 'smooth' });
      },
      error: function(xhr, status, error) {
        alert('Có lỗi xảy ra khi thiết lập địa chỉ mặc định: ' + error);
      }
    });
  });

  $('#kam_save_address .address-form__input[name="city"]').on('change', function() {
    const selectedCity = $(this).val();
    const districts = Object.keys(kam_ajax_obj.vietnam_addresses[selectedCity] || []);
    const $districtSelect = $('#kam_save_address select[name="district"]');
    $districtSelect.empty();
    $districtSelect.append('<option value="">Chọn quận/huyện</option>');
    districts.forEach(function(district) {
      $districtSelect.append('<option value="' + district + '">' + district + '</option>');
    });
  });

  $('#kam_save_address .address-form__input[name="district"]').on('change', function() {
    const selectedCity = $('#kam_save_address .address-form__input[name="city"]').val();
    const selectedDistrict = $(this).val();
    const communes = kam_ajax_obj.vietnam_addresses[selectedCity][selectedDistrict] || [];
    const $communeSelect = $('#kam_save_address select[name="commune"]');
    $communeSelect.empty();
    $communeSelect.append('<option value="">Chọn xã/phường</option>');
    communes.forEach(function(commune) {
      $communeSelect.append('<option value="' + commune + '">' + commune + '</option>');
    });
  });

  // Popup address edit
  $('#kam_edit_address .address-form__input[name="city"]').on('change', function() {
    const selectedCity = $(this).val();
    const districts = Object.keys(kam_ajax_obj.vietnam_addresses[selectedCity] || []);
    const $districtSelect = $('#kam_edit_address select[name="district"]');
    $districtSelect.empty();
    $districtSelect.append('<option value="">Chọn quận/huyện</option>');
    districts.forEach(function(district) {
      $districtSelect.append('<option value="' + district + '">' + district + '</option>');
    });
  });

  $('#kam_edit_address .address-form__input[name="district"]').on('change', function() {
    const selectedCity = $('#kam_edit_address .address-form__input[name="city"]').val();
    const selectedDistrict = $(this).val();
    const communes = kam_ajax_obj.vietnam_addresses[selectedCity][selectedDistrict] || [];
    const $communeSelect = $('#kam_edit_address select[name="commune"]');
    $communeSelect.empty();
    $communeSelect.append('<option value="">Chọn xã/phường</option>');
    communes.forEach(function(commune) {
      $communeSelect.append('<option value="' + commune + '">' + commune + '</option>');
    });
  });

  // Checkout
  // Show/Hide address popup
  $('.kam-address-selected-edit').on('click', function() {
    $('#kam-address-checkout-popup').show();
    $('body').addClass('popup-open');
  });

  // Hide popup when click outside
  $(document).on('click', function(e) {
    if (!$(e.target).closest('#kam-address-checkout-popup, .kam-address-selected-edit').length) {
      $('#kam-address-checkout-popup').hide();
      $('body').removeClass('popup-open');
    }
  });

  // Hide popup when click on overlay (backdrop)
  $('#kam-address-checkout-popup').on('click', function(e) {
    if (e.target === this || $(e.target).hasClass('kam-popup-backdrop')) {
      e.stopPropagation();
      $(this).hide();
      $('body').removeClass('popup-open');
    }
  });

  // Prevent popup from closing when clicking inside the popup content
  $('#kam-address-checkout-popup .popup-content, #kam-address-checkout-popup .kam-popup-content').on('click', function(e) {
    e.stopPropagation();
  });

  // Hide popup with Escape key
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      $('#kam-address-checkout-popup').hide();
      $('body').removeClass('popup-open');
    }
  });

  // Update selected address display when choosing an address
  $('input[name="kam-address-is-default"]').on('change', function() {
    const selectedAddress = $(this);
    updateSelectedAddressDisplay(selectedAddress);
    applyAddressToBoth(selectedAddress);
    $('#kam-address-checkout-popup').hide();
    $('body').removeClass('popup-open');
  });

  // Function to update the selected address display
  function updateSelectedAddressDisplay(selectedAddress) {
    var name = selectedAddress.data('name');
    var phone = selectedAddress.data('phone');
    var address = selectedAddress.data('address');
    var district = selectedAddress.data('district');
    var commune = selectedAddress.data('commune');
    var city = selectedAddress.data('city');
    
    $('.kam-address-selected-content p:first-child').html('<strong>' + name + '</strong><span> (' + phone + ')</span>');
    $('.kam-address-selected-content p:last-child').text(address + ', ' + commune + ', ' + district + ', ' + city);
  }

  // Function to apply selected address to both billing and shipping
  function applyAddressToBoth(selectedAddress) {
    const addressData = {
      name: selectedAddress.data('name'),
      phone: selectedAddress.data('phone'),
      city: selectedAddress.data('city'),
      district: selectedAddress.data('district'),
      commune: selectedAddress.data('commune'),
      address: selectedAddress.data('address'),
      id: selectedAddress.data('address-id')
    };
    
    // Apply to billing fields
    $('#billing_first_name').val(addressData.name);
    // $('#billing_last_name').val(addressData.name);
    $('#billing_phone').val(addressData.phone);
    $('#billing_city').val(addressData.city);
    $('#billing_state').val(addressData.district);
    $('#billing_address_1').val(addressData.commune);
    $('#billing_address_2').val(addressData.address);
    
    // Apply to shipping fields
    $('#shipping_first_name').val(addressData.name);
    // $('#shipping_last_name').val(addressData.name);
    $('#shipping_phone').val(addressData.phone);
    $('#shipping_city').val(addressData.city);
    $('#shipping_state').val(addressData.district);
    $('#shipping_address_1').val(addressData.commune);
    $('#shipping_address_2').val(addressData.address);
    
    // Add hidden fields for address IDs
    updateHiddenAddressId('billing_address_id', addressData.id);
    updateHiddenAddressId('shipping_address_id', addressData.id);
  }
  applyAddressToBoth($('input[name="kam-address-is-default"]:checked'));

  // Function to update or create hidden address ID fields
  function updateHiddenAddressId(fieldName, value) {
    if ($('input[name="' + fieldName + '"]').length === 0) {
      $('form.checkout').append('<input type="hidden" name="' + fieldName + '" value="' + value + '">');
    } else {
      $('input[name="' + fieldName + '"]').val(value);
    }
  }
});

// Checkout form address for guest
jQuery(document).ready(function($) {
  $('.form_address_check_for_guest [name="recipient_name"]').on('change', function() {
    const name = $(this).val();
    $('#billing_first_name').val(name);
    $('#shipping_first_name').val(name);
  });

  $('.form_address_check_for_guest [name="recipient_email"]').on('change', function() {
    const email = $(this).val();
    $('#billing_email').val(email);
    $('#shipping_email').val(email);
  });

  $('.form_address_check_for_guest [name="phone"]').on('change', function() {
    const phone = $(this).val();
    $('#billing_phone').val(phone);
    $('#shipping_phone').val(phone);
  });

  $('.form_address_check_for_guest [name="city"]').on('change', function() {
    const city = $(this).val();
    $('#billing_city').val(city);
    $('#shipping_city').val(city);
  });

  $('.form_address_check_for_guest [name="district"]').on('change', function() {
    const district = $(this).val();
    $('#billing_state').val(district);
    $('#shipping_state').val(district);
  });

  $('.form_address_check_for_guest [name="commune"]').on('change', function() {
    const address = $(this).val();
    $('#billing_address_1').val(address);
    $('#shipping_address_1').val(address);
  });

  $('.form_address_check_for_guest [name="address"]').on('change', function() {
    const address = $(this).val();
    $('#billing_address_2').val(address);
    $('#shipping_address_2').val(address);
  });
});