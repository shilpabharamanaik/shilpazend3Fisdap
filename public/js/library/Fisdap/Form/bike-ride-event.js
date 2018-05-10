$(function() {
    $( "#start" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
    });
    $( "#end" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#start" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
	$("#start").change(function(e){
		 $( "#end").datepicker( "option", "minDate", new Date($(this).val()));
	  });
  });