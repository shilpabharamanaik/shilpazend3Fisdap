$(function(){
    
    $("#displayOptionsTrigger").button();
	$("#emailAllAttendeesOther").button();
	$("#showAllAttendees").button();
	$("#updateDisplay").button();
	$("#outer_results_area").hide();
	
	$("#displayOptionsTrigger").click(function(e){
	e.preventDefault();
	if($(".attendee-search-table").length > 0)
		{
		
			$("#updateDisplay").show();
		}
		else {
			$("#updateDisplay").hide();
		}
		
		$("#displayOptions").dialog("open");

	});
	
	$("#updateDisplay").click(function(){
		updateColumns(false);
		$("#displayOptions").dialog("close");
	});
	
	$("#displayOptions").dialog({ modal: true, autoOpen: false, width: 600, title: "Display Options" });
    
    $("#submit").click(function(e){
        e.preventDefault();
		$("#outer_results_area").show();
        runSearch(e, $('form').serialize());	 
    });
	
	
	
    $("#showAllAttendees").click(function(e){
        e.preventDefault();
		$("#outer_results_area").show();
        runSearch(e, null);	 
    });
	
	$("#export-workshop-report-links .pdfLink").click(function(e){
		e.preventDefault();	
		createPdf($("#results_area div.island").clone(), "workshop-report", "export-workshop-report-links");
	});
	
	function emailedButton() {
		$(".toggleEmailedButton").button().click(function(e){ 
		e.preventDefault();  
		var emailed = ($(this).text() != "Emailed") ? 1 : 0; 
		var btn = $(this); 
		 
		$.post("/events/change-emailed-status/", 
			{"emailed":emailed, "attendeeid":$(this).attr("data-attendeeid")}, 
			function(response){ 
			if (emailed == 1){ 
				$(btn).button("option", "label", "Emailed"); 
			} 
			else{ 
				$(btn).button("option", "label", "Not Emailed"); 
				} 
			}, 
			"json" 
		   ); 
		});		
	}
    
    function runSearch(e, searchParam) {				
            $("#results_area").slideUp();
            $("#results_area").empty();
            processSearchParams(e, searchParam);
	
    }
        
    function processSearchParams(e,searchParam) {
		return $.post("/events/get-attendees-from-search",
			searchParam,
			
			function(response){
				$("#results_area").html(response.table);
				setMasq();
				$("#emailAllAttendeesOutlook").attr("href","mailto:" + response.outlook);
				$("#emailAllAttendeesOther").attr("href","mailto:" + response.other);
				$("#results_area").slideDown();
				$("#attendee-table").tablesorter();
				updateColumns(false);
				deleteAttendee();
				emailedButton();
			}, "json").done();
	}
	
	function deleteAttendee(){
		$(".deleteAttendee").click(function(e){
		var deleteButton  = $(this);	
		e.preventDefault();
		
		$('<div id="dialog-confirm" title="Delete Attendee?"><p>Delete this attendee permanently?</p><p>Warning!: You will not be able to recover this information.</p></div>')
		.dialog({
			resizable:false,		
			height:200,
			width:350,
			modal: true,
			buttons:
			{
				"Delete Attendee": function() {
					window.location = deleteButton.attr("href");
				  $( this ).dialog( "close" );
				},
				Cancel: function() {
				  $( this ).dialog( "close" );
				}
			}
		})
	})
		};
			
  
    function updateColumns(animate){
        var count = 0;
        $("#displayOptions").find("input:checkbox").each(function(){
                if($(this).attr("checked")){
                        showCell($(this).val(), animate);
                        count++;
                }
                else {
                        hideCell($(this).val(), animate);
                }
        });
        
        if(count > 6){
                var newWidth = 130 * count;
                if(newWidth < 900){
                        newWidth = 1020;
                }
                else if(newWidth > 1325){
                        newWidth = 1250;
                }
        }
        else {
                var newWidth = 900;
        }
        
        $(".extraLong").css("width", newWidth + "px");
     }
     
     function showCell(className, animate){
        $("." + className).each(function(){
                if(animate){
                        $(this).fadeIn();
                }
                else {
                        $(this).show();
                }
        });
     }

     function hideCell(className, animate){
        $("." + className).each(function(){
                if(animate){
                        $(this).fadeOut();
                }
                else {
                        $(this).hide();
                }
	    });
     }
     
     function setMasq() {
        $(".masq").each(function(){
                $(this).hover(function(){
                        $(this).find(".imgWrapper").find("img").show();
                }, function(){
                        $(this).find(".imgWrapper").find("img").hide();
                });
        });
     }
    
    });