$(function(){
    //Keep all Limtied fields in sync
    $(".limited").change(function(){
        $("input:radio[class=limited][value='" + $(this).val() + "']").click();
        getProductPrice($(this).parents("tr"));
    });
    
    $("a#next-link").click(function(e){
        e.preventDefault();
        $("#productsForm").submit();
    });
    
    $("a#all-products-shortcut").click(function(e){
        e.preventDefault();
        $("form input[type='checkbox']").attr('checked', true);
        $("#productsForm").submit();
    });
});

function getProductPrice(row)
{
    configuration = row.find("input[type=checkbox]").val();
    limited = row.find("input:radio[class=limited]:checked").val();
    $.post("/account/orders/calculate-product-price", {"configuration" : configuration, "limited" : limited}, function(response) {
        row.find("td.cost").html(response);
    }, "json");
}