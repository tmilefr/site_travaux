	$(window).on('load', function(){ 
		
        //counter
        $('.nicdark_counter').data('countToOptions', {
            formatter: function (value, options) {
                return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
            }
        });
        // start all the timers
        $('.nicdark_counter').bind('inview', function(event, visible) {
            if (visible == true) {
                $('.nicdark_counter').each(count);
            } 
        });
        function count(options) {
            var $this = $(this);
            options = $.extend({}, options || {}, $this.data('countToOptions') || {});
            $this.countTo(options);
        }
        ///////////
	
	});

