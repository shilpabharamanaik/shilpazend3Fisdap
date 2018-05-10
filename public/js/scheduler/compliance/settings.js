$(function(){
    $("#save-button").button();

    handleDisabledFormElements($("#send_late_shift_emails"), $("#late-data-options"), false);    
    handleDisabledFormElements($("#disable_educator_signoff"), $("#sign-off-stuff"), true);

    function handleDisabledFormElements(trigger, parentOfElements, disableChildrenWhenChecked){
		
        var firstState = false;
		
        if (disableChildrenWhenChecked) {
            firstState = trigger.attr("checked");
        } else {
	    firstState = !trigger.attr("checked");
	}
		
	if(firstState){
	    toggleState(false, parentOfElements);
	}
	
	trigger.change(function(){
	    toggleState($(this).attr("checked"), parentOfElements, disableChildrenWhenChecked);
	});
    }
	
    function toggleState(enable, parent, disableChildrenWhenChecked){
		var disabledValue = !enable;
		var color = "#bbb";

		if(disableChildrenWhenChecked){disabledValue = enable;}
		if(!disabledValue){color = "#000";}
		
		parent.find("input").attr('disabled', disabledValue);
		parent.css("color", color);
		
    }
        
});
