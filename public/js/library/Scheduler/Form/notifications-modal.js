function initNotificationsModal() {
	
	// blur all elements in notifications modal
	$("#notificationsRequirementDialog").on( "dialogopen", function( event, ui ) {
		$("#notificationsRequirementDialog").find("a, input").blur();	
	});
    
    $("#cancel-btn-n").click(function(e){
		e.preventDefault();
		$("#notificationsRequirementDialog").dialog('close');
	});
    
    $("#save-btn-n").click(function(e){
		e.preventDefault();
		
		// remover old errors
		$('#form-errors').slideUp();
		$(".input-error").removeClass('input-error');
		
		var postValues = $('#notificationsRequirementDialog form').serialize();
		var cancelBtn = $('#cancel-btn-n').hide();
		var saveBtn = $('#save-btn-n').hide();
		var throbber =  $("<img id='modalThrobber' src='/images/throbber_small.gif'>");
		saveBtn.parent().append(throbber);
		
		$.post("/scheduler/compliance/process-notifications-modal",
			postValues,
			function (response) {
				if (response === true) {
					location.reload();
				} else {
					var htmlErrors = '<div class="form-errors" id="form-errors"><ul>';
					$.each(response, function(elementId, msgs) {
						$("#"+elementId).addClass('input-error');
						$.each(msgs, function(key, msg) {
							if (msg == "Value is required and can't be empty") {
								msg = "Please enter a number of days.";
							}
						    htmlErrors += '<li>' + msg + '</li>';
						});
					});
					htmlErrors += '</ul></div>';
					//$('#control-buttons').css({ 'margin-bottom' : '0px'} );
					//console.log(htmlErrors);
					$('#notificationsForm').prepend(htmlErrors);
					throbber.hide();
					cancelBtn.show();
					saveBtn.show();
					$('#form-errors').slideDown();
					
				}
			}
		)
	});
}
   
