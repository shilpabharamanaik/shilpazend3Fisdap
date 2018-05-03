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
 * This file contains a view helper to render vimeo videos
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_VimeoVideo extends Zend_View_Helper_Abstract
{
    /**
     * @param string the ID of the video on vimeo
     * @return string the html to render
     */
    public function vimeoThumbnail($video_id, $width = 320)
    {
        $oembed = $this->getOEmbed($video_id, $width);

        return html_entity_decode($oembed->thumbnail_url);
    }

    /**
     * @param string the ID of the video on vimeo
     * @return string the html to render
     */
    public function vimeoEmbed($video_id, $width = 320)
    {
        $oembed = $this->getOEmbed($video_id, $width);

        return $oembed->html;
    }

    private function getOEmbed($video_id, $width)
    {
        /*
        You may want to use oEmbed discovery instead of hard-coding the oEmbed endpoint.
        */
        $oembed_endpoint = 'http://vimeo.com/api/oembed';

        // Grab the video url
        $video_url = "http://vimeo.com/$video_id";

        // Create the URLs
        $xml_url = $oembed_endpoint . ".xml?url=" . rawurlencode($video_url) . "&width=$width";

        // return the oEmbed XML
        return simplexml_load_string($this->getCurlResponse($xml_url)->getBody());
    }

    // Curl helper function
    private function getCurlResponse($url)
    {
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $client = new Zend_Http_Client($url);
        $client->setAdapter($adapter);
        $adapter->setConfig(array(
            "curloptions" => array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => 1,
            )));
        return $client->request();
    }
}
