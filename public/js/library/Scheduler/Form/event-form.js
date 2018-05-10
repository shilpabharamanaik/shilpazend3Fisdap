$(function() {
	
	var is_edit = false;
	var is_non_admin = false;
	var initial_site_id = "";
	var initial_base_id = "";
	var initial_student_assignments = "";
	
	$("#save").hide().parent().append("<img id='finishing-event-load-throbber' src='/images/throbber_small.gif'>");
	
	if($("#non-admin-edit-event-form").length > 0){
		
		is_edit = true;
		is_non_admin = true;
		
		$("h1").text("Edit " + getShiftType() + " shift (not admin)");
		$(".next-tab").addClass("disabled-tab-nav");
		
		// we're a non-admin. default to tab 2 and disable the other tabs, also update the title
		$(".tab").each(function(){
			if ($(this).attr("data-tabcontentid") == "assign-allocate"){
				openNewTab($(this), $(this).attr("data-tabContentId"));
			}
			else {
				$(this).addClass("disabled-event-tab");
			}
			
		});
		
		if ($("#non-admin-edit-event-form").hasClass("not-all-future-events-in-series")) {
			$(".signup-settings-line").css("opacity", "0.4");
			$("#windows").css("opacity", "0.4");
			$("#permissions-wrapper").css("opacity", "0.4");
			$("#super-limited-bob-blocker").css("height", $("#signup-perm-wrapper").height());
			$("#signup-perm-wrapper").css("opacity", "0.4");
		}
		
		// get their windows
		if ($("#windows-table").find("tr").length <= 0) {
			$.each($("#existing_windows").val(), function(i, v){
				addNewWindow(v);
			});
		}
		
	}
	else if ($("#admin-edit-event-form").length > 0) {
		is_edit = true;
				
		// get their windows
		if ($("#windows-table").find("tr").length == 0) {
			if ($("#existing_windows").val()){
				//console.log($("#existing_windows").val());
				$.each($("#existing_windows").val(), function(i, v){
					//console.log(v);
					addNewWindow(v);
				});
			}
			else {
				brokenRobot(false, "This data has been corrupted.");
			}
		}
	}
	else {
		// give them a default window
		if ($("#windows-table").find("tr").length <= 0) {
			addNewWindow();
		}
	}
	
	checkSharingAdmin();
	
	if (is_edit) {
		initial_site_id = $("#site").val();
		initial_base_id = $("#base").val();
		inital_student_assignments = [];
		$("#assigned_students").find("option").each(function(){
			inital_student_assignments.push($(this));
		});
	}
	
	if ($("#student-conflicts").length > 0) {
		$("#assign").hide();
		$("#assigning-details").hide();
		$("#student-conflicts").show();
	}
	
	$(".disabled-event-tab").click(function(e){e.preventDefault();e.stopPropogation();});
	$(".disabled-tab-nav").click(function(e){e.preventDefault();e.stopPropogation();});
	$(".disabled-tab-nav").hover(function(e){$(this).css("text-decoration", "none").css("cursor", "default")});
	
	$("#add-window").click(function(e){
		e.preventDefault();
		$(this).css("opacity", "0");
		$(this).parent().append("<img id='new-window-throbber' src='/images/throbber_small.gif'>");
		addNewWindow();
	});
	
	$("#site").chosen();
	$("#base").chosen();
	$("#sharing_cert_levels").chosen();
	$("#sharing_cert_levels_chzn").find(".chzn-results").css("max-height", "110px");
	$("#repeat_frequency").chosen();
	$("#repeat_frequency_chzn").find(".chzn-results").css("max-height", "150px");
	$("#repeat").sliderCheckbox({onText: "On", offText: "Off"});
	$("#students-can-sign-up").sliderCheckbox({onText: "On", offText: "Off"});
	$("#drop_previously_shared_students").sliderCheckbox({onText: "Yes", offText: "No"});
	$("#share_flag").sliderCheckbox({onText: "On", offText: "Off"});
	$("#cancel-btn").find("a").button();
	initFlippyDivs();
	
	$("#share_flag").change(function(){
		
		if ($("#sharing-off-blocker").css("display") == "none") {
			$("#sharing-off-blocker").show();
			$("#sharing-on-options").css("opacity", "0.5");
			
			if ($("#all-checker-wrapper").css("display") == "block") {
				$("#edit-sharing-programs").trigger("click");
			}
		}
		else {
			$("#sharing-off-blocker").hide();
			$("#sharing-on-options").css("opacity", "1");
		}
		
	});
	
	updatePermissionsSummary();
	updateStudentAssignedList();
	
	$("#save").click(function(e){
		error_msg = get_errors();
		
		// do we have too many units? we need to be under 4,000 to be valid
		var shared_count = 0;
		if ($("#share_flag").val() == 1) {
			shared_count = parseInt($("#program_count").text())+1;
		}
		
		var too_many_units = has_too_many_units(parseInt($("#shift_count").text()), parseInt($("#assigned_count").text()), shared_count);
		
		//too_many_units = true;
		
		if (error_msg || too_many_units) {
			e.preventDefault();
			// open the first tab and show errors
			$(".tab").each(function(){
				if ($(this).attr("data-tabcontentid") == "shift-details"){openNewTab($(this), $(this).attr("data-tabContentId"));}
			});
			if ($("#event-form-errors").length > 0) {$("#event-form-errors").remove();}
			$("#too-many-units-msg").hide();
			
			if (too_many_units) {
				$("#too-many-units-msg").show();
			}
			
			$("#shift-details").prepend(error_msg);
		}
		else {
			e.preventDefault();
			
			if (($("#sharing-off-blocker").css("display") != "none") || ($("#sharing-options-wrapper").css("display") == "none")) {
				$("#share_flag").val(0);
			}
			
			var window_data = {};
			window_data['new'] = {};
			window_data['existing'] = {};
			
			$("#windows-table").find("tr").each(function(){
				
				var selector_id = $(this).attr("selector_id");
				var this_window_data = getWindowData($(this));
				var window_type = "existing";
				
				if ($(this).hasClass("new_window")) {
					window_type = "new";
				}
				
				window_data[window_type][selector_id] = this_window_data;
			});
			
			var form_data = $("#event-form").serializeAssoc();
			
			busyRobot();
			
			var edit = 0;
			if (is_edit) {edit = 1;}
			
			if (is_edit) {
				if (is_non_admin) {
					form_data = $("#non-admin-edit-event-form").serializeAssoc();
				}
				else {
					form_data = $("#admin-edit-event-form").serializeAssoc();
				}
			}
			
			/*
			console.log("edit: " + edit);
			console.log("event_id: " + $("#event-form-island").attr("data-eventid"));
			console.log("type: " + getShiftType());
			console.log("form_values: ");
			console.log(form_data);
			console.log("window_data: ");
			console.log(window_data);
			*/
			
						
			$.post("/scheduler/shift/save-shift-form",
				
				{"edit" : edit,
				 "event_id" : $("#event-form-island").attr("data-eventid"),
				 "type" : getShiftType(),
				 "form_values" : form_data,
				 "window_data" : window_data},
				
				function(resp)
				{
					window.location.replace("/scheduler");
					
				}, "json").fail(function(){brokenRobot();});
				
		}




	});

    initSingleDatePicker();
    initExtendSeriesPopup();

	
	function has_too_many_units(shifts, students, programs)
	{
		var number_of_units = (shifts*students)+(shifts*programs);
		
		if(number_of_units > 4000) {
			return true;
		}
		
		return false;
	}
	
	function get_errors() {
		$(".error-label").removeClass("error-label");
		var errors = false;
		var error_msg = "<div id='event-form-errors' class='error'><ul>";
		
		// start time must be 24 hour format
		if (!is_miltiary_format($("#start_time").val())) {
			errors = true;
			$("label[for='start_time']").addClass("error-label");
			error_msg += "<li>Please enter a valid start time in 24 hour format (0000-2359).</li>";
		}
		
		// duration must be between 0.01 and 120.00
		if (!valid_duration($("#duration").val())) {
			errors = true;
			$("label[for='duration']").addClass("error-label");
			error_msg += "<li>Please enter a valid duration (0.01-120.00).</li>";
		}
		
		// slots must be a number between 0 and 5000 (inclusive)
		if (!valid_slots($("#slots").val())) {
			errors = true;
			$("label[for='slots']").addClass("error-label");
			error_msg += "<li>Please enter a valid number of students who can attend this shift (0-5000).</li>";
		}
		
		// the number of assignments cannot exceed the number of slots
		if (parseInt($("#assigned_count").text()) > parseInt($("#slots").val())) {
			errors = true;
			error_msg += "<li>The number of assigned students cannot exceed the number of slots available. Please drop students or increase the number of students who can go.</li>";
		}
		
		// there must be at least 1 day selected
		if ($("#save").val() == "Create shifts"){
			errors = true;
			error_msg += "<li>Please select at least one date.</li>";
		}

        // Event notes cannot be longer than 200 characters
        if ($("#notes").val().length > 200) {
            errors = true;
            error_msg += "<li>Please enter notes that are less than 200 characters long.</li>";
        }

        // Custom shift name cannot be longer than 128 chars
        if ($("#custom_name").val().length > 128) {
            errors = true;
            error_msg += "<li>Please enter a custom shift name that is less than 128 characters long.</li>";
        }
		
		error_msg += "</ul></div>";
		
		if (errors) {
			return error_msg;
		}
		
		return false;
	}
	
	function valid_slots(val) {
		var valid = true;
		if (val) {
			if (val.indexOf(".") != -1) {valid = false;}
			if (val.length == 0) {valid = false;}
			if (!isNumber(val)) {valid = false;}
			if (val > 5000) {valid = false;}
			if (val < 0) {valid = false;}
		}
		else {valid = false;}
		return valid;
	}
	
	function valid_duration(val) {
		var valid = true;
		if (val) {
			if (!isNumber(val)) {valid = false;}
			if (val < 0.01) {valid = false;}
			if (val > 120.00) {valid = false;}
		}
		else {valid = false;}
		return valid;
	}
	
	function is_miltiary_format(val) {
		var valid = true;
		if (val) {
			if (!isNumber(val)) {valid = false;}
			if(val.length != 4){valid = false;}
			var hr = val.substring(0,2);
			var min = val.substring(2,4);
			if (hr >= 24) {valid = false;}
			if (min >= 60) {valid = false;}
		}
		else {valid = false;}
		return valid;
	}
	
	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}
	
	$("#slots").change(function(){
		updateAssignDisable();
	});
	
	$(".active-col").each(function(){
		$(this).find("input").sliderCheckbox({onText: "On", offText: "Off"});
	});
	
	//$("#repeat_until").datepicker();
	var today = new Date();
	$("#repeat_until").datepicker({changeMonth: true,changeYear: true,minDate:today,maxDate:"+5y"});
	$("#repeat_frequency_chzn").find(".chzn-search").hide();
	$("#repeat_frequency_chzn").find(".chzn-results").css("padding-top", "0.5em");
	$("#repeat_frequency_type").buttonset();
	
	$("#preceptors").chosen();
	$("#instructors").chosen();
	
	$("#repeat_until").keydown(function(){return false;});
	
	$("#assign").button();
	
	updateAssignDisable();
	
	$(".call-chosen").each(function(){
		$(this).chosen();
		if ($(this).hasClass("remove-search")) {
			$("#" + $(this).attr("id") + "_chzn").find(".chzn-search").remove();
		}
	});
	
	$(".fancy-input").focus(function(){
		$(this).addClass("fancy-input-focus");
	});
	
	$(".fancy-input").blur(function(){
		$(this).removeClass("fancy-input-focus");
	});
	
	$("dd").each(function(){
		if ($(this).find(".errors").length > 0) {
			$(this).find(".errors").remove();
			$(this).prev().addClass("has-errors");
		}
	});

	$("#assign").click(function(e){
		e.preventDefault();
		
		var trigger = $(this);
		
		if ($(this).css("opacity") != "1") {
			return false;
		}
		
		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
		var data = {};
		data['slot_count'] = $("#slots").val();
        data['shift_count'] = $("#shift-list").find(".shift").length;
		
		if ($("#sharing-options-wrapper").css("display") == "block") {
			data['certs'] = $("#sharing_cert_levels").val();
		}
		
		$.post("/scheduler/index/generate-assign-modal", {"data" : data},
			function(resp) {
				$("#assign-modal").empty().html($(resp).html());
				initShiftAssignPicklist();
				
				$("#assign-modal").find(".picklist-ms-picker").find(".grid_2").css("width", "14.3%");
				$("#assign-modal").dialog("open");
				
				$("#picklist-fancy-filter_filters-title").blur();
				
				$(".close-assign-modal").button().blur();
				$(".save-assign-modal").button().blur();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);
				
				$(".close-assign-modal").click(function(e){
					e.preventDefault();
					$(this).unbind();
					$("#assign-modal").dialog('close');
				});
				
				$(".save-assign-modal").click(function(e){
					e.preventDefault();
					
					// now we update our select box for the form
					
					$(this).unbind();
					$("#assigned_students").find("option").remove();
									
					$("#assign-modal").find(".chosen-list").find("option").each(function(){
						$("#assigned_students").append("<option selected='selected' value='" + $(this).attr("value") + "'>" + $(this).text() + "</option>");
					});
					
					updateStudentAssignedList();
					
					$("#assign-modal").dialog('close');
				});
			}
		);	
		
		return true;
		
	});
	
	function updateStudentAssignedList() {
		
		var assignments = [];
		$("#assigned_students").find("option").each(function(){
			assignments.push($(this).text());
		});
		
		assignments.sort(function(a,b) {
			if (a.text > b.text) return 1;
			else if (a.text < b.text) return -1;
			else return 0
		});
		
		$("#assigned-list").empty();
		
		$("#assigned_count").text(assignments.length);
		if (assignments.length == 1) {
			$("#assigned_plural").text("");
		}
		else {
			$("#assigned_plural").text("s");
		}
		
		$.each(assignments, function(i, v){
			$("#assigned-list").append("<div class='student'>" + v + "</div>");
		});
	}
	
	$("input").keypress(function (evt) {
		//Deterime where our character code is coming from within the event
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) { //Enter key's keycode
			return false;
		}
		
		return true;
	});	
	
	$("#see-shift-list").click(function(e){
		e.preventDefault();
		if ($("#shift-list").css("display") == "none") {
			$(this).text("Hide list");
			$("#shift-list").slideDown();
		}
		else {
			$(this).text("See list");
			$("#shift-list").slideUp();
		}
	});
	
	$("#custom_name").keypress(function(){
		if ($(this).val().length == 128){
			return false;
		}
		return true;
	});
	
	$("#see-assigned-list").click(function(e){
		e.preventDefault();
		if ($("#assigned-list").css("display") == "none") {
			$(this).text("Hide list");
			$("#assigned-list").slideDown();
		}
		else {
			$(this).text("See list");
			$("#assigned-list").slideUp();
		}
		
		adjustShiftGuidedTourStep(5);
		adjustShiftGuidedTourStep(6);
	});
	
	$("#base").change(function(){
		locationChangeWarningListener();
	});
	
	$("#site").change(function(){
		
		$("#base_chzn").css("opacity", "0.5");
		
		$.post(
			'/scheduler/shift/get-base-options',
			{siteId: $("#site").val()},
			function(response){
				$("#base_chzn").remove();
				$("#base").removeClass("chzn-done");
				$("#base").find("option").remove();
				$("#base").append(response);
				$("#base").chosen();
			}	
		);
		
		
		$.post(
			'/scheduler/shift/get-preceptor-options',
			{siteId: $("#site").val()},
			function(response){
				$("#preceptors_chzn").remove();
				$("#preceptors").removeClass("chzn-done");
				$("#preceptors").find("option").remove();
				$("#preceptors").append(response);
				$("#preceptors").chosen();
			}	
		);
		
		checkSharingAdmin();
		locationChangeWarningListener();
	});
	
	function locationChangeWarningListener() {
		if ($("#site_change_warning").val() == "1") {
			if ($("#site").val() != initial_site_id) {
				$("#site_change_warning_display").slideDown();
				
				// remove each assigned students option
				$("#assigned_students").find("option").each(function(){
					if ($(this).text().indexOf("*") != -1) {
						$(this).remove();
					}
				})
				updateStudentAssignedList();

			}
			else {
				$("#site_change_warning_display").slideUp();
				if (inital_student_assignments) {
					$("#assigned_students").empty();
					$.each(inital_student_assignments, function(i, v){
						$("#assigned_students").append(v);
					});
				}
				
				updateStudentAssignedList();
			}
		}
	}
	
	$("#repeat").change(function(){
		if ($(this).attr("checked")) {
			$("#repeat-options").css("opacity", "1");
			$("#repeat-disable-screen").hide();
		}
		else {
			$("#repeat-options").css("opacity", "0.5");
			$("#repeat-disable-screen").show();

		}
	});
	
	$(".tab").click(function(){
		
		if (!$(this).hasClass("selected-tab")) {
			openNewTab($(this), $(this).attr("data-tabContentId"));
		}
	});
	
	$(".next-tab").click(function(e){
		e.preventDefault();
		var tabContentId = $(this).attr("data-tabContentId");
		var tab = null;
		$(".tab").each(function(){
			if ($(this).attr("data-tabContentId") == tabContentId) {
				tab = $(this);
			}
		});
		
		// scroll to top
		$('html,body').animate({scrollTop: $("h1").first().offset().top},'slow');
		
		openNewTab(tab, tabContentId);
	});
	
	function checkSharingAdmin() {
		
		var was_sharing = [];
		
		$.post("/scheduler/shift/get-sharing-options",
			{site_id: $("#site").val(),
			event_id: $("#admin-edit-event-form").attr("data-eventId")
			},
			function(response){
				
				if (response) {
					var programs = response.programs;
					var is_sharing = response.sharing;
					
					if (is_sharing) {
						
						$("#program-list-table").empty();
						
						$("#sharing_programs").find("option").remove();
						
						$("#program_count").text(programs.length);
						
						
						if (programs.length == 1) {
							$("#program_plural").text("");
						}
						else {
							$("#program_plural").text("s");
						}
						
						$("#edit-sharing-programs").unbind();
						$("#edit-sharing-programs").click(function(e){
							e.preventDefault();
							if ($("#sharing-programs-list").css("display") == "none") {
								$("#sharing-programs-list").slideDown();
								$("#all-checker-wrapper").slideDown();
							}
							else {
								$("#sharing-programs-list").slideUp();
								$("#all-checker-wrapper").slideUp();
							}
						});
						
						
						$.each(programs, function(i, v){
							$("#program-list-table").append(v);
						});
						
						$("#sharing-programs-list").find("input").each(function(){
							var selected = "";
							if ($(this).attr("data-isSharedWith") == "true") {
								selected = "selected='selected'";
								if (is_edit) {
									was_sharing.push($(this).attr("value"));
								}
							}
							
							//console.log(selected);
							$("#sharing_programs").append("<option " + selected + " value='" + $(this).attr("value") + "'>" +  $(this).attr("value") + "</option>");
						});
						
						$("#sharing-options-wrapper").slideDown();
						
						$("#program-list-table").find("tr").each(function(){
							$(this).unbind();
							$(this).click(function(e){
								if ($(e.target).attr("type") != "checkbox") {
									checkbox = $(this).find("input[value='" + $(this).attr("data-checkboxid") + "']");
									if (checkbox.prop("checked")) {
										selectProgram(false, checkbox, was_sharing);
									}
									else {
										selectProgram(true, checkbox, was_sharing);
									}
								}
								
								updateTotalProgramsText();
							});
						});
						
						$("#program-list-table").find("input[type='checkbox']").change(function(){
							if ($(this).attr("checked")) {
								selectProgram(true, $(this), was_sharing);
							}
							else {
								selectProgram(false, $(this), was_sharing);
							}
							
						});
						
						$("#program-all-checker").unbind();
						$("#program-all-checker").click(function(e){
							e.preventDefault();
							var deselect = ($(this).text() == "Select all");
							
							$("#program-list-table").find("input").each(function(){
								selectProgram(deselect, $(this), was_sharing);
							});
							
							if (deselect) {
								$(this).text("Select none");
							}
							else {
								$(this).text("Select all");
							}
							
							updateTotalProgramsText();
						});

                        updateTotalProgramsText();


                        if (!is_sharing || $("#program_count").text() == "0") {
							was_sharing = false;
							$("#program-all-checker").text("Select all");
							$("#share_flag-slider-button").trigger("click");
						}
						
						$("#share_flag").change(function(){
							updateDropOptions(was_sharing);
						});
						
					}
					
					else {
						$("#sharing-options-wrapper").slideUp();
					}
				}
				else {
					$("#sharing-options-wrapper").slideUp();
				}
			}, "json").fail(function(){brokenRobot();});
	}
	
	function updateDropOptions(was_sharing) {
		if (is_edit) {
			// if any of the 'was_sharing' programs are no longer selected, give them the option to drop students
			show_options = false;
			
			if ($("#sharing-off-blocker").css("display") == "block"){
				// sharing is off if there are any 'was_sharing' they aren't being shared anymore - warn the user
				if (was_sharing) {
					show_options = true;
				}
			}
			else {
			
				$("#program-list-table").find("input[type='checkbox']").each(function(){
					if (!$(this).attr("checked")) {
						if ($.inArray($(this).attr("value"), was_sharing) != -1) {
							// it isn't checked, and it was in the sharing collection so we need to give our user the option to drop students
							show_options = true;
						}
					}
				});
				
			}
			
			
			if (show_options) {
				$("#drop-students-options").slideDown();
			}
			else {
				$("#drop-students-options").slideUp();
			}
		}
		
	}
	
	function updateAssignDisable() {
		var val = parseInt($("#slots").val());
		if (!$("#slots").val()) {
			val = 0;
		}
		if (val < 1) {
			$("#assign-message").show();
			$("#assign").button("disable").css("opacity", "0.4");
		}
		else {
			$("#assign-message").hide();
			$("#assign").button("enable").css("opacity", "1");
		}
	}
	
	function selectProgram(select_it, program_checkbox, was_sharing) {
		var row = program_checkbox.parent().parent();
		
		if (select_it) {
			program_checkbox.attr("checked", "checked");
			row.addClass("selected-row");
			$("#sharing_programs").find("option[value='" + row.attr("data-checkboxid") + "']").attr("selected", "selected");
		}
		else {
			program_checkbox.removeAttr("checked");
			row.removeClass("selected-row");
			$("#sharing_programs").find("option[value='" + row.attr("data-checkboxid") + "']").removeAttr("selected");
		}
		
		updateDropOptions(was_sharing);

	}
	
	function updateTotalProgramsText() {
		var programs_count = 0;
		
		$("#sharing_programs").find("option:selected").each(function(){
			programs_count++;
		});
		
		$("#program_count").text(programs_count);
		
		if (programs_count == 1) {
			$("#program_plural").text("");
		}
		else {
			$("#program_plural").text("s");
		}
		
	}
	
	function updatePermissionsSummary() {
		var drop = getPermission("drop");
		var cover = getPermission("cover");
		var swap = getPermission("swap");
		adjustIndividualPermissionSummary("drop", drop);
		adjustIndividualPermissionSummary("cover", cover);
		adjustIndividualPermissionSummary("swap", swap);
	}
	
	var flippies = ['drop', 'cover', 'swap', 'drop_permission', 'cover_permission', 'swap_permission'];
	
	$.each(flippies, function(i, v){
		$("#" + getShiftType() + "_" + v).change(function(){
			updatePermissionsSummary();
		});
	});
	
	var signup_breaker = false;
	
	if($("#students_can_sign_up").val() == "1"){
		$("#students_can_sign_up_flippy").removeClass("cannot").addClass("can").text("can");
		$("#windows").show();
		signup_breaker = false;
	}
	else {
		signup_breaker = true;
		$("#windows").hide();
	}
	
	
	$("#students_can_sign_up_flippy").click(function(){
		triggerStudentFlippyChange();
	});
	
	function triggerStudentFlippyChange() {
		
		var adjust_guided_tour_step = true;
		
		if ($("#windows").css("display") == "none") {
			$("#students_can_sign_up").val("1");
			$("#windows").slideDown();
			if (signup_breaker) {
				// warn them that even if they have active windows, students still cant sign up because of scheduler settings
				$("#breaker-cannot-sign-up-notice").fadeIn();
				$("#no-active-windows-noitce").slideUp();
			}
			else {
				triggerActiveWindowsMessage();
				adjust_guided_tour_step = false;
			}
		}
		else {
			$("#students_can_sign_up").val("0");
			$("#windows").slideUp();
			// warn them that even if they have active windows, students still cant sign up because of scheduler settings
			$("#breaker-cannot-sign-up-notice").hide();
			$("#no-active-windows-notice").hide();
		}
		
		if (adjust_guided_tour_step) {
			adjustShiftGuidedTourStep(6);
		}
	}
	
	function triggerActiveWindowsMessage() {
		var has_active_window = false;
	
		if ($("#windows-table").find(".on").length > 0) {
			has_active_window = true;
		}
		
		if (has_active_window) {
			$("#no-active-windows-notice").slideUp();
		}
		else {
			if (!signup_breaker) {
				$("#no-active-windows-notice").slideDown();
			}
		}
		
		adjustShiftGuidedTourStep(6);
		
	}
	
	$("#edit-permissions").click(function(e){
		
		e.preventDefault();
		
		if ($("#edit-change-permissions-wrapper").css("display") == "none") {
			$("#edit-change-permissions-wrapper").slideDown();
		}
		else {
			$("#edit-change-permissions-wrapper").slideUp();
		}
		
	});
	
	function adjustIndividualPermissionSummary(permission, vals) {
		
		if (vals['can'] == "0") {
			$("#" + permission).find("img").attr("src", "/images/icons/denied.png");
			$("#" + permission).find(".permission-needed").hide();
		}
		else {
			$("#" + permission).find("img").attr("src", "/images/icons/approved.png");
			
			if (vals['needs_permission'] == "1") {
				$("#" + permission).find(".permission-needed").show();
			}
			else {
				$("#" + permission).find(".permission-needed").hide();
			}
		}
		
	}
	
	function getPermission(permission) {
		type = getShiftType();
		perms = {};
		var can = $("#" + getShiftType() + "_" + permission).val();
		var needs_permission = $("#" + getShiftType() + "_" + permission + "_permission").val();
		perms['can'] = can;
		perms['needs_permission'] = needs_permission;
		return perms;
	}
	
	function moveTicker() {
		var newLeft = $(".selected-tab").offset().left;
		newLeft = newLeft + (($(".selected-tab").find(".tab-text").width() / 2)-15);
		$("#ticker-wrapper").css("margin-left", newLeft + "px");
	}
	
	function openNewTab(tab, tabContentId, open_first_tour_step) {
		
		$("#" + $(".selected-tab").attr("data-tabContentId")).hide();
		$(".selected-tab").removeClass("selected-tab");
		tab.addClass("selected-tab");
		$("#" + tabContentId).addClass("temp-absolute").fadeIn().removeClass("temp-absolute");
		moveTicker();
		
		$("#guided_tour_step_2").hide();
		$("#guided_tour_step_3").hide();
		$("#guided_tour_step_4").hide();
		$("#guided_tour_step_5").hide();
		$("#guided_tour_step_6").hide();
		$("#guided_tour_step_7").hide();
		
		if ($("#guided_tour_wrapper").find(".tour_directions").css("display") == "block") {

			setTimeout(function(){
				
				if (tabContentId == "shift-details") {
					$("#guided_tour_step_2").fadeIn("fast");
					$("#guided_tour_step_3").fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[2], 2);
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[3], 3);
					
					if ((!$("#guided_tour_step_2").hasClass("selected_tour_step")) && (!$("#guided_tour_step_3").hasClass("selected_tour_step"))) {
						$("#guided_tour_step_2").trigger("click");
					}
				}
				else if (tabContentId == "assign-allocate") {
					$("#guided_tour_step_4").fadeIn("fast");
					$("#guided_tour_step_5").fadeIn("fast");
					$("#guided_tour_step_6").fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[4], 4);
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[5], 5);
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[6], 6);
					
					if ((!$("#guided_tour_step_4").hasClass("selected_tour_step")) && (!$("#guided_tour_step_5").hasClass("selected_tour_step")) && (!$("#guided_tour_step_6").hasClass("selected_tour_step"))) {
						$("#guided_tour_step_4").trigger("click");
					}
					
				}
				else {
					$("#guided_tour_step_7").fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[7], 7);
					
					if (!$("#guided_tour_step_7").hasClass("selected_tour_step")) {
						$("#guided_tour_step_7").trigger("click");
					}
				}
				
			}, 200);
		}
		
	}
	
	function addNewWindow(window_id) {
		var new_window = 0;
		
		// find out what the window_id should be if we don't have one
		if (!window_id) {
			var new_window_count = 1;
			
			$("#windows-table").find(".new_window").each(function(){
				new_window_count++;
			});
			
			new_window = 1;
			window_id = "new_" + new_window_count;
		}
		
		$.post("/scheduler/shift/add-new-window", {window_id: window_id, new_window: new_window, shift_type: getShiftType()},
						function(response){
							showSaveBtn();
							
							if ($("#loading-windows").length > 0) {
								$("#loading-windows").remove();
							}
							
							$("#windows-table").append(response['form']);
							$("#new-window-throbber").remove();
							$("#add-window").css("opacity", "1");
							initWindows(response['id']);
							adjustShiftGuidedTourStep(6);
							
						}, "json").fail(
							function(){
								brokenRobot();
							}
						);
	}
	
	function initWindows(id) {
		
		$("#window_" + id + "_row").find(".call-chosen").each(function(){
            var disable_search = 40;
            var trigger_plural = false;

            if($(this).attr("id").indexOf("group") != -1){
                disable_search = 10;
            }

            // should we also trigger the pluralization thing?
            if($(this).attr("id").indexOf("offset") != -1){
                trigger_plural = true;
            }

            $(this).chosen({disable_search_threshold: disable_search}).change(function(){
                windowChangeListener($(this));

                if(trigger_plural) {
                    $(this).parent().parent().find(".interval_frequency").trigger("change");
                }
            });
        });



		$("#window_" + id + "_row").find(".selectDate").datepicker({
			changeMonth: true,
			changeYear: true
		});
		
		$("#window_" + id + "_row").find('.selectDate').keydown(function() {
			//code to not allow any changes to be made to input field
			return false;
		});
		
		$("#offset_value_end_interval_" + id).keydown(function(event) { numbersOnlyInput(event); }).change(function(){ pluralizeIntervalChosens($(this)); }).trigger("change");
		$("#offset_value_start_interval_" + id).keydown(function(event) { numbersOnlyInput(event); }).change(function(){ pluralizeIntervalChosens($(this)); }).trigger("change");

		$("#window_" + id + "_row").find("input[type='checkbox']").sliderCheckbox({onText: "On", offText: "Off"});
		
		$("#window_" + id + "_row").find("input[type='checkbox']").change(function(){
			triggerActiveWindowsMessage();
		});
		
		// set a change listener to update the session
		$("#window_" + id + "_row").find("input").change(function(){
			windowChangeListener($(this));
		});
		
		// update the value select
		$("#window_" + id + "_row").find(".constraint-type-options").each(function(){
			
			$(this).change(function(){
				changeConstraintValuesSelect($(this));
			});
			
		});
		
		$("#window_" + id + "_row").find(".cv-col").find("select").each(function(){
			if (!$(this).val()) {
				var count = 0;
				$(this).parents("tr").find(".constraint-type-options").each(function(){
					count++;
				});
					
				if (count > 1) {
					$(this).parent().addClass("hidden-constraint-type-options").hide();
				}
				
			}
		});
		
		$("#window_" + id + "_row").find(".add-constraint").click(function(e){
			e.preventDefault();
			$(this).fadeOut();
			var current_constraint = $(this).parent().find("select").val();
			$(this).parents('.constraint').parent().find(".constraint").each(function(){
				
				if ($(this).css("display") == "none") {
					$(this).slideDown();
					var new_constraint = 1;
					if (current_constraint == "1") {
						new_constraint = 2;
					}
					$(this).find(".constraint-type-options").find("select").val("").val(new_constraint).trigger("liszt:updated");
					//changeConstraintValuesSelect($(this).find(".constraint-type-options"));
				}
				
			});
				
		});
		
		$("#window_" + id + "_row").find(".offset-type-change-trigger").button().click(function(e){
			e.preventDefault();
			var start_end = $(this).attr("data-time");
			var temp_window_id = $(this).attr("data-windowTempId");
			$("#offset-type-options-wrapper").attr("trigger", temp_window_id).fadeIn().css("top", $(this).position().top-140).css("left", $(this).position().left-35);
			
			$(".offset-type-option").unbind();
			$(".offset-type-option").click(function(e){
				e.preventDefault();
				var type = $(this).attr("data-offsettype");
				var type_id = $(this).attr("data-offsettypeid");
				$("." + start_end + "-offset-type-elements-wrapper-" + temp_window_id).hide();
				$("#" + start_end + "_" + type + "_" + temp_window_id).fadeIn();
				$("#offset_type_" + start_end + "_" + temp_window_id).val(type_id);
				$("#offset-type-options-wrapper").fadeOut();
				windowChangeListener($("#offset_type_" + start_end + "_" + temp_window_id));
			});
			
			$("#close-offset-types").click(function(e){
				e.preventDefault();
				$("#offset-type-options-wrapper").fadeOut();
			});
		});

        /*
		$(".remove-search").each(function(){

            /*
			var chzn = $("#" + $(this).attr("id") + "_chzn");
			chzn.find(".chzn-search").remove();
			if (chzn.find(".chzn-drop").width() == "120") {
				chzn.find(".chzn-drop").css("width", "118");
			}
			else if (chzn.find(".chzn-drop").width() == "78") {
				chzn.find(".chzn-drop").css("width", "68");
			}
			else if (chzn.find(".chzn-drop").width() == "98") {
				chzn.find(".chzn-drop").css("width", "64");
				chzn.find(".chzn-results").css("max-height", "150px");
			}*/

		//});

        $("#window_" + id + "_row").find(".dates-col").find(".chzn-single").css("width", "75px");
		windowChangeListener($("#offset_value_end_interval_" + id));
	}
	
	function changeConstraintValuesSelect(trigger) {
		var value_type = "";
		
		if(trigger.val() == "1"){value_type = "group";}
		else {value_type = "cert";}
		
		trigger.parents(".constraint-type-options").parent().find(".cv-col").find("dd").each(function(){
			if ($(this).attr("id").indexOf(value_type) != -1) {
				//$(this).addClass("absolute-cv-fix").fadeIn();
				$(this).removeClass("hidden-constraint-type-options").fadeIn();
			}
			else {
				$(this).addClass("hidden-constraint-type-options").fadeOut();
			}
			
		});
		
	}

	
	function getWindowData(row)
	{
		var data = {};
		var window_id = row.attr("selector_id");
		
		data['active'] = 0;
		
		if ($("#window_active_" + window_id + "-slider-button").hasClass("on")){
			data['active'] = 1;
		}
		
		data['offset_type_end'] = $("#offset_type_end_" + window_id).val();
		data['offset_type_start'] = $("#offset_type_start_" + window_id).val();
		
		data['offset_value_end'] = getOffestValue('end', data['offset_type_end'], window_id);
		data['offset_value_start'] = getOffestValue('start', data['offset_type_start'], window_id);
		

		/*
		data['offset_value_start_static'] = $("#offset_value_start_static_" + window_id).val();
		data['offset_value_start_interval'] = $("#offset_value_start_interval_" + window_id).val();
		data['offset_value_start_interval_type'] = $("#offset_value_start_interval_type_" + window_id).val();
		data['offset_value_start_prevMonth'] = $("#offset_value_start_prevMonth_" + window_id).val();
		
		data['offset_value_end_static'] = $("#offset_value_end_static_" + window_id).val();
		data['offset_value_end_interval'] = $("#offset_value_end_interval_" + window_id).val();
		data['offset_value_end_interval_type'] = $("#offset_value_end_interval_type_" + window_id).val();
		data['offset_value_end_prevMonth'] = $("#offset_value_end_prevMonth_" + window_id).val();
		*/
		
		// now get cert values if there are any
		row.find(".constraint").each(function(){
			
			if ($(this).css("display") != "none"){
				
				$(this).find(".cv-col").find("dd").each(function(){
					
					if ($(this).css("display") != "none") {
						
						var vals = {};
						
						$(this).find("select").find("option:selected").each(function(){
							vals[$(this).val()] = $(this).text();
						});
						
						if ($(this).attr("id").indexOf("cert") != -1){
							// certlevel constraints
							data['cert_constraint'] = vals;
						}
						else {
							
							// student group constraints
							data['group_constraint'] = vals;
							
						}
					}
					
				});	
			}
			
		});
		
		return data;
	}
	
		

	
	function windowChangeListener(trigger) {
	}
	
	
	function showSaveBtn() {
		$("#finishing-event-load-throbber").hide();
		$("#save").show();
		
		if (is_non_admin) {
			if ($("#non-admin-edit-event-form").hasClass("not-all-future-events-in-series")) {
				$("#super-limited-bob-blocker").css("height", $("#signup-perm-wrapper").height());
			}
		}
	}
	
	function adjustShiftGuidedTourStep(step_number)
	{
		if ($("#guided_tour_wrapper").find(".tour_directions").length > 0){
			if ($("#guided_tour_wrapper").find(".tour_directions").css("display") == "block") {
				$("#guided_tour_step_" + step_number).hide();
				setTimeout(function(){
					$("#guided_tour_step_" + step_number).fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[step_number], step_number);
				}, 400);
			}
		}
	}
	
	$("#expand-cal").find("a").each(function(){
		$(this).click(function(){
			
			adjustShiftGuidedTourStep(3);
			
		});
	});
	
	$.fn.serializeAssoc = function()
	{
		var data = { };
		$.each( this.serializeArray(), function( key, obj ) {
			var a = obj.name.match(/(.*?)\[(.*?)\]/);
			if(a !== null)
			{
				var subName = new String(a[1]);
				var subKey = new String(a[2]);
				if( !data[subName] ) data[subName] = { };
				if( data[subName][subKey] ) {
					if( $.isArray( data[subName][subKey] ) ) {
						data[subName][subKey].push( obj.value );
					} else {
						prev_val = data[subName][subKey];
						data[subName][subKey] = [prev_val];
						data[subName][subKey].push( obj.value );
					};
				} else {
					data[subName][subKey] = obj.value;
				};  
			} else {
				var keyName = new String(obj.name);
				if( data[keyName] ) {
					if( $.isArray( data[keyName] ) ) {
						data[keyName].push( obj.value );
					} else {
						data[keyName] = { };
						data[keyName].push( obj.value );
					};
				} else {
					data[keyName] = obj.value;
				};
			};
		});
		return data;	
	};
	
});

var getOffestValue = function(start_end, type_id, window_id)
{
	var offset_value = "";
	
	if (type_id == 1) {
		offset_value = [$("#offset_value_" + start_end + "_static_" + window_id).val()];
	}
	else if (type_id == 2) {
		offset_value = [$("#offset_value_" + start_end + "_interval_" + window_id).val(), $("#offset_value_" + start_end + "_interval_type_" + window_id).val()];
	}
	else {
		offset_value = [$("#offset_value_" + start_end + "_prevMonth_" + window_id).val()];
	}
	
	return offset_value;
}

var getShiftType;
getShiftType = function() {
	return $("#shift_type").val();
};

var initExtendSeriesPopup = function()
{
    setTimeout(function(){

        // first thing to do: figure out the pattern (if not 'day')
        var unsorted_pattern = [];
        var pattern = [];
        var anchors = [];

        var frequency_type = $("#extend_series_frequency_type").text();

        if(frequency_type.indexOf("day") == -1){

            $("#shift-list").find(".shift").each(function(){
                var shift_date = new Date($(this).text());
                var easy_date = moment(shift_date);
                var pattern_format = "ddd";
                var sort_pattern_format = "d";

                if(frequency_type.indexOf("week") == -1) {
                    pattern_format = "Do";
                    sort_pattern_format = "D";
                }

                var day = easy_date.format(pattern_format);

                $(this).addClass(day);

                // as soon as the pattern repeats, bail.
                if($.inArray(day, pattern) != -1) {
                    return;
                }
                else {
                    var sort_day = easy_date.format(sort_pattern_format);
                    unsorted_pattern[sort_day] = day;
                    anchors.push(shift_date);
                }

            });

            $.each(unsorted_pattern, function(i, v){
                if(v){ pattern.push(v); }
            });

            $("#extend_series_pattern").text(pattern.join("/") + " ");
        }
        else {
            pattern.push("shift");
        }

        setTimeout(function(){
            var label_width = $("#extend_series_pattern").text().length;
            var calculated_width = 455;
            calculated_width = label_width + calculated_width;
            $("#extend_series_modal").css("width", calculated_width);
        }, 100);


        var popup = $("#extend_series_modal_wrapper");

        var last_date_editing = new Date($("#shift-list").find(".shift").last().text());
        $("#extend_series_date").datepicker({ minDate: last_date_editing, changeMonth: true,changeYear: true, maxDate:"+5y" }).val(moment(last_date_editing).format("MM/DD/YYYY"));

        $(".extend_series_popup_buttons").find("a").button();

        $("#cancel_extend_series_popup").click(function(e){
            e.preventDefault();
            closeExtendSeriesPopup(popup, true);
        });

        $("#update_extend_series_popup").click(function(e){
            e.preventDefault();
            $(".extending_date").remove();
            $("#adding_dates").attr("value", "");

            var new_dates = [];

            $.each(pattern, function(i, v){

                var last_date = new Date($("#shift-list").find("." + v).last().text());
                var extended_date = new Date($("#extend_series_date").val());
                var pattern_dates = [];
                var i = 0;
                var frequency = parseInt($("#extend_series_frequency").text());

                pattern_dates[0] = last_date;

                while(pattern_dates[i] < extended_date){

                    if(i == 0) {
                        most_recently_added_date = last_date;
                    }
                    else {
                        most_recently_added_date = pattern_dates[i];
                    }

                    var moment_obj = moment(most_recently_added_date);

                    moment_obj.add(frequency_type, frequency);

                    i++;

                    var date_to_add = new Date(moment_obj.format("MMMM D YYYY"));

                    // we could be in this loop, but not want to add it. Make sure this isn't beyond our last date...
                    if(date_to_add <= extended_date) {
                        pattern_dates[i] = date_to_add;
                    }

                }

                // remove the first instance
                pattern_dates.shift();

                new_dates = new_dates.concat(pattern_dates);

            });

            new_dates.sort(date_sort_asc);

            // shift type?

            var shift_date_classes = $("#shift-list").find(".shift").first().attr("class");
            var dates_values = [];
            var shift_list_html = "";

            $.each(new_dates, function(i, v){
                var easy_date = moment(v);
                shift_list_html += "<div class='extending_date " + shift_date_classes + "'><i>Adding</i>" + easy_date.format('MMM D, YYYY') + "</div>";
                dates_values.push(easy_date.format("MM/DD/YYYY"));
            });

            $("#shift-list").append(shift_list_html);
            $("#adding_dates").attr("value", dates_values.join(","));

            setTimeout(function(){

                var first_extended_date = $(".extending_date").first();

                $("#shift-list").scrollTo(first_extended_date.prev(), 'slow');

                setTimeout(function(){

                    $(".extending_date").effect("highlight", 'slow');

                }, 400);

            }, 100);


            closeExtendSeriesPopup(popup, true);

            // now update the number of shifts we're adding/editing
            var total_shifts = $("#shift-list").find(".shift").length;

            $("#shift_count").text(total_shifts);
            $("#save").attr("value", "Save " + total_shifts + " shifts");
            $("#assigned_shift_count").text("all " + total_shifts + " shifts");

        });

        var trigger =  $("#extend_series_trigger");

        trigger.button().click(function(e){

            e.preventDefault();

            popup.css("top", trigger.position().top+37).css("left", trigger.position().left+5);

            if(popup.css("display") == "none"){
                popup.fadeIn("fast");
            }
            else {
                closeExtendSeriesPopup(popup, false);
            }

        });

    }, 300);



}; // end initExtendSeriesPopup



var date_sort_asc = function (date1, date2) {
    // This is a comparison function that will result in dates being sorted in
    // ASCENDING order. As you can see, JavaScript's native comparison operators
    // can be used to compare dates.
    if (date1 > date2) return 1;
    if (date1 < date2) return -1;
    return 0;
};


var initSingleDatePicker = function()
{
    var short_month_names = [ "Jan", "Feb", "Mar", "Apr", "May", "June",
        "July", "Aug", "Sept", "Oct", "Nov", "Dec" ];

    setTimeout(function(){
        var shift_element = $("#shift-list").find(".shift");

        if($("#single_shift_date").length > 0) {

            shift_element.css("border", "0em");
        }

        var popup = $("#edit_single_shift_date_modal_wrapper");

        var today = new Date();
        $("#single_shift_date").datepicker({ minDate: today });

        $(".edit_single_shift_date_popup_buttons").find("a").button();

        $("#cancel_single_shift_date_popup").click(function(e){
            e.preventDefault();
            closeSingleDateChangePopup(popup, true);
        });

        $("#update_single_shift_date_popup").click(function(e){
            e.preventDefault();

            var current_text = shift_element.text();

            closeSingleDateChangePopup(popup, false);
            var new_date = new Date($("#single_shift_date").val());

            var new_month = short_month_names[new_date.getMonth()];
            var new_day = new_date.getDate();
            var new_year = new_date.getFullYear();
            var new_display = new_month + " " + new_day + ", " + new_year;

            if(new_display != current_text) {
                shift_element.text(new_display).effect("highlight", "slow");
            }

        });

        var trigger =  $("#edit_Single_shift_date_trigger");

        trigger.button().click(function(e){

            e.preventDefault();

            popup.css("top", trigger.position().top-118).css("left", trigger.position().left-30);

            if(popup.css("display") == "none"){
                popup.fadeIn("fast");
            }
            else {
                closeSingleDateChangePopup(popup, false);
            }

        });

    }, 300);




}; // end initSingleDatePicker

var getCurrentSingleShiftListValue = function()
{
    return new Date($("#shift-list").find(".shift").first().text());
};

var closeSingleDateChangePopup = function(popup, resetDatePicker)
{
    popup.fadeOut("fast");

    if(resetDatePicker) {
        // return the date picker value to whatever is in the date list
        var current_date = getCurrentSingleShiftListValue();

        var current_month = current_date.getMonth() + 1;
        var current_day = current_date.getDate();

        if (current_month.length == 1) {
            current_month = "0" + current_month;
        }

        if (current_day.length == 1) {
            current_day = "0" + current_day;
        }

        setTimeout(function () {
            $("#single_shift_date").val(current_month + "/" + current_day + "/" + current_date.getFullYear());
        }, 300);
    }
};

var closeExtendSeriesPopup = function(popup, resetDatePicker)
{
    popup.fadeOut("fast");

    if(resetDatePicker) {
        // return the date picker value to whatever is in the date list
        var last_date_dom_element = $("#shift-list").find(".shift").last();
        var last_date = last_date_dom_element.text();

        if(last_date_dom_element.hasClass("extending_date")){
            last_date = last_date.substring(6, last_date.length);
        }

        var current_date = new Date(last_date);

        var current_month = current_date.getMonth() + 1;
        var current_day = current_date.getDate();

        if (current_month.length == 1) {
            current_month = "0" + current_month;
        }

        if (current_day.length == 1) {
            current_day = "0" + current_day;
        }

        setTimeout(function () {
            $("#extend_series_date").val(current_month + "/" + current_day + "/" + current_date.getFullYear());
        }, 300);
    }
};

var numbersOnlyInput = function(event)
{
    // Allow: backspace, delete, tab, escape, and enter
    if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
            // Allow: Ctrl+A
        (event.keyCode == 65 && event.ctrlKey === true) ||
            // Allow: home, end, left, right
        (event.keyCode >= 35 && event.keyCode <= 39)) {
        // let it happen, don't do anything
        return;
    }
    else {
        // Ensure that it is a number and stop the keypress
        if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
            event.preventDefault();
        }
    }
};

var pluralizeIntervalChosens = function(interval_input)
{
    if(!interval_input.val() || interval_input.val().length > 2){
        interval_input.val("1");
    }

    // make the chosen elements plural or singular based on the value
    var singular = true;
    if(interval_input.val() != 1){  singular = false; }

    var interval_type_chosen = interval_input.parent().parent().find(".chzn-container");
    var currently_selected_element = interval_type_chosen.find("a").find("span");
    var current_selected_label = currently_selected_element.text();

    if(current_selected_label.indexOf("s") != -1){
        if(singular){
            currently_selected_element.text(current_selected_label.substr(0, current_selected_label.length-1));
        }
    }
    else {
        if(!singular){
            currently_selected_element.text(current_selected_label + "s");
        }
    }
}
