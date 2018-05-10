$(function(){
	
	setNewColor();
	
	function setNewColor() {
		var color = $("#color").val();
		var isOk  = /^(#)?([0-9a-fA-F]{3})([0-9a-fA-F]{3})?$/.test("#" + color);
		if (isOk) {
			$("#color-preview").css("background-color", "#" + color);
			$("#color-preview-x").remove();
		}
		else {
			$("#color-preview").css("background-color", "#fff");
			$("#color-preview").append("<img src='/images/icons/delete.png' id='color-preview-x'>");
		}
	}
	
	$("#color").change(function(){
		setNewColor();
	});
	
	$(".use-default").click(function(e){
		e.preventDefault();
		$("#" + $(this).attr("data-type")).val($(this).attr("data-value")).css("background-color", "#FFEFC2").animate({ backgroundColor: "#fff"}, 1000);;
	 setNewColor();
	});
	
	$("#see-colors").click(function(e){
		e.preventDefault();
		toggleRecentlyUsed($("#see-colors").find("img"), $("#used-colors"));
	});
	
	$("#see-statuses").click(function(e){
		e.preventDefault();
		toggleRecentlyUsed($("#see-statuses").find("img"), $("#used-statuses"));
	});
	
	function toggleRecentlyUsed(image, hiddenDiv) {
		if (image.attr("src").indexOf("right") != -1) {
			// arrow is facing right, we'll drop down and change the img
			hiddenDiv.slideDown();
			image.attr("src", "/images/arrow_down.png");
		}
		else {
			// arrow is facing down, we'll slide up and change the img
			hiddenDiv.slideUp();
			image.attr("src", "/images/arrow_right.png");
		}
	}

	
});