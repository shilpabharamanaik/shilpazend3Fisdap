$(function(){
	// set up the page
	$('#edit-status-btn').button();
	$('#launch-site-list').button();
	$('#send-req-email').button();
	
	$("#status-filter").buttonset();
	$(".cupertino .ui-button").css('margin', '5px -3px');
	initAccordion();


	// filter the accordion
	$("#status-filter .ui-button").click(function() {

		//disengage the accorion
		var accordion = $("#attachmentsAccordion");
		disengageAccordion();

		// get the new data
		var value = $(this).attr('for');
		$.post(
			'/portfolio/index/filter-requirements-table',
			{ 'value': value,
			'type': 'accordion' },
			function (response) {
			accordion.empty().html(response);
			initAccordion();
			accordion.css("opacity", "1");
			$("#filter-table-throbber").remove();
			})
		});

	// accordion functionality
	function initAccordion() {

		var currentlyOpen;

		$(".accordionHeader").each(function(){
			$(this).click(function(){
				if (currentlyOpen) {
					closeCurrent();

					// it's not us!
					if(currentlyOpen.text() != $(this).text()){
						openCategory($(this));
					}
					else {
						// we've closed ourself, set currently open to null
						currentlyOpen = null;
					}
				}
				else {
					openCategory($(this));
				}
			})
		});

		$("#history-modal-container").dialog({
			modal: true,
			autoOpen: false,
			resizable: false,
			width: 700,
			title: "Requirement History",
			buttons: [{text: "Ok", click: function(){$(this).dialog("close")}}],
		});
		
		$('.history-btn').button().click(function(e){
			var historyButton = $(this);
			
			$(historyButton).css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-history-modal-throbber'>");
			
			e.preventDefault();
			var attachmentId = $(this).attr("data-attachmentid");
			
			$.post("/portfolio/index/generate-compliance-history", {"attachmentId" : attachmentId}, function(response){
				$("#history-modal-container").empty().html(response);
				$("#history-modal-container").dialog("open");
				$("#load-history-modal-throbber").remove();
				$(historyButton).css("opacity", "1");
			}, "json");
		});
		
		function openCategory(category){
			currentlyOpen = category;
			category.addClass("selectedCategory");
			category.next().slideDown();
			category.find(".imgWrapper").empty();
			category.find(".imgWrapper").append("<img src='/images/accordion_arrow_down.png'>");
		}

		function closeCategory(category){
			category.removeClass("selectedCategory");
			category.next().slideUp();
			category.find(".imgWrapper").empty();
			category.find(".imgWrapper").append("<img src='/images/accordion_arrow_right.png'>");
		}

		function closeCurrent() {
			if(currentlyOpen){
				closeCategory(currentlyOpen);
			}
		}
	}

	function disengageAccordion() {
		var accordion = $("#attachmentsAccordion");
		$(".accordionHeader").each(function(){
			$(this).addClass('no-hover').unbind();
		});	
		accordion.css({"opacity": "0.5", "cursor": "default"}).before("<img src='/images/throbber_small.gif' id='filter-table-throbber'>");

	}

	// turn the sites modal div into a modal
	$("#sites-modal").dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 700,
		title: "List of Cleared Sites"
	});

	// stuff for the cleared site list
	$("#launch-site-list").click(function(event) {
		event.preventDefault();

		var trigger = $(this);
		var siteIds = trigger.attr('data-siteids');
		var userContextId = trigger.attr('data-usercontextid');

		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");

		$.post("/portfolio/index/generate-sites-modal",
			{"site_ids" : siteIds, "userContextId" : userContextId},
			function(resp) {
				$("#sites-modal-content").html($(resp));
				$("#sites-modal").dialog("open");
				$("#sitesCloseButton").button().blur();
				$("#type-filter").buttonset();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);

				$("#sitesCloseButton").click(function(e){
					e.preventDefault();
					$(this).unbind();
					$("#sites-modal").dialog('close');
				});
		
				// filter the sites
				$("#type-filter .ui-button").click(function() {
					var filter = $(this).attr('for');
					if (filter == 'all') {
						$('td.clinical').parent().show();
						$('td.field').parent().show();
						$('td.lab').parent().show();
					}
					if (filter == 'clinical') {
						$('td.clinical').parent().show();
						$('td.field').parent().hide();
						$('td.lab').parent().hide();
					}
					if (filter == 'field') {
						$('td.clinical').parent().hide();
						$('td.field').parent().show();
						$('td.lab').parent().hide();
					}
					if (filter == 'lab') {
						$('td.clinical').parent().hide();
						$('td.field').parent().hide();
						$('td.lab').parent().show();
					}
					
					showNullMsg(filter);
				});

	
			}
	      );
	});

	// turn the compliance email modal div into a modal
	$("#email-modal").dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 700,
		title: "Compliance Email"
	});

	// stuff for the compliance email
	$("#send-req-email").click(function(event) {
		event.preventDefault();

		var trigger = $(this);
		var userContextId = trigger.attr('data-usercontextid');

		trigger.css("opacity", "0").parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");

		$.post("/portfolio/index/generate-compliance-email", {"userContextId" : userContextId},
			function(resp) {
				$("#email-modal-content").html($(resp));
				$("#email-modal").dialog("open");
				initEmailModal();
				$("#load-modal-throbber").remove();
				trigger.css("opacity", 1);

			}
	      );
	});

	function initEmailModal() {
		$("#emailCloseButton").button().blur();
		$("#emailSendButton").button().blur();
	
		$("#emailSendButton").click(function(e){
			e.preventDefault();
			var trigger = $(this);
			var userContextId = trigger.attr('data-usercontextid');

			trigger.hide().parent().append("<img src='/images/throbber_small.gif' id='load-modal-throbber'>");
			var cancelBtn = $('#emailCloseButton').hide();

			$.post("/portfolio/index/send-compliance-email", {
				"userContextId" : userContextId},
				function(resp) {
					$('#preview-msg').slideUp();
					$('#success-msg').slideDown();
                                        cancelBtn.show().find(".ui-button-text").html('Ok');
					$("#load-modal-throbber").remove();
				}
			);
		});
			
		$("#emailCloseButton").click(function(e){
			e.preventDefault();
			$(this).unbind();
			$("#email-modal").dialog('close');
		});
	}
	
	function showNullMsg(filter){
		$("#nullMsg").remove();
		var visibleRows = $("#sites-modal .sites-table tr:visible").length;
		if (visibleRows < 1) {
			$("#tableWrapper").append("<div id='nullMsg' class='"+filter+"'> No "+filter+" sites found.</span>");
		}
	}
});
