$(function(){
	$("#pick-sites").click(function(event) {
    	event.preventDefault();
		var trigger = $(this);
		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
		
		var mode = 'edit';
		
		$.post("/scheduler/compliance/generate-site-picker",
			   {"site_ids": trigger.attr("data-siteids"),
			    "requirement": "REQUIREMENT NAME"},
			   function(resp) {
					$("#pick-sites-modal").html($(resp).html());
					initPickSitesModal();
					$("#pick-sites-modal").dialog("open");
					$("#load-modal-throbber").remove();
					trigger.css("opacity", "1");
				});
		});
});
