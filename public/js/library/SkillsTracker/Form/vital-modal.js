//Javascript for SkillsTracker_Form_VitalModal

$(document).ready(function() {
    
    
    // init the dialog box
    $("#vitalDialog").dialog({
        tabPosition:'top',
        modal:true,
        autoOpen:false,
        resizable: false,
		width: 800,
		title: 'Vitals',
		open: function(){
            
            $('#more-vitals-link').css('color', '#A94612');
			$('#more-vitals-link').css('margin-top', '5px');
            
            if ($("#vitalDialog").parent().find(".green-buttons").length <= 0) {
                $("#vitalDialog").parent().find("#save-btn").wrap("<span class='green-buttons'></span>");
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
                    saveVitalModal();
                }
            }
        ]
    });
    
    
    var moreVitalsLink = $("#more-vitals-link")
    
    moreVitalsLink.live('click', function(event) {
        event.preventDefault();
        if ($("#more-vitals").is(":visible")) {
            $("#more-vitals").hide("blind", {}, 500);
            $("#more-vitals-link").text("More");
        } else {
            $("#more-vitals").show("blind", {}, 500);
            $("#more-vitals-link").text("Less");
        }
    });
});


var saveVitalModal = function()
{
    var saveBtn = $('#vitalDialog').parent().find('.ui-dialog-buttonset').find(':button').hide();
    saveBtn.parent('.ui-dialog-buttonset').append(throbber);
    
    if (typeof getPatientId == 'function') { 
        var patientId = getPatientId(); 
        $('#vitalDialog input[name=patientId]').val(patientId); shiftId = null; 
    }
    if (typeof getShiftId == 'function'){ 
        shiftId = getShiftId(); 
        $('#vitalDialog input[name=shiftId]').val(shiftId); 
    }
     
    $.post('/skills-tracker/patients/validate-vital', $('#vitalDialog').serialize(),
        function(response) {
            if (typeof response == 'string') {
                var entityId = response;
                $.post('/skills-tracker/patients/generate-intervention-list', {'patientId' : patientId, 'shiftId' : shiftId},
                function(response) {
                    $('#intervention-list').html($(response).fadeIn());
                    initInterventionList();
                    $('#vitalDialog').dialog('close');
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
                $('#vitalDialog').prepend(htmlErrors);
                
                saveBtn.show();
                throbber.remove();
                $('#throbber').remove();
            }
            
        });
}