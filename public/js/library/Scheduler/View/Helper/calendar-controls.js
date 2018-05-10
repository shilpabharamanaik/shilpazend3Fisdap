var mouseX;
var mouseY;
var calDisplay_shortMonthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

var calDisplay_longMonthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];

$(function () {

    var calendar = $("#main-content .calendar-display-wrapper");
    var calendarParent = calendar.parent();
    var calendar_controls = $("#main-content .calendar-controls");
    var controlsPos = 0;

    if (calendar.length == 0) {
        setInteractions("day");
    }

    if (calendar_controls.length > 0) {
        controlsPos = calendar_controls.offset().top;
    }

    $(document).mousemove(function (e) {
        mouseX = e.pageX;
        mouseY = e.pageY;
    });

    setZoomEventClick();
    setMonthDetailsEventClick();
    setClosePopup();

    // turn the display options modal div into a modal
    $("#display_options_modal").dialog({
        modal: true,
        autoOpen: false,
        resizable: false,
        width: 510,
        title: "Display options"
    });


    $('#calendar').on('click', '#edit_disply_options', function (e) {
        e.preventDefault();

        var trigger = $(this);
        trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-display-options-modal-throbber'>");

        var display_options_modal = $("#display_options_modal");


        $.post("/scheduler/index/generate-display-options-modal", {},
            function (resp) {
                $("#display_options_modal_content").html($(resp));

                $("#display_options_modal_content").find("input[type='checkbox']").each(function () {
                    $(this).sliderCheckbox({onText: 'On', offText: 'Off'});
                });

                display_options_modal.dialog("open");
                $("#load-display-options-modal-throbber").remove();
                trigger.css("opacity", 1);

                $("#save-display-options-modal").button().blur().click(function (e) {
                    e.preventDefault();
                    var save_trigger = $(this);
                    $("#close-display-options-modal").animate({opacity: 0});
                    save_trigger.animate({opacity: 0}).delay(250).parent().append("<img src='/images/throbber_small.gif' id='save-display-options-modal-throbber'>");

                    $.post("/scheduler/index/save-display-options-modal",

                        {
                            student_names: $("#student_names_slider_checkbox-slider-button").hasClass("on"),
                            instructor_names: $("#instructor_names_slider_checkbox-slider-button").hasClass("on"),
                            preceptor_names: $("#preceptor_names_slider_checkbox-slider-button").hasClass("on"),
                            site_names: $("#site_names_slider_checkbox-slider-button").hasClass("on"),
                            base_names: $("#base_names_slider_checkbox-slider-button").hasClass("on"),
                            weebles: $("#weebles_slider_checkbox-slider-button").hasClass("on"),
                            totals: $("#totals_slider_checkbox-slider-button").hasClass("on")
                        },

                        function (resp) {
                            display_options_modal.dialog('close');
                            loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
                        });

                });

                $("#close-display-options-modal").button().blur().click(function (e) {
                    e.preventDefault();
                    display_options_modal.dialog('close');
                });
            }
        );

    });


    if ($("#shift-details").length > 0) {
        // this is the shift details page
        $(".btn-data").each(function () {
            var event_id = $(this).attr("data-event-id");
            var content = buildActionButtons(event_id, true);
            $("#bottom-buttons-" + event_id).remove();
            $(this).after('<div id="bottom-buttons-' + event_id + '" class="bottom-buttons extra-small gray-button">' + content + '</div>');
            $("#bottom-buttons-" + event_id).find("a").each(function () {
                if (!$(this).hasClass("link-to-skills-tracker")) {
                    $(this).button();
                }
            });
        });
    }

    // next/prev buttons
    $("#main-content .next-prev-buttons button").each(function () {
        $(this).click(function (e) {
            e.preventDefault();

            var type = getViewType();
            var direction = $(this).attr("class").split(" ");
            direction = direction[0];

            if (type != "list") {
                if (type == "month" || type == "month-details") {
                    newDate = getNewDateForMonth(getDate(), direction);
                }
                else if (type == "week") {
                    newDate = incrementDays(getDate(), direction, 7);
                }
                else if (type == "day") {
                    newDate = incrementDays(getDate(), direction, 1);
                }

                loadNewCalendar(type, newDate);
            }

        });
    });

    // today button
    $("#main-content .go-to-today button").click(function (e) {
        e.preventDefault;
        var newDate = new Date();
        loadNewCalendar(getViewType(), newDate);
    });

    $("#main-content button").focus(function () {
        $(this).css("outline", "none");
    });

    // view type buttons
    $("#month-view-type-options button").click(function (e) {
        e.preventDefault();
        var calendar = $("#main-content .calendar-display-wrapper");

        if (!$(this).hasClass("selected")) {

            $("#month-view-type-options .selected").removeClass("selected");
            $(this).addClass("selected");

            var to_type = getMonthType();
            var endDate;

            var curDate = new Date(getYear(), getMonth(), getDay());
            loadNewCalendar(to_type, curDate, endDate);

        }
    });

    $("#main-content .view-type button").click(function (e) {
        e.preventDefault();
        var calendar = $("#main-content .calendar-display-wrapper");

        if (!$(this).hasClass("selected")) {

            var selected_view_type = $("#main-content .view-type .selected");

            var prevType = selected_view_type.text().toLowerCase();

            // update the selected button
            selected_view_type.removeClass("selected");
            $(this).addClass("selected");

            var toType = $(this).text().toLowerCase();
            var newDate;
            var endDate;
            var year;
            var month;
            var day;

            // FROM MONTH
            if (prevType == "month" || prevType == "month-details") {

                // we'll always have the same year/month
                year = getYear();
                month = getMonth();

                // zoom view open?
                if (calendar.find(".opened-zoom-view").length > 0) {
                    day = parseInt(calendar.find(".opened-zoom-view").find(".day-num").text());
                }
                else if (calendar.find(".today").length > 0) {
                    day = parseInt(calendar.find(".today").attr("data-day"));
                }
                else {
                    day = 1;
                }

                if (toType == "list") {
                    day = 1;
                }

                newDate = new Date(year, month, day);

                if (toType == "list") {
                    // we need to provide an end date if we're going to list
                    endDate = getNewDateForMonth(newDate, "next");
                }

            }
            // FROM WEEK
            else if (prevType == "week") {

                // we'll always have the same year/month
                year = getYear();
                month = getMonth();
                var useDateFromAttribs = false;

                // going to month or list?
                // they'll get the same start date
                if (toType == "day") {
                    // is there a selected day?
                    if (calendar.find(".selected-day-block").length > 0) {
                        day = parseInt(calendar.find(".selected-day-block").find(".day-num").text());
                    }
                    else if (calendar.find(".today").length > 0) {
                        day = parseInt(calendar.find(".today").find(".day-num").text());
                    }
                    else {
                        useDateFromAttribs = true;
                    }
                }
                else {
                    useDateFromAttribs = true;
                }

                if (useDateFromAttribs) {
                    day = getDay();
                }

                newDate = new Date(year, month, day);

                if (toType == "list") {
                    endDate = incrementDays(newDate, "next", 7);
                }

            }
            // FROM DAY
            else if (prevType == "day") {
                newDate = new Date(getYear(), getMonth(), getDay());
                if (toType == "list") {
                    endDate = newDate;
                }
            }
            // FROM LIST
            else {
                if (calendar.find(".day-header-fixed").length > 0) {
                    var headerId = calendar.find(".day-header-fixed").attr("id").split("-");
                    year = parseInt(headerId[3]);
                    month = (parseInt(headerId[1])) - 1;
                    day = parseInt(headerId[2]);
                    newDate = new Date(year, month, day);
                }
                else {
                    newDate = new Date(getYear(), getMonth(), getDay());
                }
            }

            var curDate = new Date(getYear(), getMonth(), getDay());

            if (toType == "month") {
                toType = getMonthType();
            }

            loadNewCalendar(toType, newDate, endDate);
        }

    });

    initCalendarModals();
});

var getMonthType = function () {
    var to_type = $("#month-view-type-options").find(".selected").text().toLowerCase();
    if (to_type == "details") {
        to_type = "month-details";
    }
    else {
        to_type = "month";
    }

    return to_type;
}

var getNewDateForMonth;
getNewDateForMonth = function (currentDate, direction) {
    if (direction == "next") {
        var newMonth = currentDate.getMonth() + 1;
    }
    else {
        var newMonth = currentDate.getMonth() - 1;
    }
    return new Date(new Date(currentDate).setMonth(newMonth));
}

var incrementDays;
incrementDays = function (currentDate, direction, numberOfDays) {
    if (direction == "next") {
        var newDay = currentDate.getDate() + numberOfDays;
    }
    else {
        var newDay = currentDate.getDate() - numberOfDays;
    }
    return new Date(new Date(currentDate).setDate(newDay));
}

var sortEvents;
sortEvents = function (collection, eventClass, contentClass) {

    collection.each(function () {
        var events = $(this).find(eventClass);

        events.sort(function (a, b) {

            // convert to integers from strings
            a = $(a).attr("data-sortby");
            b = $(b).attr("data-sortby");

            // compare
            if (a > b) {
                return 1;
            } else if (a < b) {
                return -1;
            } else {
                return 0;
            }
        });

        $(this).find(eventClass).remove();
        $(this).find(contentClass).append(events);
    });

}

var setInteractions;
setInteractions = function (type) {

    if ($.browser.mozilla) {
        $("#main-content .day-name").css("width", "13.3%").css("margin-right", "0.5%");
    }

    /**
     * Check for a specific major version of Safari.
     *
     * This can actually be used to check minor versions
     * of Safari as well if you pass in a string like '7.0'
     *
     * You should reuse this multiple times, once for each major
     * version. You can chain them together to form whatever custom
     * logic you need.
     *
     * This is better than a simple indexOf because it allows
     * us to check for the Safari/ stub beforehand to make sure
     * that engine versions, webkit versions or build versions
     * don't give us false positives.
     */
    function safariVersionStartsWith(startsWith) {
        return !!navigator.userAgent.match(' Safari/') && !navigator.userAgent.match(' Chrom') && !navigator.userAgent.match('Android') && !!navigator.userAgent.match(' Version/' + startsWith + '.');
    }

    // Make width adjustments for older Safari versions
    // that always round down when they get decimal widths.
    //
    // Newer versions implement proper rounding methods
    if (navigator.userAgent.indexOf('Safari') != -1 &&
        (safariVersionStartsWith('4') ||
        safariVersionStartsWith('5') ||
        safariVersionStartsWith('6') ||
        safariVersionStartsWith('7.0') ) &&
        navigator.userAgent.indexOf('Chrom') == -1 &&
        navigator.userAgent.indexOf('Android') == -1) {
        $("#main-content .day-block").css("width", "8.56em");
    }

    // get everything back to the top
    var cal_controls = $("#cal-island .calendar-controls");
    $('html,body').animate({scrollTop: 0}, 'slow');
    cal_controls.removeClass("fixed");

    var controlsPos = 0;
    if (cal_controls.length > 0) {
        controlsPos = cal_controls.offset().top;
    }

    $(".no-hover").hover(function () {
            $(this).css("background-color", "#fff");
        },
        function () {
            $(this).css("background-color", "#fff");
        });

    if (type == "week") {
        var weekTop = cal_controls.position().top + 45;
        setWeekPicker();
    }

    setShiftBarTooltips();

    if ($(".broken-too-many-shifts-message").length > 0) {

    }
    else {
        setControlsLock(controlsPos, type, weekTop);
    }

    if (type == "list") {
        toggleControlBtns("hide");
    }
    else {
        toggleControlBtns("show");
    }

    if (type == "month") {
        setDayClickEventMonth();
        setEventClickMonth();
        setMonthPicker();
    }
    else if (type == "month-details" || type == "detailsmonth") {
        /*
         $("#main-content .day-header-list").each(function(){
         daysPos[$(this).attr("id")] = $(this).offset().top;
         });

         setDaysAndTotalsStick(daysPos, cal_controls.position().top+45);*/
        setEventClickMonthDetails();
        setMonthPicker();
    }
    else if (type == "week") {
        var cal_wrapper_week = $("#main-content").find(".cal-wrapper-week");

        // adjust the calendar display wrapper's top margin to account for the absolutely positioned controls
        cal_wrapper_week.css("top", weekTop);
        var topOffset = 30;
        var topMargin = cal_wrapper_week.height() + topOffset;
        if ($(".student-presets").length > 0) {
            topOffset = 115;
        }
        $("#main-content .calendar-display-wrapper").css("margin-top", topMargin + "px");
        adjustEventHeight();
        setEventClickWeek();
        setDayClickEventWeek(topMargin);
    }

    else if (type == "day") {

        adjustSlotTablesHeight();
        setDayPicker();
    }

    else if (type == "list") {
        setEventClickList();
        adjustListWeebles();
        setListDatePickersChange();
        var daysPos = {};

        $("#main-content .day-header-list").each(function () {
            daysPos[$(this).attr("id")] = $(this).offset().top;
        });

        setDayNameStick(daysPos, cal_controls.offset().top);
    }

    $('#main-content').on('click', '.clickable-event-title', function (e) {
        e.preventDefault();

        var event_id = $(this).attr("data-eventid");
        var special_icons = $(this).parent().find(".special-icon");

        if (special_icons.find(".student-created-icon").length > 0 || special_icons.find(".instructor-created-icon").length > 0) {
            //Scheduler should no longer be using this link, but I'm leaving it here for the moment in case mgmt changes their minds...
            // it's a quick add!
            window.location = "/skills-tracker/shifts/my-shift/shiftId/" + event_id;
        }
        else {
            window.location = "/scheduler/shift/details/event/" + event_id;
        }
    });

    setWeebleHover();
    setIconsHover();
    setUpBottomButtons();
    setQuickAddEditListener();

    $(".open-weeble-img").each(function () {
        if ($(this).attr("src").indexOf("invisible") != -1) {
            if (getViewType() != "month-details") {
                $(this).css("margin-right", "0em");
            }
        }
    });

}

var setListDatePickersChange;
setListDatePickersChange = function () {
    $('#endDate').keydown(function () {
        //code to not allow any changes to be made to input field
        return false;
    }).change(function () {
        $("#list-go-btn").fadeIn();
    });

    $('#startDate').keydown(function () {
        //code to not allow any changes to be made to input field
        return false;
    }).change(function () {
        $("#list-go-btn").fadeIn();
    });

    $("#list-go-btn").click(function (e) {
        e.preventDefault();
        loadNewCalendar("list", new Date($("#startDate").val()), new Date($("#endDate").val()));
    });
}

var adjustListWeebles;
adjustListWeebles = function () {

    /*
     $("#main-content .weebles-positioner").each(function(){
     // the styling will struggle a bit - help it out
     width = 0;
     $(this).find("img").each(function(){
     width = width + $(this).width();

     });

     $(this).find(".plus-slots").each(function(){
     width = width + $(this).width();
     });

     $(this).find(".spacer").each(function(){
     width = width + $(this).width() + 5;
     });

     $(this).css("width", (width+6) + "px");

     });*/
}

var toggleControlBtns;
toggleControlBtns = function (state) {
    var opacity = "block";
    if (state == "hide") {
        opacity = "none";
    }
    $("#main-content").find(".next-prev-buttons").css("display", opacity);
    $("#main-content").find(".go-to-today").css("display", opacity);
    $("#edit-icon").css("display", opacity);
}

var adjustSlotTablesHeight;
adjustSlotTablesHeight = function () {

    $("#main-content .day-event").each(function () {
        var targetHeight = ($(this).find(".day-event-columns-wrapper").height()) - 12;
        var filledRows = $(this).find(".filled-slot-col").find("tr");
        var openRows = $(this).find(".open-slot-col").find("tr");

        alterDayRowHeight(filledRows, targetHeight);
        alterDayRowHeight(openRows, targetHeight);
    });

}

var alterDayRowHeight;
alterDayRowHeight = function (rows, targetHeight) {
    var count = 0;
    rows.each(function () {
        count++;
    });
    var rowHeight = parseInt(targetHeight) / parseInt(count);

    rows.each(function () {
        $(this).css("height", rowHeight + "px");
    });
}

var setDayNameStick;
setDayNameStick = function ($daysPos, controlsPos) {

    $(window).scroll(function () {

        var pagePos = $(document).scrollTop();
        var someone = false;

        // step through days pos and figure out where we are each scroll
        $.each($daysPos, function (id, pos) {
            var posToCheck = pagePos + 160;
            if (posToCheck >= pos) {
                someone = true;
                var header = $("#" + id);
                if (!header.hasClass("day-header-fixed")) {
                    $("#calendar").find(".day-header-fixed").removeClass("day-header-fixed");
                    $("#calendar").find(".first-day-header-fixed").removeClass("first-day-header-fixed");

                    header.addClass("day-header-fixed");

                    if (header.hasClass("small-top-margin")) {
                        header.addClass("first-day-header-fixed");
                    }

                }
            }
        });

        if (!someone || !$("#cal-island").find(".calendar-controls").hasClass("fixed")) {
            $("#calendar").find(".day-header-fixed").removeClass("day-header-fixed");
            $("#calendar").find(".first-day-header-fixed").removeClass("first-day-header-fixed");
        }

    });
}

var setControlsLock;
setControlsLock = function (controlsPos, type, weekTop) {

    var daysOfTheWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    if (type == "week") {
        var daysPos = {};
        for (i = 0; i < 7; i++) {
            daysPos[i] = ($("#header-" + daysOfTheWeek[i]).position().top) - 100;
        }
    }

    if (type == "month-details") {
        var week_day_totals_pos = {};
        for (i = 1; i < 6; i++) {
            // Because most Februarys will have only 4 weeks and not 5, we should make sure the week
            // exists before trying to get its offset
            if ($(".month_details_day_totals_week_" + i).length) {
                week_day_totals_pos[i] = $(".month_details_day_totals_week_" + i).offset().top;
            }
        }
    }

    $(window).scroll(function () {
        var pos = $(document).scrollTop();
        var cal_controls = $("#cal-island").find(".calendar-controls");
        var cal_wrapper_week = $("#cal-island").find(".cal-wrapper-week");

        if (pos >= controlsPos) {
            // make sure the controls have the "fixed" class
            cal_controls.addClass("fixed");

            if (type == "week") {
                cal_wrapper_week.addClass("fixed").css("top", "3.1em");
            }

            if (type == "month-details") {

                $("#month_details_day_names").addClass("month_details_day_names_fixed");

                $(".month_details_day_totals_fixed").removeClass("month_details_day_totals_fixed");

                var day_totals_to_fix;

                $.each(week_day_totals_pos, function (i, v) {

                    if (pos >= v - 186) {
                        day_totals_to_fix = $(".month_details_day_totals_week_" + i);
                    }

                });

                if (day_totals_to_fix) {
                    day_totals_to_fix.addClass("month_details_day_totals_fixed");
                }

            }


        }
        else {
            // remove the fixed class
            cal_controls.removeClass("fixed");

            if (type == "week") {
                cal_wrapper_week.removeClass("fixed").css("top", weekTop);
            }

            if (type == "month-details") {
                $("#month_details_day_names").removeClass("month_details_day_names_fixed");
                $(".month_details_day_totals_fixed").removeClass("month_details_day_totals_fixed");
            }

        }

        if (type == "week") {
            // if the position is within a 'day list', select that day
            var dayList = "Sunday";
            for (i = 0; i < 7; i++) {
                if (pos >= daysPos[i]) {
                    dayList = (daysOfTheWeek[i]);
                }
            }

            deselectDay();
            $("#calendar").find(".day-block").each(function () {
                if ($(this).attr("data-day") == dayList) {
                    $(this).addClass("selected-day-block");
                }
            });
        }
    });
}

var adjustEventHeight;
adjustEventHeight = function () {

    $("#calendar").find(".week-event-view").each(function () {

        weekEvents = $(this).find(".week-event");
        tallest = parseInt(weekEvents.height());
        var count = 0;

        weekEvents.each(function () {
            count++;
            if (!(count % 3)) {
                $(this).after("<div class='clear'></div>").addClass("right-border");
            }
            thisHeight = parseInt($(this).height());
            if (thisHeight > tallest) {
                tallest = thisHeight;
            }
        });

        weekEvents.last().css("border-bottom", "1px solid #ccc").addClass("right-border");

        if (weekEvents.last().prev().hasClass("week-event")) {
            weekEvents.last().prev().css("border-bottom", "1px solid #ccc");
        }

        if (weekEvents.last().prev().prev().hasClass("week-event")) {
            weekEvents.last().prev().prev().css("border-bottom", "1px solid #ccc");
        }

        if (weekEvents.last().prev().prev().prev().hasClass("week-event")) {
            weekEvents.last().prev().prev().prev().css("border-bottom", "1px solid #ccc");
        }

        weekEvents.css("height", tallest + "px");
    });

}

var setTitle;
setTitle = function () {

    $(".cal-control-title").unbind();
    var oText = $(".cal-control-title").text();
    var cal_parent = $(".cal-control-title").parent();
    cal_parent.empty();
    cal_parent.append("<h3 class='cal-control-title'>" + oText + "</h3>");

    titleHeader = $(".calendar-controls").find("h3");

    if ($(".calendar-display-wrapper").attr("data-title") == "List") {
        // add date pickers instead of the usual here
        titleHeader.addClass("header-with-date-pickers").text("").html($("#date-pickers-for-title").html());
        $("#date-pickers-for-title").remove();
        $("#list-go-btn").hide();

        $("#startDate").datepicker({
            onClose: function (selectedDate) {
                $("#endDate").datepicker("option", "minDate", selectedDate);
            }
        }).datepicker("option", "maxDate", $("#endDate").val());

        $("#endDate").datepicker({
            onClose: function (selectedDate) {
                $("#startDate").datepicker("option", "maxDate", selectedDate);
            }
        }).datepicker("option", "minDate", $("#startDate").val());
    }
    else {
        titleHeader.removeClass("header-with-date-pickers").text($(".calendar-display-wrapper").attr("data-title"));
    }
}

var getTooltip;
getTooltip = function (id, description) {
    return '<div class="cal-tooltip" id="' + id + '">' + description + '</div>';
}

var setShiftBarTooltips;
setShiftBarTooltips = function () {
    $("#main-content").find(".shift-bar").each(function () {
        var toolTipId = $(this).attr("data-eventId") + "-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("data-description"));
        setTooltipAction($(this), toolTip, toolTipId, 800);
    });
}

var setWeebleHover;
setWeebleHover = function () {
    $("#main-content").find(".closed-weeble-img").each(function () {
        var toolTipId = $(this).attr("data-closedSlot") + "-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("alt"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".red-weeble-img").each(function () {
        var toolTipId = $(this).attr("data-closedSlot") + "-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("alt"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".open-weeble-img").each(function () {
        var toolTipId = $(this).attr("data-openSlot") + "-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("alt"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".plus-slots").each(function () {
        var toolTipId = "plus-slot-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("data-tooltip"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });
}

var setIconsHover;
setIconsHover = function () {
    $("#main-content").find(".link-to-skills-tracker").each(function () {
        var toolTipId = $(this).attr("data-assignmentId") + "st-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("data-tooltip"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".slot-table-drop-icon").each(function () {
        var toolTipId = $(this).attr("data-assignmentId") + "drop-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("data-tooltip"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".special-icon").find("img").each(function () {
        var toolTipId = $(this).attr("class") + "-tooltip";
        var toolTip = getTooltip(toolTipId, $(this).attr("data-tooltip"));
        setTooltipAction($(this), toolTip, toolTipId, 200);
    });

    $("#main-content").find(".list-event").find(".event-title").find("img").each(function () {
        if ($(this).attr("data-tooltip")) {
            var toolTipId = $(this).attr("class") + "-tooltip";
            var toolTip = getTooltip(toolTipId, $(this).attr("data-tooltip"));
            setTooltipAction($(this), toolTip, toolTipId, 200);
        }
    });
}

var setTooltipAction;
setTooltipAction = function (trigger, toolTip, id, delay) {
    trigger.hover(function () {
        $("#main-content").append(toolTip);
        $("#" + id).css({"top": mouseY, "left": (mouseX + 20), "z-index": 2000}).delay(delay).fadeIn();
    }, function () {
        $("#" + id).fadeOut().remove();
    });
}

var setZoomViewNav;
setZoomViewNav = function () {
    // for both next/prev
    openNeighborDay($(".next-zoom-view"), 1);
    openNeighborDay($(".prev-zoom-view"), -1);
}

var openNeighborDay;
openNeighborDay = function (trigger, direction) {
    trigger.click(function (e) {
        e.preventDefault();

        var curDay = parseInt($(this).parent().find(".day-num").text());
        var newDay = curDay + direction;
        openZoomView($("#day-block-" + newDay));
    });
}

var setDayClickEventMonth;
setDayClickEventMonth = function () {

    setCloseZoomView();
    setZoomViewNav();

    $("#main-content").find(".day-block").click(function () {
        openZoomView($(this));
    });
}

var setDayClickEventWeek;
setDayClickEventWeek = function (topMargin) {

    $("#main-content").find(".day-block").click(function () {
        var dayId = "header-" + $(this).attr("data-day");
        var top = $("#" + dayId).offset().top;
        top = (top - topMargin) - 50;
        $('html,body').animate({scrollTop: top}, 'slow');

        deselectDay();
        $(this).addClass("selected-day-block");
    });

}

var deselectDay;
deselectDay = function () {
    $("#main-content").find(".selected-day-block").removeClass("selected-day-block");
}

var inView;
inView = function (element, view) {
    var result = false;
    if (element.parents(view).length > 0) {
        result = true;
    }
    return result;
}

var getPopupTopPos;
getPopupTopPos = function (top, popup, vertialClass) {
    var tickerOffset = 62;
    var newTop = top;
    // adjust the positioning so the ticker is actually pointing to hte event
    if (vertialClass == "bottom") {
        newTop = top - (popup.height() - tickerOffset);
    }
    else {
        newTop = (top - tickerOffset) + 17;
    }
    return newTop;
}

var getPopupLeftPos;
getPopupLeftPos = function (left, popup, eventTrigger) {
    var newLeft = left;
    if (horizontalClass == "left") {
        newLeft = left + eventTrigger.width();
    }
    else {
        newLeft = left - popup.width() - 14;
    }
    return newLeft;
}

var setEventClickList;
setEventClickList = function () {
    $("#main-content").find(".list-event").each(function () {
        var eventId = $(this).attr("data-eventid");
        var detailsPopup = $("#event-details-popup-" + eventId);

        $(this).click(function (e) {
            e.preventDefault();
            var popup = $("#event-details-popup-" + eventId);
            addStandardPopupDomElements(eventId);

            if (popup.css("display") == "none") {
                closeEventDetailsPopup();
                var ticker = popup.find(".details-ticker");
                var top = $(this).position().top;
                var left = $(this).position().left;
                popup.css("top", top).css("left", (left - 150));
                vertialClass = "top";
                horizontalClass = "left";
                ticker.removeClass("bottom-ticker").removeClass("top-ticker").removeClass("left-ticker").removeClass("right-ticker");
                ticker.addClass(vertialClass + "-ticker").addClass(horizontalClass + "-ticker");
                left = ($(this).find(".event-title").offset().left + $(this).find(".event-title").width()) - 150;
                buildActionButtons(eventId);
                popup.css("top", (getPopupTopPos(top, popup, vertialClass) + 15)).css("left", left).fadeIn();
            }

        });
    });

    setUpEventDetailsPopup("list-event");
}

var setDayPicker;
setDayPicker = function () {
    $("#edit-icon").unbind().css({'left': '', 'opacity': 1});

    var defaultText = $(".cal-control-title").text();
    $(".cal-control-title").text("").append("<h3 class='day-control-title'>" + defaultText + "</h3>");

    if ($('.day-picker').length == 0) {
        $(".cal-control-title").parent().append("<div class='day-picker'></div>");
    }
    $('.day-picker').hide();

    $(".day-control-title, #edit-icon").click(function () {
        $('.day-picker').fadeIn();
    });

    $('.day-picker').datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        onSelect: function (dateText, inst) {
            var date = $(this).datepicker('getDate');
            newDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            var dateFormat = "M d, yy";
            // new text
            var startDateTxt = $.datepicker.formatDate(dateFormat, newDay, inst.settings);
            var newText = startDateTxt;

            $(".day-control-title").text(newText);
            if ($("#day-go-btn").length == 0) {
                $(".cal-control-title").append("<a href='#' id='day-go-btn' class='first last'>Go</a>");
                $("#edit-icon").css('left', '-6.65em');
                $("#day-go-btn").click(function (e) {
                    var newDayDate = new Date(startDateTxt);
                    loadNewCalendar("day", newDayDate, getEndDate(), getFilters());
                    $(this).remove();
                    $("#edit-icon").css('left', '');
                });
            }
            $('.day-picker').fadeOut();
        }
    });

    $("body").click(function (e) {
        var in_picker = false;
        $(e.target).parents().each(function () {
            if ($(this).hasClass("day-picker")) {
                in_picker = true;
                return false;
            }
            return true;
        });

        if ($(e.target).attr("id") == 'edit-icon') {
            in_picker = true;
        }

        if (!in_picker) {
            if ($(e.target).hasClass("ui-corner-all")) {
                in_picker = true;
            }

            if (!in_picker) {
                if ($(e.target).hasClass("ui-icon")) {
                    in_picker = true;
                }
                if (!in_picker) {
                    if ($(e.target).hasClass("day-control-title")) {
                        in_picker = true;
                    }
                }
            }
        }

        if (!in_picker) {
            $('.day-picker').fadeOut();
        }
    });
}

var setMonthPicker;
setMonthPicker = function () {
    $(".cal-control-title").unbind();
    $("#edit-icon").unbind().css({'left': '', 'opacity': 1});
    var oText = $(".cal-control-title").text();

    $(".cal-control-title").parent().empty().append("<h3 class='cal-control-title'>" + oText + "</h3>");

    if ($(".month-picker").length <= 0) {
        $(".month-picker").remove().unbind();
        $("#month_picker_month").remove().unbind();
        $("#month_picker_year").remove().unbind();
    }

    $(".cal-control-title").attr("data-month", getMonth()).attr("data-year", getYear());

    $(".cal-control-title").hover(function () {
        $(this).css("cursor", "pointer");
    }, function () {
        $(this).css("cursor", "default");
    });

    var year = getYear();
    var year_options = [];
    for (i = 0; i < 6; i++) {
        var new_past_year = year - i;
        var new_future_year = year + i;
        year_options[new_past_year] = new_past_year;
        year_options[new_future_year] = new_future_year;
    }

    var year_picker_select = "<select id='month_picker_year' style='width:85px;' class='chzn-select' name='month_picker_year'>";
    $.each(year_options, function (i, v) {
        if (v === undefined) {
        }
        else {
            var selected = "";
            if (i == $(".cal-control-title").attr("data-year")) {
                selected = "selected='selected'";
            }
            year_picker_select += "<option value='" + i + "' " + selected + ">" + v + "</option>";
        }
    });
    year_picker_select += "</select>";

    var month_picker_select = "<select id='month_picker_month' style='width:85px;' class='chzn-select' name='month_picker_month'>";

    $.each(calDisplay_shortMonthNames, function (i, v) {
        var selected = "";
        if (i == $(".cal-control-title").attr("data-month")) {
            selected = "selected='selected'";
        }
        month_picker_select += "<option value='" + i + "' " + selected + ">" + v + "</option>";
    });

    month_picker_select += "</select>";

    $(".cal-control-title").parent().append("<div class='month-picker'>" + month_picker_select + " " + year_picker_select + "</div>");
    $(".month-picker").hide();
    $("#edit-icon").css("opacity", 1);
    $("#month_picker_month").removeClass("chzn-done").chosen().change(function () {
        addMonthGo();
    });
    $("#month_picker_year").removeClass("chzn-done").chosen().change(function () {
        addMonthGo();
    });

    // sometimes chosen sucks
    $("#month_picker_month_chzn").find(".chzn-drop").css("width", "83px");
    $("#month_picker_year_chzn").find(".chzn-drop").css("width", "83px");
    $("#month_picker_month_chzn").find(".chzn-search").find("input").css("width", "48px");
    $("#month_picker_year_chzn").find(".chzn-search").find("input").css("width", "48px");

    $(".cal-control-title, #edit-icon").click(function (e) {
        e.preventDefault();
        $(".month-picker").fadeIn();
        $("#edit-icon").css("opacity", 0);
    });
}

var addMonthGo;
addMonthGo = function () {
    if ($("#month-go-btn").length == 0) {
        $(".cal-control-title").append("<a href='#' id='month-go-btn' class='first last'>Go</a>");
        $("#month-go-btn").click(function (e) {
            var newDayDate = new Date((parseInt($("#month_picker_month").val()) + 1) + "/01/" + $("#month_picker_year").val());
            loadNewCalendar(getViewType(), newDayDate, getEndDate(), getFilters());
            $(this).remove();
        });
    }
}

var setWeekPicker;
setWeekPicker = function () {
    $("#edit-icon").unbind().css({'left': '', 'opacity': 1});
    var defaultText = $(".cal-control-title").text();
    $(".cal-control-title").text("").append("<h3 class='week-control-title'>" + defaultText + "</h3>").parent().append("<div class='week-picker'></div>");
    $('.week-picker').hide();

    $(".week-control-title, #edit-icon").click(function () {
        $('.week-picker').fadeIn();
    });

    var wpStartDate;
    var wpEndDate;

    var selectCurrentWeek = function () {
        window.setTimeout(function () {
            $('.week-picker').find('.ui-datepicker-current-day a').addClass('ui-state-active')
        }, 1);
    }

    selectCurrentWeek();

    $('.week-picker').datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        onSelect: function (dateText, inst) {
            var date = $(this).datepicker('getDate');
            wpStartDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay());
            wpEndDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 6);
            var dateFormat = "M d, yy";
            // new text
            var startDateTxt = $.datepicker.formatDate(dateFormat, wpStartDate, inst.settings);
            var newText = startDateTxt + " - " + $.datepicker.formatDate(dateFormat, wpEndDate, inst.settings);

            $(".week-control-title").text(newText);

            if ($("#week-go-btn").length == 0) {
                $(".cal-control-title").append("<a href='#' id='week-go-btn' class='first last'>Go</a>");
                $("#edit-icon").css('left', '-6.7em');
                $("#week-go-btn").click(function (e) {
                    var newDayDate = new Date(startDateTxt);
                    loadNewCalendar("week", newDayDate, getEndDate(), getFilters());
                    $(this).remove();
                    $("#edit-icon").css('left', '');
                });
            }


            selectCurrentWeek();
            $('.week-picker').fadeOut();
        },
        beforeShowDay: function (date) {
            var cssClass = '';
            if (date >= wpStartDate && date <= wpEndDate)
                cssClass = 'ui-datepicker-current-day';
            return [true, cssClass];
        },
        onChangeMonthYear: function (year, month, inst) {
            selectCurrentWeek();
        }
    });

    $("html").click(function (e) {
        var in_picker = false;
        $(e.target).parents().each(function () {
            if ($(this).hasClass("week-picker")) {
                in_picker = true;
                return false;
            }
            return true;
        });

        if ($(e.target).hasClass("week-control-title")) {
            in_picker = true;
        }

        if ($(e.target).hasClass("ui-corner-all")) {
            in_picker = true;
        }

        if ($(e.target).hasClass("ui-icon")) {
            in_picker = true;
        }

        if ($(e.target).attr("id") == 'edit-icon') {
            in_picker = true;
        }

        if (!in_picker) {
            $('.week-picker').fadeOut();
        }
    });

    $('.week-picker .ui-datepicker-calendar tr').live('mousemove', function () {
        $(this).find('td a').addClass('ui-state-hover');
    });
    $('.week-picker .ui-datepicker-calendar tr').live('mouseleave', function () {
        $(this).find('td a').removeClass('ui-state-hover');
    });
}

var setEventClickWeek;
setEventClickWeek = function () {
    $("#calendar").find(".week-event").unbind().each(function () {
        var eventId = $(this).attr("data-eventid");

        $(this).click(function (e) {
            e.preventDefault();

            closeEventDetailsPopup();
            addStandardPopupDomElements(eventId);

            var popup = $("#event-details-popup-" + eventId);
            var ticker = popup.find(".details-ticker");
            var top = $(this).position().top;
            var left = $(this).position().left;

            // defaults
            popup.css("top", top).css("left", left);
            vertialClass = "top";
            horizontalClass = "left";

            $(this).addClass("selected-event");

            // if we're any where else, just make sure we aren't cutting it off
            if ((popup.height() + top) > getBottomOfDivPos($("#cal-island"))) {
                vertialClass = "bottom";
            }

            // determine left/right classes based on right/left col
            if ($(this).next().hasClass("clear")) {
                horizontalClass = "right";
            }

            ticker.removeClass("bottom-ticker").removeClass("top-ticker").removeClass("left-ticker").removeClass("right-ticker").addClass(vertialClass + "-ticker").addClass(horizontalClass + "-ticker");
            popup.css("top", (getPopupTopPos(top, popup, vertialClass) + 15)).css("left", (getPopupLeftPos(left, popup, $(this)) + 8)).fadeIn();

            if (popup.offset().left > 575) {
                popup.css("left", (popup.offset().left - 70));
            }

            buildActionButtons(eventId);

        });

    });

    setUpEventDetailsPopup("week-event");

}

var setUpEventDetailsHolder;
setUpEventDetailsHolder = function () {
    // things are going to get a little wonky here
    // in order to minimize the amount of times we loop through our events
    // their event details div is not in an ideal location (position is limited)
    // so we're going to move these guys after the page as loaded
    if ($("#event-details-holder").length > 0) {
        $("#event-details-holder").remove();
    }
    $("#scheduler-page-title").append("<div id='event-details-holder'></div>");
}

var moveDetailsPopup;
moveDetailsPopup = function (eventId, detailsPopup) {
    // move the details popup so we can handle its positioning better
    $("#event-details-holder").append("<div class='event-details-popup' id='event-details-" + eventId + "'>" + detailsPopup.html() + "</div>");
    detailsPopup.remove();
}


var setMonthDetailsEventClick = function () {

    $('#calendar').on('click', '.month-details-event', function (e) {
        e.preventDefault();
        closeEventDetailsPopup();
        $(".selected-month-details-event").removeClass("selected-month-details-event");
        $(this).addClass("selected-month-details-event");
        addStandardPopupDomElements($(this).attr("data-eventid"));

        var popup = $("#event-details-" + $(this).attr("data-eventid"));
        var ticker = popup.find(".details-ticker");
        var week = $(this).parents(".week-row").attr("data-weekcount");
        var top = $(this).offset().top;
        var left = $(this).offset().left;

        // defaults
        popup.css("top", top).css("left", left);
        vertialClass = "top";
        horizontalClass = "right";

        // if we're in one of the last two weeks, just put the ticker at the bottom regardless (looks nicer)
        if (week >= getTopGrowthCutOff()) {
            vertialClass = "bottom";
        }

        // if we're any where else, just make sure we aren't cutting it off
        if ((popup.height() + top) > getBottomOfDivPos($("#cal-island"))) {
            vertialClass = "bottom";
        }

        // determine left/right classes based on day of week
        var day = parseInt($(this).parent().parent().parent().attr("data-weekday"));
        if (day < 4) {
            horizontalClass = "left";
        }

        var newLeft = getPopupLeftPos(left, popup, $(this));

        // adjust the left for tuesday events
        if (day == 3) {
            newLeft = newLeft;
        }

        buildActionButtons($(this).attr("data-eventid"));

        ticker.removeClass("bottom-ticker").removeClass("top-ticker").removeClass("left-ticker").removeClass("right-ticker").addClass(vertialClass + "-ticker").addClass(horizontalClass + "-ticker");
        var newest_top = getPopupTopPos(top, popup, vertialClass);
        newest_top = newest_top + 4;

        if (horizontalClass == "right") {
            newLeft = newLeft + 5;
        }

        popup.css("top", newest_top).css("left", newLeft).fadeIn();
    });
}

var setZoomEventClick;
setZoomEventClick = function () {

    $('#calendar').on('click', '.zoom-event', function (e) {
        e.preventDefault();
        closeEventDetailsPopup();

        addStandardPopupDomElements($(this).attr("data-eventid"));

        var popup = $("#event-details-" + $(this).attr("data-eventid"));
        var ticker = popup.find(".details-ticker");
        var week = $(this).parents(".week-row").attr("data-weekcount");
        var top = $(this).offset().top;
        var left = $(this).offset().left;

        // defaults
        popup.css("top", top).css("left", left);
        vertialClass = "top";
        horizontalClass = "right";

        // if we're in one of the last two weeks, just put the ticker at the bottom regardless (looks nicer)
        if (week >= getTopGrowthCutOff()) {
            vertialClass = "bottom";
        }

        // if we're any where else, just make sure we aren't cutting it off
        if ((popup.height() + top) > getBottomOfDivPos($("#cal-island"))) {
            vertialClass = "bottom";
        }

        // determine left/right classes based on day of week
        var day = parseInt($(this).parent().parent().parent().attr("data-weekday"));
        if (day < 4) {
            horizontalClass = "left";
        }

        var newLeft = getPopupLeftPos(left, popup, $(this));

        // adjust the left for tuesday events
        if (day == 3) {
            newLeft = newLeft - 95;
        }

        buildActionButtons($(this).attr("data-eventid"));

        ticker.removeClass("bottom-ticker").removeClass("top-ticker").removeClass("left-ticker").removeClass("right-ticker").addClass(vertialClass + "-ticker").addClass(horizontalClass + "-ticker");
        popup.css("top", getPopupTopPos(top, popup, vertialClass)).css("left", newLeft).fadeIn();
    });
}

var setEventClickMonthDetails = function () {

    setUpEventDetailsHolder();

    $('#calendar').find('.month-details-event').each(function () {
        var eventId = $(this).attr("data-eventid");
        moveDetailsPopup(eventId, $("#event-details-popup-" + eventId));
    });

    setUpEventDetailsPopup("month-details-event");
}

var setEventClickMonth;
setEventClickMonth = function () {

    setUpEventDetailsHolder();

    $('#calendar').find('.zoom-event').each(function () {
        var eventId = $(this).attr("data-eventid");
        moveDetailsPopup(eventId, $("#event-details-popup-" + eventId));
    });

    setUpEventDetailsPopup("zoom-event");

}

var setUpEventDetailsPopup;
setUpEventDetailsPopup = function (triggerClass) {
    popupTrigger(triggerClass);
}

var setUpBottomButtons;
setUpBottomButtons = function () {


    $(".bottom-buttons").find("a").each(function () {
        if (!$(this).hasClass("link-to-skills-tracker")) {
            $(this).button().blur();
        }
    });
}

var popupTrigger;
popupTrigger = function (popupTrigger) {

    $('body').unbind().click(function (e) {
        var target = e.target;
        if (!inView($(target), ".event-details-popup") && !inView($(target), ".ui-dialog")) {
            if (!$(target).hasClass(popupTrigger) && ($(target).parents("." + popupTrigger).length == 0)) {
                closeEventDetailsPopup();
            }
        }
    });
}

var setClosePopup;
setClosePopup = function () {
    $('#main-content').on('click', '.close-details-popup', function (e) {
        e.preventDefault();
        closeEventDetailsPopup();
    });
}

var getBottomOfDivPos;
getBottomOfDivPos = function (subject) {
    val = subject.height() + subject.offset().top;
    return val;
}

var closeEventDetailsPopup;
closeEventDetailsPopup = function () {
    $(".selected-event").removeClass("selected-event");
    $(".selected-month-details-event").removeClass("selected-month-details-event");
    $(".event-details-popup").fadeOut("fast");
}

var getTopGrowthCutOff;
getTopGrowthCutOff = function () {
    var numberOfWeeks = parseInt($(".week-row").last().attr("data-weekcount"));
    return numberOfWeeks - 1;
}

var openZoomView;
openZoomView = function (dayBlock) {
    var topGrowthCutOff = getTopGrowthCutOff();
    var numberOfDays = 28;

    $(".week-row").last().find(".day-block").each(function () {
        if (!$(this).hasClass("has-opacity")) {
            numberOfDays = parseInt($(this).attr("data-day"));
        }
    });

    var zoomView = $("#zoom-view-" + dayBlock.attr("data-day"));
    var zoomViewContent = zoomView.find(".zoom-view-content");
    var closeTrigger = zoomView.find(".close-zoom-view");
    var nextZoom = zoomView.find(".next-zoom-view");
    var prevZoom = zoomView.find(".prev-zoom-view");
    var totalsZoom = zoomView.find(".event-totals-wrapper");
    var weekdayName = zoomView.find(".weekday-name");
    var todayImg = zoomView.find(".today-img");

    if (zoomView.css("display") != "block") {

        closeZoomView();

        var day = parseInt(dayBlock.attr("data-day"));
        var weekday = parseInt(dayBlock.attr("data-weekday"));
        var week = parseInt(dayBlock.parent().attr("data-weekcount"));
        var thursdayFriday = false;

        if (weekday == 6 || weekday == 7) {
            thursdayFriday = true;
        }

        if (week < topGrowthCutOff) {
            // this day is not in the last 2 rows (we'll grow the div from the bottom)
            if (thursdayFriday) {
                // we're either a thursday or a friday (grow from the bottom-left)
                zoomView.css("top", "0px").css("right", "0px");
            }
            else {
                // we are not a thursday or a friday (grow from bottom right) (most typical case)
                zoomView.css("top", "0px");
            }

        }
        else {
            // we're in the last 2 rows (we'll have to grow the div from the top)
            if (thursdayFriday) {
                // we're either a thursday or a friday (grow from the top-left)
                zoomView.css("bottom", "0px").css("right", "0px");
            }
            else {
                // we are not a thursday or a friday (grow from top right)
                zoomView.css("bottom", "0px").css("left", "0px");
            }
        }

        zoomView.show().animate({
            width: "377px",
            height: "283px"
        }, {easing: "easeInQuint"}).addClass("opened-zoom-view");
        zoomViewContent.hide();
        closeTrigger.hide();
        nextZoom.hide();
        prevZoom.hide();
        totalsZoom.hide();
        weekdayName.hide();
        todayImg.hide();
        zoomViewContent.delay(500).fadeIn();
        closeTrigger.delay(500).fadeIn();
        totalsZoom.delay(500).fadeIn();
        weekdayName.delay(500).fadeIn();
        todayImg.delay(500).fadeIn();

        if (day != 1) {
            prevZoom.delay(500).fadeIn();
        }

        if (day != numberOfDays) {
            nextZoom.delay(500).fadeIn();
        }
    }
}

var setCloseZoomView;
setCloseZoomView = function () {
    $(".close-zoom-view").click(function (e) {
        e.preventDefault();
        closeZoomView();
    });
}

var closeZoomView;
closeZoomView = function () {
    $(".opened-zoom-view").find(".zoom-view-content").fadeOut("fast");
    $(".opened-zoom-view").find(".close-zoom-view").fadeOut();
    $(".opened-zoom-view").find(".next-zoom-view").fadeOut();
    $(".opened-zoom-view").find(".prev-zoom-view").fadeOut();
    $(".opened-zoom-view").find(".event-totals-wrapper").fadeOut();
    $(".opened-zoom-view").animate({width: "124px", height: "92px"}, 200).fadeOut().removeClass("opened-zoom-view");
}

var getViewType;
getViewType = function () {

    var view_type = $(".view-type").find(".selected").text().toLowerCase();

    if (view_type == "month") {

        if ($("#month-view-type-options").find(".selected").text().toLowerCase() != "overview") {
            view_type = "month-details";
        }

    }

    return view_type;
}

var getDay;
getDay = function () {
    var day_string = $(".calendar-display-wrapper").first().attr("data-day");

    if (day_string.indexOf("0") == 0) {
        // rmeove the first character
        day_string = day_string.substring(1);
    }

    return parseInt(day_string);
}

var getYear;
getYear = function () {
    return parseInt($(".calendar-display-wrapper").attr("data-year"));
}

var getDate;
getDate = function () {
    return new Date(getYear(), getMonth(), getDay());
}

var getEndDate;
getEndDate = function () {
    var returnVal = null;
    if (getViewType() == "list") {
        var endYear = parseInt($(".calendar-display-wrapper").attr("data-endyear"));
        var endMonth = parseInt($(".calendar-display-wrapper").attr("data-endmonth")) - 1;
        var endDay = parseInt($(".calendar-display-wrapper").attr("data-endday"));
        returnVal = new Date(endYear, endMonth, endDay);
    }
    return returnVal;
}

var getMonth;
getMonth = function () {

    // because javascript does months from 0, we need to subract one from this
    var month_string = $(".calendar-display-wrapper").first().attr("data-month");

    if (month_string.indexOf("0") == 0) {
        // remove the first character
        month_string = month_string.substring(1);
    }

    return parseInt(month_string) - 1;
}

var loadNewCalendar;
loadNewCalendar = function (type, date, endDate, filterData) {


    calendar = $(".calendar-display-wrapper").first();
    calendarParent = calendar.parent();

    if ($(".calendar-display-wrapper").length > 0) {
    }
    else {
        location.reload();
    }

    // dim current calendar and add a throbber
    calendarParent.animate({opacity: 0.5});
    $("#cal-throbber").fadeIn();
    $("#controls-blocker").show();
    //blockUi(true, null, "cal-island");

    // if there's a pop-up, hide that, too
    if ($(".event-details-popup").length > 0) {
        $(".event-details-popup").animate({opacity: 0});
    }

    var data = getCalendarData(type, date, endDate, filterData);

    //var before_ajax = new Date();
    //console.log("starting ajax request..." + before_ajax);

    $.post(
        '/scheduler/index/get-calendar',
        data,
        function (response) {
            // empty out the current calendar and fill it in with this new one
            //var after_ajax = new Date();
            //console.log("process complete...");

            $("#cal-throbber").fadeOut();

            setTimeout(function () {

                $("#calendar").empty();
                calendarParent.animate({opacity: 1});
                $("#controls-blocker").hide();

                // now update our global variables so everyone is talking again
                calendar = $(".calendar-display-wrapper").first();


                if ((response && response.length < 3333700) || type != "list") {

                    document.getElementById('calendar').innerHTML = response;

                    if ($(".calendar-display-wrapper").length > 0) {
                        if (type != "list") {
                            $("#cal-controls-inside-wrapper").find(".cal-control-title").removeClass("header-with-date-pickers");
                        }

                        // update our title in the calendar controls
                        setTitle();

                        if (type == "month") {
                            sortEvents($(".zoom-view"), ".zoom-event", ".zoom-view-content");
                            addAddtionalDomElements();
                        }
                        else if (type == "month-details") {
                            sortEvents($(".month-details-content"), ".month-details-event", ".month-details-day-content");
                        }
                        else {
                            sortEvents($("." + type + "-event-view"), "." + type + "-event", ".content");
                        }

                        // set up element interactions again
                        setInteractions(type);

                        updateFiltersText();

                        if (type == "day") {
                            $("#day-event-view").find(".btn-data").each(function () {
                                var event_id = $(this).attr("data-event-id");
                                var content = buildActionButtons(event_id, true);
                                $("#bottom-buttons-" + event_id).remove();
                                $(this).after('<div id="bottom-buttons-' + event_id + '" class="bottom-buttons extra-small gray-button">' + content + '</div>');

                                $("#bottom-buttons-" + event_id).find("a").each(function () {
                                    if (!$(this).hasClass("link-to-skills-tracker")) {
                                        $(this).button().blur();
                                    }

                                });
                            });
                        }
                    }
                    else {
                        getBrokenRobot(type, date, endDate);
                    }

                    if (type != "month" && type != "month-details") {
                        $("#month-view-type-options").hide();
                    }
                    else {
                        $("#month-view-type-options").fadeIn();
                    }

                    //Display student shift limit warning if need be
                    if ($(".calendar-display-wrapper").attr("data-studentlimitwarning") != "") {
                        $("#limit-warning-type").text($(".calendar-display-wrapper").attr("data-studentlimitwarning"));
                        $(".student-shift-limit-warning").fadeIn();
                    } else {
                        $(".student-shift-limit-warning").fadeOut();
                    }

                    /*
                     var totally_complete = new Date();
                     console.log("append compelte...");
                     console.log("-----------------------------------------------------------------------");
                     console.log("-----------------------------------------------------------------------");
                     console.log("number of DOM elements: " + document.getElementsByTagName('*').length);
                     console.log("number of events: " + $(".zoom-event").length);
                     console.log("process time: " + (Math.abs(before_ajax-after_ajax)/1000) + " seconds");
                     console.log("jquery time: " + (Math.abs(after_ajax-totally_complete)/1000) + " seconds");
                     console.log("total time: " + (Math.abs(before_ajax-totally_complete)/1000) + " seconds");
                     */
                }
                else {
                    getBrokenRobot(type, date, endDate);
                }

            }, 0);
        }, "json").fail(function () {
            $("#cal-throbber").fadeOut();

            $("#calendar").empty();
            calendarParent.animate({opacity: 1});
            $("#controls-blocker").hide();

            // now update our global variables so everyone is talking again
            calendar = $(".calendar-display-wrapper").first();

            getBrokenRobot(type, date, endDate);
        });
}


var getBrokenRobot = function (type, date, endDate) {
    var dummy_list = '';
    var display_title = "";

    var dataMonth = date.getMonth() + 1;
    var dataDay = date.getDate();
    var dataYear = date.getFullYear();

    if (!endDate) {
        if (type == "week") {
            endDate = new Date(+date + 518400000);
        }
        else {
            endDate = date;
        }
    }

    var dataEndMonth = endDate.getMonth() + 1;
    var dataEndDay = endDate.getDate();
    var dataEndYear = endDate.getFullYear();

    if (type == "day") {
        display_title = calDisplay_shortMonthNames[date.getMonth()] + " " + date.getDate() + ", " + date.getFullYear();
    }
    else if (type == "list") {
        display_title = "List";
    }
    else if (type == "month" || type == "month-details") {
        display_title = calDisplay_longMonthNames[date.getMonth()] + " " + date.getFullYear();
    }
    else if (type == "week") {
        display_title = calDisplay_shortMonthNames[date.getMonth()] + " " + date.getDate() + ", " + date.getFullYear() + " - " + calDisplay_shortMonthNames[endDate.getMonth()] + " " + endDate.getDate() + ", " + endDate.getFullYear();
    }

    dummy_list += '<div class="calendar-display-wrapper" data-title="' + display_title + '" data-month="' + dataMonth + '" data-day="' + dataDay + '" data-year="' + dataYear + '" data-endMonth="' + dataEndMonth + '" data-endDay="' + dataEndDay + '" data-endYear="' + dataEndYear + '">';

    if (type != "day") {
        var narrow_date_range_msg = "or choose a smaller date range ";
    }
    else {
        var narrow_date_range_msg = "";
    }

    var broken_msg = "<div class='broken-too-many-shifts-message'>";
    broken_msg += "<img src='/images/overwhelmed-robot.png'>";
    broken_msg += "<span class='too-many-shifts-message'>That's a lot of shifts!</span>";
    broken_msg += "<div class='subtle-no-shifts-msg'>";
    broken_msg += "In fact, that's more data than Fisdap can handle right now. Use your filters " + narrow_date_range_msg + "to narrow your search.";
    broken_msg += "</div>";
    broken_msg += "</div>";


    if (type == "day") {
        dummy_list += "<div class='day-view'>" + broken_msg + "</div>";
    }
    else if (type == "list") {
        dummy_list += "<div class='list-view'>";
        dummy_list += "<div id='date-pickers-for-title'>";
        dummy_list += "<input style='margin-right:0.5em;' type='text' id='startDate' value='" + getDateString(date) + "' class='selectDate'>-";
        dummy_list += "<input style='margin-left:0.5em;' type='text' id='endDate' value='" + getDateString(endDate) + "' class='selectDate'>";
        dummy_list += "<div class='extra-small blue-button'>";
        dummy_list += "<a href='#' id='list-go-btn' class='first last'>Go</a>";
        dummy_list += "</div>";
        dummy_list += "</div>";
        dummy_list += broken_msg;
        dummy_list += "</div>";
    }
    else if (type == "week") {
        dummy_list += broken_msg;
    }
    else {
        dummy_list += broken_msg;
    }

    dummy_list += "</div>";

    document.getElementById('calendar').innerHTML = dummy_list;
    setTitle();
    setInteractions(type);

    if (type == "list") {
        $("#cal-controls-inside-wrapper").find(".cal-control-title").addClass("header-with-date-pickers");
    }
}

// this is for month view only, helps reduce the number of DOM elements jQuery has to append in 1 chunk
var addAddtionalDomElements;
addAddtionalDomElements = function () {

    var days_of_week = ['', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // start with adding day navigation
    $(".zoom-view").each(function () {

        var day_block = $(this).parent();
        var day_number = day_block.find(".day-num").text();
        var weekday = days_of_week[day_block.attr("data-weekday")];
        var today = "";

        if (day_block.find(".today-img").length > 0) {
            today = "<img class='today-img' src='/images/today.png' style='left:16em;top:-1em;'>";
        }

        $(this).prepend('<div class="day-num">' + day_number + ' <span class="weekday-name">' + weekday + '</span>' + today);
        $(this).find(".day-num").first().after('<a href="#" class="close-zoom-view"><img src="/images/icons/delete.png"></a><a href="#" class="next-zoom-view"><img src="/images/tall_arrow_right.png"></a>');
        $(this).find(".event-totals-wrapper").after('<a href="#" class="prev-zoom-view"><img src="/images/tall_arrow_left.png"></a><div class="clear"></div>');
    });

}

/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * This function takes a date and puts it into the same format as our date picker inputs.
 * Returns a string
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getDateString = function (given_date) {
    var day = new String(given_date.getDate());
    var month = new String(given_date.getMonth() + 1);
    var year = new String(given_date.getFullYear());
    if (month.length == 1) {
        month = "0" + month;
    }
    if (day.length == 1) {
        day = "0" + day;
    }
    return month + "/" + day + "/" + year;
}

var addStandardPopupDomElements;
addStandardPopupDomElements = function (event_id) {

    var popup = $("#event-details-" + event_id);

    // now add site icon/title/subtitle
    var event_div_type = "";
    var view_type = getViewType();

    if (view_type == "month") {
        event_div_type = "zoom";
    }
    else if (view_type == "month-details") {
        event_div_type = "month-details-event";
        //	popup = $("#event-details-popup-" + event_id);
    }
    else if (view_type == "week") {
        event_div_type = "week";
        popup = $("#event-details-popup-" + event_id);
    }
    else if (view_type == "list") {
        event_div_type = "list";
        popup = $("#event-details-popup-" + event_id);
    }


    if (popup.find(".close-details-popup").length > 0) {
        // we've done this work already...
    }
    else {
        // add some of the standard DOM elemnets (do this here so we only do it when necessary)

        // first add the ticker/close
        popup.prepend('<div class="details-ticker right-ticker top-ticker"><img src="/images/ticker.png"></div><a class="close-details-popup" href="#"><img src="/images/icons/delete.png"></a>');


        var zoom_event = $("#" + event_div_type + "-event-" + event_id);

        if (popup.find(".site-icon").length > 0) {
            // we already have this content
        }
        else {

            //var cal_data = getCalendarData();

            var year = getYear();
            var month = getMonth();
            var day = 0;

            if (view_type == "month") {
                day = zoom_event.parent().parent().attr("data-day-number");
            }
            else if (view_type == "month-details") {
                day = $("#month-details-event-" + event_id).attr("data-dayNum");
            }
            else if (view_type == "week") {
                //Because week view can rollover into a new month/year, grab those values from the DOM element
                day = zoom_event.attr("data-dayNum");
                //Javascripts likes months in 0-11, rather than 1-12
                month = zoom_event.attr("data-monthNum") - 1;
                year = zoom_event.attr("data-yearNum");
            }
            else if (view_type == "list") {
                //Because list view can rollover into a new month/year, grab those values from the DOM element
                fulldate = zoom_event.parent().parent().attr("id").split("-");
                day = fulldate[4];
                month = fulldate[3] - 1;
                year = fulldate[5];
            }


            var popup_content = popup.find(".popup-content");
            var new_html = "";
            var type = popup_content.attr("data-event-type");
            var zoom_event_title_pieces = zoom_event.find(".event-title").text().split(")");
			var map_add = $.trim(zoom_event.find(".details_map_address").text());
            var time_duration = zoom_event_title_pieces[0];
            var name_site_base = zoom_event_title_pieces[1];

            if (view_type == "month-details") {
                time_duration = $("#month-details-event-" + event_id).find(".details_time_duration_wrapper").text();
                name_site_base = $("#month-details-event-" + event_id).find(".details_location_wrapper").text();
				map_add = $.trim($("#month-details-event-" + event_id).find(".details_map_address").text());
				
            }

            var date_display = calDisplay_shortMonthNames[month] + " " + day + " " + year + ", " + time_duration;

            if (view_type != "month-details") {
                date_display += ")";
            }


            new_html += '<img class="site-icon" src="/images/icons/' + type + 'SiteIconColor.png">';
            new_html += '<h3 data-eventid="' + event_id + '" class="event-title ' + type + ' ' + popup_content.attr("data-clickable-event-title") + '">';
            new_html += date_display;
            new_html += '</h3>';
            new_html += '<h2 class="event-subtitle">';
			new_html += name_site_base;
            
			if(map_add != ""){
				new_html += '<a style="padding:0px 0px 3px 0px !important; color: #7a330c;font-size: .8em; border:none;background:none;font-weight:normal;" class="small-link" target="_blank" href="http://maps.google.com/maps?q='+map_add+'">Map it</a><img src="/images/icons/new_window_link.gif" style="" />';
            }
			new_html += '</h2>';
            popup.find(".popup-content").prepend(new_html);
        }

    }

}

var buildActionButtons;
buildActionButtons = function (event_id, to_day_view) {

    var buttons_wrapper = "";

    if (getViewType() == "week") {
        buttons_wrapper = $("#event-details-popup-" + event_id).find(".bottom-buttons");
    }
    else {
        buttons_wrapper = $("#bottom-buttons-" + event_id);
    }

    var button_data = $("#btn-data-" + event_id);
    var html_content = "";
    var shift_id = button_data.attr("data-shift-id");
    var series_id = button_data.attr("data-series-id");

    // Delete quick add shift
    if (button_data.attr("data-delete-quick-add-shift")) {
        html_content += '<span class="button-wrapper edit-wrapper"><a href="#" data-shiftid="' + shift_id + '" class="delete-quick-add-shift">Delete</a></span>';
    }

    // Edit quick add shift
    if (button_data.attr("data-edit-quick-add-shift")) {
        html_content += '<span class="button-wrapper edit-wrapper"><a href="#" data-shiftid="' + shift_id + '" class="edit-quick-add-shift">Edit</a></span>';
    }

    // Assign/Drop
    if (button_data.attr("data-assign")) {
        html_content += '<span class="button-wrapper assign-wrapper"><a href="#" data-seriesid="' + series_id + '" class="assign-button" data-eventId="' + event_id + '">Assign/Drop</a></span>';
    }

    // Delete
    if (button_data.attr("data-delete")) {
        html_content += '<span class="button-wrapper delete-wrapper"><a href="#" data-future="' + button_data.attr("data-future") + '" data-seriesid="' + series_id + '" class="delete-button" data-eventId="' + event_id + '">Delete</a></span>';
    }

    // Edit
    if (button_data.attr("data-edit")) {
        html_content += '<span class="button-wrapper edit-wrapper"><a href="' + button_data.attr("data-edit-event-href") + '" data-seriesid="' + series_id + '" class="edit-button" data-eventId="' + event_id + '">Edit</a></span>';
    }

    // History
    if (button_data.attr("data-history")) {
        html_content += '<span class="button-wrapper history-wrapper"><a href="#" class="open_history_modal" data-id="' + event_id + '"';
        // flag if this is a quick-add shift
        if (shift_id > 0) {
            html_content += ' data-quickAdd="1"';
        }
        html_content += '>History</a></span>';
    }

    // Sign up
    if (button_data.attr("data-signup")) {
        html_content += '<span class="button-wrapper edit-wrapper"><a href="#" data-usercontextid="' + button_data.attr("data-user-context-id") + '" class="signup" data-eventId="' + event_id + '">Sign up</a></span>';
    }

    // Request change
    if (button_data.attr("data-shift-request")) {
        html_content += '<span class="button-wrapper history-wrapper"><a href="#" data-assignmentid="' + button_data.attr("data-assignment-id") + '" class="shift-request" data-eventId="' + event_id + '">Request change</a></span>';
    }

    // Skills & Pt. Care link
    if (button_data.attr("data-st-link")) {
        html_content += '<a href="' + button_data.attr("data-st-href") + '" class="link-to-skills-tracker">Skills & Pt. Care</a>';
    }

    if (to_day_view) {
        return html_content;
    }
    else {
        var new_btn_wrapper = buttons_wrapper.remove().empty().append(html_content);

        var popup_selector = $("#event-details-popup-" + event_id);

        if (getViewType() == "month" || getViewType() == "month-details") {
            popup_selector = $("#event-details-" + event_id);
        }

        popup_selector.find(".popup-content").append(new_btn_wrapper).find("a").each(function () {

            if (!$(this).hasClass("link-to-skills-tracker")) {
                $(this).button();
            }
        });
    }

    return;

}

var getCalendarData;
getCalendarData = function (type, date, endDate, filterData) {

    var data = {
        type: type,
        month: parseInt((date.getMonth())) + 1,
        day: parseInt(date.getDate()),
        year: parseInt(date.getFullYear())
    };

    if (endDate) {
        endMonth = parseInt((endDate.getMonth())) + 1;
        endDay = parseInt(endDate.getDate());
        endYear = parseInt(endDate.getFullYear());
        data['endMonth'] = endMonth;
        data['endDay'] = endDay;
        data['endYear'] = endYear;
    }

    if (filterData) {
        data['filters'] = filterData;
    }
    else {
        data['filters'] = getFilters();
    }

    return data;
}
