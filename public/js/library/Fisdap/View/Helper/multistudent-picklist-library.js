var students;
students = {};


function initFancyFilter() {
    // create the filter
    $("#picklist-fancy-filter").fancyFilter({
        width: 650,
        closeOnChange: false,
        onFilterSubmit: function (e) {
            data = getFilterData();
            return $.post("/ajax/get-filtered-student-picklist",
                data,
                function (response) {
                    students = response;
                    filterList($(".available-list"), $(".search-list").val());
                    updateFilterHeader();
                }, "json").done();
        }
    });

    getFilteredStudents();
}

function getFilteredStudents() {
    data = getFilterData();
    $.post("/ajax/get-filtered-student-picklist",
        data,
        function (response) {
            students = response;
        }, "json");

    return;
}

// returns a keyed object of chosen settings from the filter form
function getFilterData() {
    certLevels = [];
    $('input[name="certificationLevels[]"]:checked').each(function () {
        certLevels.push($(this).val());
    });

    gradStatus = [];
    $('input[name="graduationStatus[]"]:checked').each(function () {
        gradStatus.push($(this).val());
    });

    data = {
        'graduationMonth': $("#graduationMonth option:selected").val(),
        'graduationYear': $("#graduationYear option:selected").val(),
        'section': $("#section option:selected").val(),
        'certificationLevels': certLevels,
        'graduationStatus': gradStatus,
        'longLabel': $("#longLabel").val()
    };

    // add the session namespace, if we're tracking it
    if ($("input[name='session_namespace']").length > 0) {
        data['sessionNamespace'] = $("input[name='session_namespace']").val();
    }

    return data;
}

// buttonizes and chosen-ifies the buttons and selects in the filter and the submit/cancel buttons
function initPicklistButtons() {
	// initialize buttons
	$(".picklist-control-buttons").find("a").button();
	$("#graduationMonth").addClass("chzn-select").css("width", "85px").chosen();
	$("#graduationMonth_chzn").css("margin-right", "1em");
	$("#graduationYear").addClass("chzn-select").css("width", "85px").chosen();
	$("#section").addClass("chzn-select").css("width", "240px").chosen();
	// hide the group label for "Any group"
	$("#section_chzn").find(".group-result:first").remove();
	$("#section_chzn").find(".active-result:first").removeClass('group-option');

	$(".picklist-ms-picker").find("select").focus(function () {
		$(this).addClass("fancy-focus");
	});
	$(".picklist-ms-picker").find("input").focus(function () {
		$(this).addClass("fancy-focus");
	});
	$(".picklist-ms-picker").find("select").blur(function () {
		$(this).removeClass("fancy-focus");
	});
	$(".picklist-ms-picker").find("input").blur(function () {
		$(this).removeClass("fancy-focus");
	});

    // initiate the cancel/confirm buttons if those exist
    $('#cancel_modal').button();
    $('#submit_button').button();
}

// catch-all for specialized styling
function setPicklistStyling() {
    // if this is a mobile device, add special styling
    if (isWebkitMobile()) {
        $(".picklist-ms-picker").addClass("mobile-multiselect");
    }
}

// sets up the student list(s) according to mode (single or multiple)
function initStudentLists() {
    // sort the options if in multiple mode
    $(".picklist-ms-picker .available-list:visible").find("option").sort(sortList).appendTo($(".picklist-ms-picker .available-list"));

    // set select element to .chosen() plugin if in single mode
    $(".picklist-ms-select .available-list").css('width', '650px').chosen();
}

// initialize the available list search
function initSearch() {
    $(".search-list").keyup(function () {
        filterList($("." + $(this).attr("data-listtosearch")), $(this).val());
    });
}

// initialize the control buttons so they move users between lists
function initControlButtons() {
    $(".picklist-control-buttons").find("a").click(function (e) {
		e.preventDefault();
        clickControlButton($(this));
	});
}

// what happens when a control button is clicked
function clickControlButton(button) {
    var action = $(button).attr("data-controlfunction");
    if (action == "add" || action == "addAll") {
        moveOptions(action, $(".available-list:visible"), $(".chosen-list"));
    } else if (action == "remove" || action == "removeAll") {
        moveOptions(action, $(".chosen-list"), $(".available-list:visible"));
    }
}

// sort list by last name, first name
function sortList(a, b) {
    a_arr = a.innerHTML.toLowerCase().split(",");
    a_name = a_arr[0].split(" ");
    a_last_first = a_name[a_name.length - 1] + a_name.join(" ");

    b_arr = b.innerHTML.toLowerCase().split(",");
    b_name = b_arr[0].split(" ");
    b_last_first = b_name[b_name.length - 1] + b_name.join(" ");

    if (a.innerHTML == 'NA' || a_last_first > b_last_first) {
        return 1;
    } else if (b.innerHTML == 'NA' || b_last_first > a_last_first) {
        return -1;
    } else {
        return 0;
    }

}

function disableOption(option) {
    option.attr("disabled", "disabled");
}

function createOption(val, label, selected) {
    var thisOption = "<option value='" + val + "' data-studentId='" + val + "'";
    if (selected) {
        thisOption += "selected='selected'";
    }
    thisOption += ">" + label + "</option>";
    return thisOption;
}

function moveOptions(action, fromList, toList) {
    // loop through each of the options on the first list
    fromList.find("option").each(function () {
        // figure out if we need to move this particular option
        var addThis = false;
        if (action.indexOf("All") != -1 && !$(this).attr("disabled")) {
            addThis = true;
        }
        if ($(this).attr("selected")) {
            addThis = true;
        }

        if (addThis) {
            // add the option
            if (action.indexOf("add") != -1) {
                disableOption($(this));
                toList.append(createOption($(this).attr("data-studentId"), $(this).text()));
            } else {
                // remove the option
                var changingOption = $(this);

                // if this option was disabled on the other list, re-enable it
                toList.find("option[data-studentId='" + changingOption.attr("data-studentId") + "']").each(function () {
                    $(this).attr("disabled", false);
                });
                changingOption.remove();
            }
        }
    });

    // unselect everything
    toList.find("option:selected").removeAttr("selected");
    fromList.find("option:selected").removeAttr("selected");

    // resort the "to" list
    toList.find("option").sort(sortList).appendTo(toList);

    updateAssignedCount();
    updateAssignedField();
}

// update the text on the filter that summarizes which filter options have been selected
function updateFilterHeader() {

    var statusDescriptions = getCheckboxDescriptions("graduationStatus", false);
    var certLevelDescriptions = getCheckboxDescriptions("certificationLevels", true);
    var gradLevelDescriptions = getGradLevelDescriptions();
    var sectionDescriptions = getSectionDescriptions();

    var newText = statusDescriptions + " " + certLevelDescriptions + " " + sectionDescriptions + " " + gradLevelDescriptions;

    if(newText.trim() == 'students students'){
        newText = 'Students';
    }

    $("#picklist-fancy-filter_filters-title-text").text("Filters: " + newText);
}

// get the text string for which student groups are selected in the filter
function getSectionDescriptions() {
    var text = "";
    var section = $("#section option:selected").text();

    if (section != "Any group") {
        text += "in " + section;
    }

    return text;
}

// get the text string for which graduation dates are selected in the filter
function getGradLevelDescriptions() {
    var month = $("#graduationMonth option:selected").text();
    var year = $("#graduationYear option:selected").text();
    var text = "graduating in ";

    if (month != "Month") {
        text += month + " ";
    }

    if (year != "Year") {
        text += year;
    }

    if (text == "graduating in ") {
        text = "";
    }

    return text;
}

// get the text string for which of a given group of checkboxes are selected in the filter
function getCheckboxDescriptions(inputName, plural) {
    var hasSomeChecked = false;
    var checkedVals = [];
    var finalText = "";

    $('input[name="' + inputName + '[]"]:checked').each(function () {

        hasSomeChecked = true;
        var label = "";
        var searchingFor = inputName + "-" + $(this).val();

        $(this).parent().find("label").each(function () {
            if ($(this).attr("for") == searchingFor) {
                label = $(this).text();
                if (plural) {
                    label += "s";
                }
            }
        });

        if (label != "EMTs" && label != "AEMTs") {
            //console.log("before: " + label);
            label.toLowerCase();
            //console.log("after: " + label);
        }

        checkedVals.push(label);
    });

    if (hasSomeChecked) {
        var count = 0;
        $.each(checkedVals, function (index, value) {
            if (count != 0) {
                finalText += "/";
            }
            finalText += value;
            count++;

        });
    }

    if (finalText == "") {
        finalText = "students";
    }

    return finalText;
}

// update the user-visible count of students selected (if enabled)
function updateAssignedCount() {
    $('#current_assigned_count').html($('#chosen-list').find('option').length);
}

// update the hidden field that contains a comma-separated list of student IDs.
// this is useful for cases where the helper is part of a form that submits, or for jQuery form.serialize()
function updateAssignedField() {
    var assignedIDs = []
    $('#chosen-list').find('option').each(function (i, elem) {
        assignedIDs.push($(elem).data('studentid'));
    });
    $("input[name='multistudent_picklist_selected']").val(assignedIDs.join(','));
    $("input[name='multistudent_picklist_selected']").data("legible-value", assignedIDs.length);
}

// search, sort and count the list
function filterList(list, searchTerm) {
    var selectedOption = "";
    list.each(function (i, el) {
        selectedOption = $(el).val();

        // first remove all the options
        $(el).find("option").each(function () {
            $(this).remove();
        });

        var numStudents = 0;
        // loop through the students, adding them if appropriate
        $.each(students, function (index, value) {

            // if there's a search term, make sure the value matches
            if ($(el).prop('multiple')) {
                var testedSearchTerm = searchTerm.toLowerCase();
                var testedValue = value.toLowerCase();

                // this search term is found, add this student
                if (testedValue.indexOf(testedSearchTerm) != -1) {
                    $(el).append(createOption(index, value));

                    // if the student is already chosen, disable the "available" option
                    $(".chosen-list").find("option[data-studentId='" + index + "']").each(function () {
                        if ($(el).attr("multiple") == "multiple") {
                            disableOption($(el).find("option").last());
                        }
                    });
                }
            } else {
                // add it when no searchTerm (single select mode)
                var selectedValue = (selectedOption == index);
                $(el).append(createOption(index, value, selectedValue));
            }

            numStudents++;
        });

        // once we've added all the students, sort the list
        $(el).find("option").sort(sortList).appendTo($(el));

        // if we're in single select mode, count the number of students and add that to the list
        if ($(el).prop('multiple') != true) {
            $(el).prepend('<option data-studentid="" value="">Choose from ' + numStudents + ' students</option>');
            // and tell the jQuery .chosen plugin that the list has CHANGED
            $(el).trigger("liszt:updated");
        }
    });

}

// switch the picker between single and multiple mode
function togglePicklistMode(mode) {
    $("input[name='picklist_mode']").val(mode);

    if (mode == 'single') {
        $(".picklist-ms-picker").hide();
        $(".picklist-ms-select").fadeIn();
        $(".anon-checkbox-container").hide();
    } else {
        $(".picklist-ms-picker").fadeIn();
        $(".picklist-ms-select").hide();
        $(".anon-checkbox-container").fadeIn();
    }
}

// show or hide the message about hidden students based on whether ot any students have been hidden
function toggleHiddenStudentsNotice(hidden_students_count) {
    // show the info message if there are some students who have been hidden
    if (hidden_students_count > 0) {
        $(".hidden_students").slideDown();
    } else {
        $(".hidden_students").slideUp();
    }
}

// toggle the div explaining why some students are hidden
function initHiddenStudentMessageToggle() {
    // toggle the div explaining why some students are hidden
    $("#hiddenStudentLink").click(function (e) {
        e.preventDefault();
        if ($("#hiddenStudentInfo").css('display') == 'none') {
            $("#hiddenStudentInfo").slideDown();
        } else {
            $("#hiddenStudentInfo").slideUp();
        }
    });
}
