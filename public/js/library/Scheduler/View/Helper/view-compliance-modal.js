$(function(){
    
    // turn the history modal div into a modal
    $("#viewComplianceDialog").dialog({
	modal: true,
	autoOpen: false,
	resizable: false,
	width: 800,
	title: "Shift Compliance Status",
	open: function (){
	    $("#close-btn").blur();
		$("#about").find(".grid_4").first().addClass("grid_3").removeClass("grid_4");
        }
    });
   
});

function initViewComplianceModal(assignment_id) {
    $("#close-btn").button();
	$("#edit-status-btn").button();
	$("#status-filter").buttonset();
        $(".cupertino .ui-button").css('margin', '5px -3px');

	// filter the accordion
        $("#status-filter .ui-button").click(function() {

                var table = $("#attachmentsTable");
		table.css({"opacity": "0.5", "cursor": "default"}).before("<img src='/images/throbber_small.gif' id='filter-table-throbber'>");

                // get the new data
                var value = $(this).attr('for');
                $.post(
                    '/portfolio/index/filter-requirements-table',
                    { 'value': value,
		      'type': 'table',
		      'assignment_id': assignment_id },
                    function (response) {
                                table.html(response);
                                table.css("opacity", "1");
                                $("#filter-table-throbber").remove();
                        })
        });

 
    $("#close-btn").click(function(e){
		e.preventDefault();
		$("#viewComplianceDialog").dialog('close');
    });
}
   
