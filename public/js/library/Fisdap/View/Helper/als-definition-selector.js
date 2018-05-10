$(document).ready(function () {
    initAlsSelectors();
});

function initAlsSelectors() {
    $('#als-def-radio').buttonset();

    // We default to using the selected definition on load.
    if ($("input[name=als-type]:checked").val() == "als_skill") {
        $('#fisdap-definition-description').hide();
        $('#california-definition-description').hide();
    } else if ($("input[name=als-type]:checked").val() == "california") {
        $('#fisdap-definition-description').hide();
        $('#california-definition-description').show();
        $('#als-skill-definition-description').hide();
    }
    else {
        $('#als-skill-definition-description').hide();
        $('#california-definition-description').hide();
    }

    $('#als-type-fisdap').click(function () {
        $('#fisdap-definition-description').show();
        $('#als-skill-definition-description').hide();
        $('#california-definition-description').hide();
    });

    $('#als-type-als').click(function () {
        $('#fisdap-definition-description').hide();
        $('#als-skill-definition-description').show();
        $('#california-definition-description').hide();
    });

    $('#als-type-ca').click(function () {
        $('#fisdap-definition-description').hide();
        $('#als-skill-definition-description').hide();
        $('#california-definition-description').show();
    });

    // when a goalset is picked, update the definition summary
    $("#goalset").change(function() {
        blockUi(true, $("#goalset-definition-selector"), "no-msg");
        var selectedGoalset = $(this).val();
        $.post('/ajax/update-goalset-definitions-summary', {"selectedGoalset": selectedGoalset},
            function (response) {
                if (response.success) {
                    $("#goalset-definitions").html(response.html);
                }
                blockUi(false, $("#goalset-definition-selector"));
            }
        );
    });
}
