/**
 * Created by khanson on 1/14/16.
 */
$(document).ready(function () {
    $("input[type=submit]").button();
    $("#setup-new").button();

    $(".action-button a").button();
    $("#continue-setup").button("disable");

    // do stuff when the user selects a user
    $(".checkmark-table tr").click(function() {
        var username = $(this).attr("data-rowvalue");
        $("#continue-setup").button("enable");
        $("#continue-setup span").text("Use "+username);
    });

    // do stuff when user continues with chosen user
    $("#continue-setup").click(function(e) {
        var username = $("input[name=selected-row]").val();
        window.location = "/account/lti/login-form/username/"+username;
    });
});