$(function(){
	// Start by hiding the div...
	$('#advanced-settings, #toggle-indicator-off').hide();
	
	// Set up click handlers to toggle the indicators and the advanced settings
	$('#toggle-indicator-off, #toggle-indicator-on, #toggle-header').click(function(event){
		event.preventDefault();
		$('#toggle-indicator-off, #toggle-indicator-on, #advanced-settings').toggle();
	});
	
	// Set up the from-to date pickers.  Pretty much stolen wholesale from the
	// jQueryUI documentation at http://jqueryui.com/demos/datepicker/#date-range
	var dates = $('#advanced-startdate, #advanced-enddate').datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		changeYear: true,
		numberOfMonths: 1,
		autoresize: true,
		onSelect: function( selectedDate ) {
			var option = this.id == "advanced-startdate" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
});