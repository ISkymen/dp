;(function ($, Drupal, window) {
    Drupal.behaviors.scrollTop = {
        attach: function (context, settings) {

            var toTop = $('#return-to-top');

            toTop.click(function() {
                $('body,html').animate({
                    scrollTop : 0
                }, 500);
            });

            $(window).scroll(function() {
                if ($(window).scrollTop() >= 500) {
                    toTop.fadeIn(200);
                } else {
                    toTop.fadeOut(200);
                }
            });
        }
    };
})(jQuery, Drupal, this);