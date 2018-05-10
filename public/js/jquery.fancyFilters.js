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
 * jQuery plugin for creating "fancy filters"
 */

(function( $ ){
	/*
	 * options.closeOnChange: boolen - determines when the filter will close.
	 * True will close the fancy filter after a form element has been changed.
	 * According to uur UX standards, this should be set to “true” ONLY when
	 * there is only one filtering option and “false” if there is more than one
	 * filtering option.
	 * 
	 * options.width: int - the width of the fancy filter. Will default to 785,
	 * does not support percentages, do not include the “px”
	 * 
	 * options.clearFilters: boolean - will include a “clear filters” button if
	 * set to true. This will trigger the same ajax call, but will clear the
	 * form before doing so. “closeOnChange” will determine the behavior after
	 * clicking “clear filters”.
	 * 
	 * options.onFilterSubmit: The ajax request for the form and whatever you
	 * want it to do after. This function is used whenever a form element within
	 * your fancy filter has been changed. In order for the form to properly
	 * disable/enable and display the throbber, use the “return then done” syntax
	 * on your ajax request.
	*/
	$.fn.fancyFilter = function(options) {

		// set up some default values
		if(options.closeOnChange == undefined){options.closeOnChange = false;}
		//if(options.closeOnGo == undefined){options.closeOnGo = false;}
		if(options.width  == undefined){options.width = 785;}
		if(options.clearFilters  == undefined){options.clearFilters = false;}
		
		var filterOptionsWrapperWidth = options.width-1;
		var filterOptionsWidth = options.width - 15;
		var filtersOpen = false;
		this.addClass("filters-wrapper");
		var uniqueId = this.attr("id");
		
		// grab the div's current contents and save it for later
		var form = this.html();
		this.empty(); // empty it so we can create the structure

		// give it the fancy filter structure
		var content = 	'<div class="filters" id="' + uniqueId + '_filters">';
		content +=  		'<h3 class="bottom-rounded-corners">';
		content +=				'<button id="' + uniqueId + '_filters-title" class="filters-title">';
		content +=					'<div id="' + uniqueId + '_filters-title-icon" class="filters-title-icon"><img id="' + uniqueId + '_plus" class="plus" src="/images/icons/filter_arrow_right.png"></div>';
		content +=					'<div id="' + uniqueId + '_filters-title-text" class="filters-title-text">Filters</div>'
		content +=				'</button>';
		content +=			'</h3>';
		content +=			'<div id="' + uniqueId + '_filter-options-wrapper" class="filter-options-wrapper">';
		content +=				'<div id="' + uniqueId + '_filter-options" class="filter-options">';
		content +=					'<div id="' + uniqueId + '_filter-form" class="filter-form"></div><div class="clear"></div>';
		content +=				'</div>';
		content +=			'</div>';
		content +=		'</div>';
		content +=		'<div id="' + uniqueId + '_fancy-filter-throbber" class="fancy-filter-throbber"><img src="/images/throbber_small.gif"></div>';
		
		this.html(content);
		$("#" + uniqueId + "_filter-form").append(form); // put their form/contents back

		// set the width
		this.css("width", options.width + "px");
		$("#" + uniqueId + "_filter-options-wrapper").css("width", filterOptionsWrapperWidth + "px");
		$("#" + uniqueId + "_filter-options").css("width", filterOptionsWidth + "px");
		
		// some browser specific adjustment
		if(jQuery.browser.msie) {$("#" + uniqueId + "_filter-options-wrapper").css("top", "1.8em");}
		if(jQuery.browser.mozilla) {$("#" + uniqueId + "_filter-options-wrapper").css("top", "2em");}
		if(jQuery.browser.msie || jQuery.browser.mozilla){
			$("#" + uniqueId + "_filters-title").css("padding-bottom", "3px");
			$("#" + uniqueId + "_filters-title").css("padding-top", "1px");
		}
		
		// clear filters function
		if(options.clearFilters){
			$("#" + uniqueId + "_filter-options").append("<div id='" + uniqueId + "_clear-filters-wrapper' class='clear-filters-wrapper'><button id='" + uniqueId + "_clear-filters-btn' class='clear-filters-btn'>clear filters</button></div>");
			$("#" + uniqueId + "_clear-filters-btn").click(function(e){
				e.preventDefault();
				// reset the form
				$("#" + uniqueId + "_filter-form :input").each(function(){
					if(!$(this).hasClass('do-not-filter')) {
						$(this).val("");
					}
				})
				handleFilterSubmit(e);
			});
		}
		
		/*
		// close the filters when closeOnGo is clicked
		if (options.closeOnGo != false) {
			options.closeOnG.click(function(){
				closeFilters();
			});
		}*/
		
		// open/close filters using the title bar
		$("#" + uniqueId + "_filters-title").click(function(e){
			e.preventDefault();
			$(this).blur();
			$(this).css("outline", "none");
			if(!$(this).parent().hasClass("bottom-rounded-corners")){
				closeFilters();
			}
			else {
				$("#" + uniqueId + "_filter-options").slideDown();
				$(this).parent().removeClass("bottom-rounded-corners");
				filtersOpen = true;
				$("#" + uniqueId + "_filters-title-icon").find(".plus").remove();
				$("#" + uniqueId + "_filters-title-icon").prepend("<img id='" + uniqueId + "_minus' class='minus' src='/images/icons/filter_arrow_down.png'>");
			}
		});
		
		$("#" + uniqueId + "_filters-title").focus(function(){
			$(this).css("outline", "none");
		});
		
		$("#" + uniqueId + "_filters-title").find(".filters-title-icon").click(function(e){
			e.stopPropagation();
			$("#" + uniqueId + "_filters-title").trigger("click");
		});
		
		// call the actual 'onFilterSubmit' method to be used by the individual
		$("#" + uniqueId + "_filter-form :input").each(function(newUniqueEvent){
			if($(this).hasClass("hasDatepicker")){
				$(this).datepicker();	
			}
			if(!$(this).hasClass("do-not-filter")){
				$(this).change(function(){
					handleFilterSubmit(newUniqueEvent);
				});
			}
		});
		
		// handle the ajax request trigger thing
		function handleFilterSubmit(e){
			$("#" + uniqueId + "_fancy-filter-throbber").show();
			disableAllElements(true);
			
			if(options.onFilterSubmit !== undefined){
				// wait until the ajax call/or whatever they're doing is done
				// then enable the elements, hide the throbber and close the filter if clsoeOnChange is true
				$.when(options.onFilterSubmit(e)).then(function(){
						$("#" + uniqueId + "_fancy-filter-throbber").fadeOut();
						disableAllElements(false);
						if(options.closeOnChange){
							closeFilters();
						}
				});
			}
		}
		
		// if they click on anything other than whats inside the filter, close it
		$('html').click(function(e) {
			var target = e.target;
			if(!inFiltersWrapper($(target))){
				if (!$(target).hasClass("search-choice-close")) {
					closeFilters();
				}
			}
		});
		
		// closes the filters
		function closeFilters(){
			$("#" + uniqueId + "_filter-options").slideUp();
			$("#" + uniqueId + "_filters-title").parent().addClass("bottom-rounded-corners");
			if($("#" + uniqueId + "_filters-title-icon").find(".minus").length != 0){
				$("#" + uniqueId + "_filters-title-icon").find(".minus").remove();
				$("#" + uniqueId + "_filters-title-icon").prepend("<img id='" + uniqueId + "_plus' class='plus' src='/images/icons/filter_arrow_right.png'>");
			}
			filtersOpen = false;
		}

		// disables all form elements within the fancy filter
		// unless param is set to false (then it enables all elements)
		function disableAllElements(disabled){
			$('#' + uniqueId + '_filter-form :input').attr('disabled', disabled);
		}
		
		// returns true if the element is within the "filters" div
		function inFiltersWrapper(element){
			isInFiltersWrapper = false;
			if (element.attr('id') == 'cd-filters-wrapper_plus') {
				isInFiltersWrapper = true;
			}
			element.parents().each(function(){
				if($(this).attr("id") == uniqueId + "_filters" || $(this).hasClass("ui-datepicker-header")){
					isInFiltersWrapper = true;
				}
			});
			return isInFiltersWrapper;
		}
		
	};
		
})( jQuery );

