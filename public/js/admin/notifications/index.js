$(function() {
    //Generate a preview of the notification
    $("#preview-notification").click(function(e){
        e.preventDefault();

        //Get form parameters to send to server
        var notificationParams = {
            notificationType: $("input:radio[name='type']:checked").val(),
            title: $("#title").val(),
            message: $("#message").val()
        };

        //To ensure that the notifications are rendered the same, go to the server to the HTML for this notification
        $.post("/admin/notifications/generate-notification-preview", notificationParams, function(response){

            //Remove any prior notifications so only the previewed notification is displayed
            $(".notification-popup-container-main").empty().html(($(response).fadeIn()));

            //Add a special clickhandler to remove the "preview" notification
            $(".notification-popup-container-main").find(".notification-popup-delete").click(function(e){
                e.preventDefault();
                $(this).parents(".notification-popup-container").remove();
            });
        }, "json")
    });

    //slideToggle the students section when checked
    $("#students").on("change", function() {
        if ($(this).prop("checked")) {
            $("#students-section").slideToggle(300);
        } else {
            $("#students-section").slideToggle(300);
        }
    });

    //slideToggle the instructors section when checked
    $("#instructors").on("change", function() {
        if ($(this).prop("checked")) {
            $("#instructors-section").slideToggle(300);
        } else {
            $("#instructors-section").slideToggle(300);
        }
    });

    //submit the form on button click
    $("#post-button").button().click(function(e) {
        e.preventDefault();
        $(this).parents("form").submit();
    });

    //cancel form submition, html redirects back to history page
    $("#cancel-button").button();


});

