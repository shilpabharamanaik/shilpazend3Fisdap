$(function() {
	// detailed shift report now includes preceptor signoffs, so we need to calculate scores on them.
	//Get scores of preceptor ratings and add them together
	calcScores = function(form) {
		var formKey = $('input[name="formKey"]', form).val();		
		
		var score = 0;
		var possible = 0;
		var percentage = 0;
		
		for (i=1; i <= 7; i++) {
			var value = $('input[name="Student_' + i + '_' + formKey + '[rating]"]:checked', form).val();
			var disabledToggle = $('input[name="Student_' + i + '_' + formKey + '[disabled]"]', form).is(':checked');
			
			if (!disabledToggle) {
				if (value) {
					score += parseInt(value);
				}
				possible += 2;
			}
		}
		
		for (i=1; i <= 7; i++) {
			var value = $('input[name="Preceptor_' + i + '_' + formKey + '[rating]"]:checked', form).val();
			var disabledToggle = $('input[name="Preceptor_' + i + '_' + formKey + '[disabled]"]', form).is(':checked');
			
			if (!disabledToggle) {
				if (value) {
					score += parseInt(value);
				}
				possible += 2;
			}
		}
		
		$(".rating-possible", form).text(possible);
		$(".rating-score", form).text(score);
		if (possible == 0) {
			$(".rating-percentage", form).text("N/A");
		} else {
			percentage = score/possible * 100;
			$(".rating-percentage", form).text(percentage.toFixed(0));
		}
	}
		
	$('form.signoffForm').each(function(i, form) {
		//calculate scores when this page loads
		calcScores(form);
	});
	
	$(".lock-shift-btn").button().click(function(event) {
		event.preventDefault();
		$(".lockShiftDialog").dialog("open");
	});
	
	$("#edit-shift-link").click(function(event) {
		event.preventDefault();
		initShiftModal();
		$("#shiftDialog").dialog("open");
	});
	
	$("#export-shift-details-links .pdfLink").click(function(event) {
		event.preventDefault();
		
		// Clone the desired content so it can be submitted to the PDF generator
		divContents = $('#pdfContents').clone();
		
		// make modifications to the markup to improve PDF output
		divContents.find('.no-pdf').remove();
		
		// unhide the preceptor signoff documents
		divContents.find('.detailed-signoff-hidden').css('display', 'block');

		// Remove anything with a class that defines height=100%...
		divContents.find('.eval-sidebar').removeClass('eval-sidebar');
		divContents.find('.detail-sidebar').removeClass('detail-sidebar');

		return createPdf(divContents, "detailed-shift-report", "export-shift-details-links");
	});

    // make the icon image an svg so we can manipulate it with css
    imgToSVG('.lock-shift-btn img.icon');

});
