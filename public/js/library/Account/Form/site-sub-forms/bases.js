

// ----------------------------------------------------------------------------------------------------------------------------------
// Initializes the bases form, called from /js/account/sites/edit.js
// ----------------------------------------------------------------------------------------------------------------------------------
var initBaseFormFunctions = function()
{
	initBaseAccordion();
	initEditBaseLink();
	initMergeButton();
	initBaseSearch();
	initBaseDialog();
	initAddBaseButton();
	initAllBasesActionLinks();
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the base accordion
 * 	Makes use of the general initAccordion function found in /js/account/sites/edit.js
 * 	Is also called after a successful ajax request for editing/adding a new base
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initBaseAccordion = function()
{
	initAccordion($("#base-accordion"));
	initBaseToggleActive();
	intiBasesSelectCheckboxes();
	checkActiveBasesCount();
	
	// get the search back to a null state
	$("#search_bases").val("").trigger("keyup").focus().blur();
	calculateMergeEnable();
	
} // end initBaseAccordion()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the merge bases button
 * 	Will get the IDs of each visible, checked rows
 * 	Also checks to be sure the button is enabled
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initMergeButton = function() {
	
	$("#merge-bases").click(function(e) {
		e.preventDefault();
		var bases = {};
		var merge_btn = $(this);
		
		if (!$(this).hasClass("ui-state-disabled")) {
			// get selected bases/departments
			$(".merge-base-checkbox").each(function(){
				if ($(this).prop("checked")) {bases[$(this).attr("data-baseid")] = $(this).attr("data-basename");}
			});
			
			merge_btn.css("opacity", "0").parent().append(getThrobber("merge-bases-btn-throbber"));
			
			$.post("/account/sites-ajax/generate-merge-base-form",
				{ 'bases' : bases,
				  'site_id' : getCurrentSiteId() },
				function(resp) {
					$("#mergeBasesDialog").html($(resp).html());
					$("#mergeBasesDialog").dialog("open");
					initMergeBasesModal();
					$('#merge-bases-btn-throbber').remove();
					merge_btn.css("opacity", "1");
				}
			);
			
		} // end if not disabled
		
	}); // end click handler
	
} // end initMergeButton()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the search box for the bases accordion
 * 	Handles some styling with the "first" element by using updateAccordionTopElementBorder() found in /js/account/sites/edit.js
 * 	Also will display an error message if no results found
 * 	And will call calculateBaseMergeEnable() to enable/disable the merge button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initBaseSearch = function()
{
	// dept vs base
	var basedept = $("#search_bases").attr("data-basedept");
	
	$("#search_bases").fieldtag().keyup(function(){
		
		$("#base-accordion").find(".accordion_element").show();
		
		if ($(this).val() != "") {
			$("#base-accordion").find(".accordion_element").hide();
			$("#base-accordion").find(".accordion_element .accordion_base_name_wrapper:contains('" + $(this).val() + "')").parents(".accordion_element").show();
		}
		
		updateAccordionTopElementBorder($("#base-accordion"));
		toggleNoResultsMsg($("#base-accordion"), 'No ' + basedept + 's with the name "' + $(this).val() + '" were found.');
		calculateMergeEnable();
		
	});
	
} // end initBaseSearch()



/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Pretty simple: initializes the bases dialog
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initBaseDialog = function()
{
	$("#base-modal").dialog({ modal: true,autoOpen: false,title: "Add " + $("#base-accordion").attr("data-basedepartment"),width:455});
}


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click function for the "Add base" button
 * 	Does some styling/adds a throbber than calls openBaseModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAddBaseButton = function()
{
	$("#new-base-trigger,#new-department-trigger").click(function(e){
		e.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0").parent().append(getThrobber("load-new-base-modal-throbber"));
		openBaseModal(trigger, $("#load-new-base-modal-throbber"));
	});
	
} // end initAddBaseButton()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click function for each of the 'all action links'
 * These include: expand/collapse all (for bases only), and activate/deactivate all
 * Makes use of a generic accordion functions found in /js/account/sites/edit.js
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAllBasesActionLinks = function()
{
	if ($("#expand-all-bases").length > 0) {
		initExpandCollapseAllLinks($("#expand-all-bases"), $("#collapse-all-bases"), $("#base-accordion"));
	}
	
	initActivateDeactiveAllLinks($("#activate-all-bases"), $("#deactivate-all-bases"), $("#base-accordion"));
	
} // end initAllBasesActionLinks()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the change function for bases active checkboxes
 * 	Sends an AJAX request to /account/site-ajax/toggle-base to save on change
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initBaseToggleActive = function()
{
	var site_id = getCurrentSiteId();

	$("#base-accordion").find(".accordion_active_checkbox_wrapper").find("input[type='checkbox']").each(function(){
		
		$(this).change(function(){
			var base_id = $(this).attr("data-baseid");
			var new_association = $(this).attr("new_association");
			var active_checkbox = $(this);
			
			$.post("/account/sites-ajax/toggle-base", {"base" : base_id, "site" : site_id, "new_association": new_association},
				function(resp) {
                    // if we get a base id back, it means we've created a new base
					if (resp != true) {
                        // update the toggle switch and the edit button
						$("#active_checkbox_" + base_id + "_new_default").attr("data-baseid", resp);
                        $("#base_accordion_" + base_id + "_new_default").parent().find(".accordion_element_content a.edit_base").attr("data-baseid", resp);
					}
					if (new_association) {
						active_checkbox.removeAttr("new_association");
					}
					
					checkActiveBasesCount();
				}
			);
		});
		
	});
	
} // end initBaseToggleActive()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the change function for base select checkboxes
 * 	As the user checks/unchecks we need to call calculateMergeEnable() to enable/disable the merge button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var intiBasesSelectCheckboxes = function()
{
	$("#base-accordion").find(".accordion_select_checkbox_wrapper").find("input[type='checkbox']").change(function(){
		calculateMergeEnable();
	});
	
} // end intiBasesSelectCheckboxes()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click handlers for the "edit" links within the base accordion
 * 	Uses event delegation to make things easy
 * 	Will do styling/append a throbber then call openBaseModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initEditBaseLink = function()
{
	$('#base-accordion').on('click','.edit_base',function(e) {
		e.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0");
		trigger.parent().append(getThrobber("edit-base-throbber"));
		openBaseModal(trigger, $("#edit-base-throbber"), $(this).attr("data-baseid"));
	});
	
} // end initEditBaseLink()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * If the number of check bases is greater than 1:
 * enable the merge bases button otherwise, disable it
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var calculateMergeEnable = function()
{
	$("#merge-bases").removeAttr("style");
	if (getNumberOfCheckedBases() > 1) {$("#merge-bases").button("enable");}
	else {$("#merge-bases").button("disable");}
	
} // end calculateMergeEnable()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Counts up the number of visible checked non-standard bases/departments
 * 	Used in a few places, mostly for enabling/disabling the merge bases button
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getNumberOfCheckedBases = function()
{
	var count = 0;
	
	$("#base-accordion").find(".accordion_select_checkbox_wrapper").find("input[type='checkbox']").each(function(){
		if ($("#base_accordion_" + $(this).attr("data-baseid")).parent().css("display") != "none") {
			if ($(this).prop("checked")) {if (!$(this).hasClass("standard-department")) {count++;}}
		}
	});
	
	return count;
	
} // end getNumberOfCheckedBases()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Counts up the number of active bases/departments.
 * 	If there are zero, a warning message will appear letting hte user know they can't use
 * 	this site without at lesat 1 active base/department.
 * 	Will hide the message if at least 1.
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var checkActiveBasesCount = function()
{
	var count = 0;
	$("#base-accordion").find(".accordion_active_checkbox_wrapper").find(".slider-frame").each(function(){
		if ($(this).find(".on").length > 0) {count++;}
	});
	
	if (count == 0) {$("#no_active_bases_warning").slideDown();}
	else {$("#no_active_bases_warning").slideUp("fast");}
	
} // end checkActiveBasesCount()




 // ---------------------------------------------------------------------------------------------------------------------------------- 
 //  
 //              			The following functions are for the base modals (add/edit/merge) 					  
 //  
 // ----------------------------------------------------------------------------------------------------------------------------------
 
 
 /*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Opens the add/edit base modal
 * 	Sends an ajax request to /account/sites-ajax/generate-base-modal
 * 	Does some styling to the trigger/removes the throbber then opens the modal
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var openBaseModal = function(trigger, throbber, base_id)
{
	$.post("/account/sites-ajax/generate-base-modal",
        {"site_id" : getCurrentSiteId(), "base_id" : base_id},
		function(resp) {
			$("#base-modal").empty().append(resp.modalHTML);
			initBaseModal();
			throbber.remove();
			trigger.css("opacity", "1");
			
			var title = "Add new " + $("#base-accordion").attr("data-basedepartment");
			if (base_id) {title = "Edit " + $("#base-accordion").attr("data-basedepartment");}
			$("#base-modal").dialog('option', 'title', title);
			
			$("#base-modal").dialog("open");
			$("#base_name").blur();

            // if we just created a new default department, we need to
            // update the toggle switch and the edit button
            if (resp.newDefault) {
                $("#active_checkbox_" + base_id).attr("data-baseid", resp.baseId);
                $("#base_accordion_" + base_id).parent().find(".accordion_element_content a.edit_base").attr("data-baseid", resp.baseId);
            }
		}
	);
	
} // end openBaseModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the add/edit base modal
 * 	This includes adding some masking/fancy focus to the form elements
 * 	Also will handle creating the click handles for the cancel and save buttons
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initBaseModal = function()
{
	var base_id = null;

    //Intialize the one flippy doodad
    initFlippyDivs();

    // are we editing? what of site is this?
	if ($("#base_id").length > 0) {base_id = $("#base_id").val();}
	
	// do some styling/intializing of fancy elements
	$("#base-modal-buttons").find("a").button();
	$("#base-modal").find(".fancy-input").focus(function(){$(this).addClass("fancy-input-focus");});
	$("#base-modal").find(".fancy-input").blur(function(){$(this).removeClass("fancy-input-focus");});
	$("#base_state").chosen();

    // add 'optional' tags to the optional inputs
    $("#base-modal").find("label.optional").each(function(){
        var label = $(this).html();
       $(this).html(label+" <span class='form-desc'>(optional)</span>");
    });
	
	// click handler for the cancel button
	$("#cancel-base-modal").click(function(e){e.preventDefault();$("#base-modal").dialog("close");});
	
	// click handler for the save button
	$("#save-base-modal").click(function(e){
		e.preventDefault();
		
		// get rid of any existing errors
		$("#base-modal").find(".error").remove();
		$("#base-modal").find(".invalid-data-error").removeClass("invalid-data-error");
		
		// now hide the buttons an append a throbber
		$("#base-modal-buttons").find("a").css("opacity", "0");
		$("#base-modal-buttons").append(getThrobber("save-base-modal-throbber"));
		$("#save-base-modal-throbber").fadeIn();
		
		// do our ajax request to save the data
		$.post("/account/sites-ajax/save-base", {"base_id" : base_id, "site_id" : getCurrentSiteId(), "form_data" : getBasesModalFormData()},
			function(resp) {
				
				// if it wasn't successful, append the errors and add classes to the invalid elements
				if (resp['success'] == "false") {
					$("#base-modal").prepend(resp['result']);
					$.each(resp['form_elements_with_errors'], function(i, v){
						$("#" + v).addClass("invalid-data-error");
					});
				}
				
				// was successful, empty/add the new accordion & highlight the change/addition
				else {
					$("#base-accordion").empty().hide().append(resp['result']).fadeIn();
					$("#base-modal").dialog("close");
					initBaseAccordion();
					$("#base_accordion_" + resp['new_base_id']).effect("highlight");
				}
				
				// put the action buttons back and remove the throbber
				$("#base-modal-buttons").find("a").animate({opacity: 1});
				$("#save-base-modal-throbber").remove();
				
			} // end response function
		); // end post()
	}); // end save click handler
	
	
} // end initBaseModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the merge bases modal
 * 	This includes adding some click hanlders for selecting a base/department row
 * 	Also will handle creating the click handles for the cancel and save buttons
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initMergeBasesModal = function()
{
	
	// initialize some buttons and what not
	$("#cancel-merge-btn").button();
	$("#do-merge-btn").button();
	$(".base-input").hide();

	// add the click handler to the rows so the user can select a preceptor
	$("#mergeBasesDialog").find("tr.base-row").click(function(event) {
		event.preventDefault();
		var baseId = $(this).attr('id');
		$("tr.base-row").removeClass('selected');
		$(".base-input :input").attr('checked', false);
		$(this).addClass('selected');
		$('#target_base-'+baseId).attr('checked', true);
	});

	// click handler for the cancel button
	$('#cancel-merge-btn').click(function(e) {e.preventDefault();$('#mergeBasesDialog').dialog('close');});

	// click handler for the save button
	$('#do-merge-btn').click(function(e) {
		e.preventDefault();
		
		// remove any existing errors
		$("#mergeBasesDialog").find(".error").remove();
		$("#mergeBasesDialog").find(".prompt-error").removeClass("prompt-error");
		
		// get the data to submit
		var postValues = $('#mergeBasesDialog form').serialize();
		
		// hide the buttons & add a throbber
		var cancelBtn = $('#cancel-merge-btn').animate({opacity:0});
		var mergeBtn = $('#do-merge-btn').animate({opacity:0});
		mergeBtn.parent().append(getThrobber("mergeModalThrobber"));
		
		// submit our ajax request to actually merge the preceptors
		$.post("/account/sites-ajax/merge-bases",
			postValues,
			function (resp) {
				
				// if there's an error append that and add classes
				if (resp['success'] == "false") {
					$("#mergeBasesDialog").prepend("<div class='error'>" + resp['process_res']['target_base'] + "</div>");
					$("#mergeBasesDialog").find("label").addClass("prompt-error");
				}
				
				// on success, append our new accordion
				else {
					$("#base-accordion").empty().hide().html(resp['html_res']).fadeIn();
					$("#mergeBasesDialog").dialog("close");
					initBaseAccordion();
					$("#base_accordion_" + resp['process_res']).effect("highlight");
				}
				
				// reset our buttons and remove the throbber
				cancelBtn.animate({opacity:1});mergeBtn.animate({opacity:1});$("#mergeModalThrobber").remove();
				
			} // end response function
			
		) // end post()
		
	}); // end save click handler
	
	
} // end initMergeBasesModal()



/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Gets the preceptor modal form data and formats it in a way that the form will be able to process
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getBasesModalFormData = function()
{
	
	var form_data = {};
	form_data['base_name'] = $("#base_name").val();
	form_data['activate_bases'] = $("#activate_bases").val();

	form_data['base_address'] = $("#base_address").val();
	form_data['base_city'] = $("#base_city").val();
	form_data['base_state'] = $("#base_state").val();
	form_data['base_zip'] = $("#base_zip").val();
	
	return form_data;

} // end getBasesModalFormData()