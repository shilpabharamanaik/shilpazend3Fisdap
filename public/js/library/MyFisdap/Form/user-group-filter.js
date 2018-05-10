$(document).ready(function() {
	// init
	if ($("#user_context-student:checked").length < 1) {
		$("#filter-student-enabled").hide();
		$("#filter-student").hide();
	} 
	
	// Update the student picker when program is changed
	$('select[name="program"]').change(function() {
		var programId = $(this).val();
		$('input[name="picker_program_id"]').val(programId);
		studentp.students = {} // empty out the students in the student picker, so it will fetch data
		studentp.refreshStudentList(true);
	});
	
	
	// hide role/student filters if the everybody box is checked
	$("input[name='everyone']").click(function() {
	    if ($(this).is(':checked')) {
		$("#filter-role, #filter-student, #filter-student-enabled").hide();
	    } else {
		$("#filter-role").show();

		// we need to know if the student picker enabler should be shown:
		if ($("#user_context-student:checked").length > 0) {
			$("#filter-student").show();
		}
	    }
	});
	
	// show the student picker enabler option if student role is checked
	$("input[name='user_context[]']").change(function() {
		if ($("#user_context-student:checked").length > 0) {
			$("#filter-student").show();

		} else {
			$("#filter-student").hide();

		}
	});
	
});

