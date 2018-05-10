function initEventDeleteModal() {
    $("#delete-event-cancel-btn").button().blur();
    $("#delete-event-btn").button();
    $(".data-students").css({"margin-top": "1.5em"});
	
    if ($("#event_count").val() > 1) {
		$("#eventDeleteDialog").dialog("option", 'title', 'Delete shifts');
    }
    
    $("#delete-event-cancel-btn").click(function(e){
	e.preventDefault();
	$("#eventDeleteDialog").dialog('close');
	
	//console.log("init delete modal");
	
});
    
    $("#delete-event-btn").click(function(e){
	e.preventDefault();
	
	// BUSY ROBOT!
	if ($("#event_count").val() > 20 || $("#student_count").val() > 10) {
		busyRobot();
	}
			
	var postValues = $('#eventDeleteDialog form').serialize();
	var cancelBtn = $('#delete-event-cancel-btn').hide();
	var saveBtn = $('#delete-event-btn').hide();
	var throbber =  $("<img id='deleteModalThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post("/scheduler/index/process-event-delete",
		postValues,
		function (response) {
			if (response) {
			    if ($("#cal-display-filters").length > 0) {
					
					loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
					closeBusyRobot();
				
			    } else {
					window.location.reload(true);
					closeBusyRobot();
			    }
				
				$('#eventDeleteDialog').dialog('close');
			    cancelBtn.show();
			    saveBtn.show();
			    throbber.remove();
			} else {
				
				$("#main-delete-content").html("<div class='alert'>You are not authorized to delete this shift.</div>");
				cancelBtn.find(".ui-button-text").html("Ok");
				cancelBtn.show();
				$('#deleteModalThrobber').remove();
				closeBusyRobot();
				
			}
		}
	).fail(function(){ brokenRobot(); });
    });
}
   
