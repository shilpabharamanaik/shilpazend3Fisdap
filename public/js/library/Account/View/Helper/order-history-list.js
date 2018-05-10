$(function(){
	$(".order-history-container").delegate("#order-history-list tbody tr", "click", function(){
		orderId = $.trim($(this).find("td.order-id").text());
		$.post("/account/orders/get-order-history-modal", {"orderId" : orderId},
			   function(response){
					$(".order-history-modal").dialog("close");
					$(response).dialog({
						"modal": true,
						"width": "700px",
						"height": "auto",
						"resizable": false,
						"draggable": false,
						"title": "Order Details"
					});
				}, "json");
	});
});