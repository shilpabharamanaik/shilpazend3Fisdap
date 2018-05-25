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
 * This file contains a view helper to insert javascript to pull up an ethnio screener
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_EthnioScreener extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_html= "";

    /**
     * See if the current request URI has a corresponding ethn.io screener in the database and return a line of JS
     *
     * @return string
     */
    public function ethnioScreener() {
        //Get the current request URI
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $url = $request->getRequestUri();

        //Find a matching screener from the DB
        $ethnio = \Fisdap\EntityUtils::getRepository("EthnioScreener")->findOneByUrl($url);

        //If it exists and active, return some JS to do the thing
        if ($ethnio && $ethnio->active) {
            return "<!-- Ethnio Activation Code -->
<script type='text/javascript' language='javascript' src='//ethn.io/{$ethnio->screener_id}.js' async='true' charset='utf-8'></script>";
        }
    }
}