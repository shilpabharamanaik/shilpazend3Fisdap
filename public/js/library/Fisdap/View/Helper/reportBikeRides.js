$(function() { 
    $(".togglePaidButton").button().click(function(e){ 
        e.preventDefault(); 
        var paid = ($(this).text() != "Paid") ? 1 : 0; 
        var btn = $(this); 
         
        $.post("/bike-ride/change-paid-status/", 
            {"paid":paid, "riderid":$(this).attr("data-riderid")}, 
            function(response){ 
            if (paid == 1){ 
                $(btn).button("option", "label", "Paid"); 
            } 
            else{ 
                $(btn).button("option", "label", "Not Paid"); 
                } 
            }, 
            "json" 
           ); 
    }); 
    $(".moreInfoButton").button().click(function(e){ 
        e.preventDefault(); 
        $("#" +  $(this).attr("data-riderid")).dialog(); 
    }) 
     
})