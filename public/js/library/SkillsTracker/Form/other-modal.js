//Javascript for SkillsTracker_Form_OtherModal

$(document).ready(function() {
    
    // init the dialog
    $("#otherDialog").dialog({
        tabPosition:'top',
        modal:true,
        autoOpen:false,
        resizable: false,
		width: 800,
		title: 'Other Interventions',
		open: function(){
            
            if ($("#otherDialog").parent().find(".green-buttons").length <= 0) {
                $("#otherDialog").parent().find("#save-btn").wrap("<span class='green-buttons'></span>");
            }
            
        },
        buttons: [
            {
                text: "Cancel",
                className: "gray-button",
                click: function() { $(this).dialog('close'); }
            },
            {
                text: "Save",
                id: "save-btn",
                className: "green-buttons small",
                click: function(){
                    saveOtherModal();
                }
            }
        ]
    });
    
    var otherProc = $("#otherProcedure");
    otherProc.live('change', function() {
        toggleOtherSuccess($(this).val());
        toggleOtherSize($(this).val());
        toggleOtherAttempts($(this).val());
    });
});

function initOtherModal()
{
    toggleOtherSuccess($("#otherProcedure").val());
    toggleOtherSize($("#otherProcedure").val());
    toggleOtherAttempts($("#otherProcedure").val());
}

function toggleOtherSuccess(value)
{
    var successValues = [3,8,41,47];

    if ($.inArray(parseInt(value), successValues) >= 0) {
        $("input[name=otherSuccess]").parents(".form-prompt").show();
    } else {
        $("input[name=otherSuccess]").parents(".form-prompt").hide();
    }
}

function toggleOtherSize(value)
{
    var sizeValues = [3,47];
    
    if ($.inArray(parseInt(value), sizeValues) >= 0) {
        $("#otherSize").parents(".form-prompt").show();
    } else {
        $("#otherSize").parents(".form-prompt").hide();
    }
}

function toggleOtherAttempts(value)
{
    var attemptsValues = [3,8,41,47];
    
    if ($.inArray(parseInt(value), attemptsValues) >= 0) {
        $("#otherAttempts").parents(".form-prompt").show();
    } else {
        $("#otherAttempts").parents(".form-prompt").hide();
    }
}

var saveOtherModal = function()
{
    var saveBtn = $('#otherDialog').parent().find('.ui-dialog-buttonset').find(':button').hide();
    saveBtn.parent('.ui-dialog-buttonset').append(throbber);
    
    if (typeof getPatientId == 'function') { 
        var patientId = getPatientId(); 
        $('#otherDialog input[name=patientId]').val(patientId); shiftId = null; 
    }
    if (typeof getShiftId == 'function'){ 
        shiftId = getShiftId(); 
        $('#otherDialog input[name=shiftId]').val(shiftId); 
    }
     
    $.post('/skills-tracker/patients/validate-other', $('#otherDialog').serialize(),
        function(response) {
            if (typeof response == 'string') {
                var entityId = response;
                $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : patientId, 'shiftId' : shiftId},
                function(response) {
                    $('#intervention-list').html($(response).fadeIn());
                    initInterventionList();
                    $('#otherDialog').dialog('close');
                    saveBtn.show();
                    throbber.remove();
                    $('#throbber').remove();
                    var newEntity = $('#' + entityId).addClass('new');
                    setTimeout(function() { newEntity.toggleClass('new', 2000) }, 1000);
                    
                });									
            } else {
                htmlErrors = '<div class=\'form-errors alert\'><ul>';
                
                $('label').removeClass('prompt-error');
                
                $.each(response, function(elementId, msgs) {
                    $('label[for=' + elementId + ']').addClass('prompt-error');
                    $.each(msgs, function(key, msg) {
                        htmlErrors += '<li>' + msg + '</li>';
                    });
                });
                
                htmlErrors += '</ul></div>';
                
                $('.form-errors').remove();
                $('#otherDialog').prepend(htmlErrors);
                
                saveBtn.show();
                throbber.remove();
                $('#throbber').remove();
            }
            
        });  
    
    
}