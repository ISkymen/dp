(function ($, Drupal) {

    'use strict';

Drupal.behaviors.caption_image = {
    attach: function (context, settings) {

        $('.s-photo img', context).once().each(function() {
            var title = $(this).attr('title');
            if (title != null) {
                $(this).after( "<div class='s-photo__caption'>" + title + "</div>");
            }
            else {
                // console.log(1);
            }
        });
    }
};

})(jQuery, Drupal);