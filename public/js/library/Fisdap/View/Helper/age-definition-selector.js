$(document).ready(function() {

    // when a goalset is picked, update the definition summary
    $("#ageGoalset").change(function() {
        blockUi(true, $("#age-definition-selector"), "no-msg");
        var selectedGoalset = $(this).val();
        $.post('/ajax/update-age-definitions-summary', {"selectedGoalset": selectedGoalset},
            function (response) {
                if (response.success) {
                    $("#age-definitions").html(response.html);
                }
                blockUi(false, $("#age-definition-selector"));
            }
        );
    });
});