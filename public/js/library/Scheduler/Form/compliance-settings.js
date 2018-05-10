$(function() {
	////Grab the form values when the page first loads
	//var savedFormValues = $("#settings-form").serialize();
	/*
	window.onbeforeunload = function (e) {
		//Check to see if theree have been any changes
		if ($('#save-button').attr('data-changes') == 'true') {
			var message = "You have unsaved changes on the form.",
			e = e || window.event;
			// For IE and Firefox
			if (e) {
			  e.returnValue = message;
			}
		  // For Safari
		  return message;
		}
	};*/
	
	
	initPage();

	function initPage(){
		// should we show the success message?
		if (window.location.href.indexOf("success") >= 0) {
			showSuccess();
		}
		
		// clicking the save button shouldn't submit the form, should initiate autosave
		$("#save-button").click(function(e){
			e.preventDefault();
			var throbber = $("<img src='/images/transparent_throbber.gif' class='throbber'>");
			$('#save-button').after(throbber);
			
			// get rid of old error stuff
			$('#form-errors').slideUp();
			$('.input-error').removeClass('input-error');
			
			var postValues = $('#settingsForm').serialize() + "&" + $('#autoAssignForm').serialize();
			//console.log(postValues);
			$.post(
				'/scheduler/compliance/save-settings',
				postValues,
				function(response){
					if (response === true) {
						$('#save-button').attr('data-changes', false);
						window.location = "?message=success";
					} else {
						var htmlErrors = '<div class="form-errors" id="form-errors"><ul>';
						$.each(response, function(sectName, section) {
							$.each(section, function(elementId, msgs) {
								$("#"+elementId).addClass('input-error');
								$.each(msgs, function(key, msg) {
									if (msg == "Value is required and can't be empty") {
										msg = "Please enter a number of days.";
									}
								    htmlErrors += '<li>' + msg + '</li>';
								});
							});
						});
						htmlErrors += '</ul></div>';
						$('#control-buttons').css({ 'margin-bottom' : '0px'} );
						//console.log(htmlErrors);
						$("#settingsForm").find(".island").first().prepend(htmlErrors);
						throbber.hide();
						$('#form-errors').fadeIn("fast");
						$('html,body').animate({scrollTop: $("#settingsForm").offset().top-40},'slow');
					}
				}
			);
		});
		
		$("#settingsForm").change(function(e){
			$('#save-button').attr('data-changes', true);
			$('.success').slideUp(400, function() {
				$('.success').remove();
				$('#save-button').show();
			});
		});
		
		function showSuccess() {
			var notice = '<div class="success" style="width: 95%;">Your settings have been saved.</div>';
			$('#settingsForm .island').prepend(notice);
			$('#save-button').hide();
			$('.success').slideDown();
		}
	}

});
