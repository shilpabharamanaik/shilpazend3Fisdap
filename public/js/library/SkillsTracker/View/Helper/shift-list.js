$(function(){
	var count = 0;
	$(".shift-list-filters").each(function(){
		if(count != 0){
			$(this).css("border", "0px");
		}
		count++;
	});
	
	/*
	if(isInstructor == undefined){
		isInstructor = $("#isInstructor").val();
	}
	
	if(studentId != undefined){
		lastStudentId = studentId;
	}else if(lastStudentId != null){
		studentId = lastStudentId;
	}else{
		studentId = $('#studentId').val();
	}
	
	//grab elements from the form that need to be interacted with
	var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
	var formElements = $("#shift-filters form input");
	
	//Add the throbber gif and disable the filter inputs
	$("#shift-filters").prepend(throbber);
	formElements.attr('disabled', 'disabled');
	
	//Send AJAX request to server, once we get resp, remove throbber and enable form inputs
	$.post('/skills-tracker/shifts/filtershifts', { 
		"studentId": studentId, 
		"filter" : filter, 
		"instructor": isInstructor
	},
	function(response) {
		var shiftList = $(response.shifts);
		$("#shift-list").html(shiftList.hide().fadeIn(1000));
		
		$("#shift-list-student-title").text(response.studentName + "'s Shifts");
		
		$('#filters-' + filter).attr('checked', true);
		doTableJqueryEvents();
		throbber.remove();
		formElements.removeAttr('disabled');

	}, 'json');
	*/
});