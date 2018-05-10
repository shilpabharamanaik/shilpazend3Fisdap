$(function() {
	
	setTimeout(function(){$("#paypal-btn").click()}, 4000);
	
	$("#paypal-btn").click(function(e){
		$("form").submit();
	});
});