$(function(){
    
    // turn the history modal div into a modal
    $("#studentSignupDialog").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 600,
	title: "Shift signup"
    });
   
});

function initStudentSignupModal() {
    $("#signup-cancel-btn").button().blur();
    $("#signup-btn").button();
    
    $("#signup-cancel-btn").click(function(e){
	e.preventDefault();
	$("#studentSignupDialog").dialog('close');
    });
    
    $("#signup-btn").click(function(e){
	e.preventDefault();
			
	var eventId = $(this).attr('data-eventid');
	var cancelBtn = $('#signup-cancel-btn').hide();
	var saveBtn = $('#signup-btn').hide();
	var throbber =  $("<img id='signupModalThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post("/scheduler/index/process-student-signup",
		{ "event_id" : eventId },
		function (response) {
			if (response) {
				if (response == "full") {
					$("#main-signup-content").animate({opacity:0});
					var now_full_html = "<div style='position:absolute;width:555px;top:0.75em;'>";
					now_full_html += "<h3 style='color:#666;'>We're sorry.</h3>";
					now_full_html += "<div class='alert'>Looks like this shift has just filled up. There are no more open slots available.</div></div>";
					
					$("#signup-modal-content").append(now_full_html)
					$("#signupButtonWrapper").remove();
					$("#cancelButtonWrapper").remove();
					
					$("#signup-modal-content").find(".signup-buttons").append("<div class='small'><a href='/scheduler' id='refresh-cal'>Refresh calendar</a></div>");
					$("#refresh-cal").button();
					
					
				}
				else {
					if ($("#cal-display-filters").length > 0) {
					loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
					} else {
					window.location.reload(true);
					}
					$('#studentSignupDialog').dialog('close');
					
					cancelBtn.show();
					saveBtn.show();
				}
				
			    throbber.remove();
			} else {
				$("#main-signup-content").html("<div class='alert'>You are not authorized to sign up for this shift.</div>");
				cancelBtn.find(".ui-button-text").html("Ok");
				cancelBtn.show();
				$('#signupModalThrobber').remove();
			}
		}
	)
    });
}
   
