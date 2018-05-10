$(function(){

	
	var invalidEmail = "Please enter a valid email address.";
	var notOnFisdap = "We're sorry. We could not find an account with this email address. Please make sure that you are using the email address registered to your Fisdap account and that you are typing it correctly.";
	var multipleAccounts = "<div id='mulitpleAccountsMessage'>Looks like you have more than one Fisdap account associated with this email address. Please click on the correct one below:</div>";
    $("#save").click(function(e){
		e.preventDefault();
		$("#usersContainer").slideUp();
		$("#usersContainer").empty();
		
		var email = $("#email").val();
		
		if(validateEmail(email)){
			
			$("#throbber").show();
			$(this).fadeOut();
		
			$.post(
				'/account/help/search-accounts',
				{ email: email },
				
				function(response){
					if(response == "none"){
						showErrors(notOnFisdap);
						hideThrobber();
					}
					else if(response.length > 68){
						$("#errorsContainer").slideUp("fast");
						hideThrobber();
						$("#usersContainer").append(multipleAccounts + response);
						$("#usersContainer").slideDown();
					}
					else {
						window.location = '/account/help/email-confirmation/user/' + response;
					}
					
				}
			);
		}
		else {
			showErrors(invalidEmail);
		}
	});
	
	function showErrors(message)
	{
		$("#errorsContainer").text(message);
		$("#errorsContainer").slideDown();
	}
	
	function hideThrobber()
	{
		$("#throbber").fadeOut();
		$("#save").fadeIn();
	}
	
	function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}
});