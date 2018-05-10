$(function(){
	// initialize buttons
	$('a#addRequirement').button();
	$('a#removeRequirement').button();
	$('a#edit-req-sharing-btn').button();
	$('a#save-reqs-btn').button();
	$('a#reset-reqs-btn').button();
	disableButtons();
	
	// initialize the styles
	initStyles();

	// SELECT AN AVAILABLE REQUIREMENT
	$("select#availableRequirements").change(function(){
		disableButtons();
		$("#activeRequirements").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		$("a#addRequirement").button("enable");
		return true;
	});
	
	// SELECT AN ACTIVE REQUIREMENT
	// Determines when the "share" button should appear and will only allow one list to have item(s) selected
	$("select#activeRequirements").change(function(){
		disableButtons();
		$("#availableRequirements").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// if any are disabled, you can't select the bunch
		if ($(this).find("option:selected").hasClass('disabled')) {
			return true;
		}
		
		$('a#removeRequirement').button("enable");
		
		// if this is an admin, we'll have to loop through all the selected ones
		// and do some sharing stuff
		if ($("#shared_status").val() == 4) {
			// if any belong to another program, you can't move the bunch
			if ($(this).find("option:selected").hasClass('not-me')) {
				$('a#removeRequirement').button("disable");
			}
			
			// get the selected ones
			var selected_options = $(this).find("option:selected");
			$('a#edit-req-sharing-btn').button("enable");
			
			// base group type on the first option selected
			var groupType = getShareState(selected_options.first());
			
			if (groupType == "shared") {
				$("#edit-req-sharing-btn").button( "option", "label", "Unshare");
			} else {
				$("#edit-req-sharing-btn").button( "option", "label", "Share");
			}

			selected_options.each(function() {
				var shareState = getShareState($(this));
				// if this option isn't the same type, we can't share/unshare these
				if (shareState != groupType) {
					$('a#edit-req-sharing-btn').button("disable");
				}
			});
		}
		
		return true;
	});
	
	// MOVE BUTTONS
	$("#addRequirement, #removeRequirement").click(function(event){
		if ($(this).attr('id') == "addRequirement") {
			var origin = $("#availableRequirements");
			var destination = $("#activeRequirements");
		} else {
			var origin = $("#activeRequirements");
			var destination = $("#availableRequirements");
		}
		
		var selected_options = origin.find("option:selected");
		var changed_reqs = $("#changed_reqs").val().split(',');
		
		// loop through all the selected options and move 'em
		selected_options.each(function(){
			if (!$(this).hasClass('disabled') && !$(this).hasClass('not-me')) {
				var selected_req = $(this).val();
				destination.append($(this));
			
				// update the change log
				var req_index = changed_reqs.indexOf(selected_req);
				if (req_index == -1) {
					changed_reqs.push(selected_req);
					$(this).addClass("moved");
				} else {
					changed_reqs.splice(req_index, 1);
					$(this).removeClass("moved");
				}
			}
		});
		
		// reset the change log
		$("#changed_reqs").val(changed_reqs.join(','));
		
		// re-alphabetize options
		sortOptions(destination);
		disableButtons();
		destination.attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
	});
	
	// SHARE/UNSHARE
	$("#edit-req-sharing-btn").click(function(event) {
		event.preventDefault();
		
		var sharing_changed = $("#sharing_changed").val().split(',');
		var selected_reqs = $("#requirementsContainer").find("option:selected");
		
		// loop through the selected ones and make the change
		selected_reqs.each(function(){
			var optionInfo = $(this).val().split('_');
			var req_id = $(this).val();
			var oldOptionText = $(this).text();
			var req_index = sharing_changed.indexOf(req_id);
			
			// changing this one
			if (req_index == -1) {
				// if it IS shared and we are unsharing
				if (optionInfo[1] == "shared") {
					var newClause = " (unsharing)";
					if ($(this).hasClass('not-me')) {
						var oldClause = " (shared by "+optionInfo[2]+")";
					} else {
						var oldClause = " (shared)";
					}
					$(this).text(oldOptionText.substring(0, oldOptionText.length - oldClause.length)+newClause);
				} else {
					$(this).text(oldOptionText + " (sharing)");
				}

				$(this).addClass("changed");
				sharing_changed.push(req_id);
			}
			
			// reverting a change
			else {
				revertText($(this));
				sharing_changed.splice(req_index, 1);
			}
		});
		
		$("#sharing_changed").val(sharing_changed.join(','));
		$("#activeRequirements").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		disableButtons();
	});
	
	// REVERT BUTTON
	$('a#reset-reqs-btn').click(function(event){
		event.preventDefault();
		
		// clear out the search fields
		$(".search-list").val("");
		searchList($("#availableRequirements"), $("#availableRequirements_hidden"), "");
		searchList($("#activeRequirements"), $("#activeRequirements_hidden"), "");
		
		// find the ones that moved and move them back
		$("#availableRequirements").find("option.moved").attr("selected", "selected");
		$("#addRequirement").button("enable").trigger("click");
		$("#activeRequirements").find("option.moved").attr("selected", "selected");
		$("#removeRequirement").button("enable").trigger("click");
		
		// find the ones that changed sharing status and change them back
		$("#requirementsContainer").find("option.changed").attr("selected", "selected");
		$("#edit-req-sharing-btn").button("enable").trigger("click");
		
		// deselect everything
		$("#requirementsContainer").find("option:selected").removeAttr("selected");
	});

	// SAVE BUTTON
	$('a#save-reqs-btn').click(function(event){
		event.preventDefault();
		
		// clear out the search fields
		$(".search-list").val("");
		searchList($("#availableRequirements"), $("#availableRequirements_hidden"), "");
		searchList($("#activeRequirements"), $("#activeRequirements_hidden"), "");
		
		busyRobot();
		
		// figure out which reqs have been changed and how
		var changed_reqs = $("#changed_reqs").val().split(',');
		var remove = new Array();
		var add = new Array();
		
		// get the ones that need to be deactivated
		$("#availableRequirements option").each(function(){
			// if it's been changed, add it to the array
			if (changed_reqs.indexOf($(this).val()) > -1) {
				remove.push($(this).val());
			}
		});
		
		// get the ones that need to be activated
		$("#activeRequirements option").each(function(){
			// if it's been changed, add it to the array
			if (changed_reqs.indexOf($(this).val()) > -1) {
				add.push($(this).val());
			}
		});
		
		// figure out which reqs have had sharing status changed and how
		var sharing_changed = $("#sharing_changed").val().split(',');
		var share = new Array();
		var unshare = new Array();
		
		// for now we only care about the ones that are still active
		$("#activeRequirements option").each(function(){
			// if it's been changed, add it to the array
			if (sharing_changed.indexOf($(this).val()) > -1) {

				if (getShareState($(this)) == "shared") {
					share.push($(this).val());
				} else {
					unshare.push($(this).val());
				}
			}
		});

		
		// post the data to send the request
		$.post("/account/sites/update-site-requirements",
		       { site_id: getCurrentSiteId(),
				 remove: remove,
			     add: add,
				 share: share,
			     unshare: unshare},
				function(userContextIds) {
					var msg = 'Your site requirements have been saved.';
					var msgClass = 'success';

					if (userContextIds && userContextIds.length > 0) {
						// if there are user ids, send them on to have compliance recalculated
						var plural = "s";
						if (userContextIds.length == 1) {plural = "";}
						var new_busy_robot_txt = "Fisdap Robot is recomputing compliance for " + userContextIds.length + " user" + plural + ".";
						$("#busy-robot").find("#robot-processing-txt").text(new_busy_robot_txt);
					
						$.post("/scheduler/compliance/compute-compliance", {userContextIds: userContextIds},
							function(resp) {
								if (!resp) {
									msg = 'There has been some error in recomputing compliance.';
									msgClass = 'error';
								}
								finishProcess(msg, msgClass, sharing_changed);
							});	
					} else {
						finishProcess(msg, msgClass, sharing_changed);
					}
				}
		);
		
	});

	// search lists
	$(".search-list").keyup(function(){
		searchList($("#" + $(this).attr("data-listtosearch")),
			   $("#" + $(this).attr("data-listtosearch") + "_hidden"),
			   $(this).val().toLowerCase());
	});
	
	// search the site list
	function searchList(list, hidden_list, search_term) {
		// go through the visible ones
		$(list).find("option").each(function(){
			var name = $(this).text().toLowerCase();
			if (name.indexOf(search_term) == -1) {
				$(this).remove().clone().appendTo(hidden_list);
			}
		});
		
		// go through the hidden ones
		$(hidden_list).find("option").each(function(){
			var name = $(this).text().toLowerCase();
			if (name.indexOf(search_term) != -1) {
				$(this).remove().clone().appendTo(list);
			}
		});
		
		sortOptions(list);
	}

	// sort the select list alphabetically
	function sortOptions(list) {
		var my_options = list.find("option");
		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		list.empty().append(my_options);
	}
	
	function disableButtons() {
		// disable some buttons
		$('a#addRequirement').button("disable");
		$('a#removeRequirement').button("disable");
		$('a#edit-req-sharing-btn').button("disable");
	}

	// create some styling when the page first loads
	function initStyles() {
		// sort the options for both lists
		sortOptions($("#availableRequirements"));
		sortOptions($("#activeRequirements"));
		
		// get shared reqs and do stuff to 'em
		var sharedStatus = $("#shared_status").val();
		$("#activeRequirements").find("option").each(function(){
			var optionInfo = $(this).val().split('_');
			if (optionInfo[1] == "shared") {
				if (optionInfo[2]) {
					var sharedMsg = " (shared by "+optionInfo[2]+")";
					$(this).addClass("not-me");
				} else {
					var sharedMsg = " (shared)";
				}
				$(this).text($(this).text()+sharedMsg);

				if (sharedStatus < 4) {
					$(this).addClass("disabled");
				}
				
			}

		});

        // if this is a webkit mobile device, add special styling
        if (isWebkitMobile()) {
            $("#requirementsContainer").addClass("mobile-multiselect");
            $(".mobile-multiselect select[multiple]").css({"height": "auto", "padding": "0.2em"});
        }
	}
	
	function finishProcess(msg, msgClass, sharing_changed) {
		// update sharing language and option values
		$(sharing_changed).each(function(i, val) {
			if (val) {
				var changedOption = $("#requirementsContainer").find("option[value='"+val+"']").first();
				updateSharing(changedOption);
				
				// the only changes you can make to other people's reqs is to unshare
				// so now that this is unshared let's get rid of it
				if ($(changedOption).hasClass('not-me')) {
					$(changedOption).remove();
				}
			}
		});

		// make sure all the stuff in the available list lost it's sharing labels, too
		$("#availableRequirements").find("option").each(function(){
			var optionInfo = $(this).val().split('_');
			var state = optionInfo[1];
			var oldOptionText = $(this).text();
			if (state == "shared") {
				$(this).val(optionInfo[0]).text(oldOptionText.substring(0, oldOptionText.length - 9));
			}
		});

		// show message
		$("#req-messages").addClass(msgClass).text(msg).slideDown();
		setTimeout(function() {
			$("#req-messages").slideUp();
			}, 3000);
		setTimeout(function() {
			$("#req-messages").removeClass(msgClass).text("");
			}, 3500);
			
		closeBusyRobot();
		$("#changed_reqs").val("");
		$("#sharing_changed").val("");
		$("#requirementsContainer").find("option.moved").removeClass("moved");
	}
	
	function getShareState(option) {
		var optionInfo = option.val().split('_');
		var state = optionInfo[1];
		
		// if the option was changed, toggle the state
		if (option.hasClass("changed")) {
			if (state == "shared") {
				state = null;
			} else {
				state = "shared"
			}
		}
		
		return state;
	}
	
	function revertText(option) {
		var optionInfo = $(option).val().split('_');
		var oldOptionText = $(option).text();
		
		// if we don't actually want to unshare
		if (optionInfo[1] == "shared") {
			var oldClause = " (unsharing)";
			if ($(option).hasClass('not-me')) {
				var newClause = " (shared by "+optionInfo[2]+")";
			} else {
				var newClause = " (shared)";
			}
			$(option).text(oldOptionText.substring(0, oldOptionText.length - oldClause.length)+newClause);
		} else {
			$(option).text(oldOptionText.substring(0, oldOptionText.length - 10));
		}

		$(option).removeClass("changed");
	}
	
	function updateSharing(option) {
		var optionInfo = $(option).val().split('_');
		var oldOptionText = $(option).text();
		var req_id = optionInfo[0];
		
		// if we are unsharing
		if (optionInfo[1] == "shared") {
			var oldClause = " (unsharing)";
			$(option).val(req_id).text(oldOptionText.substring(0, oldOptionText.length - oldClause.length));
		}
		// if we are sharing it
		else {
			var oldClause = " (sharing)";
			
			// which list is it in?
			if ($(option).parent().attr("id") == "activeRequirements") {
				var newClause = " (shared)";
				var newId = req_id+"_shared";
			} else {
				// if this isn't in the active reqs list, we're not actually going to share it
				var newClause = "";
				var newId = req_id;
			}
			$(option).val(newId).text(oldOptionText.substring(0, oldOptionText.length - oldClause.length)+newClause);
		}

		$(option).removeClass("changed");
	}
	
});
	
