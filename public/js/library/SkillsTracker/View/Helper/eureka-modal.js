$(function(){
	initEurekaModal();
});

function initEurekaModal() {
	$("#eureka-modal").dialog({
		modal:true,
		autoOpen:false,
		resizable:false,
		width:780
	});
	
	$("#eureka-modal").parent().find(".ui-dialog-titlebar").hide();
	
	$("#close-eureka-modal").click(function(){
		$("#eureka-modal").dialog("close");
	});
}