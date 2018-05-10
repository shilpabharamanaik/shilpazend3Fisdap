$(function(){
	$(".send-product-code-link").click(function(e){
		e.preventDefault();
        productCode = $(this).parents("div.product-code-container").find("span.product-code").text();
        $("#sendEmailsDialog").find("#productCode").val(productCode);
        $("#sendEmailsDialog").dialog("open");
    });    
});