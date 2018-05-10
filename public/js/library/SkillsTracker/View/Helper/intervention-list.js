//Javascript for SkillsTracker_View_Helper_InterventionList

//Throbber to be used on the modal dialogs
var throbber = $("<img id='throbber' src='/images/throbber_small.gif'>");

// Delete all skills that are marked to be deleted before navigating away from this form
// BEWARE: if this helper is used on a page that already defines an onunload action, only the action that
// is defined LAST will execute, so skills may not be hard deleted
$(window).on("beforeunload", function () {
    // only run the deletion if there are skills to delete
    if ($("#intervention-table td.undo").length > 0) {
        hardDeleteSkills($("#intervention-list #patientId").val(), $("#intervention-list #shiftId").val());
    }
});

$(document).ready(function() {
    initInterventionList();
});

function initInterventionList()
{
	if ($("#intervention-table tbody tr").length > 0) {
		$("#intervention-help").hide();
	}
	
	$("#intervention-table tbody").sortable({
		update: function(event, ui) {
			var tbody = $(this);
			tbody.sortable("option", "disabled", true);
			
			var data = {"ids" : []};
			tbody.children().each(function(i, row) {
				data.ids.push($(row).attr('id'));
			});
			$.post("/skills-tracker/patients/set-skill-order", data, function(response) { tbody.sortable("option", "disabled", false); } );
		}
	});
	
    //Remove old event bindings
    $('.delete-skill').unbind('click');
    
	// Reset the hover event, then re add it for each child...
	$('#intervention-table tbody').children().each(function (index, el){
		$(el).unbind('mouseenter mouseleave');
		$(el).hover(function(){$(this).addClass('over');}, function(){$(this).removeClass('over'); });
	});
    
    //Create new event bindings
    $('.delete-skill').click(function(event) {
		event.preventDefault();
		event.stopPropagation();
		var id = $(this).attr('skillId');
		deleteSkill(id);
	});
    
    $("#vitals-btn").button().click(function(event) {
        event.preventDefault();
        loadVitalModal(null);
    });
	
    $("#airway-btn").button().click(function(event) {
        event.preventDefault();
		loadAirwayModal(null);
    });
	
    $("#cardiac-btn").button().click(function(event) {
        event.preventDefault();
        loadCardiacModal(null);
    });
    
    $("#iv-btn").button().click(function(event) {
        event.preventDefault();
        loadIvModal(null);
    });
    
    $("#meds-btn").button().click(function(event) {
        event.preventDefault();
        loadMedModal(null);
    });
	
    $("#other-btn").button().click(function(event) {
        event.preventDefault();
        loadOtherModal(null);
    });
}

function loadIvModal(ivId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
    $.post("/skills-tracker/patients/generate-iv-form", {"ivId" : ivId},
        function(resp) {
            $("#ivDialog").html($(resp).html());
            $("#ivDialog").dialog("open");
		    initIvModal();
            blockUi(false, $("#intervention-list"));
    });
}

function loadCardiacModal(cardiacId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
    $.post("/skills-tracker/patients/generate-cardiac-form", {"cardiacId" : cardiacId},
        function(resp) {
            $("#cardiacDialog").html($(resp).html());
            $("#cardiacDialog").dialog("open");
		    initCardiacModal();
            blockUi(false, $("#intervention-list"));
    });
}

function loadMedModal(medId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
    $.post("/skills-tracker/patients/generate-med-form", {"medId" : medId},
        function(resp) {
            $("#medDialog").html($(resp).html());
            $("#medDialog").dialog("open");
		    initMedModal();
            blockUi(false, $("#intervention-list"));
    });
}

function loadOtherModal(otherId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
    $.post("/skills-tracker/patients/generate-other-form", {"otherId" : otherId},
        function(resp) {
            $("#otherDialog").html($(resp).html());
            $("#otherDialog").dialog("open");
		    initOtherModal();
            blockUi(false, $("#intervention-list"));
    });
}

function loadAirwayModal(airwayId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
	var clinical_quick_add_airway_modal = false;
	if ($("#clinical_quick_add_interventions").length > 0) {
		clinical_quick_add_airway_modal = true;
	}
	
    $.post("/skills-tracker/patients/generate-airway-form", {"airwayId" : airwayId, "clinical_quick_add_airway_modal" : clinical_quick_add_airway_modal},
        function(resp) {
            $("#airwayDialog").html($(resp).html());
            $("#airwayDialog").dialog("open");
            initAirwayModal();
            blockUi(false, $("#intervention-list"));
    });
}

function loadVitalModal(vitalId)
{
    blockUi(true, $("#intervention-list"), "no-msg");
    $.post("/skills-tracker/patients/generate-vital-form", {"vitalId" : vitalId},
        function(resp) {
            $("#vitalDialog").html($(resp).html());
            $("#vitalDialog").dialog("open");
            blockUi(false, $("#intervention-list"));
    });
}

function deleteSkill(id)
{
    var row = $("#" + id)
	var completed = false;
    blockUi(true, $("#intervention-list"), "throbber");

    function complete() {
        if (completed) {
            return;
        }
        completed = true;

        $.post("/skills-tracker/patients/delete-skill/", {"id": id},
            function (response) {
                var message = $("<td colspan='4' class='undo'>" + response + "</td>");
                row.append(message.fadeIn(1000));
                //doTableJqueryEvents();
                $('#undo-delete-' + id).click(function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    undoDeleteSkill(id);
                });
                blockUi(false, $("#intervention-list"));
            });
    }
	
	row.children().fadeOut(1000, complete);
}

function undoDeleteSkill(id)
{
    var row = $("#" + id);
    blockUi(true, $("#intervention-list"), "throbber");
	
	function complete() {
		//row.fadeIn(1000);
		//row.prev().remove();
		
		row.children(":visible").remove();
		row.children().fadeIn(1000);
        blockUi(false, $("#intervention-list"));
	}
	
	$.post("/skills-tracker/patients/undo-delete-skill/", {"id" : id }, complete);
}

function duplicateSkill(id, entityName)
{
	patientId = $("#intervention-list #patientId").val();
	shiftId = $("#intervention-list #shiftId").val();
    blockUi(true, $("#intervention-list"), "throbber");
    $.post("/skills-tracker/patients/duplicate-skill/", {"id" : id, "entityName" : entityName, "patientId" : patientId, "shiftId" : shiftId },
           function(response) {
               $('#intervention-list').html($(response).fadeIn());
			   initInterventionList();
			   var newEntity = $('#' + entityName + "_" + id).addClass('new');
			   setTimeout(function() { newEntity.toggleClass('new', 2000) }, 1000);
               blockUi(false, $("#intervention-list"));
           });
}

function hardDeleteSkills(patientId, shiftId)
{
    $.ajaxSetup({async:false});
    $.post("/skills-tracker/patients/hard-delete-skills/",
        {"patientId" : patientId, "shiftId" : shiftId },
        function(response) {
            return true;
        });
}

function getShiftId()
{
	return $("#intervention-list #shiftId").val();
}