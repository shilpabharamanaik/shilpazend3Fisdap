/****************************************************************************
*                                                                           
*         Copyright (C) 1996-2011.  This is an unpublished work of          
*                          Headwaters Software, Inc.                        
*                             ALL RIGHTS RESERVED                           
*         This program is a trade secret of Headwaters Software, Inc.       
*         and it is not to be copied, distributed, reproduced, published,   
*         or adapted without prior authorization                            
*         of Headwaters Software, Inc.                                      
*                                                                           
****************************************************************************/

$(function(){
	function positionNav(){
		// Start off by displaying everything...
		// Need to do this so that the draw-widths can be properly calculated
		// for positioning.
		$('.active').children('ul').css('display', 'block');
		
		// Store these guys for use in positioning the lists...
		mainNavOffset = $('.header_nav').offset();
		maxX = $('.header_subnav').position().left + $('.header_subnav').width();
		minX = 30;
		
		subNavYLocation = $('.header_subnav').position().top-13;
		
		subNavHeight = $('.header_subnav').height();
		subNavWidth = $('.header_subnav').width();
		
		$('.main_navigation').children('li').each(function(index, el){
			// Ok, now figure out where exactly to position the sub menu.
			// Need to figure out what its width is, and
			
			newWidth = 0;
			
			$(el).children('ul').children('li').each(function (index, el){
				newWidth += $(el).width();
			});
			
			$(el).children('ul').css('text-align', 'center');
			$(el).children('ul').css('width', newWidth + 'px');
			
			// Loop here...  We don't want the height of the new element to
			// exceed the height of the sub-nav bar container.
			// This is necessary because on some browsers the height is calculated
			// differently, and sometimes it'll wrap.  And actually it's Chrome,
			// not IE.  Probably Safari too because Safari hates me.
			if($(el).children('ul').height() > subNavHeight){
				while($(el).children('ul').height() > subNavHeight && newWidth < subNavWidth){
					newWidth+=5;
					$(el).children('ul').css('text-align', 'center');
					$(el).children('ul').css('width', newWidth + 'px');
				}
			}
			
			// This should be the horizontal center of the tab (the x-pos)
			curOffset = $(el).position().left + ($(el).width()/2);
			
			// Assume it's going to work.  Adjust if it's too far off to a side
			// The -35 is to offset the default padding put on by the fact that
			// it's a UL > LI.  Not sure how else to address it...
			newX = curOffset - ((newWidth)/2);
			
			if(newX < minX){
				newX = minX;
			}else if(newX + newWidth >= maxX){
				newX = subNavWidth - newWidth - 25;
			}
			
			$(el).children('ul').css('position', 'absolute');
			
			// Add on a bit of padding to shift it down a tad...
			$(el).children('ul').css('top', subNavYLocation+10);
			$(el).children('ul').css('left', newX);
			
			if(!$(el).hasClass('active')){
				$(el).children('ul').hide();
			}
			
			// If the tab has the active link, make the default width a bit
			// wider to account for it's boldness and paddings.
			if($(el).hasClass('active')){
				$(el).children('ul').width($(el).children('ul').width()+10);
			}
			
			// Update the Y axis on the text on the main tabs to try to center
			// it.  Only do it if it's sufficiently high on the tab.
			$(el).children('a').each(function (index, el){
				if($(el).height() < $(el).parent().height()){
					$(el).css('position', 'relative');
					$(el).css('top', '2px');
				}
			});
		});
	}

	function setupMouseover(){
		//Create variable for timer
		var t;
		
		// Set up the hover action for the tabs..
		$('.main_navigation').children('li').each(function(index, el){
			$(el).mouseenter( function(){
				clearTimeout(t);
				
				// Hide all of the other lists.  Kind of need to do it this way
				// in order to make it so we can still mouse out and click on
				// the links in the sub listing.
				$(el).parent().children('li').children('ul').hide();
				$(el).children('ul').show();
				
				// This is to fix Webkit.  Basically force it to rerender the
				// to remove its weird artifacting issues.
				$('.main_header').toggle().toggle();
			});
			
			$(el).mouseleave( function () {
				t = setTimeout(function() {
					$(el).parent().children('li').children('ul').hide();
					$('.main_navigation > li.active').children('ul').show();
					$('.main_header').toggle().toggle();
				}, 4000);
			});
		});
	}
	
	function removeActiveLinks(){
		link = $('.main_navigation li ul li.active a');
		li = link.parent();
		
		newSpan = $('<span></span>');
		newSpan.html(link.html());
		newSpan.addClass('active_sub_nav');
		
		li.empty();
		li.append(newSpan);
	}
	
	if ($("#header_nav").length > 0) {
		setupMouseover();
		positionNav();
		removeActiveLinks();
	}
	
});