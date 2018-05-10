var defaultProcessingMsg = "Fisdap Robot is busy processing your request";
var busyRobot;
busyRobot = function(){
	$("#busy-robot").dialog("open");
}

var closeBusyRobot;
closeBusyRobot = function(){
	if ($("#busy-robot").length > 0) {
		$("#busy-robot").dialog("close");
		// reset the processing text upon closing
		$("#busy-robot").find("#robot-processing-txt").text(defaultProcessingMsg);
	}
}

var brokenRobot;
brokenRobot = function(refresh, msg){
	
	setUpRobots();
	closeBusyRobot();
	
	if (msg) {
		$("#broken-robot").find(".robot-refresh-txt").html(msg);
	}
	
	$("#broken-robot").dialog("open");
	
	if (refresh != false) {
		setTimeout(function(){ document.location.reload();}, 3000 );
	}
	else {
		$("#broken-robot").find(".refreshing-throbber").hide();
	}
	
}

var setUpRobots;
setUpRobots = function(){
	if ($("#broken-robot").length <= 0) {
		$("body").append("<div id='broken-robot'><img src='/images/broken_robot.png'><div id='robot-processing-txt'>Oops! Looks like something went wrong.</div> <div class='robot-refresh-txt'>We're refreshing your page to fix the problem.</div><img class='refreshing-throbber' src='/images/throbber_small.gif' id='reload-throbber'>");
		$("#broken-robot").dialog({modal:true, autoOpen: false, width:350, resizable: false});
		$("#broken-robot").prev().hide();
	}

	if ($("#busy-robot").length <= 0) {	
		$("body").append("<div id='busy-robot'><img src='/images/busy-robot.gif'><div id='robot-processing-txt'>"+defaultProcessingMsg+"</div><div id='robot-minutes-txt'>It could take him several minutes.</div><div class='robot-refresh-txt'>Please do not refresh this page or use your browser's back button.</div></div>");
		$("#busy-robot").dialog({modal:true, autoOpen: false, width:470, resizable: false});
		$("#busy-robot").prev().hide();
	}
}

$(function(){
	setUpRobots();
});