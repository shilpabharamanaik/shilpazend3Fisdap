$(function(){
	initNavBar();
});

function initNavBar() {
	
	formatForBrowser();
	
	// adjust the sub options width and positioning
	$(".nav-bar-menu-item").each(function(){
		var width = $(this).width();
		
		if (width != 0) {
			var dropdown = $("#" + $(this).attr("data-dropDownId"));
			if ($(this).css("margin-right") == "0px") {
				width_offset = 71;
				if ($.browser.safari) {
					width--;
				}
			}
			else {
				width_offset = 71;
			}
			
			var top = $(this).offset().top+$(this).height()+9;
			var left = $(this).offset().left;
			
			if ($("#nav-bar").find(".settings-link").length > 0){
				width_offset++;
			}
			else {
				width++;
				width_offset++;
				
				if ($.browser.safari) {
					width = width-1;
				}
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

}

function formatForBrowser() {
	// style for windows firefox
	if ($.browser.mozilla){
		if (navigator.platform.indexOf('Mac') == -1) {
			$("#nav-bar").css("height", "32px");
		}
    }
}