$(function(){
    $("#save-button").button();

    handleDisabledFormElements($("#send_late_shift_emails"), $("#late-data-options"), false);    
    handleDisabledFormElements($("#disable_educator_signoff"), $("#sign-off-stuff"), true);

    function handleDisabledFormElements(trigger, parentOfElements, disableChildrenWhenChecked){
		
        var firstState = false;
		
        if (disableChildrenWhenChecked) {
            firstState = trigger.attr("checked");
        } else {
	    firstState = !trigger.attr("checked");
	}
		
	if(firstState){
	    toggleState(false, parentOfElements);
	}
	
	trigger.change(function(){
	    toggleState($(this).attr("checked"), parentOfElements, disableChildrenWhenChecked);
	});
    }
	
    function toggleState(enable, parent, disableChildrenWhenChecked){
	var disabledValue = !enable;
	var color = "#bbb";

	if(disableChildrenWhenChecked){disabledValue = enable;}
	if(!disabledValue){color = "#000";}
		
	parent.find("input").attr('disabled', disabledValue);
	parent.css("color", color);
		
    }



    var window_offset_type_trigger;
    var options_popup = $("#offset-type-options-wrapper");

    $(".windows_wrapper").find(".selectDate").datepicker();

    $(".interval_frequency").keydown(function(e) {
        numbersOnlyInput(e);
    }).change(function(){

            if(!$(this).val() || $(this).val().length > 2){
                $(this).val("1");
            }

            // make the chosen elements plural or singular based on the value
            var singular = true;
            if($(this).val() != 1){  singular = false; }

            var interval_type_chosen = $(this).parent().parent().find(".chzn-container");
            var currently_selected_element = interval_type_chosen.find("a").find("span");
            var current_selected_label = currently_selected_element.text();

            if(current_selected_label.indexOf("s") != -1){
                if(singular){
                    currently_selected_element.text(current_selected_label.substr(0, current_selected_label.length-1));
                }
            }
            else {
                if(!singular){
                    currently_selected_element.text(current_selected_label + "s");
                }
            }
    });

    $(".offset-type-change-trigger").button().click(function(e){
        e.preventDefault();

        var trigger = $(this);
        var delay = 50;

        window_offset_type_trigger = trigger;

        if(options_popup.css("display") == "block") {
            options_popup.fadeOut("fast");
            delay = 250;
        }

        setTimeout(function(){


            if(trigger.parent().hasClass("to")){
                // hide "The date the shift is created" option
                $("#date_shift_is_created_offset_type_option").hide();

            }
            else {
                // show "The date the shift is created" option
                $("#date_shift_is_created_offset_type_option").show();

            }

            var top_pos = (trigger.position().top) - options_popup.height() + 10;
            var left_pos = (trigger.position().left) - options_popup.width() + 72;

            options_popup.css("top", top_pos).css("left", left_pos).fadeIn("fast");

        }, delay);



    });

    $(".offset-type-option").click(function(e){

        options_popup.fadeOut("fast");

        var selected_offset_type = $(this).attr("data-offsettype");

        var window_id = window_offset_type_trigger.attr("data-windowtempid");
        var to_from = (window_offset_type_trigger.parent().hasClass("to")) ? "end" : "start";

        var static_element = $("#" + to_from + "_static_" + window_id);
        var interval_element = $("#" + to_from + "_interval_" + window_id);
        var month_offest_element = $("#" + to_from + "_prevMonth_" + window_id);
        var shift_created_date_element = $("#" + to_from + "_dateOfShift_" + window_id);

        // show/hide the correct elements
        if(selected_offset_type == "static"){
            static_element.fadeIn();
            interval_element.hide();
            month_offest_element.hide();
            shift_created_date_element.hide();
        }
        else if(selected_offset_type == "prevMonth"){
            static_element.hide();
            interval_element.hide();
            month_offest_element.fadeIn();
            shift_created_date_element.hide();
        }
        else if(selected_offset_type == "interval"){
            static_element.hide();
            interval_element.fadeIn();
            month_offest_element.hide();
            shift_created_date_element.hide();
        }
        else if(selected_offset_type == "date_shift_is_created"){
            static_element.hide();
            interval_element.hide();
            month_offest_element.hide();
            shift_created_date_element.fadeIn();
        }

        // update our hidden input to reflect the new type id
        $("#offset_type_" + to_from + "_" + window_id).attr("value", $(this).attr("data-offsetTypeId"));

    });

    $("#close-offset-types").click(function(e){
        e.preventDefault();
        options_popup.fadeOut("fast");

    });

    $(".call-chosen").chosen({disable_search_threshold: 30}).change(function(){
        $(this).parent().parent().find(".interval_frequency").trigger("change");
    }).each(function(){
        $(this).parent().parent().find(".interval_frequency").trigger("change");
    });


    $("#field_pick").change(function(){ flippy_window_change("field", $(this)); });
    $("#lab_pick").change(function(){ flippy_window_change("lab", $(this)); });
    $("#clinical_pick").change(function(){ flippy_window_change("clinical", $(this)); });

    $("#field_pick").trigger("change");
    $("#lab_pick").trigger("change");
    $("#clinical_pick").trigger("change");

});

var flippy_window_change = function(type, flippy_trigger)
{
    var windows_wrapper = $("#" + type + "_windows");
    var signup_summary = $("#" + type + "_windows_signup_summary");
    var signup_summary_period = $("#" + type + "_windows_singup_summary_period");

    if(flippy_trigger.val() == 0) {
        windows_wrapper.animate({opacity: 0});
        signup_summary.text("cannot");
        signup_summary_period.text(".");
    }
    else {
        windows_wrapper.animate({opacity: 1});
        signup_summary.text("can");
        signup_summary_period.text("");
    }


};

var numbersOnlyInput = function(event)
{
    // Allow: backspace, delete, tab, escape, and enter
    if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
            // Allow: Ctrl+A
        (event.keyCode == 65 && event.ctrlKey === true) ||
            // Allow: home, end, left, right
        (event.keyCode >= 35 && event.keyCode <= 39)) {
        // let it happen, don't do anything
        return;
    }
    else {
        // Ensure that it is a number and stop the keypress
        if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
            event.preventDefault();
        }
    }
};