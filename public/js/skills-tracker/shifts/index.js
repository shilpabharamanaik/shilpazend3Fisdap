// This is used in the click handler for a row-
// it can be overwritten by the skills-tracker/views/scripts/shifts/index-instructor.phtml
// view (to point to the instructor summary page, as opposed to the edit page)
var rowClickURL = "/skills-tracker/shifts/my-shift/shiftId/";

//Defining this variable. It will be overwritten by the student picker javascript
var lastStudentId = null;

$(function () {
	$(".editshift-shift-link").hide();

	$('.shift_attendance').hover(function () {
		 $(this).find(".edit_attendance").hide();
		 $(this).find(".editshift-shift-link").show();
	}, function () {
		 $(this).find(".editshift-shift-link").hide();
		 $(this).find(".edit_attendance").show();
	});
	
	
	 // initialize the edit shift modal
	$(".editshift-shift-link").click(function(event) {
		window.location = window.location.href +'#edit';
		window.location.reload();
		event.preventDefault();
        initShiftModal(event);
	});

	if(window.location.hash.indexOf('edit') == 1){
        initShiftModal();
		$("#shiftDialog").dialog("open");
	}


    doTableJqueryEvents();

    if ($("#data-not-found-error").length != 0) {
        $("#addAndFilter").css("display", "none");
    }

    $("#shift-filters").fancyFilter({
        width: 450,
        closeOnChange: false,
        onFilterSubmit: function (e) {
            return $.post("/skills-tracker/shifts/filtershifts",
                {
                    "studentId": $('#studentId').val(),
                    "shiftFilter": $("#shiftsFilters").chosen().val(),
                    "atFilter": $("#attendanceFilters").chosen().val(),
                    "dateFilter": $("#dateFilters").chosen().val(),
                    "pendingFilter": $("#pending").attr("checked"),
                    "instructor": $("#isInstructor").val()
                },
                function (response) {
                    var shiftList = $(response.shifts);
                    $("#shift-list").html(shiftList.hide().fadeIn(1000));
                    doTableJqueryEvents();
                },
                "json").done();
        }
    });

    setHeaderText();
    $("#shift-filters_filter-options .chzn-select").chosen();
    $("#reset-filter-button").button();
    $("#filter-shifts-button").button();

    // ie 8 has issues
    if (jQuery.browser.msie && jQuery.browser.version.substring(0, 2) == "8.") {
        var count = 0;

        $(".shift-list-filters").each(function () {
            count = 0;
            $(this).find("label").each(function () {
                if (count != 0) {
                    $(this).append("<br />");
                }
                count++;
            })
        });
    }

    $("#shift-filters_filter-options .chzn-select, #pending").change(function (event) {
        $("#shift-filters_filters-title-text").html("Filters: <i>Editing</i>");
    });

    $("#reset-filter-button").click(function (event) {
        event.preventDefault();
        $("#shift-filters_filter-options .chzn-select").each(function () {
            resetChosen($(this).attr("id"));
        });
        $("#pending").attr("checked", false);
    });

    $("#filter-shifts-button").click(function (event) {
        event.preventDefault();
        setHeaderText();
        $("#shift-filters_filters-title").trigger("click"); // close filters
        $(this).change(); // changing the element triggers the submit in a fancy filter
    });

});

function resetChosen(select_id) {
    $("#" + select_id.replace(/-/g, '_') + "_chzn").remove();
    $("#" + select_id).removeClass("chzn-done");
    $("#" + select_id + " option").attr("selected", false);
    $("#" + select_id).chosen();
}


function setHeaderText() {
    var headerText = "Filters: ";

    if ($("#pending").attr("checked") == 'checked') {
        headerText += "pending";
    } else if ($("#dateFilters").chosen().val() == 'peri') {
        headerText += "Recent/upcoming";
    } else {
        headerText += $("#dateFilters").chosen().find("option:selected").text();
    }
    headerText += "; ";

    var type = $("#shiftsFilters").chosen().val();
    if (type) {
        headerText += "specific types; ";
    }

    var attendance = $("#attendanceFilters").chosen().val();
    if (attendance) {
        headerText += "specific attendance; ";
    }

    $("#shift-filters_filters-title-text").text(headerText);
}

function getFilter(sName) {
    var chzn = $("#" + sName + "_chzn");
    var select = $("#" + sName);
    var selections = [];

    if (chzn.find(".result-selected").length > 0) {
        chzn.find(".result-selected").each(function () {
            selections.push($(this).attr("data-optionval"));
        });
    }

    return select.chosen().val();

}

//Delete all shifts that are marked to be deleted before navigating away from this form
$(window).on('beforeunload', function () {
    // only run the deletion if there are shifts to delete
    if ($("#shift-table td.undo").length > 0) {
        hardDeleteShifts($("#shift-list #studentId").val());
    }
});

function loadShiftModal(type) {
    var studentId = ($("#available-list").val());
    if (studentId == 0) {
        $("<div>Please select a student first.</div>").dialog({"modal": true, "resizable": false, "width": 325});
        return;
    }

    $("#add-shift-container").append("<img id='shift-modal-throbber' src='/images/throbber_small.gif'>");
    $.post("/skills-tracker/shifts/generate-shift-form", {"type": type, "student_id": studentId},
        function (resp) {
            $("#shiftDialog").dialog("option", "title", "Quick Add " + type + " shift");
            $("#shiftDialog").html($(resp).html());
            initShiftModal();
            if (studentId) {
                $("#hidden-modal-student-id").val(studentId);
            }
            $("#shiftDialog").dialog("open");
            $("#shift-modal-throbber").remove();
        });
}

function loadLockModal(shiftId, mode) {
    // unbind hover behavior for the image
    var button = $("tr#" + shiftId + " .lock-shift-btn");
    $(button).unbind("hover");

    // add throbber/ui blocker
    blockUi(true, $("tr#" + shiftId + " .shift-info-container"), "throbber");
    positionBlocker($("tr#" + shiftId));

    // get the title for the modal
    var title = mode.charAt(0).toUpperCase() + mode.slice(1) + " Shift";

    $.post("/skills-tracker/shifts/generate-lock-form", {"shiftId": shiftId},
        function (resp) {
            $("#lockShiftDialog").html($(resp).html());
            $("#lockShiftDialog").dialog("option", "title", title);
            $("#lockShiftDialog").dialog("open");

            // remove the block and return to the original hover action
            blockUi(false, $("tr#" + shiftId + " .shift-info-container"));
            $(button).hover(function (e) {
                lockHoverEvent(e, $(this));
            });
            $(button).trigger("mouseleave");
        });
}


function filterShifts(filter, studentId, isInstructor) {
    if (isInstructor == undefined) {
        isInstructor = $("#isInstructor").val();
    }

    if (studentId != undefined) {
        lastStudentId = studentId;
    } else if (lastStudentId != null) {
        studentId = lastStudentId;
    } else {
        studentId = $('#studentId').val();
    }

    //grab elements from the form that need to be interacted with
    var formElements = $("#shift-filters form input");

    //Add the throbber gif and disable the filter inputs
    blockUi(true);
    formElements.attr('disabled', 'disabled');

    //Send AJAX request to server, once we get resp, remove throbber and enable form inputs
    $.post('/skills-tracker/shifts/filtershifts', {
            "studentId": studentId,
            "shiftFilter": $("#shiftsFilters").chosen().val(),
            "atFilter": $("#attendanceFilters").chosen().val(),
            "dateFilter": $("#dateFilters").chosen().val(),
            "instructor": isInstructor
        },
        function (response) {
            var shiftList = $(response.shifts);

            if ($("#available-list").val() == 0) {
                $("#shift-list").empty();
            }
            else {
                $("#shift-list").html(shiftList.hide().fadeIn(1000));
            }

            if (response.studentName) {
                $("#shift-list-student-title").text(response.studentName + "'s Shifts").show();
            }
            else {
                $("#shift-list-student-title").text("").show();
            }

            if (response.examInterviewLink) {
                $("#exam-info-check").css("display", "block");
                $("#exam-interview-link").attr("href",response.examInterviewLink);
            }
            else {
                $("#exam-info-check").css("display", "none");
            }

            $('#filters-' + filter).attr('checked', true);
            doTableJqueryEvents();
            formElements.removeAttr('disabled');
            blockUi(false);

            if (response.hasSkillsTracker && studentId.length > 0) {
                $("#addAndFilter").css("display", "block");
                $("#export-shift-list-links").css("display", "block");
            } else {
                $("#addAndFilter").css("display", "none");
                $("#export-shift-list-links").css("display", "none");
                $("#exam-info-check").css("display", "none");
            }

						$(".editshift-shift-link").hide();

						$('.shift_attendance').hover(function () {
							 $(this).find(".edit_attendance").hide();
							 $(this).find(".editshift-shift-link").show();
						}, function () {
							 $(this).find(".editshift-shift-link").hide();
							 $(this).find(".edit_attendance").show();
						});


						 // initialize the edit shift modal
						$(".editshift-shift-link").click(function(event) {
							window.location = window.location.href +'#edit';
							window.location.reload();
							event.preventDefault();
									initShiftModal(event);
						});

						if(window.location.hash.includes('#edit')){
									initShiftModal();
							$("#shiftDialog").dialog("open");
						}
        }, 'json');

}

function doTableJqueryEvents() {
    //remove previous jquery stuff because we're about to reapply them
    $('#shift-table .alt').removeClass('alt');
    //$('input[name=filters]').unbind('change');
    $('.delete-shift').unbind('click');
    $('.lock-shift-btn').unbind('click');
    $("#add-field-shift").unbind('click');
    $("#add-clinical-shift").unbind('click');
    $("#add-lab-shift").unbind('click');


    $("#add-field-shift").click(function (event) {
        event.preventDefault();
        loadShiftModal("field");
    });

    $("#add-clinical-shift").click(function (event) {
        event.preventDefault();
        loadShiftModal("clinical");
    });

    $("#add-lab-shift").click(function (event) {
        event.preventDefault();
        loadShiftModal("lab");
    });

    $('.delete-shift').click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        var id = $(this).attr('shiftid');
        deleteShift(id);
    });

    $('.lock-shift-btn').hover(function (e) {
        lockHoverEvent(e, $(this));
    });

    $('.lock-shift-btn').click(function (event) {
        event.stopPropagation();
        event.preventDefault();
        var id = $(this).attr('dataId');
        var mode = $(this).attr('title').split(" ").shift();
        loadLockModal(id, mode);
    });

    // show or hide the add shift buttons and the shift list filters

    // I don't know what this is doing, but it's hiding the add shift
    // buttons whenever there are no shifts assigned, so I'm removing it.
    // nkarnick@fisdap.net

    /*
    if ($("#quick-links").length == 0) {
        $("#addAndFilter").css("display", "none");
    }
    else {
        $("#addAndFilter").css("display", "block");
    }
    */
}

function deleteShift(id) {
    var row = $("#" + id)
    blockUi(true);

    function complete() {
        row.hide();

        $.post("/skills-tracker/shifts/delete-shift/", {"shiftId": id},
            function (response) {
                var message = $("<tr><td colspan='7' class='undo'>" + response + "</td></tr>");
                //$("#shift-list-messages").html(message.hide().fadeIn(1000));
                row.before(message.fadeIn(1000));
                doTableJqueryEvents();
                $('#undo-delete-' + id).click(function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    undoDeleteShift(id);
                });
                blockUi(false);
            },
            'json');
    }

    row.fadeOut(1000, complete);
}

function undoDeleteShift(id) {
    var row = $("#" + id);
    blockUi(true);

    function complete() {
        row.fadeIn(1000);
        var message = $("<div>Shift #" + id + " successfully restored.</div>")
        $("#shift-list-messages").html(message.hide().fadeIn(1000));
        row.prev().remove();
        doTableJqueryEvents();
        blockUi(false);
    }

    $.post("/skills-tracker/shifts/undo-delete-shift/", {"shiftId": id}, complete, 'json');
}

function hardDeleteShifts(studentId) {
    $.ajaxSetup({async: false});
    $.post("/skills-tracker/shifts/hard-delete-shifts/", {"studentId": studentId},
        function (response) {
            return true;
        });
}

// This sets up the PDF export links on the student/instructor views.
$(function () {
    $('#export-shift-list-links .pdfLink').click(function () {
        // Do a little work to clean up the shift-list elemenets...

        contents = $('#shift-list').clone();
        contents.css('width', '100%');
        contents.find('tr th').last().css('width', '135px');
        contents.find('#quick-links').remove();

        // Add on the student's name to the beginning of the contents (if it exists)...
        contents.prepend($('#shift-list-student-title').clone());

        createPdf(contents, 'shift-list.pdf', 'export-shift-list-links');

        return false;
    });
});

var lockHoverEvent = function (event, element) {
    event.stopPropagation();
    event.preventDefault();
    var img = $(element).find('img');
    var src = $(img).attr("src");

    // replace the lock icon with the appropriate state
    switch (src) {
        case "/images/icons/unlocked.svg":
            $(img).attr("src", "/images/icons/lock.svg");
            break;
        case "/images/icons/locked.svg":
            $(img).attr("src", "/images/icons/unlock.svg");
            break;
        case "/images/icons/lock.svg":
            $(img).attr("src", "/images/icons/unlocked.svg");
            break;
        case "/images/icons/unlock.svg":
            $(img).attr("src", "/images/icons/locked.svg");
            break;
    }
}
