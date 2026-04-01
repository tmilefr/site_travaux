var theHREF;
$(".confirmModalLink").click(function(e) {
	e.preventDefault();
	theHREF = $(this).attr("href");
	$("#confirmModal").modal("show");
});

$("#confirmModalYes").click(function(e) {
	window.location.href = theHREF;
});
