$(function () {

    $("#submit-selection").button();

    $("#cert_levels").chosen();
    $("#grad-month").attr("style", "width:80px;").addClass("update-people-description-on-change").chosen();
    $("#grad-year").attr("style", "width:80px;").addClass("update-people-description-on-change").chosen();
    $("#status").chosen();

    initFlippyDivs();

    var inactive_groups;
    if ($("#groups-optgroup-Inactive").length > 0) {
        inactive_groups = $("#groups-optgroup-Inactive");
        $("#groups-optgroup-Inactive").remove();
    }

    var active_groups;
    if ($("#groups-optgroup-Active").length > 0) {
        active_groups = $("#groups-optgroup-Active");
        $("#groups-optgroup-Active").remove();
    }

    var any_group_option = $("#groups").find("option[value='Any group']");
    $("#groups").find("option[value='Any group']").remove();

    $("#groups").append(any_group_option);
    $("#groups").append(active_groups);
    $("#groups").append(inactive_groups);

    $("#groups").chosen();

    $("#show_instructors").addClass("update-people-description-on-change").sliderCheckbox({
        onText: "On",
        offText: "Off"
    });

    $("#by-requirement-people-text").text(getPeopleFilterDescription());

    // select all/none links
    $(".select-aller").click(function (e) {
        e.preventDefault();

        var list = $(this).attr("data-list");
        var mode = $(this).attr("data-mode");
        var options = $("#" + list + "-fancy-element").find("." + list + "-fancy-option:visible");
        var selected_class = list + "-fancy-option-selected";

        if (mode == "all") {
            // we are selecting!
            options.each(function () {
                if (!$(this).hasClass(selected_class)) {
                    fancyOptionClick(list, $(this));
                }
            });
        } else {
            // we are deselecting!
            options.each(function () {
                if ($(this).hasClass(selected_class)) {
                    fancyOptionClick(list, $(this));
                }
            });
        }

        updateCounts(list);
    });

    function setDefaultVals() {
        // there is wonkiness with the student filter subform, so check to see if we are in a
        // default 'null' state and confirm that the values are what they should be
        if ($("#people-subfilter").attr("data-defaultblankstate") == "1") {
            $("#grad-month").val("0");
            $("#grad-year").val("0");
            $("#status").val("1");
            //$("#cert_levels").val("");
            $("#cert_levels").find("option").each(function () {
                $(this).removeAttr("selected");
            });
            $("#groups").val("");
            $("#groups").trigger("change");
        }
    }

    $(".update-people-description-on-change").change(function () {
        var new_description = getPeopleFilterDescription();
        $("#by-requirement-people-text").text(new_description);
    });

    // submit the selection of people/requirements to get the list of attachments
    $("#submit-selection").click(function (e) {
        e.preventDefault();
        // remove all previous errors
        $("#selection-form").find(".error").remove();
        var btn = $(this);

        var data = getSubfilterData();

        var errors = [];
        var found_error = false;

        // make sure either a requirement or a person is selected
        if (data.selection_by == "by-requirements") {
            if (data.requirement_ids.length == 0) {
                errors.push("Please select at least one requirement.");
                found_error = true;
            }
        } else {
            if (data.userContextIds.length == 0) {
                errors.push("Please select at least one person.");
                found_error = true;
            }
        }

        // show the error right away if nothing has been selected
        if (found_error) {
            appendSelectionErrors(errors);
        } else {
            // if we have a proper selection, make sure the attachment count is not too great
            // first hide the button and add the throbber
            btn.css("opacity", "0");
            $("#submit-selection-throbber").fadeIn();

            // hide the previous results
            $("#compliance-table").hide();
            $("#bad-result").hide();
            $("#edit-compliance-buttons").hide();

            $.post("/scheduler/compliance/get-attachment-count",
                data,
                function (attachment_count) {
                    if (attachment_count > 1000) {
                        errors.push("Oops! That's too much data to process at once. Please select either fewer people or fewer requirements.");
                        appendSelectionErrors(errors);
                        btn.css("opacity", "1");
                        $("#submit-selection-throbber").hide();
                    } else {
                        $.post("/scheduler/compliance/get-edit-compliance-form",
                            data,
                            function (response) {
                                // remove the previous results
                                $("#edit-compliance-status").remove();
                                $("#bad-result").remove();

                                var bad_result_div = "<div id='bad-result' class='grid_12 island withTopMargin'><h3 style='color:#666;margin-bottom:-1em;'>We're sorry.</h3><br /><div class='error'>";

                                if (response == "No user roles") {
                                    bad_result_div += "We couldn't find any students or instructors matching your filters.</div></div>";
                                    $("#selection-form").after(bad_result_div);
                                    $('html,body').animate({scrollTop: $("#bad-result").offset().top - 40}, 'slow');
                                } else if (response == "No requirements") {
                                    bad_result_div += "We couldn't find any requirements.</div></div>";
                                    $("#selection-form").after(bad_result_div);
                                    $('html,body').animate({scrollTop: $("#bad-result").offset().top - 40}, 'slow');
                                } else {

                                    $("#selection-form").after(response);
                                    initComplianceStatusForm();
                                    $('html,body').animate({scrollTop: $("#edit-compliance-title").offset().top - 40}, 'slow');
                                }

                                btn.css("opacity", "1");
                                $("#submit-selection-throbber").hide();
                            }, "json");
                    }
                }, "json");
        }
    });

    function getSubfilterData() {

        var show_instructors = 0;
        if ($("#show_instructors").attr("checked")) {
            show_instructors = 1;
        }

        var postdata = {
            certs: getChosenValues("cert_levels-element"),
            status: getChosenValues("status-element"),
            groups: getChosenValues("groups-element"),
            graduationMonth: $("#grad-month").val(),
            graduationYear: $("#grad-year").val(),
            show_instructors: show_instructors,
            all_students: $("#all").val(),
            selection_by: $("#selection-by-options").find(".ui-state-active").attr("for"),
            requirement_ids: getVisibleChosen("requirements", "requirement"),
            userContextIds: getVisibleChosen("people", "people")
        }

        return postdata;
    }

    // given an array of errors, render and display an error notice
    function appendSelectionErrors(errors) {
        var errorMarkup = "<div style='display:none;' class='error'><ul>";
        $(errors).each(function(i, el) {
            errorMarkup += "<li>"+el+"</li>";
        });
        errorMarkup += "</ul></div>";
        $('html,body').animate({scrollTop: 0}, 'slow');
        $("#selection-by-options").after(errorMarkup);
        $("#selection-form").find(".error").first().slideDown();
    }

    function getVisibleChosen(list, option) {
        var selected = $("#" + list).val();
        var visible = [];
        if (selected) {
            selected.forEach(function (selected_id) {
                if ($("#" + option + "-fancy-option-" + selected_id).is(":visible")) {
                    visible.push(selected_id);
                }
            });
        }

        return visible;
    }

    function getChosenValues(select_id) {
        var values = [];
        $("#" + select_id).find(":selected").each(function () {
            values.push($(this).val());
        });

        return values;
    }

    function getGradLevelDescriptions() {
        var month = $("#grad-month option:selected").text();
        var year = $("#grad-year option:selected").text();
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


    function getChosenDescriptions(select_id, values, starter_text, post_text) {
        var text = "";

        value_descriptions = [];
        $.each(values, function (i, v) {
            value_descriptions.push($("#" + select_id).find("option[value='" + v + "']").text());
        });


        if (values.length > 0) {
            text += starter_text + "" + value_descriptions.join("/");
        }

        return text + post_text;
    }


    function getPeopleFilterDescription() {
        return "students and instructors";
    }

    $("#selection-by-options").find("input").each(function () {

        // show "by requirements" or "by people" content
        $(this).click(function (e) {
            e.preventDefault();
            var list = $(this).attr("data-list");
            $(".selection-by-content").hide();
            $("#" + $(this).attr("id") + "-content").fadeIn();
            $("#selection-form").find(".error").first().remove();
            if ($("#bad-result").length > 0) {
                $("#bad-result").remove();
            }
            updateCounts(list);
        });

        // check for a default value
        if ($(this).attr("checked")) {
            $(this).trigger("click");
        }

    });

    // fancy inputs
    $('input[type="text"]').focus(function () {
        $(this).addClass("fancy-input-focus");
    });
    $('input[type="text"]').blur(function () {
        $(this).removeClass("fancy-input-focus");
    });

    // make some buttonsets!
    $("#selection-by-options").buttonset();
    $("#requirement-list-filters").buttonset();
    $("#people-list-filters").buttonset();

    // filter the lists
    $("#requirement-list-filters").change(function () {
        // update list
        changeListFilters("requirement", $(this));
        // search
        searchList("requirement", $("#requirement_search").val().toLowerCase());
    });
    $("#people-list-filters").change(function () {
        //update list
        changeListFilters("people", $(this));
        // search
        searchList("people", $("#people_search").val().toLowerCase());
    });

    // turn the boring select options into our fancy searchable list then sort 'em

    if ($("#requirements").find("option").length > 0) {
        $("#requirements").find("option").each(function () {
            addToFancyList($(this), "requirement");
        });
    }
    else {
        $("#requirement-fancy-element").append("<div style='width:88%;margin-left:2em;margin-top:4em;' class='info'>Your program has not set up any requirements yet. <a style='text-decoration:underline;' href='/scheduler/compliance/manage'>Manage</a> your requirements to get started.</div>");
    }

    $(".requirement-fancy-option").sort(sortList).appendTo($("#requirement-fancy-element"));

    $("#people").find("option").each(function () {
        addToFancyList($(this), "people");
    });
    $(".people-fancy-option").sort(sortList).appendTo($("#people-fancy-element"));

    // filter the lists on search
    $("#requirement_search").keyup(function () {
        //update list
        changeListFilters("requirement", $("#requirement-list-filters"));
        // search
        searchList("requirement", $(this).val().toLowerCase());
    });
    $("#people_search").keyup(function () {
        //update list
        changeListFilters("people", $("#people-list-filters"));
        // search
        searchList("people", $(this).val().toLowerCase());
    });

    // hover on a fancy option
    $(".requirement-fancy-option").hover(function () {
            fancyOptionOnHover("requirement", $(this));
        },
        function () {
            fancyOptionOffHover("requirement", $(this));
        });

    $(".people-fancy-option").hover(function () {
            fancyOptionOnHover("people", $(this));
        },
        function () {
            fancyOptionOffHover("people", $(this));
        });

    // click a fancy option
    $(".requirement-fancy-option").click(function (e) {
        e.preventDefault();
        fancyOptionClick('requirement', $(this));
        updateCounts('requirement');
    });

    $(".people-fancy-option").click(function (e) {
        e.preventDefault();
        fancyOptionClick('people', $(this));
        updateCounts('people');
    });


    function changeListFilters(list_name, filter_trigger) {

        $("." + list_name + "-fancy-option-hover").removeClass(list_name + "-fancy-option-hover");

        var selected_val = filter_trigger.find(".ui-state-active").attr("for").split("-");
        selected_val = selected_val[0];


        if (selected_val == "all") {
            $("." + list_name + "-fancy-option").show();
        }
        else {
            $("." + list_name + "-fancy-option").each(function () {
                if ($(this).hasClass(selected_val + "-" + list_name)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // recalculate the selected numbers
        updateCounts(list_name);
    }

    function addToFancyList(option_element, list_name) {
        var txt = "<span class='" + list_name + "-title'>" + option_element.text() + "</span>";
        var option_type = option_element.parent().attr("label").toLowerCase();

        var is_selected = "";
        if (option_element.attr("selected")) {
            is_selected = list_name + "-fancy-option-selected";
        }

        var type_img = "";

        if (list_name == "people") {
            if (option_type == 'instructors') {
                type_img = "<img src='/images/icons/lab-skills-instructor-icon.svg'>";
            }
            else {
                type_img = "<img src='/images/icons/student-weeble.svg'>";
            }
        }
        else {
            type_img = "<img class='fancy-option-" + option_type + "-icon' src='/images/icons/" + option_type + "-requirement.png'>";
        }

        var type_class = option_type + "-" + list_name;
        var classes = "class='" + list_name + "-fancy-option " + is_selected + " " + type_class + "'";
        var option_val = "data-optionval='" + option_element.attr("value") + "'";
        var id = list_name + "-fancy-option-" + option_element.attr("value");


        if ($("#" + id).length <= 0) {
            var fancy_option = "<div " + classes + " " + option_val + " id='" + list_name + "-fancy-option-" + option_element.attr("value") + "'>" + txt + " " + type_img + "</div>";
            $("#" + list_name + "-fancy-element").append(fancy_option);
        }
        else {
            // it already exists, add a class
            $("#" + id).addClass(type_class).append(type_img);
        }
    }

    // search the given list!
    function searchList(list_name, search_term) {
        if (search_term != "") {
            $("." + list_name + "-fancy-option").each(function () {
                var title = $(this).find("." + list_name + "-title").text().toLowerCase();
                if (title.indexOf(search_term) == -1) {
                    $(this).hide();
                }
            });
        }

        updateCounts(list_name);
    }

    function fancyOptionOnHover(list_name, fancy_option) {
        if (!fancy_option.hasClass(list_name + "-fancy-option-selected")) {
            fancy_option.addClass(list_name + "-fancy-option-hover");
        }
    }

    function fancyOptionOffHover(list_name, fancy_option) {
        fancy_option.removeClass(list_name + "-fancy-option-hover");
    }

    function fancyOptionClick(list_name, fancy_option) {
        var selected_class = list_name + "-fancy-option-selected";

        var parent_selector = "#" + list_name;

        if (list_name == "requirement") {
            parent_selector = parent_selector + "s";
        }

        var parent = $(parent_selector);

        if (fancy_option.hasClass(selected_class)) {
            // we're deselecting
            deselectOption(fancy_option, list_name);
        }
        else {
            fancy_option.addClass(selected_class);
            parent.find("option[value='" + fancy_option.attr("data-optionval") + "']").attr("selected", "selected");
            fancyOptionOffHover(list_name, fancy_option);
        }
    }

    function deselectOption(fancy_option, list_name) {
        var parent_selector = "#" + list_name;

        if (list_name == "requirement") {
            parent_selector = parent_selector + "s";
        }

        var parent = $(parent_selector);

        fancy_option.removeClass(list_name + '-fancy-option-selected');
        parent.find("option[value='" + fancy_option.attr("data-optionval") + "']").removeAttr("selected");
    }

    function updateCounts(list) {
        var options = $("#" + list + "-fancy-element").find("." + list + "-fancy-option");
        var total = 0;
        var selected = 0;

        options.each(function () {
            if ($(this).css('display') != 'none') {
                total++;
                if ($(this).hasClass(list + "-fancy-option-selected")) {
                    selected++;
                }
            }
        });

        if (list == 'requirement') {
            var plural = 'requirements';
        }

        if (list == 'people') {
            var plural = 'people';
        }

        $("#" + list + "-num-selected").html(selected);
        $("#" + list + "-num-total").html(total + " " + plural);
    }

    function sortList(a, b) {
        if (a.innerHTML == 'NA') {
            return 1;
        }
        else if (b.innerHTML == 'NA') {
            return -1;
        }
        return (a.innerHTML.toLowerCase() > b.innerHTML.toLowerCase()) ? 1 : -1;
    }

    updateCounts('requirement');
    updateCounts('people');


    if ($("#all").val() != 1) {
        $("#all_flippy").trigger("click");
        $("#people-subfilter").slideDown();
        $("#by-requirement-people-description").find("#by-requirement-people-text").text("students and instructors");
    }
    else {
        $("#by-requirement-people-description").find("#by-requirement-people-text").text("active students and instructors");
    }

    $("#all").change(function () {

        if ($(this).val() != 1) {
            // SPECIFIC
            $("#people-subfilter").slideDown();
            $("#by-requirement-people-text").text("students and instructors");

        }
        else {
            $("#people-subfilter").slideUp();
            $("#by-requirement-people-text").text("active students and instructors");
        }

    });

});
