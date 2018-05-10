$(function(){
	$("#test1").click(function(e){
		e.preventDefault();
		$.post(
			'/skills-tracker/settings/get-eureka-data',
			{ defId:  $(this).parent().find(".def-id").text(),
			  studentId: $(this).parent().find(".student-id").text()},
			function(response){
				$("#eureka-modal-content").empty().append("<div id='eureka-home'></div>");
				$("#eureka-modal").dialog("open");
				$("#eureka-modal-content").append($(response).eurekaGraph());
			}
		);

	});
	
});