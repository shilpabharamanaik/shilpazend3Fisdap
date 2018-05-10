$(function(){
    //Hide package options by default
    //$(".package-options").hide();
    
    //If one is checked to start, display the corresponding options
    //$("input[name=packages]:checked").parents("tr").find(".package-options").show();
    
    //Color the select package, if one exists
    colorPackageRow();
    
    //Attach calculate packge ajax function to the package selection
    //$("input[type=radio]").change(function() {
    //    calculatePackagePrice($(this).parents("tr"))
    //});
    
    //Fire off price calculation if there's a package already selected
    //if ($("input[name=packages]:checked").length) {
    //    calculatePackagePrice($("input[name=packages]:checked").parents("tr"));
    //}
    
    //Add show/hide toggle to reveal additional options for the selected package
    $("input[name=packages]").change(function(){
        $(".package-options:visible").hide("1000");
        $(this).parents("tr").find(".package-options").show("1000");
        colorPackageRow();
    });
    
    //Make the 'unselect package' link zero out the form
    $(".unselect").click(function(e) {
        e.preventDefault();
        $("input[type=hidden]").val("");
        $("input[name=packages]:checked").attr('checked', false);
        $(".package-options").hide();
        //$("td.cost").html("--");
    });
    
    $("#next-link").click(function(e){
        e.preventDefault();
        $("#packagesForm").submit();
    });
});

function colorPackageRow()
{
    $("table tr").css("background", "#FFFFFF");
    $("input[name=packages]:checked").parents("tr").css("background", "#e6efc2");
}

function calculatePackagePrice(subform)
{
    data = {
        "packageId" : subform.find("input[name=packages]:checked").val(),
        "limited" : subform.find(".limited:checked").val(),
        "certification" : subform.find(".certification:checked").val(),
    }
    
    $.post("/account/orders/calculate-package-price", data,
        function(response) {
            //$("td.cost").html("--");
            subform.find(".cost").html($("<span>$" + response.price + "</span>").fadeIn(1000));
            $("input#configuration").val(response.configuration);
            $("input#packageId").val(data.packageId);
        }, "json");
}