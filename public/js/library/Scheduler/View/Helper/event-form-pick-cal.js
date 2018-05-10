var monthNames = [ "January", "February", "March", "April", "May", "June",
				"July", "August", "September", "October", "November", "December" ];
	
$(function() {
	
	updateShiftSummary();
	
	$("#calendar-home").hide();
	start_date = new Date($("#start_date").val().replace(/-/g, '/'));
	createPickCal(start_date, getMonthShowCount(), "fade");
		
	if ($("#admin-edit-event-form").length > 0 || $("#non-admin-edit-event-form").length > 0) {
		// we're editing, show the shift summary
		$("#see-shift-list").trigger("click");
		// only show the student list if there are some students
		if ($("#assigned_count").html() != "0") { $("#see-assigned-list").trigger("click"); }
	}
	
	$("selected_days").find("option").each(function(){
		$(this).attr("id", $(this).text());
	});
	
	$("#repeat-stage").find(".slider-button").click(function(e, keepHelp){
		// if no dates selected
		if ($(".selected-day").length == 0 && $("#repeat-disable-screen").is(':hidden')) {
			$("#repeat-help").slideDown();
			$(this).trigger("click", true);
		} else {
			// hide the repeating help message
			if ($("#repeat-help").is(":visible") && !keepHelp) {
				$("#repeat-help").slideUp();
			}

			updateRepeatFrequencyTypeOptions();
			updateEchoDays();
		}
	});
	
	$("#repeat_frequency").change(function(){
		updateEchoDays();
		updateFrequencyTypeLabels($("#repeat_frequency").val());
	});
	$("#repeat_frequency_type").change(function(){
		updateEchoDays();
		
		$("#repeat_frequency_type_radios-element").find("input").each(function(){
			$(this).removeAttr("checked");
		});
					
		var freq_id = getFrequencyType(true);
		$("input[name='repeat_frequency_type_radios'][value='" + freq_id + "']").attr("checked", "checked");
		
	});
	$("#repeat_until").change(function(){updateEchoDays();});
	
	$("#next-months").click(function(e){
		e.preventDefault();
		cleanUpRows();
		var nextStartDay = getOneDayLater(getLastDayShown());
		createPickCal(nextStartDay, getMonthShowCount(), "slideFromRight");
	});
	
	$("#prev-months").click(function(e){
		e.preventDefault();
		cleanUpRows();
		var nextStartDay = getMonthsBefore(getFirstDayShown(), getMonthShowCount());
		createPickCal(nextStartDay, getMonthShowCount(), "slideFromLeft");
	});
	
	$("#expand-cal").find("a").click(function(e){
		e.preventDefault();
		cleanUpRows();
		
		
		if (!$(this).hasClass("selected-month-count")) {
			
			var oldCount = 	getMonthShowCount();
			
			$(".selected-month-count").removeClass("selected-month-count");
			$(this).addClass("selected-month-count");
			
			var newCount = getMonthShowCount();
			var expandBy = 0;
			
			if(newCount > oldCount){
				expandBy = newCount-oldCount;
				var nextStartDay = getOneDayLater(getLastDayShown());
				createPickCal(nextStartDay, expandBy, "slideDown");
			}
			else {
				var rowCount = 0;
				$(".pickcal-row").each(function(){
					rowCount++;
					if (rowCount > (newCount/3)) {
						$(this).slideUp("fast").addClass("clean-up-row");
					}
				});
			}
		}
		
		$(".month-count-display").text(getMonthShowCount());

		
	});
	
	function cleanUpRows() {
		$(".clean-up-row").each(function(){
			$(this).remove();
		});
	}
	
	function getLastDayShown() {
		var lastDayInSeries = $(".active-day-block").last().attr("data-datetime").replace(/-/g, '/');
		var lastDay = new Date(lastDayInSeries);
		return getOneDayLater(lastDay);
	}
	
	function getFirstDayShown() {
		var firstDayInSeries = $(".active-day-block").first().attr("data-datetime").replace(/-/g, '/');
		var firstDay = new Date(firstDayInSeries);
		return getOneDayLater(firstDay);
	}
	
	function getOneDayLater(day) {
		return new Date(day.getTime() + (24 * 60 * 60 * 1000));
	}
	
	function getOneDayBefore(day) {
		return new Date(day.getTime() - (24 * 60 * 60 * 1000));
	}
	
	function getMonthsBefore(day, numberOfMonthsBefore) {
		return new Date(new Date(day).setMonth(day.getMonth()-numberOfMonthsBefore));
	}
	
	function getMonthShowCount() {
		return parseInt($("#expand-cal").find(".selected-month-count").text());
	}
	
	function createPickCal(start_date, numberOfMonths, effect) {
		
		cleanUpRows();
		
		if (effect == "fade") {
			$("#calendar-home").fadeOut();
		}
		else if (effect == "slideFromRight") {
			// so slide out left!
			$("#calendar-home").hide("slide", { direction: "left" }, 400);
		}
		else if (effect == "slideFromLeft") {
			$("#calendar-home").hide("slide", { direction: "right" }, 400);
		}
		
		if (effect != "slideDown") {
			$("#calendar-home").empty();
		}
		
		var rollingYear = start_date.getFullYear();
		var html = "";
		

		
		var monthStart = 1;
		
		monthStart = start_date.getMonth()+1;
		
		var monthCount = monthStart;
		var pickCalRowCount = 1;
		
		
		for (var i = 0; i < numberOfMonths; i++) {
			
			if (pickCalRowCount == 1) {
				extraClass = "";
				if (effect == "slideDown") {extraClass = "hidden-row"}
				html += "<div class='pickcal-row " + extraClass + "'>";
			}
			
			if (monthCount > 12) {
				monthCount = 1;
				rollingYear++;
			}
			
			var calsDate = new Date(monthCount + "/01/" + rollingYear);

			html += buildCal(calsDate);
			monthCount++;
			
			if (pickCalRowCount == 3) {
				pickCalRowCount = 1;
				html += "<div class='clear'></div></div>";
			}
			else {
				pickCalRowCount++;
			}
		}
		
		$("#clear-cal").click(function(e){
			e.preventDefault();
			$(".selected-day").removeClass("selected-day");
			$("#selected_days").find("option").remove();
			removeAllEchoDays();
			disableFrequencyTypeOption(false, "day");
			disableFrequencyTypeOption(false, "week");
			disableFrequencyTypeOption(false, "month");
			updateShiftSummary();
			$("#repeat-stage").find(".slider-button").trigger("click");
		});
		
		html += "<div class='clear'></div>";
		
		if (effect != "slideDown") {
			$("#calendar-home").append(html);
		}
		else {
			$("#calendar-home").append(html);
			$(".hidden-row").slideDown();
		}
		
		if (effect == "fade") {
			$("#calendar-home").fadeIn();
		}
		else if (effect == "slideFromRight") {
			// so slide out left!
			$("#calendar-home").show("slide", { direction: "right" }, 400);
		}
		else if (effect == "slideFromLeft") {
			$("#calendar-home").show("slide", { direction: "left" }, 400);
		}
		
		$(".pickcal-row").each(function(){
			
			var tallestMonth = 0;
			$(this).find(".month").each(function(){
				height = $(this).height();
				if (height > tallestMonth) {
					tallestMonth = height;
				}
			});
			
			$(this).find(".month").css("height", tallestMonth + "px");	
			
		});

		
		initDayBlockInteractions();	
	}

	
	function initDayBlockInteractions() {
		$(".day-block").unbind();
		$(".day-block").each(function(){
			
			var val = $(this).attr("data-dateTime");
			
			// find out if it is selected or echoed and make it display that!
			if($("#" + val).length > 0){
				// it's either selected or echoed
				var parentId = $("#" + val).parent().attr("id");

				if (parentId == "echoed_days") {
					$(this).addClass("repeated-day");
				}
				else if (parentId == "selected_days") {
					$(this).addClass("selected-day");
				}
				
			}
			
			if (!$(this).hasClass("has-opacity")) {

				$(this).hover(function(){
					$(this).addClass("day-block-hover");
				}, function(){
					$(this).removeClass("day-block-hover");
				});
					
				$(this).click(function(){
					val = $(this).attr("data-dateTime");
					
					if($(this).hasClass("repeated-day")){
									
						var val = $(this).attr("data-dateTime");
						$(this).removeClass("repeated-day");
						$("#" + val).remove();
					}
					
					else {
						if ($(this).hasClass("selected-day")) {
							$(this).removeClass("selected-day");
							$("#" + val).remove();
						}
						else {
							$(this).addClass("selected-day");
							$("#selected_days").append('<option selected="selected" id="' + val + '" value="' + val + '">' + val + '</option>');
						}
						
						
						if ($(".repeated-day").length == 0) {
							updateRepeatFrequencyTypeOptions();
						}
					}
					
					updateRepeatStart();
					updateShiftSummary();
					
				});
			}
		})
		
	}
	
	function updateRepeatStart() {
		cleanUpRows();
		sortSelectedDays();
		var fromDate = $("#selected_days").find("option").first().text().split("-");
		var minDate = new Date(fromDate[1] + "/" + fromDate[2] + "/" + fromDate[0]);
		if ($("#selected_days").find("option").length > 0) {
			$("#repeat_from").text(fromDate[1] + "/" + fromDate[2] + "/" + fromDate[0]);
		}
		else {
			$("#repeat_from").text("today");
			minDate = new Date();
		}
		
		// set the min date on the repeat_until date picker
		$("#repeat_until").datepicker( "option", "minDate", getOneDayLater(minDate) );
	}
	
	function updateShiftSummary() {
		var shortMonthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun",
			"Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
		
		var selectedCount = 0;
		var echoedCount = 0;
		
		var raw_shifts = [];
		$("#shift-list").empty();
		
		$("#selected_days").find("option").each(function(){
			selectedCount++;
			var date = $(this).text();
			raw_shifts.push(date.replace(/-/g,""));
		});
		
		$("#echoed_days").find("option").each(function(){
			echoedCount++;
			var date = $(this).text();
			raw_shifts.push(date.replace(/-/g,""));
		});
		
		// now order the shift list
		var shifts = raw_shifts;
		shifts.sort(function(a,b) {
			if (a > b) return 1;
			else if (a < b) return -1;
			else return 0
		});
		
		$("#shift-list").empty();
		
		$.each(shifts, function(i, v){
			var y = v.substring(0,4);
			var m = parseInt(v.substring(4,6), 10)-1;
			var d = parseInt(v.substring(6,8), 10);
			
			if (m < 0) {
				m = 0;
			}
			
			var formatted_date = shortMonthNames[m] + " " + d + ", " + y;
			
			$("#shift-list").append("<div class='shift " + getShiftType() + "'>" + formatted_date + "</div>");
		});
		
		var totalShifts = selectedCount + echoedCount;
		$("#shift_count").text(totalShifts);
		if (totalShifts == 1) {
			$("#shift_plural").text("");
			$("#shift_count_plural").text("");
			$("#assigned_shift_count").text("1 shift");
		}
		else {
			$("#shift_plural").text("s");
			$("#shift_count_plural").text("s");
			if (totalShifts == 2) {
				$("#assigned_shift_count").text("2 shifts");
			}
			else {
				$("#assigned_shift_count").text("all " + totalShifts + " shifts");
			}
		}
		
		// now let's update our save button
		if (totalShifts == 0) {
			//$("#save").val("Create shifts").button("disable");
			$("#save").val("Create shifts");
			if($("#admin-edit-event-form").length > 0 || $("#non-admin-edit-event-form").length > 0){
				//$("#save").val("Save shifts").button("disable");
				$("#save").val("Save shifts");
			}
		}
		else {
			$("#save").button("enable");
			var btn_text = "Create "; 

			if($("#admin-edit-event-form").length > 0 || $("#non-admin-edit-event-form").length > 0){
				btn_text = "Save ";
			}
			
			btn_text += totalShifts + " shift";
			
			if (totalShifts != 1) {
				btn_text += "s";
			}
			$("#save").val(btn_text);
		}
		
		
	}
	
	function sortSelectedDays() {
		var options = $("#selected_days option");
		options.sort(function(a,b) {
			if (a.text > b.text) return 1;
			else if (a.text < b.text) return -1;
			else return 0
		})
		
		$("#selected_days").empty().append(options);
	}
	
	function updateFrequencyTypeLabels(offsetNumber) {
		var buttons = $("#repeat_frequency_type").find(".ui-button-text");
        buttons.each(function(){
			var buttonType = $(this).parent().attr("for");
            // make first letter uppercase
            var f = buttonType.charAt(0).toUpperCase();
            buttonType = f + buttonType.substr(1);
            if (offsetNumber == "1") {
                $(this).html(buttonType);
            } else {
                $(this).html(buttonType + "s");
            }
        });
	}
	
	function updateRepeatFrequencyTypeOptions() {
		
		var selectedCount = 0;
		$(".selected-day").each(function(){
			selectedCount++;
		});

		if (selectedCount == 1) {
			// all are enabled
			disableFrequencyTypeOption(false, "day");
			disableFrequencyTypeOption(false, "week");
			disableFrequencyTypeOption(false, "month");
		}
		
		if (selectedCount > 1) {
			// if more than one day is picked, we can't repeat by day
			disableFrequencyTypeOption(true, "day");
			
			var firstSelected = parseDataDatetime($(".selected-day").first().attr("data-datetime"));
			var months = [];
			multipleMonths = false;
			multipleWeeks = false;
			
			// loop through the selected days and see if we can repeat by month or week
			$(".selected-day").each(function(){
				
				// see if the days are in multiple calendar months
				var month = $(this).attr("data-datetime").split("-");
				if (months.length > 0) {
					if ($.inArray(month[1], months) === -1) {
						multipleMonths = true;
					}
					else {
						months.push(month[1]);
					}
				}
				else {
					months.push(month[1]);
				}
				
				// see if the days are a week or more apart
				var days = daydiff(firstSelected, parseDataDatetime($(this).attr("data-datetime")));
				if (days > 6) {	
					multipleWeeks = true;
				}
			});
			
			if (multipleMonths) {
				disableFrequencyTypeOption(true, "month");
			}
			else {
				disableFrequencyTypeOption(false, "month");
			}
			
			if (multipleWeeks) {
				disableFrequencyTypeOption(true, "week");
			}
			else {
				disableFrequencyTypeOption(false, "week");
			}
		}
	}
	
	function disableFrequencyTypeOption(disable, optionName) {
		if (disable) {
			$("#" + optionName).css("opacity", "0.3").removeAttr("checked");
			$("#" + optionName).next().css("opacity", "0.3").removeClass("ui-state-active");
			$("#" + optionName + "-disable").show();
		}
		else {
			$("#" + optionName).css("opacity", "1");
			$("#" + optionName).next().css("opacity", "1");
			$("#" + optionName + "-disable").hide();
		}
		
		// pick the smallest time frame that's still available
		if ($("#day-disable").is(":hidden")) { $("#day").trigger("click"); }
		else if ($("#week-disable").is(":hidden")) { $("#week").trigger("click"); }
		else if ($("#month-disable").is(":hidden")) { $("#month").trigger("click"); }
	}
	
	function updateEchoDays() {
		
		if ($("#repeat-disable-screen").css("display") == "none") {
			if (allRepeatDataComplete()) {
				
				removeAllEchoDays();
				
				var frequencyType = getFrequencyType();
				var frequency = $("#repeat_frequency").val();
				var endDate = $("#repeat_until").val();
				var first_selected_day = $(".selected-day").first().attr("data-datetime");
				
				if(first_selected_day == undefined){
					return;
				}
				
				var startDate = getStartDate(first_selected_day);
				
				var numberOfLoops = daydiff(parseDate(startDate['month'] + "/" + startDate['day'] + "/" + startDate['year']), parseDate(endDate));
				
				if(frequencyType == 'day'){
					doDailyRepeat(numberOfLoops, startDate['day'], frequency, startDate['month'], startDate['year'], endDate, false);
				}
				else if(frequencyType == 'week'){
					$(".selected-day").each(function(){
						startDate =  getStartDate($(this).attr("data-datetime"));				
						doDailyRepeat(numberOfLoops, startDate['day'], (7*frequency), startDate['month'], startDate['year'], endDate, false);
					});
				}
				else if(frequencyType == 'month'){
					$(".selected-day").each(function(){
						startDate =  getStartDate($(this).attr("data-datetime"));				
						newFrequency = frequency;
						doDailyRepeat(numberOfLoops,  startDate['day'], newFrequency,  startDate['month'],  startDate['year'], endDate, true);
					});
				}
			}
			else {
				removeAllEchoDays();
			}
		}
		else {
			removeAllEchoDays();
		}
		
		updateShiftSummary();
		
	}
	
	function removeAllEchoDays() {
		$("#echoed_days").find("option").remove();
		$(".repeated-day").removeClass("repeated-day");
	}
	
	function allRepeatDataComplete() {
		var complete = true;
		if ($("#repeat_until").val() == "") {
			complete = false;
		}
		return complete;
	}
	
	function getStartDate(dataDatetime) {
		var startingDate = dataDatetime.split("-");
		var startDate = {};
		startDate['day'] = parseInt(startingDate[2]);
		startDate['month'] = parseInt(startingDate[1]);
		startDate['year'] = parseInt(startingDate[0]);
		return startDate;
	}
	
	function parseDate(str) {
		var mdy = str.split('/')
		return new Date(mdy[2], mdy[0]-1, mdy[1]);
	}
	
	function parseDataDatetime(str) {
		var ymd = str.split('-')
		return new Date(ymd[0], ymd[1]-1, ymd[2]);
	}
	
	function daydiff(first, second) {
		return (second-first)/(1000*60*60*24)
	}
	
	function getFrequencyType(dbid) {
		var frequencyType = null;
		$("#repeat_frequency_type").find("input").each(function(){
			if ($(this).attr("checked")) {
				frequencyType = $(this).attr("id");
				if (dbid) {
					frequencyType = $(this).attr("data-dbid");
				}
			}
		});
		return frequencyType;
	}
	
	function doDailyRepeat(numberOfLoops, newDay, frequency, curMonth, curYear, endDate, monthly){
		var endFlag = false;
		var month_counter = 1;
		var skip_loop = false;
		
		for(i = 1; i <= numberOfLoops; i++){
				
			if(monthly){
				// add the number of the days in the month to our rolling counter
				newFrequent = daysInMonth((parseInt(curMonth)-1), curYear);
				newDay = newDay + parseInt(newFrequent);
				
				if((month_counter%frequency) != 0){
					// we need to not count this day
					skip_loop = true;
				}
				else {
					skip_loop = false;
				}
				
			}
			else {
				newDay = newDay + parseInt(frequency);
			}
			
			
			var currentDate = curMonth + "/" + newDay + "/" + curYear;
			var daysDiff = daydiff(parseDate(currentDate), parseDate(endDate));
			
			if(daysDiff == 0){
				// set a flag that we need to exit the loop after adding 1 more
				endFlag = true;
			}
			else if(daysDiff < 0){
				// exit now
				break;
			}
			
			var daysInCurrentMonth = daysInMonth((parseInt(curMonth)-1), curYear);
		
			if(newDay > daysInCurrentMonth){
				// start a new month
				
				if(curMonth == "12"){
					curMonth = "1";
					curYear = curYear+1;
				}
				else {
					curMonth = parseInt(curMonth)+1;
				}
				
				month_counter++;

				newDay = parseInt(newDay) - daysInCurrentMonth;
				//newDay = 1;
			}
			
			if(newDay < 10){
				displayDay = "0" + newDay;
			}
			else {
				displayDay = newDay;
			}
			
			if (!skip_loop) {
				addEchoDay(curMonth, displayDay, curYear);
			}
			
			if(endFlag){break;}
		}
	}
	
	function addEchoDay(month, day, year) {
		month = String(month);
		if (month.length == 1) {
			month = "0" + month;
		}
		
		if (month == "00") {
			month = 12;
		}
		
		var val = year + "-" + month + "-" + day;
		$("#echoed_days").append('<option selected="selected" id="' + val + '" value="' + val + '">' + val + '</option>');
		$("#day-block-" + val).addClass("repeated-day");
	}
	
	function buildCal(date){
		var returnCal = "";
		var year = date.getFullYear();  // Returns year
		var month = date.getMonth();    // Returns month (0-11)
		var weekday = date.getDay();    // Returns day (0-6)
		var daysDifference = -1;
		
		var today = new Date();
		
		// for each day from previous month
		for(var j = 0; j < weekday; j++){daysDifference++;}
		
		// how many days are in the previous month?
		if(month == 0){
			prevMonth = 12;
			prevYear = year-1;
		}
		else {
			prevMonth = month-1;
			prevYear = year;
		}
	
		var prevMonthDayCount = daysInMonth(prevMonth, prevYear);
		var daysBeforeCount = daysDifference;
	
		// set friendly month/year in the title
		returnCal += "<div class='smart-pick-cal'>";
		
		returnCal += "<div class='cal-header'><h3>" + monthNames[month] + " " + year + "</h3></div>";

		returnCal += "<div class='clear'></div>";

		returnCal += "<div class='month'>";
				returnCal += "<div class='pc-day-name'>Sun</div>";
		returnCal += "<div class='pc-day-name'>Mon</div>";
		returnCal += "<div class='pc-day-name'>Tues</div>";
		returnCal += "<div class='pc-day-name'>Wed</div>";
		returnCal += "<div class='pc-day-name'>Thurs</div>";
		returnCal += "<div class='pc-day-name'>Fri</div>";
		returnCal += "<div class='pc-day-name'>Sat</div>";
		
		for(var j = 0; j < weekday; j++){
			var dayNum = prevMonthDayCount-daysBeforeCount;
			returnCal += "<div class='day-block has-opacity'><div class='day-num'>" + dayNum + "</div></div>";
			daysBeforeCount--;
		}
			
		var dayCount = 1;
		var numOfLoops = (daysInMonth(month, year) + daysDifference);
		var weekdayCount = 1;
		var weekCount = 1;
		
		for(var i = 0; i <= numOfLoops; i++){
			
			if(i >= weekday){
				var displayMonth = parseInt(month+1);
				if(month < 10){
					displayMonth = "0" + displayMonth;
				}
				
				if (displayMonth == "010") {
					displayMonth = "10";
				}
	
				var displayDay = dayCount;
				if(dayCount < 10){
					displayDay = "0" + dayCount;
				}
				
				var todayClass = "";
				var todayImage = "";
				var beforeTodayClass = "";
				var dateTime = year + "-" + displayMonth + "-" + displayDay;
				var dayBlockDate = new Date(dateTime);
				
				if (dayBlockDate < today) {
					beforeTodayClass = "has-opacity before-today";
					if (getOneDayLater(dayBlockDate) > today) {
						todayClass = "today-block";
						beforeTodayClass = "";
					}
				}
				

				
				returnCal += "<div id='day-block-" + dateTime + "' class='day-block active-day-block " + todayClass + " " + beforeTodayClass + "' data-dateTime='" + dateTime +"' data-week='" + weekCount + "'>";
				returnCal +=    dayCount;
				returnCal += 	todayImage;
				returnCal += "</div>";
				
				dayCount++;
			}
			
			if(weekdayCount == 7){
				// we've started a new week
				weekdayCount = 1;
				weekCount++;
				returnCal += "<div class='clear'></div>";
			}
			else {
				weekdayCount++;
			}
			
		}
	
		// for each day until previous month
		var nextMonthCount = 1;
		var daysLeft = 7-weekdayCount;
	
		if(daysLeft != 6){                                                                  
			for(var k = 0; k <= daysLeft; k++){
				returnCal += "<div class='day-block has-opacity'><div class='day-num'>" + nextMonthCount + "</div></div>";
				nextMonthCount++;
			}
		}
		
			
		returnCal += "<div class='clear'></div></div></div>";
		return returnCal;
	}
	
	function daysInMonth(m, y) {
		m = m+1;
	   return /8|3|5|10/.test(--m)?30:m==1?(!(y%4)&&y%100)||!(y%400)?29:28:31;
	}
	
	function getNewMonth(m){
				if(m == '12'){
					m = '01';
				}
				else {
					m = (parseInt(m)) + 1;
				}
				
				if((parseInt(m)) < 10){
					m = "0" + m;
				}
				
				return m;
			}
			
			function getNewYear(m, y){
				m = (parseInt(m)) - 1;
				
				if(m == '12'){
					y = parseInt(y)+1;
				}
				
				return y;
			}

});