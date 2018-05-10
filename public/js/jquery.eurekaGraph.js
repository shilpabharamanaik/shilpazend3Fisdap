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
 * jQuery plugin for creating eureka graphs
 */

(function( $ ){
	$.fn.eurekaGraph = function(options) {
		// set up a variable we'll use later for returning content
		var returnContent = "";
		
		var eureka_home = $("#eureka-home");
		var in_widget = false;
		
		if (this.attr("data-nonlabpractice")) {
			eureka_home = this.find(".eureka_home");
		}
		
		if (this.attr("data-inawidget")) {
			in_widget = true;
		}
		
		// grab and save a bunch of elements coming in
		var successList = this.find(".success-list");
		
		var dateList = this.find(".date-list");
		var key = this.find(".key-wrapper");
		var modalHeader = this.find(".eureka-headers");
		var details = this.find(".details-wrapper");
		
		// initialize/calculate a few things
		var eureka = false;
		var yMin = successList.attr("data-ymin");
		var yMax = successList.attr("data-ymax");
		yMax = (parseInt(yMax)) + 0.5;
		
		// get the goal and window from the successList attributes
		var goal = successList.attr("data-goal");
		var window = successList.attr("data-window");
		
		// calculate the success precentage rate/red line success rate
		var goalPrecentageRate = goal/window;
		var lowSuccessRate = goalPrecentageRate * .75;
		
		// throw on the header
		eureka_home.parent().prepend(modalHeader.html());
		eureka_home.parent().append("<div class='print-eureka'><a href='#' id='print-eureka'>Print</a></div>");
		
		// set up our array of 1s and 0s and our corresponding array of dates
		var list = successList.text().split(',');
		var dateArray = dateList.text().split(',');
		
		if (successList.text() == "") {
			list.pop(); // remove the extra blank element
		}
		
		dateArray.pop();

		// initialize our points/colors
		var plotPoints = [];
		var seriesColors = [];
		var count = 1;
		var yaxisPoint = 0;
		var eruekaAttemptNumber = "";
		
		// will be used for labeling the x axis
		var ticks = [];
		var tickRate = 1;
		
		// only list every 
		if(list.length > 20){
			tickRate = Math.ceil(list.length / 8);
		}
		
		// set our initial points (0 and 1 are reserved for eureka point/gray line series)
		plotPoints[0] = [];
		plotPoints[1] = [];
		
		// window for checking success rates
		var movingWindow = Math.round(window / 2);
		
		// the arrays for containing the passes/fails of the windows
		var successes = []; // used for eureka
		var successesHalfWindow = []; // used for colors
		
		var currentSeriesNumber = 2;
		var currentSeriesColor = 'red';

		// add our first point (0,0) - can assume it will be red
		plotPoints[currentSeriesNumber] = [];
		plotPoints[currentSeriesNumber].push([0, 0]);
		seriesColors[currentSeriesNumber] = currentSeriesColor;
		
		ticks.push([0, 0]);

		// step through our list of passes/fails
		$.each(list, function(index, value){
			
			// for our x axis
			var label = "";
			if(count%tickRate == 0){
				label = count;
			}
			ticks.push([count, label]);
			
			// save this value for starting new series
			var preYaxisPoint = yaxisPoint;

			// figure out if we need to go up or down
			if(value == 1){yaxisPoint = yaxisPoint + 1;}
			else {yaxisPoint = yaxisPoint - 1;}
			
			// update our windows for determining success rate
			
			if(successes.length == window){successes.shift();}
			successes.push(value);
			
			// for colors
			if(successesHalfWindow.length == movingWindow){successesHalfWindow.shift();}
			successesHalfWindow.push(value);
			
			// we've reached eureka!
			var enoughAttempts = count >= window;
			
			if(enoughAttempts && ((getArraySum(successes) / window) >= goalPrecentageRate) && !eureka){
				
				eureka = true;
				eruekaAttemptNumber = count;
				
				// make our green dot on the graph
				plotPoints[0].push([count, yaxisPoint]);
				seriesColors[0] = "eruekaPoint";
				
				// begin our gray line
				plotPoints[1].push([count, yaxisPoint]);
				seriesColors[1] = "gray";
				
				// give it a point in the far distance to draw our projected path for a consistent success rate
				var eurekaLineY = ((goalPrecentageRate * 10) + yaxisPoint)*2;
				var eurekaLineX = (count + 10)*2;
				
				// if the gray line isn't off the graph
				if(eurekaLineY < yMax){
					// scale again
					eurekaLineY = eurekaLineY*2;
					eurekaLineX = count*2;
				}
				plotPoints[1].push([eurekaLineX, eurekaLineY]);
			}
			
			// what's our success rate for determining colors?
			var successPrecentage = (getArraySum(successesHalfWindow) / movingWindow);
			var seriesToAddTo = "";
			
			// determine which color the line should have
			if(successPrecentage < lowSuccessRate){seriesToAddTo = "red";}
			else if(successPrecentage < goalPrecentageRate) {seriesToAddTo = "yellow";}
			else if(successPrecentage >= goalPrecentageRate){seriesToAddTo = "green";}
			
			// are we currently using that color/series?
			if(seriesToAddTo == currentSeriesColor){
				plotPoints[currentSeriesNumber].push([count, yaxisPoint]);
			}
			else {
				// create a new series
				var newNumber = parseInt(currentSeriesNumber)+1;
				plotPoints[newNumber] = [];
				plotPoints[newNumber].push([count-1, preYaxisPoint]);
				plotPoints[newNumber].push([count, yaxisPoint]);
				seriesColors[newNumber] = seriesToAddTo;

				currentSeriesNumber = newNumber;
				currentSeriesColor = seriesToAddTo;
			}
			
			count++;
		});
		
		// if we never reached eureka, we need to give these guys some
		// values anyways or else the graph won't work
		if(!eureka){
			plotPoints[0].push([0, 0]);
			plotPoints[1].push([0, 0]);
			
		}
		else {
			// add another green dot so it becomes the top layer
			var topLayerSeriesNumber = currentSeriesNumber+1;
			plotPoints[topLayerSeriesNumber] = [];
			plotPoints[topLayerSeriesNumber].push(plotPoints[0]);
			seriesColors[topLayerSeriesNumber] = "eruekaPoint";
		}
		
		// our array that will contain colors and other series options
		var seriesOptions = [];
		
		// step through our series and apply the colors/other options
		$.each(seriesColors, function(index, value){

			var hexColor = "#000";
			var showMarker = false;
			var showShadow = false;
			var lineWidth = 2.5;
			
			if(value == "red"){hexColor = "#ed2125";}
			else if(value == "yellow"){hexColor = "#f6c61f";}
			else if(value == "green") {hexColor = "#84c724";}
			else if(value == "eruekaPoint") {
				hexColor = "#84c724";
				showMarker = true;
			}
			else {
				hexColor = "#ccc";
				showShadow = false;
				lineWidth = 1.5;
			}
			
			// add it to our seriesOptions array in a way that the jqPlot plugin will like it
			seriesOptions.push({color:hexColor, showMarker:showMarker, shadow:showShadow, lineWidth: lineWidth});
		});
		
		
		// handle some final x-axis labeling stuff
		if(tickRate != 1){
			var leftOff = ticks.pop();
			var lastTick = count-1;
			var newCount = leftOff[0];
			
			while(newCount <= lastTick){
				var newLabel = "";
				if(newCount == lastTick){newLabel = lastTick;}
				ticks.push([newCount, newLabel]);
				newCount++;
			}
		}
		
		if(eruekaAttemptNumber == count-1){
			ticks.push([count, count]);
		}
		
		if(list.length > 0){
			// Finally, call the graphing plugin
			var axes_defaults = {};
			var axes = {};
			var grid = {};
			
			if (in_widget) {
				axes_defaults = {show: false, tickOptions: {showMark:false, showLabel:false}};
				axes = {xaxis:{min:0, ticks:ticks,tickOptions: {showMark:false, showLabel:false}, show:false}, yaxis:{show:false}};
				grid = {gridLineColor: '#FAF4E4', background:'#fffdf6', borderColor:'#666666'};
			}
			else {
				axes_defaults = {tickOptions: {showMark:false}};
				axes = {xaxis:{min:0, ticks:ticks,tickOptions: {showMark:true}}};
				grid = {gridLineColor: '#FAF4E4', background:'#fffdf6'};
			}
			
			$.jqplot(eureka_home.attr("id"), plotPoints,
			{
			  axesDefaults:axes_defaults,
			  axes:axes,
			  series:seriesOptions,
			  seriesDefaults: {shadowOffset:1, shadowDepth: 2, shadowAlpha: 0.07},
			  grid: grid
			});
			
			// now remove awkward decimals off of our y-axis
			eureka_home.find(".jqplot-yaxis-tick").each(function(){
				var val = parseFloat($(this).text());
				var rounded_val = Math.round(val);
				
				if (val == rounded_val) {
					$(this).text(rounded_val);					
				}
				else {
					$(this).text("");
				}
			});
			
			
			var eurekaPointReachedText = "Attempt #" + eruekaAttemptNumber + " on " + dateArray[eruekaAttemptNumber-1];
			if(!eureka){eurekaPointReachedText = "N/A";}
			
			if (eureka) {
				this.find(".details-wrapper").addClass("eureka_reached").attr("data-eurekaAttemptNumber", eruekaAttemptNumber);
			}
			
			// now some basics to return some helpful detials/analysis to the user
			// Most of the data was caluclated in PHP, we'll just add a few additional pieces of information
			if(dateArray[0]){
				if(dateArray.length > 1){
					var dateRange = dateArray[0] + " - " + dateArray.pop();
					if(dateArray[0] == dateArray.pop()){dateRange = "All attempts from " + dateArray[0];}
				}
				else {
					dateRange = "All attempts from " + dateArray[0];
				}
			}
			else {
				var dateRange = "No attempts";
			}
			
		
			details.find(".details").prepend("Date Range: " + dateRange + "<br />");
			details.find(".details").append("Eureka Point Reached: " + eurekaPointReachedText);
			
			returnContent += details.html();
			returnContent += key.html();
			
		}
		
		else {
			if (this.attr("data-nonlabpractice")) {
				eureka_home.text("");
			}
			else {
				eureka_home.text("No attempts.");
			}
		}
		
		$("#print-eureka").click(function(e) {
			e.preventDefault();
			$('#eureka-modal').printArea();
		});
		
		// returns the sum of an array's elements
		// used to calculate success rates
		function getArraySum(arrayToAdd){
			var sum = 0;
			
			$.each(arrayToAdd, function(index, value){
				sum = sum + parseInt(value);
			});
			
			return sum;
		}
		
		
		return returnContent;
	};
		
})( jQuery );