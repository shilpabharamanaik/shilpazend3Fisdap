$(document).ready(function() {
    initReportsTable();
	
	//Make it so toggling the active/inactive switch brings up a warning about what they're about to do
	$(".report-row input.active-toggle").change(function(e) {
		var pieces = $(this).prop("id").split("_");
		report_id = pieces[1];
		if ($(this).attr("checked") == "checked") {
			var active = 1;
		} else {
			var active = 0;
		}
			
		$.post("/reports/settings/toggle-report", {"report_id" : report_id, "active" : active },
			function(response) {
				//console.log(response);
			}
		);
		
	});
	
	// filter the report list on search
    $("#report_search").fieldtag().keyup(function(){
        var search_term = $(this).val().trim();
		$(".null-search").remove();
		
		// only search if there IS a search term and the input isn't displaying the fieldtag phrase
        if (search_term != "" && !$("#report_search").hasClass('tagged')) {
            $(".report-row").each(function(){
                var title = $(this).find(".report-title").text().toLowerCase();
                
				if (title.indexOf(search_term.toLowerCase()) == -1) {
                    $(this).hide();
                } else {
					$(this).show();
				}
            });
        } else {
			$(".report-row").show();
		}
		
		// if there are no results, tell the user
		if ($("#reports-container").find(".report-row:visible").length == 0) {
			$("#reports-container").prepend("<div class='notice null-search'>No results  for \""+search_term+"\". Please try another search.</div>").fadeIn();
		}
    });

});

function initReportsTable() {
	$(".active-toggle").each(function(i, el) {
		$(el).sliderCheckbox({onText: "Active", offText: "Inactive", "width": 50});
	});
}

