$(function() {
		$(".deleteEvent").click(function(e){
			var deleteButton  = $(this);	
			e.preventDefault();
			$('<div id="dialog-confirm" title="Delete Event?"><p>Delete this event permanently?</p><p>Warning!: This will affect all riders saved under this event, if any.</p></div>')
			.dialog({
	  resizable:false,		
      height:180,
	  width:350,
      modal: true,
      buttons: {
        "Delete event": function() {
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