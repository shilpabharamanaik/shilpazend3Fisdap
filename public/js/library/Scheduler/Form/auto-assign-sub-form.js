$(function() {
	initAASubForm();
	initAutoAssignChangeAction();
});

var initAASubForm;
initAASubForm = function () {
	// don't check/uncheck checkbox when you click on the label
	$("#auto_assign-label").click(function(e) {
		e.preventDefault();
	});
	
	var parent_of_special_elements = $(".auto-assign-wrapper");
	if ($("#autoAssignForm").length > 0) {
		parent_of_special_elements = $("#autoAssignForm");
	}
	
	parent_of_special_elements.find(".slider-checkbox").each(function(){$(this).sliderCheckbox({onText: 'On', offText: 'Off'});});
	parent_of_special_elements.find(".aa-chzn-select").chosen();
	
	if ($("#autoAssignForm").length > 0) {
		$("#autoAssignForm").change(function(){
			initAutoAssignChangeAction();
			$('#save-button').attr('data-changes', true);
			$('.success').slideUp(400, function() {
				$('.success').remove();
				$('#save-button').show();
				$('#control-buttons').css({ 'margin-bottom' : '-40px'} );
			});
		});	
	}
	else {
		$("#auto_assign").change(function(){
			initAutoAssignChangeAction();
		});
	}

}
	

var initAutoAssignChangeAction;
initAutoAssignChangeAction = function() {
	if ($("#auto_assign").attr('checked')) {
		$(".hide-on-auto-assign-off").fadeIn();
		$("#auto-assign-punctuation").html(':');
	} else {
		$(".hide-on-auto-assign-off").fadeOut();
		$("#auto-assign-punctuation").html('.');
	}		
}
