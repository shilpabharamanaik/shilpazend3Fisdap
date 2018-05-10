$(function () {
    $("#lockShiftDialog").bind("dialogopen", function (event, ui) {
        initLockShiftModal();
    });
});

function initLockShiftModal() {
    // unbind actions because we are about to re-bind them
    $('#lockShiftDialog .lock-shift-btn').unbind("click");

    // buttonize the buttons
    $("#lockShiftDialog .button").button();

    // lock/unlock individual patients
    $('#lockShiftDialog .lock-run-btn').click(function (a) {
        a.preventDefault();
        var run_id = $(this).attr('data-runid');
        var lockImg = $(this).find('img');

        if (lockImg.attr('src') == '/images/icons/locked.svg') {
            $('#run_lock_switch_' + run_id).val(0);
            lockImg.attr('src', '/images/icons/unlocked.svg');
        } else {
            $('#run_lock_switch_' + run_id).val(1);
            lockImg.attr('src', '/images/icons/locked.svg');
        }

        // if this patient was verified, show the warning about unlocking patients
        if ($(this).attr("data-verified")) {
            $("#patient-unlock-notice").slideDown();
        }
    });

    // click the "unlock all" check box
    $('#unlockAllRuns').click(function (a) {
        // if any patients were verified, show the warning about unlocking patients
        if ($("#short-run-table a[data-verified=1]").length > 0) {
            $("#patient-unlock-notice").slideDown();
        }

        if ($('#unlockAllRuns').is(':checked')) {
            $('input[name=\"unlockAllRuns\"][type=\"hidden\"]').val(1);
            $('input[class=\"run_lock_switch\"][type=\"hidden\"]').val(0);
            $('.lock-run-btn').find('img').attr('src', '/images/icons/unlocked.svg');
        } else {
            $('input[name=\"unlockAllRuns\"][type=\"hidden\"]').val(0);
            $('input[class=\"run_lock_switch\"][type=\"hidden\"]').val(1);
            $('.lock-run-btn').find('img').attr('src', '/images/icons/locked.svg');
        }
    });

    // show the text area for writing a message to the notified student
    $("#sendEmail").click(function(e) {
        if ($(this).attr("checked")) {
            $("#emailText").slideDown();
        } else {
            $("#emailText").slideUp();
        }
    });

    // cancel the action, close the modal
    $('#lockShiftDialog .closeModal').click(function (e) {
        $("#lockShiftDialog").dialog('close');
    });

    // click the action button
    $('#lockShiftDialog .lock-shift-btn').click(function (e) {
        e.preventDefault();
        blockUi(true, $("#lock-shift-btn-container"), "throbber");
        $.post('/skills-tracker/shifts/validate-lock-shift', $('#lockShiftDialog form').serialize(),
            function (response) {
                if (response === true) {
                    // if we're not on the shift page, just reload (detailed shift report or shift list)
                    if(window.location.toString().indexOf('my-shift') == -1) {
                        window.location.reload();
                    } else {
                        // if we are on the shift page, go back to the shift list because the user may no longer have permission to be here
                        location.href='/skills-tracker/shifts';
                    }
                } else {
                    htmlErrors = '<div class=\'form-errors\'><ul>';
                    $.each(response, function (key, msg) {
                        htmlErrors += '<li>' + msg + '</li>';
                    });
                    htmlErrors += '</ul></div>';

                    $('.form-errors').remove();
                    $('#lockShiftDialog form').prepend(htmlErrors);
                    blockUi(false, $("#lock-shift-btn-container"));
                }
            }
        );
    });

    // make the icon image an svg so we can manipulate it with css
    imgToSVG('.lock-shift-btn img.icon');
}