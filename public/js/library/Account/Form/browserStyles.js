$(function(){
	if ( $.browser.mozilla || $.browser.msie) {
	   $(".formStyleFix").css( "margin-top","0em" );
	   	$("#baseEditDisabled").css( "left","98px" );
	   	$("#preceptorEditDisabled").css( "left","98px" );
	   	$("#preceptorMergeDisabled").css( "left","184px" );
	   	$("#baseContainer h3").css( "width","205px" );
	   	$("#activateSite").css( "width","275px" );
		$("#activateDiv").css("top", "7px");
		$("#activatePrecepDiv").css("top", "7px");
		$("#deactivateDiv").css("top:53px;");
		$("#deactivatePrecepDiv").css("top:53px;");
		$("#activateAllPrecepDiv").css("top:115px;");
		$("#activateAllDiv").css("top:115px;");
		$("#deactivateAllPrecepDiv").css("top:162px;");
		$("#deactivateAllDiv").css("top:68px;");
	} else {
		$(".formStyleFix").css( "margin-top","-0.5em" );
		$("#baseEditDisabled").css( "left","96px" );
		$("#preceptorEditDisabled").css( "left","96px" );
		$("#preceptorMergeDisabled").css( "left","181px" );
		$("#baseContainer h3").css( "width","206px" );
	}

	if( $.browser.msie ){
		$("#moveButtons").css( "margin-top", "-1em");
		$("#moveButtonsPreceptors").css( "margin-top", "-1em");
		$("#activateSite").css( "width","280px" );
		$("dt#active-label").css("margin-top", ".5em");
	}
});