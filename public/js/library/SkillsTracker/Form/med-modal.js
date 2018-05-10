//Javascript for SkillsTracker_Form_MedModal

$(function() {
    //initMedModal();
    
    // init the dialog box
    $("#medDialog").dialog({
        tabPosition:'top',
        modal:true,
        autoOpen:false,
        resizable: false,
		width: 800,
		title: 'Medication',
		open: function(){
            
            if ($("#medDialog").parent().find(".green-buttons").length <= 0) {
                $("#medDialog").parent().find("#save-btn").wrap("<span class='green-buttons'></span>");
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
                    saveMedModal();
                }
            }
        ]
    });
    
    
});

function initMedModal(includeCombobox)
{
    if (typeof(includeCombobox) == "undefined") {
        includeCombobox = true
    }
    
    if (includeCombobox) {
        //$( "#medication" ).combobox();    
    }
}

var saveMedModal = function(){
    var saveBtn = $('#medDialog').parent().find('.ui-dialog-buttonset').find(':button').hide();
    saveBtn.parent('.ui-dialog-buttonset').append(throbber);
    
    if (typeof getPatientId == 'function') { 
        var patientId = getPatientId(); 
        $('#medDialog input[name=patientId]').val(patientId); shiftId = null; 
    }
    if (typeof getShiftId == 'function'){ 
        shiftId = getShiftId(); 
        $('#medDialog input[name=shiftId]').val(shiftId); 
    }
    
    $.post('/skills-tracker/patients/validate-med', $('#medDialog').serialize(),
        function(response) {
            if (typeof response == 'string') {
                var entityId = response;
                $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : patientId, 'shiftId' : shiftId},
                function(response) {
                    $('#intervention-list').html($(response).fadeIn());
                    initInterventionList();
                    $('#medDialog').dialog('close');
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
                $('#medDialog').prepend(htmlErrors);
                
                saveBtn.show();
                throbber.remove();
                $('#throbber').remove();
            }
            
        }
    );
}