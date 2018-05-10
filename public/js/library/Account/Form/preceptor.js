	
$(function(){
	// initialize buttons
	$('a#addPreceptor').button();
	$('a#removePreceptor').button();
	$('a#addAllPreceptors').button();
	$('a#removeAllPreceptors').button();
	
	$('a#edit-preceptor-btn').button();
	$('a#merge-preceptor-btn').button();
	$('a#new-preceptor-btn').button();
	
	// hide the throbber, we'll use it later
	$('#preceptorThrobberContainer').hide();
	
	// disable the buttons
	$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
	$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
	// make the disabled buttons not do hover effects
	$(".disabled").hover(function() {
		$(this).css({"color": "white", "cursor": "default"});
	});
		
	// initialize the hidden inputs
	updateActivePreceptors();
	updateInactivePreceptors();
	
	
	$("a#merge-preceptor-btn").click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
		if ($(this).css('opacity') != "1") {
            return false;
        }
		
		
		$('#radioContainer').empty();
		$("#preceptor-table").empty();
		$("#mergePreceptorErrors").hide();
		$("#preceptorMergeModalThrobber").remove();

		var count = $("#inactivePreceptors :selected").length;

		if(count != 0){
			var selectedOptions = $("#inactivePreceptors :selected");

		}
		else{
			var selectedOptions = $("#activePreceptors :selected");
		}
		
		$(selectedOptions).each(function()
		{
			var radioName = "mergeRadio";
			var radioValue = $(this).val();
			var radioLabel = $(this).text();
			
			$('<input type="radio" id="' + radioValue + '" name="' + radioName + '" value="' + radioValue + '"><label for="' + radioValue + '">' + radioLabel + '</label><br />').appendTo($("#radioContainer"));
			$("<tr id=" + radioValue + " class='preceptor-row'><td>" + radioLabel + "</td></tr>").appendTo($("#preceptor-table"));	
		});
		$("#radioContainer").hide();
		$("#cancel-preceptor-merge-btn").button();
		$("#do-preceptor-merge-btn").button();
		
		$("tr.preceptor-row").click(function(event) {
			event.preventDefault();
			var preceptorId = $(this).attr('id');
			$("tr.preceptor-row").removeClass('selected');
			$('input[name$="mergeRadio"]').attr('checked', false);
			$(this).addClass('selected');
			$('#'+preceptorId).attr('checked', true);
		});

		$("#mergePreceptorsDialog").dialog("open");
		
	});
	
	$("#cancel-preceptor-merge-btn").click(function(e) {
		e.preventDefault();
		$('#mergePreceptorsDialog').dialog('close');
		$('#cancel-preceptor-merge-btn').show();
		$('#do-preceptor-merge-btn').show();
		$("#preceptorMergeModalThrobber").remove();
	});
	
	$("#do-preceptor-merge-btn").click(function(e)
	{
		e.preventDefault();
		var cancelBtn = $('#cancel-preceptor-merge-btn').hide();
		var mergeBtn = $('#do-preceptor-merge-btn').hide();
		var throbber =  $("<img id='preceptorMergeModalThrobber' src='/images/throbber_small.gif'>");
		mergeBtn.parent().append(throbber);
						
		var count = $('#inactivePreceptors :selected').length;

		if(count != 0){
			var selectedOptions = $('#inactivePreceptors :selected');
		}
		else{
			var selectedOptions = $('#activePreceptors :selected');
		}
	
		var notSelectedPreceptorsArray = new Array();
		$(selectedOptions).each(function()
		{
			if($(this).val() != $('input[name=mergeRadio]:checked').val()){
				notSelectedPreceptorsArray.push($(this).val());
			}
		});
		
		$.post(
			'/account/sites/merge-preceptors',
			{
				selectedPreceptor: $('input[name=mergeRadio]:checked').val(),
				notSelectedPreceptors: notSelectedPreceptorsArray,
				site: $('#siteId').val()
			},					
			function(response){
				if(typeof response == 'number'){
					var count = $('#inactivePreceptors :selected').length;
					if(count != 0){
						var selectedOptions = $('#inactivePreceptors :selected');
						var selectedOptionsHidden = $('#hiddenInactivePreceptors :selected');			
					}
					else{
						var selectedOptions = $('#activePreceptors :selected');
						var selectedOptionsHidden = $('#hiddenActivePreceptors :selected');
					}
							
					$(selectedOptions).each(function()
					{
						if($(this).val() != response){
							$(this).remove();
						}
					});

					$(selectedOptionsHidden).each(function()
					{
						if($(this).val() != response){
							$(this).remove();
						}
					});
			
					$('#mergePreceptorsDialog').dialog('close');
					$("#edit-preceptor-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
					$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
					
					$('#cancel-preceptor-merge-btn').show();
					$('#do-preceptor-merge-btn').show();
					$("#preceptorMergeModalThrobber").remove();
					
					$('#baseForm').val('');
				}
				else {
					htmlErrors = '<div id=\'mergePreceptorErrors\' class=\'form-errors alert\'><ul>';
					
					$('label').removeClass('prompt-error');
					
					htmlErrors += '<li>' + response + '</li></ul></div>';
						
					$('.form-errors').remove();
					$('#mergePreceptorsDialog form').prepend(htmlErrors);
						
					$('#cancel-preceptor-merge-btn').show();
					$('#do-preceptor-merge-btn').show();
					$("#preceptorMergeModalThrobber").remove();
				}
							
			}
		)							
	});

	
	// when the NEW button is clicked
	$("#new-preceptor-btn").button().click(function(event) {
        event.preventDefault();
		
		// set the title to new
		$("#newPreceptorDialog").dialog("option", "title", "Add New Preceptor");
		
		// reset the form
		$("#preceptor_first").val("");
		$("#preceptor_last").val("");
		$("#preceptor_work").val("");
		$("#preceptor_home").val("");
		$("#preceptor_pager").val("");
		$("#preceptor_email").val("");
		$("#preceptor_id_input").val("noPreceptor");
		
		// then open the dialog
        $("#newPreceptorDialog").dialog("open");
		
		$('#newPreceptorErrors').remove();
		$('label').removeClass('prompt-error');
    });
	
	
	// when the EDIT button is clicked (for field only):
	$("#edit-preceptor-btn").button().click(function(event) {
		event.preventDefault();
		
		//Don't do anything if the button is disabled
		//Don't do anything if the button is disabled
		if ($(this).css('opacity') != "1") {
            return false;
        }
		
		
		$('#newPreceptorErrors').remove();
		$('label').removeClass('prompt-error');
		
		// find the selected preceptor
		preceptorId = $("#inactivePreceptors").find("option:selected").val();
		if(preceptorId == null){
			preceptorId = $("#activePreceptors").find("option:selected").val();
		}
		
		// set the value of the hidden input
		$("#preceptor_id_input").val(preceptorId);
		
		// post the data to get the base then fill in the fields on the form
		$.post("/account/sites/get-preceptor-data", {preceptorId: preceptorId}, function(response){
			
			$("#preceptor_first").val(response['first_name']);
			$("#preceptor_last").val(response['last_name']);
			$("#preceptor_work").val(response['work_phone']);
			$("#preceptor_home").val(response['home_phone']);
			$("#preceptor_pager").val(response['pager']);
			$("#preceptor_email").val(response['email']);
			
			var newTitle = "Edit " + response['first_name'] + " " + response['last_name'];
			
			$("#newPreceptorDialog").dialog("option", "title", newTitle);
			$("#newPreceptorDialog").dialog("open");
			$("#preceptorThrobberContainer").hide();
			
			$('a#edit-preceptor-btn').show();


		});
		
		// hide the button and show the throbber until we're ready
		$('a#edit-preceptor-btn').hide();
		$('#preceptorThrobberContainer').show();
		
		
    });
	
	// will update the hidden active preceptors
	function updateActivePreceptors()
	{
		// empty the box
		$('#hiddenActivePreceptors').find('option').remove();
		
		// clone whats currently in active
		$("select#hiddenActivePreceptors").append($("#activePreceptors option").clone());
		
		// then select it all
		$("select#hiddenActivePreceptors *").attr("selected", true);
	}
	
	// will update the hidden Inactive preceptors
	function updateInactivePreceptors(){
		$('#hiddenInactivePreceptors').find('option').remove();
		$("select#hiddenInactivePreceptors").append($("#inactivePreceptors option").clone());
		$("select#hiddenInactivePreceptors *").attr("selected", true);
	}
	
	// sort the lists alphabetically by last name
	var my_options = $("#activePreceptors option");
	my_options.sort(function(a,b) {
		var arrayA = a.text.split(' ');
		var arrayB = b.text.split(' ');
		if (arrayA[arrayA.length-1].toUpperCase() > arrayB[arrayB.length-1].toUpperCase()) return 1;
		else if (arrayA[arrayA.length-1].toUpperCase() < arrayB[arrayB.length-1].toUpperCase()) return -1;
		else return 0
	})
		
	$("#activePreceptors").empty().append( my_options );
	
	// sort the lists alphabetically by last name
	var my_inactive_options = $("#inactivePreceptors option");
	my_inactive_options.sort(function(a,b) {
		var arrayA = a.text.split(' ');
		var arrayB = b.text.split(' ');
		if (arrayA[arrayA.length-1].toUpperCase() > arrayB[arrayB.length-1].toUpperCase()) return 1;
		else if (arrayA[arrayA.length-1].toUpperCase() < arrayB[arrayB.length-1].toUpperCase()) return -1;
		else return 0
	})
		
	$("#inactivePreceptors").empty().append( my_inactive_options );
	
	
	
	// The functions for when a new option is selected from either list
	// Determines when the "edit" button should appear and will only allow one list to have item(s) selected
	$("select#inactivePreceptors").change(function(){
		$("#activePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		var count = $("#inactivePreceptors :selected").length;

		if(count == 1){
			$("#edit-preceptor-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
		else if(count > 1){
			$("#merge-preceptor-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
		else {
			$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}

	});
	
	$("select#activePreceptors").change(function(){
		$("#inactivePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		var count = $("#activePreceptors :selected").length;

		if(count == 1){
			$("#edit-preceptor-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
		else if(count > 1){
			$("#merge-preceptor-btn").css("opacity", "1").attr('disabled', '').removeClass('disabled');
			$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
		else {
			$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
			$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		}
	});
	
	
	
	// ADDING A PRECEPTOR, activates it, moves it to the right list
	$("#addPreceptor").click(function(event)
	{
		// remove from the inactive box and add to the active box
		$("#activePreceptors").append($("#inactivePreceptors").find("option:selected"));
		
		// don't highlight the one we just added...looks less nice
		$("#activePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActivePreceptors();
		updateInactivePreceptors();
		
		var my_options = $("#activePreceptors option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#activePreceptors").empty().append( my_options );
		
		
		$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');

	});
	
	
	// REMOVING A PRECEPTORS, deactivates it, moves it to the left list
	$("#removePreceptor").click(function(event){
		$("#inactivePreceptors").append($("#activePreceptors").find("option:selected"));
		$("#inactivePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActivePreceptors();
		updateInactivePreceptors();

		var my_options = $("#inactivePreceptors option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#inactivePreceptors").empty().append( my_options );
		$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');

	});
	
	
	// ADD ALL PRECEPTORS, moves all items from left to right
	$("#addAllPreceptors").click(function(event){
		$("#activePreceptors").append($("#inactivePreceptors option"));
		$("#activePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActivePreceptors();
		updateInactivePreceptors();
		
		var my_options = $("#activePreceptors option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#activePreceptors").empty().append( my_options );
		$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');


	});
	
	
	// REMOVE ALL PRECEPTORS, moves all items from right to left
	$("#removeAllPreceptors").click(function(event){
		$("#inactivePreceptors").append($("#activePreceptors option"));
		$("#inactivePreceptors").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
		
		// finally, update our hidden select boxes
		updateActivePreceptors();
		updateInactivePreceptors();
		
		var my_options = $("#inactivePreceptors option");

		my_options.sort(function(a,b) {
			if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
			else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
			else return 0
		})
		
		$("#inactivePreceptors").empty().append( my_options );
		$("#edit-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		$("#merge-preceptor-btn").css("opacity", "0.5").attr('disabled', 'disabled').addClass('disabled');
		
	});
});
	
