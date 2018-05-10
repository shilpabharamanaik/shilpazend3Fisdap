function initPdfModal(view, mode) {
    $('#export-cancel-btn').button().blur();
    $('#export-btn').button();

	// make view type all lowercase
	var f = view.charAt(0).toLowerCase();
	view = f + view.substr(1);

	// set up the modal to edit a recurring email
	if (mode == 'edit') {
		$("#recurring").attr('checked', true);
		$("#export_type").hide();
		makeRecurring();
		$("#date-range").hide();
		$("#recurring_type").css("position", "inherit");
		$("#pdf-options").css("margin-top", "1em");
		var title = "Edit recurring email, "+view+" view";
	} else {
		// otherwise, figure out start and end date, based on view type
        initDatePickerValues(view);
		
		var title = "Export " + view + " view";
		
		if (view == "month-details") {
			title = "Export month details view";
		}
	}
	
	// create buttonsets
	$("#export_type").buttonset();
	$("#recurring_type").buttonset();
	$("#orientation_type").buttonset();
	$("#color_type").buttonset();
	$("#email_frequency_offset_type").buttonset();
	setDefault("export_type");
	setDefault("recurring_type");
	setDefault("orientation_type");
	setDefault("color_type");
	setDefault("email_frequency_offset_type");
	pluralizeButtons();
	$("#frequency").html($("#pdf_recurring_type").val());
	
	$(".cupertino .ui-button").css('margin', '5px -3px');
	$("#legend_switch").sliderCheckbox({onText: "On", offText: "Off"});
		
	// set the image
	setImage();
	
	// chosens
	$(".chzn-select").chosen();
	$(".ui-dialog").css({"overflow": "visible"});
    $(".ui-dialog .ui-dialog-content").css({"overflow": "visible"});
	$("#email_frequency_offset_chzn").css("width", "45px");
	$("#email_frequency_offset_chzn .chzn-drop").css("width", "43px").addClass("modal-chzn");
	$("#email_frequency_offset_chzn").find(".chzn-search").hide();
    $("#email_frequency_offset_chzn").find(".chzn-results").css("padding-top", "0.5em");
    $("#email_frequency_offset_chzn .chzn-drop ul.chzn-results").css({"overflow": "auto"});
	
    $("#pdfDialog").dialog({
		open: function () {
			$("#nameHelp").blur();
		    $("#nameHelp img").blur();
			$("#name").blur();
		},
		title: title,
		position:['middle',20]
    });
    
	// add the cluetip for this modal
	$('#frequencyHelp').cluetip({activation: 'click',
                        local:true, 
                        cursor: 'pointer',
                        width: 680,
						cluezIndex: 2000000,
                        cluetipClass: 'jtip',
                        sticky: true,
                        closePosition: 'title',
                        closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});

	$('#filter_summary').cluetip({activation: 'click',
                        local:true, 
                        cursor: 'pointer',
                        width: 680,
						cluezIndex: 2000000,
                        cluetipClass: 'jtip',
                        sticky: true,
                        closePosition: 'title',
                        closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});
	
	// finish styling
	if (navigator.userAgent.indexOf('MSIE') != -1) {
		styleForIE();
	}
	if (navigator.userAgent.indexOf('Chrome') != -1) {
		styleForChrome();
	}
	if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
		styleForSafari();
	}

	$("#legend-text").css("margin-top", "-0.5em");
	
	if ($("#cal-island").length > 0) {
		var filter_summary = $("#cd-filters-wrapper_filters-title-text").text();
		$(".pdf-modal-filter-description").html("<b>Filters:</b> " + filter_summary.substring(8, filter_summary.length));
		
		if (view == "month-details") {
			var display_options_summary_raw = $(".month_details_display_options_summary").text();
			var display_option_summary_no_title = display_options_summary_raw.substring(16, display_options_summary_raw.length);
			var display_option_summary = display_option_summary_no_title.substring(0, display_option_summary_no_title.length-5);
			$(".pdf-modal-filter-description").append("<br /><b>Display options:</b>" + display_option_summary);
		}
	}
	else {
		$(".pdf-modal-filter-description").remove();
		$("#filter_summary").remove();
	}

	// buttonset functionality
	$("#pdf-options .ui-buttonset .ui-button").click(function() {
		var value = $(this).attr('for');
		var buttonset = $("#pdf_" + $(this).parent().attr('id'));
		buttonset.attr("value", value);
		setImage();
	})
	// legend switch
	$("#legend_switch-slider-button").click(function() {
		//console.log("changed");
		setImage();
	});
	
	// export type changing
	$("#export_type").find("label").click(function() {
		var type = $(this).attr('for');

		switch (type) {
			case "pdf":
        $("#date-range").slideDown();
				$("#email_options").slideUp();
				$("#recurring_options").slideUp();
				$("#date-range").fadeIn();
				$("#recurring_type").fadeOut();
        $("#pdf-options").fadeIn();
        $("#legend-div").fadeIn();
				$("#export-btn .ui-button-text").html("Export PDF");
        $("#pdf-help").show();
        $("#scheduler_export_instructor").hide();
        setImage();
				break;
			case "email":
        $("#date-range").slideDown();
				$("#email_options").slideDown();
				$("#recurring_options").slideUp();
				$("#date-range").fadeIn();
				$("#recurring_type").fadeOut();
        $("#pdf-options").fadeIn();
        $("#legend-div").fadeIn();
				$("#export-btn .ui-button-text").html("Export & Send");
        $("#pdf-help").show();
        $("#scheduler_export_instructor").hide();
        setImage();
        break;
			case "recurring":
        $("#pdf-options").fadeIn();
        $("#legend-div").fadeIn();
        $("#pdf-help").show();
        $("#scheduler_export_instructor").fadeIn();
        makeRecurring();
        setImage();
				break;
		}
	});
	
	// don't let the user type in a date
	$('#start_date').keydown(function() { return false; });
	$('#end_date').keydown(function() { return false; });
	
	// dates changing
	$("#start_date").change(function(e){
		$("#end_date").datepicker( "option", "minDate", $(this).datepicker("getDate"));
	});
	$("#endDateRange").change(function(e){
		$("#start_date").datepicker( "option", "maxDate", $(this).datepicker("getDate"));
	});
	
	// recurring type changing
	$("#recurring_type .ui-button").click(function(e){
		$("#frequency").html($(this).attr("for"));
	});
	
	// offset number changing
	$("#email_frequency_offset").change(function(e) {
		pluralizeButtons();
	});
	
	// Cancel button
    $('#export-cancel-btn').click(function(event) {
		event.preventDefault();
		$('#pdfDialog').dialog('close');
		$("#pdf-link").blur();
    });
	
    // export button
    $('#export-btn').click(function(event) {
		event.preventDefault();
		var type = $("#export_type").find(".ui-state-active").attr("for");
		
		if ($("#email_id").val() < 1) {
			// if this is a new email get the filter data needed by the view
			var postValues = getCalendarData(getViewType(), getDate(), getEndDate(), getFilters());
		} else {
			var postValues = new Object();
		}
		
		// add modal form data to the post data object
		var modalForm = {};
		$.each($('#pdfDialog form').serializeArray(), function (i, e) {
			modalForm[e['name']] = e['value'];
		});
		var name = $('#name').val();
		if (name == '') {
			name = 'Shift Schedule';
			modalForm['name'] = name;
		}
		postValues['modal-form'] = modalForm;

		postValues['modal-form']['pdf_export_type'] = $("#export_type").find(".ui-state-active").attr("for");
		postValues['modal-form']['pdf_email_frequency_offset_type'] = $("#freq-inputs").find(".ui-state-active").attr("for");
		
		// prepare modal to do the ajax call
		$('#pdfForm :input').attr('disabled', true);
		var cancelBtn = $('#export-cancel-btn').hide();
		var saveBtn = $('#export-btn').hide();
		var throbber =  $("<img id='pdfThrobber' src='/images/throbber_small.gif'>");
		saveBtn.parent().append(throbber);
		$.post(
		    '/scheduler/pdf-export/process',
		    postValues,
		    function (response) {
		        if (typeof response != 'object') {
					
					switch (type) {

						// download pdf
						case "pdf":
							var pdfName = name + ".pdf";
							var divContents = $("<div></div>");
							divContents.empty();
							divContents.html(response);
							styleHeaderForPdf(divContents, view);
	
							createPdf(divContents, pdfName, "pdfDialog", 0);
							
					        $('#pdfDialog').dialog('close');
							break;
						// send one-time email
						case "email":
							var pdfName = name + ".pdf";
							var divContents = $("<div></div>");
							divContents.empty();
							divContents.html(response);
							styleHeaderForPdf(divContents, view);
							
							var success = createPdf(divContents, pdfName, "pdfDialog", 0);

							break;
						
						// create a recurring email
						case "recurring":
							if ($("#email_id").val() < 1) {
								var success = response;
								break;
							} else {
								window.location.reload(true);
								return true;
							}

                        //Show a confirmation dialog
                        case "subscribe":
                            $("#pdfDialog").empty().html($(response).fadeIn());
                            $("#calendar-ok-btn").button().click(function(e){$('#pdfDialog').dialog('close')});
                            $("#subscription_url").select();
                            break;
					}
					
					$('.form-errors').remove();
					$('#mainFormContent').slideUp();
					if (success) {
						$('#'+type+'Success').slideDown();
					} else {
						$('#fail').slideDown();
					}
					$('html,body').animate({scrollTop: $("#pdfDialog").offset().top-60},'slow');
					cancelBtn.show().find(".ui-button-text").html('Ok');
					throbber.remove();
					$('#greyscale-styles').remove();
					$('#pdf-styles').remove();
				} else {
				    htmlErrors = '<div id=\'pdfErrors\' class=\'form-errors alert\'><ul>';
					
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
					$('#pdfDialog form').prepend(htmlErrors);
	
					cancelBtn.show();
					saveBtn.show();
					saveBtn.parent().find('#pdfThrobber').remove();
				}
				return true;
			}
		)
    });
	
	function styleHeaderForPdf(divContents, view) {
		// add css files
		var htmlHead = $("head");
		htmlHead.append("<link href='/css/library/Scheduler/View/Helper/calendar-view-pdf.css' type='text/css' rel='stylesheet' id='pdf-styles'>");
		
		// get black and white styling, if necessary
		if ($("#pdf_color_type").length > 0 && $("#pdf_color_type").val() == 'b-and-w') {
			htmlHead.append("<link href='/css/library/Scheduler/View/Helper/calendar-view-greyscale.css' type='text/css' rel='stylesheet' id='greyscale-styles'>");
		}
		
		// sort the events <= javascript is the only way to do this, so it won't work for recurring emails for now
		//sortEvents($(divContents).find("." + view + "-event-view"), "." + view + "-event", ".content");

		// get rid of the bottom borders in week view
		var sections = $(divContents).find(".week-event-view");
		sections.each(function(){
			var events = $(this).find(".week-event");
			var count = 0;
			events.each(function(){
				count++;
				if (count == events.length) {
					$(this).css('border-bottom', 'none');
				}
			});
		});
	}
	
	function makeRecurring() {
		$("#email_options").slideDown();
		$("#recurring_options").slideDown();
		$("#date-range").fadeOut();
		$("#recurring_type").fadeIn();
    $("#scheduler_export_instructor").fadeIn();
		$("#export-btn .ui-button-text").html("Save recurring email");
	}
	
	function setDefault(name) {
		var inputs = $("#"+name).find('input');
		var value = $("#pdf_"+name).val();
		inputs.each(function() {
			if ($(this).attr('id') == value) {
				$(this).attr('checked', true).button("refresh");
			} else {
				$(this).attr('checked', false).button("refresh");
			}
		});
	}
	
	function pluralizeButtons() {
		var offsetNumber = $("#email_frequency_offset").val();
		var buttons = $("#email_frequency_offset_type").find(".ui-button-text");
		buttons.each(function(){
			var buttonType = $(this).parent().attr("for").substr(5);
			// make first letter uppercase
			var f = buttonType.charAt(0).toUpperCase();
			buttonType = f + buttonType.substr(1);
			if (offsetNumber == "1") {
				$(this).html(buttonType);
			} else {
				$(this).html(buttonType + "s");
			}
		});
	}
	
	function setImage() {
		var orientation = $("#pdf_orientation_type").val();
		if ($("#pdf_color_type").val() == 'color') {
			var color = 'color';
		} else {
			var color = 'bw';
		}
		if ($("#legend_switch-slider-button").html() == 'On') {
			var legend = 'legend';
		} else {
			var legend = 'nolegend';
		}
		
		if (view == "month-details") {
			view_img_type = "details";
		}
		else {
			view_img_type = view;
		}
		
		$("img#preview-image").attr("src", "/images/previews/"+view_img_type+"-"+orientation+"-"+color+"-"+legend+".png");
        $("#preview-title").show();
	}
	
	function styleForIE() {
		$("#email_subject").css("width", "342px");
		$("#email_note, #email_recipients").css("width", "98%");
		$("#freq-inputs").css("width", "97%");
	}
	
	function styleForChrome() {
		$("#name").css("width", "333px");
		$("#email_note, #email_recipients").css("width", "402px");
		$("#email_recipients").css("height", "84px");
		$("#email_note").css("height", "144px");
		$("#email_frequency_offset_type").css("top", "-6px");
	}
	
	function styleForSafari() {
		$("#name").css("width", "333px");
		$("#email_note, #email_recipients").css("width", "402px");
		$("#email_recipients").css("height", "84px");
		$("#email_note").css("height", "144px");
		$("#email_frequency_offset_type").css("top", "-6px");
		$("#freq-inputs").css("width", "97%");
	}

    function initDatePickerValues(view) {
        var start = getDate();
        var startString = parseInt((start.getMonth()))+1 + "/" + parseInt(start.getDate()) + "/" + parseInt(start.getFullYear());

        switch (view) {
            case "month":
            case "month-details":
                var end = new Date(start.getFullYear(), start.getMonth()+1, 0);
                break;
            case "week":
                var end = new Date(start.getFullYear(), start.getMonth(), start.getDate()+6);
                break;
            case "day":
                var end = getDate();
                break;
            case "list":
                var end = getEndDate();
        }

        var endString = parseInt((end.getMonth()))+1 + "/" + parseInt(end.getDate()) + "/" + parseInt(end.getFullYear());

        $("#start_date").datepicker().attr('value', startString);
        $("#end_date").datepicker().attr('value', endString);
        $("#start_date").datepicker("setDate", start);
        $("#end_date").datepicker("setDate", end);
        $("#end_date").datepicker( "option", "minDate", $("#start_date").datepicker("getDate"));
    }
	
	$("#name").blur();
	
}
