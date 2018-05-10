$(function(){
	// initialize buttons
	$('a#addProgram').button();
	$('a#removeProgram').button();
	$('a#edit-sharing-btn').button();
	$('a#request-sharing-btn').button();
	
	// disable some buttons
	$('a#addProgram').button("disable");
	$('a#removeProgram').button("disable");
	$('a#edit-sharing-btn').button("disable");
	
	// initialize the styles
	initStyles();

	// when the EDIT/ADD buttons are clicked
	$("#edit-sharing-btn, #addProgram").click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
        if ($(this).attr('disabled')) {
			return false;
		}

		$('a#removeProgram').button("disable");
		$('a#edit-sharing-btn').button("disable");
		
		// hide the button and show the throbber until we're ready
		$(this).button("disable");
		var throbber =  $("<img id='sharingThrobber' src='/images/throbber_small.gif'>");
		$(throbber).css({"position": "relative", "top": "-30px"});
		if ($(this).attr('id') == 'addProgram') {
			$(throbber).css({"position": "relative", "top": "-72px"});
		}
		if ($(this).attr('id') == 'edit-sharing-btn') {
			$(throbber).css({"left": "40px", "position": "relative", "top": "-24px"});
		}
		$(this).parent().append(throbber);
		
		// find the selected program
		programId = $("#sharingProgram").find("option:selected").val();
		if (programId == null) {
			programId = $("#nonsharingProgram").find("option:selected").val();
		}
		
		// post the data to generate the modal
		$.post("/account/sites/generate-sharing-permissions-form",
		       { programId: programId, siteId: $("#site_id").val() },
		       function(response){
				$("#sharingPermissionsDialog").html($(response).html());
				$("#sharingPermissionsDialog").dialog({
					open: function (){
						$("#cancel-sharing-btn").blur();
					}
				});
				$("#sharingPermissionsDialog").dialog("open");
				initSharingPermissionsModal();
				$("#sharingThrobber").remove();
				$('a#addProgram').button("disable");
				$('a#removeProgram').button("disable");
				$('a#edit-sharing-btn').button("disable");
				deselectAll();
			}
		);
		
		return true;
	});
	
	// when the REMOVE button is clicked
	$("#removeProgram").click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
        if ($(this).attr('disabled')) {
            return false;
        }
		// disable the button and show the throbber until we're ready
		$(this).button("disable");
		var throbber =  $("<img id='sharingThrobber' src='/images/throbber_small.gif'>");
		$(throbber).css({"position": "relative", "top": "-30px"});
		$(this).parent().append(throbber);
		
		// find the selected program
		programId = $("#sharingProgram").find("option:selected").val();
		
		// post the data to generate the modal
		$.post("/account/sites/generate-remove-sharing-form",
		       { programId: programId, siteId: $("#site_id").val() },
		       function(response){
				$("#removeSharingDialog").html($(response).html());
				$("#removeSharingDialog").dialog("open");
				initRemoveSharingModal();
				$("#sharingThrobber").remove();
				$('a#addProgram').button("disable");
				$('a#removeProgram').button("disable");
				$('a#edit-sharing-btn').button("disable");
				deselectAll();
			}
		);
		
		return true;
	});
	
	// when the REQUEST SHARING button is clicked
	$("#request-sharing-btn").click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
        if ($(this).attr('disabled')) {
            return false;
        }
		
		// hide the button and show the throbber until we're ready
		$(this).hide();
		var throbber =  $("<img id='sharingThrobber' src='/images/throbber_small.gif'>");
		$(throbber).css({"left": "-70px", "position": "relative", "top": "6px"});
		$(this).parent().prepend(throbber);
		
		// post the data to send the request
		$.post("/account/sites/send-sharing-request",
		       { siteId: $("#site_id").val() },
		       function(response){
				$("#sharingDirections").html("<div class='success'>"+response+"</div>").show();
				$("#nonsharingProgram option[value='"+$("#program_id").val()+"']").removeClass("no-request");
				$("#sharingThrobber").remove();
			}
		);
		
		return true;
	});
		
	// The functions for when a new option is selected from either list
	// Determines when the "edit" button should appear and will only allow one list to have item(s) selected
	$("select#nonsharingProgram").change(function(){
		//Don't do anything if the select is disabled
                if ($(this).attr('disabled')) {
                    return false;
                }
		$("#sharingProgram").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		$('a#removeProgram').button("disable");
		$('a#edit-sharing-btn').button("disable");
		if ($("#nonsharingProgram").find("option:selected").hasClass('no-request')) {
			$("#addProgram").button("disable");
		} else {
			$("#addProgram").button("enable");
		}
		return true;
	});
	
	$("select#sharingProgram").change(function(){
		//Don't do anything if the select is disabled
        if ($(this).attr('disabled')) {
            return false;
        }
		$("#nonsharingProgram").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		$("#addProgram").button("disable");
		if ($("#sharingProgram").find("option:selected").hasClass('not-me')) {
			$("#edit-sharing-btn").button("disable");
			$("#removeProgram").button("disable");
		} else {
			$("#edit-sharing-btn").button("enable");
			$("#removeProgram").button("enable");
		}
		return true;
	});
	
	
	// SHARING PERMISSIONS MODAL
	function initSharingPermissionsModal() {
		$("#cancel-sharing-btn").button();
		$("#save-sharing-btn").button();
		$(".slider-checkbox").each(function(){$(this).sliderCheckbox({onText: 'Yes', offText: 'No'});});
		initFlippyDivs();
		
		$('#cancel-sharing-btn').click(function(event) {
			event.preventDefault();
			$('#sharingPermissionsDialog').dialog('close');
		});

		$('#save-sharing-btn').click(function(event) {
			event.preventDefault();

			// BUSY ROBOT!
			busyRobot();
				
			var postValues = $('#sharingPermissionsDialog form').serialize();
			var cancelBtn = $('#cancel-sharing-btn').hide();
			var saveBtn = $('#save-sharing-btn').hide();
			var throbber =  $("<img id='sharingModalThrobber' src='/images/throbber_small.gif'>");
			saveBtn.parent().append(throbber);
			$.post("/account/sites/set-sharing-permissions",
				postValues,
				function (response) {
					var new_share = response['new'];
					if (response['new']) {
						// recalculate compliance, if necessary
						var userContextIds = response['compliance'];
						if (userContextIds && userContextIds.length > 0) {
							// if there are user ids, send them on to have compliance recalculated
							var plural = "s";
							if (userContextIds.length == 1) {plural = "";}
							var new_busy_robot_txt = "Fisdap Robot is recomputing compliance for " + userContextIds.length + " user" + plural + ".";
							$("#busy-robot").find("#robot-processing-txt").text(new_busy_robot_txt);
						
							$.post("/scheduler/compliance/compute-compliance", {userContextIds: userContextIds},
								function(resp) {
									reloadPage(new_share, response['site_id']);
							});
						} else {
							reloadPage(new_share, response['site_id']);
						}
					} else {
						htmlErrors = '<div id=\'sharingPermissionsErrors\' class=\'form-errors alert\'><ul>';

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
						$('#sharingPermissionsDialog form').prepend(htmlErrors);
						saveBtn.show();
						cancelBtn.show();
						$('#sharingModalThrobber').remove();
						closeBusyRobot();
					}
				}
			).fail(function(){ brokenRobot(); });
		});
	}
	
	// REMOVE FROM SHARING MODAL
	function initRemoveSharingModal() {
		$("#cancel-remove-btn").button();
		$("#remove-sharing-btn").button();
		$(".slider-checkbox").each(function(){$(this).sliderCheckbox({onText: 'Yes', offText: 'No'});});
		
		$('#cancel-remove-btn').click(function(event) {
			event.preventDefault();
			$('#removeSharingDialog').dialog('close');
		});

		$('#remove-sharing-btn').click(function(event) {
			event.preventDefault();
			
			if ($("#something-changed").val() == 1) {
				var r = confirm("If you process this request, your unsaved changes to this site will be lost.");
				if(r == false){
					$('#removeSharingDialog').dialog('close');
					return;
				}
			}
			
			// BUSY ROBOT!
			busyRobot();
			
			var postValues = $('#removeSharingDialog form').serialize();
			var cancelBtn = $('#cancel-remove-btn').hide();
			var saveBtn = $('#remove-sharing-btn').hide();
			var throbber =  $("<img id='sharingModalThrobber' src='/images/throbber_small.gif'>");
			saveBtn.parent().append(throbber);
			$.post("/account/sites/remove-from-sharing",
				postValues,
				function (response) {
					var siteId = response['site_id'];
					if (siteId) {var userContextIds = response['compliance'];
						if (userContextIds && userContextIds.length > 0) {
							// if there are user ids, send them on to have compliance recalculated
							var plural = "s";
							if (userContextIds.length == 1) {plural = "";}
							var new_busy_robot_txt = "Fisdap Robot is recomputing compliance for " + userContextIds.length + " user" + plural + ".";
							$("#busy-robot").find("#robot-processing-txt").text(new_busy_robot_txt);
						
							$.post("/scheduler/compliance/compute-compliance", {userContextIds: userContextIds},
								function(resp) {
									reloadPage(0, siteId);
							});
						} else {
							reloadPage(0, siteId);
						}
					} else {
						htmlErrors = '<div id=\'sharingPermissionsErrors\' class=\'form-errors alert\'><ul>';

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
						$('#removeSharingDialog form').prepend(htmlErrors);
						saveBtn.show();
						cancelBtn.show();
						$('#sharingModalThrobber').remove();
						closeBusyRobot();
					}
				}
			).fail(function(){ brokenRobot(); });
		});
	}
	
	function deselectAll() {
		$("#nonsharingProgram").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		$("#sharingProgram").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
	}
	
	// create some styling when the page first loads
	function initStyles() {
		// mark the non-requested programs
		$("select#nonsharingProgram option").each(function(){
			if (noRequest($(this).val()) == true) {
				$(this).addClass('no-request');
			}	
		});
		
		// if this is a non-admin member of the network, make them only able to pick themselves
		if ($("#shared_status").val() == 3) {
			$("select#sharingProgram").prop('disabled', false);
			$("select#sharingProgram option").each(function(){
				if ($(this).val() != $("#program_id").val()) {
					$(this).addClass('not-me');
				}
			});
		}
	}
	
	// determine if a given program id is one that has not yet requested to share
	function noRequest(id) {
		var nonRequestedPrograms = $("#nonRequestedPrograms").val().split(", ");
		var request = false;
		$.each(nonRequestedPrograms, function(i, value) {
			if (id == value) {
				request = true;
			}
		});
		return request;
	}
	
	function reloadPage(new_share, siteId) {
		var host =  window.location.protocol+"//"+window.location.host;
		if (new_share == 0) {
			var newUrl = host+"/account/sites/edit/siteId/"+siteId+"/tab/sharingnetwork";
		} else if (new_share == 1) {
			var newUrl = host+"/account/sites/edit/siteId/"+siteId+"/tab/bases";
		}
		$(location).attr('href',newUrl);
	}
	
});
	
