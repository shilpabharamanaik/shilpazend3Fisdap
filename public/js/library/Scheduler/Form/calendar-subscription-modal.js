$(function(){

});

function initSubscriptionModal()
{
    $("#instructor").chosen();
    $(".ui-dialog").css({"overflow": "visible"});
    $(".ui-dialog .ui-dialog-content").css({"overflow": "visible"});

    $('#subscription_summary').cluetip({activation: 'click',
        local:true,
        cursor: 'pointer',
        width: 680,
        cluezIndex: 2000000,
        cluetipClass: 'jtip',
        sticky: true,
        closePosition: 'title',
        closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});

    $("#calendar-cancel-btn").button().click(function(e){
        e.preventDefault();
        $("#calendarSubDialog").dialog("close");
    });

    $("#calendar-btn").button().click(function(e){
        e.preventDefault();
        var data = {
            calendarName: $("#calendarName").val(),
            instructor: $("#instructor").val(),
            calendarId: $("#calendarId").val(),
            filters: getFilters()
        }

        $.post(
            '/scheduler/index/generate-calendar-subscription',
            data,
            function (response) {
                $("#calendarSubDialog").empty().html($(response).fadeIn());
                $("#calendar-ok-btn").button().click(function(e){$('#calendarSubDialog').dialog('close')});
                $("#subscription_url").select();
            }, "json");
    });
}