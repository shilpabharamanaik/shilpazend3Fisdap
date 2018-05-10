function initRequestResponseModal() {
    $('#cancel-btn').button().blur();
    $('#save-btn').button();
    
    // chosen stuff
    $('.chzn-select').chosen();
    $(".ui-dialog").css({"overflow": "visible"});
    $(".ui-dialog .ui-dialog-content").css({"overflow": "visible"});
    $("#shift_chzn ul.chzn-choices").css({"overflow": "auto", "max-height": "6.5em"});
    
    var title = $("#title").val();
    $("#ui-dialog-title-requestResponseDialog").html(title);
    
    // if there are no shifts available to swap, don't let them post the form
    if ($('#assignment').attr('disabled')) {
	$('#save-btn').hide();
	$('#cancel-btn .ui-button-text').html('Ok');
	$("#directions-div").html('You do not currently have any shifts available to swap.');
    }

    
    $('#cancel-btn').click(function(event) {
	event.preventDefault();
	$('#requestResponseDialog').dialog('close');
    });
    
    $('#save-btn').click(function(event) {
	event.preventDefault();
	var postValues = $('#requestResponseDialog form').serialize();
	$('#requestResponseForm :input').attr('disabled', true);
	var cancelBtn = $('#cancel-btn').hide();
	var saveBtn = $('#save-btn').hide();
	var throbber =  $("<img id='requestResponseThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post(
	    '/scheduler/requests/process-request-response',
	    postValues,
	    function (response) {
	        if(response === true) {
		    window.location = '/scheduler/requests';
		} else {
		    htmlErrors = '<div id=\'requestResponseErrors\' class=\'form-errors alert\'><ul>';
				
		    $('label').removeClass('prompt-error');
	
		    $.each(response, function(elementId, msgs) {
		        $('label[for=' + elementId + ']').addClass('prompt-error');
		        $.each(msgs, function(key, msg) {
		            htmlErrors += '<li>' + msg + '</li>';
			});
		        if(elementId == 'site_type'){
		            $('#typeContainer').css('border-color','red');
			}
		    });
									
		    htmlErrors += '</ul></div>';

		    $('.form-errors').remove();
		    $('#requestResponseDialog form').prepend(htmlErrors);
	            saveBtn.show();
	            cancelBtn.show();
	            saveBtn.parent().find('#requestResponseThrobber').remove();
		}
	    }
	)
    });
     
}
