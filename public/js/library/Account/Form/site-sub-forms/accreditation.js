// ----------------------------------------------------------------------------------------------------------------------------------
// Initializes the accreditation form, called from /js/account/sites/edit.js
// ----------------------------------------------------------------------------------------------------------------------------------
var initAccreditationFormFunctions = function () {
    initAccreditationButtonsets();
    initPreceptorHoursToggle();
    initAccreditationSave();
    addAccreditationMasking();
    initClearAccreditationForm();

    if ($("#accreditation_preceptor_hours_wrapper").hasClass("disabled-preceptor-hours")) {
        $("#accreditation_preceptor_training_hours").attr("disabled", "disabled");
    }

    $("#save_accreditation").css("padding-top", "0.3em").css("padding-bottom", "0.5em").css("padding-left", "1em").css("padding-right", "1em");

} // end initAccreditationFormFunctions();


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes all of the buttonsets on the accreditation form
 * Because Zend:
 * 		Some fancy formatting has to happen for jQuery UI to make the elements into a buttonset
 * 		This just adds a label for each radio option (instead of Zend's single label for all radios)
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAccreditationButtonsets = function () {
    // keep track of which labels we'll need
    var labels = {};

    // remove radio options from the single <label> element
    $(".accreditation_buttonset_wrapper").each(function () {
        var buttonset = "";
        $(this).find("dd").find("label").each(function () {
            input_html = $(this).html();
            label_txt = $(this).text();
            labels[$(this).find("input").attr("id")] = label_txt;
            input_html = input_html.substring(0, input_html.length - label_txt.length);
            buttonset += input_html;
        })

        $(this).find("dd").html(buttonset);
    });

    // add individual labels for each radio option
    $.each(labels, function (id, txt) {
        $("#" + id).after("<label for='" + id + "' class='" + $("#" + id).attr("class") + "'>" + txt + "</label>");
    });

    // make 'em buttonsets
    $(".accreditation_buttonset_wrapper").each(function () {
        $(this).find("dd").addClass("extra-small").buttonset();
    });

} // end initAccreditationButtonsets()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the preceptor hours toggle
 * 	When the 'formally trained preceptors' buttonset changes, toggle the preceptor hours visibility
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initPreceptorHoursToggle = function () {
    $("#accreditation_formally_trained_preceptors-element input[type=radio]").change(function () {
        togglePreceptorHours($(this).val());
    });

} // end initPreceptorHoursToggle()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the save button for the accreditation form
 * Sends an ajax request to /account/sites-ajax/save-accreditation with current site id and form data
 * If errors, it will append those, otherwise will append a success message
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initAccreditationSave = function () {
    $("#save_accreditation").click(function (e) {
        e.preventDefault();

        var trigger = $(this);
        var form_data = getAccreditationValues();
        var msg_holder = $("#accreditation_submit_messages");

        // remove button & add throbber. Hide existing form messages
        trigger.css("opacity", "0").parent().append(getThrobber("save_accreditation_throbber"));
        if (msg_holder.is(":visible")) {
            console.log(msg_holder);
            msg_holder.slideUp();
        }

        // end our ajax request to process the form!
        $.post("/account/sites-ajax/save-accreditation", {"site_id": getCurrentSiteId(), "form_data": form_data},
            function (resp) {

                // if it wasn't successful, append the errors and add classes to the invalid elements
                if (resp['success'] == "false") {
                    $("#accreditationinfo").find(".invalid-data-error").removeClass("invalid-data-error");
                    msg_holder.html(resp['result']).fadeIn();
                    $.each(resp['form_elements_with_errors'], function (i, v) {
                        $("#" + v).addClass("invalid-data-error");
                    });
                }

                // was successful, append a success message
                else {
                    msg_holder.html(resp['result']).fadeIn().delay(3000).slideUp();
                    $("#accreditationinfo").find(".invalid-data-error").removeClass("invalid-data-error");
                }

                // put the action buttons back and remove the throbber
                trigger.animate({opacity: 1});
                $("#save_accreditation_throbber").remove();

                // scroll up so the user can see the message
                $('html,body').animate({scrollTop: $("#tab-content").offset().top},'slow');

            } // end response function

        ); // end post()

    }); // end click handler

} // end initAccreditationSave()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Toggles the preceptr hours element - deals with style and enalbing/disabling the text input
 * @param boolean value - if 1, the element will be enabled if 0 or null the element will be disabled
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var togglePreceptorHours = function (value) {
    var hours_wrapper = $("#accreditation_preceptor_hours_wrapper");
    var disabled_class = "disabled-preceptor-hours";
    var input_element = $("#accreditation_preceptor_training_hours");

    if (value == 1) {
        hours_wrapper.removeClass(disabled_class);
        input_element.removeAttr("disabled");
    }
    else {
        hours_wrapper.addClass(disabled_class);
        input_element.attr("disabled", "disabled");
    }

} // togglePreceptorHours()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Add our cool masking stuff to our accreditation phone number
 * If this user's program is not in the US, the form element will not have the 'add-masking'
 * class and we won't do the masking
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var addAccreditationMasking = function () {
    if ($("#accreditation_phone").hasClass("add-masking")) {
        $("#accreditation_phone").mask("999-999-9999? x9999");
    }

} // end addAccreditationMasking()


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Initializes the 'clear form' link
 * This adds a click handler to the link and will reset all of the text inputs and buttonsets
 * DOES NOT SAVE THE FORM - the user has to actually click save
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var initClearAccreditationForm = function () {
    $("#clear-accreditation-form").click(function (e) {
        e.preventDefault();

        // text inputs
        $("#accreditation_cao").val("");
        $("#accreditation_phone").val("");
        $("#accreditation_distance_from_program").val("");
        $("#accreditation_preceptor_training_hours").val("");
        $("#accreditation_active_ems_units").val("");
        $("#accreditation_number_of_runs").val("");
        $("#accreditation_number_of_trauma_calls").val("");
        $("#accreditation_number_of_critical_trauma_calls").val("");
        $("#accreditation_number_of_pediatric_calls").val("");
        $("#accreditation_number_of_cardiac_arrest_calls").val("");
        $("#accreditation_number_of_arrest_calls").val("");

        // buttonsets
        $("#accreditationinfo").find('input[type="radio"]:checked').each(function () {
            $(this).prop('checked', false);
            $(this).trigger("change");
        });

        // reset the disabled preceptor training hours
        togglePreceptorHours(0);
    });

} // end initClearAccreditationForm();


/*
 * ----------------------------------------------------------------------------------------------------------------------------------
 * Gets the accreditation form values
 * Returns an object that will be used to send our ajax request
 * ----------------------------------------------------------------------------------------------------------------------------------
 */
var getAccreditationValues = function () {
    var data = {};

    data['accreditation_cao'] = $("#accreditation_cao").val();
    data['accreditation_phone'] = $("#accreditation_phone").val();
    data['accreditation_distance_from_program'] = $("#accreditation_distance_from_program").val();
    data['accreditation_signed_agreement'] = $('input[name="accreditation_signed_agreement"]:checked').val();
    data['accreditation_student_supervision_type'] = $('input[name="accreditation_student_supervision_type"]:checked').val();
    data['accreditation_written_policies'] = $('input[name="accreditation_written_policies"]:checked').val();
    data['accreditation_formally_trained_preceptors'] = $('input[name="accreditation_formally_trained_preceptors"]:checked').val();

    // don't pay attention to the preceptor training hours if the user doesn't formally train their preceptors
    if (data['accreditation_formally_trained_preceptors'] == 1) {
        data['accreditation_preceptor_training_hours'] = $("#accreditation_preceptor_training_hours").val();
    }
    else {
        data['accreditation_preceptor_training_hours'] = null;
    }

    // continue if this isn't a clinical site
    if ($("#accreditation_online_medical_direction-element").length > 0) {
        data['accreditation_online_medical_direction'] = $('input[name="accreditation_online_medical_direction"]:checked').val();
        data['accreditation_advanced_life_support'] = $('input[name="accreditation_advanced_life_support"]:checked').val();
        data['accreditation_quality_improvement_program'] = $('input[name="accreditation_quality_improvement_program"]:checked').val();
        data['accreditation_active_ems_units'] = $("#accreditation_active_ems_units").val();
        data['accreditation_number_of_runs'] = $("#accreditation_number_of_runs").val();
        data['accreditation_number_of_trauma_calls'] = $("#accreditation_number_of_trauma_calls").val();
        data['accreditation_number_of_critical_trauma_calls'] = $("#accreditation_number_of_critical_trauma_calls").val();
        data['accreditation_number_of_pediatric_calls'] = $("#accreditation_number_of_pediatric_calls").val();
        data['accreditation_number_of_cardiac_arrest_calls'] = $("#accreditation_number_of_cardiac_arrest_calls").val();
        data['accreditation_number_of_cardiac_calls'] = $("#accreditation_number_of_cardiac_calls").val();

    }

    return data;

} // end getAccreditationValues()