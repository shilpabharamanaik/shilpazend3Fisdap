$(function(){
	disableTransfer();
	
	if($(".success").length != 0){
		$(".success").delay(7000).fadeOut();
	}

   $("#userId").change(function(){
		$("#userDetails").slideUp();
		$("#userDetails").empty();
		
		if($(this).val() != ""){
			$("#throbber").show();

			$.post(
				'/account/edit/find-users-to-transfer',
				{ id: $(this).val() },
				
				function(response){
					if(response == "none"){
					}
					else {
						$("#userDetails").append(response);
						$("#userDetails").slideDown();
						$("#throbber").fadeOut();
					}
					
					if($(".error").length == 0){
						enableTransfer();
					}
					else {
						disableTransfer();
					}
				}
			);
		}

	});
   
   //Bind this keypress function to all of the input tags
	$("input").keypress(function (evt) {
		//Deterime where our character code is coming from within the event
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) { //Enter key's keycode
			return false;
		}
	});

   
   function disableTransfer(){
		$("#save").attr("disabled", "disabled");
		$("#save").css("opacity", "0.55");
   }
   
   function enableTransfer(){
		$("#save").removeAttr("disabled");
		$("#save").css("opacity", "1.00");
   }
   
});