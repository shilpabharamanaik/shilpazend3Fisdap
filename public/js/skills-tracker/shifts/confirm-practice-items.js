$(function(){
    initPracticeConfirmationTable();

    function filterPracticeItems(confirmed) {
        //show all shift tables so that they can be filtered
        $('.practice-item-shift-summary').show();
        $('.practice-item-confirmation').find('tr').show();

        //Loop over each checkbox and determine if the the corresponding row is confirmed
        $('.confirm-toggle').each(function(index, element) {
            if ($(element).attr('data-confirmed') == confirmed) {
                $(element).parents('tr').show();
            } else {
                $(element).parents('tr').hide();
            }

            //If all rows of a table have been hidden, hide the table too
            if ($(element).parents('table').find('tr:visible').length == 0) {
                $(element).parents('.practice-item-shift-summary').hide();
            } else {
                $(element).parents('.practice-item-shift-summary').show();
            }
        });

        //If all tables are hidden, display a null state message
        if ($('.practice-item-shift-summary:visible').length == 0) {

            //switch the text of the message depending on if we're confirming or unconfirming
            $('#confirm-descriptor').text(confirmed ? 'none' : 'all');

            $('#practice-item-null-message').fadeIn();
        } else {
            $('#practice-item-null-message').hide();
        }
    }

    $("#practice-items-go").click(function(e){
        e.preventDefault();

        var data = {
            studentId: $("select.available-list").val(),
            evaluator: $("input[name='evaluator']:checked").val()
        };

        if (data.studentId == "") {
            $("#find-student-container").prepend($("<div class='error'>Please choose a student</div>").fadeIn());
            return false;
        } else {
            $("div.error").fadeOut(400, function(){$(this).remove()});
        }

        var trigger = $(this);
        trigger.fadeOut(400, function(e){
            trigger.parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber' style='position: relative; top: 8px; left: 22px'>");
        });


        $.post('/skills-tracker/shifts/generate-practice-item-confirmation-table', data, function(response) {
            $("#practice-confirmation-container").empty().html($(response).fadeIn(400, function(e){
                initPracticeConfirmationTable();
                $("#load-modal-throbber").hide().remove();
                trigger.fadeIn(400);
            }));
        }, "json")
    });

    function initPracticeConfirmationTable() {
        var confirmationContainer = $("#practice-confirmation-container");
        if (confirmationContainer.children().length) {
            confirmationContainer.show();
        } else {
            confirmationContainer.hide();
        }

        //Make jQuery buttons
        $("#checkbox-selector").button();
        $("#confirm-items").button();
        $('#button-set-confirmedfilter').buttonset();


        toggleConfirmButton();

        filterPracticeItems(parseInt($("input[name='confirmedfilter']:checked").val()));

        //Add change handler to the confirmation buttonset
        $("input[name='confirmedfilter']").off().change(function(e){
            filterPracticeItems(parseInt($(this).val()));
            toggleConfirmButton();
        });

        //Make the entire table row trigger the checkbox
        $(".practice-item-confirmation tr.clickable").off().click(function(event) {
            event.preventDefault();
            var trigger = $(this);

            var checkbox = trigger.find(".confirm-toggle");
            checkbox.prop("checked", !checkbox.prop("checked"));
            toggleConfirmButton();
        });

        //Keep the checkbox click from bubbling
        $(".confirm-toggle").off().click(function(e){
            e.stopPropagation();
            toggleConfirmButton();
        });

        // if they click on anything other than whats inside the options menu, close it
        $('html').click(function(e) {
            var target = e.target;
            if ($(target).parents("#checkbox-selector").length == 0) {
                $("#checkbox-selector-menu").fadeOut(100);
            }
        });

        $("#checkbox-selector").off().click(function(e) {
            e.preventDefault();
            if ($("#checkbox-selector-menu").css("display") == "none") {
                $("#checkbox-selector-menu").fadeIn(100);
            } else {
                $("#checkbox-selector-menu").fadeOut(100);
            }
        });

        $("#checkbox-selector-menu").find("ul li").off().click(function(e){
            switch ($(this).text()) {
                case "All":
                    $(".confirm-toggle:visible").prop("checked", true);
                    break;
                case "None":
                    $(".confirm-toggle:visible").prop("checked", false);
                    break;
                default:
                    $(".confirm-toggle:visible").prop("checked", false);
                    $("td.success-cell:visible:contains('" + $(this).text() + "')").parents("tr").find(".confirm-toggle").prop("checked", true);
                    break;
            }
            toggleConfirmButton();
        });

        $("#confirm-items").off().click(function(e) {
            e.preventDefault();

            var items = getCheckedItems();
            var confirmed = !parseInt($("input[name='confirmedfilter']:checked").val());

            var data = {
                itemIds: items,
                confirmed: confirmed
            };

            blockUi(true);
            $.post("/skills-tracker/shifts/confirm-practice-items-ajax", data, function(response){
                location.reload();
            }, "json")
        });
    }

    //Toggle enabling/disabling of the confirm button along with changing the text based on checked items
    function toggleConfirmButton() {
        var numChecked = $(".confirm-toggle:visible:checked").length;
        var confirmVerb = parseInt($("input[name='confirmedfilter']:checked").val()) ? "Unconfirm" : "Confirm";
        if (numChecked > 0) {
            $("#confirm-items").button( "option", {label: confirmVerb + " " + numChecked + " item" + (numChecked > 1 ? "s" : ""), disabled: false});
        } else {
            $("#confirm-items").button( "option", {label: confirmVerb + " items", disabled: true});
        }
    }

    //Grab all visible checked items and return them as an array
    function getCheckedItems() {
        return $("table.practice-item-confirmation tr:visible .confirm-toggle:checked").map(function(){
            return $(this).val();
        }).get();
    }
});