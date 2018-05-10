
var edit = 1;
var notifications_opened = false;

$(function(){
	
	if ($("#edit-requirement").length == 0) {
		edit = 0;
	}
	
	initFlippyDivs();
	initFancyFocus($(".fancy-input"));
	
	if (!edit) {
		initCustomToggle();
		initDefaultRequirementsList();
		initAssignFlippyAction();
		initAllFlippyAction();
		disableAlreadyChosenUniversalRequirements();
	}
	
	initCategoryChosen();
	initDynamicSandP();
	initSpecificSitesContent();
	initChooseSitesTrigger();
	initSave();
	initCancel();
	initChoosePeople();
	
	initNotificationsSummaryBox();

	$("#default_list").css("display", "block").css("visibility", "hidden").css("position", "absolute");
	$(".selectDate").datepicker({minDate: new Date()}).keydown(function() {return false;});
	
	$("#req-titles").click(function() {
		var descriptions = $("#req-descriptions");
		if (descriptions.is(":visible")) {
			descriptions.slideUp();
			$("#collapse-descriptions .show-specific-attachments").attr("src", "/images/icons/plus.png");
		} else {
			descriptions.slideDown();
			$("#collapse-descriptions .show-specific-attachments").attr("src", "/images/icons/minus.png");
		}
		
		for (i = 2; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		
	});
});

var initNotificationsSummaryBox = function()
{
	$('#main-content').on('click','.edit-notifications',function(e) {
		e.preventDefault();
		notifications_opened = true;
		$(".notifications-summary-box").hide().remove();
		$("#notificationForm").hide().css("position", "relative").css("left", "0px").fadeIn();
		adjustShiftGuidedTourStep(9, 500);
	});
	
	$("#notificationForm").css("position", "absolute").css("left", "-665px");
	
	var box_html  = "<div class='notifications-summary-box'><a href='#' class='edit-notifications'>Edit</a>";
	var at_least_one_is_on = false;
	
	$("#notificationForm").find("input[type='checkbox']").each(function(){
		
		var is_on = $(this).prop("checked");
		
		if (is_on) {
			
			at_least_one_is_on = true;
			var input_line = $(this).parent().parent();
			
			if (input_line.find("input[type='text']").length > 0) {
				var second_part = input_line.find(".text").last().text().replace("day", "");
				var number_of_days = input_line.find(".warning-frequency-offset").val();
				box_html	+= "- " + input_line.find(".text").first().text();
				box_html	+= " " + number_of_days + " ";
				box_html	+= " day";
				
				if (parseInt(number_of_days) != 1) {
					box_html	+= "s";
				}
				
				box_html	+= second_part + "<br />";
			}
			else {
				
				box_html	+= "- " + input_line.find("label").text() + "<br />";
			}
		}
		
	});
	
	if (!at_least_one_is_on) {
		box_html 	 += "All notifications are turned off.";
	}
	
	box_html 	 += "</div>";
	
	$("#notificationForm").after(box_html);
	
	$(".view-sample").click(function(){
		adjustShiftGuidedTourStep(9, 500);
	});
	
	$("#add-warning").click(function(){
		adjustShiftGuidedTourStep(9, 500);
	});
	
	
}


var initAutoAssign;
initAutoAssign = function() {
	$("#auto_assign").sliderCheckbox({onText: "On", offText: "Off"});
	$("#auto_assign_account_types").chosen();
	initAutoAssignChangeAction();
}

var initChoosePeople;
initChoosePeople = function() {
	$("#choose-people").click(function(e){
		e.preventDefault();
		openAssignModal(false, null, $(this));
	});
}

var initCancel;
initCancel = function() {
	$(".cancel-button").button().click(function(e){
		e.preventDefault();
		window.location.assign("/scheduler/compliance/manage");
	});
}

var clearFormErrors = function()
{
	// set everything back to normal
	$(".invalid-input").removeClass("invalid-input");
	$(".invalid-text-input").removeClass("invalid-text-input");
	$(".invalid-chosen-input").removeClass("invalid-chosen-input");
	$(".invalid-link-input").removeClass("invalid-link-input");
	$("#requirement-form-wrapper").find(".error").remove();
}

var validateData = function(data_object)
{
	var return_object = {};
	var valid = true;
	var error_messages = "<ul>";
	
	if (edit) {
	   // validate a bit differently.. we only care about the number of sites
	   // everything else has to be valid
		if (data_object['regardlessofsite'] == "0") {
			
			var existing_site_ids = $("#existing_site_ids").val().split(",");
			var number_of_exisiting_sites = 0;
			if($("#existing_site_ids").val().length != 0){
				number_of_exisiting_sites = existing_site_ids.length;
			}
			
			
			var removing_site_ids = $("#removing_site_ids").val().split(",");
			var number_of_removing_sites = 0;
			if($("#removing_site_ids").val().length != 0){
				number_of_removing_sites = removing_site_ids.length;
			}
			
			if ((number_of_exisiting_sites == number_of_removing_sites) && ($("#adding_site_ids").val().length == 0)) {
				valid = false;
				$("#choose-sites").addClass("invalid-input").addClass("invalid-link-input");
				error_messages += "<li>Please choose at least one site.</li>";
			}
			
		}
		if ($("#custom_title").length > 0) {
			// its a custom requirement, they need a title
			if (data_object['custom_title'] == "") {
				valid = false;
				$("#custom_title").addClass("invalid-input").addClass("invalid-text-input");
				error_messages += "<li>Please enter a title for your custom requirement.</li>";
			}
		}
		
	}
	else {
		if (data_object['custom_requirement'] == "1") {
			
			// Did they enter a title?
			if (data_object['custom_title'] == "") {
				valid = false;
				$("#custom_title").addClass("invalid-input");
				error_messages += "<li>Please enter a title for your custom requirement.</li>";
			}
			
			// Is it less than 255 characters?
			if (data_object['custom_title'].length > 254) {
				valid = false;
				$("#custom_title").addClass("invalid-input");
				error_messages += "<li>Please enter a title for your custom requirement that is less than 255 characters.</li>";
			}
			
						
			if (!valid) {
				$("#custom_title").addClass("invalid-text-input");
			}
			
			// Did they choose a category?
			if (!data_object['category']) {
				valid = false; 
				$("#category_chzn").find("a").first().addClass("invalid-input").addClass("invalid-chosen-input");
				error_messages += "<li>Please choose a category for your custom requirement.</li>";
			}
			
		}
		else {
			
			// did they select a title?
			if (!data_object['default_list']) {
				valid = false;
				$("#default_list_chzn").find("a").first().addClass("invalid-input").addClass("invalid-chosen-input");
				error_messages += "<li>Please choose a standard requirement or create a custom requirement.</li>";
			}
			
		}
		
		if (data_object['regardlessofsite'] == "0") {
			
			if (data_object['site_ids'] == "null") {
				valid = false;
				$("#choose-sites").addClass("invalid-input").addClass("invalid-link-input");
				error_messages += "<li>Please choose at least one site.</li>";
			}
			
		}
	}
	
	if ($("#notificationForm").css("position") == "relative") {
		
		// they've edited the notifcations, make sure this is valid
		var warning_collection = [];
		$(".warning-frequency-offset").each(function(){
			var entered_val = $(this).val();
			var bad_warning = false;
			var warning_msg = "";
			
			if (entered_val == "") {warning_msg = "<li>Please enter a number of days.</li>";}
			else if (!isNumber(entered_val) || parseInt(entered_val) <= 0 || entered_val.indexOf(".") != -1){warning_msg = "<li>Please enter a whole number.</li>";}
			else if(parseInt(entered_val) <= 0){warning_msg = "<li>Please enter a whole number.</li>";}
			else if($.inArray(entered_val, warning_collection) != -1) {warning_msg = "<li>Two warnings cannot be sent on the same day. Please enter a different number.</li>";}
			else {warning_collection.push(entered_val);}
			
			if (warning_msg) {
				valid = false;
				$(this).addClass("invalid-input").addClass("invalid-text-input");
				error_messages += warning_msg;
			}
			
		});
	}
	
	error_messages += "</ul>";
	return_object['msg'] = error_messages;
	return_object['valid'] = valid;
	return return_object;
}

var isNumber = function(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

var getData = function()
{
	var data = $("#requirement-form-wrapper").find("form").serialize();
		
	var data_object = serializeObject(data);
	data_object['auto_assign'] = "0";
	if ($("#auto_assign-slider-button").hasClass("on")) {
		data_object['auto_assign'] = "1";
	}
	
	
	data_object['custom_title'] = $("#custom_title").val();
	data_object['category'] = $("#category_chzn").find(".chzn-results").find(".result-selected").attr("data-option-val");
	data_object['default_list'] = $("#default_list_chzn").find(".chzn-results").find(".result-selected").attr("data-option-val");
	var auto_assign_accounts = [];
	$("#auto_assign_accounts_chzn").find(".chzn-results").find(".result-selected").each(function(){auto_assign_accounts.push($(this).attr("data-option-val"));});
	data_object['auto_assign_accounts'] = auto_assign_accounts;
	
	
	data_object['due_dates'] = [];
	
	$("#due_dates").find("option").each(function(){
		data_object['due_dates'].push($(this).val());
	});
	
	data_object['userContextIds'] = [];
	
	$("#userContextIds").find("option").each(function(){
		data_object['userContextIds'].push($(this).val());
	});
	
	return data_object;
}

var initSave;
initSave = function() {
	$("#save").css({"padding" : "0.33em 1em"}).addClass("small");
	$("#save").css("padding-top", "0.225em").css("padding-bottom", "0.4em");
	
	/*
	 * Save the form!
	 * Get the data, make sure its valid (throw an error if not), send request
	 * to save the requirement/associations/attachments/auto assignments, recompute compliance for affected users,
	 * and finally send the user to the manage requirements page.
	 */
	$("#save").click(function(e){
		
		e.preventDefault();
		
		// -------- Step 1: Get Data ------------------------------------------------------------------------
		var data_object = getData();

		
		// -------- Step 2: Confirm the data is valid -------------------------------------------------------
		clearFormErrors();
		var valid_results = validateData(data_object);
		
		if (!valid_results['valid']) {
			
			// append message, fade in the error, scroll to it
			$("#requirement-form-wrapper").find(".island").prepend("<div class='error' style='display:none;'>" + valid_results['msg'] + "</div>");
			$("#requirement-form-wrapper").find(".error").fadeIn();
			$('html,body').animate({scrollTop: $("#requirement-form-wrapper").find(".error").offset().top-40},'slow');
			
		}
		else {

			data_object['edit'] = edit;

			// -------- Step 3: Send an AJAX request to save the requirement/associations/attachments/etc --------
			$.post("/scheduler/compliance/save-requirement", data_object,
			function(response) {
					window.location.replace("/scheduler/compliance/manage");
			}); // close save requirement ajax request

		}
		
	}); // close click handler for save button
}

var serializeObject;
serializeObject = function(s)
{
   var o = {};
   var a = s.split("&");
   
   $.each(a, function(i, v) {
		var value_pieces = v.split("=");
		var name = value_pieces[0];
		var multiple_values = false;
		
		// it may be an array of values and we'll get a weird name - lets clean that up
		if (name.indexOf("%5B%5D") != -1) {
			name = name.substring(0, name.length - 6);
			multiple_values = true;
		}
		
		var value = value_pieces[1];
		
		// replace slashes
		if (value.indexOf("%2F") != -1) {value = value.replace(/%2F/gi, "/");}
		
		// replace commas
		if (value.indexOf("%2C") != -1) {value = value.replace(/%2C/gi, ",");}
		
		if (o[name]) {
			// add to an array of values?
			if (multiple_values) {
				o[name].push(value);
			}
		}
		else {
			if (multiple_values) {
				o[name] = [];
				o[name].push(value);
			}
			else {
				o[name] = value;
			}
		}
   });
   
   return o;
};

var openAssignModal;
openAssignModal = function(existing_temp_id, account_type, trigger){

	trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-assign-modal-throbber'>");
	
	if (trigger.attr("id") == "choose-people") {
		$("#load-assign-modal-throbber").css("left", "19%");
	}
	
	$.post("/scheduler/compliance/generate-requirement-assign-modal", {},
		function(resp) {
			
			$("#load-assign-modal-throbber").remove();
			trigger.css("opacity", "1");
			
			$("#assign-modal").empty().html($(resp).html());
			
			if (existing_temp_id) {
				if (account_type == "student") {
					$("#accountType").val(1);
				}
				else {
					$("#accountType").val(0);
				}
			}
			
			initFlippyDivs();
			initRequirementAssignPicklist(existing_temp_id);
			
			$("#assign-modal").find(".picklist-ms-picker").find(".grid_2").css("width", "14.3%");
			initFancyFocus($("#assign-modal").find(".selectDate"));
			$("#assign-modal").find(".selectDate").datepicker({minDate: new Date()}).keydown(function() {return false;});
			
			$("#assign-modal").dialog("open");
			
			$("#picklist-fancy-filter_filters-title").blur();
			
			$(".close-assign-modal").button().blur();
			$(".save-assign-modal").button().blur();
			$("#load-modal-throbber").remove();
			
			$(".close-assign-modal").click(function(e){
				e.preventDefault();
				$(this).unbind();
				$("#assign-modal").dialog('close');
			});
			
			$(".save-assign-modal").click(function(e){
				e.preventDefault();
				
				$("#assign-modal").dialog('close');
				
				var temp_id = 1;
				var people = [];
				
				$("#assign-modal").find(".chosen-list").find("option").each(function(){people.push($(this).val());});
				
				var account_type = "student";
				if ($("#accountType").val() == 0) {account_type = "instructor";}
				
				var due_date = $("#due_date").val();
				var total_count = people.length;
				
				if (existing_temp_id) {
					
					if (total_count == 0) {
						// remove the row
						removeAssignmentRow(existing_temp_id);
					}
					else {
						temp_id = existing_temp_id;
						updateSummaryRow(people, temp_id, total_count, account_type, due_date);
						updateDueDateOption(due_date, temp_id);
						updateUserContextIdsOption(people, temp_id, due_date);
					}
					
				}
				else {
					
					if (total_count != 0) {
					
						var equal_due_date_temp_id = false;
						
						$(".assignment-group").each(function(){
							
							if (($(this).attr("data-duedate") == due_date) && $(this).attr("data-accounttype") == account_type) {
								equal_due_date_temp_id = $(this).attr("data-tempgroupid");
							}
							
							temp_id++;
							
						});
						
						
						// if a row with this due date and account type already exists, just add these user role ids
						if(equal_due_date_temp_id){
							
							addToVisibleSummaryRow(people, equal_due_date_temp_id, total_count);
							addToUserContextOptions(people, equal_due_date_temp_id);
							
							
						}
						else {
							// add a row
							addVisibleSummaryRow(temp_id, account_type, total_count, due_date);
							
							// add options to due_dates select
							addDueDateOption(due_date, temp_id);
							
							// add options to userContextIds select
							addUserContextOptions(people, temp_id, due_date);
						}
					}
					
				}
				
				for (i = 7; i < 10; i++) {adjustShiftGuidedTourStep(i, 800);}
				
				updateChoosePeopleLink(false);
				
			});
		}
	);
	
}

var updateChoosePeopleLink;
updateChoosePeopleLink = function(subtract_one) {
	
	var count = 0;
	$(".assignment-group").each(function(){
		count++;
	})
	
	if (subtract_one) {
		count = count-1;
	}
	
	if (count > 0) {
		$("#choose-people").text("Choose more people");
	}
	else {
		$("#choose-people").text("Choose people");
	}
	
}

var addToVisibleSummaryRow;
addToVisibleSummaryRow = function(new_people, temp_id, additional_count) {
	var row = $("#assignment-group-" + temp_id);
	var current_count = row.find(".number-of-people").text();
	var new_total_count = parseInt(current_count) + additional_count;
	var plural = "s";
	if (new_total_count == 1) {plural = "";}
	
	row.find(".number-of-people").text(new_total_count);
	row.find(".people-plural").text(plural);
	row.effect("highlight");
}

var updateSummaryRow;
updateSummaryRow = function(people, temp_id, total_count, account_type, due_date) {
	var row = $("#assignment-group-" + temp_id);
	var plural = "s";
	if (total_count == 1) {plural = "";}
	
	row.find(".number-of-people").text(total_count);
	row.find(".people-plural").text(plural);
	row.find(".account-type").text(account_type);
	row.find(".due-date").text(due_date);
	row.find(".edit-chosen-people").attr("data-accounttype", account_type);
	row.attr("data-duedate", due_date).attr("data-accounttype", account_type);
	row.effect("highlight");
}

var updateDueDateOption;
updateDueDateOption = function(due_date, temp_id) {
	
	$("#due_dates").find("option").each(function(){
		if ($(this).attr("data-tempgroupid") == temp_id) {
			$(this).val(due_date).text(due_date);
			return;
		}
	});
	
}

var updateUserContextIdsOption;
updateUserContextIdsOption = function(people, temp_id, due_date) {
	
	$("#userContextIds").find("option").each(function(){
		if ($(this).attr("data-tempgroupid") == temp_id) {
			var people_txt = people.join(",");
			$(this).val(people_txt).text(people_txt);
			
			return;
		}
	});
	
}

var addToUserContextOptions;
addToUserContextOptions = function(new_people, temp_id){
	
	$("#userContextIds").find("option").each(function(){
		if ($(this).attr("data-tempgroupid") == temp_id) {
			var current_value = $(this).val();
			var value_to_add = new_people.join(",");
			var new_value = current_value + "," + value_to_add;
			$(this).val(new_value).text(new_value);
			return;
		}
	});
	
}

var addVisibleSummaryRow;
addVisibleSummaryRow = function(temp_id, account_type, total_count, due_date){
	
	// if its edit, and this is the first summary row we're adding, give this a title
	if (edit && $(".specific-people-content").find(".assignment-group").length <= 0) {
		$(".specific-people-content").prepend("<h2>New assignments</h2>");
	}
	
	var plural = "s";
	if (total_count == 1) {plural = "";}
	
	var top_margin = "";
	if (temp_id == 1) {top_margin = "margin-top:0.5em";}
	
	var top_border = "";
	if (temp_id != 1) {top_border = "border-top:0px;";}
	
	var row_description = '<span class="number-of-people">' + total_count + '</span> <span class="account-type">' + account_type + '</span><span class="people-plural">' + plural + '</span> selected, ';
	
	if (edit) {
		row_description = 'Assigning <span class="number-of-people">' + total_count + '</span> new <span class="account-type">' + account_type + '</span><span class="people-plural">' + plural + '</span>, ';
	}
	
	var row_html = '<div id="assignment-group-' + temp_id + '" style="display:none;' + top_margin + ' ' + top_border + '" class="grid_7 assignment-group" data-tempgroupid="' + temp_id + '" data-duedate="' + due_date + '" data-accounttype="' + account_type + '">';
	row_html    +=		'<div class="grid_8">'; 			
	row_html    +=			row_description
	row_html    +=			'due: <span class="due-date">' + due_date + '</span>'; 			
	row_html    +=		'</div>';
	
	row_html    +=		'<div class="grid_4">'; 			
	row_html    +=			'<a href="#" class="edit-chosen-people" data-accounttype="' + account_type + '">Edit</a> | <a href="#" class="remove-chosen-people">Remove all</a>';
	row_html    +=		'</div>';
	row_html    += '</div>';
	
	$(".specific-people-summary-wrapper").append(row_html).find(".assignment-group").last().fadeIn();
	
	$("#assignment-group-" + temp_id).find(".edit-chosen-people").click(function(e){
		e.preventDefault();
		openAssignModal(temp_id, $(this).attr("data-accounttype"), $(this));
	});
	
	$("#assignment-group-" + temp_id).find(".remove-chosen-people").click(function(e){
		e.preventDefault();
		removeAssignmentRow(temp_id);
		for (i = 7; i < 10; i++) {adjustShiftGuidedTourStep(i, 700);}
	});
	
}

var removeAssignmentRow;
removeAssignmentRow = function(temp_id) {

	
	$("#assignment-group-" + temp_id).find(".number-of-people").text("0");
	$("#assignment-group-" + temp_id).effect("highlight").slideUp("fast");
	updateChoosePeopleLink(true);
	
	$("#userContextIds").find("option").each(function(){
		if ($(this).attr("data-tempgroupid") == temp_id) {
			$(this).remove();
			return;
		}
	});
	
	$("#due_dates").find("option").each(function(){
		if ($(this).attr("data-tempgroupid") == temp_id) {
			$(this).remove();
			return;
		}
	});
	
	setTimeout(function() {
		$("#assignment-group-" + temp_id).remove();
			
		// now make sure the first assignment row has a top border and a top margin
		$(".assignment-group").first().css("margin-top", "0.5em").css("border-top", "1px solid #bbb");
		
		// if its edit and this is our last row, remove the 'New Assignments' title
		if (edit && $(".specific-people-content").find(".assignment-group").length <= 0) {
			$(".specific-people-content").find("h2").slideUp().remove();
		}
		
		
	}, 1000);
}


var addDueDateOption;
addDueDateOption = function(due_date, temp_id){
	var option = "<option selected='selected' value='" + due_date + "' data-tempGroupId='" + temp_id + "'>" + due_date + "</option>";
	$("#due_dates").append(option);
	
}

var addUserContextOptions;
addUserContextOptions = function(people, temp_id, due_date) {
	var people_list = people.join(",");
	var option = "<option selected='selected' value='" + people_list + "' data-tempGroupId='" + temp_id + "' data-duedate='" + due_date + "'>" + people_list + "</option>";
	$("#userContextIds").append(option);
}

var initFancyFocus;
initFancyFocus = function(elements_selector) {
	
	elements_selector.unbind('focus').unbind('blur');
	
	elements_selector.focus(function(){
		$(this).addClass("fancy-input-focus");
	});
	
	elements_selector.blur(function(){
		$(this).removeClass("fancy-input-focus");
	});
}

var initCustomToggle;
initCustomToggle = function() {
	$(".default-custom-toggle").click(function(e){
		e.preventDefault();
		
		if ($(this).text() == "Create custom requirement") {
			$(".top-wrapper").animate({height:60});
			
			// hide the default select, show the title text input, change this text, hide static req sum and show dynamic req sum
			$(".static-req-sum").hide();
			$("#default_list-element").hide();
			
			$(".dynamic-req-sum").fadeIn();
			$("#custom_title").fadeIn();
			
			$("#custom_requirement").val("1");
			
			if ($("#custom_title").val() == "") {
				$(".custom-title-default-text").fadeIn();
			}
			
			$(this).text("Select from list of standard requirements");
		}
		else {
			// hide the title text input, show the default select, change this text, hide dynamic req sum and show static req sum
			$(".top-wrapper").animate({height:45});
			
			$(".dynamic-req-sum").hide();
			$("#custom_title").hide();
			
			if ($("#default_list").val() != 0 && $("#default_list").val() != "null") {
				$(".static-req-sum").fadeIn();
			}
			
			$("#default_list-element").fadeIn();
			$("#custom_requirement").val("0");
			$(".custom-title-default-text").hide();
			$(this).text("Create custom requirement");
		}
		
		for (i = 4; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		
	});
	
	$("#custom_title").focus(function(){
		$(".custom-title-default-text").hide();
	});
	
	
	$("#custom_title").blur(function(){
		if ($(this).val() == "") {
			$(".custom-title-default-text").fadeIn();
		}
	});
	
	$(".custom-title-default-text").click(function(e){
		e.preventDefault();
		$("#custom_title").focus();
	});
	
}

var initCategoryChosen;
initCategoryChosen = function() {
	$("#category").chosen({disable_search_threshold: 10});
}

var initDefaultRequirementsList;
initDefaultRequirementsList = function() {
	$("#default_list").chosen({allow_single_deselect: true}).change(function(){
		
		var default_list = $(this);
		$(".static-req-sum").fadeOut("fast");
		$(".req-sum").append("<img src='/images/throbber_small.gif'>");
		
		if (default_list.val() != 0) {
			$.post("/scheduler/compliance/get-req-sum",
				{req_id: default_list.val()},
				function(response){
					$(".req-sum").find("img").fadeOut().remove();
					$(".static-req-sum").find(".expiration").text(response.expiration);
					$(".static-req-sum").find(".category").text(response.category);
					$(".static-req-sum").fadeIn();
				});
		}
		else {
			$(".req-sum").find("img").fadeOut().remove();
			$(".static-req-sum").fadeOut();
		}
		
	});
}

var disableAlreadyChosenUniversalRequirements;
disableAlreadyChosenUniversalRequirements = function() {
	
	var req_ids = $("#already_chosen_universal_requirements").val().split(",");
	
	$("#default_list_chzn").find(".chzn-drop").find(".chzn-results").find("li").each(function(){
		
		if ($.inArray($(this).attr("data-option-val"), req_ids) != -1) {
			// this universal requirement already has an assocatian with the current program.
			// we don't want to allow the user to select it.
			$(this).addClass("disabled-chosen-option").css("cursor", "default").css("background-color", "#fff");
		}

	});
	
	$(".disabled-chosen-option").unbind().click(function(e){
		e.preventDefault();
		e.stopPropagation();
	});
	
	$(".disabled-chosen-option").hover(function(e){
		e.preventDefault();
		$(this).removeClass("highlighted");
		e.stopPropagation();
	});
	
}

var initDynamicSandP;
initDynamicSandP = function() {
	
	$("#regardlessofsite").change(function(){
		var src = "/images/icons/program-requirement.png";
		if ($(this).val() != 1){
			src = "/images/icons/site-requirement.png";
		}
		
		$(".dynamic-s-p").find("img").attr("src", src).effect("bounce");
	})
	
}

var initSpecificSitesContent;
initSpecificSitesContent = function() {
	var hid_manage_link = false;
	$("#regardlessofsite").change(function(){
		
		if ($(this).val() == 1){
			// and hide the 'manage sites' link if it's there
			
			if(edit){
				hid_manage_link = true;
				$("#manage-sites").fadeOut();
			}
			
			$(".specific-site-content").slideUp();
		}
		else {
			$(".specific-site-content").slideDown();
			
			// if edit, we need to deal with site/unassign conflicts
			if (edit) {
				if (hid_manage_link) {
					$("#manage-sites").fadeIn();
				}
				
			    handleConflictsForSpecificChange();
			}
		}
		
		for (i = 5; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		
	})
}

var initChooseSitesTrigger;
initChooseSitesTrigger = function() {
	
	// edit will not have these elements
	if (!edit) {
		$("#remove-chosen-sites").click(function(e){
			e.preventDefault();
			$("#site_ids").val("null");
			$(".specific-sites-description").delay(800).hide();
			$("#choose-sites").fadeIn();
			for (i = 5; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		});
		
		$("#edit-chosen-sites").click(function(e){
			e.preventDefault();
			openChooseSitesModal($(this), $("#site_ids").val());
		});
	}
		
		
	$('#pick-sites-modal').on('click','#save-site-modal',function(e) {
		
		if (edit) {
			submitPickSitesModal();
		}
		else {
		
			$("#site_ids").val("null");
			
			var ids = [];
			
			$("#chosen-list").find("option").each(function(){
				ids.push($(this).val());
			});
			
			$("#site_ids").val(ids.join(","));
			
			var num_of_sites = ids.length;
			// update the row summary, show it, and hide the "choose sites" link
			$("#number-of-sites").text(num_of_sites);
			
			if (num_of_sites == 0) {
				$(".specific-sites-description").hide();
				$("#choose-sites").fadeIn();
			}
			else {
			
				if (num_of_sites == 1) {
					$("#site-plural").text("");
				}
				else {
					$("#site-plural").text("s");
				}
				
				$("#choose-sites").hide();
				
				if ($(".specific-sites-description").css("display") == "block") {
					$(".specific-sites-description").effect("highlight");
				}
				else {
					$(".specific-sites-description").fadeIn();
				}
			}
			
			for (i = 5; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
			
		}
	} );
	
	
	$("#choose-sites").click(function(e){
		e.preventDefault();
		var site_ids = [];
		
		if (edit) {
			site_ids = getSiteIdsOnEdit(false);
		}
		else {
			site_ids = $("#site_ids").val();
		}
		 
		openChooseSitesModal($(this), site_ids);
	});
}

var getSiteIdsOnEdit;
getSiteIdsOnEdit = function(return_array) {
	var site_ids = [];
	
	// turn comma separated list into array so we can work with it
	var existing_site_ids = $("#existing_site_ids").val().split(",");
	var removing_site_ids = $("#removing_site_ids").val().split(",");
	
	$.each(existing_site_ids, function(i, v){
		
		if ($.inArray(v, removing_site_ids) == -1) {
			// this site is not in our 'removing sites' list. include it as a chosen site
			site_ids.push(v);
		}
		
	});
	
	site_ids = site_ids.join(",");
	
	// now append our adding_site_ids
	if ($("#adding_site_ids").val()) {
		site_ids = site_ids + "," + $("#adding_site_ids").val();
	}
	
	if (return_array) {
		site_ids = site_ids.split(",");
	}
	
	return site_ids;
}

var initAssignFlippyAction;
initAssignFlippyAction = function() {
	
	var update_guided_tour_steps = false;
	
	$("#assign").change(function(){
		
		if ($(this).val() == 1){
			$(".assign-complete-sentence").animate({opacity:1});
			$(".requirement-period").hide();
			$("#disable-assign-complete-sentence").hide();
			
			
			if ($("#all").val() == 0) {
				$(".specific-people-content").slideDown();
				update_guided_tour_steps = true;
			}
			
		}
		else {
			$(".assign-complete-sentence").animate({opacity:0}, {complete: function() {$(".requirement-period").show();}});
			$("#disable-assign-complete-sentence").show();
			
			if ($(".specific-people-content").css("display") == "block"){
				update_guided_tour_steps = true;
			}
			
			$(".specific-people-content").slideUp();
		}
		
		if (update_guided_tour_steps) {
			for (i = 7; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		}
		
	})
	
}

var initAllFlippyAction;
initAllFlippyAction = function() {
	
	$("#all").change(function(){
		
		if ($(this).val() == 1) {
			$(".all-due-date-wrapper").fadeIn();
			$(".specific-people-content").slideUp();
		}
		else {
			$(".all-due-date-wrapper").fadeOut();
			$(".specific-people-content").slideDown();
		}
		
		for (i = 7; i < 10; i++) {adjustShiftGuidedTourStep(i, 500);}
		
	});
	
}

var initAutoAssignChangeAction;
initAutoAssignChangeAction = function() {

	$("#auto_assign").change(function(){
		
		if ($(this).val() == 1) {
			$(".hide-on-auto-assign-off").fadeIn();
		}
		else {
			$(".hide-on-auto-assign-off").fadeOut();
		}
		
	})
	
}


var openChooseSitesModal;
openChooseSitesModal = function(trigger, site_ids) {
	
	trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-sites-modal-throbber'>");
	
	if (trigger.attr("id") == "edit-chosen-sites") {
		$("#load-sites-modal-throbber").css("left", "19%");
	}
	
	var requirement_name = "";
	
	if ($("#custom_requirement").val() == 1) {
		// it is a custom requirement, use the text box
		requirement_name = $("#custom_title").val();
	}
	else {
		requirement_name = $("#default_list_chzn").find("a").find("span").text();
	}

	
	$.post("/scheduler/compliance/generate-site-picker",
		   {"site_ids": site_ids,
			"requirement": requirement_name},
		   function(resp) {
				$("#pick-sites-modal").html($(resp).html());
				initPickSitesModal();
				$("#pick-sites-modal").dialog("open");
				$("#load-sites-modal-throbber").remove();
				trigger.css("opacity", "1");
			});
}

var adjustShiftGuidedTourStep = function(step_number, delay_length)
{
	if (delay_length == null) {
		delay_length = 300;
	}
	
	if ($("#guided_tour_wrapper").find(".tour_directions").length > 0){
		if ($("#guided_tour_wrapper").find(".tour_directions").css("display") == "block") {
			$("#guided_tour_step_" + step_number).hide();
			setTimeout(function(){
				$("#guided_tour_step_" + step_number).fadeIn("fast");
				$.fn.guidedTour.updateStepLocation(guided_tour_steps[step_number], step_number);
			}, delay_length);
		}
	}
}
