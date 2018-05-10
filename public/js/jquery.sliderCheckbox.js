

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * jQuery plugin for creating "slider checkboxes"
 */

(function( $ ){
	$.fn.sliderCheckbox = function(options) {
		

		// set up some default values
		if(options.onText == undefined){options.onText = "On";}
        if(options.onBackgroundColor == undefined){options.onBackgroundColor = "#3BAAE3";}
        if(options.onBorderColor == undefined){options.onBorderColor = "#0f3f74";}
		if(options.offText == undefined){options.offText = "Off";}
        if(options.offBackgroundColor == undefined){options.offBackgroundColor = "#888";}
        if(options.offBorderColor == undefined){options.offBorderColor = "#666";}
		if(options.width == undefined){options.width = 40;}
		if(options.disabled == undefined){options.disabled = false;}
		
		
		var oldCheckbox = this;
		this.hide();
		
		var uniqueId = oldCheckbox.attr("id");
		var defaultChecked = false;
		
		if(oldCheckbox.attr("checked")){
			defaultChecked = true;
		}
		
		var defaultDisplayText = options.offText;
		if(defaultChecked){
			defaultDisplayText = options.onText;
		}
		
		var content = '<div id="' + uniqueId + '-stage" class="stage">';
        content +=		'<div class="slider-frame">';    
        content +=   		'<span class="slider-button" id="' + uniqueId + '-slider-button">' + defaultDisplayText + '</span>';
        content +=    	'</div>';
        content += 	   '</div>';
		
		this.parent().html(content);
		$('#' + uniqueId + '-stage').append(oldCheckbox);
		
		$('#' + uniqueId + '-slider-button').parent().css("width", options.width+20);
        $('#' + uniqueId + '-slider-button').css("width", options.width);
		

        $('#' + uniqueId + '-slider-button').css("background", options.offBackgroundColor);
        $('#' + uniqueId + '-slider-button').css("border-color", options.offBorderColor);
        
        if($('#' + uniqueId + '-slider-button').hasClass("on")){
            $('#' + uniqueId + '-slider-button').css("background", options.onBackgroundColor);
            $('#' + uniqueId + '-slider-button').css("border-color", options.onBorderColor);
        }
		
		if (options.disabled == true) {
			$('#' + uniqueId + '-slider-button').addClass("disabled");
		}
		
		$('#' + uniqueId + '-slider-button').click(function(e){
			e.stopPropagation();
			if (!$(this).hasClass("disabled")) {
				if($(this).hasClass("on")){
					$(this).removeClass('on').html(options.offText).parent().next('input[type="checkbox"]').removeAttr('checked');
					$(this).css("background", options.offBackgroundColor);
					 $(this).css("border-color", options.offBorderColor);
					 oldCheckbox.val(0);
				}
				else {
					$(this).addClass('on').html(options.onText).parent().next('input[type="checkbox"]').attr('checked', 'checked');
					$(this).css("background", options.onBackgroundColor);
					$(this).css("border-color", options.onBorderColor);
						 oldCheckbox.val(1);

				}
			
				oldCheckbox.trigger("change");
			}
		});
		
		if(defaultChecked){
			$('#' + uniqueId + '-slider-button').addClass('on').html(options.onText);
			
			$('#' + uniqueId + '-slider-button').css("background", options.onBackgroundColor);
			$('#' + uniqueId + '-slider-button').css("border-color", options.onBorderColor);
        
		}
		
	};
		
})( jQuery );

$("#slider").sliderCheckbox({onText: 'Compliant', offText: 'Non-compliant', width:110, onBackgroundColor:"#888", offBackgroundColor:"#ed2125"});