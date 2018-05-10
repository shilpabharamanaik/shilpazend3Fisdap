function initShiftRequestModal() {
    $('#cancel-btn').button().blur();
    $('#save-btn').button();
	$("#recipient").val("");
	
    $(".chzn-select").chosen();
	
    $(".ui-dialog").css({"overflow": "visible"});
    $(".ui-dialog .ui-dialog-content").css({"overflow": "visible"});
    $(".chzn-container-multi .chzn-results").css({"max-height": "300px"});
    $("#sites_chzn ul.chzn-choices").css({"overflow": "auto", "max-height": "6.5em"});
    $("#request-type-div").buttonset();
    $("#recipientDiv").hide();
    $("#swap-terms").hide();
    $("#duration-label label").append(" <span class='optional'>(in hours)</span>:");
    $("#save-btn .ui-button-text").html('Save');
    
    $("#shiftRequestDialog").dialog({
	open: function (){
	    $("#pending a").blur();
	}
    });

    // if the pending div exists, there is a pending request
    if ($("#pending").length == 0) {
	noPendingRequest();
    } else {
	hasPendingRequest();
    }
	
	// if we're in IE9, we need to do some weird conditional stuff
	if ($.browser.msie) {
		if (jQuery.browser.version == 9.0) {
			$("#swap-terms").find(".chzn-container-multi").find(".search-field").find("input").css("color", "#fff");
		}
	}

    // if there are 2 inputs in the div, that means there's only one request option, so don't make them pick
    if ($("#request-type-div :input").size() == 2) {
        var singleType = $("#request-type-div :input").first();
        $("#request-type-div").hide();
        $("#form_div h3").first().hide();
        $(singleType).attr("checked", true);
        setup($(singleType).val());
    }

    $("#request-type-div :input").change(function(){
	if($(this).attr("checked")){
	    setup($(this).val());
	}
    });

    $("#recipient").change(function(){
        updateRecipientRequestButton();
    });
    
    $('#cancel-btn').click(function(event) {
	event.preventDefault();
	$('#shiftRequestDialog').dialog('close');
    });
    
    $('#save-btn').click(function(event) {
	event.preventDefault();
	 var postValues = $('#shiftRequestDialog form').serialize();
	$('#shiftRequestForm :input').attr('disabled', true);
	var cancelBtn = $('#cancel-btn').hide();
	var saveBtn = $('#save-btn').hide();
	var throbber =  $("<img id='shiftRequestThrobber' src='/images/throbber_small.gif'>");
	saveBtn.parent().append(throbber);
	$.post(
	    '/scheduler/index/process-shift-request',
	    postValues,
	    function (response) {
	        if(response['success'] === true) {
                    if ($("#cal-display-filters").length > 0) {
                        loadNewCalendar(getViewType(), getDate(), getEndDate(), getFilters());
						var request_count = $("#compliance-nav-bar").find(".request-count");
						if (request_count.length > 0) {
							request_count.text(response['request_count']);
						}
						else {
							$("#compliance-nav-bar").find(".requests-txt").after("<span class='request-count'>" + response['request_count'] + "</span>");
						}
						
                    } else {
                        window.location.reload(true);
                    }
					
                    $('#shiftRequestDialog').dialog('close');
                    cancelBtn.show();
                    saveBtn.show();
                    throbber.remove();
		} else {
		    htmlErrors = '<div id=\'shiftRequestErrors\' class=\'form-errors alert\'><ul>';
				
		    $('label').removeClass('prompt-error');
	
                    $.each(response, function(elementId, msgs) {
		        $('label[for=' + elementId + ']').addClass('prompt-error');
		        $.each(msgs, function(key, msg) {
		            htmlErrors += '<li>' + msg + '</li>';
		        });
                        if(elementId == 'site_type'){
                            $('#typeContainer').css('border-color','red');
			}
		    })
                    htmlErrors += '</ul></div>';

		    $('.form-errors').remove();
		    $('#shiftRequestDialog form').prepend(htmlErrors);
	
                    cancelBtn.show();
		    saveBtn.show();
		    saveBtn.parent().find('#shiftRequestThrobber').remove();
		}
	    }
	)
    });
}

function setup(type) {
    var instructions;
    var recipient = $("#recipient_chzn").find("span").html();
    var permission = $("#drop_needs_permission").val();
    
    // drop
    if (type == 1) {
	$("#recipientDiv").slideUp();
	$("#swap-terms").slideUp();
        
        if (permission == 1) {
            instructions = "Your instructor will need to approve this drop.";
            var buttonText = "Request approval";
        } else {
            instructions = "Please confirm that you would like to drop this shift.";
            var buttonText = "Confirm drop";
        }
        $("#save-btn .ui-button-text").html(buttonText);
        $("#save-btn").show();
    }

    // cover
    if (type == 2) {
	$("#recipient-label label").html('Who do you want to ask to cover your shift?');
    	$("#recipientDiv").slideDown();
	$("#swap-terms").slideUp();
        updateRecipientRequestButton()
        instructions = "";
    }

    // swap
    if (type == 3) {
	$("#recipient-label label").html('Who do you want to swap shifts with?');
    	$("#recipientDiv").slideDown();
        if ($("#recipient_chzn").find(".active-result").size() > 0) {
            $("#swap-terms").slideDown();
        }
        updateRecipientRequestButton();
        instructions = "";
    }
    
    $("#instructions").html(instructions);

}

function updateRecipientRequestButton() {
        var buttonText;
	var recipientName = $("#recipient_chzn").find("span").html();
        var recipientValue = $("#recipient").val();
        if (recipientValue == 'calendar') {
            buttonText = 'Send shift back to the calendar';
        } else {
            buttonText = 'Send request to '+recipientName;
        }
		
		if (buttonText == "Send request to Select a student...") {
			buttonText = "Send request";
			$("#save-btn").button("disable");
		}
		else {
			$("#save-btn").button("enable");
		}
		
        $("#save-btn .ui-button-text").html(buttonText);
        
        // if there is no one in the recipient list, hide the button
        if ($("#recipient_chzn").find(".active-result").size() < 1) {
            $("#save-btn").hide();
        } else {
            $("#save-btn").show();
        }
}

function hasPendingRequest() {
	$("#save-btn").hide();
	$("#save-btn").parent().find(":button span").first().html('Ok');
}

function noPendingRequest() {
	$("#save-btn").show();
	$("#save-btn").parent().find(":button span").first().html('Cancel');
}

