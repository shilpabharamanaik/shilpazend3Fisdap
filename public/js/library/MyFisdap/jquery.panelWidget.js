/**
 * @author: Jim Cummins jcummins@fisdap.net
 * @summary: Add panel functionality to a widget.
 *
 * Dependencies:
 *  jquery.actual.min.js
 *  
 * To use this widget, simply run the following:
 * $(function(){
 *   $('.myelement').panelWidget();
 * });
 * 
 * The structure of the element should be as follows:
 * .widget-container           (Container for the whole widget)
 *  .widget-header            (Header containing minimize arrow, title, controls)
 *   .widget-title            (Header title)
 *     .widget-title-text     (Variable title per panel)
 *   .widget-rotate-left      (Go to previous panel)
 *  .widget-rotate-right     (Go to next panel)
 * .widget-render             (The body of the widget)
 *  .widget-viewport         (The viewport which the panels are seen through)
 *   .widget-overflow       (The continer that holds all of the panels)
 *    .widget-panel        (A panel!)
 * 
 */

(function ($) {
	
    $.fn.panelWidgetResize = function() {

    };

    $.fn.panelWidget = function () {
      
      return this.each(function () {
        
        var contents = $('.widget-render',this);
        var panels = $('.widget-panel',this);
        var viewport = $('.widget-viewport',this);
        var overflow = $(".widget-overflow",this);
        
        // Determine if the widget is maximized, we need this for later logic & hacks
        var isMaximized = contents.is(":visible") ;//&& contents.height()>0;

        // Set the initial properties on the visible panel
        viewport.data('widget-current-panel','0');

        // Set the widget height to be the height of the first panel
        // Get the height of the first. This is a bit of a hack.
        // We have to show and hide contents quickly since it is hidden.
        // Hidden elements and their children do not have an offset
        // This should calculate the height without forcing a redraw (flicker)
        // if(!isMaximized) { contents.show(); }

        // Set each panel to be the widget width
        var contentsWidth = contents.actual('outerWidth',{ includeMargin : true });
        panels.width(contentsWidth);

        // Set the height of the widget to the first panel height
        
        // if(!isMaximized) { contents.show(); }
        // var ctheight = panels.eq(0).height();
        // if(!isMaximized) { contents.hide(); }
        // console.log('ctheight is',ctheight);
        // contents.height(ctheight);
        // viewport.height($(panels[0]).height());
        
        //if(!isMaximized) { contents.hide(); }
        
        // FUNCTIONS
        function rotatePanels(direction, context, callback) {
          
          // Convert strings to integers
          direction = parseInt(direction,10);
          
          // Left or Right button was clicked
          if(direction === -1 || direction === 1) {
            
            // Get info about panels
            var panels = $('.widget-panel', context);
            var numPanels = panels.length;
            
            // Get info about the originally active panel
            var originalPanelId = viewport.data('widget-current-panel');
            var originalPanel = panels[originalPanelId];
            var originalPanelIndex = panels.index(originalPanel);
            
            // Get info about the newly active panel
            var newPanelIndex = originalPanelIndex + direction;
       
            // Transform negative indexes into positive indexes
            if(newPanelIndex < 0) {
              newPanelIndex = numPanels + newPanelIndex;
            }
            
            // Transform overflow index to zero
            if(newPanelIndex == numPanels) {
              newPanelIndex = 0;
            }
            
            // Update the viewport with the new current index
            viewport.data('widget-current-panel',newPanelIndex);
            
            // Get the actual panel object that we're switching to
            var newPanel = $(panels[newPanelIndex]);
            
            // Check if we're maximized again
            isMaximized = contents.is(":visible"); // && contents.height()>0;
            
            // Get the offset of the newPanel. This is a bit of a hack.
            // We have to show and hide contents quickly since it is hidden.
            // Hidden elements and their children do not have an offset
            // This should calculate the offset without forcing a redraw (flicker)
            
            // TODO: actual.jquery.js does not work with offset :(
            // if(!isMaximized) { contents.show(); contents.parent().show(); }
            var newPanelOffset = newPanel.actual('offset').left - newPanel.parent().actual('offset').left;
            //var newPanelOffset = newPanel.offset().left - newPanel.parent().offset().left;
            // if(!isMaximized) { contents.hide(); // contents.parent().hide(); }




          }

          // Get the subtitle object
          var titleText = $(".widget-title-text",context);

          // show/hide the airway management report link if it exists
           var am_launch_report_link = $(".airway_management_widget_launch_report");
          if(am_launch_report_link.length > 0){
              if(newPanel.data('widget-panel-title') == "Airway Management"){
                  setTimeout(function(){
                      am_launch_report_link.fadeIn();
                  }, 600)
              }
              else {
                  am_launch_report_link.hide();
              }
          }
          
          // Swap the widget title for the correct panel
          titleText.fadeTo(100,0.0,function(){
            titleText.text(newPanel.data('widget-panel-title'));
            titleText.fadeTo(200,1.0);
          });
          
          // Simultaneously, fade out the widget contents
          overflow.fadeTo(100,0.0, function() {
            
            // Calculate the new height of the widget
            // Use hack since it might be hidden 
            // if(!isMaximized) { contents.show(); }
            newViewportHeight = newPanel.actual('height');
            // if(!isMaximized) { contents.hide(); }

            // Only resize if not minimized
            if(newViewportHeight > 0) {
              // Resize the widget to the new height
              contents.animate({
                height: newViewportHeight
              }, 800, function() {
                
              });
            }
              
            // Shift the margin based on the new panel
            overflow.animate({
              marginLeft: (newPanelOffset*-1)+'px',
              speed:0
            },0,function(){
              
              // Fade in the widget overflow container
              overflow.fadeTo(200,1.0, function(){
                
                // Execute the callback if needed
                if (typeof callback === "function") {
                  callback.apply(context);
                }
              });
            });
              
          });
            
        }
        
        // A function to dynamically resize the widget
        function resize(context, heightChange, transition) {
          

          if(typeof(heightChange)==='undefined') { heightChange=0; }
          if(typeof(transition)==='undefined') { transition='easeOutElastic'; }
          
          var newHeight = panels.eq(viewport.data('widget-current-panel')).actual('outerHeight',{ includeMargin : true })+heightChange;

          // Resize the widget to the new height
          contents.animate({
             height: newHeight
          }, 600, transition, function() {
                  
          });
        }
        
        function expand(context) {
          currentPanelId = viewport.data('widget-current-panel');
          currentPanel = $(panels[currentPanelId]);
          currentPanel.children().last().clone().appendTo(currentPanel);
          resize(context);
        }
        
        function contract(context) {
          currentPanelId = viewport.data('widget-current-panel');
          currentPanel = $(panels[currentPanelId]);
          currentPanel.children().last().remove();
          resize(context);
        }
        
        // PUBLIC BINDINGS
        
        // Bind onclick handle and make sure it executes in the widget context, not controls context
        $('.widget-rotate-right',this).on('click', $.proxy(function() {
          return rotatePanels(1,this);
        },this));
        
        $('.widget-rotate-left', this).on('click', $.proxy(function () {
          return rotatePanels(-1,this);
        },this));
        
        $('.expand',this).on('click', $.proxy(function() {
          return expand(this);
        },this));
        
        $('.contract',this).on('click', $.proxy(function() {
          return contract(this);
        },this));

        $('.goals-widget-header',this).on('click', function(){
            var el = $(this);
            var contents = el.nextAll('.goals-widget-category-container').first();
            var direction = 1;

            // Change slide direction if the contents are already visible
            if(contents.is(':visible')) {
              direction = -1;
              $('.category-minimize-container .category-maximize',el).hide();
              $('.category-minimize-container .category-minimize',el).show();
            } else {
              $('.category-minimize-container .category-maximize',el).show();
              $('.category-minimize-container .category-minimize',el).hide();
            }

            // Redraw the parent widget
            resize(
              el.closest('.widget-container'),
              contents.actual( 'outerHeight', { includeMargin : true })*direction,
              'swing'

            );
        });

        $('.goals-widget-header',this).on('click', function() {
          $(this).nextAll('.goals-widget-category-container').first().slideToggle();
        });
        
        $('.expand-all',this).on('click', function(e) {
            e.preventDefault();
            $(this).parents(".widget-panel").find('.category-maximize').show();
            $(this).parents(".widget-panel").find('.category-minimize').hide();
            
            $(this).parents(".widget-panel").find(".goals-widget-category-container").slideDown(400,
                function(){
                    // Redraw the panel
                    resize(null, null, 'swing');
            });
        });
        
        // links to reports
        $(".launch-report-config", this).on('click', function(e){
        	e.preventDefault();
        	
        	var reportType = $(this).attr("data-reporttype");
        	var studentId = $(this).attr("data-studentid");
            var goalSetId = $(this).attr("data-goalSetId");
        	
        	$.post("/reports/index/create-report-config",
        		{
        			"reportType": reportType,
        			"studentId": studentId,
                    "goalSetId" : goalSetId
        		},
        		function (response) {
        			if (response > 0) {
        				var url = "/reports/index/display/report/"+reportType+"/config/"+response;
        				window.location = url;
           			} else {
        				location.reload(); 
        			}
        		}
        	)
        });
        
      });
    };

    // Utility function to enable quick shadow DOM selection for data elements by value
    $.fn.filterByData = function(prop, val) {
        return this.filter(
            function() { return $(this).data(prop)==val; }
        );
    };
})(jQuery);