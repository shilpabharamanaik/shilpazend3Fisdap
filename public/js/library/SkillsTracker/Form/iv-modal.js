//Javascript for SkillsTracker_Form_IvModal

$(document).ready(function() {
    var ivProc = $("#ivProcedure");
    
    ivProc.live('change', function() {
        toggleIvSuccess($(this).val());
        //toggleIvSize($(this).val());
        toggleIvAttempts($(this).val());
    });

    
});

function isOdd(num) { return num % 2;}

function initIvModal()
{
    var dialog = $('#ivDialog');
    
    toggleIvSuccess($("#ivProcedure").val());
    //toggleIvSize($("#ivProcedure").val());
    toggleIvAttempts($("#ivProcedure").val());
    
    dialog.parent().find(".ui-dialog-buttonpane").remove();
    
    $("#cancel-iv-modal").unbind().button().click(function(e){
      e.preventDefault();
      dialog.dialog("close");
    });
    
    $("#save-iv-modal").unbind().button().click(function(e){
        e.preventDefault();
        
        dialog.find(".form-errors").remove();
        dialog.find(".prompt-error").removeClass('prompt-error');
        
        var size_valid = false;
        var attempts_valid = false;
        var success_valid = false;

	    var error_msg_lis = "";
        
        if ($("#ivSize").parent().css("display") != "none" || $("#ioSize").parent().css("display") != "none") {
            
            var size = parseInt($("#ivSize").val());
            var attempts = parseInt($("#ivAttempts").val());
            var successful = $('input[name="ivSuccess"]:checked').val();
            
            if ($("#ivProcedure").val() == "2") {
					size = parseInt($("#ioSize").val());
					if(!isNaN(size)){
						size_valid = true;
					}
                    else{
						var size_error_mess = 'For "size," You can select any one from the dropdown';
					}
            }
            else {
                // must be an even number between 14 and 24
                if ((!isOdd(size)) && (size < 25) && (size > 13)) {
                    size_valid = true;
				}
				else{
					var size_error_mess = 'For "size," you may only use even numbers between 14 and 24';
                }
            }
            
            if (!size_valid) {error_msg_lis += applyError("ivSize", '' + size_error_mess + '.');}
            
            if (isNaN(attempts)) {error_msg_lis += applyError("ivAttempts", 'Tell us how many times you attempted the procedure (using only numbers).');}
            else {attempts_valid = true;}
            
            if (successful === undefined) {error_msg_lis += applyError("ivSuccess", 'Please tell us whether the procedure was performed successfully.');}
            else {success_valid = true;}
        }
        else {
            attempts_valid = true;
            size_valid = true;
            success_valid = true;
        }
        
        if (!size_valid || !attempts_valid || !success_valid) {
            // add errors
            var error_msg = '<div class="form-errors alert"><ul>' + error_msg_lis + '</ul></div>';
            $("#ivDialog").find("form").prepend(error_msg);
        }
        else {
            $("#ivDialog").find(".iv-modal-buttons").find("button").hide();
            $("#ivDialog").find(".iv-modal-buttons").append("<img src='/images/throbber_small.gif' id='iv-modal-throbber'>");
			
            if (typeof getPatientId == 'function') { 
				var patientId = getPatientId(); 
				$('#ivDialog input[name=patientId]').val(patientId); 
				shiftId = null; 
			}
			if (typeof getShiftId == 'function'){ 
				shiftId = getShiftId(); 
				$('#ivDialog input[name=shiftId]').val(shiftId); 
			}
            
            var form_data = $('#ivDialog form').serialize();
            form_data = form_data.replace("patientId=&", "patientId=" + $("#patientId").val() + "&");
            $.post('/skills-tracker/patients/validate-iv', form_data,
                function(response) {
                    if (typeof response == 'string') {
                        var entityId = response;
                        $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : $("#patientId").val(), 'shiftId' : $("#shiftId").val()},
                        function(response) {
                            $('#intervention-list').html($(response).fadeIn());
                            initInterventionList();
                            $('#ivDialog').dialog('close');
                            $("#ivDialog").find(".iv-modal-buttons").find("button").show();
                            $("#iv-modal-throbber").remove();
                            var newEntity = $('#' + entityId).addClass('new');
                            setTimeout(function() { newEntity.toggleClass('new', 2000) }, 1000);
                            
                        });									
                    } else {
                        htmlErrors = '<div class=\'form-errors alert\'><ul>';
                        
                        $.each(response, function(elementId, msgs) {
                            $('label[for=' + elementId + ']').addClass('prompt-error');
                            $.each(msgs, function(key, msg) {
                                htmlErrors += '<li>' + msg + '</li>';
                            });
                        });
                        
                        htmlErrors += '</ul></div>';
                        
                        $('.form-errors').remove();
                        $('#ivDialog form').prepend(htmlErrors);
                        
                        $("#ivDialog").find(".iv-modal-buttons").find("button").show();
                        $("#iv-modal-throbber").remove();
                    }
                    
                });
        }
    });
}

function applyError(element_id, msg) {
    $('#ivDialog').find("label[for='" + element_id + "']").addClass("prompt-error");
    return '<li>' + msg + '</li>';
}

function toggleIvSuccess(value)
{
    //var ivSuccessValues = [1,2,3,8];

    $("#ioSize").parents(".form-prompt").hide();
	$("#ivSize").parents(".form-prompt").hide();
    
    if ($("#ivProcedure").val() == "2") {
		$("#ivSize").parents(".form-prompt").hide();
		$("#ioSize").parents(".form-prompt").show();
        var iv_size_label = "Select";
		$("#ivDialog").find("label[for='ioSize']").find(".form-desc").text(iv_size_label);		
    }
	else if($("#ivProcedure").val() == "1" || $("#ivProcedure").val() == "3" || $("#ivProcedure").val() == "8"){
		var iv_size_label = "(14-24)";
		$("#ivSize").parents(".form-prompt").show();
		$("#ioSize").parents(".form-prompt").hide();
		$("#ivDialog").find("label[for='ivSize']").find(".form-desc").text(iv_size_label);
	}
}

/*
function toggleIvSize(value)
{
    var ivSizeValues = [1,2,3,8];

    if ($.inArray(parseInt(value), ivSizeValues) >= 0) {
        $("#ivSize").parents(".form-prompt").show();
    } else {
        $("#ivSize").parents(".form-prompt").hide();
    }

    var iv_size_label = "(14-24)";

    if ($("#ivProcedure").val() == "2") {
     iv_size_label = "(13-25)";
    }

    $("#ivDialog").find("label[for='ivSize']").find(".form-desc").text(iv_size_label);

}
*/

function toggleIvAttempts(value)
{
    var ivAttemptsValues = [1,2,3,8];

    if ($.inArray(parseInt(value), ivAttemptsValues) >= 0) {
        $("#ivAttempts").parents(".form-prompt").show();
    } else {
        $("#ivAttempts").parents(".form-prompt").hide();
    }
}
