jQuery(document).ready(function($) {
  const $timer = $('#countdown-timer');
  if (!$timer.length) return;
  const endTimeStr = $timer.data('end-time');
  const fixedEndTimeStr = endTimeStr.length === 16 ? endTimeStr + ':00' : endTimeStr;

  if (!fixedEndTimeStr) return;
  const endTime = new Date(fixedEndTimeStr.replace('T', ' ')).getTime();

  function updateCountdown() {
    const now = new Date().getTime();
    let diff = Math.floor((endTime - now) / 1000);
    if (diff > 0) {
      const days = Math.floor(diff / (60 * 60 * 24));
      const hours = Math.floor((diff % (60 * 60 * 24)) / (60 * 60));
      const minutes = Math.floor((diff % (60 * 60)) / 60);
      const seconds = diff % 60;
      let time = '';
      time += `<b>${days}</b> ngày `;
      time += `<b>${hours}</b> giờ `;
      time += `<b>${minutes}</b> phút `;
      time += `<b>${seconds}</b> giây`;
      $timer.html(time);
    } else {
      $timer.text("Đã hết thời gian mở bán");
      clearInterval(interval);
      $('.add-to-cart-container').hide();
    }
  }
  updateCountdown();
  const interval = setInterval(updateCountdown, 1000);
});

jQuery(document).ready(function($) {
  // -----Total Price-----
  // Hàm cập nhật tổng giá
  function updateTotalPrice() {
    const qty = parseInt($('input.qty').val()) || 1;
    const $totalPriceWrapper = $('.total-price-wrapper');
    const productPrice = parseFloat($totalPriceWrapper.data('product_price_total'));
    if (isNaN(productPrice) || productPrice <= 0) return;
    const totalPrice = productPrice * qty;
    const formattedPrice = new Intl.NumberFormat('vi-VN').format(totalPrice);
    $totalPriceWrapper.find('.total-price-wrapper-number').html(
      `<span class="woocommerce-Price-amount amount"><bdi>${formattedPrice}&nbsp;<span class="woocommerce-Price-currencySymbol">VNĐ</span></bdi></span>`
    );
  }

  // Khi thay đổi số lượng
  $('form.cart').on('change', 'input.qty', function() {
    updateTotalPrice();
  });

  // Khi thay đổi variations
  $('form.variations_form').on('found_variation', function(event, variation) {
    const $totalPriceWrapper = $('.total-price-wrapper');
    if (variation.display_price) {
      $totalPriceWrapper.data('product_price_total', variation.display_price);
      updateTotalPrice();
    }
  });
});