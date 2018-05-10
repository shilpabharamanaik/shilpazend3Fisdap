$(function(){
	// links to reports
	$(".launch-report-config").click(function(e){
		e.preventDefault();
		$(this).append("<img class='reportLinkThrobber' src='/images/throbber_small.gif'>");
		
		var reportType = $(this).attr("data-reporttype");
		var studentId = $(this).attr("data-studentid");
		
		$.post("/reports/index/create-report-config",
			{
				"reportType": reportType,
				"studentId": studentId,
			},
			function (response) {
				if (response > 0) {
					var url = "/reports/index/display/report/"+reportType+"/config/"+response;
					window.location = url;
				} else {
					location.reload();
				}
			}
		)
		
	});
});