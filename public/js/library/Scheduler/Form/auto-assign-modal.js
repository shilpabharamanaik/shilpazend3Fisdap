function initAAModal() {
    $("#cancel-btn-aa").click(function(e){
		e.preventDefault();
		$("#autoAssignRequirementDialog").dialog('close');
	});
    
    $("#save-btn-aa").click(function(e){
		e.preventDefault();
		
		var postValues = $('#autoAssignRequirementDialog form').serialize();
		var cancelBtn = $('#cancel-btn-aa').hide();
		var saveBtn = $('#save-btn-aa').hide();
		var throbber =  $("<img id='modalThrobber' src='/images/throbber_small.gif'>");
		saveBtn.parent().append(throbber);
		
		$.post("/scheduler/compliance/process-auto-assign",
			postValues,
			function (response) {
				location.reload();
			}
		)
	});
}
   
