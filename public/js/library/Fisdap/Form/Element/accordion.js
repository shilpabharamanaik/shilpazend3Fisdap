$(function(){
	// Take all of the accordion sections and add click handlers to them...
	$('.accordion_header').each(function(){
		el = $(this);
		
		el.click(function(e){
			updateVisibleGroup($(this).attr('data-group_id'));
		});
	});
	
	// Use this to toggle the selected elements...
	$('.accordion_option').click(function(e){
		if($(this).hasClass('accordion_option_selected')){
			$(this).removeClass('accordion_option_selected');
		}else{
			$(this).addClass('accordion_option_selected');
		}
		
		updateHiddenAccordionElements();
	});
	
	// Update the hidden field to reflect what was selected on load, and close all groups.
	updateHiddenAccordionElements();
	updateVisibleGroup();
});

var old_group_ids = {};

function updateVisibleGroup(groupId){
	if(groupId == undefined){
		groupId = null;
	}
	
	// Grab the currently selected header's ID
	oldGroupId = $('.accordion_header.selected_header').attr('data-group_id');
	
	// Hide all of them to start, remove stylings and change icons.
	$('.accordion_options').hide({duration: 50, easing: 'linear'});
	$('.accordion_header').removeClass('selected_header');
	$('.accordion_header div img').attr('src', "/images/accordion_arrow_right.png");
	
	// If the groupId is null, or if we just clicked on a different header, open that header.
	if(groupId !== null && groupId != oldGroupId){
		// Find and show the clicked one, add styles, change icon
		$('.accordion_options[data-group_id="' + groupId + '"]').show({duration: 100, easing: 'linear'});
		$('.accordion_header[data-group_id="' + groupId + '"]').addClass('selected_header');
		$('.accordion_header[data-group_id="' + groupId + '"] div img').attr('src', "/images/accordion_arrow_down.png");
	}
}

function updateHiddenAccordionElements(){
	// For each accordion container, update the hidden input for it...
	$('.accordion_container').each(function(i, e){
		selectedItems = [];
		selectedIds = [];
		
		$(e).find('.accordion_option').each(function(i2, e2){
			if($(e2).hasClass('accordion_option_selected')){
				selectedIds.push($(e2).attr('data-element_id'));
				selectedItems.push($(e2).text());
			}
		});
		
		$(e).find('#' + $(e).attr('data-element_name') + '_input').val(selectedIds.join(','));
		
		outputText = "";
		
		// Update the display text for this container...
		if(selectedItems.length == 0){
			outputText = "No items have been selected yet.  Use the box above to select one or more.";
		}else{
			resetLink = "<a href='#' onclick='return resetSelected(\"" + $(e).attr('data-element_name') + "\");'>Clear all</a>";
			outputText = "Current selected items (" + resetLink + "): " + selectedItems.join(', ');
		}
		
		$('.accordion_display[data-element_name="' + $(e).attr('data-element_name') + '"]').html(outputText);
	});
}

function resetSelected(accordionName){
	$('.accordion_container[data-element_name="' + accordionName + '"]').find('.accordion_option_selected').each(function(i, e){
		$(e).removeClass('accordion_option_selected');
	});
	
	updateHiddenAccordionElements();
	
	return false;
}