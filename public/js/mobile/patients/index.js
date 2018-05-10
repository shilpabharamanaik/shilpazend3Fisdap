$(function() {
	$(".skill-summary-buttons").hide();
	$(".skill-summary").addClass("clickable").click(function (e) {
		$(this).prev().toggle("blind", {}, 500);
		$(this).next().toggle("blind", {}, 500);
	});

	$(".skill-summary-buttons a:not('.delete-skill')").css("font-size", "11pt").button();
	$(".new-skill").css("font-size", "11pt").button().parent().addClass('orange-button medium');
	$(".delete-skill").click(function(e) {
		var targetUrl = $(this).attr("href");
		e.preventDefault();
		$("<div>Are you sure you want to delete this?</div>").dialog({
			modal: true,
			resizeable: false,
			width: "90%",
			buttons: {
				"No": function() {
					$( this ).dialog( "close" );
				},
				"Yes": function() {
					window.location.href = targetUrl;
				}
			}
		});
	});
});
