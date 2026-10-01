$(".checkall").change(function(e) {
	$("."+$(this).data("target")).prop('checked', this.checked);
});
