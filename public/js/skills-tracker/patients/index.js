//Check if this is the first time the page has been loaded
var initialPageLoad = true;

$(function(){
	handlerObject = initCustomTabs('custom-tabs', document.location.hash);
	
	$(".airway_management_options_wrapper").find("br").first().remove();
	
	$("#airway_success").change(function(){
		airwayElementsToggle();
	});
	airwayElementsToggle();
});

var airwayElementsToggle = function()
{
	if ($("#airway_success").prop("checked")) {
		$(".airway_management_options_wrapper").slideDown(300);
		setTimeout(function(){
			$(".airway_management_options_wrapper").animate({opacity:1});
		}, 400);
	}
	else {
		$(".airway_management_options_wrapper").animate({opacity:0});

		setTimeout(function(){
			$(".airway_management_options_wrapper").slideUp(300);
		}, 400);
	}
}

initCustomTabs = function(tabTagId, defaultSelectedSpan){
	var selectedSpan = defaultSelectedSpan;
	
	if(defaultSelectedSpan == ''){
		selectedSpan = "#patientCare";
	}
	
	// Hide the current save/submit buttons and autosave text...
	// Will be copied and added to the top in calls to refreshTabs.
	$('div.save-button-container').hide();
	$('div#autosave-timer').hide();
	
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
		
		if (selectedSpan != newSelectedSpan) {
			unloadAutosave(selectedSpan);			
		}
		
		// Take the save button from the current span and put clone it into the header...
		$('#autosave-text-container').empty();
		// hide the original divs
		$('#autosave-timer-nar').hide();
		$('#autosave-timer-sig').hide();
		$('#autosave-timer').hide();
		// clone the span and move it up
		cloneAutosave = $(newSelectedSpan).find('div#autosave-timer, div#autosave-timer-nar, div#autosave-timer-sig').first().clone(true);
		cloneAutosave.show();
		$('#autosave-text-container').append(cloneAutosave);
		
		$('#save-button-container').empty();
		cloneSaveButton = $($(newSelectedSpan).find('div.save-button-container').first().html());
		cloneSaveButton.attr('id', 'save-clone');
		cloneSaveButton.show().button().addClass('extra-small').val("Save");
		$('#save-button-container').addClass('green-buttons extra-small');
		$('#save-button-container').append(cloneSaveButton);
		
		if (selectedSpan != newSelectedSpan || initialPageLoad == true) {
			loadAutosave(newSelectedSpan);			
		}
		
		selectedSpan = newSelectedSpan;
		initialPageLoad = false
	};
	
	unloadAutosave = function(s){
		switch(s){
			case '#patientCare':
				removePCAutosave();
				break;
			case '#narrative':
				removeNarrativeAutosave();
				break;
			case '#signoff':
				removeSigAutosave();
				break;
		}
	}

	loadAutosave = function(s){
		switch(s){
			case '#patientCare':
				initPCAutosave();
				break;
			case '#narrative':
				$.post('/skills-tracker/patients/get-narrative/',
				       {patientId: $("#hiddenPatientId").val()},
				       function(data){
						$.each(data, function(key, value) {
							$('#' + key).val(value);
						});
					}
				);
				initNarrativeAutosave();
				break;
			case '#signoff':
				initSigAutosave();
				break;
		}
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
	
	$('.narrative-link').click(function(e) {
		e.preventDefault();
		refreshTabs("#narrative");
		$( 'html, body' ).animate( {scrollTop: 0}, 0);
		return false;
	});
	
	$('.patient-care-link').click(function(e) {
		e.preventDefault();
		refreshTabs("#patientCare");
		$( 'html, body' ).animate( {scrollTop: 0}, 0);
		return false;
	});
	
	$('.signoff-link').click(function(e) {
		e.preventDefault();
		refreshTabs("#signoff");
		$( 'html, body' ).animate( {scrollTop: 0}, 0);
		return false;
	});

	refreshTabs(selectedSpan);
}
