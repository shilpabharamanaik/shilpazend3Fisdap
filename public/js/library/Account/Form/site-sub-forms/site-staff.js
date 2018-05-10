

// ----------------------------------------------------------------------------------------------------------------------------------
// Initializes the site staff form, called from /js/account/sites/edit.js
// ----------------------------------------------------------------------------------------------------------------------------------
var initSiteStaffFormFunctions = function()
{
    initSiteStaffDialog();
	initDeleteSiteStaffDialog();

    initAddStaffMemberButton();
    initSiteStaffSearch();
    initAccordion($("#site-staff-accordion"));
    initEditStaffMemberLink();
	initDeleteStaffMemberLink();


    initExpandCollapseAllLinks($("#expand-all-staff-members"), $("#collapse-all-staff-members"), $("#site-staff-accordion"));

}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Pretty simple: initializes the delete site staff member confirmation dialog
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initDeleteSiteStaffDialog = function()
{
	$("#delete-staff-member-modal").dialog({
		modal: true,
		autoOpen: false,
		title: "Delete staff member",
		width:455
	});

	$("#delete-staff-member-modal-buttons").find("a").button();

	// click handler for the cancel button
	$("#cancel-delete-staff-member-modal").click(function(e){e.preventDefault();$("#delete-staff-member-modal").dialog("close");});

	// click handler for the save button
	$("#delete-staff-member").click(function(e){
		e.preventDefault();

		// now hide the buttons an append a throbber
		$("#delete-staff-member-modal-buttons").find("a").css("opacity", "0");
		$("#delete-staff-member-modal-buttons").append("<img class='modal-throbber' src='/images/throbber_small.gif'>");

		// do our ajax request to save the data
		var delete_staff_member_id = $("#delete_staff_member_id").val();
		$.post("/account/sites-ajax/delete-staff-member", {"staff_member_id": delete_staff_member_id},
			function(resp) {

				// remove the staff member from the accordion
				$("#staff-member-"+delete_staff_member_id).remove();
				initAccordion($("#site-staff-accordion"));
				$("#delete-staff-member-modal").dialog("close");

				// put the action buttons back and remove the throbber
				$("#delete-staff-member-modal-buttons").find("a").animate({opacity: 1});
				$("#delete-staff-member-modal-buttons img.modal-throbber").remove();

			} // end response function
		); // end post()
	}); // end save click handler
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Pretty simple: initializes the new/edit site staff member dialog
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initSiteStaffDialog = function()
{
    $("#staff-member-modal").dialog({
        modal: true,
        autoOpen: false,
        title: "Add new staff member",
        width:455
    });
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click function for the "Add base" button
 * 	Does some styling/adds a throbber than calls openBaseModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAddStaffMemberButton = function()
{
    $("#add-staff-member-btn").click(function(e){
        e.preventDefault();
        var trigger = $(this);
        openStaffMemberModal(trigger);
    });

} // end initAddBaseButton()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the search box for the site staff accordion
 * 	Handles some styling with the "first" element by using updateAccordionTopElementBorder() found in /js/account/sites/edit.js
 * 	Also will display an error message if no results found
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initSiteStaffSearch = function()
{
    // get the search back to a null state
    $("#search_staff_members").val("").trigger("keyup").focus().blur();

	$("#search_staff_members").fieldtag().keyup(function(){
		
		$("#site-staff-accordion").find(".accordion_element").show();
		
		if ($(this).val() != "") {
			$("#site-staff-accordion").find(".accordion_element").hide();
			$("#site-staff-accordion").find(".accordion_element .accordion_staff_member_name_wrapper:contains('" + $(this).val() + "')").parents(".accordion_element").show();
		}
		
		updateAccordionTopElementBorder($("#site-staff-accordion"));
		toggleNoResultsMsg($("#site-staff-accordion"), 'No staff members with the name "' + $(this).val() + '" were found.');
		
	});
	
} // end initBaseSearch()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click handlers for the "edit" links within the base accordion
 * 	Uses event delegation to make things easy
 * 	Will do styling/append a throbber then call openStaffMemberModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initEditStaffMemberLink = function()
{
	$('#site-staff-accordion').on('click','.edit_staff_member',function(e) {
		e.preventDefault();
		var trigger = $(this);
		openStaffMemberModal(trigger, $(this).attr("data-staffMemberId"));
	});

} // end initEditStaffMemberLink()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the click handlers for the "delete" links within the base accordion
 * 	Uses event delegation to make things easy
 * 	Will do styling/append a throbber then call openBaseModal()
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initDeleteStaffMemberLink = function()
{
	$('#site-staff-accordion').on('click','.delete-staff-member',function(e) {
		e.preventDefault();
		openDeleteStaffMemberModal($(this).attr("data-staffMemberId"));
	});

} // end initDeleteStaffMemberLink()


 // ---------------------------------------------------------------------------------------------------------------------------------- 
 //  
 //              			The following functions are for the site staff add/edit modal
 //  
 // ----------------------------------------------------------------------------------------------------------------------------------
 
 
 /*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Opens the add/edit staff member modal
 * 	Sends an ajax request to /account/sites-ajax/generate-staff-member-modal
 * 	Does some styling to the trigger/removes the throbber then opens the modal
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var openStaffMemberModal = function(trigger, staff_member_id)
{
    trigger.css("opacity", "0").parent().append("<img class='load-modal-throbber' src='/images/throbber_small.gif'>");

	$.post("/account/sites-ajax/generate-staff-member-modal", {"site_id" : getCurrentSiteId(), "staff_member_id" : staff_member_id},
		function(resp) {
			$("#staff-member-modal").empty().append(resp);
			initStaffMemberModal();
			$("img.load-modal-throbber").remove();
			trigger.css("opacity", "1");
			
			var title = "Add new staff member";
			if (staff_member_id) {
                title = "Edit staff member";
            }
			$("#staff-member-modal").dialog('option', 'title', title);
			
			$("#staff-member-modal").dialog("open");
			$("#staff_member_name").blur();
		}
	);
	
} // end openStaffMemberModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the add/edit staff member modal
 * 	This includes adding some masking/fancy focus to the form elements
 * 	Also will handle creating the click handles for the cancel and save buttons
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initStaffMemberModal = function()
{
	var staff_member_id = null;

    // are we editing?
	if ($("#staff_member_id").length > 0) {
        staff_member_id = $("#staff_member_id").val();
    }
	
	// do some styling/intializing of fancy elements
	$("#staff-member-modal-buttons").find("a").button();
	$("#staff-member-modal").find(".fancy-input").focus(function(){$(this).addClass("fancy-input-focus");});
	$("#staff-member-modal").find(".fancy-input").blur(function(){$(this).removeClass("fancy-input-focus");});
	$("#staff_member_bases").chosen();

	// add phone masking (class check weeds out non-US addresses)
	if ($("#staff_member_phone").hasClass("add-masking")) {
		$("#staff_member_phone").mask("999-999-9999? x9999");
		$("#staff_member_pager").mask("999-999-9999");
	}
	
	// click handler for the cancel button
	$("#cancel-staff-member-modal").click(function(e){e.preventDefault();$("#staff-member-modal").dialog("close");});
	
	// click handler for the save button
	$("#save-staff-member-modal").click(function(e){
		e.preventDefault();
		
		// get rid of any existing errors
		$("#staff-member-modal").find(".error").remove();
		$("#staff-member-modal").find(".invalid-data-error").removeClass("invalid-data-error");
		
		// now hide the buttons an append a throbber
		$("#staff-member-modal-buttons").find("a").css("opacity", "0");
		$("#staff-member-modal-buttons").append("<img class='modal-throbber' src='/images/throbber_small.gif'>");
		
		// do our ajax request to save the data
		var formValues = $("#staff-member-modal form").serializeArray();
		formValues.push({"name": "site_id", "value": getCurrentSiteId()});
		$.post("/account/sites-ajax/save-staff-member", formValues,
			function(resp) {
				
				// if it wasn't successful, append the errors and add classes to the invalid elements
				if (resp['success'] == "false") {
					$("#staff-member-modal").prepend(resp['result']);
					$.each(resp['form_elements_with_errors'], function(i, v){
						$("#" + v).addClass("invalid-data-error");
					});
				}
				
				// was successful, empty/add the new accordion & highlight the change/addition
				else {
					// update the accordion
					if (staff_member_id > 0) {
						var row = $("#staff-member-" + resp['new_staff_member_id']);
						$(row).replaceWith(resp['result']).fadeIn();
					} else {
						$("#site-staff-accordion").append(resp['result']).fadeIn();
					}
					sortStaffMembers($("#site-staff-accordion"));
					$("#staff-member-modal").dialog("close");
                    initAccordion($("#site-staff-accordion"));
					$("#site_staff_accordion_" + resp['new_staff_member_id']).effect("highlight");
				}
				
				// put the action buttons back and remove the throbber
				$("#staff-member-modal-buttons").find("a").animate({opacity: 1});
				$("#staff-member-modal-buttons img.modal-throbber").remove();

			} // end response function
		); // end post()
	}); // end save click handler
	
	
} // end initStaffMemberModal()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Opens the delete staff member modal
 * 	Does some styling to the trigger/removes the throbber then opens the modal
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var openDeleteStaffMemberModal = function(staff_member_id)
{
	$("#delete_staff_member_id").val(staff_member_id);
	$("#delete-staff-member-modal").dialog("open");
	$("#delete-staff-member").blur();

} // end openStaffMemberModal()

var sortStaffMembers = function(accordion)
{
	var sortedList = $(accordion).find(".accordion_element").sort(sortList);
	$(accordion).empty().append(sortedList);

} // end sortStaffMembers()

function sortList(a, b) {
	return ($(a).attr("data-sortstring").toLowerCase() < $(b).attr("data-sortstring").toLowerCase()) ? 1 : -1;
}