$(function(){
	$("#state").chosen();
	
	$("#site-type-filter").buttonset();

	// filter the site list on search
    $("#site_search").fieldtag().keyup(function(){
		// refilter, then search
		filterByType();
        searchList($(this).val().toLowerCase());
    });

	// filter by state
	$("#state").change(function(){
		var filterState = $("#state_chzn .chzn-results .result-selected").attr("data-option-val");
		
		// clear out list and add throbber
		$("#sites-table").remove();
		$("#filterMessage").remove();
		$("#site-list").prepend("<img id='filterThrobber' src='/images/throbber_small.gif'>");
		
		// reset filters
		disableButtons();
		$("#all-sites").click();
		$("#site_search").val("").fieldtag();
		
		$.post("/account/sites/filter-sites",
            { state: filterState },
            function(response) {
				// add the new sites
				$("#filterThrobber").remove();
                $('#site-list').append(response);
				setListStyles();
            });
	});
	
	// filter by site type
	$("#site-type-filter").change(function(){
		// filter, then re-search
        filterByType(); 
        searchList($("#site_search").val().toLowerCase());
	});
	
	// filter the list by site type
	function filterByType() {
		var filterType = $("#site-type-filter").find(".ui-state-active").attr("for").split("-");
        filterType = filterType[0];
		
		$("#sites-table tbody tr").each(function() {
			var type = $(this).attr('class');
			if (filterType == 'all') {
				$(this).show();
			} else if (type.indexOf(filterType) == -1) {
				$(this).hide();
				if ($(this).hasClass('selectedRow')) {
					$(this).removeClass('selectedRow');
					disableButtons();
				}
			} else {
				$(this).show();
			}
		});
	}
	
	// search the site list
    function searchList(search_term) {
		// only search if there IS a search term and the input isn't displaying the fieldtag phrase
        if (search_term != "" && !$("#site_search").hasClass('tagged')) {
            $("#sites-table tbody tr").each(function(){
                var name = $(this).find(".siteName").text().toLowerCase();
                var city = $(this).find(".siteCity").text().toLowerCase();
				if (typeof($(this).attr("data-sitecontact")) == "string") {
					var contact = $(this).attr("data-sitecontact").toLowerCase();
				} else {
					var contact = "";
				}
				if (typeof($(this).attr("data-siteaddress")) == "string") {
					var address = $(this).attr("data-siteaddress").toLowerCase();
				} else {
					var address = "";
				}
                
				if (name.indexOf(search_term) == -1 && city.indexOf(search_term) == -1 &&
					contact.indexOf(search_term) == -1 && address.indexOf(search_term) == -1) {
                    $(this).hide();
					
					// if the one that was selected is not part of the search, de-select
					if ($(this).hasClass('selectedRow')) {
						$(this).removeClass('selectedRow');
						disableButtons();
					}
                }
            });
        }
    }

});

function resetRows() {
	// make no one have the selected class
	$('tr').removeClass('selectedRow');
}

