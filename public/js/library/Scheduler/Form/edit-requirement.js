$(function(){
	// initialize everything!
	// these are edit specific initializations, but there is stil a lot being done in requirement.js
	initManageLinks();
	triggerFlippys();
	initUnassignButton();
	initDueDateActions();
	initUndoLinks();
	initCheckboxSelectorActions();
	initAttachmentsRowClick();
	initRemoveExistingItemsLinks();
	initHTMLClickHandler();
	intitConflictDetailsTrigger();
	initAttachmentsSearch();
	initRowDueDateChange();
	
	$("#edit-custom-title").click(function(e){
		e.preventDefault();
		
		if ($(this).css("display") != "none") {
			$(this).fadeOut();
			$(".top-wrapper").find("h3").css("color", "#fff");
			$("#custom-default-label").css("color", "#fff");
			$("#custom_title").fadeIn("fast");
		}
	});
	
});


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * For the triggers of the collapsable content (for sites and attachments)
 * This will also handle our null case of attahcments
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initManageLinks = function()
{
	var total_people = 0;
	$(".attachment-summary").find("b").each(function(){
		total_people += parseInt($(this).text());
	})

	if (total_people == 0) {
		$("#attachment-buttons").hide();
		$("#unassign-errors").hide();
		$(".existing_attachments").hide();
		$(".specific-people-content").css("margin-top", "0.5em");
	}
	
	$(".manage-links").click(function(e){
		e.preventDefault();
		var controlling = $("#" + $(this).attr("data-divtoopen"));
		var second_part_of_txt = $(this).text().split(" ");
		second_part_of_txt = second_part_of_txt[1];
		
		if (controlling.css("display") == "block") {
			controlling.slideUp();
			$(this).text("Manage " + second_part_of_txt);
		}
		else {
			controlling.slideDown();
			$(this).text("Hide " + second_part_of_txt);
		}
	});
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * If a due date is changed (individually), we need to make the row appear compliant.
 * This function will inititalize that handler
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initRowDueDateChange = function()
{
	$("#attachments").find("tr").each(function(){
		var row = $(this);
		row.find(".selectDate").change(function(){
			// remove anything about being past due since we can only choose due dates in the future
			// if the date is equal to today, make the row appear non-compliant, otherwise, make it appear non-compliant
			if ($(this).val() == getTodayString()) {
				makeRowAppearNonCompliant(row, false);
			}
			else if (row.hasClass("non-compliant")) {
				makeRowAppearCompliant(row, false);
			}
		})
	});
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Used by our due date change functions. We need to compare today to our due date values.
 * This function takes today's date and puts it into the same format as our due date inputs.
 * Returns a string
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getTodayString = function()
{
	var today = new Date();
	var day = new String(today.getDate());
	var month = new String(today.getMonth()+1);
	var year = new String(today.getFullYear());
	if(month.length == 1){month = "0" + month;}
	if(day.length  == 1){day = "0" + day;}
	return month + "/" + day + "/" + year;
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the search box for the attachments list
 * This will hide rows with names that do not contain the value in the search text box
 * Change occurs as the user types
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAttachmentsSearch = function()
{
	var place_holder_txt = $("#search-attachments-place-holder-text");
	place_holder_txt.click(function(e){$("#search_attachments").trigger("focus");})
	
	// start with making the default text on the search text box show/hide
	$("#search_attachments").focus(function(){
		place_holder_txt.hide();
	}).blur(function(){
		if ($(this).val() == "") {place_holder_txt.fadeIn();}
		else {place_holder_txt.hide();}
		
	}).keyup(function(){
		
		var searchTerm = $(this).val();
		var found_one_already = false;
		
		// now do the actual search
		$(".existing_attachments").find(".notice").remove();
		if (searchTerm == null) {searchTerm = "";}
		searchTerm = searchTerm.toLowerCase();
		
		$("#attachments").find("tr").hide().css("border-top", "1px").each(function(){
			if ($(this).find(".name-cell").text().toLowerCase().indexOf(searchTerm) != -1){
				if (!found_one_already) {
					$(this).css("border-top", "0px");
					found_one_already = true;
				}
				$(this).fadeIn("fast");
			}
		});
		
		if (!found_one_already) {
			// all rows are hidden, give the user an notice
			$(".existing_attachments").append('<div class="notice">No results for "' + searchTerm + '". Please try another search.</div>').find(".notice").fadeIn();
		}
		
		updateActionButtons();
	});

} // end initAttachmentsSearch()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the click handler for the 'details' link on the multple unassign conflicts notice div
 * and the multiple add site conflicts notice div will slide-down a list of details when clicked.
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var intitConflictDetailsTrigger = function()
{
	$('#unassign-errors').on('click','.people-conflict-details',function(e) {
		e.preventDefault();
		var details_content = $("#unassign-errors").find(".conflict-details");
		if (details_content.css("display") == "block") {details_content.slideUp();}
		else {details_content.slideDown();}
	});
	
	$('#add-site-errors').on('click','.site-conflict-details',function(e) {
		e.preventDefault();
		var details_content = $("#add-site-errors").find(".conflict-details");
		if (details_content.css("display") == "block") {details_content.slideUp();}
		else {details_content.slideDown();}
	});
	
} // end intitConflictDetailsTrigger()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the click handler for 'html'. This will close any popups when the user clicks off of them
 * (the checkbox selector menu and the due date pop up)
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initHTMLClickHandler = function()
{
	$('html').click(function(e) {
		var target = e.target;
		var openner = "";
		var selector_button = false;
		
		if ($(target).hasClass("ui-icon")) {
			selector_button = true;
			openner = "many-due";
		}
		
		$(target).parents().each(function(){
			if($(this).attr("id") == "many-due" || $(this).attr("id") == "checkbox-selector"){
				selector_button = true;
				openner = $(this).attr("id");
			}
			
			// if the many due date menu is open, allow them to choose anthing within that div.
			if ($("#many-due-date-menu").css("display") == "block") {
				if ($(this).attr("id") == "many-due-date-menu" || $(this).attr("id") == "ui-datepicker-div" || $(this).hasClass("ui-icon")) {
					selector_button = true;
					openner = "many-due";
				}
			}
		});
		
		if (!selector_button) {$("#checkbox-selector-menu, #many-due-date-menu").hide();}
		if (openner == "many-due") {$("#checkbox-selector-menu").hide();}
		else {$("#many-due-date-menu").hide();}
	});
	
} // end initHTMLClickHandler()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the click handlers for the attachment rows. Clicking a row should select it.
 * This function will handle when the user actually clicks the checkbox (so we odn't get the double selection issue)
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initRemoveExistingItemsLinks = function()
{
	$(".remove-existing-site").click(function(e){
		e.preventDefault();
		addToRemovingSiteIds($(this).parent().parent().attr("data-siteid"));
	});
		
	$(".remove-existing-attachment").click(function(e){
		e.preventDefault();
		addToRemovingAttachments($(this).parent().parent(), true);
	});
	
} // end initRemoveExistingItemsLinks()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the click handlers for the attachment rows. Clicking a row should select it.
 * This function will handle when the user actually clicks the checkbox (so we odn't get the double selection issue)
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAttachmentsRowClick = function()
{
	$('#attachments').find(".attachment-checkbox").click(function(e){
		changeAttachmentRowClass($(this).prop('checked'), $(this).parent().parent());
		e.stopPropagation();
	})
	
	$('#attachments').find("tr").click(function(e){
		var target = e.target;
		if (!$(target).hasClass("selectDate") && !$(target).hasClass("remove-existing-attachment") && !$(target).hasClass("undo-remove-attachment")) {
			toggleAttachmentRow(!$(this).find(".attachment-checkbox").prop('checked'), $(this));
		}
	});
	
} // end initAttachmentsRowClick()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the checkbox selector actions:
 * the button to drop down the menu, and the menu option click handlers
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initCheckboxSelectorActions = function()
{
	// the button that opens the menu
	$("#checkbox-selector").button().click(function(e){
		e.preventDefault();
		if ($("#checkbox-selector-menu").css("display") == "block") {$("#checkbox-selector-menu").hide();}
		else {$("#checkbox-selector-menu").fadeIn(100);}
	});
	
	// the menu buttons that will select certain rows
	$("#checkbox-selector-menu").find("a").click(function(e){
		e.preventDefault();
		var selecting = $(this).attr("data-typeid");
		$('#attachments').find(".attachment-checkbox").each(function(){
			var row = $(this).parent().parent();
			var select_it = true;
			
			// should we select this row? Depends on what we're trying to select...
			if (selecting == "all") {select_it = true;}
			else if (selecting == "none") {select_it = false;}
			else if (selecting == "past-due") {select_it = ($(this).attr("data-pastdue") == 1);}
			else {select_it = ($(this).attr("data-selectortypeid") == selecting);}
				
			toggleAttachmentRow(select_it, row);
		});
		
	});
	
} // end initCheckboxSelectorActions()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the 'undo' links for sites and attachments.
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initUndoLinks = function()
{
	// undo remove exisiting site
	$('#sites').on('click','.undo-remove-exisiting',function(e) {
		e.preventDefault();
		var row = $(this).parent().parent();
		var site_id = row.attr("data-siteid");
		var unassigning_conflicts = [];
		unassigning_conflicts = addingSiteConflicts(site_id, unassigning_conflicts);
		if (unassigning_conflicts.length <= 0) {
			removeFromRemovingSiteIds(site_id);
		}
		else {
			appendSingleSiteConflictNotice(row.find(".site_name").text(), unassigning_conflicts);
		}
	});
	
	// undo add new non-exisiting site
	$('#sites').on('click','.undo-add-site',function(e) {
		e.preventDefault();
		removeFromAddingSiteIds($(this).parent().parent().attr("data-siteid"));
	});
	
	// undo remove exisitng attachment
	$('#attachments').on('click','.undo-remove-attachment',function(e) {
		e.preventDefault();
		removeFromRemovingAttachments($(this).parent().parent().attr("data-attachmentid"));
		updateActionButtons();
	});
	
} // end initUndoLinks()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the many due button (disabled by default), the submit due button and cancel button.
 * The submit due button is the blue button within the 'due date' popup. On click it should change the due date value
 * for each selected attachment. It will also remove the 'non-compliant' class for the past due rows
 * Cancel just closes the due date pop up
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initDueDateActions = function()
{
	var many_due_popup = $("#many-due-date-menu");
	
	$("#submit-many-due-date").button().click(function(e){
		e.preventDefault();
		many_due_popup.hide();
		
		$('#attachments').find(".attachment-checkbox").each(function(){
			var row = $(this).parent().parent();
			if ((row.css("display") != "none") && ($(this).prop('checked')) && (row.find(".selectDate").length > 0)) {
				row.find(".selectDate").val($("#many-due-date").val());
				row.effect("highlight");
				
				if ($("#many-due-date").val() == getTodayString()) {
					makeRowAppearNonCompliant(row, true);
				}
				else if (row.hasClass("non-compliant")) {
					makeRowAppearCompliant(row, true);
				}
			}
		});
	});
	
	$("#cancel-many-due-date").button().click(function(e){
		e.preventDefault();
		many_due_popup.hide();
	});
	
	$("#many-due").button().button("disable").click(function(e){
		e.preventDefault();
		if (many_due_popup.css("display") == "block") {many_due_popup.hide();}
		else {many_due_popup.fadeIn(100);}
	});
	
} // end initSubmitDueButton()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Makes an attachment row appear compliant (removes classes, colors, and what not)
 * @param {DOM Element} the non-compliant <tr> to be made compliant
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var makeRowAppearCompliant = function(row, selected)
{
	row.removeClass("non-compliant").removeClass("non-compliant-selected-attachment-row").find(".status-cell").text("");
	if(selected){row.addClass("selected-attachment-row");}
	row.find(".attachment-checkbox").attr("data-pastdue", "0");
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Makes an attachment row appear NON-compliant (adds classes, colors, and what not)
 * @param {DOM Element} the compliant <tr> to be made non-compliant
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var makeRowAppearNonCompliant = function(row, selected)
{
	row.addClass("non-compliant").removeClass("selected-attachment-row").find(".status-cell").text("past due");
	if(selected){row.addClass("non-compliant-selected-attachment-row");}
	row.find(".attachment-checkbox").attr("data-pastdue", "1");
}


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Init the unassign button should be disabled by default, and disabled if 0 attachments are chosen.
 * Create the click event that will step through each selected attachment and unassign it if there are no site conflicts.
 * If conflicts, it will display them.
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initUnassignButton = function()
{
	$("#unassign").button().button("disable").click(function(e){
		
		e.preventDefault();
		var conflict_objects = [];
		
		$("#unassign-errors").find(".notice").remove();
		$('#attachments').find(".attachment-checkbox").each(function(){
			var row = $(this).parent().parent();
			if ((!row.hasClass("hidden-row")) && ($(this).prop('checked'))) {
				// if this isn't already on the removing attachments list
				if (row.find(".removing-description").length <= 0) {
					remove_result = addToRemovingAttachments($(this).parent().parent(), false);
					if(remove_result != true){
						conflict_objects.push(remove_result);
					}
				}
			}
		});
		
		var number_of_conflict_people = conflict_objects.length;
		
		if(number_of_conflict_people > 0){
			
			if (number_of_conflict_people == 1) {appendSinglePersonConflictNotice(conflict_objects[0]['name'], conflict_objects[0]['site_conflicts']);}
			else {
				// we need to explain to the user that some people could not be unassigned.
				var notice_html = "<div class='notice'>";
				notice_html    += 	number_of_conflict_people + " people cannot be unassigned because of the sites they are scheduled to attend. ";
				notice_html    +=   "<a href='#' class='people-conflict-details'>Details</a>";
				notice_html    += 	"<div class='conflict-details'>";
				
				$.each(conflict_objects, function(i, v){
					notice_html +=		"<li>" + getSinglePersonConlfictMessage(v['name'], v['site_conflicts'], true) + "</li>";
				})
				
				notice_html    += 	"</div>";
				notice_html    += "</div>";
				
				$("#unassign-errors").hide().append(notice_html).fadeIn();
			}
			
		}
		
		updateActionButtons();
		
	});
	
} // end initUnassignButton()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Triggers 'clicks' on flippys based on default data from their respective hidden form elements
 * We don't want to leave this just up to the flippy plugin because we need certain DIVs to show and what not
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var triggerFlippys = function()
{	
	// site vs program flippy
	if ($("#regardlessofsite").val() == 0) {
		$("#regardlessofsite_flippy").trigger("click");
	}
	else {
		$("#manage-sites").trigger("click").hide();
	}
	
} // end triggerFlippys()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Returns true/false if the given row is pending unassignment
 * @param {DOM element} row the <tr> of the attachment
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var rowIsPendingUnassignment = function(row)
{
	if (row.find(".pending-removal").length > 0) {
		return true;
	}
	
	return false;

} // end rowIsPendingUnassignment()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Appends a 'notice' div to the unassign-errors div - used when there are attachment unassign site conflicts
 * @param {string} user_name the name of the user associated with the attachment
 * @param {array} site_name_conflicts an array of site names that are causing the conflict for this user
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var appendSinglePersonConflictNotice = function(user_name, site_conflict_names)
{
	var html = "<div class='notice'>" + getSinglePersonConlfictMessage(user_name, site_conflict_names, false) + "</div>";
	$("#unassign-errors").hide().append(html).fadeIn();
	
} // end appendSinglePersonConflictNotice()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Appends a 'notice' div to the add-site-errors div - used when there are attachment unassign/site conflicts
 * @param {string} site_name the name of the site
 * @param {array} user_name_conflicts an array of user names that are causing the conflict for this site
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var appendSingleSiteConflictNotice = function(site_name, user_name_conflicts)
{
	var single_site_conflicts = [];
	
	$.each(user_name_conflicts, function(i, v){
		single_site_conflicts = v;
	});
	
	var html = "<div class='notice'>" + getSiteConflictMessage(site_name, single_site_conflicts) + "</div>";
	$("#add-site-errors").hide().empty().append(html).fadeIn();
	
} // end appendSingleSiteConflictNotice()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * @param {string} site_name the name of the site
 * @param {array} user_name_conflicts an array of user names that are causing the conflict for this site
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getSiteConflictMessage = function(site_name, user_name_conflicts){
	var is_are = "are";
	if (user_name_conflicts.length == 1) {is_are = "is";}
	return site_name + " cannot be added because " + prettyList(user_name_conflicts) + " " + is_are + " scheduled to attend.";
} 

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Returns a list separated by commas. Also includes 'and' before the last item
 * @param {array} collection an array
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var prettyList = function(collection)
{
	var list = "";
	var total_count = collection.length;
	
	if (total_count == 1) {list = collection.join(" ");}
	else if(total_count == 2) {list = collection.join(" and ");}
	else {
		var count = 0;
		$.each(collection, function(i, v){
			if (count != 0) {list += ", ";}
			if (count == total_count) {list += "and ";}
			list += v;
			count++;
		});
	}
	
	return list;
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Returns a string that explains an unassign conflict for a given attachment
 * @param {string} user_name the name of the user associated with the attachment
 * @param {array} site_name_conflicts an array of site names that are causing the conflict for this user
 * @param {bool} short_version true if you don't want to include 'NAME cannot be unassigned because'
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getSinglePersonConlfictMessage = function(user_name, site_conflict_names, short_version)
{	
	if (site_conflict_names) {	
		var first_name = user_name.split(" ");
		first_name = first_name[0];
		var site_list = prettyList(site_conflict_names);
		
		var short_msg = " is scheduled to attend " + site_list + ".";
		if (short_version) {return user_name + short_msg;}
		else {return user_name + " cannot be unassigned because " + first_name + short_msg;}
	}
	
	return "";

} // end getSinglePersonConflictMessage()

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Moves a currently attached user to the 'removing_attachment_ids' element
 * @param {element} row the <tr> element of the attachment
 * @param {bool} display_conflicts if true will append a notice box (only true if we're dealing with 1 at a time)
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addToRemovingAttachments = function(row, display_conflicts)
{
	var user_name = row.find(".name-cell").text();
	var attachment_id = row.attr("data-attachmentid");
	var height = row.height()-3;
	var has_conflict = false;
	
	$("#unassign-errors").find(".notice").remove();
	
	// first see if this user is attending any sites that require this requirement
	if ($("#regardlessofsite").val() == 0){
		
		var users_sites = row.attr("data-userssites");
		if (users_sites) {users_sites = users_sites.split(",");}
		
		// note that this is what is currently on the page -  not necessarily whats in the db
		var site_assocations = getSiteIdsOnEdit(true);
		
		var site_conflict_names = [];
		// now step through each of the users sites and see if one of them is in our site assocations array
		if (users_sites) {
			$.each(users_sites, function(i, v){
				if ($.inArray(v, site_assocations) != -1) {
					// found a conflict
					has_conflict = true;
					
					var site_name = $("#site-table-row-" + v).find(".site_name").text();
					site_conflict_names.push(site_name);
				}
			});
		}
	}
	
	if (has_conflict) {
		if (display_conflicts) {appendSinglePersonConflictNotice(user_name, site_conflict_names);}
		else {return {name: user_name, site_conflicts: site_conflict_names};}
	}
	else {
		row.find("td").addClass("pending-removal");
		row.append("<td colspan='6' class='removing-description' style='height:" + height + "px;'>Unassigning " + user_name + " <a href='#' class='undo-remove-attachment'>undo</a></td>");
		row.find(".removing-description").effect("highlight");
		
		// now add this attachment id to the list of removing_attachment_ids
		addToHiddenCommaSeparatedList($("#removing_attachment_ids"), attachment_id);
	}
	
	return true;

} // end addToRemovingAttachments()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Deals with disabling/enabling the "Due" and "Unassign" buttons
 * This is based on the number of attachments selected.
 * Counts, speficially, ones with a due date input - for the 'due' button only
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var updateActionButtons = function(){
	
	// --------- Step 1: count the number of visible attachments ---------
	//		Only count the rows that are visible, checked, and not pending unassignment
	var count = 0;
	var with_due_date_count = 0;
	
	$('#attachments').find(".attachment-checkbox").each(function(){
		var row = $(this).parent().parent();
		if ((row.css("display") != "none") && ($(this).prop('checked')) && !rowIsPendingUnassignment(row)) {
			count++;
			if (row.find(".selectDate").length > 0) {
				with_due_date_count++;
			}
		}
	});
	
	// --------- Step 2: enable/disable the due date/unassign buttons ---------
	// 		The due date button needs at least 1 selected WITH a due date input
	if (with_due_date_count == 0) {$("#many-due").button("disable");}
	else {$("#many-due").button("enable");}
	if (count == 0) {$("#unassign").button("disable");}
	else {$("#unassign").button("enable");}
	
	// --------- Step 3: Update the info div within the due date popup ---------
	// 		If there are some selected rows without due date inputs, give the user
	//		an info div that explains that. Otherwise, make sure the info div is hidden.
	if (with_due_date_count != count) {
		
		var difference = count-with_due_date_count;
		var plural_completed_attachments_text = difference + " selected people have";
		var plural_completed_attachments_requirement_text = "s";
		
		if (difference == 1) {
			plural_completed_attachments_text = "selected person has";
			plural_completed_attachments_requirement_text = "";
		}
		
		$("#plural-completed-attachments").text(plural_completed_attachments_text);
		$("#some-completed-attachments-selected").find(".plural-completed-attachments-requirement").text(plural_completed_attachments_requirement_text);
		$("#many-due-date-menu").find(".info").fadeIn();
		
	}
	else {
		$("#many-due-date-menu").find(".info").hide();
	}
	 
	// --------- Step 4: Finally, change the 
	if (with_due_date_count == 1) {$("#will-recieve-due-date-count").text("1 person");}
	else {$("#will-recieve-due-date-count").text(with_due_date_count + " people");}
	
} // end updateActionButtons()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Toggles the selection of an attachment row
 * @param {bool} select true if we are selecting the row, false if we are deselecting
 * @param {element} row the <tr> element for the attahcment
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var toggleAttachmentRow = function(select, row)
{
	// if the row is hidden, leave it alone
	if (row.css("display") != "none"){
		row.find(".attachment-checkbox").prop('checked', select);
		changeAttachmentRowClass(select, row);
	}

} // end toggleAttachmentRow()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Changes the class of an attachment row based on if it is selected or not (also depends on compliance status)
 * @param {bool} select true if we are selecting the row, false if we are deselecting
 * @param {element} row the <tr> element for the attahcment
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var changeAttachmentRowClass = function(select, row)
{
	var non_compliant_selected_class = "non-compliant-selected-attachment-row";
	var selected_class = "selected-attachment-row";
	
	if (select) {
		if (row.hasClass("non-compliant")){row.addClass(non_compliant_selected_class);}
		else {row.addClass(selected_class);}
	}
	else {
		row.removeClass(non_compliant_selected_class).removeClass(selected_class);
	}
	
	updateActionButtons();
	
} // end changeAttachmentRowClass()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Handles the submission of the pick sites modal (for edit only, of course)
 * Here mostly to clean up requirement.js and keep that limited to shared functions only
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var submitPickSitesModal = function()
{
	$("#add-site-errors").hide().empty();
	
	// if we're editing a requirement, sites look a bit different...
	var existing_site_ids = $("#existing_site_ids").val().split(",");
	var removing_site_ids = $("#removing_site_ids").val().split(",");
	var adding_site_ids   = $("#adding_site_ids").val().split(",");
	
	// we'll need to compare what is in our modal lists to what we've got in our hidden form elements
	var unassigning_conflicts = {};
	var site_name_of_first_conflict = "";
	var site_id_names = {};
	var number_of_conflicts = 0;
	
	// start by going through what is in the chosen list
	$("#chosen-list").find("option").each(function(){
		var site_id = $(this).val();
		var site_name = $(this).text();
		var site_type = $(this).attr("class");
		
		unassigning_conflicts = addingSiteConflicts(site_id, unassigning_conflicts);
		
		if (!unassigning_conflicts[site_id]) {
			if ($.inArray(site_id, removing_site_ids) != -1) {
				// the user had removed this, but now they are re-adding it. Turn the row back into an existing site id
				removeFromRemovingSiteIds(site_id);
			}
			else if (($.inArray(site_id, existing_site_ids) == -1) && ($.inArray(site_id, adding_site_ids) == -1)) {
				// this site was not an already exisiting assocation AND it isn't already on adding site ids. Create a new row
				// but first, make sure that adding this won't causing an un-assigning conflicts
				addToAddingSiteIds(site_id, site_name, site_type);
			}
		}
		else {
			if(!site_name_of_first_conflict){site_name_of_first_conflict = site_name;}
			site_id_names[site_id] = site_name;
			number_of_conflicts++;
		}
	});
	
	// now go through the available list - we'll have to take action on certain items
	$("#available-list").find("option").each(function(){
		if (!$(this).hasClass("disabled")) {
			var site_id = $(this).val();
			var site_name = $(this).text();
			if (($.inArray(site_id, removing_site_ids) == -1) && ($.inArray(site_id, existing_site_ids) != -1)) {
				// this site is NOT on removing_sites, but it IS on existing sites. We need to put it into a pending-removal state
				addToRemovingSiteIds(site_id);
			}
			else if ($.inArray(site_id, adding_site_ids) != -1) {
				// this site IS on adding_sites, but now it's not in the chosen list, remove it from adding
				removeFromAddingSiteIds(site_id);
			}
		}
	});
	
	if(number_of_conflicts > 0){
		handleManySiteConflicts(number_of_conflicts, site_name_of_first_conflict, unassigning_conflicts, site_id_names);
	}
	
} // end submitPickSitesModal()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Handles the bulk add of sites with conflicts -- called by the 'submit' choose sites modal form
 * @param {Int} number_of_conflicts the number of conflicts we have
 * @param {String} site_name_of_first_conflict the name of the first conflict we encountered
 * @param {Array} unassigning_conflicts the object of arrays of conflicts
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var handleManySiteConflicts = function(number_of_conflicts, site_name_of_first_conflict, unassigning_conflicts, site_id_names)
{
	if (number_of_conflicts == 1) {
		appendSingleSiteConflictNotice(site_name_of_first_conflict, unassigning_conflicts);
	}
	else {
		// we need to explain to the user that some sites could not be removed.
		var notice_html = "<div class='notice'>";
		notice_html    += 	number_of_conflicts + " sites cannot be added because of the people that are scheduled to attend. Reassign them in the table below to solve this. ";
		notice_html    +=   "<a href='#' class='site-conflict-details'>Details</a>";
		notice_html    += 	"<div class='conflict-details'>";
		
		$.each(unassigning_conflicts, function(i, v){
			notice_html +=		"<li>" + getSiteConflictMessage(site_id_names[i], v) + "</li>";
		})
		
		notice_html    += 	"</div>";
		notice_html    += "</div>";
		
		$("#add-site-errors").hide().empty().append(notice_html).fadeIn();
	}
} // end handleManySiteConflicts()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Changes the row styles and moves an existing site association to the 'removing_site_ids' element
 * @param {int} site_id the ID of the site
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addToRemovingSiteIds = function(site_id)
{
	var row = $("#site-table-row-" + site_id);
	var height = row.height()-3;
	var site_name = row.find(".site_name").text();
	
	row.find("td").addClass("pending-removal");
	row.append("<td colspan='4' class='removing-description' style='height:" + height + "px;'>Removing " + site_name + " <a href='#' class='undo-remove-exisiting'>undo</a></td>");
	row.find(".removing-description").effect("highlight");
	
	// now add this site id to the list of removing_site_ids
	addToHiddenCommaSeparatedList($("#removing_site_ids"), site_id);
	
} // end addToRemovingSiteIds()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Changes the row styles and moves either:
 * 		a) an existing site association that IS currently on the "removing_site_ids" list off of the list
 * 		b) an exisiting user/requirement attachment that IS currently on the "removing_attachments" list off of the list
 * 		
 * @param {element} row the <tr> of the site
 * @param {hidden_element} hidden_element the hidden form element to remove from
 * @param {int} id the ID of either the site or the attachment
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var removeFromRemovingElement = function(row, hidden_element, id)
{
	row.find(".removing-description").remove();
	row.find(".pending-removal").removeClass("pending-removal");
	row.effect("highlight");
	removeFromHiddenCommaSeparatedList(hidden_element, id);
	
} // end removeFromRemovingElement()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Calls a function that will change the row styles and moves an existing site association that
 * IS currently on the "removing_site_ids" list off of the list
 * 		
 * @param {int} site_id the ID of the site
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var removeFromRemovingSiteIds = function(site_id){
	$("#add-site-errors").slideUp().empty();
	removeFromRemovingElement($("#site-table-row-" + site_id), $("#removing_site_ids"), site_id);
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Calls a function that will change the row styles and moves an existing attachment that
 * IS currently on the "removing_attachments" list off of the list
 * 		
 * @param {int} attachment_id the ID of the attachment
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var removeFromRemovingAttachments = function(attachment_id){
	removeFromRemovingElement($("#attachment-row-" + attachment_id), $("#removing_attachment_ids"), attachment_id);
}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Determines if there are any pending unassigned users (on the removing_attachment_ids) that are scheduled to attend
 * a given site ID. If they are, this site cannot be 'added' until the user is removed from unassigned.
 * 		
 * @param {int} site_id the ID of the site
 * @param {array} unassigning_conflicts an associative array (keyed by site ID) of user names
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addingSiteConflicts = function(site_id, unassigning_conflicts, return_count_only)
{
	// any conflicts for this site?
	var count = 0;
	if($("#removing_attachment_ids").val()){
		var attachment_ids = $("#removing_attachment_ids").val().split(",");
		$.each(attachment_ids, function(i, v){
			var user_site_ids = $("#attachment-row-" + v).attr("data-userssites").split(",");
			if ($.inArray(site_id, user_site_ids) != -1) {
				// found a conflict
				if (!unassigning_conflicts[site_id]) {
                    unassigning_conflicts[site_id] = [];
					count++;
                }
				unassigning_conflicts[site_id].push($("#attachment-row-" + v).find(".name-cell").text());
			}
		});
	}
	
	if (return_count_only) {
        return count;
	}
	
	return unassigning_conflicts;

} // end addingSiteConflicts()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Handles the case where a user has made this a program requirement (after sites were already added) and has some pending
 * unassignments. This will trigger the 'X' click for sites with conflicts - kind of a wonky user expereince but for MVP will do.
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var handleConflictsForSpecificChange = function()
{
	$("#add-site-errors").hide().empty();

	$("#sites").find("tr").each(function(){
		
		// if this isn't already in a 'removing' state
		if ($(this).find(".pending-removal").length <= 0) {
			var site_id = $(this).attr("data-siteid");
			var unassigning_conflicts_count = addingSiteConflicts(site_id, {}, true);
			
			if (unassigning_conflicts_count > 0) {
				if ($(this).find(".remove-existing-site")) {
					$(this).find(".remove-existing-site").trigger("click");   
				}
				else {
					// undo the add
					$(this).find(".undo-add-site").trigger("click");
				}
			}
		}
		
	});
	
	
} // end handleConflictsForSpecificChange()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Adds a non-exisiting site association to the table and the ID is added to the adding_site_ids element
 * 		
 * @param {int} site_id the ID of the site to be added
 * @param {string} site_name the name of the site, we'll use this just for displaying it on the table
 * @param {string} site_type the type of the site (lab/clinical/field) also used for visual purposes only
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addToAddingSiteIds = function(site_id, site_name, site_type)
{
	var new_row = "<tr id='site-table-row-" + site_id + "' style='height:33px;' data-siteid='" + site_id + "'>";
	new_row    +=	"<td colspan='4' class='adding-description adding-" + site_type + "'>Adding <span class='site_name adding-site-name " + site_type + "'>" + site_name + "</span> <a href='#' class='undo-add-site'>undo</a></td>";
	new_row    += "</tr>";
	
	$("#sites").append(new_row);
	$("#site-table-row-" + site_id).effect("highlight");
	
	// now add this site id to the list of removing_site_ids
	addToHiddenCommaSeparatedList($("#adding_site_ids"), site_id);
	
} // end addToAddingSiteIds()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Removes a non-exisiting site from the "adding_site_ids" element and removes the row from the table
 * @param {int} site_id the ID of the site to be removed
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var removeFromAddingSiteIds = function(site_id)
{
	var row = $("#site-table-row-" + site_id);
	row.effect("highlight");
	removeFromHiddenCommaSeparatedList($("#adding_site_ids"), site_id);
	setTimeout(function() {row.remove();}, 400);
	
} // end removeFromAddingSiteIds()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * A general function that removes a given id from a given hidden form element
 * The hidden form element's value is a comma separated list
 * @param {DOM element} hidden_element the <input> that has the comma separated list of IDs
 * @param {int} id_to_remove the ID to remove from the comma separated list
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var removeFromHiddenCommaSeparatedList = function(hidden_element, id_to_remove)
{
	var ids = hidden_element.val().split(",");
	var new_val = [];
	$.each(ids, function(i, v){if (v != id_to_remove) {new_val.push(v);}})
	hidden_element.val(new_val.join(","));
	
} // end removeFromHiddenCommaSeparatedList()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * A general function that adds a given id to a given hidden form element
 * The hidden form element's value is a comma separated list
 * @param {DOM element} hidden_element the <input> that has the comma separated list of IDs
 * @param {int} new_id the ID to add to the comma separated list
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addToHiddenCommaSeparatedList = function(hidden_element, new_id)
{
	var ids = [];	
	if(hidden_element.val()){ids = hidden_element.val().split(",");}
	ids.push(new_id);
	hidden_element.val(ids.join(","));
	
} // end addToHiddenCommaSeparatedList()