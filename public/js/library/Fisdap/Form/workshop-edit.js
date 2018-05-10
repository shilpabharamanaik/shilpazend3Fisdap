$(function() {
    $( "#deadline" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
    });
    $( "#date" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#deadline" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
    $("#deadline").change(function(e){
	 $( "#date").datepicker( "option", "minDate", new Date($(this).val()));
	  });
  });