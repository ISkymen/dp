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
;(function ($, Drupal) {
  Drupal.behaviors.swiper = {
    attach: function (context, settings) {
      if ( $('.swiper-container').length ) {
        var mySwiper = new Swiper ('.swiper-container', {
          // Optional parameters
          loop: true,
          autoplay: {
            delay: 5000,
          },
          // Navigation arrows
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          // If we need pagination
          pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true,
          },
          effect: 'slide', // Could be "slide", "fade", "cube", "coverflow" or "flip"
          preventInteractionOnTransition: false,
        })
      }
    }
  };
})(jQuery, Drupal);
;(function ($, Drupal) {
  Drupal.behaviors.swiper = {
    attach: function (context, settings) {
      if ( $('.swiper-container').length ) {
        var mySwiper = new Swiper ('.swiper-container', {
          // Optional parameters
          loop: true,
          speed: 1000,
          autoplay: {
            delay: 5000,
          },
          // Navigation arrows
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          // If we need pagination
          pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true,
          },
          effect: 'slide', // Could be "slide", "fade", "cube", "coverflow" or "flip"
          preventInteractionOnTransition: false,
        });
        if($(".swiper-container .swiper-slide").length == 3) {
          $('.swiper-container').addClass( "disabled" );
        }
      }
    }
  };
})(jQuery, Drupal);
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
//# sourceMappingURL=scripts.js.map
