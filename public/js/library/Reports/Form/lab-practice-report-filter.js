$(document).ready(function() {
	// we want the report control's certification level field to control which group of students is available
	// so first we prevent the user from clicking the multi student picker's certlevel checkboxes
	$(".certLevelWrapper").click(function(e){
		e.preventDefault();
	})
	
	var certLevelSelected = $("input[name='certLevel']:checked").val();
	$("input[name='certificationLevels[]']").attr('checked', false);
	$("input[name='certificationLevels[]'][value='" + certLevelSelected + "']").attr('checked', 'checked').change();
	$("#msp_filters").find(".certLevelWrapper").hide();
	$("#msp_filters").find(".graduatingWrapper").css("border-left", "0px");
	
	// next we manually trigger the change function on the multi-student picker's checkboxes
	// when the report settings select box is changed
	$("input[name='certLevel']").change(function(e){
		var certLevelSelected = $("input[name='certLevel']:checked").val();
		$("input[name='certificationLevels[]']").attr('checked', false);
		$("input[name='certificationLevels[]'][value='" + certLevelSelected + "']").attr('checked', 'checked').change();
	});
	
	// when the report is in detailed mode, allow only one student to be selected
	$('#msp_container').delegate('.checkboxCell, .Name-cell, .msp_student_toggle_checkbox', 'click', function(e) {
		// are we in detailed mode?
		var mode = $('input[name="reportType"]:checked').val();
		if (mode == 'detailed') {
			// uncheck all other checkboxes
			if ($(e.target).is('input')) {
				var thisValue = $(e.target).val();
			} else {
				var thisValue = $(e.target).parents('tr').find('input.msp_student_toggle_checkbox').val();
			}
			$('.msp_student_toggle_checkbox[value!="' + thisValue + '"]').attr('checked', false).parents('tr').css('background-color', 'transparent');
		}
	});
	// if the report is switched to detailed mode, and more than one student is already selected, we need to display a warning
	$('input[name="reportType"]').change(function(e) {
		var mode = $('input[name="reportType"]:checked').val();
		if (mode == 'detailed') {
			$("#include-classmates").slideUp();
			var numChecked = $('.msp_student_toggle_checkbox:checked').length;
			if (numChecked > 1) {
				alert('You can only view data from one student with a detailed report. Please limit your student selection to just one student.');
			}
		}
		else {
			$("#include-classmates").slideDown();
		}
		
	});
	
	$( "#start_date" ).datepicker({
		defaultDate: "-1y",
		changeMonth: true,
		changeYear: true,
		onClose: function( selectedDate ) {
			$( "#end_date" ).datepicker( "option", "minDate", selectedDate );
		},
		onSelect: function () {
		}
	});
	
	$("#start_date").focus(function(){$("#ui-datepicker-div").css("z-index", "100");});
	$("#end_date").focus(function(){$("#ui-datepicker-div").css("z-index", "100");});
	
	$( "#end_date" ).datepicker({
		defaultDate: null,
		changeMonth: true,
		changeYear: true,
		onClose: function( selectedDate ) {
			$( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
	
	$("#button-set-certLevel").find("label").each(function(){
		$(this).css("width", "115px");
	});
	
	$("#button-set-reportType").find("label").each(function(){
		$(this).css("width", "115px");
	});
	
	
	
	

});
