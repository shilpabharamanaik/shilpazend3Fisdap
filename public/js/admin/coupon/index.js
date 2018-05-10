$(function() {
    
    $("#searchButton").button().parent().addClass("extra-small green-buttons");
	$("a#newLink").button().parent();
	$("a#allLink").button().parent();
    $("#program-table").tablesorter();

    
    $("#searchButton").click(function(e){
		e.preventDefault();
		$.post("/admin/coupon/get-coupon-table", {"start" : $("#startDate").val(), "end" : $("#endDate").val()},
		
		function(response){
			$("#results_area").empty().html($(response).fadeIn());
			$("#program-table").tablesorter();
		}, "json")
		
    });
    
   $( "#startDate" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
    });
    
    $( "#endDate" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function( selectedDate ) {
        $( "#startDate" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
	$("#startDate").change(function(e){
		 $( "#endDate").datepicker( "option", "minDate", new Date($(this).val()));
	  });
  });