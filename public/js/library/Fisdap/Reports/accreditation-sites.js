$(document).ready(function() {
	
	// update the site accordion when the selected sites change
	$('#sites_filters').change(function() {
		updateAccreditationAccordion();
	});
	
});

	function updateAccreditationAccordion() {
		// set blocker and throbber
		$("#report-form").prepend("<div id='form-blocker'></div>");
		$("#form-blocker").css("height", $("#report-form").height()-20);
		$("#report-form").prepend("<img id='update-table-throbber' src='/images/throbber_small.gif'>");
		
		sites_filters = $('#sites_filters').val();
		if (sites_filters === null) {
			sites_filters = ['0-Clinical', '0-Field'];
		}
		
		$.post("/reports/index/update-accreditation-accordion",
				{ 'sites_filters' : sites_filters },
				function(resp) {
					// destroy the old modal before initializing the new stuff
					$("#edit-modal-container").dialog ('destroy').remove();
					$(".accreditation-accordion").replaceWith(resp);
					initAccreditationAccordion();
					
					// remove the blocker and the throbber
					$("#form-blocker").remove();
					$("#update-table-throbber").remove();

					return true;
				}
			);
	}
	