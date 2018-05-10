$(function(){
    // edit email
    $(".edit-recurring-email").button().click(function(event) {
        event.preventDefault();
        var trigger = $(this);
        trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-pdf-modal-throbber'>");

        var mode = 'edit';

        $.post("/scheduler/pdf-export/generate", {"email_id": trigger.attr("data-emailid")},
            function(resp) {
                $("#pdfDialog").html($(resp).html());
                initPdfModal(trigger.attr("data-emailViewType"), mode);
                $("#pdfDialog").dialog("open");
                $("#load-pdf-modal-throbber").remove();
                trigger.css("opacity", "1");
            });
    });

    // delete email
    $(".delete-recurring-subscription").click(function(event) {
        event.preventDefault();
        var trigger = $(this);
//        trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-delete-modal-throbber'>");

        var calendarId = trigger.attr("data-calendarid");
        initDeleteEmailModal(calendarId);
        $("#calendarId").val(calendarId);
        $("#deleteCalendarName").text(trigger.attr("data-calendarName"));
        $("#deleteSubscriptionDialog").dialog("open");

     /*   $.post("/scheduler/emails/generate-delete-email", {"email_id": trigger.attr("data-emailid")},
            function(response) {
                $("#deleteRecurringEmailDialog").html(response);
                initDeleteEmailModal();
                $("#deleteRecurringEmailDialog").dialog("open");
                $("#load-delete-modal-throbber").remove();
                $(trigger).css("opacity", "1");
            });*/
    });

    // click headers
    $("div.subscription-header").click(function(event) {
        var id = $(this).attr('id').substring(7);
        var infoRow = $("#info-"+id);
        var arrow = $("#arrow-"+id);
        var row = $(this).parent();

        if ($(infoRow).css('display') == 'none') {
            $(infoRow).slideDown();
            $(arrow).attr("src", "/images/accordion_arrow_down.png");
            $(row).addClass("accordion-header-selected");
        } else {
            $(infoRow).slideUp();
            $(arrow).attr("src", "/images/accordion_arrow_right.png");
            $(row).removeClass("accordion-header-selected");
        }
    });

    // click for details
    $(".clickable").click(function(event) {
        var list = $(this).find('ul');
        var sign = $(this).find('img.clickable');

        if ($(list).css('display') == 'none') {
            $(list).slideDown();
            $(sign).attr("src", "/images/icons/minus.png");
        } else {
            $(list).slideUp();
            $(sign).attr("src", "/images/icons/plus.png");
        }
    });

    // turn delete confirmation div into a modal
    $("#deleteSubscriptionDialog").dialog({
        modal: true,
        autoOpen: false,
        resizable: false,
        width: 600,
        title: "Delete Calendar Subscription",
        open: function (){
            $("#cancel-delete-btn").blur();
        }
    });

});

function initDeleteEmailModal(calendarId) {
    $("#cancel-delete-btn").button().click(function(e){
        e.preventDefault();
        $("#deleteSubscriptionDialog").dialog('close');
    });

    $("#confirm-delete-btn").button().click(function(e){
        e.preventDefault();

        var cancelBtn = $('#cancel-delete-btn').hide();
        var confirmBtn = $(this).hide();
        var throbber =  $("<img id='deleteModalThrobber' src='/images/throbber_small.gif'>");
        confirmBtn.parent().append(throbber);
        $.post("/scheduler/settings/process-delete-subscription",
            { "calendarId" : calendarId },
            function (response) {
                if (response) {
                    window.location.reload(true);
                } else {
                    $("#deleteSubscriptionDialog").html("<div class='alert'>You are not authorized to delete this email.</div>");
                    cancelBtn.find(".ui-button-text").html("Ok");
                    cancelBtn.show();
                    $(throbber).remove();
                }
            }
        )
    });
}
