$(function(){
	
	initToolTips();
	
	if($.browser.msie){
		if(jQuery.browser.version == 8.0){
			$("#baseEditDisabled").css("background-color", "white");
			$("#preceptorEditDisabled").css("background-color", "white");
			$("#preceptorMergeDisabled").css("background-color", "white");
		}
	}
	
	function initToolTips(){
		$("#activateDiv").hide();
		$("#deactivateDiv").hide();
		$("#deactivateAllDiv").hide();
		$("#activateAllDiv").hide();
		
		$("#activatePrecepDiv").hide();
		$("#deactivatePrecepDiv").hide();
		$("#activateAllPrecepDiv").hide();
		$("#deactivateAllPrecepDiv").hide();
		
		$("#addProgramDiv").hide();
		$("#removeProgramDiv").hide();
		
		setToolTipFunctions($("a#addBase"), $("#activateDiv"));
		setToolTipFunctions($("a#removeBase"), $("#deactivateDiv"));
		setToolTipFunctions($("a#addAll"), $("#activateAllDiv"));
		setToolTipFunctions($("a#removeAll"), $("#deactivateAllDiv"));
		
		setToolTipFunctions($("a#addPreceptor"), $("#activatePrecepDiv"));
		setToolTipFunctions($("a#removePreceptor"), $("#deactivatePrecepDiv"));
		setToolTipFunctions($("a#addAllPreceptors"), $("#activateAllPrecepDiv"));
		setToolTipFunctions($("a#removeAllPreceptors"), $("#deactivateAllPrecepDiv"));
		
		setToolTipFunctions($("a#addProgram"), $("#addProgramDiv"));
		setToolTipFunctions($("a#removeProgram"), $("#removeProgramDiv"));
	}
	
	

	
	//Bind this keypress function to all of the input tags
	$("input").keypress(function (evt) {
		//Deterime where our character code is coming from within the event
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) { //Enter key's keycode
			return false;
		}
	});

	handlerObject = initCustomTabs('custom-tabs', document.location.hash);
	
	var somethingChanged = false;
	
	function setToolTipFunctions(trigger, toolTip){
		trigger.hover(function(){
			toolTip.fadeIn("fast");
		}, function() {
			toolTip.fadeOut("fast");
		});
	}
	

	$('#topLeftElements input').change(function() { 
		somethingChanged = true; 
		$("#something-changed").val(1);
	});
	 
	$('#bottomElements input').change(function() { 
		somethingChanged = true; 
		$("#something-changed").val(1);
	});
	 
	$("#moveButtons a").click(function(event) { 
		somethingChanged = true; 
		$("#something-changed").val(1);
	});
	
	$("#moveButtonsPreceptors a").click(function(event) { 
		somethingChanged = true; 
		$("#something-changed").val(1);
	});
	
	$("#Close").click(function(event) {
        event.preventDefault();
		if($('#errorContainer').length){
			var errorsOnPage = true;
		}
		else {
			var errorsOnPage = false;
		}
		if(somethingChanged || errorsOnPage){
			var r = confirm("You made changes that haven't been saved yet.");
			if(r == true){
				window.location = "/account/sites"
			}
			else {
				
			}
		}
		else if ($("#addEditText").text() == "Add"){
			var r = confirm("If you leave the page now, this site won't be saved for your program.");
			if(r == true){
				window.location = "/account/sites"
			}
			else {
				
			}
		}
		else {
			window.location = "/account/sites"
		}
	});
	
	
	$("#save").click(function(event) {

		var throbber =  $("<img id='saveThrobber' src='/images/throbber_small.gif'>");
		$("#saveThrobberWrapper").append(throbber);
		
		
	})
	
	
});


// THE TABS
initCustomTabs = function(tabTagId, defaultSelectedSpan) {
	var selectedSpan = defaultSelectedSpan;
	
	if(defaultSelectedSpan == ''){
		selectedSpan = "#siteInfo";
	}
	
	refreshTabs = function(newSelectedSpan){
		// Hide the current selected tab...
		$(selectedSpan).hide();
		
		$('#' + tabTagId + ' span a').each(function(index, el){
			tabIndex = index+1;
			if($(el).attr('href') == newSelectedSpan){
				// Never toggle the selected one, and default it to being selected
				$("#tab_img_" + tabIndex).css('display', 'none');
				$("#tab_img_over_" + tabIndex).css('display', 'inline');
				
				$(newSelectedSpan).show();
				
				$('#ticker_' + tabIndex).css('display', 'inline');
			}else{
				$($(el).attr('href')).hide();
				$("#tab_img_over_" + tabIndex).css('display', 'none');
				$("#tab_img_" + tabIndex).css('display', 'inline');
				$('#ticker_' + tabIndex).css('display', 'none');
			}
		});
		
		selectedSpan = newSelectedSpan;
		initialPageLoad = false
	};


	// Set up the click handler for the anchor tags...
	$('#' + tabTagId + ' span a').each(function (index, el){
		var tabIndex = index+1;
		
		// Add in a the image and hover over effect...
		numberImage = $("<img id='tab_img_" + tabIndex + "' src='/images/icons/tab_" + tabIndex + ".png'>")	;
		overImage = $("<img id='tab_img_over_" + tabIndex + "' src='/images/icons/tab_" + tabIndex + "_active.png'>").css('display', 'none');

		$(el).parent().prepend(numberImage).prepend(overImage);

		$(el).parent().hover(function(){
			$("#tab_img_" + tabIndex).css('display', 'none');
			$("#tab_img_over_" + tabIndex).css('display', 'inline');
		}, function(){
			if(selectedSpan != $(el).attr('href')){
				$("#tab_img_" + tabIndex).css('display', 'inline');
				$("#tab_img_over_" + tabIndex).css('display', 'none');
			}
		});

		$(el).click(function (el){
			// Change the internal tracker for the selected tab
			//selectedSpan = $(this).attr('href');
			refreshTabs($(this).attr('href'));
			return false;
		});
		
		// Hide the tabs by default
		$($(el).attr('href')).hide();
		$('#ticker_' + tabIndex).css('display', 'none');
	});


	refreshTabs(selectedSpan);
}
