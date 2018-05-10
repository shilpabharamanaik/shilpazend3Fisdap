$(function () {
    // make sure we have the basic functions needed for the student picker
    // since this picker is loaded after initial page load
    $.getScript("/js/library/Fisdap/View/Helper/multistudent-picklist-library.js",
        function () {
            initShiftAssignPicklist();
        });
});

var initShiftAssignPicklist;
initShiftAssignPicklist = function () {
    // we don't need the hybrid single student selector
    $(".picklist-ms-select").remove();

    // create the fancy filters for the picklist
    $("#picklist-fancy-filter").fancyFilter({
        width: 650,
        closeOnChange: true,
        onFilterSubmit: function (e) {
            return $.post("/scheduler/index/get-filtered-students",
                getShiftAssignFilterData(),
                function (response) {
                    // update the picklist
                    getStudentsResponse(response);
                }, "json").done();
        }
    });

    // initialize picklist using functions from multistudent-picklist-library.js
    initPicklistButtons();
    updateFilterHeader();
    updateAssignedCount();
    initHiddenStudentMessageToggle();

    // do custom initialization
    getInitialShiftAssignList();
    initShiftAssignAvailableList();
    initShiftAssignControlButtons();
    initShiftAssignSearch();
    setShiftAssignPicklistStyling();
}

// get an array containing the data we need from the fancy filter so we can tell
// the database what users we're looking for
function getShiftAssignFilterData() {
    // first use the standard method to get most of the filter data
    var data = getFilterData();

    // then add in the extra stuff we need for this picker
    if ($("#hidden_cert_levels").length > 0) {
        // we also need to pass in a shift type
        var type = $("h1").text().split(" ");
        type = type[1];
    }
    data['event_id'] = $("#event_id").val();
    data['hidden_cert_levels'] = $("#hidden_cert_levels").val();
    data['event_type'] = type;

    return data;
}

// update the picklist with the students returned via ajax
function getStudentsResponse(response) {
    // redefine the students object based on the response
    students = response['assignable'];

    // use base functions to toggle the alert and set up the available list
    toggleHiddenStudentsNotice($(response['hidden_students']).length);
    filterList($(".available-list"), $(".search-list").val());
    updateFilterHeader();

    // set the status of the control buttons
    var students_with_data = response['has_data']
    disableDropAllButton(students_with_data);
    checkSlotCount();

    // every time the chosen list changes, toggle the alert about data based on info we've gotten from the response
    $(".chosen-list").change(function (e) {
        var hasData = false;
        $(this).find("option:selected").each(function (i, el) {
            if ($.inArray(parseInt($(el).val()), students_with_data) > -1) {
                hasData = true;
            }
        });
        if (hasData) {
            $(".has-data").slideDown();
        } else {
            $(".has-data").slideUp();
        }
        disableDropAllButton(students_with_data);
    });
}

// custom set up for initializing the lists
function styleForEditMode() {
    $("#cert-requirements").hide();
    var number_of_slots = $(".text-slots").text().split(" ");
    $(".text-slots").text("This shift has " + number_of_slots[3] + " slots.").css({"text-align": "left", "margin-bottom": "0.5em"});
    $(".text-slots").parent().css({"width": "653px", "margin-left": "-0.4em"});
    $(".text-slots").parent().parent().removeClass("grid_4").addClass("grid_12");
}

function addStudentsFromOtherPrograms(val, label, response) {
    // put them on the chosen list
    $(".chosen-list").append(createOption(val, label));

    // does this student have entered data?
    if ($.inArray(parseInt(val), response['has_data']) > -1) {
        disableOption($(".chosen-list").find("option[value='" + val + "']"));
        $(".has-data-other-program").slideDown();
    } else if (label.indexOf("*") != -1) {
        // we're casey - we can drop this student - but we can't assign them again, show our message
        $(".drop-only-students").slideDown();
    } else {
        // this needs to be a disabled option - we are not Cassey so it's for show only.
        disableOption($(".chosen-list").find("option[value='" + val + "']"));
    }
}

function getInitialShiftAssignList() {
    // if we're assigning students to a new shift, we don't have shift type info, so we need some special styling
    var edit_shift = $("#assign-modal-content").find("#site-icon").length;
    if (!edit_shift) {
        styleForEditMode();
    }

    // get the students to start it off
    $(".ms-picklist-wrapper").css("opacity", "0.5");
    $(".ms-picklist-wrapper").append("<img id='first-ms-picker-load' src='/images/throbber_small.gif'>");

    $.post("/scheduler/index/get-filtered-students",
        getShiftAssignFilterData(),
        function (response) {
            if (response) {
                // figure out who the assigned students are
                assigned_students = {};
                // if this is a saved shift, we'll get the info we need from the database
                if (edit_shift) {
                    assigned_students = response['assigned'];
                } else {
                    // if this is a new shift, we need to get the assigned students just from the picker
                    if ($("#assigned_students").find("option").length > 0) {
                        $("#assigned_students").find("option").each(function () {
                            assigned_students[$(this).attr("value")] = $(this).text();
                        });
                    }
                }

                // add the assigned students to the chosen list
                $.each(assigned_students, function (val, label) {
                    $(".chosen-list").append(createOption(val, label));
                    if (label.indexOf("*") != -1) {
                        // the requirements have somehow changed, and even though is is our student - if we drop them
                        // we won't be able to assign them again - show our warning message
                        $(".drop-only-students").slideDown();
                    }
                });

                // now go through and deal with all the students from different programs who may be already assigned to this shift
                $.each(response['different_program_students'], function (val, label) {
                    addStudentsFromOtherPrograms(val, label, response);
                });

                // update the picklist
                getStudentsResponse(response);

                // take us out of "loading" mode
                $(".ms-picklist-wrapper").css("opacity", "1");
                $("#first-ms-picker-load").remove();
            }
        }, "json");

    // sort the options
    $(".available-list").find("option").sort(sortList).appendTo($(".available-list"));
}

// initialize the available list so that add buttons change dynamically as you select stuff
function initShiftAssignAvailableList() {
    $(".available-list").change(function () {
        var trying_to_add = $(this).find("option:selected").length;
        var total_slots = parseInt($("#total_slots").val());

        if ((trying_to_add + getChosenCount()) > total_slots) {
            disableAddButtons(true, "add");
        } else {
            disableAddButtons(false, "add");
        }
    });
}

// if any of the assigned students already have data associated with this shift, we cannot drop them
function disableDropAllButton(students_with_data) {
    // see if any of the students has data
    var hasData = false;
    $(".chosen-list").find("option").each(function (i, el) {
        if ($.inArray(parseInt($(el).val()), students_with_data) > -1) {
            hasData = true;
        }
    });

    // set the "remove all" button status accordingly
    disableAddButtons(hasData, "removeAll");
}

// disable/enable the given action button
function disableAddButtons(disable, given_action) {
    $(".picklist-control-buttons").find("a").each(function () {

        var action = $(this).attr("data-controlfunction");
        if (action == given_action) {
            if (disable) {
                $(this).addClass("disabled-control-button");
                $(this).button("disable");
            } else {
                $(this).removeClass("disabled-control-button");
                $(this).button("enable");
            }
        }
    });
}

// see if there are any slots open and enable/disable the add buttons accordingly
function checkSlotCount() {
    var total_slots = parseInt($("#total_slots").val());
    var chosen_count = getChosenCount();

    // if we've filled all the slots, we can't add any more
    if (chosen_count == total_slots) {
        // disable the add buttons
        disableAddButtons(true, "add");
        disableAddButtons(true, "addAll");
    } else {
        // there are still slots left, so we can enable the add button
        disableAddButtons(false, "add");

        // count up the number of students on the available list
        var available_count = $(".available-list").find('option:not(:disabled)').length;

        // if there are more students available than there are open slots, we can't add them all
        if ((available_count + chosen_count) > total_slots) {
            disableAddButtons(true, "addAll");
        } else {
            disableAddButtons(false, "addAll");
        }
    }
}

// see how many students have been assigned to this shift
function getChosenCount() {
    return $('.chosen-list').find("option").length;
}

// initialize the control buttons so they move students between lists
function initShiftAssignControlButtons() {
    $(".picklist-control-buttons").find("a").click(function (e) {
        e.preventDefault();
        if (!$(this).hasClass("disabled-control-button")) {
            clickControlButton($(this));
            checkSlotCount();
        }
    });
}

// custom function for how the search works. It filters the list AND THEN checks the slot count, too
function initShiftAssignSearch() {
    $(".search-list").keyup(function () {
        filterList($("." + $(this).attr("data-listtosearch")), $(this).val());
        checkSlotCount();
    });
}

// set custom styling for this picklist
function setShiftAssignPicklistStyling() {
    setPicklistStyling();

    // we need to make sure the filter drop downs are short enough to be contained in the modal
    $("#graduationYear_chzn").find(".chzn-results").css("max-height", "145px");
    $("#graduationMonth_chzn").find(".chzn-results").css("max-height", "145px");
    $("#section_chzn").find(".chzn-results").css("max-height", "112px");
}

