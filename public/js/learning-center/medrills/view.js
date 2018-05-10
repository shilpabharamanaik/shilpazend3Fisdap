/**
 * Created by khanson on 10/8/14.
 */
$(function() {
    // get the video count and hide the arrows if there's only one video
    var videoCount = $(".view_video").length;
    if (videoCount == 1) {
        $(".nav-arrow").css({"visibility": "hidden"});
    }

    // show the selected video
    var selectedVideo = $("#video-count").html();
    showSelectedVideo(selectedVideo);

    // functionality for "previous" arrow
    $("#prev-video").click(function(){
        var currentVideo = parseInt($("#video-count").html());

        // stop playing the current video, if applicable
        stopPlayback(currentVideo);

        // figure out which video is the previous one
        if (currentVideo == 1) {
            var previousVideo = videoCount;
        } else {
            var previousVideo = currentVideo-1;
        }

        // show the previous video
        showSelectedVideo(previousVideo);
    });

    // functionality for "next" arrow
    $("#next-video").click(function(){
        var currentVideo = parseInt($("#video-count").html());

        // stop playing the current video, if applicable
        stopPlayback(currentVideo);

        // figure out which video is the next one
        if (currentVideo == videoCount) {
            var nextVideo = 1;
        } else {
            var nextVideo = currentVideo+1;
        }

        // show the next video
        showSelectedVideo(nextVideo);
    });

    // show the selected video in the portal
    function showSelectedVideo(selectedVideo) {
        // hide all the videos, then show the selected one
        $(".view_video").hide();
        $(".view_video[data-videoNumber="+selectedVideo+"]").fadeIn(800);

        // update the counter
        $("#video-count").html(selectedVideo)
    }

    // stop playback on the indicated video
    function stopPlayback(video) {
        var frame = $(".view_video[data-videoNumber="+video+"] iframe");
        $(frame).attr('src', $(frame).attr('src'));
    }
});