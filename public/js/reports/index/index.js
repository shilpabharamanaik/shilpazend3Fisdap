$(document).ready(function() {
	$("#category").chosen().change(function(){
		filterReports();
	});
	
	// filter the report list on search
    $("#report_search").fieldtag().keyup(function(){
		filterReports();
    });
});

function filterReports() {
	var category = $("#category").val().toString();
	var search_term = $("#report_search").val().trim();
	
	// show everything
	$(".report-row").show();
	$(".null-search").remove();	
		
	// then go through and hide the ones that don't match
	$(".report-row").each(function(){
        var categories = $(this).find(".report-categories").attr("data-categoryids").split(",");
		if (category != "all" && $.inArray(category, categories) < 0) {
			$(this).hide();
		} else {
			if (search_term != "" && !$("#report_search").hasClass('tagged')) {
				var title = $(this).find(".report-title").text().toLowerCase();
				if (title.indexOf(search_term.toLowerCase()) == -1) {
					$(this).hide();
				}
			}
		}
    });
	
	// if there are no results, tell the user
	if ($("#reports-container").find(".report-row:visible").length == 0) {
		if (search_term != "" && !$("#report_search").hasClass('tagged')) {
			var keywordPhrase = ' for "'+search_term+'"';
		} else {
			var keywordPhrase = '';
		}
		
		if (category != "all") {
			var categoryPhrase = ' in '+$('#category option:selected').text();
		} else {
			var categoryPhrase = ' in all categories';
		}
		$("#reports-container").prepend("<div class='notice null-search'>No results found"+keywordPhrase+categoryPhrase+". Please try another search.</div>").fadeIn();
	}
}

