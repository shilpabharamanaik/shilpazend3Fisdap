$(function(){
	$("#Submit").button().click(function(e){
		$(this).parents("form").submit();
	});
	$("#cancel-button").button();
	
	$("#defaultGoalSet").change(function(e){
		if ($(this).is(":checked")) {
			$(this).attr('disabled', 'disabled');
			$.post(
				'/reports/goal/check-default-goalsets',
				{'goalSetId' : $("#id").val(), 'programId' : $("#program_id").val(), "certification" : $("#account_type").val()},
				function(response) {
					if (response) {
						alert('"' + response + '"' + " goalset is already marked as the default for this certification level. Saving will overwrite that default.");
					}
					$("#defaultGoalSet").removeAttr('disabled');
				}
			);
		}
	});
	
	$("input[name='defaultGoalSetId']").change(function(e){
		blockUi(true);
		$.post(
			'/reports/goal/generate-default-goal-set-form',
			{'defaultGoalSetId' : $(this).val()},
			function(response) {
				$("#customize-form-container").empty().html($(response).fadeIn());
				$("#Submit").button().click(function(e){
					$(this).parents("form").submit();
				});
				$("#cancel-button").button().click(function(event){
					autoLinkCancelButtonsToUrls(this, event);
				});
				blockUi(false);
			}
		);
	});
});