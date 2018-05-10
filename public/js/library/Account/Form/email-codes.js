$(function(){
	$("#emailCloseButton").button();
    // the dialog box for email activation codes
    $("#emailDialog").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:575,
		maxHeight:420,
        title:"Email Activation Codes",
		open: function(){
			emailModalOpenButtonStyles();
		}
        });
	

	
	// open/close triggers for dialog          
    $("a#email").click(function(){
		$("#activeCodesSelectedError").fadeOut();
		$("#noCodesSelectedError").fadeOut();
		$("#warningErrors").hide();

		
		$(this).hide();
		$(this).parent().append("<img id='emailButtonThrobber' src='/images/throbber_small.gif'>");
		
		$("#emailErrors").hide();
		$("#emailInputElements").empty();
		$("#message").val("");
		
		var codes = [];
		var distributedCodes = [];
	
		var activeFlag = false;
		var distributedFlag = false;
		$("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");
			if(checkbox.is(":checked")){
				if($(this).find(".statusCell").html().indexOf("Activated by") >= 0){
					activeFlag = true;
				}
				else if($(this).find(".statusCell").html().indexOf("Distributed to") >= 0){
					distributedFlag = true;
					distributedCodes.push(checkbox.val());
				}
				codes.push(checkbox.val());
			}
		});
		var errors = false;
		
		if(codes.length == 0){
			errors = true;
			$("#noCodesSelectedError").slideDown();
			emailModalOpenButtonStyles();
		}
		
		if(activeFlag){
			errors = true;
			$("#activeCodesSelectedError").slideDown();
			emailModalOpenButtonStyles();
		}
		
		if(!errors) {
			$("#activeCodesSelectedError").fadeOut();
			$("#noCodesSelectedError").fadeOut();

		
			$.post(
				'/account/orders/generate-modal',
				{ codes: codes },
				function(response){
					var returnString = "<table id='emailCodes'>";
					$.each(response, function(key, value) {
						if(jQuery.inArray(value['number'], distributedCodes) >= 0){
							returnString += "<tr class='codeRow distributed'>";
							$("#warningErrors").show();
						}
						else {
							returnString += "<tr class='codeRow'>";
						}
						returnString += "<td class='number'>" + value['number'] + "";
						returnString += "<div class='products'>" + value['products'] + "</div></td>";
						returnString += "<td class='emailInput'><input style='font-size:10pt;' type='text' id='" + value['number'] + "'></td>";
						returnString += "</tr>";
					});
					returnString += "</table>";
					$("#emailInputElements").append(returnString);
					$("#emailDialog").dialog('open');
					emailModalOpenButtonStyles();
				}
			);
		}
	});
	

	
	function emailModalOpenButtonStyles(){
		$("#emailButtonThrobber").remove();
		$("#sendButtonThrobber").hide();
		$("#sendButton").show();
		$("a#email").show();
	}
	
    $("#emailCloseButton").click(function(e){
		e.preventDefault();
		$("#emailDialog").dialog('close');
	});
});