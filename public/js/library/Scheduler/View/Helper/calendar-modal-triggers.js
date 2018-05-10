// ------------------ Init all of the calendar's modals ----------------------
var initCalendarModals;
initCalendarModals = function() {
	setShiftRequestModal();
	setShiftHistoryModal();
	setStudentSignupModal();
	setStudentDropModal();
	setShiftDeleteModal();
	setEventDeleteModal();
	setViewComplianceModal();
	setEventEditModal();
	setAssignModal();
}

// ------------------ Set shift request modal -----------
var setShiftRequestModal;
setShiftRequestModal = function() {
	
	$('#main-content').on('click','.shift-request',function(event) {
		event.preventDefault();

		var trigger = $(this);
		var assignmentId = trigger.attr('data-assignmentid');
		trigger.hide().parent().prepend("<img src='/images/throbber_small.gif' style='position:relative;margin-right:11em;' id='load-modal-throbber'>");

		$.post("/scheduler/index/generate-shift-request-form", {"assignment_id" : assignmentId},
			function(resp) {
				$("#shiftRequestDialog").html($(resp).html()).dialog({
					open: function (){
						$("#pending a").blur();
					}
				}).dialog("open");
				
				initShiftRequestModal();
				trigger.show();
				$("#load-modal-throbber").remove();
			}
		);
	});

}

// ------------------ Set shift history modal----------------------
var setShiftHistoryModal;
setShiftHistoryModal = function() {
	
	$('#main-content').on('click','.open_history_modal',function(event) {
		event.preventDefault();
		
		var trigger = $(this);
		var id = trigger.attr('data-id');
        var quick_add = trigger.attr('data-quickadd');
		
		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
		var history_modal = $("#history-modal");
		
		$.post("/scheduler/index/generate-shift-history", {"id" : id, "quick_add" : quick_add},
			function(resp) {
				$("#history-modal-content").html($(resp));
				history_modal.dialog("open");
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);

				$("#historyCloseButton").button().blur().click(function(e){
					e.preventDefault();
					history_modal.dialog('close');
				});
			}
		);
	});
}

// ------------------ Set student sign up modal ----------------------
var setStudentSignupModal;
setStudentSignupModal = function() {
	
	$('#main-content').on('click','.signup',function(event) {
		event.preventDefault();

		var trigger = $(this);
		var eventId = trigger.attr('data-eventid');

		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");

		$.post("/scheduler/index/generate-student-signup", {"event_id" : eventId},
			function(resp) {
				$("#signup-modal-content").html($(resp));
				$("#studentSignupDialog").dialog("open");
				initStudentSignupModal();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);

			}
		);
	});

}

// ------------------ Set student drop modal ----------------------
var setStudentDropModal;
setStudentDropModal = function() {
	
	$('#main-content').on('click','.drop-student',function(event) {
		event.preventDefault();

		var trigger = $(this);
		var assignmentId = $(this).attr('data-assignmentid');

		trigger.hide().parent().append("<img src='/images/throbber_small.gif' id='drop-modal-throbber'>");

		$.post("/scheduler/index/generate-student-drop", {"assignment_id" : assignmentId},
			function(resp) {
				$("#drop-modal-content").html($(resp));
				$("#studentDropDialog").dialog("open");
				initStudentDropModal();
				trigger.show();
				$("#drop-modal-throbber").remove();
			}
		);
	});

}

// ------------------ Set shift delete modal ----------------------
var setShiftDeleteModal;
setShiftDeleteModal = function() {
	
	$('#main-content').on('click','.delete-quick-add-shift',function(event) {
		event.preventDefault();

		var trigger = $(this);
		var shiftId = trigger.attr('data-shiftid');

		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");

		$.post("/scheduler/index/generate-shift-delete", {"shift_id" : shiftId},
			function(resp) {
				$("#shift-delete-modal-content").html($(resp));
				$("#shiftDeleteDialog").dialog("open");
				initShiftDeleteModal();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);
			}
		);
	});

}

// ------------------ Set event delete modal ----------------------
var setEventDeleteModal;
setEventDeleteModal = function() {
	
	$('#main-content').on('click','.delete-button',function(event) {
		event.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
		// first lets find out if this event is part of a series
		var event_id = $(this).attr("data-eventid");
		var series_id = $(this).attr("data-seriesid");
		eventIds = new Array();
		
		if (series_id) {
			
			if ($(this).attr("data-future") != 0) {
				// launch the "which shifts" modal
				$.post("/scheduler/index/generate-which-events-in-series", {"series_id" : series_id, "event_id" :event_id, "event_action" : "delete"},
					function(resp) {
						$("#cancel-which-events-modal").blur();
						$("#whichEventsDialog").html($(resp).html()).dialog("open");
						$("#cancel-which-events-modal").blur();
						initWhichShiftsMultiPickcal();
						$("#load-modal-throbber").remove();
						trigger.css("opacity", 1);
						
						// continue button opens the delete confirmation modal
						$("#continue-which-events-modal").click(function(e){
							e.preventDefault();
							var cancelBtn = $('#cancel-which-events-modal').hide();
							var continueBtn = $('#continue-which-events-modal').hide();
							var throbber =  $("<img id='whichEventsThrobber' src='/images/throbber_small.gif'>");
							continueBtn.parent().append(throbber);

							if($(".selected-series-option").attr("id") == "multiple-shifts-option"){
								// we've got a bunch of event ids, use the multiselect box
								eventIds = $("#selected_days").val();
							} else {
								// just use the ID that triggered this
								eventIds[0] = event_id;
							}
							
							deleteEvents(eventIds);
						
						});
					}
				);	
			
			}
			// delete only single shifts in the past
			else {
				eventIds[0] = event_id;
				deleteEvents(eventIds);
			}
			
		}
		// stand-alone shifts
		else {
			eventIds[0] = event_id;
			deleteEvents(eventIds);
		}
		
	});
	
}

// ------------------ Set view compliance modal ----------------------
var setViewComplianceModal;
setViewComplianceModal = function() {
	
	$('#main-content').on('click','.view-compliance',function(event) {
		event.preventDefault();
		
		var throbber = "<img src='/images/throbber_small.gif' id='view-compliance-modal-throbber'>";
		var weebleCell = $(this).parent().find('td.weeble-cell:first');
		var assignmentId = $(this).attr('data-assignmentid');
		
		var day_view = false;
		if (weebleCell.length > 0) {
			var cellWidth = weebleCell.width()-1;
			var cellHeight = weebleCell.height();
			var cellHtml = weebleCell.html();
			weebleCell.html(throbber).width(cellWidth).height(cellHeight);
		}
		else {
			day_view = true;
			$(this).append(throbber);
		}
		
		$.post("/scheduler/index/generate-view-compliance", {"assignment_id" : assignmentId},
			function(resp) {
				$("#view-compliance-modal-content").html($(resp));
				$("#viewComplianceDialog").dialog("open");
				initViewComplianceModal(assignmentId);
				
				if (day_view) {
					$("#view-compliance-modal-throbber").remove();
				}
				else {
					weebleCell.html(cellHtml).width(cellWidth);
				}
			}
		);
	});

}

// ------------------ Set event edit modal ----------------------
var setEventEditModal;
setEventEditModal = function() {
	
	$('#main-content').on('click','.edit-button',function(event) {
		
		var trigger = $(this);
		var series_id = trigger.attr("data-seriesid");
		
		if (series_id) {
			
			event.preventDefault();
			trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
			eventIds = new Array();
			
			var event_id = trigger.attr("data-eventid");
			

			// launch the "which shifts" modal
			$.post("/scheduler/index/generate-which-events-in-series", {"series_id" : series_id, "event_id" :event_id, "event_action" : "edit"},
				function(resp) {
					$("#whichEventsDialog").html($(resp).html()).dialog("open");
					initWhichShiftsMultiPickcal();
					$("#load-modal-throbber").remove();
					trigger.css("opacity", 1);
					
					// continue button opens the delete confirmation modal
					$("#continue-which-events-modal").click(function(e){
						e.preventDefault();
						var cancelBtn = $('#cancel-which-events-modal').hide();
						var continueBtn = $('#continue-which-events-modal').hide();
						var throbber =  $("<img id='whichEventsThrobber' src='/images/throbber_small.gif'>");
						continueBtn.parent().append(throbber);

						if($(".selected-series-option").attr("id") == "multiple-shifts-option"){
							// we've got a bunch of event ids, use the multiselect box
							eventIds = $("#selected_days").val();
						} else {
							// just use the ID that triggered this
							eventIds[0] = event_id;
						}
						
						editEvents(eventIds);
					
					});
				}
			);
		}
		
	});
	
}

// ------------------ Set assign modal ----------------------
var setAssignModal;
setAssignModal = function() {
	
	$('#main-content').on('click','.assign-button',function(e) {
		e.preventDefault();
		
		var trigger = $(this);
		var eventId = trigger.attr('data-eventid');
		
		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
		$.post("/scheduler/index/generate-assign-modal", {"event_id" : eventId},
			function(resp) {
				
				$("#assign-modal").empty().html($(resp).html()).dialog("open").find(".picklist-ms-picker").find(".grid_2").css("width", "14.3%");
				initShiftAssignPicklist();
				
				$("#picklist-fancy-filter_filters-title").blur();
				
				$(".close-assign-modal,.save-assign-modal").button().blur();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);

                $("#assign-modal-content").find(".picklist-control-buttons a").click(function(e){
                    if ($(this).attr("data-controlfunction") == "add" || $(this).attr("data-controlfunction") == "removeAll") {
                        $(".save-assign-modal").button( "option", "label", "Save" );
                        $("#conflict_check").val(0);
                    }
                });

				
				$(".close-assign-modal").click(function(e){
					e.preventDefault();
					$(this).unbind();
					$("#assign-modal").dialog('close');
				});
				
				$(".save-assign-modal").click(function(e){
					e.preventDefault();
					var save_btn = $(this);
                    $("#assign-conflicts-warning").remove();

					save_btn.css("opacity", "0").parent().append("<img id='assign-modal-save-throbber' src='/images/throbber_small.gif'>");

					var students = [];
					$("#assign-modal").find(".chosen-list").find("option").each(function(){
						students.push($(this).attr("value"));
					});

					$.post("/scheduler/index/assign-students", {"event_id" : eventId, "students" : students, "conflict_check" : $("#conflict_check").val()},
                        function (r) {
                            save_btn.css("opacity", "1");
                            $("#assign-modal-save-throbber").remove();

                            if (r === true) {
                                $(this).unbind();
                                $("#assign-modal").dialog('close');
                                loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
                            } else {
                                $("#assign-modal-content").append($(r).fadeIn());
                                save_btn.button( "option", "label", "Confirm" );
                                $("#conflict_check").val(1);
                            }
                        }, "json");
				});
			}
		);

	});
}


// -------------------------- a few helper functions for the modal init guys ------------------------------

var deleteEvents;
deleteEvents = function(eventIds) {
	$.post("/scheduler/index/generate-event-delete", {"event_ids" : eventIds},
		function(resp) {
			$("#whichEventsDialog").dialog("close");
			$("#eventDeleteDialog").html($(resp).html()).dialog("open");
			initEventDeleteModal();
			$("#load-modal-throbber").remove();
			$(".delete-button").css("opacity", 1);
		}
	);	
}

var editEvents;
editEvents = function(eventIds) {
	
	// this will save the event_ids into the session so they can be edited
	$.post("/scheduler/shift/save-event-ids-to-session", {"event_ids" : eventIds},
		function(resp) {
			// now redirect the user
			window.location = "/scheduler/shift/edit/event-session/" + resp;
		}
	);	
	
}

