jQuery(document).ready(function ($) {
    $('#main-menu.mobile-sidebar .children .menu-item-has-children > a').each(function() {
        if (!$(this).next('.toggle').length) {
            $('<button class="toggle" aria-label="Chuyển đổi"><i class="icon-angle-down"></i></button>')
                .insertAfter($(this));
        }
    });

    // Hover effect using event delegation to ensure it works even if elements are loaded dynamically
    $('.comment-form-rating').on('mouseenter', 'a[role="radio"]', function(){
        const $stars = $('.comment-form-rating a[role="radio"]');
        const idx = $stars.index(this);
        $stars.removeClass('active');
        $stars.each(function(i){
            if(i <= idx) $(this).addClass('active');
        });
    });

    // Click effect
    $('.comment-form-rating').on('click', 'a[role="radio"]', function(e){
        e.preventDefault();
        const $stars = $('.comment-form-rating a[role="radio"]');
        const idx = $stars.index(this);
        setTimeout(function(){
            $stars.removeClass('selected active');
            $stars.each(function(i){
                if(i <= idx) $(this).addClass('selected active');
            });
        }, 100);
    });

    // ---------

    if (window.innerWidth < 810) {
        $('#my-account-nav.nav-vertical').append('<div id="my-account-nav-btn"></div>');
        $('#my-account-nav-btn').click(function (e) {
            e.preventDefault();
            if (window.innerWidth < 810) {
                $(this).parent().toggleClass('active');
            }
        });
    }

    // Zalo Button
    function adjustZaloButtonPosition() {
        let $button = $("#zalo-chat-button");
        let $footer = $("#footer");

        let footerTop = $footer.offset().top;          // Vị trí top của footer
        let winHeight = $(window).height();            // Chiều cao cửa sổ
        let scrollTop = $(window).scrollTop();         // Vị trí scroll
        let buttonHeight = $button.outerHeight();      // Chiều cao nút chat

        // Vị trí nút chat hiện tại nếu giữ nguyên bottom
        let buttonBottomPos = scrollTop + winHeight - $button.outerHeight() - 20;

        // Nếu nút chat chạm footer -> fix lại
        if (buttonBottomPos + buttonHeight + 20 > footerTop) {
            let overlap = buttonBottomPos + buttonHeight + 20 - footerTop;
            $button.css("bottom", overlap + 20 + "px");
        } else {
            $button.css("bottom", "20px"); // luôn cách mép dưới màn hình 20px
        }
    }
    adjustZaloButtonPosition();
    $(window).on("scroll resize", adjustZaloButtonPosition);
    

    // My Account auto redirect to account info tab
    $(document).ready(function($) {
        // var currentPath = window.location.pathname;
        // if (currentPath === '/tai-khoan/' || currentPath === '/tai-khoan') {
        //     window.location.href = '/tai-khoan/edit-account/';
        // }
        $('.header-nav a.account-login').attr('href', '/tai-khoan/edit-account/');
    });

    // Training Page
    if ($('#my-calendar').length) {
        renderCalendar("#my-calendar");
        renderCalendarDropdown("#my-calendar-mobile");
    }

    // My Account - Payback filter date
    $(".payback__filter-date").daterangepicker({
        opens: 'left',
        startDate: '01/01/2000',
        endDate: moment().format('DD/MM/YYYY'),
        locale: {
            format: 'DD-MM-YYYY'
        }
    });

    // Validate form in Register page
    // $("#custom-register-form").on("submit", function(e){
    //     let email = $(this).find('[name="email"]').val().trim();
    //     let pass  = $(this).find('[name="password"]').val().trim();
    //     let cccd  = $(this).find('[name="cccd"]').val().trim();
    //     let phone = $(this).find('[name="phone"]').val().trim();
    //     let username = $(this).find('[name="username"]').val().trim();

    //     let error = false;

    //     $(".form-error").remove(); // clear error trước

    //     // validate email
    //     if(email === "" || !/^\S+@\S+\.\S+$/.test(email)){
    //         $(this).find('[name="email"]').after("<span class='form-error' style='color:red'>Email không hợp lệ</span>");
    //         error = true;
    //     }

    //     // validate password
    //     if(pass.length < 6){
    //         $(this).find('[name="password"]').after("<span class='form-error' style='color:red'>Mật khẩu tối thiểu 6 ký tự</span>");
    //         error = true;
    //     }

    //     // validate CCCD (12 số)
    //     if(cccd === "" || !/^[0-9]{12}$/.test(cccd)){
    //         $(this).find('[name="cccd"]').after("<span class='form-error' style='color:red'>CCCD phải gồm 12 số</span>");
    //         error = true;
    //     }

    //     // validate Phone (10 số, đầu 0)
    //     if(phone === "" || !/^0[0-9]{9}$/.test(phone)){
    //         $(this).find('[name="phone"]').after("<span class='form-error' style='color:red'>Số điện thoại không hợp lệ</span>");
    //         error = true;
    //     }

    //     // validate username (chỉ chữ cái tiếng Anh, số, ít nhất 3 ký tự)
    //     if (username === "" || !/^[a-zA-Z0-9]{3,}$/.test(username)) {
    //         $(this).find('[name="username"]').after("<span class='form-error' style='color:red'>Tên đăng nhập chỉ gồm chữ cái tiếng Anh, số, tối thiểu 3 ký tự</span>");
    //         error = true;
    //     }

    //     if(error){
    //         e.preventDefault(); // chặn submit nếu có lỗi
    //     }
    // });
});

function renderCalendar(containerSelector) {
    var today = new Date();
    var currentMonth = today.getMonth();
    var currentYear = today.getFullYear();

    function updateCalendar(month, year) {
        var firstDay = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();

        jQuery(containerSelector + " .calendar-title").text("Tháng " + (month + 1) + " Năm " + year);
        var tbody = jQuery(containerSelector + " .calendar-body");
        tbody.empty();

        var date = 1;
        for (var i = 0; i < 6; i++) {
            var row = jQuery("<tr></tr>");
            for (var j = 0; j < 7; j++) {
                var cell = jQuery("<td></td>");
                if (i === 0 && j < firstDay) {
                    cell.html("");
                } else if (date > daysInMonth) {
                    cell.html("");
                } else {
                    var fullDate = year + "-" + String(month + 1).padStart(2, "0") + "-" + String(date).padStart(2, "0");
                    var content = `<div class="date-number">${date}</div>`;

                    if (typeof dataByDate[fullDate] !== "undefined") {
                        // Kiểm tra nếu date_train đã diễn ra chưa
                        let dateTrain = dataByDate[fullDate].date_train; // dạng YYYY-MM-DD
                        let now = new Date();
                        let trainDate = new Date(dateTrain + 'T23:59:59'); // so sánh đến hết ngày đó
                        let isPast = trainDate < now;
                        // Có thể dùng biến isPast để hiển thị trạng thái hoặc xử lý logic
                        // Ví dụ: thêm class 'event-past' nếu đã diễn ra
                        let pastClass = isPast ? 'event-past' : '';
                        content += `<div 
                            class="event-status status-${dataByDate[fullDate].status} ${pastClass}"
                            data-id="${dataByDate[fullDate].id}"
                            data-location="${dataByDate[fullDate].location_train}"
                            data-time="${dataByDate[fullDate].time_train}"
                            data-number_member="${dataByDate[fullDate].number_member}"
                            data-price_train="${dataByDate[fullDate].price_train}"
                            data-desc="${dataByDate[fullDate].desc}"
                            data-title="${dataByDate[fullDate].content}"
                            data-is_logged_in="${dataByDate[fullDate].is_logged_in}"
                            data-is_past="${isPast}"
                        >${dataByDate[fullDate].status_label}${isPast ? ' <span class="event-past-label">(Đã diễn ra)</span>' : ''}</div>`;
                        content += `<div class="event-content">${dataByDate[fullDate].content}</div>`;
                        cell.addClass("has-event");
                    }

                    cell.html(content);
                    date++;
                }
                row.append(cell);
            }
            tbody.append(row);
        }

        jQuery('td.has-event').on('click', function() {
            // Tạo popup HTML
            let hasRegistered = jQuery(this).find('.event-status').hasClass('status-registered');
            let isPast = jQuery(this).find('.event-status').hasClass('event-past');
            let id = jQuery(this).find('.event-status').data('id');
            let location = jQuery(this).find('.event-status').data('location');
            let time = jQuery(this).find('.event-status').data('time');
            let number_member = jQuery(this).find('.event-status').data('number_member');
            let price_train = jQuery(this).find('.event-status').data('price_train');
            let desc = jQuery(this).find('.event-status').data('desc');
            let title = jQuery(this).find('.event-status').data('title');
            let is_logged_in = jQuery(this).find('.event-status').data('is_logged_in');
            var popupHtml = `
            <div id="calendar-popup-overlay">
                <div id="calendar-popup">
                    <div>
                        <img src="/wp-content/uploads/2025/10/o0NJ0EiSGmrE85bp7uCdVprQcE0.avif" alt="Event Image">
                    </div>
                    <div>
                        <h3>${title}</h3>
                        <div>${desc}</div>
                        <hr>
                        <div>
                        <div><b>Địa điểm:</b> ${location}</div>
                        <div><b>Thời gian:</b> ${time}</div>
                        <div><b>Số lượng:</b> <b>${number_member}</b></div>
                        <div><b>Học phí:</b> ${price_train}</div>
                        </div>
                        ${is_logged_in ? `<button ${hasRegistered || isPast ? 'disabled' : ''} data-id="${id}" id="calendar-popup-register" class="button primary">${hasRegistered ? 'Bạn đã đăng ký' : 'Nộp đơn đăng ký'}</button>` : ''}
                    </div>
                </div>
            </div>
            `;
            // Xóa popup cũ nếu có
            jQuery('#calendar-popup-overlay').remove();
            // Thêm popup vào body
            jQuery('body').append(popupHtml);

            // Đóng popup
            jQuery('#calendar-popup-overlay').on('click', function(e){
            if (e.target.id === 'calendar-popup-close' || e.target.id === 'calendar-popup-overlay') {
                jQuery('#calendar-popup-overlay').remove();
            }
            });

            // Xử lý nút đăng ký (có thể mở form hoặc chuyển trang)
            jQuery('#calendar-popup-register').on('click', function(e){
                // alert('Bạn đã chọn nộp đơn đăng ký!');
                const eventId = jQuery(this).data('id');
                jQuery.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kam_register_event',
                        event_id: eventId
                    },
                    success: function(response) {
                        alert('Đăng ký thành công!');
                        jQuery(`#my-calendar .event-status[data-id="${eventId}"]`).text('Đã đăng ký');
                        jQuery(`#my-calendar .event-status[data-id="${eventId}"]`).removeClass('status-limited').addClass('status-registered');
                        jQuery('#calendar-popup-overlay').remove();
                    },
                    error: function() {
                        alert('Có lỗi xảy ra, vui lòng thử lại.');
                    }
                });
            });
        });
    }

    jQuery(containerSelector + " .calendar-prev").on("click", function () {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar(currentMonth, currentYear);
    });

    jQuery(containerSelector + " .calendar-next").on("click", function () {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar(currentMonth, currentYear);
    });

    updateCalendar(currentMonth, currentYear);
}

function renderCalendarDropdown(containerSelector) {
    var today = new Date();
    var currentMonth = today.getMonth();
    var currentYear = today.getFullYear();

    function updateDropdown(month, year) {
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        
        jQuery(containerSelector + " .calendar-title").text("Tháng " + (month + 1) + " Năm " + year);
        var dropdown = jQuery(containerSelector + " .calendar-body");
        dropdown.empty();

        var dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];

        for (var date = 1; date <= daysInMonth; date++) {
            var fullDate = year + "-" + String(month + 1).padStart(2, "0") + "-" + String(date).padStart(2, "0");
            
            // Chỉ hiển thị những ngày có dữ liệu
            if (typeof dataByDate[fullDate] !== "undefined") {
                var currentDate = new Date(year, month, date);
                var dayOfWeek = dayNames[currentDate.getDay()];
                var formattedDate = dayOfWeek + ", " + String(date).padStart(2, "0") + "." + String(month + 1).padStart(2, "0") + "." + year;
                
                // Kiểm tra nếu date_train đã diễn ra chưa
                let dateTrain = dataByDate[fullDate].date_train; // dạng YYYY-MM-DD
                let now = new Date();
                let trainDate = new Date(dateTrain + 'T23:59:59'); // so sánh đến hết ngày đó
                let isPast = trainDate < now;
                // Có thể dùng biến isPast để hiển thị trạng thái hoặc xử lý logic
                // Ví dụ: thêm class 'event-past' nếu đã diễn ra
                let pastClass = isPast ? 'event-past' : '';

                var item = jQuery(`<div 
                    class="dropdown-item"
                    data-id="${dataByDate[fullDate].id}"
                    data-location="${dataByDate[fullDate].location_train}"
                    data-time="${dataByDate[fullDate].time_train}"
                    data-number_member="${dataByDate[fullDate].number_member}"
                    data-price_train="${dataByDate[fullDate].price_train}"
                    data-desc="${dataByDate[fullDate].desc}"
                    data-title="${dataByDate[fullDate].content}"
                    data-is_logged_in="${dataByDate[fullDate].is_logged_in}"
                    data-is_past="${isPast}"
                ></div>`);
                var itemHeader = jQuery('<div class="dropdown-item-header"></div>');
                item.append(itemHeader);
                var contentHeader = `<span class="date-number">${formattedDate}</span>`;
                contentHeader += `<span class="event-status status-${dataByDate[fullDate].status}">${dataByDate[fullDate].status_label}</span>`;
                contentHeader += '<i class="dropdown-icon"></i>';
                var contentBody = `<div class="event-content ${pastClass}" style="display: none;"><p><span class="event-status status-${dataByDate[fullDate].status}">${dataByDate[fullDate].status_label}</span>${dataByDate[fullDate].time_train}</p><p>${dataByDate[fullDate].content}</p></div>`;

                itemHeader.html(contentHeader);
                item.append(contentBody);
                dropdown.append(item);
            }
        }

        // Mobile
        jQuery('#my-calendar-mobile .event-content').on('click', function() {
            // Tạo popup HTML
            const parent = jQuery(this).parent();
            let hasRegistered = jQuery(this).find('.event-status').hasClass('status-registered');
            let isPast = jQuery(this).hasClass('event-past');
            let id = jQuery(parent).data('id');
            let location = jQuery(parent).data('location');
            let time = jQuery(parent).data('time');
            let number_member = jQuery(parent).data('number_member');
            let price_train = jQuery(parent).data('price_train');
            let desc = jQuery(parent).data('desc');
            let title = jQuery(parent).data('title');
            let is_logged_in = jQuery(parent).data('is_logged_in');
            var popupHtml = `
            <div id="calendar-popup-overlay">
                <div id="calendar-popup">
                    <div>
                        <img src="/wp-content/uploads/2025/10/o0NJ0EiSGmrE85bp7uCdVprQcE0.avif" alt="Event Image">
                    </div>
                    <div>
                        <h3>${title}</h3>
                        <div>${desc}</div>
                        <hr>
                        <div>
                        <div><b>Địa điểm:</b> ${location}</div>
                        <div><b>Thời gian:</b> ${time}</div>
                        <div><b>Số lượng:</b> <b>${number_member}</b></div>
                        <div><b>Học phí:</b> ${price_train}</div>
                        </div>
                        ${is_logged_in ? `<button ${hasRegistered || isPast ? 'disabled' : ''} data-id="${id}" id="calendar-popup-register" class="button primary">${hasRegistered ? 'Bạn đã đăng ký' : 'Nộp đơn đăng ký'}</button>` : ''}
                    </div>
                </div>
            </div>
            `;
            // Xóa popup cũ nếu có
            jQuery('#calendar-popup-overlay').remove();
            // Thêm popup vào body
            jQuery('body').append(popupHtml);

            // Đóng popup
            jQuery('#calendar-popup-overlay').on('click', function(e){
            if (e.target.id === 'calendar-popup-close' || e.target.id === 'calendar-popup-overlay') {
                jQuery('#calendar-popup-overlay').remove();
            }
            });

            // Xử lý nút đăng ký (có thể mở form hoặc chuyển trang)
            jQuery('#calendar-popup-register').on('click', function(e){
                // alert('Bạn đã chọn nộp đơn đăng ký!');
                const eventId = jQuery(this).data('id');
                jQuery.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kam_register_event',
                        event_id: eventId
                    },
                    success: function(response) {
                        alert('Đăng ký thành công!');
                        jQuery(parent).find('.event-status').text('Đã đăng ký');
                        jQuery(parent).find('.event-status').removeClass('status-limited').addClass('status-registered');
                        jQuery('#calendar-popup-overlay').remove();
                    },
                    error: function() {
                        alert('Có lỗi xảy ra, vui lòng thử lại.');
                    }
                });
            });
        });
    }

    jQuery(containerSelector + " .calendar-prev").on("click", function () {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateDropdown(currentMonth, currentYear);
    });

    jQuery(containerSelector + " .calendar-next").on("click", function () {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateDropdown(currentMonth, currentYear);
    });

    updateDropdown(currentMonth, currentYear);
    
    // Thêm click event handler cho dropdown items
    jQuery(containerSelector).on('click', '.dropdown-item-header', function() {
        jQuery(this).parent().toggleClass('active');
        jQuery(this).parent().find('.event-content').slideToggle();
    });
}

// jQuery(document).ready(function($){
//     $('#custom-register-form').on('submit', function(e){
//         e.preventDefault();
        
//         var form = $(this);
//         var submitBtn = form.find('button[type="submit"]');
//         var originalText = submitBtn.text();
        
//         // Disable submit button
//         submitBtn.prop('disabled', true).text('Đang kiểm tra...');
        
//         // Clear previous errors
//         $('.error-message').remove();
        
//         $.ajax({
//             url: ajax_object.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'validate_register_form',
//                 username: form.find('input[name="username"]').val(),
//                 email: form.find('input[name="email"]').val(),
//                 ref_code: form.find('input[name="ref_code"]').val()
//             },
//             success: function(response){
//                 if(response.success){
//                     // No errors, submit form
//                     form.off('submit').submit();
//                 } else {
//                     // Show errors
//                     var errorHtml = '<div class="error-message" style="color:red;margin:10px 0;">';
//                     $.each(response.data.errors, function(index, error){
//                         errorHtml += '<p>' + error + '</p>';
//                     });
//                     errorHtml += '</div>';
//                     form.append(errorHtml);
//                     submitBtn.prop('disabled', false).text(originalText);
//                 }
//             },
//             error: function(){
//                 alert('Có lỗi xảy ra. Vui lòng thử lại.');
//                 submitBtn.prop('disabled', false).text(originalText);
//             }
//         });
//     });
// });

jQuery(document).ready(function ($) {
    if (window.innerWidth < 768) {
        jQuery('.header-search').click(function (e) {
            e.preventDefault();
            jQuery(this).addClass('current-dropdown');
        });
        // Đóng dropdown khi click ra ngoài
        jQuery(document).on('click', function(e) {
            if (!jQuery(e.target).closest('.header-search').length) {
                jQuery('.header-search').removeClass('current-dropdown');
            }
        });
    }

    // Đóng popup địa chỉ checkout khi click nút close
    $(document).on('click', '.kam-address-popup-close', function(){
        $('#kam-address-checkout-popup').hide();
    });

    // Clear previous click events before adding new one
    $(document).on('click', '#main-menu.mobile-sidebar .menu-item-has-children:not(#menu-item-210) > a', function(e){
        e.preventDefault();
        console.log('click');
        e.stopPropagation();
        $(this).parent().toggleClass('active');
    });
});