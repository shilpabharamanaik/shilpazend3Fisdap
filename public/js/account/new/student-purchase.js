$(function () {
    var currentCost = parseInt($("#totalCost").val());
    var productCount = 0;

    $(".delete").each(function () {
        productCount++;

        // when the 'X' is clicked to delete a product
        $(this).click(function () {
            blockUi(true);
            var theRow = $(this).parent();
            var proPrice = parseInt(theRow.children(".proCost").children(".price").text());
            var configId = $("#orderSum").attr("data-configid");
            var productConfig = theRow.attr("id");
            $.post("/account/new/update-order-configuration", {
                "orderConfigurationId": configId,
                "productConfiguration": productConfig,
                "productCost": proPrice
            }, function (response) {
                if (response === true) {
                    location.reload();
                } else {
                    blockUi(false);
                }
            }, "json");
        });
    });

    if (productCount == 0) {
        $("#noProductsMessage").show();
        $("#productTable").hide();
    }

    function updatePrice(changeVal, op) {
        if (op == 'add') {
            currentCost += changeVal;
        }
        else {
            currentCost -= changeVal;
        }
    }

    // add decimal, make it a string
    function formatPrice(cost) {
        return cost.toFixed(2).toString();
    }

    function disablePlaceOrder() {
        $( "#orderButton" ).button("disable");
    }
});