$(function() {
	$("#emtGradFlag").change(function(){
		pastEducationAnimation($(this), $("#emtGrad"), $("#emtGradDate-month"), $("#emtGradDate-year"));
	});
	
	$("#emtCertFlag").change(function(){
		pastEducationAnimation($(this), $("#emtCert"), $("#emtCertDate-month"), $("#emtCertDate-year"));
	});
	
	$('#studentForm :input:not(:hidden):not(:submit):not(select)').blur(function() {doValidation($(this).parents(".form-prompt").find('label').attr('for'))});
});

function pastEducationAnimation(checkbox, hiddenContents, monthInput, yearInput){
	if(checkbox.is(":checked")){
		hiddenContents.slideUp();
		monthInput.val(0);
		yearInput.val(0);
	}
	else {
		hiddenContents.slideDown();
	}
}

function doValidation(id) {
	var url = '/ajax/validate-student-form';
	var data = $('#studentForm').serialize();

	$.post(url, data, function(resp) {
		$("#"+id).parent().find('.form-live-errors').remove();
		$("#"+id).parent().find('.form-validation-icon').remove();

	    $("#"+id).parent().append(getErrorHtml(resp[id], id));			

	}, 'json');
}

function getErrorHtml(formErrors , id) {
    if (formErrors) {
		var o = '<img src="/images/badinput.png" class="form-validation-icon">';
	} else {
		var o = '<img src="/images/check.png" class="form-validation-icon">';		
	}
	
	
	o += '<ul id="form-live-errors-' + id + '" class="form-live-errors">';
	
    for (errorKey in formErrors) {
        o += '<li>' + formErrors[errorKey] + '</li>';
    }
    o += '</ul>';
    return o;
}