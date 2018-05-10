$(function() {
    initLabPracticeWidget();
    
    $("#add-lab-partner").click(function(e){
        e.preventDefault();
        var shiftId = $(this).attr('data-shiftid');

        showLoginForm($(this).parents(".island"));
    });
    
    $("#add-lab-partner-instructor").click(function(e){
        e.preventDefault();
        var shiftId = $(this).attr('data-shiftid');
        $(".lab-partner-student-picker").slideToggle(800, function(){$('html, body').animate({scrollTop: $(this).offset().top - 100}, 1000);});
    });
    
    $("#add-lab-partners-instructor").button().click(function(e){
        e.preventDefault();
        var parentContainer = $(this).parents(".island");
        var studentIds = [];
        $(".msp_student_toggle_checkbox:checked").each(function(i, el){
            studentIds.push($(el).val());
        });
        
        
        $(".lab-partner-student-picker").slideToggle(800);
        if (studentIds.length) {
            blockUi(true);
            $.post("/skills-tracker/shifts/generate-multiple-practice-widgets", {"studentIds" : studentIds}, function(response){
                updateStudentPicker();
                blockUi(false);
                $(response).each(function(i, el){
                    widget = $("<div class='grid_12 island' data-labWidgetId='" + i + "'>" + el + "</div>");
                    parentContainer.after(widget.fadeIn(800));
                    widget.find(".pick-lab-shift").click(function(e){
                        e.preventDefault();
                        $.post("/skills-tracker/shifts/generate-lab-practice-widget", {"shiftId" : $(this).attr('data-shiftid')}, function(response) {
                        	// Find the widget that matches this specific instance...
                        	$('div[data-labWidgetId="' + i + '"]').html($(response).fadeIn(800));
                            initLabPracticeWidget();
                        }, "json");
                    });
                });
                initLabPracticeWidget();
            }, "json");
        }
    });
});

function showLoginForm(afterContainer)
{
    var form = $("<div class='grid_12 island hidden'>" + $(".loginForm").html() + "</div>");
    afterContainer.after(form);
    $(form).slideDown();
    $('html, body').animate({scrollTop: $(form).offset().top - 100}, 1000);
    $("#ok-link").button();
    $(".blue-button .ui-button-text-only .ui-button-text").css("padding-top", "0.5em");
    $(".blue-button .ui-button-text-only .ui-button-text").css("padding-bottom", "0.5em");
    $("#cancel-link").button();
    
    $(document).keypress(function(e) {
        if(e.which == 13) {
            if($("#ok-link").length > 0){
                $('#ok-link').trigger('click');
            }
        }
    });

    form.find("#cancel-link").click(function(e){
        e.preventDefault();
        form.slideUp();
        //form.remove();
    });
    
    form.find("#ok-link").click(function(e){
        e.preventDefault();
        form.find(".form-errors").remove();
        
        var username = form.find("#username").val();
        var password = form.find("#password").val();
        
        if (username == "" || password == "") {
            form.find(".login-form-wrapper").prepend("<div class='form-errors'>Please enter a username and password.</div>");                    
            form.find("#dummy-content-table").css("margin-bottom", "6em");                    
            return false;
        }
        
        blockUi(true);
        $.post("/skills-tracker/shifts/validate-lab-partner", {"username" : username, "password" : password}, function(response) {
            blockUi(false);
            form.html($(response).fadeIn(800));
            
            form.find("#try-again").click(function(e){
                e.preventDefault();
                form.remove();
                showLoginForm(afterContainer);
            });
            
            form.find(".pick-lab-shift").click(function(e){
                e.preventDefault();
                $.post("/skills-tracker/shifts/generate-lab-practice-widget", {"shiftId" : $(this).attr('data-shiftid')}, function(response) {
                    form.html($(response).fadeIn(800));
                    initLabPracticeWidget();
                }, "json");
            });
            
            initLabPracticeWidget();
            }, "json");
        
        return true;
    });
}

function initLabPracticeWidget()
{
    // unbind everything because we are about to add a ton of event handlers
    $(".delete-practice-item").unbind('click');
    $(".open-practice-item").unbind('click');
    $(".remove-practice-widget").unbind('click');
    $(".patientType").unbind('change');

    //Add click handler to all existing practice items to allow for deletion
    $(".delete-practice-item").click(function(e){
        e.preventDefault();
        var link = $(this);
        var cell = $(this).closest(".action-cell");
        var row = link.parents("tr");
        var itemId = $(link).attr('data-practiceid');

        // create the function that will happen once the countdown is complete
        function deletePracticeItem() {
            blockUi(true, $(cell), "throbber");
            positionBlocker($(row));
            $.post("/skills-tracker/shifts/delete-practice-item", {"practiceItemId": itemId}, function (response) {
                if (response == 0) {
                    $(link).parents("tr").remove();
                    link.parents("tbody").append("<tr><td colspan='6'>You have not entered any lab practice yet. Click the button below to begin.</td></tr>").fadeIn();
                } else {
                    link.parents("tr").fadeOut(1000, function (e) {
                        if (link.parents("tbody").find("tr:visible").length <= 1) {
                            $("#confirm-multiple-items").remove();
                        }
                        link.parents("tr").remove();
                    });

                }

                // reload the practice widgets on the page
                $('.widget-container').each(function (index, el) {
                    if ($(el).attr('data-widget-id') > 0) {
                        reloadWidget($(el).attr('data-widget-id'));
                    }
                });

            }, "json");
        }

        // set up countdown
        delayedAction(cell, deletePracticeItem, "deletePracticeItem"+itemId);

    });
    
    //Add click handlers to all of the existing practice items to edit
    $(".open-practice-item").click(function(e) {
        e.preventDefault();
        openPracticeModal({"practiceItemId" : $(this).attr("data-practiceid")});
    });
    
    //Loop through all of the practice item select boxes and turn them into fancy menu buttons
    $(".new-practice-item").each(function(i, element) {
        var shiftId = $(element).attr("data-shiftid");
        
        // Silk is the browser used on Amazon Kindles.  If that term appears in the useragent, don't do the fancy button thingy.
        if(navigator.userAgent.indexOf('Silk') < 0){
	        $(element).menuButton({
	            ident: shiftId,
				defaultSelectValue: "Lab practice item",
				type: $(element).attr("data-shifttype"),
	            onFilterSubmit: function(e){
	                // first save to session
	                $.post("/skills-tracker/shifts/save-default-definition", {
	                    "selectedDefId" : $("#lab_practice_items_" + shiftId).find(".menu-button-container").find(".selected").attr("value"),
	                    "shiftId" : shiftId
	                    }, function(response){}, "json");
	                
	                return openPracticeModal({"practiceDefinitionId": $('#menuButtonContainer_' + shiftId).find(".selected").attr("value"), "shiftId" : shiftId});
	        }});
        }else{
        	$(element).change(function(e){
                // first save to session
                $.post("/skills-tracker/shifts/save-default-definition", {
                    "selectedDefId" : $("#lab_practice_items_" + shiftId).find(".menu-button-container").find(".selected").attr("value"),
                    "shiftId" : shiftId
                    }, function(response){}, "json");
                
                return openPracticeModal({"practiceDefinitionId": $(element).val(), "shiftId" : shiftId});
        	});
        }
    });
    
    //Add click handler to delete practice widgets
    $(".remove-practice-widget").click(function(e){
        e.preventDefault();
        var practiceContainer = $(this).parents(".island");
        blockUi(true, practiceContainer, "throbber");
        $.post("/skills-tracker/shifts/remove-lab-partner-widget", {"shiftId" : $(this).attr("data-shiftid")}, function(response){
            updateStudentPicker();
            practiceContainer.slideUp(800, function(){$(this).remove()});
        }, "json");
    });
    
    $(".patientType").change(function(e) {
        $.post("/skills-tracker/shifts/update-practice-patient-type", {"patientTypeId" : $(this).val(), "practiceItemId" : $(this).attr('data-practiceid')}, function(response){
            
        }, "json");
    });
}

function updatePracticeItemWidget(shiftId)
{
    var selectedDefId = $("#lab_practice_items_" + shiftId).find(".menu-button-container").find(".selected").attr("value");
    
    $.post("/skills-tracker/shifts/generate-lab-practice-widget", {"shiftId" : shiftId, "selectedDefId" : selectedDefId}, function(response) {
            $("#lab_practice_items_" + shiftId).parent().html($(response).fadeIn(800));
            initLabPracticeWidget();
        }, "json");
}

function openPracticeModal(options)
{
    blockUi(true);
	options['primary_shift_id'] = $('#primary_shift_id').val();
	
	return $.post("/skills-tracker/shifts/get-practice-form", options, 
			function(response){
                blockUi(false);
				
				// Two cases here- either we get back the data/form to display, or a URL
				// that points to an eval that we need to open.
				if(response.data != undefined && response.form != undefined){
					$("#practiceDialog").html(response.form).dialog("option", "title", response.data['name']).dialog("open");
					$("#button-set-passed").buttonset();
					$("#button-set-evaluatorType").buttonset();
				} else{
					openEvalModal(response);
				}
			}, "json").done();
}

//Force a refresh of the student picker if it exists on the page
function updateStudentPicker()
{
    if(typeof msp_performFilter == 'function') {
        msp_performFilter();
    }
}

var frameModal = null;
var newIFrame = null;

function postSave(shiftId){
	frameModal.dialog('close');
	frameModal.dialog('destroy');
	
	$('#jquery-dialog-container').empty();
	
	newIFrame.empty();
	
	updatePracticeItemWidget(shiftId);
	
	// Refresh the widgets on the page...
	$('.widget-container').each(function(index, el){
		if($(el).attr('data-widget-id') > 0){
			reloadWidget($(el).attr('data-widget-id'));
		}
	});
}

function openEvalModal(evalUrl){
	var isIpad = navigator.userAgent.match(/iPad/i) != null;
	var isKindle = navigator.userAgent.match(/Silk/i) != null;
	
	// For iPads, just launch the window in the current one.  The "postUrl" page that gets used for these evals will
	// handle redirecting them back to /skills-tracker/shifts/my-shift/shiftId/$shiftId
	if(isIpad || isKindle){
		window.location = evalUrl;
	}else{
		frameModal = $('<div id="evalModal"></div>');
		
		$('#jquery-dialog-container').append(frameModal);
		
		frameModal.empty();
		
		frameModal.dialog({
			autoOpen: false, 
			width: 975, 
			height: 600,
			resizable: false,
			modal: true
		});
		
		newIFrame = $("<iframe id='eval-frame' type='text/html' width='925' height='525' src='" + evalUrl + "'></iframe>");
        
        $("#evalModal").find(".ui-dialog-content").css("overflow", "hidden");
        $("#evalModal").css("overflow", "hidden");
		frameModal.dialog('open');
		frameModal.append(newIFrame);
		frameModal.show();
	}
}


