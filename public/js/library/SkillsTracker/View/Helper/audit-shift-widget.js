//Javascript for SkillsTracker_View_Helper_AuditShiftWidget

$(function() {
	var audited = false;
	
    //Dialog to ask if we should lock the shift before auditing
    $("#lockedDialog").dialog({
            autoOpen: false,
			resizable: false,
			modal: true,
			buttons: {
				Cancel: function() {
					$( this ).dialog( "close" );
				},
                "Lock": function() {
					audited = true;
					$( this ).dialog( "close" );
                    auditShift(1);
				}
			},
			close: function (event, ui) {
				if (!audited) {
					$("#audit").removeAttr('checked');
				}
			}
		});
    
    //Bind onChange event to the audit checkbox
    $("#audit").change(function() {
        
        //If this shift is not locked, prompt the user before auditing the shift
        if ($("#locked").val() == 0) {
            $("#lockedDialog").dialog("open");
        } else {
            auditShift(0);
        }
        
    });
});

function auditShift(locked)
{
    if ($("#audit").is(":checked")) {
        var audit = 1;
    } else {
        var audit = 0;
    }
    var shiftId = $("#shiftId").val();
        
    $("#audit").attr('disabled', 'disabled');
    $.post("/skills-tracker/shifts/audit-shift", {"shiftId" : shiftId, "audit" : audit, "lockedFlag" : locked},
           function(response) {
            $("#audit").removeAttr('disabled');
            $("#locked").val(response);
           });
}