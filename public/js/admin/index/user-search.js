$(function(){

	$("#updateDisplay").button();
	$("#displayOptionsTrigger").button();
	

	
	$("#displayOptionsTrigger").click(function(e){
		e.preventDefault();
		if($(".user-search-table").length > 0){
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

	 $("#searchButton").click(function(e){
		e.preventDefault();
		
		var searchString = $("#searchString").val();
		
		if(searchString.length > 0){
		
			$(this).fadeOut();
			$("#searchThrobber").show();
			
			$("#results_area").slideUp();
			$("#results_area").empty();

		
			 var postRequest = $.post(
				'/admin/index/get-users-from-search',
				{ searchString: searchString },
				
				function(response){
					$("#searchButton").fadeIn();
					$("#searchThrobber").hide();
					$("#results_area").append(response);
					updateColumns(false);
					setMasq();
					$("#results_area").slideDown();
				}
			);
			 
			postRequest.error(function() {
				$("#searchButton").fadeIn();
				$("#searchThrobber").hide();
				$("#results_area").append("<div class='clear'></div><div class='grid_12 island withTopMargin'><div class='error'>No response from server. It may have timed out, please create a more specific search.</div></div>");
				$("#results_area").slideDown();
			});
		}
		else {
			// error -  enter something
		}
	});
	 
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
		$("td." + className).each(function(){
			if(animate){
				$(this).fadeIn();
			}
			else {
				$(this).show();
			}
		});
	 }
	 
	 function hideCell(className, animate){
		$("td." + className).each(function(){
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