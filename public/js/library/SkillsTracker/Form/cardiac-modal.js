//Javascript for SkillsTracker_Form_CardiacModal
$(document).ready(function() {
    
    // init the dialog box
    $("#cardiacDialog").dialog({
        tabPosition:'top',
        modal:true,
        autoOpen:false,
        resizable: false,
		width: 800,
		title: 'Cardiac',
		open: function(){
            
            if ($("#cardiacDialog").parent().find(".green-buttons").length <= 0) {
                $("#cardiacDialog").parent().find("#save-btn").wrap("<span class='green-buttons'></span>");
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
                    saveCardiacModal();
                }
            }
        ]
    });
    
    var cardiacProc = $("#cardiacProcedure");
    cardiacProc.live('change', function() {
        toggleCardiacProcedureMethod($(this).val());
        toggleCardiacPacingMethod($(this).val());
    });
});

function initCardiacModal()
{
    toggleCardiacProcedureMethod($("#cardiacProcedure").val());
    toggleCardiacPacingMethod($("#cardiacProcedure").val());
}

function toggleCardiacProcedureMethod(value)
{
    var cardiacProcedureMethodValues = [1,2];

    if ($.inArray(parseInt(value), cardiacProcedureMethodValues) >= 0) {
        $("input[name=procedureMethod]").parents(".form-prompt").show();
    } else {
        $("input[name=procedureMethod]").parents(".form-prompt").hide();
    }
}

function toggleCardiacPacingMethod(value)
{
    var cardiacPacingMethodValues = [4];
    
    if ($.inArray(parseInt(value), cardiacPacingMethodValues) >= 0) {
        $("input[name=pacingMethod]").parents(".form-prompt").show();
    } else {
        $("input[name=pacingMethod]").parents(".form-prompt").hide();
    }
}

var saveCardiacModal = function()
{
    var saveBtn = $('#cardiacDialog').parent().find('.ui-dialog-buttonset').find(':button').hide();
    saveBtn.parent('.ui-dialog-buttonset').append(throbber);
    
    if (typeof getPatientId == 'function') { 
        var patientId = getPatientId(); 
        $('#cardiacDialog input[name=patientId]').val(patientId); shiftId = null; 
    }
    if (typeof getShiftId == 'function'){ 
        shiftId = getShiftId(); 
        $('#cardiacDialog input[name=shiftId]').val(shiftId); 
    }
    
    var form_data = $('#cardiacDialog').serialize();
     
    $.post('/skills-tracker/patients/validate-cardiac', form_data,
        function(response) {
            if (typeof response == 'string') {
                var entityId = response;
                $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : patientId, 'shiftId' : shiftId},
                function(response) {
                    $('#intervention-list').html($(response).fadeIn());
                    initInterventionList();
                    $('#cardiacDialog').dialog('close');
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
                $('#cardiacDialog').prepend(htmlErrors);
                
                saveBtn.show();
                throbber.remove();
                $('#throbber').remove();
            }
            
        }
    );
}