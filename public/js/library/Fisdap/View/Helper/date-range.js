$(function() {
	// turn the start dates into datepickers
    $(".selectStartDate").each(function() {
		var end = $(this).parent().find(".selectEndDate");
		$(this).datepicker({
			onClose: function( selectedDate ) {
				$(end).datepicker( "option", "minDate", selectedDate );
			}
		}).datepicker( "option", "maxDate",  $(end).val() );
	});	
	
	// turn the end dates into datepickers
    $(".selectEndDate").each(function() {
		var start = $(this).parent().find(".selectStartDate");
		$(this).datepicker({
			onClose: function( selectedDate ) {
				$( start ).datepicker( "option", "maxDate", selectedDate );
			}
		}).datepicker( "option", "minDate", $(start).val() );
	});

});