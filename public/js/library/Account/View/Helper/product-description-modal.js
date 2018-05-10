$(function () {

    $("#productDescriptionsDialog").dialog({
        modal: true,
        autoOpen: false,
        resizable: false,
        width: 725,
        height: 400,
        title: "Product Descriptions",
        buttons: [
            {
                text: "Ok",
                className: "gray-button",
                click: function () {
                    $(this).dialog("close");
                }
            }
        ]
    });

    $("#productDescriptionsLink").click(function (e) {
        e.preventDefault();
        $("#productDescriptionsDialog").dialog('open');
    });

});