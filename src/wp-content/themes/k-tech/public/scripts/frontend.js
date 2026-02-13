
document.addEventListener(
    "wpcf7mailsent",
    function (event) {
        Swal.fire({
            title: "Xin cảm ơn!",
            text: "Form đã được gửi thành công.",
            icon: "success",
            confirmButtonText: "OK",
        });

        if (event.target.id === 'wpcf7-f27cb117-p1-o1') {
            var popup = document.getElementById('popup-kh');
            if (popup) {
                popup.style.display = 'none';
            }
        }
    },
    false
);

document.addEventListener(
    "wpcf7invalid",
    function (event) {
        Swal.fire({
            title: "Có lỗi xảy ra!",
            text: "Vui lòng kiểm tra lại thông tin đã nhập.",
            icon: "error",
            confirmButtonText: "OK",
        });
    },
    false
);

document.addEventListener(
    "wpcf7mailfailed",
    function (event) {
        Swal.fire({
            title: "Có lỗi xảy ra!",
            text: "Không thể gửi email. Vui lòng thử lại sau.",
            icon: "error",
            confirmButtonText: "OK",
        });
    },
    false
);




/*Thông báo Header*/

document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.text-swiper-container', {
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 2000,
        },
        speed: 1000,
    });
});


/*Bảng xếp hạng Master*/
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.bxh-tab');
    const contents = document.querySelectorAll('.bxh-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            if (this.dataset.tab == 'tong') {
                document.querySelector('.bxh-controls').style.display = 'none';
            } else {
                document.querySelector('.bxh-controls').style.display = '';
            }

            this.classList.add('active');
            document.querySelector('#tab-' + this.dataset.tab).classList.add('active');
        });
    });
});




document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.descSwiper', {
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
        
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});
