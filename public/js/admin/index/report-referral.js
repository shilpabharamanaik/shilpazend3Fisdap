$(function() {

    $("#searchButton").button().parent().addClass("extra-small green-buttons");
    $("#results").hide();


    $("#searchButton").click(function(e){
        runSearch(e)
        $("#results").show();
    });

    function runSearch(e) {				
        $("#results_area").slideUp();
        $("#results_area").empty();

        processSearchParams(e);
    }

    function processSearchParams(e) {
        var addressesOnly = $("#addressesOnly").prop("checked") ? 1 : 0;
        return $.post("/admin/index/get-programs-from-search",
            $('#startDate').serialize() + "&endDate=" + $("#endDate").val() + "&addressesOnly=" + addressesOnly,

            function(response){
                $("#results_area").html(response.table);
                $("#results_area").slideDown();
                $("#program-table").tablesorter();

            }, "json").done();
    }

    $("#export-referral-report-links .pdfLink").click(function(e){
        e.preventDefault();		
        createPdf($("#results_area").clone(), "referral-report", "export-referral-report-links");
    });

    $("#export-referral-report-links .csvLink").click(function(e){
        e.preventDefault();

        var tables = {};
        // add the programs table
        tables["Programs"] = $("#program-table");

        createCsv(tables, "referral-report", "export-referral-report-links");
    });    


    $( "#startDate" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
    });

    $( "#endDate" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        onClose: function( selectedDate ) {
            $( "#startDate" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

    $("#startDate").change(function(e){
        $( "#endDate").datepicker( "option", "minDate", new Date($(this).val()));
    });
});
