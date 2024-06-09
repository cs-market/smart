(function (_, $) {
    $.ceEvent('on', 'ce.commoninit', function (context) {
        $(".scroll-top__button").bind('click', function () {
            $('body,html').animate({scrollTop: 0}, 400);
        });
    });
}(Tygh, Tygh.$));