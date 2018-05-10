$(function(){
    $(".new-order").click(function(e) {
        e.preventDefault();
        loc = $(this).attr('href');
        $.post("/account/orders/clear-session-orders", {},
               function(response){
                window.location = loc;
                return true;
        }, "json");
    });
});