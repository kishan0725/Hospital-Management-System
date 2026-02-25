
(function($) {

  var wow = new WOW({
    boxClass: 'wow',
    animateClass: 'animated',
    offset: 0,
    mobile: false
  });
  wow.init();


  $(window).scroll(function() {
    if ($(".navbar").offset().top > 50) {
      $(".navbar-fixed-top").addClass("top-nav-collapse");
      $(".top-area").addClass("top-padding");
      $(".navbar-brand").addClass("reduce");

      $(".navbar-custom ul.nav ul.dropdown-menu").css("margin-top", "11px");

    } else {
      $(".navbar-fixed-top").removeClass("top-nav-collapse");
      $(".top-area").removeClass("top-padding");
      $(".navbar-brand").removeClass("reduce");

      $(".navbar-custom ul.nav ul.dropdown-menu").css("margin-top", "16px");

    }
  });

	var navMain = $(".navbar-collapse"); 
	navMain.on("click", "a:not([data-toggle])", null, function () {
	   navMain.collapse('hide');
	});


  $(window).scroll(function() {
    if ($(this).scrollTop() > 100) {
      $('.scrollup').fadeIn();
    } else {
      $('.scrollup').fadeOut();
    }
  });
  $('.scrollup').click(function() {
    $("html, body").animate({
      scrollTop: 0
    }, 1000);
    return false;
  });


  $(function() {
    $('.navbar-nav li a').bind('click', function(event) {
      var $anchor = $(this);
      var nav = $($anchor.attr('href'));
      if (nav.length) {
        $('html, body').stop().animate({
          scrollTop: $($anchor.attr('href')).offset().top
        }, 1500, 'easeInOutExpo');

        event.preventDefault();
      }
    });
    $('.page-scroll a').bind('click', function(event) {
      var $anchor = $(this);
      $('html, body').stop().animate({
        scrollTop: $($anchor.attr('href')).offset().top
      }, 1500, 'easeInOutExpo');
      event.preventDefault();
    });
  });


  $('#owl-works').owlCarousel({
    items: 4,
    itemsDesktop: [1199, 5],
    itemsDesktopSmall: [980, 5],
    itemsTablet: [768, 5],
    itemsTabletSmall: [550, 2],
    itemsMobile: [480, 2],
  });


  $('.owl-carousel .item a').nivoLightbox({
    effect: 'fadeScale',
    theme: 'default',
    keyboardNav: true,
    clickOverlayToClose: true,
    onInit: function() {},
    beforeShowLightbox: function() {},
    afterShowLightbox: function(lightbox) {},
    beforeHideLightbox: function() {},
    afterHideLightbox: function() {},
    onPrev: function(element) {},
    onNext: function(element) {},
    errorMessage: 'The requested content cannot be loaded. Please try again later.'

  })(jQuery, window, document);


})(jQuery);
$(window).load(function() {
  $(".loader").delay(100).fadeOut();
  $("#page-loader").delay(100).fadeOut("fast");
});
