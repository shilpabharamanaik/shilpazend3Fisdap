$(function(){

	var passwordsMatch = false;
	
	$("#newPassword").change(function(){
		removeImages("newPassword");
		lessThanFiveCharacters($(this), "newPasswordSuccess", "newPasswordError");

	});
	
	$("#new2").change(function(){
		removeImages("new2");
		if(!lessThanFiveCharacters($(this), "new2Success", "new2Error")){
			removeImages("new2");

			if($(this).val() != $("#newPassword").val()){
				$(this).parent().append("<img src='/images/badinput.png' class='liveValidation' id='new2Error'>");
				passwordsMatch = false;
			}
			else {
				$(this).parent().append("<img src='/images/check.png' class='liveValidation' id='new2Success'>");
				passwordsMatch = true;
			}
		}
		
	});
	
	function removeImages(input){
		if(input == "new2"){
			$("#new2Success").remove();
			$("#new2Error").remove();
		}
		else {
			$("#newPasswordSuccess").remove();
			$("#newPasswordError").remove();
		}
	}
	
	function lessThanFiveCharacters(input, imgSuccessId, imgErrorId)
	{
		if(input.val().length < 5){
			input.parent().append("<img src='/images/badinput.png' class='liveValidation' id='" + imgErrorId + "'>");
			return true;
		}
		else {
			input.parent().append("<img src='/images/check.png' class='liveValidation' id='" + imgSuccessId + "'>");
			return false;
		}
	}
	
	function noErrors(){
		if($("#newPasswordSuccess").length > 0 && $("#new2Success").length > 0){
			return true;
		}
		else {
			return false;
		}
	}
	
	function getErrorMessage()
	{
		var msg = "";
		var oneErrorAlready = false;
		
		// they haven't entered a password that is long enough
		if($("#newPasswordSuccess").length == 0){
			msg += "Please enter a password that is at least 5 characters long.";
			oneErrorAlready = true;
		}
		
		// their passwords do not match
		if(passwordsMatch == false){
			if(oneErrorAlready){
				msg += "<br />";
			}
			msg += "Your passwords do not match.";
		}

		
		return msg;
	}
	
	$("#save").click(function(e){
		if(noErrors()){
			
		}
		else {
			e.preventDefault();
			$("#errorsContainer").html(getErrorMessage());
			$("#errorsContainer").slideDown();
		}
	})
	
});