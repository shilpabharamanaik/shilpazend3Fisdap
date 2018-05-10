//Javascript for SkillsTracker_Form_VerificationSubForm

$(document).ready(function() {

	// initialize the unverify button
    $("#unlockButton").button().parent().addClass('gray-button extra-small').click(function(event) {
        event.preventDefault();
        var runId = $("#runId").val();
		
		if(runId != ''){
			blockUi(true);
        
			$.post("/skills-tracker/patients/unverify",
				   {"runId" : runId},
				   function(response) {
					window.location.reload();
			});
		} else {
			var shiftId = $('#shiftId').val();

			$.post("/skills-tracker/signoff/unverify",
				{"shiftId" : shiftId},
				function(response) {
					window.location.reload();
				});
		}
    });

});