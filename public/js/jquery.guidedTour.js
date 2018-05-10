/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * jQuery plugin for creating "guided tours"
 * 
 * @author Hammer :)
 * 
 */

$(function(){
	
	window.scrollTo(0,0);
	
	var guided_tour_steps = {};

	// Since the only file that calls this file is the GuidedTourHelper view helper,
	// it's safe to call the guided tour plugin
	if ($("#guided_tour_wrapper")) {
		// let's be sure we are scrolled to the top to begin
		$("#guided_tour_wrapper").guidedTour();
	}
	

	
});


(function( $ ){

	
	/*
	 * The plugin!
	*/
	$.fn.guidedTour = function(options) {
		
		window.scrollTo(0,0);
		
		
		// ---------------------------------------------------------------------------------------------------------------------------------------------- 
		//  
		//              						Pretty self documenting code... the following few steps will initialize
		//									friendly variables & call a bunch of initializion functions to set everything up.
		//  
		// ----------------------------------------------------------------------------------------------------------------------------------------------
		
		// set up some plugin-global variables for easy use
		// take guided tours out of the content wrapper and just append it to the body
		var guided_tour_wrapper = this;
		guided_tour_wrapper.remove();
		$("body").append(guided_tour_wrapper);
		
		var tour_id = guided_tour_wrapper.attr("data-tourid");
		var welcome_modal = $("#guided_tour_" + tour_id + "_welcome_modal");
		var end_msg = welcome_modal.find(".guided_tour_end_msg");
		var list = $("#guided_tour_" + tour_id);
		var tour_directions = $("#tour_" + tour_id + "_directions");
		var corner_robot = $("#guided_tour_" + tour_id + "_corner_robot");
		var steps = stepsToObject();
		guided_tour_steps = steps;
		var visited_steps = [];
		var number_of_steps = getNumberOfSteps();
		
		// hide everything until we're ready to go
		tour_directions.hide();
		list.hide();
		
		corner_robot.hover(function(){
			if ($(this).hasClass("inactive_tour_robot")) {
				$("#guided_tour_" + tour_id + "_robot_tooltip").fadeIn(100);
			}
		}, function(e) {
			if ($(this).hasClass("inactive_tour_robot")) {
				$("#guided_tour_" + tour_id + "_robot_tooltip").fadeOut(50);
			}
		});
		
		corner_robot.click(function(e){
			e.preventDefault();
			
			if ($(this).hasClass("inactive_tour_robot")) {
				// begin the tour!
				$("#guided_tour_" + tour_id + "_robot_tooltip").fadeOut(50);
				beginTour();
			}
			
		});
		
		// if we have an active tour, we want to hide this robot until the user begins the tour
		// if we have an INactive tour (the user had a record for this tour), the robot needs to stay visible in the corner
		if (corner_robot.hasClass("active_tour_robot")) {
			corner_robot.hide();
		}
		
		// call all of our initialization functions
		$("#completed_tour_" + tour_id).button().click(function(e){
			e.preventDefault();
			$(this).hide();
			$("#tour_" + tour_id + "_complete_throbber").fadeIn();
			
			// send ajax request to create a history record, then close modal/tour guide
			$.post("/ajax/complete-guided-tour", {"tour_id" : tour_id},
				function(response) {
					var green_color = "#84c724";
					$("#tour_" + tour_id + "_complete_throbber").hide();
					$(".guided_tour_step_text").html(end_msg);
					tour_directions.find(".guided_tour_arrow").css("opacity", "0");
					tour_directions.find(".guided_tour_dots_wrapper").css("opacity", "0");
					tour_directions.find(".guided_tour_current_step").addClass("guided_tour_green_circle");
					
					adjustTourStepTextHeight();
					
					guided_tour_wrapper.find(".guided_tour_step").fadeOut();
					$("#tour_" + tour_id + "_checkmark").fadeIn();
					corner_robot.hide();
					$("#tour_" + tour_id + "_close").fadeIn("fast");
				}
				
			); // end ajax request
				
			
		});
		
		$("#tour_" + tour_id + "_close").click(function(){
			
			// reset the tour and close everything
			$("#tour_" + tour_id + "_directions").slideUp("fast");
			corner_robot.removeClass("active_tour_robot").addClass("inactive_tour_robot").delay(1000).fadeIn("fast");
			$("#tour_" + tour_id + "_checkmark").hide();
			
			// put everything back to normal, but wait for the slide animation to be done
			setTimeout(function(){
				tour_directions.find(".guided_tour_arrow").css("opacity", "1");
				tour_directions.find(".guided_tour_dots_wrapper").css("opacity", "1");
				tour_directions.find(".guided_tour_current_step").removeClass("guided_tour_green_circle");
				
				$("#footer_text").css("margin-bottom", "0.5em");
				
				$("#tour_" + tour_id + "_close").fadeOut();
				
			},300);
			
			
		});
		
		initGuidedTourWelcomeModal();
		initGuidedTourNavigation();
		
		if (welcome_modal.attr("data-startingstepid") != "0") {
			
			// they've made some progress on this tour.
			// Give the page 2 seconds to load (so positions will adjust appropriately)
			// Open it, and start at the step they were last looking at.
			setTimeout(function(){
				beginTour(welcome_modal.attr("data-startingstepid"));
			}, 2000);
			
		}
		
		
		// ---------------------------------------------------------------------------------------------------------------------------------------------- 
		//  
		//              			The following functions are for basic initialzing (click handlers/displaying steps/etc)					  
		//  
		// ----------------------------------------------------------------------------------------------------------------------------------------------
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the welcome modal
		 * 	Determines auto open based on a data attribute on the element (set by the PHP view helper)
		 * 	Removes the title bar and hides the end message (this is used later)
		 * 	Initializes jQuery UI buttons
		 * ----------------------------------------------------------------------------------------------------------------------------------
		*/
		function initGuidedTourWelcomeModal()
		{
			// get the modal set up and possibly opened
			var auto_open = welcome_modal.attr("data-autoopen");
			welcome_modal.dialog({modal:true, width:500, autoOpen: auto_open});
			welcome_modal.parent().find(".ui-dialog-titlebar").remove();
			welcome_modal.find(".guided_tour_end_msg").remove();
			welcome_modal.find("button").button();
			
			// call our other initializing functions to set up button event handlers
			initLaterButton();
			initAllSetButton();
			initStartTourButton();
			
		} // end initGuidedTourWelcomeModal()
		
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the left/right arrows found in the guided tour navigation at the bottom
		 * Triggers the click for the next/previous step
		 * ----------------------------------------------------------------------------------------------------------------------------------
		*/
		function initGuidedTourNavigation()
		{
			$(".guided_tour_arrow").click(function(e){
				e.preventDefault();
				var current_step = parseInt($(".guided_tour_current_step").text());
				var previous_step = current_step;
				
				if($(this).hasClass("guided_tour_next")){current_step++;}
				else {current_step--;}
				
				if(current_step > 0 && current_step <= number_of_steps){
					selectStep(current_step);
				}
				
				if (tour_id == 6) {
					// cases to change tabs
					// 3 -> 4, 6 -> 7, 4 -> 3, 7 -> 6
					if ((previous_step == 3 && current_step == 4) || (previous_step == 6 && current_step == 7) || (previous_step == 4 && current_step == 3) || (previous_step == 7 && current_step == 6)) {
						handleTabTriggersForAddShift();
					}
				}
				
			});
			
		} // end initGuidedTourNavigation()
		
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the steps - puts the actual circles on the page
		 * They are hidden on page load - the welcome modal buttons will trigger this function
		 * Uses the global steps object
		 * ----------------------------------------------------------------------------------------------------------------------------------
		*/
		function initSteps()
		{
			$.each(steps, function(i, v){
				
				var step_id = "guided_tour_step_" + i;
				var pointer = v['pointer'];
				guided_tour_wrapper.append("<div data-stepdbid='" + v['step_db_id'] + "' class='guided_tour_step' id='" + step_id + "'><div class='guided_tour_arrow_" + pointer + "'></div>" + i + "</div>");
				
				$.fn.guidedTour.updateStepLocation(v, i);
				
				$("#" + step_id).click(function(e){
					e.preventDefault();
					selectStep(i);
				});
				
				$(".guided_tour_dots_wrapper").append("<span class='dot' id='guided_tour_dot_" + i + "'>.</span>");
				
		   });
			
		   if (tour_id == 10) {
				$(".guided_tour_dots_wrapper").css("margin-left", "-0.6em");
		   }
			
		} // end initSteps()
		
		
		$.fn.guidedTour.updateStepLocation = function(step_data, step_id)
		{
			var step_dom = $("#guided_tour_step_" + step_id);
			var focus_element = step_data['focus_element'];
			var number = step_id;
			var pointer = step_data['pointer'];
			var auto_xy_pos = step_data['auto_xy_pos'];
			
			// now we'll deal with positioning of the circle
			var element_top_offest = focus_element.offset().top;
			var element_left_offest = focus_element.offset().left;
			var element_height = focus_element.height();
			var element_width = focus_element.width();
			
			if (element_width < 10) {
			  element_width = focus_element.width();
			}
			
			var top_val = 0;
			var left_val = 0;
			
			
			if (pointer == "left" || pointer == "right") {
				top_val = getStepTopValFromLeftRight(step_data, element_height, element_top_offest, auto_xy_pos);
				left_val = getStepLeftFromLeftRight(pointer, element_left_offest, element_width);
			}
			else {
				top_val = getStepTopValFromTopBottom(pointer, element_height, element_top_offest);
				left_val = getStepLeftFromTopBottom(step_data, auto_xy_pos, element_left_offest, element_width);
			}
			
			// wonky, terrible, but necessary to get things done
			if ((number == 1 && tour_id == 3) || (number == 1 && tour_id == 4)) {
				top_val = top_val +24;
			}
			

			
			step_dom.css("top", top_val).css("left", left_val);
		}
		
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the click hanlder for the "I'm good" button.
		 * This button triggers an AJAX request to create a history record for this user/tour.
		 * After successful ajax request, closes the modal and opens the corner robot.
		 * The next time this user loads this page, they will not have the guided tour open automatically.
		 * ----------------------------------------------------------------------------------------------------------------------------------
		*/
		function initAllSetButton()
		{
			if (tour_id == 3) {
				$("#end_tour_guide_" + tour_id).css("margin-left", "0.2em").css("margin-right", "0.4em");
			}
			
			$("#end_tour_guide_" + tour_id).click(function(e){
				
				// hide the button/append a throbber
				e.preventDefault();
				var trigger = $(this);
				trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='end_tour_guide_throbber'>");
				$("#end_tour_guide_throbber").fadeIn();
				
				// send ajax request to create a history record, then close modal/tour guide
				$.post("/ajax/complete-guided-tour", {"tour_id" : tour_id},
					function(response) {
						trigger.css("opacity", "1");
						$("#end_tour_guide_throbber").remove();
						closeGuidedTourFromModal();
					}
					
				); // end ajax request
				
			}); // end click handler
			
		} // end initAllSetButton()
		
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the click hanlder for the "later" button.
		 * This just closes the modal and opens the corner robot.
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 */
		function initLaterButton()
		{
			$("#later_tour_guide_" + tour_id).click(function(e){
				e.preventDefault();
				closeGuidedTourFromModal();
			});
			
		} // end initLaterButton()
		
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Initializes the click hanlder for the "Show be around" button.
		 * This button triggers the tour to begin. All once hidden tour elements will be show and the modal will close.
		 * ----------------------------------------------------------------------------------------------------------------------------------
		*/
		function initStartTourButton()
		{
			$("#start_tour_guide_" + tour_id).click(function(e){
				e.preventDefault();
				welcome_modal.dialog("close");
				beginTour();
				
			}); // end click handler
			
		} // end initStartTourButton()
		
		
		function beginTour(starting_step_id)
		{
			
			$("#footer_text").css("margin-bottom", "10em");
			
			// better to inti these here... the page will have fully loaded its content by now
			if ($("#guided_tour_step_1").length <= 0) {
				initSteps();
			}
			
			// wonky, dumb, grrr
			if (tour_id == 5) {
				
				if ($("#fixed-menu-bar").hasClass("fixed-menu-bar")) {fixedPosManageRequirements();}
				$(window).scroll(function() {
					if ($("#fixed-menu-bar").hasClass("fixed-menu-bar")) {fixedPosManageRequirements();}
					else {absolutePosManageRequirements();}
				});
				
			}
			else if (tour_id == 2 || tour_id == 7) {
				
				if ($(".calendar-controls").hasClass("fixed")) {fixedPosCalendar();}
				$(window).scroll(function() {
					if ($(".calendar-controls").hasClass("fixed")) {fixedPosCalendar();}
					else {absolutePosCalendar();}
				});
				
				// make one of hte steps z-index higher
				var add_shift_first_step = false;
				if ($("#cal-add-shift-btns").length > 0) {add_shift_first_step = true;}
				
				if (tour_id == 7) {filters_step_number = 5;}
				else {filters_step_number = 3;}
				
				if (!add_shift_first_step) {filters_step_number = filters_step_number - 1;}
				
				$("#guided_tour_step_" + filters_step_number).css("z-index", "1501");
				
			}
			
			guided_tour_wrapper.find(".guided_tour_step").fadeIn();
			guided_tour_wrapper.find(".tour_directions").slideDown();
			
			// they've already done this tour. We're going to always give them the "I'm done" option
			$("#completed_tour_" + tour_id).delay(100).fadeIn();
			
			corner_robot.addClass("active_tour_robot").removeClass("inactive_tour_robot").delay(500).fadeIn();
			
			if (starting_step_id) {
				if (getStepByDatabaseId(starting_step_id).length > 0) {
					getStepByDatabaseId(starting_step_id).trigger("click");
				}
				else {
					$("#guided_tour_step_1").trigger("click");
				}
			}
			else {
				// then trigger the first one
				$("#guided_tour_step_1").trigger("click");
			}
			
			if (tour_id == 6) {
				handleTabTriggersForAddShift();
			}
		}
		
		function fixedPosManageRequirements()
		{
			$("#guided_tour_step_3").css("position", "fixed").css("top", "9px").css("left", "19px");
			$("#guided_tour_step_4").css("position", "fixed").css("top", "9px");
			$.fn.guidedTour.updateStepLocation(steps[5], 5);
		}
		
		function absolutePosManageRequirements()
		{
			$("#guided_tour_step_3").css("position", "absolute");
			$("#guided_tour_step_4").css("position", "absolute");
			$.fn.guidedTour.updateStepLocation(steps[3], 3);
			$.fn.guidedTour.updateStepLocation(steps[4], 4);
			$.fn.guidedTour.updateStepLocation(steps[5], 5);
		}
		
		
				
		function fixedPosCalendar()
		{
			var arrows_fixed_top_val = "3px";
			var arrows_fixed_left_val = "29px";
			
			var view_type_fixed_top_val = "3px";
			var view_type_fixed_left_val = "962px";
			
			arrows_step_number = getCalendarArrowsStepNumber();
			view_type_step_number = getCalendarViewTypeStepNumber();
			
			$("#guided_tour_step_" + arrows_step_number).css("position", "fixed").css("top", arrows_fixed_top_val).css("left", arrows_fixed_left_val);
			$("#guided_tour_step_" + view_type_step_number).css("position", "fixed").css("top", arrows_fixed_top_val).css("left", view_type_fixed_left_val);
		}
		
		function getCalendarArrowsStepNumber()
		{
			var add_shift_first_step = false;
			if ($("#cal-add-shift-btns").length > 0) {add_shift_first_step = true;}
			
			if (tour_id == 7) {arrows_step_number = 6;}
			else {arrows_step_number = 4;}
			
			if (!add_shift_first_step) {arrows_step_number = arrows_step_number - 1;}
			
			return arrows_step_number;
		}
		
		function getCalendarViewTypeStepNumber()
		{
			var add_shift_first_step = false;
			if ($("#cal-add-shift-btns").length > 0) {add_shift_first_step = true;}
			
			if (tour_id == 7) {view_type_step_number = 7;}
			else {view_type_step_number = 5;}
			
			if (!add_shift_first_step) {view_type_step_number = view_type_step_number - 1;}
			
			return view_type_step_number;
		}
		
		function absolutePosCalendar()
		{
			arrows_step_number = getCalendarArrowsStepNumber();
			view_type_step_number = getCalendarViewTypeStepNumber();
			
			$("#guided_tour_step_" + arrows_step_number).css("position", "absolute");
			$("#guided_tour_step_" + view_type_step_number).css("position", "absolute");
			$.fn.guidedTour.updateStepLocation(steps[arrows_step_number], arrows_step_number);
			$.fn.guidedTour.updateStepLocation(steps[view_type_step_number], view_type_step_number);
		}
		
		function getStepByDatabaseId(db_id)
		{
			var step = false;
			
			$("#guided_tour_wrapper").find(".guided_tour_step").each(function(){
				
				if (parseInt($(this).attr("data-stepdbid")) == parseInt(db_id)) {
					step = $(this);
				}
				
			});
			
			return step;
		}
		
		// ---------------------------------------------------------------------------------------------------------------------------------------------- 
		//  
		//              			The following functions are just helper functions used by our initialization stuff					  
		//  
		// ----------------------------------------------------------------------------------------------------------------------------------------------
		
		function selectStep(step_number)
		{
			$(".guided_tour_current_step").text(step_number).effect("bounce");
			$(".foucsed-tour-element").removeClass("foucsed-tour-element");
			
			// highlight the element
			var step_dom_element = $("#guided_tour_step_" + step_number);
			var focus_element = steps[step_number]['focus_element'];
			var step_text = steps[step_number]['text'];
			
			focus_element.addClass("foucsed_tour_element");
			
			$(".guided_tour_step").removeClass("selected_tour_step");
			$(".dot").css("opacity", "0.4");
			
			step_dom_element.addClass("selected_tour_step");
			
			$(".guided_tour_step_text").hide().html(step_text).fadeIn();
			$("#guided_tour_dot_" + step_number).css("opacity", "1");
			
			scrollToStep(step_number);
			
			adjustTourStepTextHeight();
			
			$.post("/ajax/update-tour-progress", {"tour_id" : tour_id, "step_id": step_dom_element.attr("data-stepdbid")},
				function(response) {
				}
				
			); // end ajax request
			
		}
		
		function scrollToStep(step_number)
		{
			var step_dom_element = $("#guided_tour_step_" + step_number);
			var focus_element = steps[step_number]['focus_element'];
			var scroll_to_pos = 0;
			
			// where should we scroll to? which value is smaller?
			if (focus_element.offset().top < step_dom_element.offset().top) {
				scroll_to_pos = focus_element.offset().top;
			}
			else {
				scroll_to_pos = step_dom_element.offset().top;
			}
			
			scroll_to_pos = scroll_to_pos - 20;
			
			// now finally, scroll to
			$('html,body').animate({scrollTop: scroll_to_pos},'slow');
		}
		
		function adjustTourStepTextHeight()
		{
			var step_text_height = $(".guided_tour_step_text").height();
			var top_padding = "2.25em";
			
			if (step_text_height > 30) {
				if (step_text_height < 55) {
					// two lines of text
					top_padding = "1.7em";
				}
				else {
					if (step_text_height < 80) {
						// three lines of text
						top_padding = "1.2em";
					}
					else {
						// four lines of text
						top_padding = "0.55em";
					}
				}
			}
			
			$(".guided_tour_step_text").css("padding-top", top_padding);
		}
		
		function getNumberOfSteps() {
			var count = 0;
			
			$.each(steps, function(i, v){
				count++;
			});
			
			return count;
		}
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * Closes the guided tour via the modal. Triggered by 2 of the buttons.
		 * Opens the robot in the corner & closes the modal.
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 */
		function closeGuidedTourFromModal()
		{
			welcome_modal.dialog("close");
			corner_robot.removeClass("active_tour_robot").addClass("inactive_tour_robot").fadeIn();
			
		} // end closeGuidedTourFromModal()
		
		/*
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 * This turns the HTML UL of steps into an object since it's slightly easier to work with
		 * Called just to initialize our data
		 * ----------------------------------------------------------------------------------------------------------------------------------
		 */
		function stepsToObject()
		{
			var steps_object = {};
			var step_count = 1;
			
			$("#guided_tour_" + tour_id).find("li").each(function(){
				
				var focus_dom_element = $("" + $(this).find(".focus_element_selector").text());
				
				// make sure the focus element is actually there
				if (focus_dom_element.length > 0) {
				
					steps_object[step_count] = {focus_element: focus_dom_element,
												text: $(this).find(".step_text").html(),
												pointer: $(this).attr("data-pointer"),
												step_db_id: $(this).attr("data-stepdbid"),
												auto_xy_pos:$(this).attr("data-autoxypos"),
												manual_x_pos:$(this).attr("data-manualxpos"),
												manual_y_pos:$(this).attr("data-manualypos"),
												hidden_on_page_load:$(this).attr("data-hiddenonpageload")
											   };
					
					step_count++;
				}
				
			});
			
			return steps_object;
		
		} // end stepsToObject();
		
		
		function getStepTopValFromLeftRight(step_data, element_height, element_top_offest, auto_xy_pos)
		{
			var top_val = 0;
			
			// first deal with the "top" position
			if (step_data['manual_y_pos'] != 0) {
				top_val = step_data['manual_y_pos'];
			}
			else {
				
				if (auto_xy_pos == "top") {
					top_val = element_top_offest;
				}
				else if (auto_xy_pos == "bottom") {
					top_val = element_top_offest + element_height;
				}
				else {
					top_val = element_top_offest + (element_height/2);
				}
			}
			
			// the minus 20 offsets the height from the pointer to the top of the circle
			return top_val - 20;
			
		}
		
		function getStepLeftFromLeftRight(pointer, element_left_offest, element_width)
		{
			var left_val = 0;
			
			// now for left
				
			if (pointer == "left") {
				left_val = element_left_offest + element_width + 12;
			}
			else {
				left_val = element_left_offest - 45;
			}
			
			return left_val;
		}
		
		
		function getStepTopValFromTopBottom(pointer, element_height, element_top_offest)
		{
			var top_val = 0;
			
			// now for top
			if (pointer == "top") {
				top_val = element_top_offest - element_height + 58;
			}
			else {
				top_val = element_top_offest - 40;
			}
			
			return top_val;
		
		}
		
		
		function getStepLeftFromTopBottom(step_data, auto_xy_pos, element_left_offest, element_width)
		{
			var left_val = 0;
			
			if (step_data['manual_x_pos'] != 0) {
				left_val = step_data['manual_x_pos'];
			}
			else {
				if (auto_xy_pos == "left") {
					left_val = element_left_offest;
				}
				else if (auto_xy_pos == "right") {
					left_val = element_left_offest + element_width - 40;
				}
				else {
					left_val = element_left_offest + (element_width/2) - 20;
				}
			}
			
			return left_val;
		}
		
		function handleTabTriggersForAddShift()
		{
			// open the right default tab for add shift interface
			$("#guided_tour_step_2").hide();
			$("#guided_tour_step_3").hide();
			$("#guided_tour_step_4").hide();
			$("#guided_tour_step_5").hide();
			$("#guided_tour_step_6").hide();
			$("#guided_tour_step_7").hide();
				
			setTimeout(function(){
				
				var selected_step_number = parseInt($("#guided_tour_wrapper").find(".guided_tour_current_step").text());
				
				if (selected_step_number < 4) {
					// open tab 1
					$("#shift_details_tab").trigger("click");
					$("#guided_tour_step_2").fadeIn("fast");
					$("#guided_tour_step_3").fadeIn("fast");
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[2], 2);
					$.fn.guidedTour.updateStepLocation(guided_tour_steps[3], 3);
				}
				else if (selected_step_number < 7) {
					// open tab 2
					$("#assign_tab").trigger("click");
				}
				else {
					// open tab 3
					$("#notes_tab").trigger("click");
				}
				
				// then call our scroll to method (since the tabs do their own scrolling)
				setTimeout(function(){
					scrollToStep(selected_step_number);
				}, 600);
				
			}, 300);
			
		}
		
		
	}; // end guidedTour
	
	
})( jQuery );

