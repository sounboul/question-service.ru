(function($) {
    "use strict";
    var $window = $(window);

    // получение GET параметра
    function getURLParameter(sParam)
    {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam)
            {
                return sParameterName[1];
            }
        }
    }

    /*------------------------------------
            01. Preloader
        --------------------------------------*/
    $('#preloader').fadeOut('normall', function() {
        $(this).remove();
    });

    /*------------------------------------
        02. Header class - on scroll
    --------------------------------------*/
    $window.on('scroll', function() {
        var scroll = $window.scrollTop();
        if (scroll <= 50) {
            $("header").removeClass("scrollHeader").addClass("fixedHeader");
        }
        else {
            $("header").removeClass("fixedHeader").addClass("scrollHeader");
        }
    });

    /*------------------------------------
        03. Scroll To Top
    --------------------------------------*/
    $window.on('scroll', function() {
        if ($(this).scrollTop() > 500) {
            $(".scroll-to-top").fadeIn(400);

        } else {
            $(".scroll-to-top").fadeOut(400);
        }
    });

    $(".scroll-to-top").on('click', function(event) {
        event.preventDefault();
        $("html, body").animate({
            scrollTop: 0
        }, 600);
    });

    /*------------------------------------
        05. Resize function
    --------------------------------------*/
    $window.resize(function(event) {
        setTimeout(function() {
            SetResizeContent();
        }, 500);
        event.preventDefault();
    });

    /*------------------------------------
        06. FullScreenHeight function
    --------------------------------------*/
    function fullScreenHeight() {
        var element = $(".full-screen");
        var $minheight = $window.height();
        element.css('min-height', $minheight);
    }

    /*------------------------------------
        07. ScreenFixedHeight function
    --------------------------------------*/
    function ScreenFixedHeight() {
        var $headerHeight = $("header").height();
        var element = $(".screen-height");
        var $screenheight = $window.height() - $headerHeight;
        element.css('height', $screenheight);
    }

    /*------------------------------------
        08. FullScreenHeight and screenHeight with resize function
    --------------------------------------*/
    function SetResizeContent() {
        fullScreenHeight();
        ScreenFixedHeight();
    }

    SetResizeContent();

    // Подсветка выбранной категории в sidebar
    if (env.request_pathInfo.includes('/category/')) {
        $('.categories-list [href="' + env.request_pathInfo + '"]').addClass('active');
    }

    // Установка категории в поисковой форме
    if (env.request_pathInfo === '/search/') {
        $('#categories-search-form option[value=' + getURLParameter('categoryId') + ']').prop('selected', true);
    }

    // Подгрузка листинга при нажатии кнопки "Показать еще"
    $('.container').on('click', 'a.load-listing', function(e){
        e.preventDefault();

        var listGrid = $(e.target).parents('.list-grid');
        listGrid.find('.load-listing-button').remove();

        $.get(e.target.href, function(data) {
            listGrid.append(data);
        });
    });
})(jQuery);
