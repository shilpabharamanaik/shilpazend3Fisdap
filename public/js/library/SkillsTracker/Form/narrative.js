//save the narrative before navigating away from this form
window.onbeforeunload = function () {
    if ($("#narrativeForm").length > 0) {
        removeNarrativeAutosave();
    }

}

//Boolean to keep track of whether or not the autosave timer is running
var timerRunning = false;

//Timeout to stop the autosave timer
var timeout = 0;

//Autosave timer
var timer = 0;

//Time updater
var narrativeClock = 0;

// Tracks whether an ajax request is currently pending...
// Flips to true before we send it, false when the ajax returns.
var pendingChange = false;

// Tracks whether or not to send a second request when the
// pending one completes.  This prevents us from spamming several
// ajax requests for clicking many fields at once.  Effectively
// makes it asynch without locking the interface.
var resendRequest = false;

//Start the timer at zero
var narrativeTime = 0;

var narrativeChangeTimer = null;

var alreadySavedNar = false;

function removeNarrativeAutosave() {
    //$.ajaxSetup({async:false});
    autosaveNarrativeData();
    $('#narrativeForm textarea:visible').unbind('keypress');
    $("#narrativeForm #save").unbind('click');
    $('#shift-overview-link').unbind('click');
    clearInterval(narrativeClock);
    clearInterval(timer);
    clearTimeout(timeout);
    narrativeTime = 0;
}

function autosaveNarrativeData(asyncValue) {
    if (asyncValue == undefined) {
        asyncValue = true;
    }

    var performNarrativeAutosave = function () {
        if ($('#narrativeForm #patientId').val() == "") {
            $('#narrativeForm #patientId').val(getPatientId());
        }

        data = $('#narrativeForm').serialize();

        $.ajax({
            type: "POST",
            url: "/skills-tracker/patients/save-narrative-ajax",
            data: data,
            success: function (response) {
                // Reset the counter...
                narrativeTime = 0;

                $("#narrativeForm #narrativeId").val(response);

                pendingChange = false;

                alreadySavedNar = true;

                if (resendRequest) {
                    resendRequest = false;
                    autosaveNarrativeData();
                }
            },
            async: asyncValue
        });
    }

    if (!pendingChange || asyncValue == false) {
        pendingChange = true;

        performNarrativeAutosave();
    } else {
        resendRequest = true;
    }
}

function initNarrativeAutosave() {
    alreadySavedNar = false;
    narrativeTime = 0;

    // Tracks whether an ajax request is currently pending...
    // Flips to true before we send it, false when the ajax returns.
    pendingChange = false;

    // Tracks whether or not to send a second request when the
    // pending one completes.  This prevents us from spamming several
    // ajax requests for clicking many fields at once.  Effectively
    // makes it asynch without locking the interface.
    resendRequest = false;

    //Make the save button just trigger an autosave
    $("#save-clone").click(function (e) {
        e.preventDefault();
        autosaveNarrativeData();
    });
    $('#shift-overview-link').click(function (e) {
        $.ajaxSetup({async: false});
        autosaveNarrativeData();
    });

    $('textarea:visible').keypress(startTimer);

    narrativeClock = setInterval(function () {
        narrativeTime++;

        if (narrativeTime < 60) {
            timestr = narrativeTime + ' seconds';
        } else if (narrativeTime >= 60 && narrativeTime < 120) {
            timestr = '1 minute';
        } else if (narrativeTime >= 120 && narrativeTime < 3600) {
            timestr = Math.floor(narrativeTime / 60) + ' minutes';
        } else {
            timestr = '&gt;1 hour';
        }

        //Only display the timer notification we've done at least one autosave
        if (alreadySavedNar) {
            //show the timer readout and highlight for a few seconds
            var timerReadout = $("#autosave-timer-nar").show();
            //timerReadout.addClass("updated").removeClass('updated', 3000);

            $('#autosave-timer-nar').html('Last saved ' + timestr + ' ago');
        }
    }, 1000);
}

function startTimer() {
    //if the timer is not running, start it
    if (timerRunning == false) {

        //flip boolean flag to true
        timerRunning = true;

        //start the timed interval to save data
        timer = setInterval(function () {
            autosaveNarrativeData();
        }, 30000);
    }

    //if the timeout to stop saving is already going, reset it
    if (timeout) {
        clearTimeout(timeout);
    }

    //start a timeout to stop the timer
    timeout = setTimeout(function () {
        clearInterval(timer);
        timerRunning = false;
    }, 30000);
}

$(function () {

    $('#narrativeForm textarea').focus(function() {
        autosaveNarrativeData();
        startTimer();
    });

    $('#narrativeForm textarea').blur(function () {
        autosaveNarrativeData();
    });

});