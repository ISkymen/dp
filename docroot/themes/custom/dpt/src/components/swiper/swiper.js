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