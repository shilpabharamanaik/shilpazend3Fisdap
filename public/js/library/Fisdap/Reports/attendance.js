$(function(){
	//Init the picklist mode on page load
	if ($("input[name='reportType']:checked").val() == 'summary') {
                var plmode = "multiple";
        } else if ($("input[name='reportType']:checked").val() == 'detailed') {
                var plmode = "single";
        }

        togglePicklistMode(plmode);
	
	// when the report is in detailed mode, swap the student picklist
	$("input[name='reportType']").change(function(e){
		if ($("input[name='reportType']:checked").val() == 'summary') {
			togglePicklistMode("multiple");
			$(".student-picklist").text("Select one or more student(s)");
		} else if ($("input[name='reportType']:checked").val() == 'detailed') {
			togglePicklistMode("single");
			$(".student-picklist").text("Select one student");
		}
	});
});	
