$(function(){
	//Init the picklist mode on page load
	toggleSummaryDetailed($("input[name='reportType']:checked"));
	
	// when the report is in detailed mode, swap the student picklist
	$("input[name='reportType']").change(function(e){
		toggleSummaryDetailed($(this));
	});
	
	function toggleSummaryDetailed(radioButton) {
		if ($(radioButton).val() == 'summary') {
			togglePicklistMode("multiple");
			$(".student-picklist").text("Select one or more student(s)");
		} else if ($(radioButton).val() == 'detailed') {
			togglePicklistMode("single");
			$(".student-picklist").text("Select one student");
		}
	}

	$("#report-content").on("click", ".eureka-trigger", function(e){
		initEurekaModal();
		e.preventDefault();
		blockUi(true);
		$.post(
			'/skills-tracker/settings/get-eureka-data',
			{ defId:  $(this).attr("data-defId"),
			  studentId: $(this).attr("data-studentId"),
			  startDate: $(this).attr("data-startDate"),
			  endDate: $(this).attr("data-endDate")},
			function(response){
				$("#eureka-modal-content").empty().append("<div id='eureka-home'></div>");
				$("#eureka-modal").dialog("open");
				$("#eureka-modal-content").append($(response).eurekaGraph());
				blockUi(false);
			}
		);
	});

});
