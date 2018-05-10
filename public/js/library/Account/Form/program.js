$(function() {
	
	$("#descriptionWrapper").hide();
	
	$('#programForm :input:not(:hidden):not(:submit):not(select)').blur(function() {doValidation($(this).parents(".form-prompt").find('label').attr('for'))});

    $("#next-link").click(function(e){
        e.preventDefault();
        $("#programForm").submit();
    });
	
	$("#referral").change(function(){
		$("#descriptionWrapper").show();
	});
	
	if ($("#referral").val() != 0) {
		$("#descriptionWrapper").show();
	}
	
});

function doValidation(id) {
	var url = '/ajax/validate-program-form';
	var data = $('#programForm').serialize();

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