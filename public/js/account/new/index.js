$(function(){
    baseOptions = {
        activation: 'click',
        local:true, 
        cursor: 'pointer',
        width: 650,
        cluetipClass: 'jtip',
        sticky: true,
        closePosition: 'title',
        closeText: '<img width="25" height="25" src="/images/icons/delete.png" alt="close" />'
    }
    $('#codehelp').cluetip(baseOptions);
    
    $("#continue").click(function(e){
        e.preventDefault();
        $(this).parents('form').submit();
    });

    $(".green-buttons a").button();
});