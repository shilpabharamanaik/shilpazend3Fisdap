$(function(){
	
	$(".student-presets").find("a").button();
	
	
	$(".my-shifts").click(function(e){
		e.preventDefault();
		
		$("#reset-filters-btn").trigger("click");
		
		if ($("#available_filters-element").find(".slider-button").hasClass("on")) {
			$("#available_filters-element").find(".slider-button").trigger("click");
		}
		
		submitFilters();
		
	});
	
	
	$(".my-available-shifts-wrapper").find("a").click(function(e){
		e.preventDefault();
		
		var type = $(this).attr("data-availShiftType");
		
		$("#reset-filters-btn").trigger("click");
		
		if ($("#chosen_filters-element").find(".slider-button").hasClass("on")) {
			$("#chosen_filters-element").find(".slider-button").trigger("click");
		}
		
		disablePeopleOptions(false, "chosen", $("#chosen-blocker"));
		
		var option_val = "0-" + type;
		
		$('#sites_filters').val('');
		$('#sites_filters').val(option_val);
		
		$("#sites_filters_chzn").find(".chzn-results").find("li").each(function(){
			if ($(this).attr("data-optionval") == option_val) {
				setSelectedForChosen($(this), $("#sites_filters_chzn"), $('#sites_filters'));
				return false;
			}
			return true;
		});
		
		siteChangeListener($("#sites_filters_chzn"));
		
		submitFilters();
		
		
	});
	
	if ($(".student-presets").length > 0) {
		$("#cal-display-filters").css("top", "22em");
	}
	
	function submitFilters() {
		var data = getFilters();
		loadNewCalendar(getViewType(), getDate(), getEndDate(), data);
		updateFiltersText();
	}
	
});