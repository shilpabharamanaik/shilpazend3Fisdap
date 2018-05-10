$(function () {
    // make sure we have the basic functions needed for the student picker
    // since this picker is loaded after initial page load
    $.getScript("/js/library/Fisdap/View/Helper/multistudent-picklist-library.js");
});

var get_filtered_students_url = "/scheduler/compliance/get-filtered-msp-students";

var initRequirementAssignPicklist;
initRequirementAssignPicklist = function (temp_group_id) {
    // we don't need the hybrid single student selector
    $(".picklist-ms-select").remove();

    // create the fancy filters for the picklist
    $("#picklist-fancy-filter").fancyFilter({
        width: 650,
        closeOnChange: false,
        onFilterSubmit: function (e) {
            return $.post(get_filtered_students_url,
                getRequirementFilterData(),
                function (response) {
                    getUsersResponse(response, temp_group_id);
                }, "json").done();
        }
    });

    // initialize picklist using functions from multistudent-picklist-library.js
    initPicklistButtons();
    setPicklistStyling();
    updateFilterHeader();
    initSearch();
    initHiddenStudentMessageToggle();

    // initialize the rest of the modal
    initRequirementControlButtons();
    getInitialList(temp_group_id);
    initAccountTypeToggle(temp_group_id);
}

// get an array containing the data we need from the fancy filter so we can tell
// the database what users we're looking for
var getRequirementFilterData;
getRequirementFilterData = function () {
    // first use the standard method to get most of the filter data
    var data = getFilterData();

    // then add in the extra stuff we need for this picker
    var account_type = "students";
    if ($("#accountType").val() == 0) {
        account_type = "instructors";
    }
    data["account_type"] = account_type;

    return data;
}

// update the picklist with the users returned via ajax
var getUsersResponse;
getUsersResponse = function (response, temp_group_id) {
    students = response['assignable'];

    // deal with hidden students
    toggleHiddenStudentsNotice($(response['hidden_students']).length);

    // use base functions to set up the available list
    filterList($(".available-list"), $(".search-list").val());
    updateFilterHeader();

    // tweak the options based on where this modal was launched from
    if ($(".assign_modal_hidden_attachments").length > 0) {
        // this is from the 'manage' page, we're going to handle disabling slightly different
        adjustOptionsViaManagePage();
    }
    else {
        adjustOptionsViaReqForm(temp_group_id);
    }

    countOptions();
}

// set up the >, <, >>, and << buttons to move options between the list correctly
// we don't use the standard control buttons because moving users for the requirements modal has some
// specialized stuff, like the asterisks
var initRequirementControlButtons;
initRequirementControlButtons = function() {
    $(".picklist-control-buttons").find("a").click(function(e){

        e.preventDefault();
        if (!$(this).hasClass("disabled-control-button")) {
            var action = $(this).attr("data-controlfunction");
            if (action == "add" || action == "addAll") {
                moveRequirementOptions(action, $(".available-list"), $(".chosen-list"));
            }
            else if (action == "remove" || action == "removeAll") {
                moveRequirementOptions(action, $(".chosen-list"), $(".available-list"));
            }

        }
    });
}

// Get the list of students available for assignment
var getInitialList;
getInitialList = function (temp_group_id) {

    // put the picker in the "pending" stage while we ping the db
    $(".ms-picklist-wrapper").css("opacity", "0.3");
    $(".assign-selected-totals").css("opacity", "0.3");
    $(".ms-picklist-wrapper").append("<img id='first-ms-picker-load' src='/images/throbber_small.gif'>");

    // go get the students
    $.post(get_filtered_students_url,
        getRequirementFilterData(),
        function (response) {
            if (response) {
                getUsersResponse(response, temp_group_id);
                $(".ms-picklist-wrapper").css("opacity", "1");
                $(".assign-selected-totals").css("opacity", "1");
                $("#first-ms-picker-load").css("opacity", "0");
                $("#first-ms-picker-load").hide();
                $("#first-ms-picker-load").remove();

                // instructors
                if ($("#accountType").val() == 0) {
                    toggleInstructorsStudents("Instructors", "instructor");
                }
                else {
                    toggleInstructorsStudents("Students", "student");
                }

            }
        }, "json");


    // sort the options
    $(".available-list").find("option").sort(sortList).appendTo($(".available-list"));

}

// when the account type changes, the selected list is cleared by triggering the "deselect all" arrow
// and the available list is updated to the new account type
var initAccountTypeToggle;
initAccountTypeToggle = function(temp_group_id) {
    $("#accountType").change(function () {
        $("#assign-modal").find(".picklist-control-buttons").find("a").last().trigger("click");
        getInitialList(temp_group_id);
    });
}

// style the modal and change the language based on the selected account type
var toggleInstructorsStudents;
toggleInstructorsStudents = function (toggle_to, account_type_txt) {

    // hide the hidden students warning and the student filters
    if (toggle_to == "Instructors") {
        $(".hidden_students").slideUp();
        $("#picklist-fancy-filter_filters").slideUp();
    }
    else {
        $(".hidden_students").slideDown();
        $("#picklist-fancy-filter_filters").slideDown();
    }

    // change the list headers
    $(".picklist-ms-picker").find("h2").each(function () {
        var first_part = $(this).text().split(" ");
        first_part = first_part[0];
        $(this).text(first_part + " " + toggle_to);
    });

    // change the language on the selected counter
    $("#assign-modal").find(".account-type").text(account_type_txt);

}

// make some additions to the modal if we're assigning folks straight from the manage reqs page
// this includes assigning folks to multiple requirements at once
var adjustOptionsViaManagePage = function () {

    var details_msg = "<ul>";

    var hidden_req_count = 0;
    var userContextIds_by_req = {};
    var show_asterisks_msg = false;
    var removed_one = false;

    // go through each of the requirements (remember we might be assignment more than one at once)
    // and get information about which uses have already been assigned
    $(".assign_modal_hidden_attachments").each(function () {
        hidden_req_count++;
        var req_id = $(this).attr("id").split("_");
        req_id = req_id[1];

        userContextIds_by_req[req_id] = {
            id: req_id,
            name: $("#edit_" + req_id).parent().find(".requirement-title").text(),
            users: $(this).val().split(",")
        };
    });

    // now go through the available users list and see if any options are already assigned
    $("#assign-modal").find(".available-list").find("option").each(function () {

        var userContextId = $(this).val();
        var matched_req = [];

        $.each(userContextIds_by_req, function (i, v) {
            if ($.inArray(userContextId, v['users']) != -1) {
                matched_req.push(v['name']);
            }
        });

        // if this user has been assigned to at least one of the reqs, do stuff
        if (matched_req.length > 0) {
            // they are assigned to all of the requirements selected ... remove this option
            if (matched_req.length == hidden_req_count) {
                $(this).remove();
                // delete this user from the students object to make sure it stays removed, even when searching
                delete students[userContextId];
                removed_one = true;
            } else {
                // they are assigned to at least 1 of the selected requirements, but not all.
                // This is still a chooseable option, but we need to inform the user
                var txt = $(this).text();
                $(this).text("*" + txt);
                $(this).removeClass("already-added-user");
                details_msg += "<li>" + txt + " has " + prettyList(matched_req) + " assigned.</li>";
                show_asterisks_msg = true;
                // update this user's name in the students object to make sure it stays asterisked, even when searching
                students[userContextId] = $(this).text();
            }
        } else {
            $(this).removeClass("already-added-user");
        }

    });

    details_msg += "</ul>";

    // remove the messages explaining that some users have been assigned; we might add them again later
    $("#assign-modal-content").find(".hidden_instructors").remove();
    $("#assign-to-req-msg").remove();

    // if there are some users who are assigned to some but not all the reqs, let the user know
    if (show_asterisks_msg) {
        $("#asterisks-msg-details").html(details_msg);
        $("#asterisks-msg").show();
    }

    // if any users have been removed entirely from the list, let the user know
    if (removed_one) {
        var msg = "all of the selected requirements.";
        if (hidden_req_count == 1) {
            msg = "this requirement.";
        }

        // tailor the message to the account type
        if ($("#accountType").val() == 0) {
            var hidden_instructors_msg = "<div class='info hidden_instructors'><b>Where did my instructors go?</b><br />Your instructors will not appear in the Available Instructors list if they have already been assigned to " + msg + "</div>";
            $("#assign-modal-content").find(".ms-picklist-wrapper").prepend(hidden_instructors_msg);
        } else {
            $("#hiddenStudentInfo").find("ul").append("<li id='assign-to-req-msg'>they have already been assigned to " + msg + "</li>");
            $("#assign-modal-content").find(".hidden_students").show();
        }
    }
}

// make some additions to the modal if we're assigning folks from the edit/add req page
// this includes assigning multiple groups of folks to the requirement at once
var adjustOptionsViaReqForm = function (temp_group_id) {

    // now remove any students who have already been selected and are part of a pending assignment group
    var pending_assignments_userContextIds = [];
    var already_assigned_userContextIds = [];

    var chosen_list = $("#assign-modal").find(".chosen-list");
    var available_list = $("#assign-modal").find(".available-list");


    // go through the hidden select used to track assignment groups and get info and the
    // users who are pending assignment
    $("#userContextIds").find("option").each(function () {

        // if this group is the one we're editing, get info from it
        if ($(this).attr("data-tempgroupid") == temp_group_id) {
            // go through all the users in this group and add them to the chosen list
            // disable the corresponding option on the available list
            $.each($(this).val().split(","), function (i, v) {
                var original_option = available_list.find("option[value='" + v + "']");
                var cloned_option = original_option.clone();
                disableOption(original_option);
                chosen_list.append(cloned_option);

            });
        } else {
            // if this is not the group we're editing, track the users in this group
            $.each($(this).text().split(","), function (i, v) {
                pending_assignments_userContextIds.push(v);
            });
        }
    });

    // go through the attachments table and track the users who have already been assigned
    $("#attachments").find("tr").each(function () {
        already_assigned_userContextIds.push($(this).attr("data-usercontextid"));
    });

    // now go through all the options on the available list and modify them accordingly
    $(".available-list").find("option").each(function () {

        var userContextId = $(this).val();

        // if this user is pending assignment in another group, label them as such and disable the option
        if ($.inArray(userContextId, pending_assignments_userContextIds) != -1) {
            if (!$(this).hasClass("already-added-user")) {
                var txt = $(this).text();
                $(this).text("*" + txt + ": " + getDueDate(userContextId));
                $(this).addClass("already-added-user");
                disableOption($(this));
            }
        }
        // if this user is has already been assigned this requirement, label them as such and disable the option
        else if ($.inArray(userContextId, already_assigned_userContextIds) != -1) {
            var txt = $(this).text();
            $(this).text(txt + ": assigned");
            $(this).addClass("already-added-user");
            disableOption($(this));
        }
        // otherwise, this user has not been added (either previously or pending) yet
        else {
            $(this).removeClass("already-added-user");
        }

    });
}

// given a user role id, return the due date for the group in which that user belongs
var getDueDate;
getDueDate = function (userContextId) {

    var due_date = 0;

    // go through the hidden select used to track assignment groups
    $("#userContextIds").find("option").each(function () {

        var option_ids = $(this).text().split(",");

        // if the user is in this group, return the due date for this group
        if ($.inArray(userContextId, option_ids) != -1) {
            due_date = $(this).attr("data-duedate");
            return;
        }

    });


    return due_date;
}

// update the assignment count and show the info about the asterisks, if necessary
var countOptions;
countOptions = function () {
    var count = 0;
    var selected_with_astericks = 0;
    $(".chosen-list").find("option").each(function () {
        count++;
        if ($(this).text().indexOf("*") != -1) {
            selected_with_astericks++;
        }
    });
    $("#assign-modal").find(".number-selected").text(count);

    var plural = "s";
    if (count == 1) {
        plural = "";
    }

    $("#assign-modal").find(".assign-modal-plural").text(plural);

    if (selected_with_astericks == 0) {
        $("#assign-modal-content").find(".notice").slideUp();
    } else {
        $("#assign-modal-content").find(".notice").slideUp();
        var notice = "<div id='req-notice' class='notice' style='display:none;'>People with an * next to their name have already been assigned to at least 1 of these requirements. Saving this form will assign the requirements they do not yet have with the given due date.</div>";
        $("#assign-modal-content").find(".ms-picklist-wrapper").prepend(notice);
        $("#req-notice").slideDown();
    }

}

// move users between the lists
var moveRequirementOptions;
moveRequirementOptions = function (action, fromList, toList) {
    // move the options using the standard method
    moveOptions(action, fromList, toList);
    // use the custom method to update the assignment count
    countOptions();
}