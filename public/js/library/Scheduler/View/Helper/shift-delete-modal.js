$(function(){
    
    // turn the delete modal div into a modal
    $("#shiftDeleteDialog").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 600,
	title: "Delete shift"
    });
   
});

function initShiftDeleteModal() {
    $("#delete-cancel-btn").button().blur();
    $("#no-shift-btn").button().blur();
    $("#delete-btn").button();
    
    $("#delete-cancel-btn").click(function(e){
	e.preventDefault();
	$("#shiftDeleteDialog").dialog('close');
    });
    
    $("#no-shift-btn").click(function(e){
	e.preventDefault();
	var okBtn = $('#no-shift-btn').hide();
	var throbber =  $("<img id='deleteModalThrobber' src='/images/throbber_small.gif'>");
	okBtn.parent().append(throbber);
	if ($("#cal-display-filters").length > 0) {
	    loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
	} else {
	    window.location.reload(true);
	}
	$('#shiftDeleteDialog').dialog('close');
	okBtn.show();
	throbber.remove();
    });
    
    $("#delete-btn").click(function(e){
	e.preventDefault();
			
	var shiftId = $(this).attr('data-shiftid');
	var cancelBtn = $('#delete-cancel-btn').hide();
	var saveBtn = $('#delete-btn').hide();
	var throbber =  $("<img id='deleteModalThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post("/scheduler/index/process-shift-delete",
		{ "shift_id" : shiftId },
		function (response) {
			if (response) {
			    if ($("#cal-display-filters").length > 0) {
				loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
			    } else {
				window.location.reload(true);
			    }
			    $('#shiftDeleteDialog').dialog('close');
			    cancelBtn.show();
			    saveBtn.show();
			    throbber.remove();
			} else {
				$("#main-delete-content").html("<div class='alert'>You are not authorized to delete this shift.</div>");
				cancelBtn.find(".ui-button-text").html("Ok");
				cancelBtn.show();
				$('#deleteModalThrobber').remove();
			}
		}
	)
    });
}
   
