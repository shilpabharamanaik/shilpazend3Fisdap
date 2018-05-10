$(function() {
	
	var currentlyOpen = 0;
	settingsClock = setInterval(function(){}, 1000);
	
	$("#autosave-text").html("<span class='right-txt-align'>Autosave:</div>").show();
		
	initPage();
	
	function initPage(){
		initStyles();
		
		// clicking the save button shouldn't submit the form, should initiate autosave
		$("#save-button").click(function(e){
			e.preventDefault();
			autosave();
		});
		
		// opening the emt/paramedic/aemt tabs
		$("#lab-skills-tabs :input").each(function(){
			if($(this).attr("checked")){openLabSkillsTab($(this).attr("id"));}
		});
		
		//closing teh emt/paramedic/aemt tabs
		$("#lab-skills-tabs :input").change(function(){
			removeSelected();
			openLabSkillsTab($(this).attr("id"));
			
			$(".category-accordion").find("h3").find("button").each(function(){
				var currentOpenCert = getCurrentCert() + "-content";
				if($(this).parent().parent().parent().attr("id") == currentOpenCert){
					if(!$(this).parent().hasClass("bottom-rounded-corners")){
						var id = $(this).attr("id").split("-");
						currentlyOpen = id[0];
					}
				}
			});
					
		});
		
		setUpElements();
		setUpLabPracticeItemButtons($(".add-lab-practice-item"));
		
		$("#update-skillsheets").click(function(e){
			e.preventDefault();
			blockUi(true);
			$.post("/skills-tracker/settings/update-skillsheets", {}, function(response){
				location.reload();
			}, "json");
		});

        $("#update-ppcp-skillsheets").click(function(e){
            e.preventDefault();
            blockUi(true);
            $.post("/skills-tracker/settings/update-ppcp", {}, function(response){
                location.reload();
            }, "json");
        });
		
		// Add category function - currently STAFF ONLY
		$(".add-category").each(function(){
			$(this).click(function(e){
				e.preventDefault();
				var addCatBtn = $(this);
				var addCatThrobber = addCatBtn.parent().find("img");
				var certLevel = $(this).attr("id");
				var accordion = $(this).parent().parent().find(".category-accordion");
				
				addCatBtn.fadeOut();
				addCatThrobber.fadeIn();
				
				$.post(
					'/skills-tracker/settings/add-category',
					{ certLevel:  certLevel },
					function(response){
						closeAll();
						$(response).hide().appendTo(accordion).fadeIn();
						setUpElements();
						
						var btn = $("#" + $("#lab-skills-tabs :checked").attr("id") + "-content").find("h3:last").find("button");
						var id = btn.attr("id").split("-");
						
						setUpLabPracticeItemButtons($("#" + $("#lab-skills-tabs :checked").attr("id") + "-content").find(".category-content:last").find(".add-lab-practice-item"));
						
						// get the selected cert level
						// find the last "category accordion h3" and open it's content
						var btn = $("#" + $("#lab-skills-tabs :checked").attr("id") + "-content").find("h3:last").find("button");
						var id = btn.attr("id").split("-");
						btn.parent().removeClass("bottom-rounded-corners");
						//$("#" + id[0] + "-content").slideDown();
						currentlyOpen = id[0];
						var categoryName = btn.find(".category-name");
						var newWidth =	categoryName.find(".category-name-static").width();
						categoryName.find("input").css("width", newWidth+5);
						categoryName.find("input").fadeIn();
						categoryName.find("input").focus();
						
						addCatBtn.fadeIn();
						addCatThrobber.hide();
					}
				);
			});
		});
		
		/*
		$(".category-name").hover(function(){
			var id = $(this).parent().attr("id").split("-");
				
			if(currentlyOpen == id[0]){
				$(this).find(".tooltip").fadeIn();
			}
		}, function(){
			var id = $(this).parent().attr("id").split("-");
				
			if(currentlyOpen == id[0]){
				$(this).find(".tooltip").fadeOut();
			}
		});
		*/
		
		$("#delete-skillsheet-a").click(function(e){
			e.preventDefault();
			var deleteBtn = $(this);
			var definitionId = $("#hiddenDefIdForSkillsheet").val();
			deleteBtn.parent().append("<img class='delete-skillsheet-throbber' src='/images/throbber_small.gif'>");
			$.post(
			'/skills-tracker/settings/add-skillsheet',
			{ defId:  definitionId},
			function(response){
				$("#" + definitionId + "-skillsheet").find(".hidden-skillsheet-id").text("");
				$("#" + definitionId + "-skillsheet").parent().addClass("no-eval");
				$("#" + definitionId + "-skillsheet").parent().find("img").attr("src", "/images/icons/eval_icon.png");
				$("#skillsheet-modal-wrapper").dialog('close');
				deleteBtn.parent().find(".delete-skillsheet-throbber").remove();
			}
			);
		});
		
		$("#save-skillsheet").click(function(e){
			e.preventDefault();
			var saveBtn = $(this);
			var definitionId = $("#hiddenDefIdForSkillsheet").val();
			var skillsheet = $("#" + getCurrentCert() + "_skillsheet_select").val();
	
			if(saveBtn.find(".ui-button-text").text() != "Ok"){
				saveBtn.css("opacity", "0");
				
				$.post(
				'/skills-tracker/settings/add-skillsheet',
				{ defId:  definitionId,
				  skillsheetId: skillsheet},
				function(response){
					saveBtn.css("opacity", "1");
					$("#" + definitionId + "-skillsheet").find(".hidden-skillsheet-id").text(skillsheet);
					$("#" + definitionId + "-skillsheet").parent().removeClass("no-eval");
					$("#" + definitionId + "-skillsheet").parent().find("img").attr("src", "/images/icons/eval_icon_check.png");
					$("#skillsheet-modal-wrapper").dialog('close');
				}
				);
			}
			else {
				$("#skillsheet-modal-wrapper").dialog('close');
			}
		});
	
		$("#close-skillsheet-modal").click(function(e){
			e.preventDefault();
			$("#skillsheet-modal-wrapper").dialog('close');
		});
			
		$(".ui-dialog-titlebar-close").click(function(){
			if($(".def-name-throbber").length > 0){
				$(".hidden-name-input :input").each(function(){
					defNameLoseFocus($(this), false, getDefIdByInput($(this)));
				})
			}
		});
		
	}
	
	function setAutosaveTimer(){
		var pcTime = 0;
		
		$("#autosave-text").html("Last saved <span id='autosave-timer'>0 seconds</span> ago");
		
		clearInterval(settingsClock);
		
		settingsClock = setInterval(function(){
		pcTime++;

		if(pcTime < 60){
			timestr = pcTime + ' seconds';
		}else if(pcTime >= 60 && pcTime < 120){
			timestr = '1 minute';
		}else if(pcTime >= 120 && pcTime < 3600){
			timestr = Math.floor(pcTime/60) + ' minutes';
		}else{
			timestr = '&gt;1 hour';
		}	
		
		$('#autosave-timer').text(timestr);
		
		}, 1000);
		
		$("#autosave-text").fadeIn();

	}
	
	// set up styles/buttons/modals
	function initStyles(){
		
		$(".slider-checkbox").each(function(){$(this).sliderCheckbox({onText: 'Show', offText: 'Hide'});});
		$("#lab-skills-tabs").buttonset().find("label").css("width", "160px");
		$("#control-buttons").css("top", $("#settings-wrapper").offset().top+35).css("left", $("#settings-wrapper").offset().left + $("#settings-wrapper").width()-225 );
		$("#close-skillsheet-modal, #close-def-name-modal").css("color", "#666");
		$("#close-skillsheet-modal, #save-skillsheet, #close-def-name-modal, #save-def-name").button();
		
		// the dialog box for confirming a defintion name change
		$("#def-name-modal-confirm").dialog({modal:true,autoOpen:false,resizable:false,width:520,title:"Edit"});
		$("#def-name-modal-confirm").dialog({
			open: function() {
				$("#save-def-name").blur();	
				$("#close-def-name-modal").blur();	
			}
		});
		
		// the dialog box for adding/changing skillsheets
		$("#skillsheet-modal-wrapper").dialog({modal:true,autoOpen:false,resizable:false,width:"auto",title:"Add Skillsheet"});
		$(".skillsheets-selectbox-wrapper").each(function(){$(this).find("select").css("font-size", "9pt");});

		// enter key
		$(document).keypress(function(e) {
			if(e.which == 13) {
				e.preventDefault();
				$("input").each(function(){
					if($(this).hasClass("fancy-input-focus")){
						$(this).blur();
					}
				});
			}
		});
	}
	
	function setUpElements(){
		
		//refresh it all
		$("#lab-practice-tab").find("input").each(function(){
			if($(this).parent().attr("id") != "lab-skills-tabs"){
				$(this).unbind();
			}
		});

        // set up the elements that enable or disable other elements
        handleDisabledFormElements($("#send_late_shift_emails"), $("#late-data-options"), false);
        handleDisabledFormElements($("#disable_educator_signoff"), $("#sign-off-stuff"), true);
        handleDisabledFormElements($("#practice_skills_clinical"), $("#set-quick-add-clinical"), true);
		
		$(".chzn-select").chosen().change(function(){
			
			var def_id = $(this).attr("id").split("definition");
			def_id = def_id[1].split("_practice_skills");
			def_id = def_id[0];
			
			
			if ($.inArray("0airway_management", $(this).val()) != -1) {
				$("#airway_management_icon_" + def_id).fadeIn("fast");
			}
			else {
				$("#airway_management_icon_" + def_id).fadeOut("fast");				
			}
			
		});
		
		
		$(".chzn-select").trigger("change");

		$('input[type="text"]').focus(function() {
			$(this).addClass("fancy-input-focus");
		});
		
		$("input[type='text']").click(function(){
			$(this).select();
		});
		
		$('input[type="text"]').each(function(){
			$(this).blur(function() {
				if(!$(this).hasClass("def-name-input")){
					$(this).removeClass("fancy-input-focus");
				}
			});
		});
		
		$("#settings-wrapper :input").each(function(){
			if(!$(this).hasClass("def-name-input") && $(this).parent().attr("id") != "lab-skills-tabs"){
				$(this).change(function(){
					if(!$(this).hasClass("def-name-input") && !$(this).hasClass("additional-handling")){
						autosave();
					}
				});
			}
		});
		
		$(".category-accordion").find("h3").find("button").each(function(){
			$(this).unbind();
			$(this).click(function(e){
				e.preventDefault();
				var id = $(this).attr("id").split("-");
				
				if(currentlyOpen != id[0])
				{
					closeAll();
					$(this).parent().removeClass("bottom-rounded-corners");
					$("#" + id[0] + "-content").slideDown();
					currentlyOpen = id[0];
				}
				else {
					if ($.browser.mozilla){
						var id = $(this).attr("id").split("-");
						var newWidth =	$(this).find(".category-name-static").width();
						
						var txtBox = $(this).find("input");
						txtBox.remove();
						txtBox.css("width", newWidth+5).addClass("ff-input-fix").appendTo($(this).parent()).fadeIn("fast").focus();
						$(this).parent().css("position", "relative");
						$(".ff-input-fix").blur(function(){closeCategoryNameInputFF($(this).parent().find("button"))})
					}
				}
				
			});
		});
		
		$(".defintion-name").each(function(){
			$(this).unbind();
			$(this).click(function(e){
				e.preventDefault();
				$(this).css("opacity", "0");
				var textBox = $(this).parent().find(".hidden-name-input :input");
				var newTop = ($(this).height() / 2) - (textBox.height() / 2) - 1; 
				textBox.css("top", newTop+"px");
				textBox.parent().fadeIn("fast");
				textBox.focus();
			});
		});
		
		// for changing definition (LPI) names
		$(".hidden-name-input :input").blur(function(){
			var nameInput = $(this);
			var defId = getDefIdByInput(nameInput);
			var tableCell = nameInput.parent().parent();

			if(nameInput.val() != tableCell.find("a").text()){
				var newTop = (tableCell.find("a").height() / 2) - (nameInput.height() / 2) + 4; 
				tableCell.append("<img src='/images/throbber_small.gif' class='def-name-throbber'>");
				$(".def-name-throbber").css("top", newTop+"px");
				$.post(
					'/skills-tracker/settings/number-of-practice-items',
					{ defId:  defId },
					function(response){
						if(response > 0){
							var pluralize = "";
							var pluralizeHasHave = "has";
							
							if(response > 1){pluralize = "s";pluralizeHasHave = "have";}
							
							$("#defNameChange-number-of-students-text").text(response);
							$("#defNameChange-plural-student").text(pluralize);
							$("#defNameChange-student-hasHave").text(pluralizeHasHave);
							$("#defNameChange-item-name").text(nameInput.parent().parent().find("a").text());
							$("#defNameChange-new-item-name").text(nameInput.val());

							$("#def-name-modal-confirm").dialog("open");
							
							$("#close-def-name-modal").click(function(e){
								e.preventDefault();
								defNameLoseFocus(nameInput, false, defId);
								$("#def-name-modal-confirm").dialog("close");
							});
							
							$("#save-def-name").click(function(e){
								e.preventDefault();
								defNameLoseFocus(nameInput, true, defId);
								$("#def-name-modal-confirm").dialog("close");
							});
						}
						else {
							defNameLoseFocus(nameInput, true, defId);
						}
					}
				);
			}
			else {
				defNameLoseFocus(nameInput, true, defId);
			}
		});

		
		$(".category-name").click(function(){
			var id = $(this).parent().attr("id").split("-");
			if(currentlyOpen == id[0]){
				var newWidth =	$(this).find(".category-name-static").width();
				$(this).find("input").css("width", newWidth+5).fadeIn("fast").focus();
			}
		});
		
		// open/close triggers for skillsheet dialog          
		$(".attach-skillsheet-trigger").click(function(e){
			e.preventDefault();

			var rawDefId = $(this).attr("id").split("-");
			var defId = rawDefId[0];
			var defName = $(this).parent().parent().find(".def-name-col").find(".defintion-name").text();
			var skillsheetId = $(this).find(".hidden-skillsheet-id").text();
			var skillsheetWrapper = $("#" + getCurrentCert() + "-skillsheet-selectbox-wrapper");
			
			$("#change-warning").hide();
			$(".skillsheets-selectbox-wrapper").hide();
			skillsheetWrapper.show();
			$("#hiddenDefIdForSkillsheet").val(defId);
			skillsheetWrapper.find("select").unbind();
			
			if(skillsheetId != ""){
				skillsheetWrapper.find("select").val(skillsheetId);
				$("#delete-skillsheet").show();
				toggleActionButton(false, "Ok", $("#save-skillsheet").parent());
				$("#close-skillsheet-modal").hide();
				
				skillsheetWrapper.find("select").change(function(e){
					if($(this).val() != skillsheetId){
						$("#close-skillsheet-modal").show();
						toggleActionButton(true, "Change", $("#save-skillsheet").parent());
					}
					else {
						$("#close-skillsheet-modal").hide();
						toggleActionButton(false, "Ok", $("#save-skillsheet").parent());
						$("#change-warning").slideUp();
					}	
				});
				$( "#skillsheet-modal-wrapper" ).dialog( "option", "title", "Edit Skillsheet for " + defName);
			}
			else {
				$("#delete-skillsheet").hide();
				$("#close-skillsheet-modal").show();
				toggleActionButton(true, "Add", $("#save-skillsheet").parent());
				$( "#skillsheet-modal-wrapper" ).dialog( "option", "title", "Add Skillsheet to " + defName);
			}
			
			$("#skillsheet-modal-wrapper").dialog('open');
		});
		
		$(".category-name :input").blur(function(){closeCategoryNameInput($(this));});
	}
	
	function autosave(){
		var skills_data = [];
		$(".chzn-select").each(function(){
			var sel_skills = $(this).val();
			var el_name = $(this).attr("name");
			if (sel_skills) {
				$.each(sel_skills, function(i, v){
					var obj = {name: el_name, value: v};
					skills_data.push(obj);
				});
			}
			
		});
		
		var form_data = $("#settings-wrapper :input").serializeArray();
		
		$.post(
			'/skills-tracker/settings/autosave',
			//{ data: "so simple",
			//  skills: "this should be simple!" },
			{skills: skills_data,
			 form_data: form_data},
			function(response){
				setAutosaveTimer();
			}
		);
	}
	
	function setUpLabPracticeItemButtons(btns){
		btns.click(function(e){
			e.preventDefault();
			var practiceItemBtn = $(this);
			var category =  practiceItemBtn.parent().parent();
			var catId = category.attr("id").split("-");
			var table = category.find(".category-table").find("tbody");
			practiceItemBtn.fadeOut().parent().find(".add-lpi-throbber").fadeIn();

			$.post(
				'/skills-tracker/settings/add-practice-definition',
				{ catId:  catId[0] },
				function(response){
					if(category.find(".no-practice-items").length != 0){category.find(".no-practice-items").fadeOut();}
					
					if($.browser.mozilla){table.append(response);}
					else {$(response).hide().appendTo(table).fadeIn();}
					
					table.find("dt").hide();
					setUpElements();
					
					$('.category-table-scrollable-content').animate({scrollTop: table.height()},'slow');
					
					table.find('input[type="checkbox"]:last').sliderCheckbox({onText: 'Show', offText: 'Hide'});
					table.find(".defintion-name:last").parent().find(".hidden-name-input").fadeIn();
					table.find(".defintion-name:last").parent().find(".hidden-name-input :input").css("top", "-2px").focus();
					
					practiceItemBtn.fadeIn();
					practiceItemBtn.parent().find(".add-lpi-throbber").hide();
				}
			);
			
		});
	}
	
	function defNameLoseFocus(defNameInput, successful, defId){
		if(defNameInput.val() == "" || !(defNameInput.val().replace(/\s/g, '').length)){
			defNameInput.val("New Practice Item");
		}
		
		if(!successful){
			defNameInput.parent().parent().find(".def-name-throbber").remove();
			defNameInput.removeClass("fancy-input-focus");
			defNameInput.parent().fadeOut();
			defNameInput.parent().parent().find("a").css("opacity", "1");
		}
			
		if(successful){
			if(defNameInput.val() != defNameInput.parent().parent().find("a").text()){
				$.post(
				'/skills-tracker/settings/change-definition-name',
				{ defId:  defId,
				  name: defNameInput.val()},
				function(response){
					successfulDefNameChange(defNameInput);
				}
				);
			}
			else {
				successfulDefNameChange(defNameInput);
			}
		}
		else {
			defNameInput.val(defNameInput.parent().parent().find("a").text()); 
		}
	}
		
	function successfulDefNameChange(defNameInput){
		defNameInput.parent().parent().find("a").text(defNameInput.val());
		defNameInput.parent().parent().find(".def-name-throbber").remove();
		defNameInput.removeClass("fancy-input-focus");
		defNameInput.parent().fadeOut();
		defNameInput.parent().parent().find("a").css("opacity", "1");
	}
	
	function getDefIdByInput(nameInput){
		var rawDefId = nameInput.attr("id").split("_definition");
		var rawDefId = rawDefId[1].split("_");
		var defId = rawDefId[0];
		return defId;
	}
	
   	function getCurrentCert(){
		var certLevel = 0;
		$("#lab-skills-tabs :input").each(function(){
			if($(this).attr("checked")){certLevel = $(this).attr("id");}
		});
		return certLevel;
	}

	function closeCategoryNameInput(inputSelector){
		inputSelector.removeClass("fancy-input-focus");
		if(inputSelector.val() == ""){
			inputSelector.val("New Category");
		}
		inputSelector.parent().find(".category-name-static").text(inputSelector.val());
		inputSelector.fadeOut();
	}
	
	function closeCategoryNameInputFF(btn){
		var inputSelector = btn.parent().find("input");
		inputSelector.removeClass("fancy-input-focus");
		
		if(inputSelector.val() == ""){
			inputSelector.val("New Category");
		}
		
		btn.find(".category-name-static").text(inputSelector.val());
		inputSelector.removeClass("ff-input-fix").fadeOut().remove();
		btn.find(".category-name").append(inputSelector);
	}
	 
	function openLabSkillsTab(divToOpen){$("#" + divToOpen + "-content").fadeIn();}
	
	function removeSelected(){
		$("#lab-skills-tabs :input").each(function(){
			var divToClose = $(this).attr("id");
			$("#" + divToClose + "-content").hide();
		});
	}
	
	function closeAll(){
		currentlyOpen = 0;
		$(".category-accordion").find("h3").find("button").each(function(){
			var currentOpenCert = getCurrentCert() + "-content";
			if($(this).parent().parent().parent().attr("id") == currentOpenCert){
				$(this).parent().addClass("bottom-rounded-corners");
				var id = $(this).attr("id").split("-");
				$("#" + id[0] + "-content").slideUp();
				if($.browser.mozilla){
					closeCategoryNameInputFF($(this));
				}
				else {
					closeCategoryNameInput($(this).find((".category-name :input")));
				}
				
			}
		});
	}
	
	function toggleActionButton(actionOn, text, btn){
		var toRemove = "green-buttons";
		var toAdd = "gray-button";

		if(actionOn){
			toRemove = "gray-button";
			toAdd = "green-buttons";
		}
		
		btn.removeClass(toRemove);
		btn.addClass(toAdd);
		btn.find(".ui-button-text").text(text);
		
		if(actionOn){btn.find(".ui-button-text").css("color", "#fff");}
		else {btn.find(".ui-button-text").css("color", "#666");}
	}

	function loadCustomNarrativeModal()
	{
	    $.post("/skills-tracker/settings/generate-custom-narrative-form", null,
	        function(resp) {
			$("#customNarrativeDialog").html($(resp).html());
			$("#customNarrativeDialog").dialog("open");
			initCustomNarrativeModal();
			$("#load-modal-throbber").remove();
		});
	}

	function loadRequireEvalsModal()
    {
		$.post("/skills-tracker/settings/generate-require-evals-form", null,
				function(resp) {
					$("#requireEvalsDialog").html($(resp).html());
					$("#requireEvalsDialog").dialog("open");
					initRequireEvalsModal();
					$("#load-modal-throbber").remove();
				});
	}

	$("#edit_link").click(function(event) {
		$(this).append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		event.preventDefault();
		loadCustomNarrativeModal();
	});

	$("#edit_evals_link").click(function(event) {
		$(this).append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		event.preventDefault();
		loadRequireEvalsModal();
	});

    function handleDisabledFormElements(trigger, parentOfElements, disableChildrenWhenChecked){
        // add a class to the trigger so it doesn't autosave right away on every change
        trigger.addClass("additional-handling");

        var firstState = false;

        if(disableChildrenWhenChecked){
            firstState = trigger.attr("checked");
        }else{
            firstState = !trigger.attr("checked");
        }

        // disable the children on first load, if appropriate
        if(firstState){
            toggleState(false, parentOfElements);
        }

        // every time the trigger is clicked, toggle the children appropriately then save
        trigger.change(function(){
            toggleState($(this).attr("checked"), parentOfElements, disableChildrenWhenChecked);

            // now that the toggled states have changed, we can autosave
            autosave();
        });
    }

    function toggleState(enabled, parent, disableChildrenWhenChecked){

        var disabled = !enabled;
        var color = "#bbb";

        if(disableChildrenWhenChecked){disabled = enabled;}
        if(!disabled){color = "#000";}

        //jQuery 1.10 broke the slick logic we were using here, so we have to use the removeAttr() function instead.
        // go ahead and uncheck the boxes, too, since once they are disabled, they're not going to submit and so
        // they'll be saved as 0s anyway
        if(disabled){
            parent.find("input").prop({disabled: disabled, checked: false});
        }else if(!disabled){
            parent.find("input").prop('disabled', false);
        }
        parent.css("color", color);

    }


});
