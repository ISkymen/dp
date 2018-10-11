(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dpMistakeMain = {
    attach: function (context, settings) {
      $('.s-photo .field__label', context).once('.s-photo .field__label').each(function () {
        $(this).css('background', drupalSettings.dp_mistake.color);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);