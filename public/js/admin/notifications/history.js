$(function () {
    var loadMoreCell = $(".load-more-cell");
    var offset = parseInt(loadMoreCell.attr("data-offset"));
    var limit = 30;

    //Ajax function load more notifications on cell click
    loadMoreCell.click(function() {
        $.post("/admin/notifications/load-more-notifications", {offset: offset, limit: limit}, function (response) {
            //initially hide the JSON response HTML and then add fade in effect
            $(response.html).hide().appendTo(".notification-table-body").fadeIn();
            //increment the offset on each load
            offset += 30;
            //hide the load more cell once there are no more notifications to load
            if (response.count < limit) {
                loadMoreCell.hide();
            }
        });
    });

    // turn active checkboxes into sliders
    $(".active-toggle").each(function(i, el) {
        $(el).sliderCheckbox({onText: "Active", offText: "Inactive", "width": 50});
    });

    //Make it so toggling the active/inactive switch changes the status of the notification
    $("input.active-toggle").change(function(e) {
        // block the row
        var row = $(this).parents("tr");
        blockUi(true, row, "no-msg");

        var pieces = $(this).prop("id").split("_");
        var notificationId = pieces[1];
        if ($(this).attr("checked") == "checked") {
            var active = 1;
        } else {
            var active = 0;
        }

        $.post("/admin/notifications/toggle-notification", {"notification_id" : notificationId, "active" : active },
            function(response) {
                blockUi(false, row);
            }
        );

    });
});