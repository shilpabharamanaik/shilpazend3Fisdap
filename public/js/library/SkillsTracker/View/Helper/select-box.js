$(document).ready(function() {
    enableSelectBoxes();
    $('html').click(function(e) {
        var target = e.target;
        if((!$(target).hasClass("selected")) && (!$(target).hasClass("selectArrow"))){
            close();
        }
    });
});

var closed = true;
 
function enableSelectBoxes(){
    
    $('div.selectBox').each(function(){
        $(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
        $(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));

        $(this).children('span.selected,span.selectArrow').click(function(){
            if(closed){open();}
            else{close();}
        });

        $(this).find('span.selectOption').click(function(){
            close();
            $(this).closest('div.selectBox').attr('value',$(this).attr('value'));
            if($(this).attr('id') == 'open'){
                $('span.selectArrow').html('&#9660');
            }
            else {
                $('span.selectArrow').html('<a id="goLink" href="#">Go</a>');
                $("#goLink").click(function(e){
                    window.alert("opening " + $('div.selectBox').attr('value')); 
                });
            }
            $(this).parent().siblings('span.selected').html($(this).html());
        });
    });                
};

function close() {
    closed = true;
    $(".selectBox").children('div.selectOptions').css('display','none');
}

function open() {
    closed = false;
    $(".selectBox").children('div.selectOptions').css('display','block');    
}