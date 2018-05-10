$(function(){
	$("#startDate").addClass("selectDate");
	$("#endDate").addClass("selectDate");
	
	$("#startDate, #endDate").change(function(){
		$.post("/account/orders/get-order-history-list", {"startDate" : $("#startDate").val(), "endDate" : $("#endDate").val()},
			   function(response){
				$(".order-history-container").html($(response).fadeIn(800));
			   }, "json");
	});
});