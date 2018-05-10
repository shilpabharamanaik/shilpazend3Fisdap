$(function(){
	formatForBrowser();
	
	// adjust the sub options width and positioning
	$(".compliance-nav-bar-menu-item").each(function(){
		var width = $(this).width();
		
		if (width != 0) {
			var dropdown = $("#" + $(this).attr("data-dropDownId"));
			if ($(this).css("margin-right") == "0px") {
				width_offset = 72;
				if ($.browser.safari) {width--;}
			}
			else {
				width_offset = 71;
				if ($.browser.mozilla){
					width++;
				}
			}
			
			var top = $(this).offset().top+$(this).height()+9;
			var left = $(this).offset().left;
			
			if ($("#portfolio-page-content").length > 0 || $("#settings-wrapper").length > 0){
				top = $(this).position().top+$(this).height()+9;
				left = $(this).position().left;
			}

			
			dropdown.css("width", width+width_offset).css("left", left).css("top", top);
			
			$(this).hover(function(){
				dropdown.fadeIn(100);
			}, function(e) {
				dropdown.fadeOut(50);
			});
		}
		else {
			$(this).remove();
		}
		
	});
	
	window.isHovering = function (selector) {
        return $(selector).data('hover')?true:false; //check element for hover property
    }
	
	if ($("#portfolio-page-content").length > 0) {
		$("#portfolio-page-content").css("border-top", "0px");
	}
	
	if ($(".student-compliance-nav-bar").length > 0) {
		
		/*
		var special_class ="top-right-radius-option";
		var current_page = $(".student-compliance-nav-bar").attr("data-currentpage");
		
		if (current_page == "shift_requests" || current_page == "portfolio") {
			$(".single-navbar-option").first().addClass(special_class);
		}*/
		
	}
	
});

function formatForBrowser() {
	// style for windows firefox
	if ($.browser.mozilla){
		if (navigator.platform.indexOf('Mac') == -1) {
			$("#compliance-nav-bar").css("height", "32px");
		}
    }
}