jQuery(function() {
    jQuery(document).foundation();
    jQuery('.fancybox').fancybox();
    jQuery('.fancybox-media').fancybox({
        openEffect  : 'none',
        closeEffect : 'none',
        helpers : {
            media : {},
            overlay: {
                locked: false
            }
        }
    });
    jQuery('.slick').slick({
        arrows: false,
        dots: true,
        lazyLoad: 'ondemand',
        autoplay: true,
        autoplaySpeed: 5000,
        speed: 1000
    });

    var masonry = jQuery('.videos .masonry');
    masonry.isotope({
        itemSelector: '.brick',
        layoutMode: 'fitRows'
    });
    jQuery('.videos .button-group a').on('click', function(){
        var filter;
        if(jQuery(this).attr('data-filter') == "*") {
            filter = "*";
        } else {
            filter = '.category-' + jQuery(this).attr('data-filter');
        }
        masonry.isotope({ filter: filter });
    })
});