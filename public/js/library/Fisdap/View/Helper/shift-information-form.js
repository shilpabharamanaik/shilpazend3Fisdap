$(document).ready(function() {
    $(".chzn-select").css('width', '390px').chosen(); //manually setting width because chosen element might appear in a hidden container (which messes up display)
    $("#date_range-element input").datepicker();
    $(".audit-status-buttonset").buttonset();
    
    // change the audit status
	$("input[name='audit-status-filters']").change(function() {
        var value = $(this).attr('id').substring(13);
        $("#auditStatus").val(value);
    });
});