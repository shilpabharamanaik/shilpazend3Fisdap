//Javascript for SkillsTracker_View_Helper_AddPreceptorWidget

//Throbber to be used on the modal dialogs
var throbber = $("<img id='throbber' src='/images/throbber_small.gif'>");



$(function() {

    $("#preceptorDialog").dialog({
        "tabPosition":"top",
        "modal":true,
        "autoOpen":false,
        "resizable":false,
        "width":300,
        "title":"Add Preceptor",
        "buttons":[{"text":"Save","id":"save-btn","class":"gray-button small","click":function() {
							var saveBtn = $('#preceptorDialog').parent().find('#save-btn').hide();
							saveBtn.parent().append(throbber);
							var modalInputs = $('#preceptorDialog form :input:not(":hidden")');
							
							$.post('/skills-tracker/patients/validate-preceptor', $('#preceptorDialog form').serialize(),
								function(response) {
                                    
									if (typeof response == 'string') {
										
										// add the new preceptor to the select box
										$("#preceptor").append(response);
										
										// now sort the list again
										var preceptor_options = $("#preceptor option");

										preceptor_options.sort(function(a,b) {
											
											if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
											else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
											else return 0
										
										})
										
										$("#preceptor").empty().append( preceptor_options );
										
                                        $("#preceptorDialog").dialog("close");
										
										//Zero out the form inputs in case they want to enter another
										modalInputs.val("");
										
										//Show the save button and remove the throbber
										saveBtn.show();
										throbber.remove();
									} else {
										htmlErrors = '<div class=\'form-errors alert\'><ul>';
										
										$('label').removeClass('prompt-error');
										
										$.each(response, function(elementId, msgs) {
											$('label[for=' + elementId + ']').addClass('prompt-error');
											$.each(msgs, function(key, msg) {
												htmlErrors += '<li>' + msg + '</li>';
											});
										});
										
										htmlErrors += '</ul></div>';
										
										$('.form-errors').remove();
										$('#preceptorDialog form').prepend(htmlErrors);
										
										saveBtn.show();
										throbber.remove();
									}
								});
						}}]});
	
	$( "#preceptorDialog" ).on( "dialogopen", function( event, ui ) {
		// hide any error messages
		$("#preceptorDialog").find(".form-errors").remove();
		$("#preceptorDialog").find(".prompt-error").removeClass("prompt-error");
	} );
});