//Javascript for /skills-tracker/shifts/index
// IE 8 fix? This works please don't ask me why.
var shiftId = null;

//Delete all runs that are marked to be deleted before navigating away from this form;
// skills are automatically deleted via the javascript for the intervention list
$(window).on('beforeunload', function () {
    // only run the deletion if there are patients to delete
    if ($("#run-table td.undo").length > 0) {
        hardDeleteRuns($("#shiftId").val());
    }
});

$(document).ready(function(){
    // initialize the lock shift button
	$('.lock-shift-btn').button().click(function (event) {
		event.preventDefault();
		var id = $("#lockshift_shiftId").val();
		loadLockModal(id);
	});

    // initialize the edit shift modal
	$("#edit-shift-link").click(function(event) {
        event.preventDefault();
        initShiftModal();
		$("#shiftDialog").dialog("open");
	});
	
	doTableJqueryEvents();

    // make the icon image an svg so we can manipulate it with css
    imgToSVG('.lock-shift-btn img.icon');
});


function doTableJqueryEvents() {
	//remove previous jquery stuff because we're about to reapply them
	$('#run-table .alt').removeClass('alt');
	$('.delete-run').unbind('click');

	// delete patient icon
    $('.delete-run').click(function(event) {
		event.preventDefault();
		var id = $(this).attr('runid');
		deleteRun(id, "run");
	});

    $('#add-run-btn').button().parent().addClass("gray-button");
}

/**
 * This just sets the soft delete flag in the database
 * @param id
 * @param type
 */
function deleteRun(id, type) {
	var row = $("#" + id);
	blockUi(true);
	
	function complete() {
		row.hide();
		doTableJqueryEvents();
		if (type == "patient") {
			var url = "/skills-tracker/shifts/delete-patient";
			var data = { "patientId" : id };
		} else {
			var url = "/skills-tracker/shifts/delete-run";			
			var data = { "runId" : id };
		}
		
		$.post(url, data,
			   function(response) {
					var message = $("<tr><td colspan='7' class='undo'>" + response + "</td></tr>");
					//$("#shift-list-messages").html(message.hide().fadeIn(1000));
					row.before(message.fadeIn(1000));
				
					//var message = $(response);
					//$("#run-list-messages").html(message.hide().fadeIn(1000));
					$('#undo-delete-' + id).click(function(event) {
						event.preventDefault();
						undoDeleteRun(id, type);
					});
					blockUi(false);
				},
				'json');
	}
	
	row.fadeOut(1000, complete);
}

function undoDeleteRun(id, type) {
	var row = $("#" + id);
	blockUi(true);
	
	function complete() {
		row.fadeIn(1000);

		var message = $("<div>Patient #" + id + " successfully restored.</div>")
		
		row.prev().remove();
		$("#run-list-messages").html(message.hide().fadeIn(1000));
		doTableJqueryEvents();
		blockUi(false);
	}
	
	if (type == "patient") {
		var url = "/skills-tracker/shifts/undo-delete-patient/";
		var data = { "patientId" : id };
	} else {
		var url = "/skills-tracker/shifts/undo-delete-run/"
		var data = { "runId" : id };
	}
	
	$.post(url, data, complete, 'json');
}

function hardDeleteRuns(shiftId)
{
    $.ajaxSetup({async:false});
    $.post("/skills-tracker/shifts/hard-delete-runs/",
            {"shiftId": shiftId},
            function(response) {
                return true;
            }
    );
}

function loadLockModal(shiftId) {
	$.post("/skills-tracker/shifts/generate-lock-form", {"shiftId": shiftId},
			function (resp) {
				$("#lockShiftDialog").html($(resp).html());
				$("#lockShiftDialog").dialog("open");
			});
}
