//Javascript for SkillsTracker_Form_AirwayModal

$(document).ready(function() {
    
    
    // init the dialog box
    $("#airwayDialog").dialog({
        tabPosition:'top',
        modal:true,
        autoOpen:false,
        resizable: false,
		width: 800,
		title: 'Airway',
		open: function(){
            
            if ($("#airwayDialog").parent().find(".green-buttons").length <= 0) {
                $("#airwayDialog").parent().find("#save-btn").wrap("<span class='green-buttons'></span>");
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
                    saveAirwayModal();
                }
            }
        ]
    });
    
    var airwayProc = $("#airwayProcedure");
    airwayProc.live('change', function() {
        toggleAirwaySuccess($(this).val());
        toggleAirwaySize($(this).val());
        toggleAirwayAttempts($(this).val());
        
        // show the airway management checkbox
        if ($(this).val() == 0) {
            $(".airway_management_credit_wrapper").fadeOut("fast");
        }
        else {
            $(".airway_management_credit_wrapper").fadeIn("fast");
        }
    });
    
    $("#airway_management_credit").live('change', function() {
        toggleAirwaySuccess($("#airwayProcedure").val(), true);
    });
});

function initAirwayModal()
{
    toggleAirwaySuccess($("#airwayProcedure").val());
    toggleAirwaySize($("#airwayProcedure").val());
    toggleAirwayAttempts($("#airwayProcedure").val());
    
    // show the airway management checkbox
    if ($("#airwayProcedure").val() == 0) {
        $(".airway_management_credit_wrapper").fadeOut("fast");
    }
    else {
        $(".airway_management_credit_wrapper").fadeIn("fast");
    }
}

function toggleAirwaySuccess(value, fromAirwayManagementCheck)
{
    var airwaySuccessValues = [1,3,5,6,9,10,11,28];
    
    // if airway management credit is checked, we need to capture success regardless of procedure type
    if ($("#airway_management_credit").prop("checked")) {
        if (value == 0) {
            $("input[name=airwaySuccess]").parents(".form-prompt").slideUp();
        }
        else {
            if ($("input[name=airwaySuccess]").parents(".form-prompt").css("display") != "block") {
                $("input[name=airwaySuccess]").parents(".form-prompt").slideDown("fast");
            }
        }
    }
    else {
        if ($.inArray(parseInt(value), airwaySuccessValues) >= 0) {
            $("input[name=airwaySuccess]").parents(".form-prompt").fadeIn("fast");
        } else {
            if (fromAirwayManagementCheck) {
                $("input[name=airwaySuccess]").parents(".form-prompt").slideUp("fast");
            }
            else {
                $("input[name=airwaySuccess]").parents(".form-prompt").hide();
            }
        }
    }

    
    
}

function toggleAirwaySize(value)
{
    var airwaySizeValues = [5,6,9,10,11];
    
    if ($.inArray(parseInt(value), airwaySizeValues) >= 0) {
        $("#airwaySize").parents(".form-prompt").fadeIn("fast");
    } else {
        $("#airwaySize").parents(".form-prompt").hide();
    }
}

function toggleAirwayAttempts(value)
{
    var attemptsValues = [1,3,5,6,9,10,11];
    
    if ($.inArray(parseInt(value), attemptsValues) >= 0) {
        $("#airwayAttempts").parents(".form-prompt").fadeIn("fast");
    } else {
        $("#airwayAttempts").parents(".form-prompt").hide();
    }
}

var saveAirwayModal = function()
{
    var saveBtn = $('#airwayDialog').parent().find('.ui-dialog-buttonset').find(':button').hide();
    $('#airwayDialog').parent().find('.ui-dialog-buttonset').append(throbber);
    
    if (typeof getPatientId == 'function') { 
        var patientId = getPatientId(); 
        $('#airwayDialog input[name=patientId]').val(patientId); shiftId = null; 
    }
    if (typeof getShiftId == 'function'){ 
        shiftId = getShiftId(); 
        $('#airwayDialog input[name=shiftId]').val(shiftId); 
    }
    
    var clinical_quick_add_airway_modal = false;
	if ($("#clinical_quick_add_interventions").length > 0) {
		clinical_quick_add_airway_modal = true;
	}
	
    
    $.post('/skills-tracker/patients/validate-airway?shift_id='+shiftId, $('#airwayDialog').serialize(),
        function(response) {
            if (typeof response == 'string') {
                var entityId = response;
                $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : patientId, 'shiftId' : shiftId},
                function(response) {
                    $('#intervention-list').html($(response).fadeIn());
                    initInterventionList();
                    $('#airwayDialog').dialog('close');
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
                $('#airwayDialog').prepend(htmlErrors);
                
                saveBtn.show();
                throbber.remove();
                $('#throbber').remove();
            }
            
        });
    
    
}