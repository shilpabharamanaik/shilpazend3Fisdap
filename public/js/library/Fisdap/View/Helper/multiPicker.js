$(function(){
	initPicklist();	
});

var initPicklist;
initPicklist = function(){
	$(".picklist-control-buttons").find("a").button();
	
	$(".picklist-ms-picker").find("select").focus(function(){$(this).addClass("fancy-focus");});
	$(".picklist-ms-picker").find("input").focus(function(){$(this).addClass("fancy-focus");});
	$(".picklist-ms-picker").find("select").blur(function(){$(this).removeClass("fancy-focus");});
	$(".picklist-ms-picker").find("input").blur(function(){$(this).removeClass("fancy-focus");});
		
	// sort the options if in multiple mode
	$(".picklist-ms-picker .available-list").find("option").sort(sortList).appendTo($(".available-list"));
	
	$(".picklist-control-buttons").find("a").click(function(e){
		
		e.preventDefault();
		var action = $(this).attr("data-controlfunction");
		
		if (action == "add" || action == "addAll") {
			moveOptions(action, $(".available-list:visible"), $(".chosen-list"));
		}
		else if (action == "remove" || action == "removeAll") {
			moveOptions(action, $(".chosen-list"), $(".available-list:visible"));
		}
		
	});
	
	var items = {};
	$(".available-list").find("option").each(function(){items[$(this).val()] = $(this).text();});
	
	$(".search-list").keyup(function(){
		filterList($("." + $(this).attr("data-listtosearch")), $(this).val());
	});
	
	// initiate the cancel/confirm buttons if those exist
	$('#cancel_modal').button();
	$('#submit_button').button();
	
	// update the # of students selected
	updateAssignedCount();
	
	function filterList(list, searchTerm) {
		list.each(function(i, el) {
			// first remove all the options
			$(el).find("option").each(function(){$(this).remove();});
			
			var numStudents = 0;
			// loop through the students, adding them if appropriate
			$.each(items, function(index, value) {
				
				// if there's a search term, make sure the value matches
				if (typeof(searchTerm) != 'undefined') {
					var testedSearchTerm = searchTerm.toLowerCase();
					var testedValue = value.toLowerCase();
					
					// this search term is found, add this student
					if (testedValue.indexOf(testedSearchTerm) != -1){
						$(el).append(createOption(index, value));
						
						// if the student is already chosen, disable the "available" option
						$(".chosen-list").find("option[data-itemId='"+index+"']").each(function(){
								disableOption($(el).find("option").last());
						});
					}
				} else {
					// add it when no searchTerm (single select mode)
					$(el).append(createOption(index, value));
				}
				
				numStudents++;
			});
			
			// once we've added all the students, sort the list
			$(el).find("option").sort(sortList).appendTo($(el));
			
			// if we're in single select mode, count the number of students and add that to the list
			if ($(el).prop('multiple') != true) {
				$(el).prepend('<option data-itemId="" value="" selected="selected">Choose from ' + numStudents + ' students</option>');
				// and tell the jQuery .chosen plugin that the list has CHANGED
				$(el).trigger("liszt:updated");
			}
		});
		
		
	}
	
	// sort list by last name, first name
	function sortList(a, b) {
		a_arr = a.innerHTML.toLowerCase().split(",");
		a_name = a_arr[0].split(" ");
		a_last_first = a_name[a_name.length -1] + a_name.join(" ");
		
		b_arr = b.innerHTML.toLowerCase().split(",");
		b_name = b_arr[0].split(" ");
		b_last_first = b_name[b_name.length -1] + b_name.join(" ");
		
		if (a.innerHTML == 'NA' || a_last_first > b_last_first) {
			return 1;
		}
		else if (b.innerHTML == 'NA' || b_last_first > a_last_first) {
			return -1;
		}else{
			return 0;
		}
	
	}

	function disableOption(option) {
		option.attr("disabled", "disabled").css("color", "#ccc");
	}
	
	function createOption(val, label) {
		return "<option value='" + val + "' data-itemId='" + val + "'>" + label + "</option>";
	}

	function moveOptions(action, fromList, toList) {
		// loop through each of the options on the first list
		fromList.find("option").each(function(){
			// figure out if we need to move this particular option
			var addThis = false;
			if (action.indexOf("All") != -1 && !$(this).attr("disabled")) {addThis = true;}
			if ($(this).attr("selected")) {addThis = true;}
			
			if (addThis) {
				// add the option
				if (action.indexOf("add") != -1) {
					disableOption($(this));
					toList.append(createOption($(this).attr("data-itemId"), $(this).text()));
				} else {
					// remove the option
					var changingOption = $(this);
					
					// if this option was disabled on the other list, re-enable it
					toList.find("option[data-itemId='"+changingOption.attr("data-itemId")+"']").each(function() {
							$(this).attr("disabled", false).css("color", "#444");
					});
					changingOption.remove();
				}
			}
		});
		
		// unselect everything
		toList.find("option:selected").removeAttr("selected");
		fromList.find("option:selected").removeAttr("selected");
		
		// resort the "to" list
		toList.find("option").sort(sortList).appendTo(toList);
		
		updateAssignedCount();
		updateAssignedField();
	}
	
	// update the user-visible count of students selected (if enabled)
	function updateAssignedCount(){
		$('#current_assigned_count').html($('#chosen-list').find('option').length);
	}
	
	// update the hidden field that contains a comma-separated list of student IDs.
	// this is useful for cases where the helper is part of a form that submits, or for jQuery form.serialize()
	function updateAssignedField(){
		var assignedIDs = [];
		$('#chosen-list').find('option').each(function(i, elem) {
			if($(elem).data('itemid') > 0){
				assignedIDs.push($(elem).data('itemid'));
			}
		});
		$("input[name='multi_picklist_selected']").val(assignedIDs.join(','));
		$("input[name='multi_picklist_selected']").data("legible-value", assignedIDs.length);
	}
}
