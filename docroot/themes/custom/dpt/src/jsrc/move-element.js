(function ($, Drupal, window, document) {

    'use strict';

Drupal.behaviors.move_element = {
    attach: function (context, settings) {
        if ($(window).width() < 960) {
          $('.s-menu--footer').insertAfter('.s-menu--main');
        }
        else {
          $('.s-menu--footer').insertAfter('.s-footer .s-site');
        }

      $(window).resize(function () {
        if ($(window).width() < 943) {
          $('.s-menu--footer').insertAfter('.s-menu--main');
        }
        else {
          $('.s-menu--footer').insertAfter('.s-footer .s-site');
        }
      })
    }
};

})(jQuery, Drupal, this, this.document);