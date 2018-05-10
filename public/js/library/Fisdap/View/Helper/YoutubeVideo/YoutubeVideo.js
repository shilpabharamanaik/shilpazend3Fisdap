function onYouTubePlayerReady(){
	$('#youtube_player')[0].addEventListener("onStateChange", "stateChangeHandler");
}

function stateChangeHandler(state){
	switch(state){
		case -1:
			//console.log("Loaded");
			break;
		case 0:
			//console.log("Ended");
			makeAjaxCall();
			break;
		case 1:
			//console.log("Playing");
			break;
		case 2:
			//console.log("Paused");
			break;
		case 3:
			//console.log("Buffering");
			break;
		case 5:
			//console.log("Video Cued");
			break;
		default:
			//console.log("Unknown state: " + state);
	}
}

function makeAjaxCall(){
	data = {
		vid: videoID,
		uid: userID
	};

	$.post('/ajax/mark-video-as-viewed', data);
}

$(function(){
	// Add a click listener to the hideVideo link
	$('#hideVideo').click(function(){
		// Need to do two things-
		// First, hide the appropriate elements.
		$('#youtube_player_div').parent().fadeOut(1000);
		//$('#hideVideo').hide();
		
		// Second, post back to the server that someone clicked the link.
		makeAjaxCall();
		
		return false;
	});
});

