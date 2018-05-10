$(function(){
	errorDivs = $(".form-errors");
	if (errorDivs.length > 0) {
		$('body').animate({ scrollTop: errorDivs.offset().top });
	}
	
	$("#saveButton").click(function(e){
		blockUi(true);
	});
});