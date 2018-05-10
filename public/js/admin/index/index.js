$(function(){
    $(".contentExpand").hide();
    $(".headerExpand").find("a").click(function(e){e.stopPropagation()});
    $(".headerExpand").click(function(){
        var this_header = $(this);
        $(this).next(".contentExpand").slideToggle(300, function(){
            this_header.toggleClass("minus");
        });
        return false;
    });
});


