$(function(){
	
	$('a#editButton').button();
	disableButtons();
	
	$('a#editButton').click(function(event) {
		event.preventDefault();
		
		// do some throbber-y stuff
		$(this).parent().append("<img id='addSiteThrobber' src='/images/throbber_small.gif'>");
		$(this).css("opacity", "0");
		
		var siteId = $(this).attr('data-siteId');
		$.post("/account/sites/add-existing-site",
            { site_id: siteId },
            function (response) {
				if (response) {
					// reroute to edit site page
					window.location = "/account/sites/edit/siteId/" + siteId;
				} else {
					// reroute to program sites page
					window.location = "/account/sites";
				}
            });
    });
	
	// click functions the same for all rows
	$('#site-list').on('click', 'tr', function() {
		
		// if this is the title row, do nothing
		if ($(this).attr("id") == "titles") {
			return false;
		}
		
		// tell the add button who it's looking at
		var selectedOne = $(this).attr('data-siteId');
		$('a#editButton').each(function () {
			$(this).attr('data-siteId', selectedOne);
		});
		
		resetRows();
		
		// now make the row we just clicked the selected one
		if($(this).hasClass('lab')){
			var type = "Lab";
			var siteType = "lab";
		} else if($(this).hasClass('clinical')){
			var type = "Clinical";
			var siteType = "clinical";
		} else {
			var type = "Field";
			var siteType = "field";
		}
		
		// display some info about the selected site	
		var address = $(this).attr('data-siteaddress');
		var contact = $(this).attr('data-sitecontact');
		var title = $(this).attr('data-sitetitle');
		if (contact && title) { contact += ", " + title; }
		showInfo("address", address, siteType);
		showInfo("contact", contact, siteType);
		
		// add the selected class so we can identify this row later
		$(this).addClass('selectedRow');
		
		$('a#editButton').button('enable');
		$('a#editButton').text("Add " + type + " Site");
		return true;
	});
	
	function showInfo(type, value, siteType) {
		if (value) {
			//$("#site-info").attr("class", siteType);
			if ($("#"+type+"-info").is(":visible")) {
				$("#"+type).hide();
				$("#"+type).text(value);
				$("#"+type).fadeIn();
			} else {
				$("#"+type).text(value);
				$("#"+type+"-info").fadeIn();
			}
		} else {
			$("#"+type+"-info").fadeOut();
		}
	}	
});

function disableButtons() {
	$('#editButton').button('disable').text('Add').attr('data-siteId', '');
	$("#address-info").fadeOut(300);
	$("#contact-info").fadeOut(300);
}
	
