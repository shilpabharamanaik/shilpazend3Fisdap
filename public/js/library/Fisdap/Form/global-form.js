
$(document).ready(function(){
    $('input[type=submit].link-back').click(function(e){
        autoLinkCancelButtonsToUrls(this);
    });
    
	// auto attrib-based features: linking back, warning user if changes made
    $('input[type=submit]').click(function(event){
        autoLinkCancelButtonsToUrls(this, event);
    });
	
	// auto attrib-based features: linking back, warning user if changes made
    $('button.cancel-button').click(function(event){
        autoLinkCancelButtonsToUrls(this, event);
    });

	// save form values
	changeTracker.saveStartState();
})

// cancel-action
function autoLinkCancelButtonsToUrls(element, event)
{
	// feature: config-loosing-changes:		is feature enabled? (get message to display)
    var confirmLoosingChanges = $(element).attr('confirm-loosing-changes');
    
    if (typeof(confirmLoosingChanges)!='undefined') {
        abandonChanges = changeTracker.autoConfimLoosingChanges(confirmLoosingChanges, event);
    } else {
		abandonChanges = true;
	}
	
	if(abandonChanges) {
		// will trigger redirect back if attribute auto-redirect exists
		var autoRedirectLink = $(element).attr('auto-redirect');
		
		if (typeof(autoRedirectLink)!='undefined') {
			event.preventDefault();
			autoRedirectBack(autoRedirectLink);
		}
	} else {
		event.preventDefault();
	}
}

function autoRedirectBack(backLink)
{
    if (typeof(backLink)=='undefined' || backLink=='') {
        backLink = $('a.page-title-link').first().attr('href');
    }
    
    if (typeof(backLink)!='undefined'){
		//if (history.length==1) {	// was it open in new tab?
		//window.close();
		//} else {      			// was it open in same page?
		window.location = backLink;
		//}
	}
}

var changeTracker = new function() {
	/**
	 *	For now, save first form we find
	 */
	this.saveStartState = function()
	{
		var startState = $('form').serialize();
		this.startState = startState;
	}
	
	/**
	 *	If no changes on form returns true
	 *	if changes made, asks for user confirmation, returns true if user agrees
	 *	@return boolean continueEvenIfChangesPresent?
	 */
	this.autoConfimLoosingChanges = function(message, event)
	{
		var currentState = $('form').serialize();
		
		var changesMade = (currentState != this.startState);
		
		if (!changesMade) {
			okToContinue = true;
		} else {
			// default message
			if(typeof(message)=='undefined' || message=='') {
				message = 'You have made changes. Are you sure you want to abandon them?';
			}
			okToContinue = confirm(message);
		}
		
		return okToContinue;
	}

}
