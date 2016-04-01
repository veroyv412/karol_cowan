(function( $ ) {
    $.fn.photoswipe = function(options){
        var galleries = [],
            _options = options;

        var init = function($this){
            galleries = [];
            $this.each(function(i, gallery){
                galleries.push({
                    id: i,
                    items: []
                });

                $(gallery).find('a').each(function(k, link) {
                    var $link = $(link);
                    if ( $link.data('size') ){
                        var size = $link.data('size').split('x');
                        if (size.length != 2){
                            throw SyntaxError("Missing data-size attribute.");
                        }
                        $link.data('gallery-id',i+1);
                        $link.data('photo-id', k);

                        var item = {
                            src: link.href,
                            msrc: link.children[0].getAttribute('src'),
                            w: parseInt(size[0],10),
                            h: parseInt(size[1],10),
                            title: $link.data('title'),
                            el: link,
                            originalImage: {
                                src: link.href,
                                w: $link.data('width'),
                                h: $link.data('height')
                            },
                            mediumImage: {
                                src: link.href,
                                w: $link.data('width'),
                                h: $link.data('height')
                            }
                        }

                        galleries[i].items.push(item);
                    }

                });

                $(gallery).on('click', 'a', function(e){
                    e.preventDefault();
                    var gid = $(this).data('gallery-id'),
                        pid = $(this).data('photo-id');
                    openGallery(gid,pid);
                });
            });
        }

        var parseHash = function() {
            var hash = window.location.hash.substring(1),
                params = {};

            if(hash.length < 5) {
                return params;
            }

            var vars = hash.split('&');
            for (var i = 0; i < vars.length; i++) {
                if(!vars[i]) {
                    continue;
                }
                var pair = vars[i].split('=');
                if(pair.length < 2) {
                    continue;
                }
                params[pair[0]] = pair[1];
            }

            if(params.gid) {
                params.gid = parseInt(params.gid, 10);
            }

            if(!params.hasOwnProperty('pid')) {
                return params;
            }
            params.pid = parseInt(params.pid, 10);
            return params;
        };

        var openGallery = function(gid,pid){
            var pswpElement = document.querySelectorAll('.pswp')[0],
                items = galleries[gid-1].items,
                options = {
                    index: pid,
                    galleryUID: gid,
                    getThumbBoundsFn: function(index) {
                        var thumbnail = items[index].el.children[0],
                            pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                            rect = thumbnail.getBoundingClientRect();

                        return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
                    }
                };
            $.extend(options,_options);
            var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);

            // create variable that will store real size of viewport
            var realViewportWidth,
                useLargeImages = false,
                firstResize = true,
                imageSrcWillChange;

            // beforeResize event fires each time size of gallery viewport updates
            gallery.listen('beforeResize', function() {
                // gallery.viewportSize.x - width of PhotoSwipe viewport
                // gallery.viewportSize.y - height of PhotoSwipe viewport
                // window.devicePixelRatio - ratio between physical pixels and device independent pixels (Number)
                //                          1 (regular display), 2 (@2x, retina) ...


                // calculate real pixels when size changes
                realViewportWidth = gallery.viewportSize.x * window.devicePixelRatio;

                // Code below is needed if you want image to switch dynamically on window.resize

                // Find out if current images need to be changed
                if(useLargeImages && realViewportWidth < 1000) {
                    useLargeImages = false;
                    imageSrcWillChange = true;
                } else if(!useLargeImages && realViewportWidth >= 1000) {
                    useLargeImages = true;
                    imageSrcWillChange = true;
                }

                // Invalidate items only when source is changed and when it's not the first update
                if(imageSrcWillChange && !firstResize) {
                    // invalidateCurrItems sets a flag on slides that are in DOM,
                    // which will force update of content (image) on window.resize.
                    gallery.invalidateCurrItems();
                }

                if(firstResize) {
                    firstResize = false;
                }

                imageSrcWillChange = false;

            });


            // gettingData event fires each time PhotoSwipe retrieves image source & size
            gallery.listen('gettingData', function(index, item) {
                // Set image source & size based on real viewport width
                if( useLargeImages ) {
                    item.src = item.originalImage.src;
                    item.w = item.originalImage.w;
                    item.h = item.originalImage.h;
                } else {
                    item.src = item.mediumImage.src;
                    item.w = item.mediumImage.w;
                    item.h = item.mediumImage.h;
                }

                // It doesn't really matter what will you do here,
                // as long as item.src, item.w and item.h have valid values.
                //
                // Just avoid http requests in this listener, as it fires quite often

            });

            gallery.init();
        }

        // initialize
        init(this);
        $(this.selector).data({'photoswipe': this});

        // Parse URL and open gallery if it contains #&pid=3&gid=1
        var hashData = parseHash();
        if(hashData.pid > 0 && hashData.gid > 0) {
            openGallery(hashData.gid,hashData.pid);
        }

        return this;
    };
}( jQuery ));