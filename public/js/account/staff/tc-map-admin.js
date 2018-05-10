$(function(){
	
	$("tr").each(function(){
		$(this).click(function(e){
			e.preventDefault();
			var newUrl = "/account/staff/edit-state/stateId/" + $(this).attr("data-stateId");
			window.location=newUrl;
		});
	});
	
});