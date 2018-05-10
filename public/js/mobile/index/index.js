$(function() {
    $(".shift-container a").button();
    $("#shift-list-container div.shift-container:odd").addClass('alt');
    $(".shift-buttons").hide();
    
    $(".shift-container .shift-summary").addClass("clickable").click(function(e) {
        e.stopPropagation();
        //e.preventDefault();
        
        var buttonsContainer = $("#shift-buttons-" + $(this).parent().attr('id'));
        
        if (buttonsContainer.is(":visible")) {
            buttonsContainer.hide("blind");            
        } else {
            buttonsContainer.show("blind");
            $(".shift-buttons:visible:not(#"+buttonsContainer.attr('id')+")").hide("blind");
        }
    });
    
    $("#add-shift-btn").parent().addClass('gray-button small-btn').children().first().button().click(function(e) {
        e.preventDefault();
	    initShiftModal();
        $("#shiftDialog").dialog( "option", "width", "90%");
        $("#shiftDialog").dialog("open");
    });
    
    $(".lock-shift").click(function(e) {
        e.preventDefault();
        var shiftId = $(this).parents(".shift-container").attr('id');
        loadLockModal(shiftId);
    });
});

function loadLockModal(shiftId)
{
    $.post("/skills-tracker/shifts/generate-lock-form", {"shiftId" : shiftId, "patientCareUrl" : "/mobile/patients/patient/runId/"},
        function(resp) {
         $("#lockShiftDialog").html($(resp).html());
         $("#lockShiftDialog").dialog( "option", "width", "90%");
         $("#lockShiftDialog").dialog("open");
    });
}
