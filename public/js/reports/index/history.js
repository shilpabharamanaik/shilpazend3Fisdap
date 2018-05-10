$(document).ready(function() {
	// filter the report list on search
    $("#report_search").fieldtag().keyup(function(){
		filterReports();
    });

    // Are there any pending reports on this page that need status updates?
    var reportsPendingElems = $('a.run-report.report-pending');
    if (reportsPendingElems.length > 0) {
        // collect the config IDs of these pending reports
        var pendingIds = []
        reportsPendingElems.each(function(i, elem) {
            pendingIds.push($(elem).attr('data-config-id'));
        });

        // start polling the server to see when report(s) are ready
        (function poll() {
            setTimeout(function () {
                $.ajax({
                    url: "/reports/index/check-cached-reports",
                    type: "POST",
                    dataType: "json",
                    data: { config_ids : pendingIds },
                    success: function (data) {
                        // change throbbers into links for any ready Reports
                        $.each(data.reportsReady, function(i, configId) {
                            $("a.run-report.report-pending[data-config-id='" + configId + "']")
                                .removeClass('report-pending')
                                .html('View results');
                        });

                        // change throbbers into errors messages for any errored out reports
                        $.each(data.reportsError, function(i, configId) {
                            $("a.run-report.report-pending[data-config-id='" + configId + "']")
                                .removeClass('report-pending')
                                .html('Error');
                        });

                        if (data.waiting) {
                            //Setup the next poll recursively
                            poll();
                        }
                    }
                });
            }, 5000);
        })();
    }
});

function filterReports() {
	var search_term = $("#report_search").val().trim();
	
	// show everything
	$(".report-row").show();
	$(".null-search").remove();	
		
	// then go through and hide the ones that don't match
	$(".report-row").each(function(){
        if (search_term != "" && !$("#report_search").hasClass('tagged')) {
			var title = $(this).find(".report-title").text().toLowerCase();
			var description = $(this).find(".report-categories").text().toLowerCase();
			if (title.indexOf(search_term.toLowerCase()) == -1 && description.indexOf(search_term.toLowerCase()) == -1) {
				$(this).hide();
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
		
		$("#reports-container").prepend("<div class='notice null-search'>No results found"+keywordPhrase+". Please try another search.</div>").fadeIn();
	}
}

