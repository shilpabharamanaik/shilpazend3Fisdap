$(function(){
	$("#add-run-btn").button().parent().addClass('orange-button small-btn');
	
    $(".run-buttons a:not('.delete-run')").button();
    
    $(".run-buttons").hide();
    
    $(".run-container .run-summary").addClass("clickable").click(function(e) {
        e.stopPropagation();
        //e.preventDefault();
        
        var buttonsContainer = $("#run-buttons-" + $(this).parent().attr('id'));
        
        if (buttonsContainer.is(":visible")) {
            buttonsContainer.hide("slow");            
        } else {
            buttonsContainer.show("slow");
            $(".run-buttons:not(#"+buttonsContainer.attr('id')+")").hide("slow");
        }
    });
    
    $(".delete-run").click(function(e) {
		var targetUrl = $(this).attr("href");
		e.preventDefault();
		$("<div>Are you sure you want to delete this?</div>").dialog({
			modal: true,
			resizeable: false,
			width: "90%",
			buttons: {
				"No": function() {
					$( this ).dialog( "close" );
				},
				"Yes": function() {
					window.location.href = targetUrl;
				}
			}
		});
	});
});