/**
 * This just tracks how many skills we've added.  Each unique number is used
 * to pull back the appropriate form elements, to keep them all separate.
 */
var quickSkillsCount = 0;

// Tracks whether an ajax request is currently pending...
// Flips to true before we send it, false when the ajax returns.
var pendingChange = false;

// Tracks whether or not to send a second request when the 
// pending one completes.  This prevents us from spamming several
// ajax requests for clicking many fields at once.  Effectively
// makes it asynch without locking the interface.
var resendRequest = false;

//Tracks whether we've already initiated an autosave since the page was loaded
var alreadySaved = false;

//JS Timer to update the last saved info
var patientCareClock = 0;

//Int to keep track of last save
var autosaveTime = 0;

/**
 * This function updates the eval hook link
 * when you change the type of procedure
 */
function updateSkill(e){
	procId = $(e.currentTarget).val();

	if (typeof EvalHooks[procId] == "undefined") {
		hookId = 0;
	} else {
		hookId = EvalHooks[procId].hookid;
	}

	skillNumber = $(e.currentTarget).attr('id').substring(10);
	updateEvalLink(hookId, skillNumber);
}

/**
 * This function is what handles the updating of the eval link
 */
function updateEvalLink(hookId, skillNumber) {
	var updatedURL = evalLinkURLPath + "/hid/" + hookId + "/sid/" + $('#quick-shift-id').first().val();
	$('#eval-link-' + skillNumber).attr("href", updatedURL);
    
	if (hookId > 0) {
        $('#eval-link-' + skillNumber).removeAttr('onclick');
        $('#eval-link-' + skillNumber).unbind('click');
		$('#eval-link-' + skillNumber).show();
		$('#eval-link-' + skillNumber).bind('click',(function(){
			window.open(updatedURL, '_blank', 'width=1020,height=700'); 
			return false;
		}));
	} else {
		$('#eval-link-' + skillNumber).hide();
	}

}

/**
 * This function just runs through and updates the available radio buttons
 * when you change the type of subject.
 */
function updateTypes(e){
	skillNumber = $(e.currentTarget).attr('ordinalValue');
	updateSelection(skillNumber);
}

/**
 * This function is what handles the updating of the radio buttons and their
 * state.
 */
function updateSelection(skillNumber)
{
	if($("#subject-name-" + skillNumber).val() == 'Manikin'){
		$("#subject-type-live-" + skillNumber).hide();
		$("#live-label-" + skillNumber).hide();

		$("#subject-type-dead-" + skillNumber).hide();
		$("#dead-label-" + skillNumber).hide();

		$("#subject-type-sim-" + skillNumber).show();
		$("#subject-type-sim-" + skillNumber)[0].checked = true;
		$("#sim-label-" + skillNumber).show();

		$("#subject-type-other-" + skillNumber).show();
		$("#other-label-" + skillNumber).show();
	}else{
		$("#subject-type-live-" + skillNumber).show();
		$("#subject-type-live-" + skillNumber)[0].checked = true;
		$("#live-label-" + skillNumber).show();

		$("#subject-type-dead-" + skillNumber).show();
		$("#dead-label-" + skillNumber).show();

		$("#subject-type-sim-" + skillNumber).hide();
		$("#sim-label-" + skillNumber).hide();

		$("#subject-type-other-" + skillNumber).hide();
		$("#other-label-" + skillNumber).hide();
	}
}

/**
 * This function is used to create a new skill row to be placed into the table
 * (it doesn't actually add it to the table though- it just returns the HTML
 * element).
 */
function createNewSkill(skillNumber)
{	
	newSkill = $("<tr id='skill-" + skillNumber + "'></tr>");
	newSkill.addClass('quick-row');

	procDiv = $('<td class="first-cell"></td>');
	
	procElement = $('#skillsprocedure').clone(true).first();
	
	procElement.show();
	
	procElement.attr('id', 'procedure-' + skillNumber);
	procElement.attr('name', 'skills[' + skillNumber + '][procedure]');
	procElement.click(updateSkill);
	
	evalLink = $(evalLinkMarkup);
	evalLink.attr('id', 'eval-link-' + skillNumber);

	grabbyImage = $("<img src='/images/icons/wide_grabby.png' style='margin-top: 10px;' title='Click and drag to reorder'/>");

	procDiv.append(grabbyImage);
	procDiv.append(' ');
	procDiv.append(procElement);
	procDiv.append(' ');
	procDiv.append(evalLink);
	
	procDiv.addClass('quick-cell');

	subjDiv = $('<td></td>');

	// First, add on the dropdown for the subject types...
	subjSelector = $("<select ordinalValue='" + skillNumber + "' class='subject-name' id='subject-name-" + skillNumber + "' name='skills[" + skillNumber + "][subject-name]'><option value='Human'>Human</option><option value='Animal'>Animal</option><option value='Manikin'>Manikin</option></select>");
	subjSelector.click(updateTypes);
	subjDiv.append(subjSelector);

	// Now, add on the 4 available radio buttons:
	// live, dead, sim, other.
	subjDiv.append($("<label class='inline' id='live-label-" + skillNumber + "' for='subject-type-live-" + skillNumber + "'><input id='subject-type-live-" + skillNumber + "' name='skills[" + skillNumber + "][subject-type]' type='radio' value='live' />Live</label>"));
	subjDiv.append($("<label class='inline' id='dead-label-" + skillNumber + "' for='subject-type-dead-" + skillNumber + "'><input id='subject-type-dead-" + skillNumber + "' name='skills[" + skillNumber + "][subject-type]' type='radio' value='dead' />Dead</label>"));
	subjDiv.append($("<label class='inline' id='sim-label-" + skillNumber + "' for='subject-type-sim-" + skillNumber + "'><input id='subject-type-sim-" + skillNumber + "' name='skills[" + skillNumber + "][subject-type]' type='radio' value='sim' />Sim</label>"));
	subjDiv.append($("<label class='inline' id='other-label-" + skillNumber + "' for='subject-type-other-" + skillNumber + "'><input id='subject-type-other-" + skillNumber + "' name='skills[" + skillNumber + "][subject-type]' type='radio' value='other' />Other</label>"));

	subjDiv.addClass('quick-cell');

	successDiv = $('<td align="center"></td>');
	successDiv.html("<input type='checkbox' id='successful-" + skillNumber + "' name='skills[" + skillNumber + "][successful]' value='1' />");
	successDiv.addClass('quick-cell');

	signoffDiv = $('<td align="center"></td>');
	signoffDiv.html("<input type='checkbox' id='signoff-" + skillNumber + "' name='skills[" + skillNumber + "][signoff]' value='1' />");
	signoffDiv.addClass('quick-cell');

	toolsDiv = $('<td align="center" class="last-cell"></td>');
	copyLink = $("<a href='#' id='quick-skill-copy-" + skillNumber + "'><img class='small-icon' src='/images/icons/duplicate.png' /></a>");
	copyLink.click(function(e){
		copySkill(skillNumber);
		return false;
	});

	deleteLink = $("<a href='#' id='quick-skill-delete-" + skillNumber + "'><img class='small-icon' src='/images/icons/delete.png' /></a>");
	deleteLink.click(function(e){
		deleteSkill(skillNumber);
		return false;
	});
	
	
	toolsDiv.append(copyLink);
	toolsDiv.append(deleteLink);
	toolsDiv.addClass('quick-cell');

	toolsDiv.append($("<input type='hidden' id='skill-id-" + skillNumber + "' name='skills[" + skillNumber + "][skill-id]' value='' />"));

	newSkill.append(procDiv);
	newSkill.append(subjDiv);
	newSkill.append(successDiv);
	newSkill.append(toolsDiv);
	
	//newSkill.append(signoffDiv);

	newSkill.hover(function(){$(this).addClass('quick-hover')}, function(){$(this).removeClass('quick-hover')});

	return newSkill;
}

/**
 * This function is used to copy an existing skill from the table.
 */
function copySkill(oldSkillNumber)
{
	quickSkillsCount++;

	// Yeah, pretty unneccessary, but renaming it here makes the logic easier to
	// follow below.
	newSkillNumber = quickSkillsCount;

	skillCopy = createNewSkill(newSkillNumber);

	skillCopy.find('#procedure-' + newSkillNumber).val($('#procedure-' + oldSkillNumber).val());

	skillCopy.find('#successful-' + newSkillNumber).attr('checked', $('#successful-' + oldSkillNumber).attr('checked'));

	// Make sure to uncheck the signoff checkbox on a copy...
	skillCopy.find('#signoff-' + oldSkillNumber).attr('checked', false);
	
	// Make sure we nuke the skill ID as well, since this will technically be a new skill...
	skillCopy.find('#skill-id-' + oldSkillNumber).val('');

	$('#quick-skills-body').append(skillCopy);

	skillCopy.find('#subject-name-' + newSkillNumber).val($('#subject-name-' + oldSkillNumber).val());

	// This needs to get called after we add the element to the DOM, but
	// before we copy the value from the source node, otherwise it'll get
	// overwritten.
	updateSelection(newSkillNumber);
	$('#procedure-' + quickSkillsCount).change();
	$('#procedure-' + quickSkillsCount).click();

	skillCopy.find('#subject-type-live-' + newSkillNumber).attr('checked', $('#subject-type-live-' + oldSkillNumber).attr('checked'));
	skillCopy.find('#subject-type-dead-' + newSkillNumber).attr('checked', $('#subject-type-dead-' + oldSkillNumber).attr('checked'));
	skillCopy.find('#subject-type-sim-' + newSkillNumber).attr('checked', $('#subject-type-sim-' + oldSkillNumber).attr('checked'));
	skillCopy.find('#subject-type-other-' + newSkillNumber).attr('checked', $('#subject-type-other-' + oldSkillNumber).attr('checked'));
	
	//Start an auto-save once we're done copying
	saveQuickSkills();
}

/**
 * This function is used to delete records from the table.  It will also
 * append in the ID of the skill being deleted, if one exists.  This makes it
 * so when we get to the POST page, we can go through and kill out any records
 * that the user deleted.
 */
function deleteSkill(skillNumber){
	$('#skill-' + skillNumber).remove();

	//Start an auto-save once we're done copying
	saveQuickSkills();
	
	//if($('#quick-skills-body').children().length == 0){
	//	$('#quick-skills-header').hide();
	//	$('#quick-skills-footer').hide();
	//	
	//	// I think we shouldn't hide these if we delete all of the rows...
	//	// will still need a way to save or cancel when we delete everything.
	//	//$('#save-button').hide();
	//	//$('#cancel-button').hide();
	//}
}

/**
 * This function is used to create and add a new skill to the list.
 */
function addNewSkill(){
	if(quickSkillsCount >= 0){
		$('#quick-skills-header').show();
		$('#quick-skills-footer').show();
		$('#save-button').show();
		$('#cancel-button').show();
	}

	quickSkillsCount++;

	$('#quick-skills-body').append(createNewSkill(quickSkillsCount));
	
	$('#procedure-' + quickSkillsCount).change();
	updateSelection(quickSkillsCount);

	// Kick back false so that the form doesn't post...
	return false;
}

/**
 * This is mostly a stub function- but currently takes the contents of the form
 * and does an AJAX post to save the current state.  Will probably be used to
 * implement autosave at some point, but right now it's not being called 
 * anywhere.
 */
function saveQuickSkills(){
	$('#returnMode').first().val('ajax');
	postData = $("#quick-skills-form").serialize();
	$('#returnMode').first().val('text');
	
	if (!pendingChange) {
		pendingChange = true;
		
		$.post($("#quick-skills-form").attr('action'), postData, function(data){
			alreadySaved = true;
			autosaveTime = 0;
			
			// Do whatever on success...
			pendingChange = false;

			if(resendRequest){
				resendRequest = false;
				saveQuickSkills();
			}
		});
	} else {
		resendRequest = true;
	}
	
	return false;
}

/**
 * This is what sets everything else in motion.  Basically hides the buttons,
 * table header/footer, and the procedures dropdown that gets coppied around.
 */
$(function(){
	
	patientCareClock = setInterval(function(){
		autosaveTime++;

		if(autosaveTime < 60){
			timestr = autosaveTime + ' seconds';
		}else if(autosaveTime >= 60 && autosaveTime < 120){
			timestr = '1 minute';
		}else if(autosaveTime >= 120 && autosaveTime < 3600){
			timestr = Math.floor(autosaveTime/60) + ' minutes';
		}else{
			timestr = '&gt;1 hour';
		}
		
		//Only display the timer notification we've done at least one autosave
		if (alreadySaved) {
			if (autosaveTime <= 1) {
				//show the timer readout and highlight for a few seconds
				var timerReadout = $("#autosave-timer").show();
				timerReadout.addClass("updated").removeClass('updated', 3000);
			}				
			
			$('#autosave-timer').html('Last saved ' + timestr + ' ago');
		}
	}, 1000);
	
	function onNewSkillClick(e){
		addNewSkill();
		// Do this to prevent the default action from happening.
		return false;
	}

	$("#add-skills-btn").button().addClass('gray-button small');
	$("#signoff-btn").button().addClass('gray-button small');
	$("#cancel-button").button().addClass('gray-button small');
	$("#save-button").button().addClass('green-button medium');

	$('#save-button').hide();
	$('#cancel-button').hide();
	//$('#quick-skills-header').hide();
	//$('#quick-skills-footer').hide();
	
	$('#skillsprocedure').hide();

	
	$('#save-button').click(saveQuickSkills);
	$('#quick-skills-form input, #quick-skills-form select').live('change', saveQuickSkills);
	
	// Send out a request to find the currently assigned skills.
	// but only if we're actually sending data. sometimes #quick-student-id and #quick-shift-id don't exist
	postData = {
		studentID: $('#quick-student-id').first().val(), 
		shiftID: $('#quick-shift-id').first().val()
	};
	if (typeof(postData.studentID) != 'undefined' && typeof(postData.shiftID) != 'undefined') {
		$.post("/skills-tracker/shifts/get-skills-json", postData, function(data){
			// Enable the add button...
			$('#add-skills-btn').click(onNewSkillClick);
			
			// Loop over the returned skills and populate the listing...
			for(x in data['active']){
				addNewSkill();
				
				skillData = data['active'][x];
				
				// Set the default values, based on the provided skillData, if available.
				if(skillData['procedure'] != undefined){
			
					$('#procedure-' + quickSkillsCount).first().val(skillData['procedure']);
					$('#procedure-' + quickSkillsCount).change();
					
					hookLink = $(skillData['hook-html']);
					hookLink.attr('id', 'eval-link-' + quickSkillsCount);
					hookLink.find('img').first().css('height', 25).css('width', 17);
			
			
					
					$('#eval-link-' + quickSkillsCount).replaceWith(hookLink);
				}
				
				if(skillData['subject-name'] != undefined){
					$('#subject-name-' + quickSkillsCount).first().val(skillData['subject-name']);
					updateSelection(quickSkillsCount);
				}
				
				if(skillData['subject-type'] != undefined){
					$('#subject-type-' + skillData['subject-type'] + '-' + quickSkillsCount).first().attr('checked', true);
				}
	
				if(skillData['successful'] != undefined){
					$('#successful-' + quickSkillsCount).first().attr('checked', skillData['successful']);
				}
				
				if(skillData['id'] != undefined){
					$('#skill-id-' + quickSkillsCount).first().val(skillData['id']);
				}
			}
					
			if(data['inactive'].length > 0){
				dispDiv = $("<div>The following skills have been completed, but are no longer used in this program:</div>");
				
				summaryList = $("<ul></ul>");
				for(x in data['inactive']){
					datum = data['inactive'][x];
					
					listItem = $("<li></li>");
					listItem.text(datum['procedure-name'] + "; Patient: " + datum['subject-type'] + " " + datum['subject-name']);
					
					summaryList.append(listItem);
				}
				
				summaryList.css('width', '100%');
				
				$('#inactive-summary').append(dispDiv).append(summaryList);
			}
			
			// Snag a copy of the state after the load- so if the user hits cancel we can recall their state...
			cancelData = $("#quick-skills-form").serialize();
			$('#cancel-button').click(function (e){
				$.post("/skills-tracker/shifts/get-skills-json", cancelData, function(data){
					window.location.reload(true);
				})
				return false;
			});
			
			//If there are no skills returned from the DB, add a blank one to start with
			if (quickSkillsCount == 0) {
				addNewSkill();
			}
			
			$('#quick-skills-body').sortable();
		}, 'json');
	}
});