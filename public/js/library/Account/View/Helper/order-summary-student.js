$(function(){
    var currentCost = parseInt($("#cost").text());
    $("#cost").text(formatPrice(currentCost));
    
    // add decimal, make it a string
    function formatPrice(cost){
        return cost.toFixed(2).toString();
    }
});