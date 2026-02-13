<?php


function bxh_shortcode() {
    ob_start();
    $year = date('Y');
    $month = date('n');
    $months = [
        1 => 'Tháng Một',
        2 => 'Tháng Hai', 
        3 => 'Tháng Ba',
        4 => 'Tháng Tư',
        5 => 'Tháng Năm',
        6 => 'Tháng Sáu',
        7 => 'Tháng Bảy',
        8 => 'Tháng Tám',
        9 => 'Tháng Chín',
        10 => 'Tháng Mười',
        11 => 'Tháng Mười Một',
        12 => 'Tháng Mười Hai'
    ];
    $month_text = $months[$month];
    ?>
    <div class="bxh-wrapper">
        <div class="bxh-wrapper-top">
            <div class="bxh-tabs">
                <button class="bxh-tab active" data-tab="thang">Bảng xếp hạng tháng</button>
                <button class="bxh-tab" data-tab="tong">Bảng xếp hạng tổng</button>
            </div>

            <div class="bxh-controls">
                <div class="bxh-year-dropdown">
                    <button class="bxh-year" data-value="<?php echo $year; ?>" id="year-btn"><?php echo $year; ?></button>
                    <div class="year-dropdown-content" id="year-dropdown" style="display:none;">
                        <div class="bxh-item-option" data-year="2025">2025</div>
                        <div class="bxh-item-option" data-year="2024">2024</div>
                        <div class="bxh-item-option" data-year="2023">2023</div>
                        <div class="bxh-item-option" data-year="2022">2022</div>
                        <div class="bxh-item-option" data-year="2021">2021</div>
                    </div>
                </div>
                <div class="bxh-month-dropdown">
                    <button class="bxh-month" data-value="<?php echo $month; ?>" id="month-btn"><?php echo $month_text; ?></button>
                    <div class="month-dropdown-content" id="month-dropdown" style="display:none;">
                        <div class="bxh-item-option" data-month="1">Tháng Một</div>
                        <div class="bxh-item-option" data-month="2">Tháng Hai</div>
                        <div class="bxh-item-option" data-month="3">Tháng Ba</div>
                        <div class="bxh-item-option" data-month="4">Tháng Tư</div>
                        <div class="bxh-item-option" data-month="5">Tháng Năm</div>
                        <div class="bxh-item-option" data-month="6">Tháng Sáu</div>
                        <div class="bxh-item-option" data-month="7">Tháng Bảy</div>
                        <div class="bxh-item-option" data-month="8">Tháng Tám</div>
                        <div class="bxh-item-option" data-month="9">Tháng Chín</div>
                        <div class="bxh-item-option" data-month="10">Tháng Mười</div>
                        <div class="bxh-item-option" data-month="11">Tháng Mười Một</div>
                        <div class="bxh-item-option" data-month="12">Tháng Mười Hai</div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="master-heading-mobile">Bảng xếp hạng tháng</h2>
        <div class="bxh-content active" id="tab-thang">
            <div class="loader"></div>
        </div>

        <h2 class="master-heading-mobile">Bảng xếp hạng tổng</h2>
        <div class="bxh-content" id="tab-tong">
            <?php
                $db = new KTech_Affiliate_DB();
                $masters = $db->get_top_users_by_month();
                if (!empty($masters)) {
                    foreach ($masters as $i => $master) {
                        $class = ($i < 3) ? 'top3' : '';
                        echo '<div class="bxh-item ' . $class . '"><span>' . ($i + 1) . '</span> ' . $master->display_name . '</div>';
                    }
                } else {
                    echo '<p>Chưa có dữ liệu thống kê</p>';
                }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bang_xep_hang', 'bxh_shortcode');
