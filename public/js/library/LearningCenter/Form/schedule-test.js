$(document).ready(function() {
	// get array of tests for which ShowTotals is set to false
	var noShowTotals = $('input[name="noShowTotals"]').val().split(',');
		if ($.inArray($('#test_id option:selected').val(), noShowTotals) > -1) {			
			$("select[name='is_published']").val('0');
			$("select[name='is_published']").prop('disabled', true);
		} else {
			$("select[name='is_published']").prop('disabled', false);
		}
	$("#test_id").change(function() {
		// if this test is one of those set to ShowTotals = false, then we need the UI to indicate
		// that students CANNOT see their scores.
		if ($.inArray($(this).val(), noShowTotals) > -1) {
			$("select[name='is_published']").val('0');
			$("select[name='is_published']").prop('disabled', true);
		} else {
			$("select[name='is_published']").prop('disabled', false);
		}
		
		// set the hidden field in the multi-student picker to the new moodle quiz ID when it changes
		// first get the existing additionalData so we play nice with any other modifications to it
		if (typeof(window.msp_addtlData) != 'undefined') {
			if ($.isEmptyObject(window.msp_addtlData)) {
				window.msp_addtlData = {
					"moodleQuizId" : parseInt($(this).val(), 10)
				}
			} else {
				window.msp_addtlData.moodleQuizId = parseInt($(this).val(), 10);
			}
		} else {
			window.msp_addtlData = {
				"moodleQuizId" : parseInt($(this).val(), 10)
			}
		}
		
		// trigger the msp to reload/redisplay its list
		msp_performFilter();
	});
});