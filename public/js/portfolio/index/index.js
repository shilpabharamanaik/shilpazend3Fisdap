$(function(){
	// switching students
	$('#available-list').change(function(){
		// only update if a student is chosen
		if ($(this).val()) {
			blockUi(true);
			window.location = "/portfolio/index/about/studentId/" + $(this).val();
		}
	});
	
	// links to reports
	$(".launch-report-config").click(function(e){
		e.preventDefault();
		
		var reportType = $(this).attr("data-reporttype");
		var studentId = $(this).attr("data-studentid");
		var goalSetId = $(this).attr("data-goalsetid");
		
		$.post("/reports/index/create-report-config",
			{
				"reportType": reportType,
				"studentId": studentId,
				"goalSetId": goalSetId
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