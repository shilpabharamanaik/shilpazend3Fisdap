
$(function(){
    //after a delay, remove the success alert message if there is one
    if($(".success").length != 0){
        $(".success").delay(7000).fadeOut();
    }

    $("#student-table").tablesorter();

    $("#filter-content").fancyFilter({
        width: 525,
        closeOnChange: false,
        onFilterSubmit:
            function(e) {
                return $.post("/learning-center/index/get-submit-scores-form",
                    getFiltersPostData(),
                function(response){
                    getSubmitScoresForm(response);
                }, "json").done();

            }
    });

    function getSubmitScoresForm(response){
        $("#submit-scores-form").empty();

        $("#submit-scores-form").append(response);

        $("#student-table").tablesorter();

        $("#filter-content").fancyFilter({
            width: 525,
            closeOnChange: false,
            onFilterSubmit:
                function(e) {
                    return $.post("/learning-center/index/get-submit-scores-form",
                        getFiltersPostData(),
                        function(response){
                            getSubmitScoresForm(response);
                        }, "json").done();

                }
        })

        var inactive_groups;
        if ($("#groups-optgroup-Inactive").length > 0) {
            inactive_groups = $("#groups-optgroup-Inactive").clone();
            $("#groups-optgroup-Inactive").remove();
        }

        var active_groups;
        if ($("#groups-optgroup-Active").length > 0) {
            active_groups = $("#groups-optgroup-Active").clone();
            $("#groups-optgroup-Active").remove();
        }

        var any_group_option = $("#groups").find("option[value='Any group']").clone();
        $("#groups").find("option[value='Any group']").remove();

        $("#groups").append(any_group_option);
        $("#groups").append(active_groups);
        $("#groups").append(inactive_groups);

        var non_selected = true;
        $("#groups").find("option").each(function(){
            if($(this).is(':selected')){
                non_selected = false;
            }
        });

        $("#groups").chosen();

        $("#grad-month").css("width", "92px").chosen();
        $("#grad-year").css("width", "92px").chosen();

        updateFilterHeader();

        $('input[type=submit].green-buttons').button().css('padding', '3px 10px').parent().addClass('green-buttons');

    }

    var inactive_groups;
    if ($("#groups-optgroup-Inactive").length > 0) {
        inactive_groups = $("#groups-optgroup-Inactive").clone();
        $("#groups-optgroup-Inactive").remove();
    }

    var active_groups;
    if ($("#groups-optgroup-Active").length > 0) {
        active_groups = $("#groups-optgroup-Active").clone();
        $("#groups-optgroup-Active").remove();
    }

    var any_group_option = $("#groups").find("option[value='Any group']").clone();
    $("#groups").find("option[value='Any group']").remove();

    $("#groups").append(any_group_option);
    $("#groups").append(active_groups);
    $("#groups").append(inactive_groups);

    var non_selected = true;
    $("#groups").find("option").each(function(){
        if($(this).is(':selected')){
            non_selected = false;
        }
    });

    $("#groups").chosen();


    $("#grad-month").css("width", "92px").chosen();
    $("#grad-year").css("width", "92px").chosen();

    updateFilterHeader();

    function getFiltersPostData() {
        var postdata = {
            graduationMonth: $("#grad-month").val(),
            graduationYear: $("#grad-year").val(),
            section: $("#groups-element").find(":selected").val()
        }

        cert_array = [];
        $("input[name='certificationLevels[]']").each(function(i,e){
                if($(e).is(":checked"))
                    cert_array.push($(e).val());
            }
        )

        if (cert_array.length > 0) {
            postdata["certificationLevels"] = cert_array;
        }

        status_array = [];
        $("input[name='status[]']").each(function(i,e){
                if($(e).is(":checked"))
                    status_array.push($(e).val());
            }
        )

        if (status_array.length > 0) {
            postdata["gradStatus"] = status_array;
        }

        return postdata;
    }

    function updateFilterHeader() {

        var postdata = getFiltersPostData();

        // Showing active AEMT/EMT/Paramedic students in EMT Day Class graduating in May 2012

        var certs = postdata.certificationLevels;
        var cert_descriptions = [];

        if (certs) {
            var length = certs.length;
            for (var i = 0; i < length; i++) {
                $("input[name='certificationLevels[]']").each(function(){
                    if ($(this).attr("value") == certs[i]) {
                        cert_descriptions.push($(this).parent().text() + "s");
                    }
                });

            }
        }

        /// cert level descriptions
        var cert_txt = "";
        if (cert_descriptions.length > 0) {
            cert_txt = cert_descriptions.join("/");
        }
        else {
            cert_txt = "students";
        }

        var statuses = postdata.gradStatus;
        var status_descriptions = [];

        if (statuses) {
            var st_length = statuses.length;
            for (var i = 0; i < st_length; i++) {
                $("input[name='status[]']").each(function(){
                    if ($(this).attr("value") == statuses[i]) {
                        status_descriptions.push($(this).parent().text());
                    }
                });

            }
        }

        /// status descriptions
        var status_txt = "";
        if (status_descriptions.length > 0) {
            status_txt = status_descriptions.join("/");
        }
        else {
            status_txt = "all";
        }

        var gradLevelDescriptions = getGradLevelDescriptions();
        var sectionDescriptions = getSectionDescriptions();
        var newText = status_txt + " " + cert_txt + " " + sectionDescriptions + " " + gradLevelDescriptions;
        $("#filter-content_filters-title-text").text("Filters: " + newText);
    }

    function getSectionDescriptions() {

        var text = "";
        var section = $("#groups option:selected").text();

        if (section != "Any group") {
            text += "in " + section;
        }

        return text;

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

});