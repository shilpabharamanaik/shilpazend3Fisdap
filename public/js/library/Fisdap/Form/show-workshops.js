$(function(){
	
$(".deleteWorkshop").click(function(e){
		var deleteButton  = $(this);	
		e.preventDefault();
		
		$('<div id="dialog-confirm" title="Delete Workshop?"><p>Delete this workshop permanently?</p><p>Warning!: You will not be able to recover this information.</p></div>')
		.dialog({
			resizable:false,		
			height:200,
			width:350,
			modal: true,
			buttons:
			{
				"Delete Workshop": function() {
					window.location = deleteButton.attr("href");
				  $( this ).dialog( "close" );
				},
				Cancel: function() {
				  $( this ).dialog( "close" );
				}
			}
		})
	})	


	
});	