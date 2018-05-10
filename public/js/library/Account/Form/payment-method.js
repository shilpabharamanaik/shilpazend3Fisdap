$(function(){
	$("#confirmPayment").click(function(e){
		e.preventDefault();
		$.post("/account/orders/process-payment-method", $("form").serialize(), function(response){window.location="/account/orders/billing"}, "json");
	});
});