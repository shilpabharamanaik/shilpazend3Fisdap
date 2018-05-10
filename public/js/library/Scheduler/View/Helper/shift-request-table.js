$(function(){

    //Chunk of code needed to make :contains case insensitive
    $.expr[":"].contains = $.expr.createPseudo(function(arg) {
        return function( elem ) {
            return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
        };
    });

    //Show/hide requirements based on search field
    $("#request_search").fieldtag().keyup(function(){
        $("#pending").find("tbody tr").show();

        if ($(this).val() != "") {

            $("#pending").find("tbody tr").hide();
            $("#pending").find("tbody tr td div.person:contains('" + $(this).val() + "')").parents('tr').show();
        }
        toggleApprovalButtons()
    });

    $(".checkbox-toggle").click(function(e){
        e.stopPropagation();
        toggleApprovalButtons();
    });

    function toggleApprovalButtons() {
        var numChecked = $(".checkbox-toggle:visible:checked").length;
        if (numChecked > 0) {
            $("#approve-requests").button( "option", {label: "Approve " + numChecked + " request" + (numChecked > 1 ? "s" : ""), disabled: false});
            $("#deny-requests").button( "option", {label: "Deny " + numChecked + " request" + (numChecked > 1 ? "s" : ""), disabled: false});
        } else {
            $("#approve-requests").button( "option", {label: "Approve requests", disabled: true});
            $("#deny-requests").button( "option", {label: "Deny requests", disabled: true});
        }
    }

    //Setup button bar
    $("#checkbox-selector").button().addClass("small");
    $("#approve-requests").button({ disabled: true });
    $("#deny-requests").button({ disabled: true });

    // disable controls if there are no requests
    if ($("#no-reqs").length > 0) {
        $("#controls-blocker").show();
        $("#requirement-filters-wrapper").css("opacity", ".35");
        $("#checkbox-selector").button("option", "disabled", true);
    }

    // if they click on anything other than whats inside the options menu, close it
    $('html').click(function(e) {
        var target = e.target;
        if ($(target).parents("#checkbox-selector").length == 0) {
            $("#checkbox-selector-menu").fadeOut(100);
        }
    });

    $("#checkbox-selector").click(function(e) {
        e.preventDefault();
        if ($("#checkbox-selector-menu").css("display") == "none") {
            $("#checkbox-selector-menu").fadeIn(100);
        } else {
            $("#checkbox-selector-menu").fadeOut(100);
        }
    });

    $("#checkbox-selector-menu").find("ul li").click(function(e){
        switch ($(this).text()) {
            case "All":
                $(".checkbox-toggle:visible").prop("checked", true);
                break;
            case "None":
                $(".checkbox-toggle:visible").prop("checked", false);
                break;
            default:
                $(".checkbox-toggle:visible").prop("checked", false);
                $("td.request div:contains('" + $(this).text() + "')").parents("tr").find(".checkbox-toggle").prop("checked", true);
                break;
        }
        toggleApprovalButtons();
    });

    $("input[name='type']").change(function(e){
        $("#pending").find("tbody tr").hide();

        switch($(this).val()) {
            case "all":
                $("#pending").find("tbody tr").show();
                break;
            default:
                $("td.shift." + $(this).val()).parents("tr").show();
        }
        toggleApprovalButtons();
    });

    $("#approve-requests, #deny-requests").click(function(e){
        e.preventDefault();
        var trigger = $(this);
        $("#approve-requests, #deny-requests").css("opacity", "0");
        trigger.parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='position: relative; top: .4em; right: 6em;'>");
        blockUi(true);
        var checkedRequests = getCheckedRequests();

        $.post("/scheduler/requests/approve-deny-requests", {"checkedRequests": checkedRequests, "state_id": trigger.attr("data-stateid")},
        function(response){
            $("#load-modal-throbber").remove();
            location.reload();
        }, "json");
    });

    function getCheckedRequests() {
        var requests = $("#pending tr:visible .checkbox-toggle:checked").map(function(){
            return $(this).val();
        }).get();

        return requests;
    }

    $(".request-response").button().blur();
    $(".blue-button .ui-button-text-only").css({"left" : "-2px"});

    $(".request-response").click(function(event) {
        event.preventDefault();
        var trigger = $(this);
        var requestId = trigger.attr('data-requestid');
        var stateId = trigger.attr('data-stateid');

        trigger.parent().parent().prepend("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
        trigger.parent().parent().find('span').hide();

        $.post("/scheduler/requests/generate-request-response-form", {"request_id" : requestId, "state_id" : stateId },
            function(resp) {
                $("#requestResponseDialog").html($(resp).html());
                $("#requestResponseDialog").dialog("open");
                initRequestResponseModal();
                $("#load-modal-throbber").remove();
                trigger.parent().parent().find('span').show();
            }
        );
    });

    $(".shift-request-table .clickable").click(function(event) {
        event.preventDefault();
        var trigger = $(this);
        var requestId = trigger.attr('data-requestid');

        checkbox = trigger.find(".checkbox-toggle");
        checkbox.prop("checked", !checkbox.prop("checked"));
        toggleApprovalButtons();
    });

    $(".cancel-request").click(function(event) {
        event.preventDefault();
        var trigger = $(this);
        var requestId = trigger.attr('data-requestid');
        var type = trigger.attr('data-request-type');

        trigger.parent().prepend("<img src='/images/throbber_small.gif' id='throbber'>");
        trigger.hide();

        $.post("/scheduler/requests/generate-request-cancel-modal",
            { "request_id" : requestId },
            function(resp) {
                $("#requestCancelDialog").html(resp);
                initRequestCancelModal();
                $("#requestCancelDialog").dialog("open");
                $("#requestCancelDialog").dialog("option", 'title', 'Cancel ' + type);
                $("#throbber").remove();
                trigger.show();
            }
        );
    });

    $(".swap-history").click(function(event) {
        event.preventDefault();
        var trigger = $(this);
        var requestId = trigger.attr('data-requestid');

        trigger.parent().append("<img src='/images/throbber_small.gif' id='history-throbber'>");
        trigger.hide();

        $.post("/scheduler/requests/generate-swap-history",
            { "request_id" : requestId },
            function(resp) {
                $("#history-modal-content").html($(resp));
                initSwapHistoryModal();
                $("#history-modal").dialog("open");
                $("#history-throbber").remove();
                trigger.show();
            }
        );
    });

    var visibleRows = 30;
    if ($("#completed").find("tr:visible").length > visibleRows) {
        $("#completed").find("tr").hide();
        $("#completed").find("tr:nth-child(-n+" + visibleRows + ")").show();
        $("#completed").append("<tfoot><tr><td class='load-more-cell' colspan='6'><a href='#' id='load-more'>Load more</a></td></tr></tfoot>")
    }

    $("#load-more").click(function(e){
        e.preventDefault();
        visibleRows += 30;
        $("#completed").find("tr:nth-child(-n+" + visibleRows + ")").fadeIn();
        if ($("#completed").find("tr:hidden").length == 0) {
            $(this).parents("tfoot").remove();
        }
    });

    $('#main-content').on('click','.open_history_modal',function(event) {
        event.preventDefault();
        event.stopPropagation();

        var trigger = $(this);
        var eventId = trigger.attr('data-eventid');

        trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='position: relative; top: -4px; float: right;'>");

        var history_modal = $("#history-modal");

        $.post("/scheduler/index/generate-shift-history", {"id" : eventId},
            function(resp) {
                $("#history-modal-content").html($(resp));
                history_modal.dialog("open");
                $("#load-modal-throbber").remove();
                trigger.css("opacity", 1);

                $("#historyCloseButton").button().blur().click(function(e){
                    e.preventDefault();
                    history_modal.dialog('close');
                });
            }
        );
    });
});

