$(function(){
	
	// we're going to do some fancy animations/slidings/fadings/movings/etc.
	// we need some specific numbers to make this look super smooth
	
	var small_widget = false;
	
	// first determine the width of our widget and make adjustments if necessary
	setTimeout(function(){
		if ($(".airway_management_widget_wrapper").width() < 800) {

			$(".live_patients_pie_chart").find("canvas").each(function(){
				$(this).attr("width", "90");
				$(this).attr("height", "90");
			});
			
			$(".overall_progress_bar_wrapper").css("width", "54%").css("margin-top", "1em");

            $(".airway_management_widget_wrapper").css("padding-bottom", "3em");

			small_widget = true;
		}
		
		// make sure the widget has loaded before we begin working with the DOM
		$(".live_patients_pie_chart").each(function(){
			
			$(this).find("canvas").each(function(){
				
				var ctx = $(this).get(0).getContext("2d");
				var myNewChart = new Chart(ctx);
				
				var data = [];
				
				// build our data object
				$(this).parent().find(".pic_chart_legend_color_box").each(function(){
					var item_data = {};
					item_data['value'] = parseInt($(this).attr("data-value"));
					item_data['color'] = $(this).attr("data-color");
					data.push(item_data);
				});
				
				new Chart(ctx).Pie(data,{segmentStrokeWidth:1});
				
			});
			
			
		});
	},800);
	
	// the graphs need a second before they can be generated
	setTimeout(function(){
		
		var details_trigger;
		
		$(".airway_management_eureka_wrappers").each(function(){
			
			var am_widget_wrapper = $(this);
			var widget_eureka_title = $(this).find(".widget_details_graph_title");
			var graph_count = 0;
			
			var coa_graph;
			var coa_title = "Eureka (last 20 attempts)";
			var coa_details;
			var show_coa_class = "show_coa_attempts";
			var show_coa_text = "Show last 20 attempts";
			
			var all_attempts_graph;
			var all_attempts_title = "Eureka (all attempts)";
			var all_attempts_details;
			var show_all_class = "show_all_attempts";
			var show_all_text = "Show all attempts";
			
			
			$(this).find(".graph_wrapper").each(function(){
				$(this).attr("data-inawidget", "1");
				$(this).eurekaGraph();
				
				var details_wrapper = $(this).find(".details-wrapper");
				var eureka_home = $(this).find(".eureka_home");
				
				eureka_home.after("<div class='widget_details_wrapper'>" + details_wrapper.html() + "</div>");
					
				details_wrapper.remove();
				var title_element = $(this).find(".widget_details_wrapper").find(".eureka_custom_details_msg");
				title_element.remove();
				
				if (graph_count == 0) {	
					coa_graph = eureka_home;
					coa_details = coa_graph.parent().find(".widget_details_wrapper");
				}
				else {
					all_attempts_graph = eureka_home;
					all_attempts_details = all_attempts_graph.parent().find(".widget_details_wrapper");
					all_attempts_graph.hide();
				}
				
				// move the key
				if (graph_count == 0) {
					var key = $(this).find(".key-wrapper");
					am_widget_wrapper.append("<div class='key-wrapper'>" + key.html() + "</div>");
					key.remove();
				}
				
				graph_count++;
			});
			
			// do some key style adjustments if this is hte small widget
			if (small_widget) {
				
				var eureka_key = am_widget_wrapper.find(".key-wrapper");
				
				eureka_key.css("width", "110%").css("margin-left", "-2em");
				eureka_key.find(".left-justified").css("width", "16px");
				eureka_key.find(".key-line").css("width", "8px");
				eureka_key.find(".key-green-point").css("margin-left", "0em").css("width", "0.01em");
			}
			
			// toggle between graphs
			$(this).find(".airway_management_bottom_eureka_links").find(".toggle_am_eureka_graphs").click(function(e){
				e.preventDefault();
				
				if ($(this).hasClass(show_all_class)) {
					// we're toggling TO ALL attmepts
					$(this).removeClass(show_all_class);
					$(this).addClass(show_coa_class);
					$(this).text(show_coa_text);
					coa_graph.hide();
					all_attempts_graph.fadeIn();
					widget_eureka_title.text(all_attempts_title);
					
					// now update the details (if they are visible)
					if (coa_details.css("display") != "none") {
						coa_details.hide();
						all_attempts_details.show();
					}
				}
				else {
					// we're toggling TO COA attempts
					$(this).removeClass(show_coa_class);
					$(this).addClass(show_all_class);
					$(this).text(show_all_text);
					all_attempts_graph.hide();
					coa_graph.fadeIn();
					widget_eureka_title.text(coa_title);
					
					// now update the details (if they are visible)
					if (all_attempts_details.css("display") != "none") {
						all_attempts_details.hide();
						coa_details.show();
					}
				}
				
			});
			
			// show details
			details_trigger = $(this).find(".airway_management_bottom_eureka_links").find(".show_am_widget_eureka_details");
			details_trigger.click(function(e){
				e.preventDefault();
				trigger = $(this);
				
				if (trigger.hasClass("disabled_trigger_link")) {
					return;
				}
				
				trigger.addClass("disabled_trigger_link");
				
				var coa_graph_visible = false;
				var details_wrapper = all_attempts_details;
				
				// find out which details we need to show
				if(am_widget_wrapper.find(".airway_management_bottom_eureka_links").find(".toggle_am_eureka_graphs").hasClass(show_all_class)){
					// currently looking at just the last 20 attempts
					coa_graph_visible = true;
					details_wrapper = coa_details;
				}
				
				var key = am_widget_wrapper.find(".key-wrapper");
				var coa_success_wrapper = $(".success_progress_wrapper");
				var height = details_wrapper.height() + 3;
				
				if (details_wrapper.css("display") == "none") {
					// show it
					var key_visiblity = "hidden";
					
					if (key.css("display") != "none") {
						key.animate({marginTop: height-23});
						key_visiblity = "visible";
					}
					else {
						// move the coa success wrapper
						coa_success_wrapper.animate({marginTop: height-8});
					}
					
					var height_adjustment = getAnimationAdjustment("show", "details", key_visiblity, "height");
					
					change_airway_management_widget_height(height_adjustment);
					details_wrapper.css("position", "absolute").css("opacity", "1");
					
					setTimeout(function(){
						details_wrapper.fadeIn().css("position", "static");
						key.css("margin-top", "0");
						coa_success_wrapper.css("margin-top", "1em");
					}, 600);
					
					$(this).text("Hide details");
					
				}
				else {
					// hide it
					details_wrapper.animate({opacity: 0});
					var key_visiblity = "hidden";
					
					setTimeout(function(){
						if (key.css("display") != "none") {
							key.css("margin-top", height+10);
							key_visiblity = "visible";
						}
						else {
							coa_success_wrapper.css("margin-top", height+25);
						}
						
						details_wrapper.hide().css("opacity", "1");
						
					}, 300);
					
					
					setTimeout(function(){
						var height_adjustment = getAnimationAdjustment("hide", "details", key_visiblity, "height");
						if (key.css("display") != "none") {
							key.animate({marginTop: 0});
						}
						else {
							coa_success_wrapper.animate({marginTop: 8});
						}
						
						change_airway_management_widget_height(height_adjustment);
					},500);
					
					$(this).text("Show details");
				}
				
				setTimeout(function(){
					$(".disabled_trigger_link").removeClass("disabled_trigger_link");
				}, 1000);
				
			});	
			
			
			// show key
			$(this).find(".airway_management_bottom_eureka_links").find(".show_am_widget_eureka_key").click(function(e){
				e.preventDefault();
				
				trigger = $(this);
				
				if (trigger.hasClass("disabled_trigger_link")) {
					return;
				}
				
				trigger.addClass("disabled_trigger_link");
				
				var key_wrapper = am_widget_wrapper.find(".key-wrapper");
				var coa_success_wrapper = $(".success_progress_wrapper");
				var height = key_wrapper.height() + 3;
				
				var details_wrapper = all_attempts_details;
				
				// find out which details we need to show
				if(am_widget_wrapper.find(".airway_management_bottom_eureka_links").find(".toggle_am_eureka_graphs").hasClass(show_all_class)){
					// currently looking at just the last 20 attempts
					details_wrapper = coa_details;
				}
				
				
				if (key_wrapper.css("display") == "none") {
					// let's show it
					var details_visibility = "hidden";
					
					if (details_wrapper.css("display") != "none") {
						details_visibility = "visible";
					}
					
					var height_adjustment = getAnimationAdjustment("show", "key", details_visibility, "height");
					var margin_adjustment = getAnimationAdjustment("show", "key", details_visibility, "margin");
					
					// move the coa success wrapper
					coa_success_wrapper.animate({marginTop: height+margin_adjustment});
					
					change_airway_management_widget_height(height_adjustment);
					key_wrapper.css("position", "absolute").css("opacity", "1");
					
					setTimeout(function(){
						key_wrapper.fadeIn().css("position", "static");
						coa_success_wrapper.css("margin-top", "1em");
					}, 600);
						
					$(this).text("Hide key");	
				}
				else {
					// hide it
					var details_visibility = "hidden";
					
					if (details_wrapper.css("display") != "none") {
						details_visibility = "visible";
					}
					
					key_wrapper.animate({opacity: 0});
					
					
					var height_adjustment = getAnimationAdjustment("hide", "key", details_visibility, "height");
					var margin_adjustment = getAnimationAdjustment("hide", "key", details_visibility, "margin");
					
					setTimeout(function(){
						coa_success_wrapper.css("margin-top", height+margin_adjustment);
						key_wrapper.hide().css("opacity", "1");
					}, 300);
					
					setTimeout(function(){
						coa_success_wrapper.animate({marginTop: 8});
						change_airway_management_widget_height(height_adjustment);
					},400);
					
					$(this).text("Show key");
				}
				
				setTimeout(function(){
					$(".disabled_trigger_link").removeClass("disabled_trigger_link");
				}, 1000);
				
				
			});
			
		});
		
		
		//details_trigger.trigger("click");
		
	}, 1000);


	var getAnimationAdjustment = function(action, trigger, partner_visiblity, return_value)
	{
		var adjustments = getAdjustmentObject();
		var size = getWidgetSize();
		
		var return_val = adjustments[size][action][trigger][partner_visiblity][return_value];
		return return_val;
	}
	
	var getWidgetSize = function()
	{
		var size = "big";
		
		if (small_widget) {
			size = "small";
		}
		
		return size;
	}
	
	var getAdjustmentObject = function()
	{
		var adjustments = {
			small: {
				show: {
					details: {
						hidden: {height:50},
						visible: {height:44}
					},
					key: {
						hidden: {height:70, margin:12},
						visible: {height:70, margin:12}
					}
				},
				hide: {
					details: {
						hidden: {height:-80},
						visible: {height:-100}
					},
					key: {
						hidden: {height:-100, margin:13},
						visible: {height:-110, margin:12}
					}
				}
			},
			big: {
				show: {
					details: {
						hidden: {height:0},
						visible: {height:60}
					},
					key: {
						hidden: {height:12, margin:-3},
						visible: {height:60, margin:-2}
					}
				},
				hide: {
					details: {
						hidden: {height:-85},
						visible: {height:-100}
					},
					key: {
						hidden: {height:-50, margin:13},
						visible: {height:-110, margin:14}
					}
				}
			}
			
		};
		
		return adjustments;
		
	}
	
	var change_airway_management_widget_height = function(adjustment)
	{
		$(".airway_management_widget_launch_report").show();
		
		var airway_management_widget_wrapper = $(".airway_management_widget_wrapper");
		var widget = airway_management_widget_wrapper.parent().parent().parent().parent();
		widget.css("background-color", "#494544");
		if (!adjustment) {
			adjustment = 0;
		}
		
		var new_height = airway_management_widget_wrapper.actual('height') + 30;
		
		if (adjustment) {
			new_height = new_height + adjustment;
		}
		
		// also add some extra pixels for the absolutely positioned link at the bottom
		var final_height = new_height+75;

        airway_management_widget_wrapper.css("padding-bottom", "0em");
		widget.animate({height: final_height}, 500);
	} 

});