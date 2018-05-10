$(function() {
	$("#first, #middle, #last").blur(updateName);
	updateName();
	
	$("#orderButton").click(function(e){
		var msg = '<p>Processing payment with Braintree, please do not close this window...</p>';
		
		blockUi(true, null, null, msg);
	});
});


function updateName() {
	first = $("#first").val();
	middle = $("#middle").val();
	last = $("#last").val();
	
	fullName = "";
	
	if (first != "") {
		fullName += first + " ";
	}
	
	if (middle != "") {
		fullName += middle + " ";
	}
	
	if (last != "") {
		fullName += last;
	}
	$("#NAME").val(fullName);
}