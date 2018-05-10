$(function(){

    function handleQueuedEdits(){
        // hide queued edits notice
        setTimeout(function () {
            $("#queued-edits-notice").slideUp();
        }, 10000); // close after 10 seconds no matter what

        // deal with searching for queued edits, if necessary
        if ($(".manage-requirement-row.pending").length > 0)
        {
            // first, check for edits
            // collect the requirement IDs of the pending requirements
            var reqIds = []
            $(".manage-requirement-row.pending").each(function (i, elem) {
                reqIds.push($(elem).attr('data-req-id'));
            });

            if(reqIds){
                $("#queued-edits-notice").css("display","inline");
            }


            // start polling the server to see if reqs are being edited
            (function poll() {
                setTimeout(function () {
                    $.ajax({
                        url: "/scheduler/compliance/check-queued-edits",
                        type: "POST",
                        dataType: "json",
                        data: {req_ids: reqIds},
                        success: function (data) {
                            // update any rows that have finished processing
                            if ($(data.reqsUpdated).length > 0) {
                                updateRows(data.reqsUpdated);
                            }

                            if (data.waiting) {
                                //Setup the next poll recursively
                                poll();
                            } else {
                                $("#queued-edits-notice").slideUp(); // if the notice is still showing
                            }
                        }
                    });
                }, 5000); // run every 5 seconds while we have outstanding edits
            })();
        }
    }

    handleQueuedEdits();

	//Setup button bar
	$("#checkbox-selector").button().addClass("small");
	$("#edit-requirements, #assign-requirements, #more-selector").button({ disabled: true }).addClass("small");
	
	// disable controls if there are no reqs
	if ($("#no-reqs").length > 0) {
		$("#controls-blocker").show();
		$("#requirement-filters-wrapper").css("opacity", ".35");
		$("#checkbox-selector").button("option", "disabled", true);
	}
	
	$("#checkbox-selector").click(function(e) {
		e.preventDefault();
		if ($("#checkbox-selector-menu").css("display") == "none") {
			$("#checkbox-selector-menu").fadeIn(100);
		} else {
			$("#checkbox-selector-menu").fadeOut(100);
		}
	});
	
	$("#edit-requirements").click(function(e){
		e.preventDefault();
		$("#edit-requirements").css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='left:5.2em;'>");
		var checkedReqs = getCheckedReqIds();
		window.location.assign("/scheduler/compliance/edit-requirement/id/" + checkedReqs[0]);
	});
	
	// open the auto-assign modal
	$("#auto-assign-requirements").click(function(e) {
		e.preventDefault();
		var checkedReqs = getCheckedReqIds();
		$("#more-selector").css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='right:2.7em;'>");
		$.post("/scheduler/compliance/generate-auto-assign", {"requirement_ids" : checkedReqs},
			function(resp) {
				$("#autoAssignRequirementDialog").html($(resp).html());
				initAASubForm();
				initAutoAssignChangeAction();
				initBulkReqEditModal('aa');
				initAAModal();
				$("#load-modal-throbber").remove();
				$("#more-selector").css("opacity", "1");
				$("#autoAssignRequirementDialog").dialog("open");
			});
		
	});
	
	// open the notifications modal
	$("#notification-requirements").click(function(e) {
		e.preventDefault();
		var checkedReqs = getCheckedReqIds();
		$("#more-selector").css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='right:2.7em;'>");
		$.post("/scheduler/compliance/generate-notifications-modal", {"requirement_ids" : checkedReqs},
			function(resp) {
				$("#notificationsRequirementDialog").html($(resp).html());
				initNotificationsSubForm();
				initBulkReqEditModal('n');
				initNotificationsModal();
				$("#load-modal-throbber").remove();
				$("#more-selector").css("opacity", "1");
				$("#notificationsRequirementDialog").dialog("open");
			});
		
	});
	
	// open the assign modal
	$("#assign-requirements").click(function(e) {
		e.preventDefault();
		// function located in requirement-assign-modal.js
		openModalViaManage($(this), getCheckedReqIds());
	});
	
	// if they click on anything other than whats inside the options menu, close it
	$('html').click(function(e) {
		var target = e.target;
		if ($(target).parents("#checkbox-selector").length == 0) {
			$("#checkbox-selector-menu").fadeOut(100);
		}
	});
	
	$("#more-selector").click(function(e) {
		e.preventDefault();
		$("#more-selector-menu").css("left", $(this).position().left);
		if ($("#more-selector-menu").css("display") == "none") {
			$("#more-selector-menu").fadeIn(100);
		} else {
			$("#more-selector-menu").fadeOut(100);
		}
	});
	
	var button_bar = $("#fixed-menu-bar");
	var button_bar_pos = $("#fixed-menu-bar").offset().top;
	var button_bar_height = 60;
	
	$(window).scroll(function() {
		var list_bottom = $("#manage-requirements-container").offset().top + $("#manage-requirements-container").height() - 100;
		if (!button_bar.hasClass("fixed-menu-bar")) {
				list_bottom = list_bottom - button_bar_height;
		}
		var page_pos = $(document).scrollTop();

		if (page_pos > button_bar_pos && page_pos < list_bottom) {
			button_bar.addClass("fixed-menu-bar");
		}
		else {
			button_bar.removeClass("fixed-menu-bar");
		}
		
	});
	
	
	// if they click on anything other than whats inside the options menu, close it
	$('html').click(function(e) {
		var target = e.target;
		if ($(target).parents("#more-selector").length == 0) {
			$("#more-selector-menu").fadeOut(100);
		}
	});
	
	$("#checkbox-selector-menu ul li").click(function(e){
		checkRequirements($(this).text());
	});

    function updateRows(rows) {
        $.each(rows, function(reqId, html) {
            $("#requirement-"+reqId).replaceWith(html);
        });
        initManageRequirementsTable();
    }
	
	function checkRequirements(toggle) {
		switch (toggle) {
			case "All":
				$("input[name='requirements[]']").prop("checked", true).triggerHandler('change');
				break;
			case "None":
				$("input[name='requirements[]']").prop("checked", false).triggerHandler('change');
				break;
			case "Active":
				$("input[name='requirements[]']").prop("checked", false)
				$("input[name='requirements[]']").each(function(i, el){
					$(this).prop("checked", $("#toggle_" + $(el).val()).is(":checked")).triggerHandler('change');
				});
				break;
			case "Inactive":
				$("input[name='requirements[]']").prop("checked", false).triggerHandler('change');
				$("input[name='requirements[]']").each(function(i, el){
					$(this).prop("checked", $("#toggle_" + $(el).val()).is(":not(:checked)")).triggerHandler('change');
				});
				break;
		}
	}
	
	function getCheckedReqIds() {
		var checkedReqs = [];
		$("input[name='requirements[]']:checked:visible").each(function(){
			checkedReqs.push($(this).attr('id').substring(5));
		});
		return checkedReqs;
	}
	
	function toggleButtonBar() {
		$("#assign-requirements, #more-selector").button( "option", "disabled", !$("input[name='requirements[]']:checked:visible").length );
		
		// for now we can only edit one req at a time
		$("#edit-requirements").button( "option", "disabled", !($("input[name='requirements[]']:checked:visible").length == 1));
		
		//disable edit button if the only checked requirement is disabled
		if ($("input[name='requirements[]']:checked:visible").length == 1) {
			var active = $("#toggle_" + $("input[name='requirements[]']:checked:visible").val()).is(":checked");
			$("#edit-requirements").button( "option", "disabled", !active);
		}
	}
	
	function initManageRequirementsTable() {
		$(".active-toggle").each(function(i, el) {
			$(el).sliderCheckbox({onText: "Active", offText: "Inactive", "width": 50, "disabled": $(el).prop("disabled")});
		});
		
		//Toggle the edit/assign/auto-assign buttons
		$("input[name='requirements[]']").change(function(e){
			toggleButtonBar();
		});
		
		//Make sure the requirement checkboxes don't trigger the click events of the parent container
		$("input[name='requirements[]']").click(function(e) {
			e.stopPropagation();
		});
		
		$(".attachment-info-container").hide();
		$(".attachment-specific").hide();
		
		$(".show-specific-attachments").parent().click(function(e){
			$(this).find(".attachment-specific").slideToggle();
			
			var arrow = $(this).find(".show-specific-attachments");
			var plus = arrow.attr('src') == "/images/icons/plus.png";
		
			arrow.attr(
				'src', 
				arrow.attr('src').replace(plus ? 'plus' : 'minus', plus ? 'minus' : 'plus')
			);
		});
		
		$(".requirement-row-header:not('.pending')").unbind("click").click(function(e) {
			toggleInfo($(this));
		});
		
		// expand/collapse all links
		$(".expand-all, .collapse-all").click(function(e) {
			e.preventDefault();
			
			if ($(this).hasClass('expand-all')) {
				var criterion = ":hidden";
			} else {
				var criterion = ":visible";
			}
			
			var category = $(this).parent().parent();
			
			category.find(".manage-requirement-row:not('.pending')").each(function() {
				var header = $(this).find(".requirement-row-header");
				var info = $(this).find(".attachment-info-container");
				if (info.is(criterion)) {
					header.trigger('click');
				}
			});
		});
		
		function toggleInfo(row) {
			var info = $(row).parent().find(".attachment-info-container");
			var arrow = $(row).find(".arrow-toggle");
			
			
			var guided_tour_open = false;
			
			if ($(row).parents("#shared-requirements-container").length <= 0) {
				if ($("#guided_tour_wrapper").find(".tour_directions").length > 0){
					if ($("#guided_tour_wrapper").find(".tour_directions").css("display") == "block") {
						guided_tour_open = true;
					}
				}
				
				if (guided_tour_open) {$("#guided_tour_step_5").hide();}
			}
				
			if (info.is(':visible')) {
				info.slideUp();
				arrow.attr('src', arrow.attr('src').replace('_down', '_right'));
				row.removeClass("selectedRow");
			} else {
				info.slideDown();
				arrow.attr('src', arrow.attr('src').replace('_right', '_down'));
				row.addClass("selectedRow");	
			}
			
			if (guided_tour_open) {
				setTimeout(function(){
					$("#guided_tour_step_5").fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[5], 5);
				}, 400);
			}
			
		}
		
		//Make it so toggling the active/inactive switch brings up a warning about what they're about to do
		$(".requirement-row-header:not('.pending') .slider-button").click(function(e) {
			e.stopPropagation();
			
			$("#activationModal").dialog("open");
			
			var pieces = $(this).prop("id").split("_");
			var subPieces = pieces[1].split("-");
			requirement_id = subPieces[0];
			active = $("#toggle_" + requirement_id).is(":checked");
			user_count = $(this).parents(".manage-requirement-row").find(".assigned_user_count").attr('data-usercount');

			var requirement_title = $(this).parents(".requirement-row-header").find(".requirement-title").text();	
			$("#activationModal .section-header").text(requirement_title);
			if (active) {
				$("#activation-text").show();
				$("#deactivation-text").hide();
				$( "#activationModal" ).dialog( "option", "title", "Activate Requirement" );
			} else {
				$("#activation-text").hide();
				$("#deactivation-text").show();
				$( "#activationModal" ).dialog( "option", "title", "Deactivate Requirement" );
			}
			
		});
	}
	
	//Init the table on page load
	initManageRequirementsTable();
	
	//Chunk of code needed to make :contains case insensitive
	$.expr[":"].contains = $.expr.createPseudo(function(arg) {
		return function( elem ) {
			return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
		};
	});	
	
	//Show/hide requirements based on search field
	$("#requirement_search").fieldtag().keyup(function(){
		searchReqList("#manage-requirements-container", $(this));
		toggleButtonBar();
	});
	
	//Show/hide requirements based on search field
	$("#shared_req_search").fieldtag().keyup(function(){
		searchReqList("#shared-requirements-container", $(this));
	});
	
	function searchReqList(list, searchBox) {
		$(list+" .manage-requirement-row").show();
		$(list+" .requirement-category").show();
		
		if ($(searchBox).val() != "") {
			$(list+" .manage-requirement-row").hide();
			$(list+" .manage-requirement-row .requirement-title:contains('" + $(searchBox).val() + "')").parents(".manage-requirement-row").show();
		}
		
		$(list+" .requirement-category").each(function(i, el) {
			if ($(el).find(".manage-requirement-row:visible").length == 0) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});
		
		if ($(list).find(".requirement-category:visible").length == 0) {
			$(list+" .null-search").html("No results for \""+$(searchBox).val()+"\". Please try another search.").fadeIn();
		} else {
			$(list+" .null-search").hide();	
		}
	}
	
	//Active/Inactive toggle modal dialog
	$("#activationModal").dialog({
		"autoOpen": false,
		"modal": true,
		"width": 600,
		"draggable": false,
		"resizable": false,
		"open": function(event, ui){
			trigger = "";
		},
		"close": function( event, ui ) {
			if (trigger != "ok") {
				revertToggle(requirement_id);
			} else {
				if (user_count > 0) {
					if (user_count == 1) {
						var plural = '';
					} else {
						var plural = 's';
					}
					var user_phrase = user_count + " user" + plural;
					
					busyRobot();
					$("#busy-robot").find("#robot-processing-txt").text("Fisdap Robot is recomputing compliance for " + user_phrase + ".");
				}
				
				$.post("/scheduler/compliance/toggle-requirement", {"active": active, "requirementId": requirement_id}, function(response){
					closeBusyRobot();
                    // dim requirement table and add a throbber
                    $("#manage-requirements-container").animate({opacity: 0.5});

                    var req_throbber_top = $("#fixed-menu-bar").offset().top+60;
                    if ($("#fixed-menu-bar").hasClass("fixed-menu-bar")) {
                        req_throbber_top = 200;
                    }

                    $("#req-throbber").css("top", req_throbber_top).fadeIn();
                    $("#table-blocker").show();

                    $("#queued-edits-notice").slideDown();

                    $("#manage-requirements-container").animate({opacity: 1});

                    $("#req-throbber").fadeOut();
                    $("#table-blocker").hide();

                    $("#manage-requirements-container").html($(response)).fadeIn();
                    initManageRequirementsTable();
                    handleQueuedEdits();
                    // scroll to top
                    $('html,body').animate({scrollTop: 0},'slow');
				}, "json");
			}
		}
	});
	
	$("#activation-cancel-btn").button().click(function(e){
		e.preventDefault();
		$("#activationModal").dialog("close");
	});
	
	$("#activation-btn").button().click(function(e){
		trigger = "ok";
		e.preventDefault();
		$("#activationModal").dialog("close");
	});
	
	function revertToggle(requirement_id) {
		var toggle = $("#toggle_" + requirement_id + "-slider-button");
		
		if($(toggle).hasClass("on")){
			$(toggle).removeClass('on').html("Inactive").parent().next('input[type="checkbox"]').removeAttr('checked');
			$(toggle).css("background", "#888");
			$(toggle).css("border-color", "#666");
		}
		else {
			$(toggle).addClass('on').html("Active").parent().next('input[type="checkbox"]').attr('checked', 'checked');
			$(toggle).css("background", "#3BAAE3");
			$(toggle).css("border-color", "#0f3f74");
		}
	}
	
	//Setup requirement filters form
	$("#requirement-filters-wrapper").fancyFilter({
			width:591,
			closeOnChange: false,
			onFilterSubmit: function(e) {}
	});
	
	$("#requirement-filters-wrapper").delay(200).fadeIn();
	
	
	var chosenId = 0;
	var filters_allStudents = {};
	$("#students").find("option").each(function(){

		var attribs = $(this).text().split("|");
		var name = attribs[0];
		var cert = attribs[1];
		var gradMonth = attribs[2];
		var gradYear = attribs[3];
		var groups = attribs[4].split(",");
		

		$(this).prop("data-certLevel", cert);
		$(this).prop("data-gradMonth", gradMonth);
		$(this).prop("data-gradYear", gradYear);

		$(this).text(name).prop("label", name);

		filters_allStudents[$(this).prop("value")] = {name:name,cert:cert,gradMonth:gradMonth,gradYear:gradYear,groups:groups,chosenId:chosenId};
		chosenId++;
	});
	
	/*
	* get selected certs (for chosen shifts)
	*/
   function getAccountTypes() {
		var types = [];
		if ($("#accountType").val()) {
			$("#accountType").find("option").each(function() {
				if ($(this).attr("selected")) {types.push($(this).text());}
			});
		}
		else {
			$("#accountType").find("option").each(function() {
				types.push($(this).text());
			});
		}
		return types;
	}
	
	/*
	* Update student list - when it's filtered
	*/
	function updateStudentList() {
	   types = getAccountTypes();
	   
	   var newOptions = {};
	   var newOptionsChosenIds = [];
	   var newOptionsCount = 0;
	
	   $.each(filters_allStudents, function(id, attribs){
		   var hasCert = $.inArray(attribs.cert, types);
		   if (hasCert != -1) {
			   // it has everything, we can include it
			   newOptions[id] = attribs;
			   newOptionsChosenIds.push(attribs.chosenId);
			   newOptionsCount++;
		   }
	   });
	
		$("#students_chzn").find(".chzn-results").find("li").addClass("hidden-sd-option");
	 
		$.each(newOptions, function(id, attribs){
			var chosenIdTxt = "students_chzn_o_" + attribs.chosenId;
			var selectedChosenIdTxt = "students_chzn_c_" + attribs.chosenId;
			if (!$("#" + chosenIdTxt).hasClass("result-selected")) {
				$("#" + chosenIdTxt).removeClass("hidden-sd-option");
			}
		});
	 
		$("#students_chzn").find(".result-selected").each(function(){
	 
			// if these aren't in our "newOptions" array, we need to remove them
			var myChosenId = $(this).attr("id").split("_o_");
			myChosenId = parseInt(myChosenId[1]);
			var isThere = $.inArray(myChosenId,newOptionsChosenIds);
	 
			if (isThere == -1) {
				// it's not there... delete it
				$("#students_chzn_c_" + myChosenId).remove();
				$(this).removeClass("result-selected").addClass("active-result");
			}
	 
		});
	 
		// for each of the selected ones, find out if they are in the list
		$("#students_chzn").find(".chzn-choices").effect("highlight", {}, 800);
		addStudentLabel();
	
		//Now deal with instructors
		if ($.inArray("Instructor", types) == -1) {
			$("#instructors_chzn").find(".chzn-results").find("li").addClass("hidden-sd-option");
		} else {
			$("#instructors_chzn").find(".chzn-results").find("li").removeClass("hidden-sd-option");			
		}
		
		$("#instructors_chzn").find(".chzn-choices").effect("highlight", {}, 800);
		addInstructorLabel();
	}
	
	function getStudentListCount() {
		var count = 0;
		$("#students_chzn").find(".chzn-results").find("li").each(function(){
			if ($(this).css("display") != "none"){
				count++;
			}
		});
		return count;
	}
	
	function getInstructorListCount() {
		var count = 0;
		$("#instructors_chzn").find(".chzn-results").find("li").each(function(){
			if ($(this).css("display") != "none"){
				count++;
			}
		});
		return count;
	}
	
	function getStandardListCount(dependentName) {
	var count = 0;
	$("#" + dependentName + "_chzn").find(".chzn-results").find("li").each(function(){
		if (!$(this).hasClass("group-result")) {
			if ($(this).css("display") != "none"){
				// don't count hte "All Clinical Sites", "All Field Sites" or "All lab sites"
				if ($(this).text().indexOf("All ") == -1) {
					count++;
				}
			}
		}
	});
	return count;
}
	
	function addStudentLabel() {
		// no students selected
		// do we have All certs/months/years?
		$(".students-chosen-text").remove();
		var text = "";
		
		if (($("#students_chzn").find(".result-selected").length == 0)) {
			if (getStudentListCount() > 0) {
				var hasSelections = $("#students_chzn").find(".chzn-choices").find(".search-choice").length;
				if (hasSelections == 0) {
					var item_count = getStudentListCount();
					
					if (item_count == 1) {
						text = "1 student...";
					}
					else {
						text = "All " + item_count + " students...";
					}
				}
				else {
					// the text should say 'All students matching above criteria'
					text = "All " + getStudentListCount() + " students matching above criteria...";
				}
			}
			else {
				text = "0 students match above criteria...";
			}
	
			$("#students_chzn").find(".search-field").append("<div class='students-chosen-text'>" + text + "</div>");
		}
	}
	
	function addInstructorLabel() {
		// no students selected
		// do we have All certs/months/years?
		$(".instructors-chosen-text").remove();
		var text = "";
		
		if (($("#instructors_chzn").find(".result-selected").length == 0)) {
			if (getInstructorListCount() > 0) {
				var hasSelections = $("#instructors_chzn").find(".chzn-choices").find(".search-choice").length;
				if (hasSelections == 0) {
					var item_count = getInstructorListCount();
					
					if (item_count == 1) {
						text = "1 instructor...";
					}
					else {
						text = "All " + item_count + " instructors...";
					}
				}
				else {
					// the text should say 'All students matching above criteria'
					text = "All " + getStudentListCount() + " instructors matching above criteria...";
				}
			}
			else {
				text = "0 instructors...";
			}
	
			$("#instructors_chzn").find(".search-field").append("<div class='instructors-chosen-text'>" + text + "</div>");
		}
	}
	
	function addStandardLabel(name) {
		$("." + name + "-chosen-text").remove();
		var text = "";
		if ($("#" + name + "_chzn").find(".result-selected").length == 0) {
	
			var displayName = name;
	
			if (name == "accountType") {
				displayName = "account types";
			} else if (name == "category") {
				displayName = "categories";
			}
			
			var item_count = getStandardListCount(name);
			
			if (item_count > 0) {
				if (item_count == 1) {
					text = "1 " + displayName.substring(0, displayName.length - 1) + "...";
				}
				else {
					text = "All " + item_count + " " + displayName + "...";
				}
			}
			else {
				text = "0 " + displayName;
			}
	
			$("#" + name + "_chzn").find(".search-field").append("<div class='" + name + "-chosen-text'>" + text + "</div>");
		}
	}
	
	function updateAllLabels() {
		$(".search-field").find("input").addClass("white-text");
		addInstructorLabel();
		addStudentLabel();
		addStandardLabel("category");
		addStandardLabel("accountType");
		addStandardLabel("sites");
	}
	
	$("#category").chosen().change(function(){updateAllLabels()});
	$("#accountType").chosen().change(function(){updateStudentList(); updateAllLabels()});
	$("#students").chosen().change(function(){updateAllLabels()});
	$("#instructors").chosen().change(function(){updateAllLabels()});
	$("#sites").chosen().change(function(){updateAllLabels()});
	
	updateStudentList();	
	updateAllLabels();
	
	$("#siteRequirements").sliderCheckbox({});
	$("#programRequirements").sliderCheckbox({});
	
	// update filters!
	$.each(["students", "instructors", "sites", "accountTypes", "category"], function(i,v){
		
		var chzn = $("#" + v + "_chzn");
		
		chzn.find(".search-field").find("input").blur(function(){
			if ($("#" + v).val()) {$("." + v + "-chosen-text").remove();}
			else {
				if (v == "students") {addStudentLabel();}
				else if (v == "instructors") {addInstructorLabel();}
				else {addStandardLabel(v)}
			}
		});
		
		
		chzn.find(".search-field").find("input").focus(function(){
			// we're focused and typing...remove all labels
			$("." + v + "-chosen-text").remove();
		});
	});
	
	$("#siteRequirements").change(function(e) {
		var siteList = $("#site-list");
		var blocker = $("#sitelist-blocker");
		if ($(this).attr("checked")) {
			siteList.css("opacity", "1");
			blocker.hide();
		}
		else {
			siteList.css("opacity", "0.5");
			blocker.show();
		}
	});
	
	$("#requirement_search").focus(function(){$(this).addClass("fancy-input-focus");});
	$("#requirement_search").blur(function(){$(this).removeClass("fancy-input-focus");});
	
	
	
	$("#filters-reset-btn").button().click(function(e){
		e.preventDefault();
		
		resetChosen("category");
		resetChosen("accountType");
		resetChosen("students");
		resetChosen("instructors");
		resetChosen("sites");
		
		if (!$("#programRequirements-slider-button").hasClass("on")) {
			$("#programRequirements-slider-button").trigger("click");
		}
		if (!$("#siteRequirements-slider-button").hasClass("on")) {
			$("#siteRequirements-slider-button").trigger("click");
		}
		
		updateAllLabels();
	});
	
	$("#filters-go-btn").button().click(function(e){
		e.preventDefault();
		$("#requirement-filters-wrapper_filters-title").trigger("click");
		
		// dim current calendar and add a throbber
		$("#manage-requirements-container").animate({opacity: 0.5});
		
		var req_throbber_top = $("#fixed-menu-bar").offset().top+60;
		if ($("#fixed-menu-bar").hasClass("fixed-menu-bar")) {
			req_throbber_top = 200;
		}
		
		$("#req-throbber").css("top", req_throbber_top).fadeIn();
		$("#table-blocker").show();
		//blockUi(true);

		$.post("/scheduler/compliance/filter-requirements", $("#requirement-filters").serialize(), function(response){
			$("#manage-requirements-container").animate({opacity: 1});
			
			$("#req-throbber").fadeOut();
			$("#table-blocker").hide();
			//blockUi(false);
			$("#manage-requirements-container").html($(response)).fadeIn();
			initManageRequirementsTable();
			// scroll to top
			$('html,body').animate({scrollTop: 0},'slow');
			
		}, "json");
	});
	
	function resetChosen(elementName) {
	   $("#" + elementName).find("option").each(function(){
		   $(this).removeAttr("selected");
	   });
	
	   $("#" + elementName + "_chzn").find(".result-selected").each(function(){
		   $(this).removeClass("result-selected").addClass("active-result");
	   });
	
	   $("#" + elementName + "_chzn").find(".hidden-sd-option").removeClass("hidden-sd-option");
	   $("#" + elementName + "_chzn").find(".hidden-group-result").removeClass("hidden-group-result");
	   $("#" + elementName + "_chzn").find(".search-choice").remove();
	}
});


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
	
	if (total_count == 1) {list = collection[0];}
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