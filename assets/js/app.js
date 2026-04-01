


    //isotope

		///////////

		jQuery(document).ready(function($) {

			$(window).on('load', function(){ 
				//responsive
				$('.nicdark_menu').tinyNav({
					active: 'selected',
					header: 'MENU'
				});
			});

			$(".clickable-row").click(function() {
				window.location = $(this).data("href");
			});
		});

	///////////

