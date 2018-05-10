/* Author: Bill Chapman

Modified by Alex Stevenson 09/13/2012

Desc: Turn a select box in to an accordion
Code Standards: methods and hash keys are camelCase and variables are under_scored
Requirements: jquery-ui for accordion and modal
Usage: Works on a select box with options groups, converts it to an accordion
Output: HTML for accordion format that is than converted to accordion by jquery ui
<div id="accordion">
<a href="#">First header</a>
<div>First content</div>
<a href="#">Second header</a>
<div>Second content</div>
</div>

Usage: $('element').accordionSelect({options},callBackMethod = function(id,value) );
The call back method will have an id and a value passed in synononmous to value and text
for a select box entry
 */

//Should allow noConflict compatibility
(function($) {

	$.fn.accordionSelect = function(options, callBack) {

		// all options are initialized here for reference, options provided
		// externall will take precidence
		options = $.extend({
			containerId : "container_for_accordion",
			linkText : "Click here to select an item",
			target : 'self',
			selected: {}
		}, options || {});

		// Take the select box that this was called upon and convert it to an
		// accordion
		/*
		 * We create the structure required by teh jquery ui accordion and
		 * attach a callback function to each link
		 */
		var accordion_container = $("<div id='" + options.containerId + "'></div>");

		var optgroups = $(this).children('optgroup');
		
		var counter = 0;
		
		optgroups.each(function() {
			if ($(this).text().length > 0) {
				counter++;
				
				accordion_container.append($("<div><a href='#'>" + $(this).text() + "</a></div>"));
				
				var option_container = $("<div class='option_container' style='overflow: none'/>");
				var optionGroups = $(this).children('option');
				
				var submenu = $("<select style='width: 95%' multiple='multiple' id='accordion_sub_" + counter + "' name='" + $(this).parent().attr('name') + "[]'></div>");
				
				optionGroups.each(function() {
					singleOption = $("<option value='" + $(this).val() + "'>" + $(this).html() + "</option>");
					
					if($.inArray($(this).val(), options.selected) > -1){
						singleOption.attr('selected', 'SELECTED');
					}
					
					submenu.append(singleOption);
				});
				
				option_container.append(submenu);
				
				accordion_container.append(option_container);
			}
		});

		// If you set modal to true it pops up the accordion in a modal window.
		// otherwise it either
		// replaces the select box (self) or appends to the specified target
		// element
		if (options.target == 'modal') {

			var replacement_link = $("<a href='#'>" + options.linkText + "</a>");
			var replacement_storage = $("<input type='text'></input>");
			replacement_storage.attr("name", $(this).attr("name"));

			$(this).replaceWith(replacement_link);
			// bind the modalization and accordion creation to the link that
			// replaced the select box
			replacement_link.bind('click', {
				container : accordion_container,
				storage : replacement_storage,
				modal : options.modal
			}, function(event) {
				event.data.container.appendTo('body');
				event.data.container.accordion({
				// leave defaults on accordion for now
				}).dialog({
					modal : true
				});
			});

			// replace this component with the accordion
		} else if (options.target == 'self') {
			$(this).replaceWith(accordion_container);
			accordion_container.accordion();

			// the accordion should be placed inside of a target element
		} else {
			$(options.target).append(accordion_container);
			accordion_container.accordion({autoheight: false});
			$('div[role="tab"]').each(function(el, i){
				$(el).addClass('thin-tab');
			})
		}
	}

})(jQuery);