$(document).on("click keypress", '.typeahead', function(e) {
	var	object = $( this );
	$.get( object.attr('data-source') , function(data){ 
		
		
		object.typeahead({
			source:		data ,
			autoSelect: true,
			displayText: function(item){ return item.label;},
			afterSelect: function(item){ 
				console.log(item.id);
				$('#'+object.attr('data-dst')).val(item.id);
			}
		});
			
	},'json');
});


/*$(".typeahead").each(function( index ) {
	var	object = $( this );
	$.get( object.attr('data-source') , function(data){ 
		
		
		object.typeahead({
			source:		data ,
			autoSelect: true,
			displayText: function(item){ return item.label;},
			afterSelect: function(item){ 
				$('#'+object.attr('data-dst')).val(item.id);
			}
		});
			
	},'json');

});*/
