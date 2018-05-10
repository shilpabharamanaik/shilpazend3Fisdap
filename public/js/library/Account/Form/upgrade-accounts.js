$(function(){
    $("a#next-link").css("float", "right").click(function(e){
        e.preventDefault();
        $("form").submit();
    });
    
    // for downgradable products, only allow one checkbox to be checked in a given student picker TD
    // Using .delegate() because this HTML is not in the DOM when upgrade-accounts.js is parsed
    $(document).delegate("div.productInnerCell.downgradable input", "change", function(e) {
        if ($(this).is(':checked')) {
            $(this).closest('div.productInnerCell.downgradable').find('input').not($(this)).attr('checked', false);
        }
    });
});