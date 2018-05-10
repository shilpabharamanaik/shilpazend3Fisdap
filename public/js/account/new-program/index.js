$(function(){
    $("#go-btn").click(function(){
        $(this).parents("form").submit();
    });
});