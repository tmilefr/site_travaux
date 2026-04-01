//add row
$(document).on("click", '.addRow', function(e) {
  var	object = $( this );
  console.log(object);
  var html = $('#model'+object.attr('ref')).html();
  $('#DR_'+object.attr('ref')).append(html); 
});

// remove row
$(document).on('click', '.removeRow', function () {
  $(this).closest('.input-group').remove();
});