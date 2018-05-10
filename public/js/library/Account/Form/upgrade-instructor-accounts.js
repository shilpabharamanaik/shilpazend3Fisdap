$(document).ready(function() {
    //Add onChange event to all of the product checkboxes
    $("input.product").change(function(e){
        updateSummary();
    });


    //Make the table heading fixed by using floatThead jQuery plugin
    var $product_table = $('#instructors').find("table.fisdap-table");
    $product_table.floatThead({
        scrollContainer: function($table){
            return $table.closest('#instructors');
        }
    });
	
	function updateSummary(){
        var totalCost = 0;
        var numAccounts = [];

        //Loop over all the checked products and add them to an array and sum the cost
        $("input.product:checked").each(function(i, el){
            totalCost += parseFloat($(el).attr('data-price'));
            if( typeof numAccounts[$(el).attr('data-productid')] == "undefined" ) {
                numAccounts[$(el).attr('data-productid')] = 1;
            } else {
                numAccounts[$(el).attr('data-productid')]++;
            }
        });

        //Zero out the number of accounts before adding the new values
        $('.num_accounts').text(0);

        //Loop over the possible products and add the sum of accounts
        numAccounts.forEach(function(count, product_id) {
            $('#num_accounts_' + product_id).text(count);
        });

        //Add the total cost to the DOM
        $("#totalCost").text(totalCost.toFixed(2));
	}
	
	
	$("#next-link").click(function(e){
		e.preventDefault();
		if($("input.product:checked").length == 0){
			$("#noneSelectedError").show();
		}
		else {
			$("#noneSelectedError").hide();
			$("form").submit();
		}
	});
	
});