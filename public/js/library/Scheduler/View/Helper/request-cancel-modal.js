$(function(){
    
    // turn the history modal div into a modal
    $("#requestCancelDialog").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 600,
	title: "Cancel Request",
	open: function (){
	    $("#no-cancel-btn").blur();
        }
    });
   
});

function initRequestCancelModal() {
    $("#no-cancel-btn").button();
    $("#yes-cancel-btn").button();
    
    $("#no-cancel-btn").click(function(e){
	e.preventDefault();
	$("#requestCancelDialog").dialog('close');
    });
    
    $("#yes-cancel-btn").click(function(e){
	e.preventDefault();
			
	var requestId = $(this).attr('data-requestid');
	var noBtn = $('#no-cancel-btn').hide();
	var yesBtn = $('#yes-cancel-btn').hide();
	var throbber =  $("<img id='cancelModalThrobber' src='/images/throbber_small.gif'>");
	yesBtn.parent().append(throbber);
	$.post("/scheduler/requests/process-request-cancel",
		{ "request_id" : requestId },
		function (response) {
			if (response) {
				window.location = "/scheduler/requests";
				window.location.reload(true);
			} else {
				$("#cancel-modal-content").html("<div class='alert'>You are not authorized to cancel this request.</div>");
				noBtn.find(".ui-button-text").html("Ok");
				noBtn.show();
				$('#cancelModalThrobber').remove();
			}
		}
	)
    });
}
   
