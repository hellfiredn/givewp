<div class="voucher">
    <div class="voucher__header">
        <h1 class="voucher__header-title">Phiếu quà tặng của bạn</h1>
    </div>

    <div class="voucher__section">
      <?php
        $user_id = get_current_user_id();
        $vouchers = get_user_meta($user_id, 'kam_user_vouchers', true);
        if (!is_array($vouchers)) {
          $vouchers = array();
        }
      ?>
      <?php if (!empty($vouchers)) { ?>
        <?php foreach ($vouchers as $voucher) { ?>
          <?php
              $voucher_db = new KTech_Voucher_DB();
              $voucher_obj = $voucher_db->get_by_id($voucher['voucher_id']);
          ?>
          <div class="voucher__item" data-voucher-id="<?php echo esc_attr($voucher['voucher_id']); ?>" data-description="<?php echo esc_attr($voucher_obj->description); ?>" data-image="<?php echo esc_url($voucher_obj->image_url); ?>">
            <img src="<?php echo $voucher_obj->image_url; ?>" />
          </div>
        <?php } ?>
      <?php } ?>
    </div>

    <!-- Voucher Popup -->
    <div id="voucher-detail-popup">
      <div class="voucher-detail-popup-inner">
        <button id="voucher-popup-close"><img src="/wp-content/uploads/2025/10/close.png" /></button>
        <img class="voucher-popup-logo" src="/wp-content/uploads/2025/10/logo.avif" />
        <div id="voucher-send-result">
          <img id="voucher-popup-image" src="" />
          <p id="voucher-popup-description"></p>
          <button id="voucher-send-email" class="button">Dùng ngay</button>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    jQuery(document).ready(function($){
      $('.voucher__item').on('click', function(){
        $('#voucher-popup-image').attr('src', $(this).data('image'));
        $('#voucher-popup-description').text($(this).data('description'));
        $('#voucher-detail-popup').css('display', 'flex');
        $('#voucher-send-email').data('voucherId', $(this).data('voucher-id'));
      });
      $('#voucher-popup-close').on('click', function(){
        $('#voucher-detail-popup').css('display', 'none');
      });
      $('#voucher-send-email').on('click', function(){
        var voucherId = $(this).data('voucherId');
        var btn = $(this);
        btn.prop('disabled', true).text('Đang gửi...');
        $.ajax({
          url: '/wp-admin/admin-ajax.php',
          type: 'POST',
          data: {
            action: 'send_voucher_email',
            voucher_id: voucherId
          },
          success: function(res){
            $('#voucher-send-result').html('<b>Voucher đã được gửi về email:<br />' + '<?php echo esc_js(wp_get_current_user()->user_email); ?>' + '!</b>').show();
            btn.text('Dùng ngay').prop('disabled', false);
          },
          error: function(){
            $('#voucher-send-result').text('Gửi thất bại, thử lại sau.').show();
            btn.text('Dùng ngay').prop('disabled', false);
          }
        });
      });
    });
  </script>
</div>