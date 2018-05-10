var monthNames = [ "January", "February", "March", "April", "May", "June",
				"July", "August", "September", "October", "November", "December" ];

$(function() {});

var initWhichShiftsMultiPickcal;
initWhichShiftsMultiPickcal = function(){
	$("#whichEventsDialog").dialog({
		open: function (){
		    $("#cancel-which-events-modal").blur();
		}
	});

	//We have to replace the dashes with slashes, otherwise JS interprets the dates as UTC
	start_date = new Date($("#series_days").find("option").first().text().replace(/-/g, '/'));
	end_date = new Date($("#series_days").find("option").last().text().replace(/-/g, '/'));

	$("#multipick-cal-home").hide();

	//Create the datepickers and set the min and max accordingly
	$("#startDateRange").datepicker({ minDate: start_date, maxDate: end_date });
	$("#endDateRange").datepicker({ minDate: $("#startDateRange").datepicker("getDate"), maxDate: end_date });

	createPickCal(start_date, getMonthShowCount(), "fade");

	$(".series-option").click(function(e){

		$(".selected-series-option").removeClass("selected-series-option");
		$(this).addClass("selected-series-option");

		if ($(this).attr("id") == "multiple-shifts-option") {
            filterSelectedDays($("#startDateRange").datepicker("getDate"), end_date);
			$("#multiple-shifts-multipick-cal").slideDown();
		}
		else {
            //Since we're choosing "just this shift" we know there's only one shift being affected
            updateActionButtonText(1);
			$("#multiple-shifts-multipick-cal").slideUp();
		}

	});

	$("#cancel-which-events-btn, #continue-which-events-btn").find("a").button();

	// cancel button closes the modal
	$("#cancel-which-events-modal").click(function(e){
		e.preventDefault();
		$("#whichEventsDialog").dialog("close");
	});

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

	//Run the filter once onLoad
    //Commenting this out and moving it to be called once the user has chosen to edit/delete multiple shifts
	//filterSelectedDays($("#startDateRange").datepicker("getDate"), end_date);

	$("#startDateRange").change(function(e){
		filterSelectedDays($(this).datepicker("getDate"), $("#endDateRange").datepicker("getDate"));
		$("#endDateRange").datepicker( "option", "minDate", $(this).datepicker("getDate"));
	});

	$("#endDateRange").change(function(e){
		filterSelectedDays($("#startDateRange").datepicker("getDate"), $(this).datepicker("getDate"));
	});

	function filterSelectedDays(startDate, endDate) {
		//Reset the selected days
		$("#selected_days").empty();

		$("#series_days").find("option").each(function(){
			var currentDate = new Date($(this).text().replace(/-/g, '/'));
			toggleSelectedDay($("#mpc-day-block-" + $(this).text()), $(this).text(), $(this).attr("value"), (currentDate >= startDate && currentDate <= endDate));
		});
	}

	function cleanUpRows() {
		$(".clean-up-row").each(function(){
			$(this).remove();
		});
	}

	function getLastDayShown() {
		var lastDayInSeries = $(".active-mpc-day-block").last().attr("data-datetime").replace(/-/g, '/');
		var lastDay = new Date(lastDayInSeries);
		return getOneDayLater(lastDay);
	}
	
	function getFirstDayShown() {
		var firstDayInSeries = $(".active-mpc-day-block").first().attr("data-datetime").replace(/-/g, '/');
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
		//return parseInt($("#expand-cal").find(".selected-month-count").text());
		return 3;
	}

	function createPickCal(start_date, numberOfMonths, effect) {

		cleanUpRows();

		if (effect == "fade") {
			$("#multipick-cal-home").fadeOut();
		}
		else if (effect == "slideFromRight") {
			// so slide out left!
			$("#multipick-cal-home").hide("slide", { direction: "left" }, 400);
		}
		else if (effect == "slideFromLeft") {
			$("#multipick-cal-home").hide("slide", { direction: "right" }, 400);
		}

		if (effect != "slideDown") {
			$("#multipick-cal-home").empty();
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
			$("#repeat_until").val("");
			updateShiftSummary();
		});

		html += "<div class='clear'></div>";

		if (effect != "slideDown") {
			$("#multipick-cal-home").append(html);
		}
		else {
			$("#multipick-cal-home").append(html);
			$(".hidden-row").slideDown();
		}

		if (effect == "fade") {
			$("#multipick-cal-home").fadeIn();
		}
		else if (effect == "slideFromRight") {
			// so slide out left!
			$("#multipick-cal-home").show("slide", { direction: "right" }, 400);
		}
		else if (effect == "slideFromLeft") {
			$("#multipick-cal-home").show("slide", { direction: "left" }, 400);
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

		$("#multipick-cal-home").find(".month").css("height", "220px");


		initDayBlockInteractions();
	}


	function initDayBlockInteractions() {
		$(".mpc-day-block").unbind();
		$(".mpc-day-block").each(function(){

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
					$(this).addClass("mpc-day-block-hover");
				}, function(){
					$(this).removeClass("mpc-day-block-hover");
				});

				$(this).click(function(){
					val = $(this).attr("data-dateTime");
					event_id = $(this).attr("data-eventid");
					toggleSelectedDay($(this), val, event_id, !$(this).hasClass("selected-day"));
				});
			}
		})

	}

	/**
	 * Given a DOM element representing a day to select OR the date and event_id
	 * (if the given day is not currently displayed on the calendar) and toggle to
	 * selected or unselected
	 */
	function toggleSelectedDay(dayElement, dateString, event_id, show)
	{
		if (show) {
			$(dayElement).addClass("selected-day");
			$("#selected_days").append('<option selected="selected" id="' + dateString + '" value="' + event_id + '">' + dateString + '</option>');
		} else {
			$(dayElement).removeClass("selected-day");
			$("#" + dateString).remove()
		}

		//Total all of the selected days and change the form text
        updateActionButtonText($("#selected_days").find("option:selected").length);
	}

    /**
     * Given the number of shifts selected, update the button text for the edit/delete button
     * @param shiftCount
     */
    function updateActionButtonText(shiftCount)
    {
        $(".total-selected-days").text(shiftCount);
        $(".total-selected-days-plural").text(shiftCount > 1 ? "s" : "");
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

	function parseDate(str) {
		var mdy = str.split('/')
		return new Date(mdy[2], mdy[0]-1, mdy[1]);
	}

	function daydiff(first, second) {
		return (second-first)/(1000*60*60*24)
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
			returnCal += "<div class='mpc-day-block has-opacity'><div class='day-num'>" + dayNum + "</div></div>";
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
				var dayBlockDate = new Date(dateTime.replace(/-/g, '/'));

				if (dayBlockDate < today) {
					beforeTodayClass = "has-opacity before-today";
					if (getOneDayLater(dayBlockDate) > today) {
						todayClass = "tompc-day-block";
						beforeTodayClass = "";
					}
				}

				var series_day = false;
				var series_event_id = "";
				$("#series_days").find("option").each(function(){
					var date_string = year + "-" + displayMonth + "-" + displayDay;
					if ($(this).text() == date_string) {
						series_day = true;
						series_event_id = $(this).attr("value");
					}

				});


				if (!series_day) {
					beforeTodayClass = "has-opacity";
				}

				returnCal += "<div id='mpc-day-block-" + dateTime + "' class='mpc-day-block active-mpc-day-block " + todayClass + " " + beforeTodayClass + "' data-eventid='" + series_event_id + "' data-dateTime='" + dateTime +"' data-week='" + weekCount + "'>";
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
				returnCal += "<div class='mpc-day-block has-opacity'><div class='day-num'>" + nextMonthCount + "</div></div>";
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

}
