//Javascript for /skills-tracker/shifts/index specifically for clinical shifts
$(document).ready(function(){
	var interventionList = $('#intervention-list');
	
	$("#add-skills-link").click(function(event){
		event.preventDefault();
		if (interventionList.is(":visible")) {
			hideInterventionList();
		} else {
			showInterventionList();
		}
	});
	
	// Show this if they've manually opened it before, or if there's anything already inside the list.
	if ($.cookie('interventionListClinical') == 'show' || $('#intervention-table tbody tr').length > 0) {
		showInterventionList();
	} else {
		hideInterventionList();
	}
});

function showInterventionList()
{
	$('#intervention-list').slideDown();
	$.cookie('interventionListClinical', 'show', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_down.png');
    $("#add-skills-text").html('Hide skills');
}

function hideInterventionList()
{
	$('#intervention-list').slideUp();
	$.cookie('interventionListClinical', 'hide', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_right.png');
    $("#add-skills-text").html('Show skills');
}