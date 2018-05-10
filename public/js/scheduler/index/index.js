$(function(){
	$("#pdf-link").click(function(event) {
    	event.preventDefault();
		var trigger = $(this);
		
		if ($("#compliance-nav-bar").length > 0) {
			trigger.addClass("selected-single-nav-bar-item").prepend("<img src='/images/throbber_small.gif' id='load-pdf-modal-throbber'>");
		}
		else {
			trigger.addClass('selected').parent().prepend("<img src='/images/throbber_small.gif' id='load-pdf-modal-throbber'>");
		}
		
		var formValues = getFilters();
		var view = getViewType();
		
		$.post("/scheduler/pdf-export/generate", formValues,
		function(resp) {
			$("#pdfDialog").html($(resp).html());
			initPdfModal(view, 'new');
			$("#pdfDialog").dialog("open");
			$("#load-pdf-modal-throbber").remove();
			trigger.removeClass('selected-single-nav-bar-item');
			trigger.removeClass('selected');
		});
    });

    $("#sub-link").click(function(e){
        //Get information from filters to populate
        var data = getFilters();

        var location_text = getLocationText();
        var avail_text = getAvailText(data['show_avail']);
        var chosen_text = getChosenText(data['show_chosen']);

        if (chosen_text) {
            var filters_text = "showing";
            filters_text += " shifts";
            filters_text += location_text + chosen_text;

            if (avail_text) {
                filters_text += " <b>(Available shifts will be excluded)</b>";
            }

        } else {
            var filters_text = "Filters: no shifts";
        }



        $("#calendarSubDialog").find(".pdf-modal-filter-description").html("<b>Filters:</b> " + filters_text);
        initSubscriptionModal();
        $("#calendarSubDialog").dialog('open');
    });

	// only do this if the shift modal is initialized
	if ($("#shiftDialog").length > 0 ) {
		
		// only use quick add to add shifts in scheduler for students
		if (isStudent()) {
			$("#add-field-scheduler-shift").click(function(event) {
				event.preventDefault();
				var throbber =  $("<img id='add-shift-throbber' src='/images/throbber_small.gif'>");
				$(this).parent().prepend(throbber);
				loadShiftModal("field");
			});
		
			$("#add-clinical-scheduler-shift").click(function(event) {
				event.preventDefault();
				var throbber =  $("<img id='add-shift-throbber' src='/images/throbber_small.gif'>");
				$(this).parent().prepend(throbber);
				loadShiftModal("clinical");
			});
		
			$("#add-lab-scheduler-shift").click(function(event) {
				event.preventDefault();
				var throbber =  $("<img id='add-shift-throbber' src='/images/throbber_small.gif'>");
				$(this).parent().prepend(throbber);
				loadShiftModal("lab");
			});
		}
		
		setQuickAddEditListener();
	}
});

function setQuickAddEditListener() {

	$(".edit-quick-add-shift").unbind();
	
	// this is for the editing
	$(".edit-quick-add-shift").click(function(event) {
		event.preventDefault();
		var trigger = $(this);
		var shiftId = trigger.attr('data-shiftid');

		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		loadShiftModal(shiftId);
	});
}

function loadShiftModal(type)
{
	if (isNaN(parseInt(type))) {
		var title = "Quick Add "+type+" shift";
	} else {
		var title = "Edit Quick Add shift";
	}
    $.post("/skills-tracker/shifts/generate-shift-form", {"type" : type},
        function(resp) {
			$("#shiftDialog").dialog("option", "title", title);
			$("#shiftDialog").html($(resp).html());
			initShiftModal();
			$("#shiftDialog").dialog("open");
			$("#load-modal-throbber").remove();
			$("#add-shift-throbber").remove();
			$(".edit-quick-add-shift").css("opacity", "1");
    });
}

var isStudent;
function isStudent(){
	var student_presets = $(".student-presets");
			
	if (student_presets.length > 0) {
		return true;
	}
	
	return false;
}
