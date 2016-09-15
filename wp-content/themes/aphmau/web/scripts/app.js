jQuery(function() {
    jQuery(document).foundation();
    jQuery('.fancybox').fancybox();
    jQuery('.slick').slick({
        arrows: false,
        dots: true,
        lazyLoad: 'ondemand',
        autoplay: true,
        autoplaySpeed: 5000,
        speed: 1000
    });
});