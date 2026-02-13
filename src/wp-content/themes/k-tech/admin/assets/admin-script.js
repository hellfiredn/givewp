
jQuery(document).ready(function ($) {
    $('.nocodevnRedirectAdmin').on('click', function () {
        var redirect = $(this).data('redirect');
        window.location.href = redirect;
    });

    $('.nocodevnRedirectAdmin').hover(
        function () {
            var redirect = $(this).data('redirect');
            var tag_a = $(this).closest('a').attr('href', redirect);
            $(this).addClass('hovered');
        },
        function () {
            var tag_a = $(this).closest('a').attr('href', 'javascript:;');
            $(this).removeClass('hovered');
        }
    );
});
