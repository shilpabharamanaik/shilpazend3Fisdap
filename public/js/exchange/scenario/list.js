$(function(){
	$('.scenario_table tbody tr').hover(function(e){$(this).addClass('scenario_table_hover')}, function(e){$(this).removeClass('scenario_table_hover')})
	$('.edit_button').click(
		function(e){
			scenarioId = $(e.currentTarget).attr('data-scenarioid');
			if(scenarioId > 0){
				window.location = "/exchange/scenario/index/scenarioId/" + scenarioId;
			}
			return false;
		}
	);
	$('.delete_button').click(
		function(e){
			scenarioId = $(e.currentTarget).attr('data-scenarioid');
			if(scenarioId > 0){
				window.location = "/exchange/scenario/delete/scenarioId/" + scenarioId;
			}
			return false;
		}
	);

	$('#scenario_select, #author_select').chosen();
	
	$('#author_select').change(function(e){
		authId = $('#author_select').val()
		
		if(authId > 0){
			blockUi(true);
			window.location = "/exchange/scenario/list/author_id/" + authId;
		}
	});
});