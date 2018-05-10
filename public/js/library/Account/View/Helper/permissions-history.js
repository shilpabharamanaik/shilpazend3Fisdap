$(function() {
	
	$( "#permissions-history-dialog" ).dialog({
		resizable: false,
		height:430,
		width:770,
		modal: true,
		title: "Permissions History",
		buttons: { "Ok": function() { $(this).dialog("close"); } },
		autoOpen: false,
		open: function() {
			$("button").each(function(){
				if($(this).text() == 'Ok'){
					$(this).blur();
					$(this).css("color", "black");
				}
			});
		},
	});
	
	// the trigger
	$("#permissionsHistory").click(function(e){
		e.preventDefault();
		$("#permissions-history-dialog").dialog( "open" );
	});
	
});