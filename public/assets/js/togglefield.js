$(document).on("click", '.togglefield', function(e) {
	var	object = $( this );
	var obj = $( '#'+ object.attr('data-toggle'));
	var readonly = ! obj.attr('readonly');
	obj.attr('readonly', readonly);

});


