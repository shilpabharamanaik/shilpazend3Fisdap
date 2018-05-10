$(function(){
    //$("#instructors-2").parents("tr").find("input:not(input[name=account_type])").attr('disabled', 'disabled');
    
    $("input[name=account_type]").change(disableElements);
    $("#next-link").click(function(e){
        e.preventDefault();
        $("#accountHoldersForm").submit();
    });
    disableElements();
});

function disableElements()
{
    var accountType = $("input[name=account_type]:checked").val();
    $("input:disabled, select:disabled").removeAttr('disabled');

    if (accountType == '1') {
        $("#account_type-2").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#account_type-3").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#next-link").html("Next (Products - Packages) >>");
    } else if (accountType == '2') {
        $("#account_type-1").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#account_type-3").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#next-link").html("Next (Order Summary) >>");
    } else {
        $("#account_type-1").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#account_type-2").parents("tr").find("input:not(input[name=account_type]), select").attr('disabled', 'disabled');
        $("#next-link").html("Next (Order Summary) >>");
    }
}