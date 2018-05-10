$(function(){
	
	$("#include_observed_airway_managements").sliderCheckbox({onText: 'On', offText: 'Off'});
	
	$("#button-set-airway_management_report_type").change(function(e){
		var report_type = $(this).find(".ui-state-active").text();
		if (report_type == "Detailed") {
			$(".observed_slider_wrapper").fadeIn("fast");
		}
		else {
			$(".observed_slider_wrapper").fadeOut("fast");
		}
	});
	
	$("#button-set-airway_management_report_type").trigger("change");
	
});

var order_by_airway_mangagement_attempt_col = function()
{
	/*
	$(".am_attempt_number_column").each(function(){
	//	$(this).trigger("click");
	});
	*/
}

var updateAirwayManagementTotalsRow = function(initializing)
{
	setTimeout(function(){
		
		$("#report-content").find("table").each(function(){
		
            var table = $(this);
            var success_rate_span = table.find(".success_rate_number");

            updateIndividualAirwayManagementTotalsRow(table);

            table.parent().find(".search-box").keyup(function(){
                updateIndividualAirwayManagementTotalsRow(table);
            });

            // if this is the first time we're running through this function,
            // update the eureka button that launches the modal
            var modal_trigger = $(this).parent().parent().find(".table-title").find(".am_dialog_trigger");

            if (success_rate_span.parent().hasClass("eureka_reached")) {
                modal_trigger.addClass("eureka-goal-met").removeClass("eureka-goal-notmet");
            }
            else {
                modal_trigger.addClass("eureka-goal-notmet").removeClass("eureka-goal-met");
            }

		
		});
		
	}, 500);
	

}

var updateIndividualAirwayManagementTotalsRow = function(table)
{
	var total_attempts_span = table.find(".total_attempts_number");
	var success_rate_span = table.find(".success_rate_number");
	
	var attempts_count = 0;
	var attempts = [];
	var success_count = 0;
	
	table.find(".success_attempt_count_cell").each(function(){
		attempts_count++;
		success_count++;
		attempts[parseInt($(this).text())] = true;
	});
	
	table.find(".failure_attempt_count_cell").each(function(){
		attempts_count++;
		attempts[parseInt($(this).text())] = false;
	});
	
	var number_of_attempts = (Object.keys(attempts).length);
	total_attempts_span.text(number_of_attempts);

    // make the total attempts cell green if they have reach the goal
    var am_attempts_goal = parseInt(table.find(".airway_management_attempts_goal").text());
    var attempts_goal_cell = table.find(".total_attempts_goal_cell");
    if(number_of_attempts >= am_attempts_goal){
        attempts_goal_cell.addClass("eureka_reached");
    }
    else {
        attempts_goal_cell.removeClass("eureka_reached");
    }


	
	if (number_of_attempts < 20) {
		success_rate_span.text("N/A");
		success_rate_span.parent().removeClass("eureka_reached");
	}
	else {
		var starting_point = number_of_attempts-19;
		var successes = 0;
		for(i = starting_point; i <= number_of_attempts; i++){
			if (attempts[i]) {
				successes++;
			}
		}
		
		var rate = (successes / 20)*100;
		var success_rate = Math.round(rate);
		
		success_rate_span.text(success_rate + "% (" + successes + "/20)");
		
		if (success_rate == 100) {
			success_rate_span.parent().addClass("eureka_reached");
		}
		else {
			success_rate_span.parent().removeClass("eureka_reached");
		}
		
	}
}

var initAirwayManagementEurekaGraphs = function()
{
	
	$(".am_eureka_dialog").each(function(){
		
		var all_attempts_wrapper = $(this).find(".all_time_attempts_wrapper");
		var coa_attempts_wrapper = $(this).find(".coa_attempts_wrapper");
		
		$(this).find(".graph_wrapper").each(function(){
			if (!$(this).find(".eureka_home").hasClass("jqplot-target")) {
				$(this).eurekaGraph();
			}
		});
		
		
		var am_dialog = $(this).dialog({modal:true,
										autoOpen:false,
										width:650,
										title: "Eureka Graph"
										});

		am_dialog.find(".show_coa_attempts_trigger").click(function(e){
			e.preventDefault();
			all_attempts_wrapper.hide();
			coa_attempts_wrapper.fadeIn("fast");
		});
		
		am_dialog.find(".show_all_attempts_trigger").click(function(e){
			e.preventDefault();
			coa_attempts_wrapper.hide();
			all_attempts_wrapper.fadeIn("fast");
		});
		
		$("#am_eureka_dialog_trigger_" + $(this).attr("data-studentid")).click(function(e){
			e.preventDefault();
			all_attempts_wrapper.hide();
			coa_attempts_wrapper.fadeIn("fast");
			am_dialog.dialog("open");
		})
		
	});

	/*
	$("#report-results").find(".graph_wrapper").each(function(){
		$(this).eurekaGraph();
	})*/
}