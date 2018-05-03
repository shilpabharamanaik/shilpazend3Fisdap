<?php

/**
 * Twitter Widget helper
 *
 * Displays a small widget for displaying recent Fisdap Tweets
 *
 * Call as $this->twitterWidget() in your layout script
 */
class Fisdap_View_Helper_TwitterWidget
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function twitterWidget()
    {
        $this->view->headScript()->appendFile("/js/jScrollPane/jquery.mousewheel.js")
				->appendFile("/js/jScrollPane/jScrollPane-1.2.3.min.js")
				->appendFile("/js/twitter_ticker.js");
                
        $this->view->headLink()->appendStylesheet("/css/twitter_ticker.css")
				->appendStylesheet("/js/jScrollPane/jScrollPane.css");

        
        $html = '<div id="twitter-ticker" style="z-index:-1;">
		<!-- Twitter container, hidden by CSS and shown if JS is present -->
	
		<div id="top-bar">
		<!-- This contains the title and icon -->
	
		<div id="twitIcon"><img src="/images/twitter_64.png" width="64" height="64" alt="Twitter icon" /></div>
		<!-- The twitter icon -->
	
		<h2 class="tut">Fisdap tweets</h2>
		<!-- Title -->
	
		</div>
	
		<div id="tweet-container"><img id="loading" src="/images/loading.gif" width="16" height="11" alt="Loading.." /></div>
		<!-- The loading gif animation - hidden once the tweets are loaded -->
	
		<div id="scroll"></div>
		<!-- Container for the tweets -->
	
        </div>';
        
        return $html;
    }
}