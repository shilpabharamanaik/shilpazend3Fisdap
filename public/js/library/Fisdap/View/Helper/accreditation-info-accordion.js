$(function(){
	initAccreditationAccordion();
});

// accordion functionality
function initAccreditationAccordion() {
	initAccordion();

	$("#edit-modal-container").dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 750,
		title: "Edit Accreditation Info",
	});
	
	// open the edit modal
	$('.edit-accred-info-btn').button().click(function(e){
		var editButton = $(this);
		
		$(editButton).button('disable').css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-edit-modal-throbber'>");
		
		e.preventDefault();
		var siteId = $(this).attr("data-siteid");
		
		$.post("/reports/index/generate-accreditation-info",
			   {"siteId" : siteId},
			   function(response){
					$("#accreditationinfo").empty().html(response.form);
					$("#edit-modal-container h3").html(response.siteName + " - Accreditation Info");
					$("#save-accred-info-button").attr("data-siteid", response.siteId);
					
					initAccreditationFormFunctions();
					
					// remove the button that comes with the form
					$("#save_accreditation").remove();
					$("#edit-modal-container").dialog("open");
					$("#load-edit-modal-throbber").remove();
					$(editButton).button('enable').css("opacity", "1");
				},
				"json");
	});
	
	// save your edits
	$("#save-accred-info-button").button().click(function(e){
		var editButton = $(this);
		
		$(editButton).button('disable').parent().parent().css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='save-edit-modal-throbber'>");
		e.preventDefault();
		
		var form_data = getAccreditationValues();
		var msg_holder = $("#accreditation_submit_messages");
		msg_holder.slideUp();
		
		// end our ajax request to process the form!
		$.post("/account/sites-ajax/save-accreditation", {"site_id" : $(this).attr("data-siteid"), "form_data" : form_data},
			function(resp) {
				
				// if it wasn't successful, append the errors and add classes to the invalid elements
				if (resp['success'] == "false") {
					$("#accreditationinfo").find(".invalid-data-error").removeClass("invalid-data-error");
					msg_holder.html(resp['result']).fadeIn();
					$.each(resp['form_elements_with_errors'], function(i, v){$("#" + v).addClass("invalid-data-error");});
				}
				
				// was successful, close the modal and trigger the table to update
				else {
					$("#edit-modal-container").dialog("close");
					updateAccreditationAccordion(); // custom function per use of this helper
				}
				
				// put the action buttons back and remove the throbber
				$(editButton).button('enable').parent().parent().animate({opacity: 1});
				$("#save-edit-modal-throbber").remove();
				
			} // end response function
			
		); // end post()
	});
	
	// cancel the edit
	$("#cancel-accred-info-button").button().click(function(e){
		e.preventDefault();
		$("#edit-modal-container").dialog("close");
		$("#accreditation_submit_messages").slideUp();
	});
	

}