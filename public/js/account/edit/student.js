$(function(){
	$('#available-list').change(function(){
		// only update if a student is chosen
		if ($(this).val()) {
			blockUi(true);
			window.location = "/account/edit/student/studentId/" + $(this).val();
		}
	});
});