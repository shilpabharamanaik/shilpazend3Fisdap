$(function () {
    $('.delete-config').click(function (e) {
        blockUi(true);
        configRow = $(this).parents("tr");
        configId = $(this).attr("data-configid");
        $.post("/account/orders/delete-order-configuration", {"orderConfigurationId": configId}, function (response) {
            location.reload();
        }, "json");
    });

    $('td.quantity input[type=text]').blur(function (e) {
        configRow = $(this).parents("tr");
        configId = $(this).attr("data-configid");
        quantity = $(this).val();
        if (quantity == 0) {
            $(configRow).find('.delete-config').click();
        } else if (quantity > 0) {
            blockUi(true);
            $.post("/account/orders/update-order-configuration", {
                "orderConfigurationId": configId,
                "quantity": quantity
            }, function (response) {
                location.reload();
            }, "json");
        }
    });
});

function calculateTotal() {
    total = 0;
    $("table.order-summary tr").each(function (i, el) {
        price = parseFloat($(el).find("span.price").text());
        quantity = parseInt($(el).find("td.quantity input[type=text]").val());
        total += price * quantity;
    });
    $(".total-sum").html(total).formatCurrency();
}
