var getFilters;
getFilters = function()
{
	var data = {};

	data['sites'] = getChosenVal("sites", false);
	data['bases'] = getChosenVal("bases", true, 'sites');
	data['preceptors'] = getChosenVal("preceptors", false);
	
	data['show_avail'] = 0;

	if($("#bases_filters_chzn").find(".result-selected").length > 0){
		$("#bases_filters_chzn").find(".result-selected").each(function(){
			// user selected
			data['bases_selected_by_user'] = 1;
		});
	}
	else {
		// not user selected
		data['bases_selected_by_user'] = 0;
	}
	
	
	if ($("#available_filters-slider-button").hasClass("on")) {
		data['show_avail'] = 1;
		data['avail_certs'] = getChosenVal("available_cert", false);
		data['avail_groups'] = getChosenVal("available_group", false);
		
		if ($("#available_open_window_filters").attr("checked")) {
			data['avail_open_window'] = 1;
		}
		else {
			data['avail_open_window'] = 0;
		}
	}

	data['show_chosen'] = 0;

	if ($("#chosen_filters-slider-button").hasClass("on")) {
		data['show_chosen'] = 1;

		var certs = $("#chosen_cert_filters").val();
		var groups = getChosenVal("chosen_group", false);
		var month = getGradMonth();
		var year = getGradYear();
		
		data['gradYear'] = year;
		data['gradMonth'] = month;
		data['certs'] = certs;
		data['groups'] = groups;
		
		if($("#students_filters_chzn").find(".result-selected").length > 0){
			data['chosen_students'] = [];
			
			$("#students_filters_chzn").find(".result-selected").each(function(){
				data['chosen_students'].push($(this).attr("data-optionval"));
				data['students_selected_by_user'] = 1;
			});
			
		}
		else {
			data['students_selected_by_user'] = 0;
			
			var hasSelections = $("#students_filters_chzn").find(".chzn-choices").find(".search-choice").length;
			if (hasSelections == 0) {
				
				// are there are hidden options? if so, we have filtered the student list and we need each visible option
				if($("#students_filters_chzn").find(".chzn-results").find(".hidden-sd-option").length > 0){
					data['chosen_students'] = [];
					$("#students_filters_chzn").find(".chzn-results").find("li").each(function(){
						if (!$(this).hasClass("hidden-sd-option")) {
							data['chosen_students'].push($(this).attr("data-optionval"));
						}
					});
				}
				else {
					// just say all students
					data['chosen_students'] = "all";
				}
			}
			else {
				if (getStudentListCount() > 0) {
					data['chosen_students'] = getChosenVal("students", true, null);
				}
				else {
					var csvals = [];
					csvals.push(-1);
					data['chosen_students'] = csvals;
				}
				
			}
		}
	}

	return data;
};

var getChosenVal;
getChosenVal = function(sName, isFiltered, pController)
{
	var chzn = $("#" + sName + "_filters_chzn");
	var select = $("#" + sName + "_filters");
	var selections = [];

	if(chzn.find(".result-selected").length > 0){
		chzn.find(".result-selected").each(function(){
			selections.push($(this).attr("data-optionval"));
		});
	}
	else {
			
		if (pController) {parentVal = getChosenVal(pController, false);}
		else {parentVal = "";}
			
		if ((isFiltered && parentVal != "all")) {
			// go through every option that does not have a disabled class
			chzn.find(".chzn-results").find("li").each(function(){
				if (!$(this).hasClass("group-result")) {
					if (!$(this).hasClass("hidden-sd-option")) {
						selections.push($(this).attr("data-optionval"));
					}
				}
			});
			
		}
		else {
			selections = "all";
		}
	}

	return selections;
}


/*
 * getGradMonth
 */
var getGradMonth;
getGradMonth = function() {
	var month = 0;
	$("#grad_filters-month").find("option").each(function(){
		if ($(this).text() == "All months") {
			month = $(this).text();
		}
		else {
			if ($(this).attr("selected")) {
				month = $(this).text();
			}
		}
	});
	return month;
}

/*
 * Getgradyear
 */
var getGradYear;
getGradYear = function() {
	var year = 0;
	$("#grad_filters-year").find("option").each(function(){
		if ($(this).attr("selected")) {
			year = $(this).text();
		}
	});
	return year;
}
