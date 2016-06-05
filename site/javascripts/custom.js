//Filterable Portofolio
 $(function(){
      
      var $container = $('#portofolio');

      $container.isotope({
        itemSelector : '.category'
      });
      
      
      var $optionSets = $('#options .option-set'),
          $optionLinks = $optionSets.find('a');

      $optionLinks.click(function(){
        var $this = $(this);
        // don't proceed if already selected
        if ( $this.hasClass('selected') ) {
          return false;
        }
        var $optionSet = $this.parents('.option-set');
        $optionSet.find('.selected').removeClass('selected');
        $this.addClass('selected');
  
        // make option object dynamically, i.e. { filter: '.my-filter-class' }
        var options = {},
            key = $optionSet.attr('data-option-key'),
            value = $this.attr('data-option-value');
        // parse 'false' as false boolean
        value = value === 'false' ? false : value;
        options[ key ] = value;
        if ( key === 'layoutMode' && typeof changeLayoutMode === 'function' ) {
          // changes in layout modes need extra logic
          changeLayoutMode( $this, options )
        } else {
          // otherwise, apply new options
          $container.isotope( options );
        }
        
        return false;
      });

      
    });


//Pretty Photo
$(document).ready(function () {
    var viewportWidth = $('body').innerWidth();
    try {       
        /*$("a[data-gal^='prettyPhoto']").prettyPhoto(
            {   animation_speed: 'normal',
                theme: 'dark_rounded',
                slideshow: 3000,
                autoplay_slideshow: false,
                social_tools: false,
                changepicturecallback: function(){
                    // 1024px is presumed here to be the widest mobile device. Adjust at will.
                    if (viewportWidth < 1025) {
                        $(".pp_pic_holder.pp_default").css("top",window.pageYOffset+"px");
                    }
                }
            });*/
    }
    catch (e)
    { }

    try {
        $('.portfolio_showcase').cycle({
            fx: 'fade',
            speed: 'slow',
            timeout: 4000,
            pager: '#number',
            pause: 1
        });
     }
    catch (e)
    { }

});