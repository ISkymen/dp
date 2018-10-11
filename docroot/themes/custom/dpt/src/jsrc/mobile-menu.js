;(function ($, Drupal) {
  Drupal.behaviors.mobileMenu = {
    attach: function (context, settings) {
      var hamburger = $('.js-s-amenu__toggle');
      hamburger.click(function () {
        $('body').toggleClass('js-amenu');
      });
    }
  };
})(jQuery, Drupal);