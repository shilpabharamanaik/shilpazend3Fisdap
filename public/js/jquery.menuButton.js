// menu button
(function( $ ){
	$.fn.menuButton = function(options) {
        $(".island").css("position", "static");
		$(".fisdap-table tfoot").css("background-color", "#fff");
		
		var isMobile = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry)/);
		var isiPad = navigator.userAgent.match(/iPad/i) != null;
		
		if(isMobile){
			$('head').append('<link rel="stylesheet" href="/css/jquery.menuButton.ipad.css" type="text/css" />');
		}

		var defaultSelectValue = 0;
		var defaultSelectText = "Open New Skill";
		
        if (this.find("option:selected")) {
			defaultSelectValue = this.find("option:selected").val();
			defaultSelectText = this.find("option:selected").text();
		}

        if (options.ident == undefined) {options.defaultSelectValue = null}
        if (options.type == undefined) {options.type = "lab"}
        


        var container = $("<div class='menu-button-container " + options.type + "' id='menuButtonContainer_" + options.ident + "'></div>");
        
        var content = "";
        content +=      "<span class='selected bottomRoundCorners' value='" + defaultSelectValue + "'>" + defaultSelectText + "</span>";
        content +=      "<span class='selectArrow bottomRightRoundedCorners'>&#9660</span>";
        content +=      "<div class='selectOptions'>"
        //content +=          "<span id='open' class='selectOption' value='Open New Skill'>Open New Skill</span>";
		
		var options_contain_img = false;
		
		// get elements not in an optgroup
		if(this.find("optgroup").length == 0){
			this.children("option").each(function(index, element) {
				content += "<span class='selectOption' value='" + $(this).val() + "'>" + $(this).html() + "</span>";
			});
		}
		else {

			// get options IN an opt group
			this.children("optgroup").each(function(index, element) {
				content += "<span class='selectOptgroupName'>" + element.label + "</span>";
				
				$(this).find("option").each(function(){
					var html_to_add = $(this).html();
					
					// replace the '&lt;' to '<' and '&gt;' to '>'
					html_to_add = html_to_add.replace("&lt;","<");
					html_to_add = html_to_add.replace("&gt;",">");
					
					if (html_to_add.indexOf("img") != -1) {
						options_contain_img = true;
					}
					
					content += "<span class='selectOption' value='" + $(this).val() + "'>" + html_to_add + "</span>";
				});
				
			});
			
		}
		
		var closed = true;
		var width = this.width() + 43;
		
		if (options_contain_img) {
			// account for the funny width situation
			width = width/2;
		}
		
		if(isMobile){
			width = width * 1.3;
		}
		
		
		
        content += "</div>";
        container.html(content);
        
        this.parent().append($(container));
        this.remove();

        $(container).children('span.selected,span.selectArrow').click(function(){
            if(closed){open();}
            else{close();}
        });
		
		if(defaultSelectValue != 0){
			$(container).find('span.selectArrow').html('<a id="goLink" href="#">Go</a>');
			$(container).find("#goLink").click(function(e){
				e.preventDefault();
				e.stopPropagation();
				//window.alert("opening " + $(container).attr('value'));
				options.onFilterSubmit(e);
			});
		}
		
		$("div.menu-button-container span.selected").css("width", width);
		$("div.selectOptions").css("width", width+56);

		if(isMobile){
			$("div.selectOptions").css("width", width+75);
		}
		
		if(width > 300) {
			$(".selected").css("background-position-x", "2.2%, left");
		}
        
        $(container).find('span.selectOption').click(function(){
            close();
            $(this).parent().parent().find('.selected').attr("value", $(this).attr("value"));
            
			// Set the value of the div to be the value of this object
			this.value = $(this).attr('value');
			
			if($(this).attr('value') == 0){
                $(container).find('span.selectArrow').html('&#9660');
            }
            else {
                $(container).find('span.selectArrow').html('<a id="goLink" href="#">Go</a>');
                $(container).find("#goLink").click(function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    //window.alert("opening " + $(container).attr('value'));
                    options.onFilterSubmit(e);
                });
            }
            $(this).parent().siblings('span.selected').html($(this).html());
        });
        
		// if they click on anything else close it
		$('html').click(function(e) {
			var target = e.target;
			if(!$(target).hasClass("selected") && !$(target).hasClass("selectOptgroupName") && !$(target).hasClass("selectArrow")){
				close();
			}
		});
		
		if ($.browser.mozilla){
			$(".selected").css("background-position", "5%, bottom");
			
					
			if(width > 300) {
				$(".selected").css("background-position", "2.2%, bottom");
			}
		}
		
		if(isiPad){
			$(".selectOptions").css("-webkit-overflow-scrolling", "touch");
		}
		
        function close() {
            closed = true;
			$(container).find(".selected").addClass("bottomRoundCorners");
			$(container).find(".selectArrow").addClass("bottomRightRoundedCorners");
            $(container).children('div.selectOptions').hide();
        }
        
        function open() {
            closed = false;
			$(container).find(".selected").removeClass("bottomRoundCorners");
			$(container).find(".selectArrow").removeClass("bottomRightRoundedCorners");
            $(container).children('div.selectOptions').css("top", $(container).offset().top + $(container).height()).css("left", $(container).offset().left).show();
        }
    };
})( jQuery );