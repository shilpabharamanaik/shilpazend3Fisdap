//Javascript for SkillsTracker_Form_Signoff

$(document).ready(function() {

	// if this signoff has been verified, disable the whole signoff form
	if ($("#verificationId").val()) {
		disableSignoffForm();
	}
});

function disableSignoffForm() {
	$('#signoffForm').css('color', '#999999');
	$('#signoffForm :input').each(function (index, el){
		$(el).attr('disabled', true);
	});

	$('#signoffForm .ui-button').unbind('click');
	$('#signoffForm .ui-button').unbind('mouseover');

	// After disabling everything, re-enable the unlock button.
	$('#unlockButton').attr('disabled', false);
}