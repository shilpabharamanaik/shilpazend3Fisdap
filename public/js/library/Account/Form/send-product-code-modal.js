$(function(){
	//Note: The preview-email-container div is automatically hidden each time the modal is opened.
	//You'll see the code that does this in Account_Form_SendProductCodeModal.php
	
	$("#preview-email").click(function(e){
		e.preventDefault();
		
		if ($("#preview-email-container").is(":visible")) {
			$("#preview-email-container").toggle(600);
		} else {
			$.post("/account/orders/preview-product-code-email", $(this).parents("form").serialize(), function(response) {
				$("#preview-email-container").html(response);
				$("#preview-email-container").toggle(600);
			});
		}
		
	});
});