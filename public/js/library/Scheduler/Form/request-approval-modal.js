function initRequestApprovalModal() {
    $('#cancel-btn').button().blur();
    $('#approve-btn').button().blur();
    $('#deny-btn').button();
   
    var title = $("#title").val();
    $("#ui-dialog-title-requestApprovalDialog").html(title);
 
    $('#cancel-btn').click(function(event) {
	event.preventDefault();
	$('#requestApprovalDialog').dialog('close');
    });
    
    $('#approve-btn').click(function(event) {
	event.preventDefault();
	$('#state_id').val(2);
	postForm();
    });
     
    $('#deny-btn').click(function(event) {
	event.preventDefault();
	$('#state_id').val(3);
	postForm();
    });
    
    function postForm() {
	var postValues = $('#requestApprovalDialog form').serialize();
	$('#requestApprovalForm :input').attr('disabled', true);
	var cancelBtn = $('#cancel-btn').hide();
	var approveBtn = $('#approve-btn').hide();
	var denyBtn = $('#deny-btn').hide();
	var throbber =  $("<img id='requestApprovalThrobber' src='/images/throbber_small.gif'>");
	cancelBtn.parent().append(throbber);
	$.post(
	    '/scheduler/requests/process-request-approval',
	    postValues,
	    function (response) {
	        if(response === true) {
		    window.location = '/scheduler/requests';
		} else {
		    htmlErrors = '<div id=\'requestApprovalErrors\' class=\'form-errors alert\'><ul>';
				
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
		    $('#requestApprovalDialog form').prepend(htmlErrors);
	            cancelBtn.show();
	            $('#requestApprovalThrobber').remove();
		}
	    }
	)
    }
}
