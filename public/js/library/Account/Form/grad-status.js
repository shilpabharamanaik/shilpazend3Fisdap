//Javascript for Account_Form_GradStatus

$(document).ready(function() {
	$("#editDateFlag, #editStatusFlag, #editCertFlag, #editShiftFlag, input[name='gradStatus']").change(function() {
		toggleDivs("slow");
	});

	toggleDivs(null);
});

function toggleDivs(speed) {
	var dateFlag = $("#editDateFlag");
	var statusFlag = $("#editStatusFlag");
	var certFlag = $("#editCertFlag");
    var shiftFlag = $("#editShiftFlag");
	var gradDateSettings = $("#grad-date-settings");
	var gradStatusSettings = $("#grad-status-settings");
	var goodDataSettings = $("#good-data-settings");
	var certLevelSettings = $("#cert-level-settings");
    var shiftLimitSettings = $("#shift-limit-settings");
	
	var gradStatus = $("input[name='gradStatus']:checked");
	var leftProgramSettings = $("#left-program-settings");
	
	//Show/Hide Graduation Date prompts
	if (dateFlag.attr("checked")) {
		gradDateSettings.fadeIn(speed);
	} else {
		gradDateSettings.fadeOut(speed);
	}

	//Show/Hide Graduation Status prompts
	if (statusFlag.attr("checked")) {
		gradStatusSettings.fadeIn(speed);
		goodDataSettings.fadeIn(speed);
	} else {
		gradStatusSettings.fadeOut(speed);
		goodDataSettings.fadeOut(speed);
		leftProgramSettings.fadeOut(speed);
	}
	
	//Show/Hide Certification Level prompts
	if (certFlag.attr("checked")) {
		certLevelSettings.fadeIn(speed);
	} else {
		certLevelSettings.fadeOut(speed);
	}

    //Show/Hide Shift Limit prompts
    if (shiftFlag.attr("checked")) {
        shiftLimitSettings.fadeIn(speed);
    } else {
        shiftLimitSettings.fadeOut(speed);
    }

	if (gradStatus.val() == 4 && statusFlag.attr("checked")) {
		leftProgramSettings.fadeIn(speed);
	} else {
		leftProgramSettings.fadeOut(speed);
	}
}