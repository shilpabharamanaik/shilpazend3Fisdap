
/**
 * Created by khanson on 1/15/16.
 */
$(function () {
    $("select.chzn-select").chosen();
    $(".selectDate").datepicker();
    var email = $("#email").val();
    updateGravatar(email);

    // if the user changes their email, update the gravatar
    $("#email").change(function (e) {
        var email = $(this).val();
        updateGravatar(email);
    });
});

// update the gravatar image and link
function updateGravatar(email) {
    blockUi(true, $("#gravatar"), "throbber");
    $.post("/ajax/get-gravatar",
        {"email": email},
        function (response) {
            $("#gravatar").html(response);
            blockUi(false, $("#gravatar"));
        }, "json");
}