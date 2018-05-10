//Javascript for SkillsTracker_View_Helper_InterventionList

//Throbber to be used on the modal dialogs
var throbber = $("<img id='throbber' src='/images/throbber_small.gif'>");

// Delete all skills that are marked to be deleted before navigating away from this form
// BEWARE: if this helper is used on a page that already defines an onunload action, only the action that
// is defined LAST will execute, so skills may not be hard deleted
$(window).on('beforeunload', function () {
	hardDeleteSkills();
});


$(document).ready(function() {
    initInterventionList();
    
    // We only want to do this once...  Init the modals and the output fields for them.
    $(".vitals-btn").button().each(function(i, e){
    	$(e).click(function(event) {
            event.preventDefault();
            loadVitalModal($(this).attr('data-vital_id'));
            //refreshVitalDisplay(vitalId);
        });
    });
});

function initInterventionList()
{
	if ($("#intervention-table_1 tbody tr").length > 0 || 
		$("#intervention-table_2 tbody tr").length > 0 ||
		$("#intervention-table_3 tbody tr").length > 0) {
		$("#intervention-help").hide();
	}
	
	$(".priority_drop_target").droppable({
		accept: ".draggable_skill",
		activate: function(event, ui){},
		drop: function(event, ui){
			// This was added in on the over event- need to remove it as there is no out event here.
			$(this).removeClass("priority_1_dragdrop priority_2_dragdrop priority_3_dragdrop");
			
			newPriority = $(this).attr('data-priority');
			// Remove any priority stylings from the draggable element, and re-add the new priority class.
			jqDraggable = $(ui.draggable);
			jqDraggable.find('.procedure-grabby-column').removeClass("priority_1 priority_2 priority_3");
			jqDraggable.find('.procedure-grabby-column').addClass("priority_" + newPriority);
			
			// Append the draggable object (the original row) to the new table.
			$(this).append(jqDraggable);
			
			// Finally, send a command up to the server to change the priority on the back-end...
			scenarioId = $('#scenario_id').val();
			
			data = {
				"scenarioId": scenarioId,
				"skillId": jqDraggable.attr('id'),
				"priority": newPriority
			}
			
			$.post("/exchange/scenario/set-skill-priority", data,
				function(response) {
					return response;
				}
			);
		},
		out: function(event, ui){
			$(this).removeClass("priority_" + $(this).attr('data-priority') + "_dragdrop");
		},
		over: function(event, ui){
			$(this).addClass("priority_" + $(this).attr('data-priority') + "_dragdrop");
		}
	});
	
	$(".draggable_skill").draggable({
		start: function(event, ui){
			addDropTargets();
			$(ui.handle).css("width", 640);
		},
		stop: function(event, ui){
			removeDropTargets();
		},
		helper: function(){
			return $("<table style='width: 640px;' class='over'>" + $(this).html() + "</table>");
		}
		
	});
	
	/*
	$("#intervention-table tbody").sortable({
		update: function(event, ui) {
			var tbody = $(this);
			tbody.sortable("option", "disabled", true);
			
			var data = {"ids" : []};
			tbody.children().each(function(i, row) {
				data.ids.push($(row).attr('id'));
			});
			$.post("/exchange/scenario/set-skill-order", data, function(response) { tbody.sortable("option", "disabled", false); } );
		}
	});
	*/
	
    //Remove old event bindings
    $('.delete-skill').unbind('click');
    
	// Reset the hover event, then re add it for each child...
	$('.intervention-table tbody').children().each(function (index, el){
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
    
    // Set up the radio button sets for ALS/BLS togglers
    $('.toggle-als-bls').buttonset().addClass('extra-small');

    
}

//Refresh the vitals display portion to reflect the values stored in the modal.
function refreshVitalDisplay(vitalId){
	//console.log(vitalId);
	$('.vitals_blood_pressure[data-vital_id="' + vitalId + '"]').text($('#bp-systolic').val() + " / " + $('#bp-diastolic').val());
	$('.vitals_pulse[data-vital_id="' + vitalId + '"]').text($('#pulse-rate').val() + " " + $("#pulse-quality option:selected").text());
	$('.vitals_respirations[data-vital_id="' + vitalId + '"]').text($('#respirations-rate').val() + " " + $("#respirations-quality option:selected").text());
	$('.vitals_spo2[data-vital_id="' + vitalId + '"]').text($('#spo2').val());
	
	skinsAr = [];
	
	$('.skins').parent().each(function(i, e){
		if($(e.children[0]).is(':checked')){
			skinsAr.push(e.textContent);
		}
	});
	
	$('.vitals_skin[data-vital_id="' + vitalId + '"]').text(skinsAr.join(", "));
	
	pupilSum = 0;
	
	equalVal = parseInt($('input[name="pupils[equal]"]:checked').val());
	roundVal = parseInt($('input[name="pupils[round]"]:checked').val());
	reactiveVal = parseInt($('input[name="pupils[reactive]"]:checked').val());
	
	pupilSum = pupilSum + equalVal + roundVal + reactiveVal;
	
	if(pupilSum == 3){
		$('.vitals_pupils[data-vital_id="' + vitalId + '"]').text("PERRL: Yes");
	}else if(pupilSum == -3){
		$('.vitals_pupils[data-vital_id="' + vitalId + '"]').text("N/A");
	}else{
		$('.vitals_pupils[data-vital_id="' + vitalId + '"]').text("PERRL: No");
	}
	
	lungSoundAr = [];
	
	$('.lung-sounds').parent().each(function(i, e){
		if($(e.children[0]).is(':checked')){
			lungSoundAr.push(e.textContent);
		}
	});
	
	$('.vitals_lung_sounds[data-vital_id="' + vitalId + '"]').text(lungSoundAr.join(", "));
	
	$('.vitals_blood_glucose[data-vital_id="' + vitalId + '"]').text($('#bloodGlucose').val());
	$('.vitals_apgar[data-vital_id="' + vitalId + '"]').text($('#apgar').val());
	$('.vitals_gcs[data-vital_id="' + vitalId + '"]').text($('#gcs').val());
}

// This just adds a decent sized area for the drop targets- mostly useful if there are no elements in the priority.
function addDropTargets(){
	$('.priority_drop_target').each(function(i, e){
		$(e).append($("<tr class='temp_drop_target'><td colspan='4'><div class='drop_target_text'>Drop skill here</div></td></tr>"));
	});
}

function removeDropTargets(){
	$('.temp_drop_target').remove();
}

function loadIvModal(ivId)
{
    $.post("/skills-tracker/patients/generate-iv-form", {"ivId" : ivId},
        function(resp) {
         $("#ivDialog").html($(resp).html());
         $("#ivDialog").dialog("open");
		 initIvModal();
    });
}

function loadCardiacModal(cardiacId)
{
    $.post("/skills-tracker/patients/generate-cardiac-form", {"cardiacId" : cardiacId},
        function(resp) {
         $("#cardiacDialog").html($(resp).html());
         $("#cardiacDialog").dialog("open");
		 initCardiacModal();
    });
}

function loadMedModal(medId)
{
    $.post("/skills-tracker/patients/generate-med-form", {"medId" : medId},
        function(resp) {
         $("#medDialog").html($(resp).html());
         $("#medDialog").dialog("open");
		 initMedModal();
    });
}

function loadOtherModal(otherId)
{
    $.post("/skills-tracker/patients/generate-other-form", {"otherId" : otherId},
        function(resp) {
         $("#otherDialog").html($(resp).html());
         $("#otherDialog").dialog("open");
		 initOtherModal();
    });
}

function loadAirwayModal(airwayId)
{
    $.post("/skills-tracker/patients/generate-airway-form", {"airwayId" : airwayId},
        function(resp) {
         $("#airwayDialog").html($(resp).html());
         $("#airwayDialog").dialog("open");
		 initAirwayModal();
    });
}

function loadVitalModal(vitalId)
{
    $.post("/skills-tracker/patients/generate-vital-form", {"vitalId" : vitalId},
        function(resp) {
         $("#vitalDialog").html($(resp).html());
         $("#vitalDialog").dialog({close: function(event, ui){
        	 refreshVitalDisplay(vitalId);
         }});
         $("#vitalDialog").dialog("open");
    });
}

function deleteSkill(id)
{
    var row = $("#" + id)
	var completed = false;
	blockUi(true);
	
	function complete() {
		//row.children().hide();
		if (completed) {
			return;
		}
		completed = true;
		
		$.post("/exchange/scenario/delete-skill/", {"id" : id },
			   function(response) {
					var message = $("<td colspan='4' class='undo'>" + response + "</td>");
					row.append(message.fadeIn(1000));
					//doTableJqueryEvents();
					$('#undo-delete-' + id).click(function(event) {
						event.preventDefault();
						event.stopPropagation();
						undoDeleteSkill(id);
					});
					blockUi(false);
		});
	}
	
	row.children().fadeOut(1000, complete);
}

function undoDeleteSkill(id)
{
    var row = $("#" + id);
	blockUi(true);
	
	function complete() {
		//row.fadeIn(1000);
		//row.prev().remove();
		
		row.children(":visible").remove();
		row.children().fadeIn(1000);
		blockUi(false);
	}
	
	$.post("/exchange/scenario/undo-delete-skill/", {"id" : id }, complete);
}

function duplicateSkill(id, entityName)
{
	blockUi(true);
	
	scenarioId = $('#scenario_id').val();
	
    $.post("/exchange/scenario/duplicate-skill/", {"id" : id, "entityName" : entityName, "scenarioId": scenarioId },
           function(response) {
            $('#intervention-list').html($(response).fadeIn());
			initInterventionList();
			var newEntity = $('#' + entityName + "_" + id).addClass('new');
			setTimeout(function() { newEntity.toggleClass('new', 2000) }, 1000);
			blockUi(false);
           });
}

function hardDeleteSkills(patientId, shiftId)
{
	$.ajaxSetup({async:false});
	$.post("/exchange/scenario/hard-delete-skills/", {"patientId" : getPatientId() },
		   function(response) {
			return true;
		   });
}

function toggleAlsBls(id, skillType, state){
	scenarioId = $('#scenario_id').val();
	
	data = {
		"skillId": id,
		"scenarioId": scenarioId,
		"state": state,
		"skillType": skillType
	}
	
	$.post("/exchange/scenario/toggle-skill-als/", data,
		function(response) {
			return response;
		}
	);
}

function getShiftId()
{
	return $("#intervention-list #shiftId").val();
}

//function blockUi(block) {
//	if (block) {
//		$.blockUI({ 
//            theme:     true, 
//            message:  '<p>Please wait...</p>'
//        }); 
//	} else {
//		$.unblockUI();
//	}
//}