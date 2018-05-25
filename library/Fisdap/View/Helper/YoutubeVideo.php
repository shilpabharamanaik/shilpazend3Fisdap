<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This file contains a view helper to render an embedded youtube video
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_YoutubeVideo extends Zend_View_Helper_Abstract
{
    /**
     * @param string the ID of the video on youtube
     * @return string the html to render
     */
    public function youtubeVideo($videoId, $viewscript = null, $width = 640, $height = 385)
    {
		//$videoId = 'aYE2Ah5Ob5k';
		
		// Check to see if the user has hidden this video already.
		$em = \Fisdap\EntityUtils::getEntityManager();
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$videoView = $em->getRepository('Fisdap\Entity\VideoView')->findOneBy(array('user' => $user->id, 'video_key' => $videoId));
		
		// If they already have one, do nothing for right now.  Might want to
		// throw in a link to view the video eventually though.
		if($videoView){
			return '';
		}else{
			$html .= '
				<script lang="text\javascript" src="/js/library/Fisdap/View/Helper/YoutubeVideo/YoutubeVideo.js"></script>
				<script id="swfobjScript" lang="text\javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
				
				<div id="hideLander"></div>
				<div id="youtube">You need Flash player 8+ and JavaScript enabled to view this video.</div>
				
				<script lang="text\javascript">
					var videoID = "' . $videoId . '";
					var userID = "' . $user->id . '";

					var params = { allowScriptAccess: "always", wmode: "transparent", fs: 1};
					var atts = { id: "youtube_player" };
					swfobject.embedSWF("https://www.youtube.com/e/' . $videoId . '?wmode=transparent&enablejsapi=1&version=3", "youtube", "' . $width . '", "' . $height . '", "8", null, null, params, atts);
				</script>';

			$link = "<a href='#hideLander' id='hideVideo'>Don't show me this video again</a>.";
			
			//If we passed in a view script, render the layout of the video
			if ($viewscript) {
				return '<div id="youtube_player_div">' . $this->view->partial($viewscript, array('video' => $html, 'link' => $link)) . "</div>";
			}
			
			return '<div id="youtube_video_div">' . $html . "<br>" . $link . '</div>';
		}
    }
}
