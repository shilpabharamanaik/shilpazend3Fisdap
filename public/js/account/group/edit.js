$(function () {
    $('#group_start_date, #group_end_date').datepicker();

    $('#save_button').button();
    $('#edit_students, #edit_instructors, #edit_tas').button();

    // reorder the options in each list alphabetically by last name
    alphabetizeOptions($('#group_students'));
    alphabetizeOptions($('#group_instructors'));
    alphabetizeOptions($('#group_tas'));

    // set up modal functionality
    $('#edit_students').click(function () {
        launchModal('students');
        return false;
    });
    $('#edit_instructors').click(function () {
        launchModal('instructors');
        return false;
    });
    $('#edit_tas').click(function () {
        launchModal('tas');
        return false;
    });

    $('#submit_button').button().click(function () {
        saveNamesFromForm();
        return false;
    });

    $('#cancel_modal').button().click(function () {
        $('#modal_student_list').dialog("close");
        return false;
    });

    // initialize cancel and save buttons
    $('#cancel_button').button();

    populateEmptyLists();

    $('#save_button').click(function () {
        // Check to make sure the user can save...

        // If there is no name, throw a validation error.
        if ($('#group_name').val() == '') {
            $('#error_message').css('display', 'block').html("You must give this group a name.");
            return false;
        }

        // If the start date is after the end date, throw a validation error.
        if ($('#group_start_date').datepicker('getDate').getTime() > $('#group_end_date').datepicker('getDate').getTime()) {
            $('#error_message').css('display', 'block').html("The group end date must be after the start date.");
            return false;
        }

        $('#error_message').css('display', 'none').html("");

        data = {
            group_id: $('#hidden-group-id').val(),
            group_name: $('#group_name').val(),
            group_start_date: $('#group_start_date').val(),
            group_end_date: $('#group_end_date').val(),
            group_event_notifications: ($('#group_event_notifications').is(':checked') ? true : false)
        }

        data['assigned_students'] = [];
        data['assigned_instructors'] = [];
        data['assigned_tas'] = [];

        $('#group_students').find('option').each(function (i, e) {
            if ($(e).attr('value') > 0) {
                data['assigned_students'].push($(e).attr('value'));
            }
        });

        $('#group_instructors').find('option').each(function (i, e) {
            if ($(e).attr('value') > 0) {
                data['assigned_instructors'].push($(e).attr('value'));
            }
        });

        $('#group_tas').find('option').each(function (i, e) {
            if ($(e).attr('value') > 0) {
                data['assigned_tas'].push($(e).attr('value'));
            }
        });

        blockUi(true);

        // Save this back to the server and redirect them back to the edit page after saving...
        $.post('/account/group/save/', data, function (result) {
            postSave(result);
        });
        return false;
    });

    updateCounts();
});

populateEmptyLists = function () {
    if ($('#group_students').find('option').length == 0) {
        newOpt = $('<option style="color: gray">No students selected</option>');
        newOpt.attr('value', 0);
        $('#group_students').append(newOpt);
    }

    if ($('#group_tas').find('option').length == 0) {
        newOpt = $('<option style="color: gray">No teaching assistants selected</option>');
        newOpt.attr('value', 0);
        $('#group_tas').append(newOpt);
    }

    if ($('#group_instructors').find('option').length == 0) {
        newOpt = $('<option style="color: gray">No instructors selected</option>');
        newOpt.attr('value', 0);
        $('#group_instructors').append(newOpt);
    }
}

postSave = function (response) {
    if (response > 0) {
        window.location = '/account/group/view';
    }

    blockUi(false);
}

saveNamesFromForm = function () {
    type = $('#hidden-assignment-type').val();

    emptyText = '';

    switch (type) {
        case 'students':
            targetList = $('#group_students');
            emptyText = 'No students selected';
            break;
        case 'tas':
            targetList = $('#group_tas');
            emptyText = 'No teaching assistants selected';
            break;
        case 'instructors':
            targetList = $('#group_instructors');
            emptyText = 'No instructors selected';
            break;
    }

    // Empty the target list...
    targetList.empty();

    // Copy the people from the chosen list into the target list...
    $('#chosen-list').find('option').each(function (i, e) {
        targetList.append(e);
    });

    populateEmptyLists();

    $('#modal_student_list').dialog("close");

    updateCounts();
}

launchModal = function (type) {
    $('#hidden-assignment-type').val(type);
    initGroupEditPicklist();

    modalTitle = "Add/Remove ";

    // Clean up some of the stuff in the modal (switch "Students" to "TAs" or whatever, etc.)
    switch (type) {
        case 'students':
            $('#chosen-header').html('Selected Students');
            $('#available-header').html('Available Students');
            $('#picklist-fancy-filter_filters').show();
                $("#modal_student_list .count-text").html("Total students selected: <span id='current_assigned_count'>0</span>");
            modalTitle += "Students";
            break;
        case 'tas':
            $('#chosen-header').html('Selected Teaching Assistants');
            $('#available-header').html('Available Teaching Assistants');
                $("#modal_student_list .count-text").html("Total teaching assistants selected: <span id='current_assigned_count'>0</span>");
            $('#picklist-fancy-filter_filters').show();
            modalTitle += "Teaching Assistants";
            break;
        case 'instructors':
            $('#chosen-header').html('Selected Instructors');
            $('#available-header').html('Available Instructors');
                $("#modal_student_list .count-text").html("Total instructors selected: <span id='current_assigned_count'>0</span>");
            $('#picklist-fancy-filter_filters').hide();
            modalTitle += "Instructors";
            break;
    }

    $('#modal_student_list').dialog({
        modal: true,
        width: 750,
        title: modalTitle
    });

    return false;
}

updateCounts = function () {
    $('#student_count').html($('#group_students').find('option[value!="0"]').length);
    $('#instructor_count').html($('#group_instructors').find('option[value!="0"]').length);
    $('#ta_count').html($('#group_tas').find('option[value!="0"]').length);
}

alphabetizeOptions = function (list) {
    var options = $(list).find("option");

    options.sort(function (a, b) {
        if ($(a).attr("data-lastname").toLowerCase() > $(b).attr("data-lastname").toLowerCase()) return 1;
        else if ($(a).attr("data-lastname").toLowerCase() < $(b).attr("data-lastname").toLowerCase()) return -1;
        else return 0
    })

    $(list).empty().append(options);
}