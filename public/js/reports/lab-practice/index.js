$(function(){
	
	if ( $.browser.msie ) {
		$(".wide-table-container").css("margin-bottom", "0em");
		
		$("tr").each(function(){
			$(this).hover(function(e){
				e.preventDefault();
						$(".wide-table-container").css("margin-bottom", "0em");

			}, function(e){
						$(".wide-table-container").css("margin-bottom", "0em");

			});
		});
	}
	
	// set up eureka modal stuff
	initEurekaFunctionality($(".eureka-trigger"));
	
	$("#pdfLink").click(function(e){
		e.preventDefault();
		createReportPDF("labPracticeSummary");
	});

	function createReportPDF(divId, pdfName){
		pdfName = $("h1:first-of-type").text().replace('.', '-') + ".pdf";
		divContents = $('#' + divId).clone();
		divContents.find(".tablescroll_wrapper").css('height', 'auto');
		divContents.find(".lab-practice-report-wrapper").css("background", "#fff").css("width", "100%");
		divContents.find(".lab-practice-report-headers").css("background", "#fff").css("width", "105%");
		divContents.find(".island").css("width", "100%").addClass("withNoShadow");
		return createPdf(divContents, pdfName);
	}
	
	function initEurekaFunctionality(trigger)
	{
		trigger.click(function(e){
			e.preventDefault();
			blockUi(true);
			$.post(
				'/skills-tracker/settings/get-eureka-data',
				{ defId:  $(this).attr("data-defId"),
				  studentId: $(this).attr("data-studentId"),
				  startDate: $(this).attr("data-startDate"),
				  endDate: $(this).attr("data-endDate")},
				function(response){
					$("#eureka-modal-content").empty().append("<div id='eureka-home'></div>");
					$("#eureka-modal").dialog("open");
					$("#eureka-modal-content").append($(response).eurekaGraph());
					blockUi(false);
				}
			);
		});
	}
	
	// the help bubble trigger for the detailed report
	$("#helpBubbleTrigger").click(function(e){
		e.preventDefault();
		if($("#key").css("display") == "none"){
			var offset = $(this).offset();
			$("#key").css("left", offset.left-310).css("top", offset.top+20);
			$("#key").fadeIn();
		}
		else {
			$("#key").fadeOut();
		}
	});
	
	$("#closeHelp").click(function(){
		$("#key").fadeOut();
	});
	
	
	// randomize some table rows
	$("table").each(function(){
		if($(this).attr("data-shufflerows")){
			
			var table = $(this);
			var rows = [];
			var studentRow = "";
			var rowCount = 1;
			
			table.find("tr").each(function(){
				if($(this).find(".student-col").text() == "Anonymous"){
					rows.push($(this));
					$(this).remove();
				}
				else if($(this).find(".student-col").text() != "Student"){
					// we found our student row
					studentRow = $(this);
					$(this).removeClass("odd").addClass("even");
				}
			});
			
			// step through our now random rows and add them to the table again
			$.each(shuffle(rows), function(index, value){
				if(rowCount%2 == 0){value.removeClass("odd").addClass("even");}
				else {value.removeClass("even").addClass("odd");}
				
				table.append(value);
				initEurekaFunctionality(value.find(".eureka-trigger"));
				rowCount++;
			});
		}
	});
	
	
	function shuffle(o){
		for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
		return o;
	}
	
});
