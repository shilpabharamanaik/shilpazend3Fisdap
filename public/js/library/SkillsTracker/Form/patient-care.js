//Javascript for SkillsTracker_Form_PatientCare

$(document).ready(function() {
	$("#primary, #secondary, input[name='alert']").live('change', function() {
		togglePatientFields("slow");
	});

	$("#new-preceptor").click(function(e){
		e.preventDefault();
		$("#preceptorDialog").dialog("open");
	})

	// Stupid hack to place the orientation checkboxes below the 'Alert - ' radio button.
	$(".alert_fields_wrapper .form-prompt label:eq(1)").after($("#alert-fields"));

	// Listen for click on Alert buttons.
	$("input[name=alert]").on("click", function() {
		// If anything other than Alert is selected, uncheck all the orientations.
		if ($(this).val() != 1) {
			$("#alert-fields :checkbox").each(function () {
				$(this).prop('checked', false);
			});
		}

		// Update the score.
		updateAvpuScore();
	});

	// Listen for click on Orientation boxes.
	$("#alert-fields :checkbox").on("click", function() {
		// Update the score.
		updateAvpuScore();

		// Force save to fire because autosave behaved oddly in specific use cases.
		autosavePCData();
	});

	// Here for the initial load.
	updateAvpuScore();

	togglePatientFields(null);
});

function updateAvpuScore()
{
	var alertOriented = $("input[name=alert]:checked");

	if (alertOriented.val() == 1) {
		$("#avpu_score").text("Alert & Disoriented");

		var checked = 0;
		$("#alert-fields :checkbox").each(function () {
			if (this.checked) {
				checked++;
			}
		});
		if (checked > 0) {
			$("#avpu_score").text("A&Ox" + checked);
		}

	} else {
		$("#avpu_score").text("");
	}
}

function togglePatientFields(speed)
{
	var primary = $("#primary");
	var secondary = $("#secondary");
	var alertOriented = $("input[name=alert]:checked");
	var arrest = $("#arrest-fields");
	var trauma = $("#trauma-fields");
	var alertFields = $("#alert-fields");
	
	var traumaValues = new Array(27, 28, 29, 30, 31, 32);
	
	//Show/Hide Arrest prompts
	if (primary.val() == 4 || secondary.val() == 4) {
		arrest.fadeIn(speed);
	} else {
		arrest.fadeOut(speed);
	}

	//Show/Hide Trauma prompts
	if ($.inArray(parseInt(primary.val()), traumaValues) >= 0 || $.inArray(parseInt(secondary.val()), traumaValues) >= 0) {
		trauma.fadeIn(speed);
	} else {
		trauma.fadeOut(speed);
	}
	
	//Show/Hide Mental Status Assessment prompts
	if (alertOriented.val() == 1) {
		alertFields.fadeIn(speed);
	} else {
		alertFields.fadeOut(speed);
	}
}

function getPatientId()
{
	idPrompt = $('#hiddenPatientId');
	var runId = $('#patientCareForm #runId').val();
	
	if ($(idPrompt).val()) {
		return $(idPrompt).val();
	} else {
		$.ajaxSetup({async:false});
		$.post("/skills-tracker/patients/generate-patient-id", {"runId" : runId},
			   function(response) {
				$(idPrompt).val(response);
			   });
	}
	return $(idPrompt).val();
}