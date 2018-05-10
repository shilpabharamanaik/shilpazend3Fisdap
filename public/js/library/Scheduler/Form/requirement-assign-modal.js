
$(function(){
	
	$( "#assign-modal" ).dialog({ modal: true,
								autoOpen: false,
								title: "Assign Requirement",
								open: function(){
									$("#asterisks-msg").hide();
									$("#asterisks-msg-details").hide();
	
									$('#asterisks-msg-details-trigger').unbind().click(function(e){
										e.preventDefault();
										var details = $("#asterisks-msg-details");
										if (details.css("display") == "block") {details.slideUp();}
										else {details.slideDown();}
									});
									
								},
								width: 740});
});



var openModalViaManage = function(trigger, checkedReqs)
{
	trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-assign-modal-throbber'>");

	$.post("/scheduler/compliance/generate-requirement-assign-modal", {"requirement_ids" : checkedReqs},
	function(resp) {
		$("#load-assign-modal-throbber").remove();
		trigger.css("opacity", "1");
		
		$("#assign-modal").empty().html($(resp).html());
		
		var number_of_req = checkedReqs.length;
		
		if (number_of_req == 1) {
			$("#assign-modal").prepend("<h3 class='section-header'>" + $("#edit_" + checkedReqs[0]).parent().find(".requirement-title").text() + "</h3><br />");
		}
		else {
			$("#assign-modal").prepend("<h3 style='margin-top:0.25em;' class='section-header'>" + number_of_req + " Requirements Selected</h3><a id='show-reqs-n'>see list</a><br /><div id='req-list-n' style='display: none;margin-top:0em;'></div>");
			var req_names = [];
			$.each(checkedReqs, function(i, v){req_names.push( $("#edit_" + v).parent().find(".requirement-title").text())});
			$("#req-list-n").append(req_names.join(" | "));
		}
		
		$("#show-reqs-n").click(function(e){
			e.preventDefault();
			if ($("#req-list-n").css("display") == "none") {$("#req-list-n").slideDown();}
			else {$("#req-list-n").slideUp();}
		});
		
		initFlippyDivs();
		if($("#accountType_flippy").text() == "instructors"){$("#accountType_flippy").trigger("click");}
		initRequirementAssignPicklist();
		
		$("#assign-modal").find(".picklist-ms-picker").find(".grid_2").css("width", "14.3%");
		
		$("#assign-modal").find(".selectDate").datepicker({minDate: new Date()}).keydown(function() {return false;});
		$("#assign-modal").dialog("open");
		$("#picklist-fancy-filter_filters-title").blur();
		$(".close-assign-modal, .save-assign-modal").button().blur();
		$("#assign-modal").find(".save-assign-modal").find(".ui-button-text").text("Save");
		
		$(".close-assign-modal").click(function(e){
			e.preventDefault();
			$(this).unbind();
			$("#assign-modal").dialog('close');
		});
		
		
		$("#assign-modal").find(".save-assign-modal").click(function(e){
			e.preventDefault();
			$(this).css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='save-assign-modal-throbber'>");
			var selected_userContextIds = [];
			
			$("#assign-modal").find(".chosen-list").find("option").each(function(){
				selected_userContextIds.push($(this).attr("value"));
			});
			
			if (selected_userContextIds.length > 0) {
				$.post("/scheduler/compliance/save-requirement-assign-modal", {"requirement_ids" : checkedReqs, "userContextIds" : selected_userContextIds, "due_date" : $("#due_date").val()},
				function(resp) {
					location.reload();
				});	
			}
			else {
				$("#assign-modal").dialog('close');
			}
		});
	});
}