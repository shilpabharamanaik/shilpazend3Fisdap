
// ----------------------------------------------------------------------------------------------------------------------------------
// Initializes the preceptor form, called from /js/account/sites/edit.js
// ----------------------------------------------------------------------------------------------------------------------------------
var initPreceptorFormFunctions = function()
{
	initPreceptorAccordion();
	initEditPreceptorLink();
	initMergePerceptorsButton();	
	initPreceptorSearch();
	initPreceptorDialog();
	initAddPreceptorButton();
	initAllPreceptorActionLinks();
}



/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the preceptor accordion
 * 	Makes use of the general initAccordion function found in /js/account/sites/edit.js
 * 	Is called after a successful ajax request for editing/adding a new preceptor
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorAccordion = function()
{
	initAccordion($("#preceptor-accordion"));
	initPreceptorToggleActive();
	intiPreceptorSelectCheckboxes();

	// get the search back to a null state
	$("#search_preceptors").val("").trigger("keyup").focus().blur();
	calculatePreceptorMergeEnable();
	
} // end initPreceptorAccordion()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the merge preceptors button
 * 	Will get the IDs of each visible, checked rows
 * 	Also checks to be sure the button is enabled
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initMergePerceptorsButton = function() {
	
	$("#merge-preceptors").click(function(e) {
		e.preventDefault();
		var merge_btn = $(this);
		var preceptors = {};
		
		if (!$(this).hasClass("ui-state-disabled")) {
			
			// get selected preceptors
			$(".merge-preceptor-checkbox").each(function(){
				if ($(this).prop("checked")) {
					preceptors[$(this).attr("data-preceptorid")] = $(this).attr("data-preceptorname");
				}
			});
			
			merge_btn.css("opacity", "0").parent().append(getThrobber("merge-preceptors-btn-throbber"));
			
			$.post("/account/sites-ajax/generate-merge-preceptor-form",
				{ 'preceptors' : preceptors,
				  'site_id' : getCurrentSiteId() },
				function(resp) {
					$("#mergePreceptorsDialog").html($(resp).html());
					$("#mergePreceptorsDialog").dialog("open");
					
					initMergePreceptorsModal();
					$('#merge-preceptors-btn-throbber').remove();
					merge_btn.css("opacity", "1");
				}
			);
			
		} // end if not disabled
	});
	
} // end initMergePerceptorsButton()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the search box for the preceptors accordion
 * 	Handles some styling with the "first" element by using updateAccordionTopElementBorder() found in /js/account/sites/edit.js
 * 	Also will display an error message if no results found
 * 	And will call calculatePreceptorMergeEnable() to enable/disable the merge button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorSearch = function()
{
	$("#search_preceptors").fieldtag().keyup(function(){
		
		$("#preceptor-accordion").find(".accordion_element").show();
		
		if ($(this).val() != "") {
			$("#preceptor-accordion").find(".accordion_element").hide();
			$("#preceptor-accordion").find(".accordion_element .accordion_preceptor_name_wrapper:contains('" + $(this).val() + "')").parents(".accordion_element").show();
		}
		
		updateAccordionTopElementBorder($("#preceptor-accordion"));
		toggleNoResultsMsg($("#preceptor-accordion"), 'No preceptors with the name "' + $(this).val() + '" were found.');
		calculatePreceptorMergeEnable();
		
	});
	
} // end initPreceptorSearch()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Pretty simple: initializes the preceptor dialog
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorDialog = function()
{
	$("#preceptor-modal").dialog({ modal: true,autoOpen: false,title: "Add preceptor",width:455});
} 

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click function for the "Add preceptor" button
 * 	Does some styling/adds a throbber than calls openPreceptorModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAddPreceptorButton = function()
{
	$("#add-preceptor-btn").click(function(e){
		e.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0").parent().append(getThrobber("load-new-preceptor-modal-throbber"));
		openPreceptorModal(trigger, $("#load-new-preceptor-modal-throbber"));
	});
	
} // end initAddPreceptorButton()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click function for each of the 'all action links'
 * These include: expand/collapse all, and activate/deactivate all
 * Makes use of a generic accordion functions found in /js/account/sites/edit.js
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAllPreceptorActionLinks = function()
{
	if ($("#expand-all-preceptors").length > 0) {
		initExpandCollapseAllLinks($("#expand-all-preceptors"), $("#collapse-all-preceptors"), $("#preceptor-accordion"));
	}
	
	initActivateDeactiveAllLinks($("#activate-all-preceptors"), $("#deactivate-all-preceptors"), $("#preceptor-accordion"));
	
} // end initAllPreceptorActionLinks()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the change function for preceptor active checkboxes
 * 	Sends an AJAX request to /account/site-ajax/toggle-preceptor to save on change
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorToggleActive = function()
{
	var site_id = getCurrentSiteId();
	
	$("#preceptor-accordion").find(".accordion_active_checkbox_wrapper").find("input[type='checkbox']").each(function(){
		$(this).change(function(){
			var preceptor_id = $(this).attr("data-preceptorid");
			$.post("/account/sites-ajax/toggle-preceptor", {"preceptor" : preceptor_id, "site" : site_id},
				function(resp) {}
			);
		});
	});
	
} // end initPreceptorToggleActive()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the change function for preceptor select checkboxes
 * 	As the user checks/unchecks we need to call calculatePreceptorMergeEnable() to enable/disable the merge button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var intiPreceptorSelectCheckboxes = function()
{
	$("#preceptor-accordion").find(".accordion_select_checkbox_wrapper").find("input[type='checkbox']").change(function(){
		calculatePreceptorMergeEnable();
	});
	
} // end intiPreceptorSelectCheckboxes()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click handlers for the "edit" links within the preceptor accordion
 * 	Uses event delegation to make things easy
 * 	Will do styling/append a throbber then call openPreceptorModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initEditPreceptorLink = function()
{
	$('#preceptor-accordion').on('click','.edit_preceptor',function(e) {
		e.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0");
		trigger.parent().append(getThrobber("edit-preceptor-throbber"));
		openPreceptorModal(trigger, $("#edit-preceptor-throbber"), $(this).attr("data-preceptorid"));
	});
	
} // end initEditPreceptorLink()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * If the number of check preceptors is greater than 1:
 * enable the merge preceptors button otherwise, disable it
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var calculatePreceptorMergeEnable = function()
{
	$("#merge-preceptors").removeAttr("style");
	if (getNumberOfCheckedPreceptors() > 1) {$("#merge-preceptors").button("enable");}
	else {$("#merge-preceptors").button("disable");}
	
} // end calculatePreceptorMergeEnable()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Counts up the number of visible checked preceptors
 * 	Used in a few places, mostly for enabling/disabling the merge preceptors button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getNumberOfCheckedPreceptors = function()
{
	var count = 0;
	$("#preceptor-accordion").find(".accordion_select_checkbox_wrapper").find("input[type='checkbox']").each(function(){
		if ($("#preceptor_accordion_" + $(this).attr("data-preceptorid")).parent().css("display") != "none") {
			if ($(this).prop("checked")) {count++;}
		}
	});
	
	return count;

} // end getNumberOfCheckedPreceptors()




 // ---------------------------------------------------------------------------------------------------------------------------------- 
 //  
 //              			The following functions are all about the preceptor modals (add/edit/merge) 					  
 //  
 // ---------------------------------------------------------------------------------------------------------------------------------- 


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Opens the add/edit preceptor modal
 * 	Sends an ajax request to /account/sites-ajax/generate-preceptor-modal
 * 	Does some styling to the trigger/removes the throbber then opens the modal
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var openPreceptorModal = function(trigger, throbber, preceptor_id)
{
	$.post("/account/sites-ajax/generate-preceptor-modal", {"site_id" : getCurrentSiteId(), "preceptor_id" : preceptor_id},
		function(resp) {
			$("#preceptor-modal").empty().append(resp);
			initPreceptorModal();
			throbber.remove();
			trigger.css("opacity", "1");
			
			var title = "Add new preceptor";
			if (preceptor_id) {title = "Edit preceptor";}
			$("#preceptor-modal").dialog('option', 'title', title);
			
			$("#preceptor-modal").dialog("open");
			$("#preceptor_first").blur();
		}
	);
	
} // end openPreceptorModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the add/edit preceptor modal
 * 	This includes adding some masking/fancy focus to the form elements
 * 	Also will handle creating the click handles for the cancel and save buttons
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorModal = function()
{
	// are we editing?
	var preceptor_id = null;
	if ($("#preceptor_id").length > 0) {preceptor_id = $("#preceptor_id").val();}
	
	// add some cool styling/masking
	$("#preceptor-modal-buttons").find("a").button();
	$("#preceptor-modal").find(".fancy-input").focus(function(){$(this).addClass("fancy-input-focus");});
	$("#preceptor-modal").find(".fancy-input").blur(function(){$(this).removeClass("fancy-input-focus");});
	
	if ($("#preceptor_home").hasClass("add-masking")) {
		$("#preceptor_home").mask("999-999-9999");
		$("#preceptor_work").mask("999-999-9999? x9999");
		$("#preceptor_pager").mask("999-999-9999");
	}
	
	// the click handler for the cancel button
	$("#cancel-preceptor-modal").click(function(e){e.preventDefault();$("#preceptor-modal").dialog("close");});
	
	// the click handler for the save button
	$("#save-preceptor-modal").click(function(e){
		e.preventDefault();
		
		// to prevent the double click bug
		if ($("#preceptor-modal-buttons").find("a").first().css("opacity") != 0) {
			
			// get rid of exisiting errors
			$("#preceptor-modal").find(".error").remove();
			$("#preceptor-modal").find(".invalid-data-error").removeClass("invalid-data-error");
			
			// now hide the buttons an append a throbber
			$("#preceptor-modal-buttons").find("a").css("opacity", "0");
			$("#preceptor-modal-buttons").append(getThrobber("save-preceptor-modal-throbber"));
			$("#save-preceptor-modal-throbber").fadeIn();
			
			// do our ajax request to save the data
			$.post("/account/sites-ajax/save-preceptor",
				   {"preceptor_id" : preceptor_id, "site_id" : getCurrentSiteId(), "form_data" : getPreceptorModalFormData()},
					function(resp) {
						
						// if it wasn't successful, append the errors and add classes to the invalid elements
						if (resp['success'] == "false") {
							$("#preceptor-modal").prepend(resp['result']);
							$.each(resp['form_elements_with_errors'], function(i, v){
								$("#" + v).addClass("invalid-data-error");
							});
						}
						// was successful, empty/add the new accordion & highlight the change/addition
						else {
							$("#preceptor-accordion").empty().hide().append(resp['result']).fadeIn();
							$("#preceptor-modal").dialog("close");
							initPreceptorAccordion();
							$("#preceptor_accordion_" + resp['new_preceptor_id']).effect("highlight");
						}
						
						// put the action buttons back and remove the throbber
						$("#preceptor-modal-buttons").find("a").animate({opacity: 1});
						$("#save-preceptor-modal-throbber").remove();
						
					} // end response function
			); // end post()
			
		} // end the double click bug check
		
	}); // end save click handler
	
	
} // end initPreceptorModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the merge preceptors modal
 * 	This includes adding some click hanlders for selecting a preceptor row
 * 	Also will handle creating the click handles for the cancel and save buttons
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initMergePreceptorsModal = function()
{
	// initialize some buttons and what not
	$("#cancel-preceptor-merge-btn").button();
	$("#do-preceptor-merge-btn").button();
	$(".preceptor-input").hide();

	// add the click handler to the rows so the user can select a preceptor
	$("#mergePreceptorsDialog").find("tr.preceptor-row").click(function(event) {
		event.preventDefault();
		var preceptorId = $(this).attr('id');
		$("tr.preceptor-row").removeClass('selected');
		$(".preceptor-input :input").attr('checked', false);
		$(this).addClass('selected');
		$('#target_preceptor-'+preceptorId).attr('checked', true);
	});
	
	// click handler for hte cancel button
	$('#cancel-preceptor-merge-btn').click(function(e) {e.preventDefault();$('#mergePreceptorsDialog').dialog('close');});

	// click handler for the save button
	$('#do-preceptor-merge-btn').click(function(e) {
		e.preventDefault();
		
		// to prevent the double click bug
		if ($('#do-preceptor-merge-btn').css("opacity") != 0) {
			
			// remove any existing errors
			$("#mergePreceptorsDialog").find(".error").remove();
			$("#mergePreceptorsDialog").find(".prompt-error").removeClass("prompt-error");
			
			// get the data to submit
			var postValues = $('#mergePreceptorsDialog form').serialize();
			postValues['target_preceptor'] = $(".preceptors-table").find(".selected").attr("id");
			
			// hide the buttons & add a throbber
			var cancelBtn = $('#cancel-preceptor-merge-btn').css("opacity", "0");
			var mergeBtn = $('#do-preceptor-merge-btn').css("opacity", "0");
			mergeBtn.parent().append(getThrobber("mergeModalThrobber"));
			
			// submit our ajax request to actually merge the preceptors
			$.post("/account/sites-ajax/merge-preceptors",
				postValues,
				function (resp) {
					
					// if there's an error append that and add classes
					if (resp['success'] == "false") {
						$("#mergePreceptorsDialog").prepend("<div class='error'>" + resp['process_res'] + "</div>");
						$("#mergePreceptorsDialog").find("label").addClass("prompt-error");
					}
					
					// on success, append our new accordion
					else {
						$("#preceptor-accordion").empty().hide().html(resp['html_res']).fadeIn();
						$("#mergePreceptorsDialog").dialog("close");
						initPreceptorAccordion();
						$("#preceptor_accordion_" + resp['process_res']).effect("highlight");
					}
					
					// reset our buttons and remove the throbber
					cancelBtn.animate({opacity:1});mergeBtn.animate({opacity:1});$("#mergeModalThrobber").remove();
					
				} // end response function
				
			) // end post()
		
		} // end if to prevent double click bug
		
	}); // end save click handler
	
	
} // end initMergePreceptorsModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Gets the preceptor modal form data and formats it in a way that the form will be able to process
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getPreceptorModalFormData = function()
{
	var form_data = {};
	form_data['preceptor_first'] = $("#preceptor_first").val();
	form_data['preceptor_last'] = $("#preceptor_last").val();
	form_data['preceptor_work'] = $("#preceptor_work").val();
	form_data['preceptor_home'] = $("#preceptor_home").val();
	form_data['preceptor_pager'] = $("#preceptor_pager").val();
	form_data['preceptor_email'] = $("#preceptor_email").val();
	return form_data;

} // end getPreceptorModalFormData()