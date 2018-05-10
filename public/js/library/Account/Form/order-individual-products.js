$(function(){
    $(".ui-button-text").css("line-height", "1");
    $("select.chzn-select").chosen();

    // since we're dealing with a hidden form value that will trigger invalid data, hide it's name
    $("ul.form-errors").find('b').hide();
    // funky fix for form decorator
    $("label").each(function(){if($(this).attr('for') == "save"){$(this).hide();}});
    
    var currentConfig = parseInt($("#orderConfig").val());
    // set the total cost to whatever our orderCost value is
    var currentPrice = parseFloat($("#orderCost").val());
    // display the price
    $("#totalCost").text(formatPrice(currentPrice));

    // add decimal, make it a string
    function formatPrice(cost){
        return cost.toFixed(2).toString();
    }
    
    // prices and configurations are found within hidden inputs on the form
    // this function finds the appropriate hidden value
    function findValue(value, checkbox){
        // split the checkbox name after 'product' the remaining value with give us a
        // number that we can combine with 'price' or 'config' to find the hidden input
        // with the value we are looking for
        var name = checkbox.attr('id').split('product');
        var valueName = value + name[1];
        var returnVal = 0;

        $("input:hidden").each(function(){
           if($(this).attr('id') == valueName){
                returnVal = $(this).val();
           }
        });
        return returnVal;
    }
    
    $("input:checkbox").each(function(){
       $(this).change(function(){
            // get the price/configuration
            var price = parseFloat(findValue('price', $(this)));
            var config = parseInt(findValue('config', $(this)));
            
            // update our current price/configuration based on the checkbox's state
            if($(this).is(':checked')){
                currentPrice += price;
                currentConfig += config;
            }
            else {
                currentPrice -= price;
                currentConfig -= config;
            }
            
            // reprint/assign the totals
            $("#totalCost").text(formatPrice(currentPrice));
            $('#orderConfig').val(currentConfig.toString());
            $('#orderCost').val(currentPrice.toString());
       });
    });
});
