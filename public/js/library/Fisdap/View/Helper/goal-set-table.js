$(document).ready(function() {
	initGoalsetTable();
});

function initGoalsetTable() {
	// style for safari
	if ($.browser.safari) {
		$("#add-goalset").css({"position": "relative", "top": "-10px"});
	}
	
	// set the selected row based on the hidden input
	var goalsetId = $("#selected-goalset").val();
	if (goalsetId > 0) {
		$(".goalset-table td.goalset-name").each(function() {
			if ($(this).attr("data-goalsetid") == goalsetId) {
				$(this).parent().addClass("selected");
			}
		});
	}
	
	// initialize the deletion confirmation modal
	$("#deleteGoalsetConfirmationModal").dialog({
		"autoOpen": false,
		"modal": true,
		"width": 600,
		"draggable": false,
		"resizable": false,
		"title": "Delete Custom Goalset",
		"open": function(event, ui){
			trigger = "";
		},
		"close": function( event, ui ) {
			if (trigger == "ok") {
				var goalsetId = $("#delete-goalset-confirm").attr("data-goalsetid");
				
				// block the table and add a throbber
				$("#goalset-table").prepend("<div id='goalset-table-blocker'></div>");
				$("#goalset-table").prepend("<img id='load-goalset-table-throbber' src='/images/throbber_small.gif'>");

				$.post("/reports/goal/delete-goalset",
						{"goalsetId": goalsetId},
						function(data){
							// update the goal set table
							$("#goalset-table").replaceWith(data.goalsetTable);
							initGoalsetTable();
							
							// remove the blocker and the throbber
							$("#goalset-table-blocker").remove();
							$("#load-goalset-table-throbber").remove();

							return true;
						}
				);
			}
		}
	});
	
	$("#delete-goalset-cancel").button().click(function(e){
		e.preventDefault();
		$("#deleteGoalsetConfirmationModal").dialog("close");
	});
	
	$("#delete-goalset-confirm").button().click(function(e){
		trigger = "ok";
		e.preventDefault();
		$("#deleteGoalsetConfirmationModal").dialog("close");
	});
	
	// SELECT THE GOAL SET
	$(".goalset-name:not('.not-selectable')").click(function(e){
		e.preventDefault();
		// deselect all rows
		$(".goalset-table tr").removeClass("selected");
		
		// figure out which goalset we're working with
		var goalsetId = $(this).attr("data-goalsetid");
		
		// select this row
		$(this).parent().addClass("selected");
		
		// update hidden input
		$("#selected-goalset").val(goalsetId);
		
	})
	
	// DELETE THE GOAL SET
	$(".delete-goalset a").click(function(e){
		e.preventDefault();
		var goalsetId = $(this).attr("data-goalsetid");
		var goalset_title = $(this).parent().parent().find("td").first().text();	
		
		$("#deleteGoalsetConfirmationModal").dialog("open");

		$("#deleteGoalsetConfirmationModal .goalset-name").text(goalset_title);
		$("#delete-goalset-confirm").attr("data-goalsetid", goalsetId);
	})
	
}