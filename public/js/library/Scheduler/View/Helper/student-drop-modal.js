$(function(){
    
    // turn the history modal div into a modal
    $("#studentDropDialog").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 600,
	title: "Drop student",
	open: function (){
	    $("#cancel-btn").blur();
        }
    });
   
});

function initStudentDropModal() {
    $("#cancel-btn").button();
    $("#do-drop-btn").button();
    
    $("#cancel-btn").click(function(e){
	e.preventDefault();
	$("#studentDropDialog").dialog('close');
    });
    
    $("#do-drop-btn").click(function(e){
	e.preventDefault();
			
	var assignmentId = $(this).attr('data-assignmentid');
	var cancelBtn = $('#cancel-btn').hide();
	var saveBtn = $('#do-drop-btn').hide();
	var throbber =  $("<img id='dropModalThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post("/scheduler/index/process-student-drop",
		{ "assignment_id" : assignmentId },
		function (response) {
			if (response) {
			    if ($("#cal-display-filters").length > 0) {
				loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
			    } else {
				window.location.reload(true);
			    }
			    $('#studentDropDialog').dialog('close');
			    cancelBtn.show();
			    saveBtn.show();
			    throbber.remove();
			} else {
				$("#main-signup-content").html("<div class='alert'>You are not authorized to drop students from this shift.</div>");
				cancelBtn.find(".ui-button-text").html("Ok");
				cancelBtn.show();
				$('#dropModalThrobber').remove();
			}
		}
	)
    });
}
   
