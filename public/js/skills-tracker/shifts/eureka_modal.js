$(function(){
	$(".open_eureka").click(function(e){
		if ( !$.browser.msie ) {
			e.preventDefault();
			blockUi(true);
	
			$.post(
				'/skills-tracker/settings/get-eureka-data',
				{ defId:  $(this).attr("data-defid"),
				  studentId: $(this).attr("data-studentid")},
				function(response){
					$("#eureka-modal-content").empty().append("<div id='eureka-home'></div>");
					$("#eureka-modal").dialog("open");
					$("#eureka-modal-content").append($(response).eurekaGraph());
					blockUi(false);
				}
			);
		}
	});
	
});