$(function(){
	// make some changes from the base filter set
	$("#state-filter").hide();
	$("#site_search").css("width", "525px");
	
	$('a#editMySiteButton').button();
	$('a#add-new-site').button();
	$('a#internalPercepMergeButton').button();
	disableButtons();

	$('a#editMySiteButton').click(function(event) {
		if ($(this).css("opacity") != 1) {
			event.preventDefault();
		} else {
			//check for IE
			if(navigator.appVersion.indexOf("MSIE")!=-1){
				window.location($(this).attr("href"));
			}
		}
	});
	
	$('a#internalPercepMergeButton').click(function(event) {
		//Don't do anything if the button is disabled
		if ($(this).attr('aria-disabled') != "false") {
            return false;
        }
		
		return true;
	});

	// click functions the same for all rows
	$('#site-list').on('click', 'tr', function() {
		
		// if this is the title row, do nothing
		if ($(this).attr("id") == "titles") {
			return false;
		}
	
		resetRows();
		
		// now make the row we just clicked the selected one
		if($(this).hasClass('lab')){
			var type = "Lab";
		} else if($(this).hasClass('clinical')){
			var type = "Clinical";
		} else {
			var type = "Field";
		}
		
		// add the selected class so we can identify this row later
		$(this).addClass('selectedRow');
		
		var selectedOne = $(this).attr('data-siteId');
		
		$('a#editMySiteButton').button('enable');
		$('a#editMySiteButton').each(function () {
			var href = "/account/sites/edit/siteId";
			$(this).attr('href', href + "/" + selectedOne);
		});
		
		$('a#internalPercepMergeButton').button('enable');
		$('a#internalPercepMergeButton').each(function () {
			var href = $(this).attr('href');
            var hrefArray = href.split('siteId');
            var finalHref = "" + hrefArray[0] + "siteId=" + selectedOne;
            $(this).attr('href', finalHref);
		});
		
		return true;
	});

});

function disableButtons(){
	$('#editMySiteButton').button('disable');
    $('#internalPercepMergeButton').button('disable');
}
	
