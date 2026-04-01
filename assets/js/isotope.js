    //isotope
	$(window).on('load', function(){ 
		
		$('.nicdark_masonry_btns a').click( function() {
			var filterValue = $( this ).attr('data-filter');
			$container.isotope({ filter: filterValue });
		});
		  
		var $container = $('.nicdark_masonry_container').isotope({
			itemSelector: '.nicdark_masonry_item'
		});

		$( '.nicdark_simulate_click' ).trigger( "click" );
	
	});
	///////////