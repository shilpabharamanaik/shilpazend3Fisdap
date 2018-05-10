$(function(){
    $("#apply").button().click(function(e) {
		e.preventDefault();
        code = $("#coupon").val();
        orderId = $("#orderId").val();
		
		if (code != "") {
			blockUi(true);
			$.post("/account/new/validate-coupon", {"code" : code, "orderId" : orderId}, function(response) {
				if (response == false) {
					$(".coupon-errors").html($("<div class='error' style='position:absolute;'>Coupon not valid.</div>").fadeIn(400).delay(2000).fadeOut(800));
					$("#couponId").val("");
					blockUi(false);
				} else {
					location.reload();
				}
			}, "json");
		}
	});
});