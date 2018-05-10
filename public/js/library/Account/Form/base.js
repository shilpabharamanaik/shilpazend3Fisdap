	
$(function(){
	// initialize buttons
	$('a#addBase').button();
	$('a#removeBase').button();
	$('a#addAll').button();
	$('a#removeAll').button();
	
	$('a#edit-base-btn').button();
	$('a#edit-base-btn-lab').button();
	$('a#edit-depart-btn').button();
	$('a#new-base-btn').button();
	$('a#new-base-btn-lab').button();
	$('a#new-depart-btn').button();
	$('a#delete-base-btn').button();
	$('a#merge-base-btn').button();
	
	// disable the buttons
	$("#edit-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
	$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
	$("#merge-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
	
	// hide the throbbers, we'll use them later
	$('#throbberContainer').hide();
	$('#mergeThrobberContainer').hide();
	
	// initialize the hidden inputs
	updateActiveBases();
	updateInactiveBases();

	// mark the default departments
	$("select#inactiveBase option, select#activeBase option").each(function(){
		if(isDefault($(this).text())) {
			$(this).addClass('clinical');
		}	
	});
	
	// make the disabled buttons not do hover effects
	$(".disabled").hover(function() {
		$(this).css("color", "white");
	});
	
	function newButtonAction(title, modal, type){
		// set the title to new
		modal.dialog("option", "title", title);
		// reset the form
		resetFields(type);
		// then open the dialog
		modal.dialog("open");
		$('#baseErrors').remove();
		$('#departFormErrors').remove();
		$('label').removeClass('prompt-error');
	}
	
	function resetFields(type){
		if(type == "base"){
			$("#base_name").val("");
			$("#base_city").val("");
			$("#base_state").val("");
			$("#base_zip").val("");
			$("#base_address").val("");
			$("#base_id_input").val("noBase");
		}
		else {
			$("#depart_name").val("");
			$("#depart_id_input").val("noBase");
		}
		return true;
	}
	
	// when the NEW button is clicked (for field only):
	$("#new-base-btn").button().click(function(event) {
		event.preventDefault();
		newButtonAction("Add New Base", $("#newBaseDialog"), "base");
	});
	
	// when the NEW button is clicked (for lab only):
	$("#new-base-btn-lab").button().click(function(event) {
		event.preventDefault();
		newButtonAction("Add New Department", $("#newBaseDialog"), "base");
	});
	
	// when the NEW button is clicked (for clinical only):
	$("#new-depart-btn").button().click(function(event) {
		event.preventDefault();
		newButtonAction("Add New Department", $("#newDepartDialog"), "depart");
	});
	
	
	// when the EDIT button is clicked (for field only):
	$("#edit-base-btn").button().click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
		if ($(this).css('opacity') != "1") {
            return false;
        }
		
		$('#baseErrors').remove();
		$('label').removeClass('prompt-error');
		
		// find the selected base
		baseId = $("#inactiveBase").find("option:selected").val();
		if(baseId == null){
			baseId = $("#activeBase").find("option:selected").val();
		}
		
		// set the value of the hidden input
		$("#base_id_input").val(baseId);
		
		// post the data to get the base then fill in the fields on the form
		$.post("/account/sites/get-base-data", {baseId: baseId, baseType: true }, function(response){
			
			$("#base_name").val(response['name']);
			$("#base_city").val(response['city']);
			$("#base_address").val(response['address']);
			$("#base_state").val(response['state']);
			$("#base_zip").val(response['zip']);
			
			var newTitle = "Edit " + response['name'];
			
			$("#newBaseDialog").dialog("option", "title", newTitle);
			$("#newBaseDialog").dialog("open");
			$("#throbberContainer").hide();
			$("#edit-base-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#edit-base-btn").show();


		});
		
		// hide the button and show the throbber until we're ready
		$("#edit-base-btn").hide();
		$('#throbberContainer').show();
		return true;
	});
	
	
	// when the EDIT button is clicked (for lab/clinical only):
	$("#edit-depart-btn").button().click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
                if ($(this).css('opacity') != "1") {
                    return false;
                }
		
		$('#departFormErrors').remove();
		$('label').removeClass('prompt-error');
		
		// find the selected base
		baseId = $("#inactiveBase").find("option:selected").val();
		if(baseId == null){
			baseId = $("#activeBase").find("option:selected").val();
		}
		
		// set the value of the hidden input
		$("#depart_id_input").val(baseId);
		
		// post the data to get the base then fill in the fields on the form
		$.post("/account/sites/get-base-data", {baseId: baseId, baseType: false }, function(response){
			
			$("#depart_name").val(response['name']);
			
			var newTitle = "Edit " + response['name'];
			
			$("#newDepartDialog").dialog("option", "title", newTitle);
			$("#newDepartDialog").dialog("open");
			$("#throbberContainer").hide();
			$("#edit-depart-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#edit-depart-btn").show();


		});
		
		// hide the button and show the throbber until we're ready
		$("#edit-depart-btn").hide();
		$('#throbberContainer').show();
    });
	
	// the the MERGE button is clicked (for site admins only)
	$("a#merge-base-btn").click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
                 if ($(this).css('opacity') != "1") {
                    return false;
                }
		
		// figure out which bases are selected
		var count = $("#inactiveBase :selected").length;
		
		if (count != 0) {
			var selectedOptions = $("#inactiveBase :selected");
		} else {
			var selectedOptions = $("#activeBase :selected");
		}

		var options = {};
		$(selectedOptions).each(function() {
			if (!isDefault($(this).text())) {
				options[$(this).val()] = $(this).text();
			}
		});
		var siteId = $("#site_id").val();
		var mergeBtn = $('#merge-base-btn').hide();
		$('#mergeThrobberContainer').show();	
				
		$.post("/account/sites/generate-merge-base-form",
			{ 'options' : options,
			  'site_id' : siteId },
			function(resp) {
				$("#mergeBasesDialog").html($(resp).html());
				$("#mergeBasesDialog").dialog("open");
				initMergeBasesModal();
				$('#mergeThrobberContainer').hide();
				mergeBtn.show();
			}
		);
	});
	
	function initMergeBasesModal() {
		$("#cancel-merge-btn").button();
		$("#do-merge-btn").button();
		$(".base-input").hide();

		$("tr.base-row").click(function(event) {
			event.preventDefault();
			var baseId = $(this).attr('id');
			$("tr.base-row").removeClass('selected');
			$(".base-input :input").attr('checked', false);
			$(this).addClass('selected');
			$('#target_base-'+baseId).attr('checked', true);
		});

		$('#cancel-merge-btn').click(function(event) {
			event.preventDefault();
			$('#mergeBasesDialog').dialog('close');
		});

		$('#do-merge-btn').click(function(event) {
			event.preventDefault();
			
			var postValues = $('#mergeBasesDialog form').serialize();
			var cancelBtn = $('#cancel-merge-btn').hide();
			var mergeBtn = $('#do-merge-btn').hide();
			var throbber =  $("<img id='mergeModalThrobber' src='/images/throbber_small.gif'>");
			mergeBtn.parent().append(throbber);
			$.post("/account/sites/merge-bases",
				postValues,
				function (response) {
					if (typeof response == 'number') {
						if ($('#inactiveBase :selected').length != 0) {
							var selectedOptions = $('#inactiveBase :selected');
							var selectedOptionsHidden = $('#hiddenInactive :selected');
						} else {
							var selectedOptions = $('#activeBase :selected');
							var selectedOptionsHidden = $('#hiddenActive :selected');
						}
						$(selectedOptions).each(function() {
							if($(this).val() != response){
								$(this).remove();
							}
						});
						$(selectedOptionsHidden).each(function() {
							if($(this).val() != response){
								$(this).remove();
							}
						});		
						$('#mergeBasesDialog').dialog('close');
						$("#edit-base-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
						$("#merge-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
					} else {
						htmlErrors = '<div id=\'mergeBasesErrors\' class=\'form-errors alert\'><ul>';

						$('label').removeClass('prompt-error');

						$.each(response, function(elementId, msgs) {
							$('label[for=' + elementId + ']').addClass('prompt-error');
							$.each(msgs, function(key, msg) {
								htmlErrors += '<li>' + msg + '</li>';
							});
							if(elementId == 'site_type'){
								$('#typeContainer').css('border-color','red');
							}
						});

						htmlErrors += '</ul></div>';

						$('.form-errors').remove();
						$('#mergeBasesDialog form').prepend(htmlErrors);
						mergeBtn.show();
						cancelBtn.show();
						$('#mergeModalThrobber').remove();
					}
				}
			)
		});
	}
	
	// will update the hidden active bases
	function updateActiveBases()
	{
		// empty the box
		$('#hiddenActive').find('option').remove();
		
		// clone whats currently in active
		$("select#hiddenActive").append($("#activeBase option").clone());
		
		// then select it all
		$("select#hiddenActive *").attr("selected", true);
		
	}
	
	// will update the hidden Inactive bases
	function updateInactiveBases(){
		$('#hiddenInactive').find('option').remove();
		$("select#hiddenInactive").append($("#inactiveBase option").clone());
		$("select#hiddenInactive *").attr("selected", true);
	}
	
	// determines if the selected option is a default department
	function isDefault(optionText){
		if(optionText == "Cardiac Care Unit"
			   || optionText == "Cardiac Cath. Lab"
			   || optionText == "Clinic"
			   || optionText == "ER"
			   || optionText == "ICU"
			   || optionText == "IV Team"
			   || optionText == "Labor & Delivery"
			   || optionText == "Neonatal ICU"
			   || optionText == "OR"
			   || optionText == "Post Op"
			   || optionText == "Pre Op"
			   || optionText == "Psychiatric Unit"
			   || optionText == "Respiratory Therapy"
			   || optionText == "Triage"
			   || optionText == "Urgent Care"
			   || optionText == "Anesthesia"
			   || optionText == "Burn Unit"){
			
			return true;
		
		}
		else {
			return false;
		}
	}

	
	// sort the lists alphabetically
	var my_options = $("#activeBase option");

	my_options.sort(function(a,b) {
		if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
		else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
		else return 0
	})
		
	$("#activeBase").empty().append( my_options );
	
	var my_inactive_options = $("#inactiveBase option");

	my_inactive_options.sort(function(a,b) {
		if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
		else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
		else return 0
	})
		
	$("#inactiveBase").empty().append( my_inactive_options );
	
	
	
	// The functions for when a new option is selected from either list
	// Determines when the "edit" button should appear and will only allow one list to have item(s) selected
	$("select#inactiveBase").change(function(){
		updateButtons('#inactiveBase', '#activeBase');
	});
	
	$("select#activeBase").change(function(){
		updateButtons('#activeBase', '#inactiveBase');
	});
	
	function updateButtons(listID, otherListID) {
		$(otherListID).attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		var count = 0
		$(listID+" :selected").each(function(){
			if (!isDefault($(this).text())) {
				count++;
			}
		});

		if (count == 1 && $(listID+" :selected").length == 1) {
			var optionText = $("select"+listID).find("option:selected").text();
			if(!isDefault(optionText)){
				$("#edit-base-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
				$("#edit-depart-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
				$("#merge-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			}
			else{
				$("#edit-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
				$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
				$("#merge-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			}

		} else if(count > 1) {
			$("#edit-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#merge-base-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
		} else {
			$("#edit-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#merge-base-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
	}
	
	// ADDING A BASE, activates it, moves it to the right list
	$("#addBase").click(function(event)
	{
		// remove from the inactive box and add to the active box
		$("#activeBase").append($("#inactiveBase").find("option:selected"));
		
		// don't highlight the one we just added...looks less nice
		$("#activeBase").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActiveBases();
		updateInactiveBases();
		
		var my_options = $("#activeBase option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#activeBase").empty().append( my_options );
		
		$("#edit-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#merge-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
	});
	
	
	// REMOVING A BASE, deactivates it, moves it to the left list
	$("#removeBase").click(function(event){
		$("#inactiveBase").append($("#activeBase").find("option:selected"));
		$("#inactiveBase").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActiveBases();
		updateInactiveBases();

		var my_options = $("#inactiveBase option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#inactiveBase").empty().append( my_options );
		$("#edit-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#merge-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');

	});
	
	
	// ADD ALL BASES, moves all items from left to right
	$("#addAll").click(function(event){
		$("#activeBase").append($("#inactiveBase option"));
		$("#activeBase").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActiveBases();
		updateInactiveBases();
		
		var my_options = $("#activeBase option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#activeBase").empty().append( my_options );
		$("#edit-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#merge-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
	});
	
	
	// REMOVE ALL BASES, moves all items from right to left
	$("#removeAll").click(function(event){
		$("#inactiveBase").append($("#activeBase option"));
		$("#inactiveBase").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActiveBases();
		updateInactiveBases();
		
				var my_options = $("#inactiveBase option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#inactiveBase").empty().append( my_options );
		$("#edit-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#merge-base-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
		$("#edit-depart-btn").css("opacity", "0.5").attr('disabled', true).addClass('disabled');
	});
});
	
