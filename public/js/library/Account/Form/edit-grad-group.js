$(function(){
	$("#editGradGroupCloseButton").button();
	$("#updateGradGroup").button();
	
	var updateGroups = false;
	var updateGrad = false;
	
    // the dialog box for updating the graduation date/groups
    $("#gradGroupDialog").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:525,
        title:"Edit Graduation/Groups",
		open:function(){
			setModalDefaults();
		}
        });
	
	function setModalDefaults(){
		$("#editGroupsButton").blur();
		$("#updateGradGroup").show();
		$("#editGradGroupButtonThrobber").find("img").hide();
		toggleEditDiv($("#editGroupsButton"), $("#editGroupsWrapper"), "close", false);
		toggleEditDiv($("#editGradButton"), $("#editGradWrapper"), "close", false);
		updateGroups = false;
		updateGrad = false;
		$("#edit_grad-month").val(0);
		$("#edit_grad-year").val(0);
		$("#edit_groups-year").val('all');
		$("#edit_groups-id").val(0);
		$("#groupsReset").show();
		$("#gradReset").show();
	}
	
	$("#edit_groups-id").change(function(){resetMessageFunction($(this), $("#groupsReset"), false);});
	$("#edit_grad-month").change(function(){resetMessageFunction($(this), $("#gradReset"), true);});
	$("#edit_grad-year").change(function(){resetMessageFunction($(this), $("#gradReset"), true);});
	
	function resetMessageFunction(select, resetMessage, gradDate){
		if(gradDate){
			if(($("#edit_grad-month").val() == 0) ||  ($("#edit_grad-year").val() == 0)){
				resetMessage.slideDown();
			}
			else {
				resetMessage.slideUp();
			}
		}
		else {
			if(select.val() == 0){
				resetMessage.slideDown();
			}
			else {
				resetMessage.slideUp();
			}
		}
	}
	
	// weird zend form fix
	$("#gradGroupDialog").find("#group-year").remove();
	$("#gradGroupDialog").find("#group-id").remove();
	
	$("#editGroupsButton").click(function(e){
		e.preventDefault();
		// we were open, now close it
		if(updateGroups){
			toggleEditDiv($(this), $("#editGroupsWrapper"), "close", true);
			// update our flag
			updateGroups = false;
		}
		// now open it
		else {
			toggleEditDiv($(this), $("#editGroupsWrapper"), "open", true);
			// update our flag
			updateGroups = true;
		}
	});
	
	$("#editGradButton").click(function(e){
		e.preventDefault();
		// we were open, now close it
		if(updateGrad){
			toggleEditDiv($(this), $("#editGradWrapper"), "close", true);
			// update our flag
			updateGrad = false;
		}
		// now open it
		else {
			toggleEditDiv($(this), $("#editGradWrapper"), "open", true);
			// update our flag
			updateGrad = true;
		}
	});
	
	function toggleEditDiv(controllerButton, divToTrigger, state, slide){
		if(state == 'open'){
			if(slide){
				divToTrigger.slideDown();
			}
			else {
				divToTrigger.show();
			}
			controllerButton.find("#closed").remove();
			controllerButton.find("#opened").remove();
			controllerButton.append("<img id='opened' src='/images/arrow_down.png'>");
		}
		else {
			if(slide){
				divToTrigger.slideUp();
			}
			else {
				divToTrigger.hide();
			}
			controllerButton.find("#opened").remove();
			controllerButton.find("#closed").remove();
			controllerButton.append("<img id='closed' src='/images/arrow_left.png'>");
		}
	}
	
	function getNumberOfCodesSelected() {
		var codes = [];
		$("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");
			if(checkbox.is(":checked")){
				codes.push(checkbox.val());
			}
		});
		
		return codes.length;
	}
	
	function anyInstructorsSelected() {
		var instructors = false;
		$("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");

			if(checkbox.is(":checked")){
				var cert;
				var count = 0;
				$(this).find("td").each(function(){
					count++;
					if(count == 5){
						if($(this).text() == 'Instructor'){
							instructors = true;
						}
					}
				});
			}
		});
		
		return instructors;
	}

	
	
	// open/close triggers for dialog          
    $("a#gradGroups").click(function(){
		if(getNumberOfCodesSelected() == 0){
			$("#noCodesSelectedError").slideDown();
		}
		else if(anyInstructorsSelected()){
			$("#instructorsSelectedError").slideDown();
		}
		else {
			$("#instructorsSelectedError").fadeOut();
			$("#noCodesSelectedError").fadeOut();
			$(this).hide();
			$(this).parent().append("<img id='gradGroupButtonThrobber' src='/images/throbber_small.gif'>");
			$("#gradGroupDialog").dialog('open');
			gradGroupModalOpenButtonStyles();
		}
	});
	
	function gradGroupModalOpenButtonStyles(){
		$("#gradGroupButtonThrobber").remove();
		//$("#sendButtonThrobber").hide();
		//$("#sendButton").show();
		$("a#gradGroups").show();
	}
	
    $("#editGradGroupCloseButton").click(function(e){
		e.preventDefault();
		$("#gradGroupDialog").dialog('close');
	});
});