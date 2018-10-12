/**
 * @file
 * si_stat functionality.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  $(document).ready(function () {
    $.ajax({
      type: 'POST',
      cache: false,
      url: drupalSettings.si_stat.url,
      data: drupalSettings.si_stat.data
    });
  });
})(jQuery, Drupal, drupalSettings);
