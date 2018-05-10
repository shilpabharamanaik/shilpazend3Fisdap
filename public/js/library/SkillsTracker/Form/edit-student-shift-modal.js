function initShiftModal(event) {
    initSites(event);

    var shownConflictMessage = false;
    var originalAttendance = $('input[name=attendence]:checked').val()

    $('#cancel-edit-btn').button().blur();
    $('#save-edit-btn').button();

    $(".ui-dialog").css({
        "overflow": "visible"
    });
    $(".ui-dialog .ui-dialog-content").css({
        "overflow": "visible"
    });
    $("#date").datepicker({monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"]});
    $("#shiftDialog").dialog({
        open: function () {
            $("#site_chzn a").blur();
            $(".quick-add-notice a").blur();
            $("#cancel-edit-btn").blur();
        }
    });
    $(".quick-add-notice a").css({
        "color": "#A94612"
    });

    initStyles();

    $(".chzn-select").chosen().blur();
    if ($("#hasSites").val() == 0 || $("#at-limit").length > 0 || $("#is_locked").length > 0) {
        $('#save-edit-btn').hide();
        $('#cancel-edit-btn .ui-button-text').html('Ok').blur();
    }

    $('#cancel-edit-btn').click(function (event) {
        event.preventDefault();
        $('#shiftDialog').dialog('close');
        window.history.pushState({},null,'shifts');
    });

    $(".ui-dialog-titlebar-close").click(function (event) {
        event.preventDefault();
        $('#shiftDialog').dialog('close');
        window.history.pushState({},null,'shifts');
    });

    //We need to hide the shift conflict message if they change any of the fields
    $("#date, #time, #hours").change(function (e) {
        shownConflictMessage = false;
        $(".shift-conflicts-msg").slideUp();
    });


    $('input[name=attendence]').change(function (event) {
        // if the original attendance was attended and this shift is not locked, we need to update the button text
        if (originalAttendance <= 2 && !$("#locked").val()) {
            var attendance = $('input[name=attendence]:checked').val();
            var buttonText = $("#save-edit-btn .ui-button-text");
            if (attendance <= 2) {
                $(buttonText).html("Save");
            } else if (attendance >= 3) {
                $(buttonText).html("Save & Lock");
            }
        }
    });

    $('#save-edit-btn').click(function (event) {
        event.preventDefault();

        // Are we just editing a shift? Because, if we are, don't check for scheduling conflicts:
        var shift_id = $("#shiftDialog").find("#shiftId").val();

        if (shift_id.length > 0) {
            // just submit the form
            disableForm();
            submitForm();
        }
        else {
            // check for conflicts

            // Clean up the time a bit here before we send it up to the server- validation is happening in two places and it's pretty difficult/kludgy to
            // fix before validating on the server side.
            // Remove all non digit chars
            cleanTimeVal = $('#time').val().replace(/\D/g, '');

            // Pad with 0's on the left until we hit 4 chars in length
            while (cleanTimeVal.length < 4) {
                cleanTimeVal = "0" + cleanTimeVal;
            }

            $('#time').val(cleanTimeVal);

            disableForm();
            $("#date").attr("disabled", false);
            var postData = $('#shiftDialog form').serialize();
            $("#date").attr("disabled", "disabled");


            //Make sure the student doesn't have another shift scheduled at this time
            if (shownConflictMessage == false && $("#date").val() != "" && $("#hours").val() != "" && $("#time").val() != "") {
                $.post("/skills-tracker/shifts/check-conflicts", postData, function (response) {
                    //If there are conflicts, show the message, remember that we showed the message and renable the form
                    if (response === true) {
                        shownConflictMessage = true;
                        $(".shift-conflicts-msg").slideDown();
                        enableForm();
                    } else {
                        //No conflicts? Submit the form
                        submitForm();
                    }
                }, "json");
            } else {
                //The conflict message is still showing? They must have ignored it and hit save again, submit the form
                submitForm();
            }
        }


    });


    function isInt(n) {
        return n % 1 === 0;
    }

    function submitForm() {
        $("#site").attr("disabled", false);
        $("#base").attr("disabled", false);
        $("#date").attr("disabled", false);
        $.post('/skills-tracker/shifts/validate-shift', $('#shiftDialog form').serialize(), function (response) {
            if (isInt(response)) {
                if ($('#cal-display-filters').length > 0) {
                    loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
                    $('#shiftDialog').dialog('close');
                } else {
					if(window.location.hash){
					window.location.href = "/skills-tracker/shifts/";
					}else{
                    window.location.href = "/skills-tracker/shifts/my-shift/shiftId/" + response;
					}
                }
            } else {
                htmlErrors = '<div class=\'form-errors alert\'><ul>';

                $('label').removeClass('prompt-error');

                $.each(response, function (elementId, msgs) {
                    $('label[for=' + elementId + ']').addClass('prompt-error');
                    $.each(msgs, function (key, msg) {
                        htmlErrors += '<li>' + msg + '</li>';
                    });
                });

                htmlErrors += '</ul></div>';

                $('.form-errors').remove();
                $('#shiftDialog form').prepend(htmlErrors);

                enableForm();
            }
        });
    }

    function disableForm() {
        $('#save-edit-btn').hide().parent().append("<img id='shift-edit-throbber' src='/images/throbber_small.gif'>");
        $('#cancel-edit-btn').hide();
    }

    function enableForm() {
        $('#save-edit-btn').show().parent().find("#shift-edit-throbber").remove();
        $('#cancel-edit-btn').show();
    }
}

function initStyles() {
    $(".chzn-drop .chzn-search input").css("width", "315px");
}
