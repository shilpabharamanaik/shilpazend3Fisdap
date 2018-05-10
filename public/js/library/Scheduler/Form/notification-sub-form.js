$(function() {
	initNotificationsSubForm();
});

var initNotificationsSubForm;
initNotificationsSubForm = function(){
	
	$(".notification-sliders").each(function(){$(this).sliderCheckbox({onText: 'On', offText: 'Off'});});
	
	// don't check/uncheck checkbox when you click on the label
	$("#on_noncompliant-label, #on_assign-label").click(function(e) {
		e.preventDefault();
	});
	
	// update labels
	// offset number changing
	$('.warning-frequency-offset').each(function(e) {
		var id = $(this).attr('id').substring(25);
		pluralizeDays(id);
	});
	
	$("#add-warning").click(function(e){
		e.preventDefault();
		addWarning();
	});
	
	// offset number changing
	$("#warnings").on('change','.warning-frequency-offset',function(e) {
		var id = $(this).attr('id').substring(25);
		pluralizeDays(id);
	});
	
	// view/hide samples
	$(".view-sample").click(function(e) {
		e.preventDefault();
		var sample = $("#" + $(this).attr('data-section'));
		if (sample.is(":visible")) {
			sample.slideUp();
			$(this).html("View sample");
		} else {
			sample.slideDown();
			$(this).html("Hide sample");
		}
	});
}
	
function addWarning() {
	var added = $("#added_warnings");
	var newCount = parseInt($("#added_warnings").val()) + 1;
	$(added).val(newCount);
	var id = "new"+newCount;
	
	var warningRow =
		"<div id='warning_"+id+"' class='input-line warning-row'>"+
			"<div class='slider'>"+
				"<input type='hidden' value='1' name='warning_switch_"+id+"'>"+
				"<input id='warning_switch_"+id+"' class='notification-sliders warning-switch' checked='checked' type='checkbox' value='1' name='warning_switch_"+id+"'>"+
			"</div>"+
			"<div class='text'>Send a warning</div>"+
			"<div class='textbox'>"+
				"<input id='warning_frequency_offset_"+id+"' class='warning-frequency-offset fancy-input' type='textbox' value='30' name='warning_frequency_offset_"+id+"'>"+
			"</div>"+
			"<div class='text'><span id='warning_offset_label_"+id+"'>days</span> before each assignment is due or expires.</div>"+
		"</div>";
		
	$("#warnings").append(warningRow);
	
	var newRow = $("#warning_"+id);
	$("#warning_switch_"+id).each(function(){$(this).sliderCheckbox({onText: 'On', offText: 'Off'});});
	
	// this stuff is just for on the settings page
	$('#save-button').attr('data-changes', true);
	$('.success').slideUp(400, function() {
		$('.success').remove();
		$('#save-button').show();
		$('#control-buttons').css({ 'margin-bottom' : '-40px'} );
	});
}
	
function pluralizeDays(id) {
	var offsetNumber = $("#warning_frequency_offset_"+id).val();
	var label = $("#warning_offset_label_"+id);
	if (offsetNumber == "1") {
		$(label).html('day');
	} else {
		$(label).html('days');
	}
}
