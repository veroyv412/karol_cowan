;
(function($) {
    "use strict";
    var nooGetViewport = function() {
        var e = window,
            a = 'inner';
        if (!('innerWidth' in window)) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        return {
            width: e[a + 'Width'],
            height: e[a + 'Height']
        };
    };
    var nooInit = function() {
        var isTouch = 'ontouchstart' in window;
        if (isTouch) {
            $(".carousel-inner").swipe({
                swipeLeft: function(event, direction, distance, duration, fingerCount) {
                    $(this).parent().carousel('prev');
                },
                swipeRight: function(event, direction, distance, duration, fingerCount) {
                    $(this).parent().carousel('next');
                },
                threshold: 0
            });
        }
        if ($('.navbar').length) {
            var $window = $(window);
            var $body = $('body');
            var navTop = $('.navbar').offset().top;
            var adminbarHeight = 0;
            if ($body.hasClass('admin-bar')) {
                adminbarHeight = $('#wpadminbar').outerHeight();
            }
            var lastScrollTop = 0,
                navHeight = 0,
                defaultnavHeight = 70;
            var navbarInit = function() {
                if (nooGetViewport().width > 992) {
                    var $this = $(window);
                    var $navbar = $('.navbar'),
                        navHeight = $navbar.outerHeight();
                    if ($navbar.hasClass('fixed-top')) {
                        var navFixedClass = 'navbar-fixed-top';
                        if ($navbar.hasClass('shrinkable') && !$body.hasClass('one-page-layout')) {
                            navFixedClass += ' navbar-shrink';
                        }
                        var checkingPoint = navTop;
                        if (($this.scrollTop() + adminbarHeight) >= checkingPoint) {
                            if (!$navbar.hasClass('navbar-fixed-top')) {
                                if ($body.hasClass('page-menu-transparent')) {
                                    $navbar.closest('.noo-header').css({
                                        'height': '1px'
                                    });
                                    $navbar.closest('.noo-header').css({
                                        'position': 'relative'
                                    });
                                } else {
                                    $('.navbar-wrapper').css({
                                        'min-height': navHeight + 'px'
                                    });
                                }
                                $navbar.addClass(navFixedClass);
                                $navbar.css('top', adminbarHeight);
                            }
                        } else {
                            if ($body.hasClass('page-menu-transparent')) {
                                $navbar.closest('.noo-header').css({
                                    'height': ''
                                });
                                $navbar.closest('.noo-header').css({
                                    'position': ''
                                });
                            } else {
                                $('.navbar-wrapper').css({
                                    'min-height': ''
                                });
                            }
                            $navbar.removeClass(navFixedClass);
                        }
                    }
                }
            };
            $window.bind('scroll', navbarInit).resize(navbarInit);
            if ($body.hasClass('one-page-layout')) {
                $('.navbar-scrollspy > .nav > li > a[href^="#"]').on("click", function(e){
                    e.preventDefault();
                    var target = $(this).attr('href').replace(/.*(?=#[^\s]+$)/, '');
                    if (target && ($(target).length)) {
                        var position = Math.max(0, $(target).offset().top);
                        position = Math.max(0, position - (adminbarHeight + $('.navbar').outerHeight()) + 5);
                        $('html, body').animate({
                            scrollTop: position
                        }, {
                            duration: 800,
                            easing: 'easeInOutCubic',
                            complete: window.reflow
                        });
                    }
                });
                $body.scrollspy({
                    target: '.navbar-scrollspy',
                    offset: (adminbarHeight + $('.navbar').outerHeight())
                });
                $(window).resize(function() {
                    $body.scrollspy('refresh');
                });
            }
        }
        $('.noo-slider-revolution-container .noo-slider-scroll-bottom').on("click", function(e) {
            e.preventDefault();
            var sliderHeight = $('.noo-slider-revolution-container').outerHeight();
            $('html, body').animate({
                scrollTop: sliderHeight
            }, 900, 'easeInOutExpo');
        });
        $('body').on('mouseleave ', '.masonry-style-elevated .masonry-portfolio.no-gap .masonry-item', function() {
            $(this).closest('.masonry-container').find('.masonry-overlay').hide();
            $(this).removeClass('masonry-item-hover');
        });
        $('.masonry').each(function() {
            var self = $(this);
            var $container = $(this).find('.masonry-container');
            var $filter = $(this).find('.masonry-filters a');
            var masonry_options = {
                'gutter': 0
            };
            $container.isotope({
                itemSelector: '.masonry-item',
                transitionDuration: '0.8s',
                masonry: masonry_options
            });
            imagesLoaded(self, function() {
                $container.isotope('layout');
            });
            $(window).resize(function() {
                $container.isotope('layout');
            });
            $filter.on("click", function(e) {
                e.stopPropagation();
                e.preventDefault();
                var $this = jQuery(this);
                if ($this.hasClass('selected')) {
                    return false;
                }
                self.find('.masonry-result h3').text($this.text());
                var filters = $this.closest('ul');
                filters.find('.selected').removeClass('selected');
                $this.addClass('selected');
                var options = {
                        layoutMode: 'masonry',
                        transitionDuration: '0.8s',
                        'masonry': {
                            'gutter': 0
                        }
                    },
                    key = filters.attr('data-option-key'),
                    value = $this.attr('data-option-value');
                value = value === 'false' ? false : value;
                options[key] = value;
                $container.isotope(options);
            });
        });
        $(window).scroll(function() {
            if ($(this).scrollTop() > 500) {
                $('.go-to-top').addClass('on');
            } else {
                $('.go-to-top').removeClass('on');
            }
        });
        $('body').on('click', '.go-to-top', function() {
            $("html, body").animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        $('body').on('click', '.search-button', function() {
            if ($('.searchbar').hasClass('hide')) {
                $('.searchbar').removeClass('hide').addClass('show');
                $('.searchbar #s').focus();
            }
            return false;
        });
        $('body').on('mousedown', $.proxy(function(e) {
            var element = $(e.target);
            if (!element.is('.searchbar') && element.parents('.searchbar').length === 0) {
                $('.searchbar').removeClass('show').addClass('hide');
            }
        }, this));
		
		//Featured Album
		if($('.noo-featured-albums').length > 0) {
			$('.noo-featured-albums').owlCarousel({
				items : 5,
				itemsCustom : false,
				itemsDesktop : [1320, 4],
				itemsDesktopSmall : [1200, 3],
				itemsTablet : [768, 2],
				itemsTabletSmall : false,
				itemsMobile : [479, 1],
				slideSpeed:500,
				paginationSpeed:800,
				rewindSpeed:1000,
				autoHeight: false,
				addClassActive: true,
				autoPlay: false,
				loop:true,
				pagination: true
			});
		}
    };
    $(document).ready(function() {
        nooInit();
        $('#loading').fadeOut(300);
    });
    /*$(document).bind('noo-layout-changed', function() {
        nooInit();
    });*/
})(jQuery);