$(function(){
	initComplianceStatusForm();
});

var initComplianceStatusForm;

initComplianceStatusForm = function()
{
	
	$("#saveButton").parent().addClass("green-buttons");
	$("#saveButton").button().css("padding", "0.35em").css("width", "75px");
	$("#cancel-btn").button().click(function(e){
		e.preventDefault();
		$(this).css("opacity", "0");
		$(this).parent().append("<img src='/images/throbber_small.gif' id='cancel-status-throbber'>");
		window.location.href = '/scheduler/compliance/clear-status-session';
	});
	
	setTimeout( "$('.success').fadeOut();",3000 );

	// initialize the expiration buttons
	$(".expiration-btn").button().click(function(e){
		e.preventDefault();
		
		$(".exp-count").text(getEmptyExpirationCount($(this).parents(".compliance-section")));
		
		$("#expiration-menu").show().css("left", $(this).offset().left).css("top", $(this).offset().top + 50);
		
		var expButton = $(this);
		
		//Overwrite the previous clickhandler since it's related to a different compliance section
		$("#exp-apply").unbind("click").click(function(e){
			e.preventDefault();
			var date = $("#expDatePicker").val();
			
			//Loop through all expiration dates and set them to the chosen one
			$(expButton).parents(".compliance-section").find(".expirationDate:visible").each(function(i, el){
				if (!$(el).val()) {
					$(el).val(date);
				}
			});
			$("#expiration-menu").hide();
			$("#expDatePicker").val("");
			
			disableAllExpirationDatePickers();
		});
 	});

	// initialize the cancel buttons for the expiration dates
	$("#exp-cancel").button().parent().addClass("extra-small");
	$("#exp-cancel").click(function(e){
		e.preventDefault();
		$("#expiration-menu").hide();
	});
	$("#exp-apply").button().parent().addClass("blue-button extra-small");
	
	$("#edit-compliance-status").submit(function(e){
		//Remove previous errors
		$(".form-errors").remove();
		$("label").removeClass("prompt-error");
		$(".input-error").removeClass("input-error");
		
		var hasErrors = false;
		$(".selectDate:visible").each(function(i, el) {
			if (!isValidDate($(this).val())) {
				$(this).addClass("input-error");
				$(this).parents(".form-prompt").find("label").addClass("prompt-error");
				hasErrors = true;
			}
		});
		
		if (hasErrors) {
			height = $("#edit-compliance-title").after($("<div class='form-errors'>Please enter valid dates for all requirements (mm/dd/yyyy).</div>").slideDown()).offset().top;
			
			$('html,body').animate({scrollTop: height},'slow');
			
			return false;
		}
		
		busyRobot();
		
		return true;
	});
	
	$(".mark-compliant").click(function(e) {
		e.preventDefault();
		$(this).parents(".compliance-section").find("input[type=checkbox]").each(function(i, el) {
			if (!$(el).is(":checked")) {
				$('#' + $(el).attr("id") + '-slider-button').click();
			}
		});
	});
	
	$(".mark-non-compliant").click(function(e) {
		e.preventDefault();
		$(this).parents(".compliance-section").find("input[type=checkbox]").each(function(i, el) {
			if ($(el).is(":checked")) {
				$('#' + $(el).attr("id") + '-slider-button').click();
			}
		});
	});

	initComplianceSliders();
	initDatePickers();
	disableAllExpirationDatePickers();

	// adding a new attachment
	$(".add-new-attachment").click(function(e){
		e.preventDefault();
		var attachmentId = $(this).attr("data-attachmentid");
		var row = $(this).parents(".compliance-row");
		
		//Set hidden element so that PHP knows this is a new element
		$("#renewed_" + attachmentId).val(1);
		
		//Gray out the old compliance row
		row.children(":not('new-compliance-row')").css("opacity", ".5");
		row.children(":not('new-compliance-row')").find(":text").attr("disabled", "disabled");
		row.children(":not('new-compliance-row')").find(".slider-button").addClass("disabled");
		
		var newRow = $("#dummy-row").clone();
		newRow.removeAttr("id").removeClass("compliance-row");
		newRow.css("display", "block");
		newRow.find("#completed").attr("id", "completed_" + attachmentId + "_new").attr("name", "completed_" + attachmentId + "_new");
		newRow.find("#expirationDate").attr("id", "expirationDate_" + attachmentId + "_new").attr("name", "expirationDate_" + attachmentId + "_new").addClass("selectDate");
		newRow.find("#dueDate").attr("id", "dueDate_" + attachmentId + "_new").attr("name", "dueDate_" + attachmentId + "_new").addClass("selectDate");
		newRow.find(".compliance-title").html(row.find(".compliance-title").html());

		row.append($(newRow).fadeIn());
		
		
		var completedElement = $("#completed_" + attachmentId + "_new");

		completedElement.sliderCheckbox({
			onText: 'Compliant',
			offText: 'In Progress',
			width: 90,
			offBackgroundColor: "#c6c1bc",
			onBackgroundColor: "#888"
		});
		
		completedElement.change(function(e) {
			updateDateFields($(this), attachmentId + "_new");
		});
		updateDateFields(completedElement, attachmentId + "_new");
		
		initDatePickers();
		
		$(this).fadeOut();
	});
	
}

var isValidDate = function(dateString)
{
    // First check for the pattern
    if(!/^\d{2}\/\d{2}\/\d{4}$/.test(dateString))
        return false;

    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[1], 10);
    var month = parseInt(parts[0], 10);
    var year = parseInt(parts[2], 10);

    // Check the ranges of month and year
    if(year < 1000 || year > 3000 || month == 0 || month > 12)
        return false;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};

var initComplianceSliders;
initComplianceSliders = function() {
	$("#compliance-table input[type=checkbox]").each(function (i, el) {
		var settings = {
			onText: 'Compliant',
			offText: 'Non-compliant',
			width: 90,
			offBackgroundColor: "#ed2125",
			onBackgroundColor: "#888"
		}

		if ($(el).hasClass("to-do")) {
			settings.offText = "In Progress";
			settings.offBackgroundColor = "#c6c1bc";
		}

		if ($(el).hasClass("expired")) {
			settings.disabled = true;
		}

		$(el).sliderCheckbox(settings);
		$(el).change(function () {
			updateDateFields($(this), $(this).attr("data-attachmentid"))
		});
	});
}

var initDatePickers;
initDatePickers = function() {
	$(".selectDate").datepicker().change(function(e){
		disableExpirationDatePicker($(this).parents(".compliance-section"));
	});
}

var updateDateFields;
updateDateFields = function(el, attachmentId) {
	if ($(el).is(":checked")) {
		$("#expirationDate_" + attachmentId).parents(".form-prompt").show();
		$("#dueDate_" + attachmentId).parents(".form-prompt").hide();
	} else if (!$(el).hasClass("expired")) {
		$("#expirationDate_" + attachmentId).parents(".form-prompt").hide();
		$("#dueDate_" + attachmentId).parents(".form-prompt").show();
	} else {
		$("#dueDate_" + attachmentId).parents(".form-prompt").hide();
	}

	disableExpirationDatePicker($(el).parents(".compliance-section"));
}

var getEmptyExpirationCount;
getEmptyExpirationCount = function(section) {
	var expCount = 0;
	$(section).find(".expirationDate:visible").each(function(i, el){
		if (!$(el).val()) {
			expCount++;
		}
	});
	
	return expCount;
}

var disableAllExpirationDatePickers;
disableAllExpirationDatePickers = function() {
	$(".compliance-section").each(function(i, el){
		disableExpirationDatePicker(el);
	});
}

var disableExpirationDatePicker;
disableExpirationDatePicker = function(section) {
	$(section).find(".expiration-btn").button("option", "disabled", getEmptyExpirationCount($(section)) < 2);
}
